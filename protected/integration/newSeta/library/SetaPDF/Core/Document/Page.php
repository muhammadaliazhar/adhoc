<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Page.php 1763 2022-07-26 07:14:52Z jan.slabon $
 */

/**
 * Class representing a PDF page
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page implements SetaPDF_Core_Canvas_ContainerInterface
{
    /**
     * The page indirect object
     *
     * @var SetaPDF_Core_Type_IndirectObject
     */
    protected $_pageObject;

    /**
     * Inherited attributes
     *
     * @var array An array of SetaPDF_Core_Type_Dictionary_Entry instances
     */
    protected $_inheritedAttributes = [];

    /**
     * Flag for resolving of inherited attributes
     *
     * @var boolean
     */
    protected $_inheritedAttributesResolved = false;

    /**
     * Flag for observing the page object
     *
     * @var boolean
     */
    protected $_pageIsObserved = false;

    /**
     * The annotations object
     *
     * @var SetaPDF_Core_Document_Page_Annotations
     */
    protected $_annotations;

    /**
     * The contents object for this page
     *
     * @var SetaPDF_Core_Document_Page_Contents
     */
    protected $_contents;

    /**
     * The canvas object of this page
     *
     * @var SetaPDF_Core_Canvas
     */
    protected $_canvas;

    /**
     * The additional actions object of this page
     *
     * @var SetaPDF_Core_Document_Page_AdditionalActions
     */
    protected $_additionalActions;

    /**
     * Creates a new page for a specific document.
     *
     * @param SetaPDF_Core_Document $document
     * @param array|SetaPDF_Core_Type_Dictionary $values
     * @return SetaPDF_Core_Document_Page
     * @throws SetaPDF_Core_Exception
     */
    public static function create(SetaPDF_Core_Document $document, $values = [])
    {
        if ($values instanceof SetaPDF_Core_Type_Dictionary) {
            $page = $values;
        } else {
            $page = new SetaPDF_Core_Type_Dictionary();
            $page->offsetSet('Type', new SetaPDF_Core_Type_Name('Page', true));

            foreach ($values AS $value) {
                $page->offsetSet(null, $value);
            }
        }

        // Add required resource dictionary
        if (!$page->offsetExists('Resources')) {
            $page->offsetSet('Resources', new SetaPDF_Core_Type_Dictionary());
        }

        return new self($document->createNewObject($page));
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_IndirectObject $pageObject
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct(SetaPDF_Core_Type_IndirectObject $pageObject)
    {
        SetaPDF_Core_Type_Dictionary::ensureType($pageObject);

        // TODO: Check for default/required keys

        $this->_pageObject = $pageObject;
    }

    /**
     * Release memory/resources.
     */
    public function cleanUp()
    {
        $this->_pageObject = null;
        if ($this->_annotations !== null) {
            $this->_annotations->cleanUp();
            $this->_annotations = null;
        }

        if ($this->_contents !== null) {
            $this->_contents->cleanUp();
            $this->_contents = null;
        }

        if ($this->_canvas !== null) {
            $this->_canvas->cleanUp();
            $this->_canvas = null;
        }

        if ($this->_additionalActions !== null) {
            $this->_additionalActions->cleanUp();
            $this->_additionalActions = null;
        }
    }

    /**
     * Get the page indirect object.
     *
     * @param boolean $observe
     * @return SetaPDF_Core_Type_IndirectObject
     */
    public function getPageObject($observe = false)
    {
        if ($observe === true) {
            $this->_ensureObservation();
        }
        return $this->_pageObject;
    }

    /**
     * Get the page object.
     * 
     * @param bool $observe
     * @return SetaPDF_Core_Type_IndirectObject
     */
    public function getObject($observe = false)
    {
        return $this->getPageObject($observe);
    }

    /**
     * Get the page dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getDictionary()
    {
        return SetaPDF_Core_Type_Dictionary::ensureType($this->getObject(true));
    }

    /**
     * Get the pages stream proxy object.
     * 
     * @return SetaPDF_Core_Document_Page_Contents
     */
    public function getStreamProxy()
    {
        return $this->getContents();
    }

    /**
     * Ensures that all inherited properties are resolved.
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _ensureInheritedAttributes()
    {
        if ($this->_inheritedAttributesResolved) {
            return;
        }

        $pageDict = $this->getDictionary();
        $needed = [];
        foreach ([
             'Resources', 'MediaBox', 'CropBox', 'Rotate'
         ] as $key) {
            if (!$pageDict->offsetExists($key)) {
                $needed[$key] = true;
            }
        }

        if (count($needed) > 0) {
            $parentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($pageDict, 'Parent');

            /** @noinspection PhpConditionAlreadyCheckedInspection */
            while ($parentDict instanceof SetaPDF_Core_Type_Dictionary && count($needed) > 0) {
                foreach (array_keys($needed) AS $key) {
                    if ($parentDict->offsetExists($key)) {
                        $this->_inheritedAttributes[$key] = $parentDict->offsetGet($key);
                        unset($needed[$key]);
                    }
                }

                $tmpParentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($parentDict, 'Parent');
                /** @noinspection NotOptimalIfConditionsInspection */
                if ((count($needed) > 0) && ($tmpParentDict instanceof SetaPDF_Core_Type_Dictionary)) {
                    $parentDict = $tmpParentDict;
                } else {
                    break;
                }
            }
        }

        $this->_inheritedAttributesResolved = true;
    }

    /**
     * Get an attribute of the page object or from an inherited pages object.
     *
     * @param string $name
     * @param bool $inherited
     * @return SetaPDF_Core_Type_AbstractType|null
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAttribute($name, $inherited = true)
    {
        $pageDict = $this->getDictionary();

        if ($pageDict->offsetExists($name)) {
            return $pageDict->offsetGet($name);
        }

        if ($inherited &&
            (
                $name === 'Resources' || $name === 'MediaBox' ||
                $name === 'CropBox' || $name === 'Rotate'
            )
        ) {
            $this->_ensureInheritedAttributes();
            if (isset($this->_inheritedAttributes[$name])) {
                return $this->_inheritedAttributes[$name];
            }
        }

        return null;
    }

    /**
     * Make sure that the page object is observed.
     */
    protected function _ensureObservation()
    {
        if ($this->_pageIsObserved === false) {
            $this->_pageObject->observe();
            $this->_pageIsObserved = true;
        }
    }

    /**
     * Flattens the inherited attributes to the main page object.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function flattenInheritedAttributes()
    {
        $this->_ensureInheritedAttributes();

        if (count($this->_inheritedAttributes) === 0) {
            return;
        }

        $pageDict = $this->getDictionary();
        foreach ($this->_inheritedAttributes AS $entry) {
            $pageDict->offsetSet(null, clone $entry);
        }
    }

    /**
     * Get width and height of the page.
     *
     * @param string $box
     * @param boolean $fallback
     * @return array|boolean array(width, height)
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getWidthAndHeight($box = SetaPDF_Core_PageBoundaries::CROP_BOX, $fallback = true)
    {
        $boundary = $this->getBoundary($box, $fallback);
        if ($boundary === false) {
            return false;
        }

        $rotation = $this->getRotation();
        $interchange = ($rotation / 90) % 2;

        return [
            $interchange ? $boundary->getHeight() : $boundary->getWidth(),
            $interchange ? $boundary->getWidth() : $boundary->getHeight()
        ];
    }

    /**
     * Get the width of the page.
     *
     * @param string $box
     * @param boolean $fallback
     * @return float|integer|boolean
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getWidth($box = SetaPDF_Core_PageBoundaries::CROP_BOX, $fallback = true)
    {
        $widthAndHeight = $this->getWidthAndHeight($box, $fallback);
        return is_array($widthAndHeight) ? $widthAndHeight[0] : false;
    }

    /**
     * Get the height of the page.
     *
     * @param string $box
     * @param boolean $fallback
     * @return float|integer|boolean
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getHeight($box = SetaPDF_Core_PageBoundaries::CROP_BOX, $fallback = true)
    {
        $widthAndHeight = $this->getWidthAndHeight($box, $fallback);
        return is_array($widthAndHeight) ? $widthAndHeight[1] : false;
    }

    /**
     * Get a page boundary box of the page (wihtout logic for invalid values).
     *
     * @param string $box See {@link SetaPDF_Core_PageBoundaries::XXX_BOX} constants
     * @param boolean $fallback Use the fallback box instead if box not exist
     * @param boolean $asRect Return boundary box as {@link SetaPDF_Core_DataStructure_Rectangle}
     * @return boolean|SetaPDF_Core_DataStructure_Rectangle|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_Type_Exception
     * @see getBoundary()
     */
    protected function _getBoundary($box = SetaPDF_Core_PageBoundaries::CROP_BOX, $fallback = true, $asRect = true)
    {
        /** @var SetaPDF_Core_Type_Array $value */
        $value = $this->getAttribute($box);

        if ($value !== null) {
            if ($asRect) {
                return new SetaPDF_Core_DataStructure_Rectangle($value->ensure());
            }
            return $value;
        }

        // No fallback
        if ($fallback === false) {
            return false;
        }

        // $box is bleed, trim or art box
        if (
            $box === SetaPDF_Core_PageBoundaries::BLEED_BOX ||
            $box === SetaPDF_Core_PageBoundaries::TRIM_BOX ||
            $box === SetaPDF_Core_PageBoundaries::ART_BOX
        ) {
            return $this->_getBoundary(SetaPDF_Core_PageBoundaries::CROP_BOX, true, $asRect);
        }

        // $box is crop box
        if ($box === SetaPDF_Core_PageBoundaries::CROP_BOX) {
            return $this->_getBoundary(SetaPDF_Core_PageBoundaries::MEDIA_BOX, true, $asRect);
        }

        return false;
    }

    /**
     * Get a page boundary box of the page.
     *
     * To work with the boundary box it should be cloned and reset by the
     * {@link SetaPDF_Core_Document_Page::setBoundary()} method. This is
     * necessary because a box could be inherited by a parent page tree node.
     *
     * @param string $box See {@link SetaPDF_Core_PageBoundaries::XXX_BOX} constants
     * @param boolean $fallback Use the fallback box instead if box not exist
     * @param boolean $asRect Return boundary box as {@link SetaPDF_Core_DataStructure_Rectangle}
     * @return boolean|SetaPDF_Core_DataStructure_Rectangle|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getBoundary($box = SetaPDF_Core_PageBoundaries::CROP_BOX, $fallback = true, $asRect = true)
    {
        $boundary = $this->_getBoundary($box, $fallback);
        if ($boundary === false) {
            return false;
        }

        // The crop, bleed, trim, and art boxes shall not ordinarily extend beyond the boundaries of the media box.
        // If they do, they are effectively reduced to their intersection with the media box.
        if ($box !== SetaPDF_Core_PageBoundaries::MEDIA_BOX) {
            $mediaBox = $this->getBoundary(SetaPDF_Core_PageBoundaries::MEDIA_BOX, $fallback);
            if ($mediaBox === false) {
                return $boundary;
            }

            if ($mediaBox->intersect($boundary)) {
                $llx = max($boundary->getLlx(), $mediaBox->getLlx());
                $lly = max($boundary->getLly(), $mediaBox->getLly());
                $urx = min($boundary->getUrx(), $mediaBox->getUrx());
                $ury = min($boundary->getUry(), $mediaBox->getUry());

                $boundary = SetaPDF_Core_DataStructure_Rectangle::byArray([$llx, $lly, $urx, $ury]);
            } elseif ($fallback === false) {
                return false;
            } else if ($box === SetaPDF_Core_PageBoundaries::CROP_BOX) {
                $boundary = $mediaBox;
            } else {
                $boundary = $this->getBoundary();
            }
        }

        if ($asRect) {
            return $boundary;
        }

        return $boundary->getValue();
    }

    /**
     * Checks a boundary for validity.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|SetaPDF_Core_Type_Array $newBoundary
     * @param string $newBox
     * @throws SetaPDF_Core_Type_Exception
     * @throws OutOfBoundsException
     */
    private function _checkBoundary($newBoundary, $newBox)
    {
        if (!($newBoundary instanceof SetaPDF_Core_DataStructure_Rectangle)) {
            $newBoundary = new SetaPDF_Core_DataStructure_Rectangle($newBoundary);
        }

        if ($newBox === SetaPDF_Core_PageBoundaries::MEDIA_BOX) {
            $artBox = $this->getBoundary(SetaPDF_Core_PageBoundaries::ART_BOX, false);
            if ($artBox !== false && !$newBoundary->contains($artBox)) {
                throw new OutOfBoundsException('new MediaBox wouldn\'t be in the ArtBox');
            }

            $bleedBox = $this->getBoundary(SetaPDF_Core_PageBoundaries::BLEED_BOX, false);
            if ($bleedBox !== false && !$newBoundary->contains($bleedBox)) {
                throw new OutOfBoundsException('new MediaBox wouldn\'t be in the BleedBox');
            }

            $cropBox = $this->getBoundary(SetaPDF_Core_PageBoundaries::CROP_BOX, false);
            if ($cropBox !== false && !$newBoundary->contains($cropBox)) {
                throw new OutOfBoundsException('new MediaBox wouldn\'t be in the CropBox');
            }

            $trimBox = $this->getBoundary(SetaPDF_Core_PageBoundaries::TRIM_BOX, false);
            if ($trimBox !== false && !$newBoundary->contains($trimBox)) {
                throw new OutOfBoundsException('new MediaBox wouldn\'t be in the TrimBox');
            }
        } else {
            $mediaBox = $this->getBoundary(SetaPDF_Core_PageBoundaries::MEDIA_BOX);

            if (!$mediaBox->contains($newBoundary)) {
                throw new OutOfBoundsException('new ' . $newBox . ' wouldn\'t be in the MediaBox');
            }
        }
    }

    /**
     * Set a boundary box.
     *
     * A boundary consists of four numeric values: llx, lly, urx and ury. They can be passed in various ways:
     *
     * <ul>
     *  <li>By a simple PHP array.</li>
     *  <li>A {@link SetaPDF_Core_Type_Array PDF Array} with 4 {@link SetaPDF_Core_Type_Numeric numeric} values.</li>
     *  <li>An instance of {@link SetaPDF_Core_DataStructure_Rectangle}.</li>
     *  <li>
     *     An instance of {@link SetaPDF_Core_Type_Dictionary_Entry} where the key defines the box and the value the
     *     boundary itself.
     *  </li>
     * </ul>
     *
     * @param array|SetaPDF_Core_Type_Dictionary_Entry|SetaPDF_Core_Type_Array|SetaPDF_Core_DataStructure_Rectangle $boundary
     *          The data of the boundary.
     * @param string $box The page boundary name
     * @param boolean $checkBoundary Ensure that boundary values are valid or not
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setBoundary($boundary, $box = SetaPDF_Core_PageBoundaries::CROP_BOX, $checkBoundary = true)
    {
        $document = $this->_pageObject->getOwnerPdfDocument();
        SetaPDF_Core_SecHandler::checkPermission($document, SetaPDF_Core_SecHandler::PERM_ASSEMBLE);

        $pageDict = $this->getDictionary();

        if (is_array($boundary)) {
            $boundary = SetaPDF_Core_DataStructure_Rectangle::byArray($boundary, true);
        }

        if ($boundary instanceof SetaPDF_Core_Type_Dictionary_Entry) {
            if ($checkBoundary) {
                $this->_checkBoundary($boundary->getValue(), $boundary->getKeyValue());
            }
            $pageDict->offsetSet(null, $boundary);

        } elseif ($boundary instanceof SetaPDF_Core_Type_Array) {
            if ($checkBoundary) {
                $this->_checkBoundary($boundary, $box);
            }
            $pageDict->offsetSet($box, $boundary);

        } elseif ($boundary instanceof SetaPDF_Core_DataStructure_Rectangle) {
            if ($checkBoundary) {
                $this->_checkBoundary($boundary, $box);
            }
            $pageDict->offsetSet($box, $boundary->getValue());

        } elseif ($boundary === null) {
            if($box === SetaPDF_Core_PageBoundaries::MEDIA_BOX) {
                throw new InvalidArgumentException('Deleting the MediaBox isn\'t possible');
            }

            $pageDict->offsetUnset($box);

        } else {
            throw new InvalidArgumentException(
                'Argument have to be an instance of SetaPDF_Core_Type_Dictionary_Entry, ' .
                'SetaPDF_Core_Type_Array or SetaPDF_Core_DataStructure_Rectangle'
            );
        }
    }

    /**
     * Get the media box of this page.
     *
     * @param bool $fallback
     * @param bool $asRect
     * @return bool|SetaPDF_Core_DataStructure_Rectangle|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getMediaBox($fallback = true, $asRect = true)
    {
        return $this->getBoundary(SetaPDF_Core_PageBoundaries::MEDIA_BOX, $fallback, $asRect);
    }

    /**
     * Set the media box.
     *
     * @param array|SetaPDF_Core_Type_Dictionary_Entry|SetaPDF_Core_Type_Array|SetaPDF_Core_DataStructure_Rectangle $boundary
     * @param boolean $checkBoundary Ensure that boundary values are valid or not
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setMediaBox($boundary, $checkBoundary = true)
    {
        $this->setBoundary($boundary, SetaPDF_Core_PageBoundaries::MEDIA_BOX, $checkBoundary);
    }

    /**
     * Get the crop box of this page.
     *
     * @param bool $fallback
     * @param bool $asRect
     * @return bool|SetaPDF_Core_DataStructure_Rectangle|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getCropBox($fallback = true, $asRect = true)
    {
        return $this->getBoundary(SetaPDF_Core_PageBoundaries::CROP_BOX, $fallback, $asRect);
    }

    /**
     * Set the crop box.
     *
     * @param array|SetaPDF_Core_Type_Dictionary_Entry|SetaPDF_Core_Type_Array|SetaPDF_Core_DataStructure_Rectangle $boundary
     * @param boolean $checkBoundary Ensure that boundary values are valid or not
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setCropBox($boundary, $checkBoundary = true)
    {
        $this->setBoundary($boundary, SetaPDF_Core_PageBoundaries::CROP_BOX, $checkBoundary);
    }

    /**
     * Get the bleed box of this page.
     *
     * @param bool $fallback
     * @param bool $asRect
     * @return bool|SetaPDF_Core_DataStructure_Rectangle|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getBleedBox($fallback = true, $asRect = true)
    {
        return $this->getBoundary(SetaPDF_Core_PageBoundaries::BLEED_BOX, $fallback, $asRect);
    }

    /**
     * Set the bleed box.
     *
     * @param array|SetaPDF_Core_Type_Dictionary_Entry|SetaPDF_Core_Type_Array|SetaPDF_Core_DataStructure_Rectangle $boundary
     * @param boolean $checkBoundary Ensure that boundary values are valid or not
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setBleedBox($boundary, $checkBoundary = true)
    {
        $this->setBoundary($boundary, SetaPDF_Core_PageBoundaries::BLEED_BOX, $checkBoundary);
    }

    /**
     * Get the trim box of this page.
     *
     * @param bool $fallback
     * @param bool $asRect
     * @return bool|SetaPDF_Core_DataStructure_Rectangle|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getTrimBox($fallback = true, $asRect = true)
    {
        return $this->getBoundary(SetaPDF_Core_PageBoundaries::TRIM_BOX, $fallback, $asRect);
    }

    /**
     * Set the trim box.
     *
     * @param array|SetaPDF_Core_Type_Dictionary_Entry|SetaPDF_Core_Type_Array|SetaPDF_Core_DataStructure_Rectangle $boundary
     * @param boolean $checkBoundary Ensure that boundary values are valid or not
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setTrimBox($boundary, $checkBoundary = true)
    {
        $this->setBoundary($boundary, SetaPDF_Core_PageBoundaries::TRIM_BOX, $checkBoundary);
    }

    /**
     * Get the art box of this page.
     *
     * @param bool $fallback
     * @param bool $asRect
     * @return bool|SetaPDF_Core_DataStructure_Rectangle|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getArtBox($fallback = true, $asRect = true)
    {
        return $this->getBoundary(SetaPDF_Core_PageBoundaries::ART_BOX, $fallback, $asRect);
    }

    /**
     * Set the art box.
     *
     * @param array|SetaPDF_Core_Type_Dictionary_Entry|SetaPDF_Core_Type_Array|SetaPDF_Core_DataStructure_Rectangle $boundary
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setArtBox($boundary)
    {
        $this->setBoundary($boundary, SetaPDF_Core_PageBoundaries::ART_BOX);
    }

    /**
     * Get the page rotation.
     *
     * @return integer
     */
    public function getRotation()
    {
        try {
            $rotate = SetaPDF_Core_Type_Numeric::ensureType($this->getAttribute('Rotate'));
        } catch (SetaPDF_Core_Type_Exception $e) {
            return 0;
        }

        $rotation = (int)$rotate->getValue() % 360;

        if ($rotation < 0) {
            $rotation += 360;
        }

        return $rotation;
    }

    /**
     * Set the page rotation.
     *
     * @param integer $rotation The rotation value
     * @return SetaPDF_Core_Document_Page Returns the SetaPDF_Core_Document_Page object for method chaining.
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setRotation($rotation)
    {
        if (($rotation % 90) !== 0) {
            throw new InvalidArgumentException('The page rotation value has to be a multiple of 90.');
        }

        $document = $this->_pageObject->getOwnerPdfDocument();
        SetaPDF_Core_SecHandler::checkPermission($document, SetaPDF_Core_SecHandler::PERM_ASSEMBLE);

        $rotation %= 360;

        $pageDict = $this->getDictionary();

        $rotationValue = SetaPDF_Core_Type_Dictionary_Helper::getValue($pageDict, 'Rotate');
        if (!$rotationValue instanceof SetaPDF_Core_Type_Numeric) {
            $pageDict->offsetSet('Rotate', new SetaPDF_Core_Type_Numeric($rotation));
            return $this;
        }

        $rotationValue->setValue($rotation);

        return $this;
    }

    /**
     * Rotate a page by degrees.
     *
     * @param integer $rotation Degrees to rotate by
     * @return SetaPDF_Core_Document_Page Returns the SetaPDF_Core_Document_Page object for method chaining.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function rotateBy($rotation)
    {
        $currentRotation = $this->getRotation();
        $this->setRotation($currentRotation + $rotation);

        return $this;
    }

    /**
     * Get the orientation of the page.
     *
     * @param string $box See {@link SetaPDF_Core_PageBoundaries::XXX_BOX} constants
     * @param bool $fallback Use the fallback box instead if box not exist
     * @return bool|string false or one of {@link SetaPDF_Core_PageFormats::ORIENTATION_XXX} constants
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getOrientation($box = SetaPDF_Core_PageBoundaries::CROP_BOX, $fallback = true)
    {
        $widthAndHeight = $this->getWidthAndHeight($box, $fallback);
        if (!is_array($widthAndHeight)) {
            return false;
        }

        return SetaPDF_Core_PageFormats::getOrientation($widthAndHeight[0], $widthAndHeight[1]);
    }

    /**
     * Gets the annotation instance of this page.
     *
     * @return SetaPDF_Core_Document_Page_Annotations
     */
    public function getAnnotations()
    {
        if ($this->_annotations === null) {
            $this->_annotations = new SetaPDF_Core_Document_Page_Annotations($this);
        }

        return $this->_annotations;
    }

    /**
     * Gets the contents instance of this page.
     *
     * @return SetaPDF_Core_Document_Page_Contents
     */
    public function getContents()
    {
        if ($this->_contents === null) {
            $this->_contents = new SetaPDF_Core_Document_Page_Contents($this);
        }

        return $this->_contents;
    }

    /**
     * Gets the canvas instance for this page.
     *
     * @return SetaPDF_Core_Canvas
     */
    public function getCanvas()
    {
        if ($this->_canvas === null) {
            $this->_canvas = new SetaPDF_Core_Canvas($this);
        }

        return $this->_canvas;
    }

    /**
     * Gets the additional actions object instance for this page.
     *
     * @return SetaPDF_Core_Document_Page_AdditionalActions
     */
    public function getAdditionalActions()
    {
        if ($this->_additionalActions === null) {
            $this->_additionalActions = new SetaPDF_Core_Document_Page_AdditionalActions($this);
        }

        return $this->_additionalActions;
    }

    /**
     * Get the date and time the page was edited.
     *
     * @param boolean $asString
     * @return null|string|SetaPDF_Core_DataStructure_Date
     * @throws Exception
     */
    public function getLastModified($asString = true)
    {
        try {
            $lastModified = SetaPDF_Core_Type_AbstractType::ensureWithType(
                SetaPDF_Core_Type_StringValue::class,
                $this->getAttribute('LastModified', false)
            );
        } catch (SetaPDF_Core_Type_Exception $e) {
            return null;
        }

        if ($asString === true) {
            return $lastModified->getValue();
        }

        return new SetaPDF_Core_DataStructure_Date($lastModified->ensure());
    }

    /**
     * Set the date and time the page was edited.
     *
     * @param string|SetaPDF_Core_DataStructure_Date $date The last modification date. An instance of
     *          {@link SetaPDF_Core_DataStructure_Date}. Alternatively a string which is passed to its constructor.
     * @throws SetaPDF_Core_Type_Exception
     * @throws Exception
     */
    public function setLastModified($date)
    {
        $pageDict = $this->getDictionary();

        if ($date === null) {
            $pageDict->offsetUnset('LastModified');
            return;
        }

        if (!($date instanceof SetaPDF_Core_DataStructure_Date)) {
            $date = new SetaPDF_Core_DataStructure_Date($date);
        }

        $pageDict->offsetSet('LastModified', $date->getValue());
    }

    /**
     * Get a group attributes object.
     *
     * @return null|SetaPDF_Core_TransparencyGroup
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getGroup()
    {
        $pageDict = $this->getDictionary();
        if (!$pageDict->offsetExists('Group')) {
            return null;
        }
    
        return new SetaPDF_Core_TransparencyGroup($pageDict->getValue('Group'));
    }

    /**
     * Set the group attributes object.
     *
     * @param false|SetaPDF_Core_TransparencyGroup $group
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setGroup($group)
    {
        $pageDict = $this->getDictionary();
        if ($group === false) {
            $pageDict->offsetUnset('Group');
            return;
        }
    
        if (!$group instanceof SetaPDF_Core_TransparencyGroup) {
            throw new InvalidArgumentException('Group parameter has to be an instance of SetaPDF_Core_TransparencyGroup');
        }
    
        $pageDict->offsetSet('Group', $group->getDictionary());
    }

    /**
     * Get the metadata stream of a page.
     *
     * This is a method for low level access to the XMP stream data of a page.
     *
     * @return null|string Null if no metadata are available.<br/>
     *                     A string if the desired structure is available.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getMetadata()
    {
        $metadataStream = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Metadata');
        if ($metadataStream instanceof SetaPDF_Core_Type_Stream) {
            return $metadataStream->getStream();
        }

        return null;
    }

    /**
     * Set the metadata stream.
     *
     * To remove the metadata just pass null to this method.
     *
     * @TODO Automatically remove the XML declaration in the first line
     * @param string $metadata
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setMetadata($metadata)
    {
        $pageObject = $this->getPageObject(true);
        $pageDict = SetaPDF_Core_Type_Dictionary::ensureType($pageObject);
        $metadataExists = $pageDict->offsetExists('Metadata');
        $document = $pageObject->getOwnerPdfDocument();

        if ($metadata === null) {
            if ($metadataExists) {
                $streamReference = $pageDict->getValue('Metadata');
                $document->deleteObject($streamReference->getValue());
                $pageDict->offsetUnset('Metadata');
            }
            return;
        }

        if ($metadataExists) {
            try {
                $stream = SetaPDF_Core_Type_Stream::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($pageDict, 'Metadata')
                );
                $stream->setStream($metadata);
                return;
            } catch (SetaPDF_Core_Type_Exception $e) {
                // ignore this and create a new object
            }
        }

        $stream = new SetaPDF_Core_Type_Stream();
        $streamDictionary = new SetaPDF_Core_Type_Dictionary();
        $streamDictionary->offsetSet('Type', new SetaPDF_Core_Type_Name('Metadata', true));
        $streamDictionary->offsetSet('Subtype', new SetaPDF_Core_Type_Name('XML', true));
        $stream->setValue($streamDictionary);
        $stream->setStream($metadata);

        $pageDict->offsetSet('Metadata', $document->createNewObject($stream));
    }

    /**
     * Converts the page object into a form XObject.
     *
     * @param SetaPDF_Core_Document $document
     * @param string $box The name of the bounding box
     * @return SetaPDF_Core_XObject_Form
     * @throws SetaPDF_Exception_NotImplemented
     * @throws SetaPDF_Core_Type_Exception
     */
    public function toXObject(SetaPDF_Core_Document $document, $box = SetaPDF_Core_PageBoundaries::CROP_BOX)
    {
        $dict = new SetaPDF_Core_Type_Dictionary();
        $dict->offsetSet('Type', new SetaPDF_Core_Type_Name('XObject', true));
        $dict->offsetSet('Subtype', new SetaPDF_Core_Type_Name('Form', true));
        $bbox = $this->getBoundary($box);
        $dict->offsetSet('BBox', clone $bbox->getValue());
        $resources = $this->getAttribute('Resources');
        if ($resources) {
            $dict->offsetSet(null, clone $resources);
        }

        $contents = $this->getContents();

        if ($contents->count() === 1) {
            $stream = clone $contents->getStreamObject();
            $filter = $stream->getValue()->offsetGet('Filter');
            if ($filter) {
                $filter = clone $filter;
                $dict->offsetSet(null, $filter);
            }
            $stream->setValue($dict);
            
        } else {
            $dict->offsetSet('Filter', new SetaPDF_Core_Type_Name('FlateDecode', true));
            $stream = new SetaPDF_Core_Type_Stream($dict);
            $stream->setStream($contents->getStream());
        }

        $rotation = $this->getRotation();
        $gs = new SetaPDF_Core_Canvas_GraphicState();
        if ($rotation !== 0) {
            switch ($rotation) {
                case -270:
                case 90:
                    $gs->translate(0, $bbox->getWidth());
                    break;
                case -180:
                case 180:
                    $gs->translate($bbox->getWidth(), $bbox->getHeight());
                    break;
                case 270:
                case -90:
                    $gs->translate($bbox->getHeight(), 0);
                    break;
            }

            $gs->rotate(0, 0, -$rotation);
        }

        $gs->translate(-$bbox->llx, -$bbox->lly);

        $m = $gs->getCurrentTransformationMatrix();

        $dict->offsetSet('Matrix', new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Numeric($m->getA()),
            new SetaPDF_Core_Type_Numeric($m->getB()),
            new SetaPDF_Core_Type_Numeric($m->getC()),
            new SetaPDF_Core_Type_Numeric($m->getD()),
            new SetaPDF_Core_Type_Numeric($m->getE()),
            new SetaPDF_Core_Type_Numeric($m->getF())
        ]));

        $object = $document->createNewObject($stream);
        
        return SetaPDF_Core_XObject::get($object);
    }
}
