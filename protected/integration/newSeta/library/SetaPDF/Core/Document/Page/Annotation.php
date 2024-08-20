<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Annotation.php 1742 2022-06-20 14:29:40Z jan.slabon $
 */

/**
 * Class representing a PDF annotation
 *
 * See PDF 32000-1:2008 - 12.5
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation
{
    /**
     * Annotation type
     * 
     * @var string
     */
    const TYPE_TEXT            = 'Text';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_LINK            = 'Link';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_FREE_TEXT       = 'FreeText';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_LINE            = 'Line';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_SQUARE          = 'Square';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_CIRCLE          = 'Circle';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_POLYGON         = 'Polygon';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_POLY_LINE       = 'PolyLine';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_HIGHLIGHT       = 'Highlight';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_UNDERLINE       = 'Underline';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_SQUIGGLY        = 'Squiggly';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_STRIKE_OUT      = 'StrikeOut';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_STAMP           = 'Stamp';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_CARET           = 'Caret';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_INK             = 'Ink';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_POPUP           = 'Popup';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_FILE_ATTACHMENT = 'FileAttachment';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_SOUND           = 'Sound';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_MOVIE           = 'Movie';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_WIDGET          = 'Widget';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_SCREEN          = 'Screen';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_PRINTER_MARK    = 'PrinterMark';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_TRAP_NET        = 'TrapNet';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_WATERMARK       = 'Watermark';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_3D              = '3D';

    /**
     * Annotation type
     *
     * @var string
     */
    const TYPE_REDACT          = 'Redact';

    /**
     * The annotation dictionary
     * 
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_annotationDictionary;

    /**
     * @var SetaPDF_Core_Type_IndirectObjectInterface
     */
    protected $_indirectReference;

    /**
     * The rectangle
     *
     * @var SetaPDF_Core_DataStructure_Rectangle
     */
    protected $_rect;

    /**
     * @var SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     */
    protected $_additionalActions;

    /**
     * Creates an annotation dictionary with default values.
     * 
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @param string $subtype
     * @return SetaPDF_Core_Type_Dictionary
     */
    protected static function _createAnnotationDictionary($rect, $subtype)
    {
        if (!($rect instanceof SetaPDF_Core_DataStructure_Rectangle)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $rect = SetaPDF_Core_DataStructure_Rectangle::create($rect);
        }

        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('Type', new SetaPDF_Core_Type_Name('Annot', true));
        $dictionary->offsetSet('Subtype', new SetaPDF_Core_Type_Name($subtype, true));
        $dictionary->offsetSet('Rect', $rect->getValue());
        
        return $dictionary;
    }

    /**
     * Creates an annotation object by an annotation dictionary or its parent object.
     *
     * @param SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObject|SetaPDF_Core_Type_IndirectReference $objectOrDictionary
     * @return SetaPDF_Core_Document_Page_Annotation
     * @throws SetaPDF_Core_Type_Exception
     * @throws InvalidArgumentException
     */
    public static function byObjectOrDictionary(SetaPDF_Core_Type_AbstractType $objectOrDictionary)
    {
        $dictionary = SetaPDF_Core_Type_Dictionary::ensureType($objectOrDictionary);

        $subtypeValue = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Subtype', null, true);
        if ($subtypeValue === null) {
            throw new InvalidArgumentException('An annotation dictionary needs at least a Subtype entry.');
        }

        switch ($subtypeValue) {
            case self::TYPE_LINK:
            case self::TYPE_POPUP:
            case self::TYPE_TEXT:
            case self::TYPE_SQUARE:
            case self::TYPE_CIRCLE:
            case self::TYPE_STAMP:
            case self::TYPE_WIDGET:
            case self::TYPE_HIGHLIGHT:
            case self::TYPE_UNDERLINE:
            case self::TYPE_SQUIGGLY:
            case self::TYPE_STRIKE_OUT:
            case self::TYPE_INK:
            case self::TYPE_POLYGON:
            case self::TYPE_POLY_LINE:
            case self::TYPE_FILE_ATTACHMENT:
            case self::TYPE_FREE_TEXT:
            case self::TYPE_LINE:
            case self::TYPE_CARET:
            case self::TYPE_SCREEN:
                $className = 'SetaPDF_Core_Document_Page_Annotation_' . $subtypeValue;
                return new $className($objectOrDictionary);

            // Default to Markup until all annotation types are implemented
            case self::TYPE_SOUND:
            case self::TYPE_REDACT:
                return new SetaPDF_Core_Document_Page_Annotation_Markup($objectOrDictionary);

            default:
                return new self($objectOrDictionary);
        }
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObject|SetaPDF_Core_Type_IndirectReference $objectOrDictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct(SetaPDF_Core_Type_AbstractType $objectOrDictionary)
    {
        if ($objectOrDictionary instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            $this->_indirectReference = $objectOrDictionary;
        }

        $dictionary = SetaPDF_Core_Type_Dictionary::ensureType($objectOrDictionary);

        if (!$dictionary->offsetExists('Subtype')) {
            throw new InvalidArgumentException('An annotation dictionary needs a Subtype entry.');
        }
        if (!$dictionary->offsetExists('Rect')) {
            throw new InvalidArgumentException('An annotation dictionary needs a Rect entry.');
        }
        
        $this->_annotationDictionary = $dictionary;
    }

    /**
     * Release memory/cycled references
     */
    public function cleanUp()
    {
        if ($this->_additionalActions !== null) {
            $this->_additionalActions->cleanUp();
            $this->_additionalActions = null;
        }

        $this->_annotationDictionary = null;
        $this->_indirectReference = null;
        $this->_rect = null;
    }

    /**
     * Get the annotation dictionary.
     * 
     * @return SetaPDF_Core_Type_Dictionary
     * @deprecated
     */
    public function getAnnotationDictionary()
    {
        return $this->_annotationDictionary;
    }

    /**
     * Get the annotation dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        return $this->_annotationDictionary;
    }

    /**
     * Get the indirect object of this annotation.
     *
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     */
    public function getIndirectObject(SetaPDF_Core_Document $document = null)
    {
        if ($this->_indirectReference === null && $document !== null) {
            $this->_indirectReference = $document->createNewObject($this->getDictionary());
        }

        return $this->_indirectReference;
    }
    
    /**
     * Get the annotation type specified in the Subtype entry.
     * 
     * @return string
     */
    public function getType()
    {
        return SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Subtype', '', true);
    }
    
    /**
     * Get the rectangle object of this annotation.
     * 
     * @return SetaPDF_Core_DataStructure_Rectangle
     */
    public function getRect()
    {
        if ($this->_rect === null) {
            $this->_rect = new SetaPDF_Core_DataStructure_Rectangle(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Rect')
            );
        }

        return $this->_rect;
    }

    /**
     * Set the rectangle object.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle $rect
     */
    public function setRect(SetaPDF_Core_DataStructure_Rectangle $rect)
    {
        $this->_rect = $rect;
        $this->getDictionary()->offsetSet('Rect', $rect->getValue());
    }

    /**
     * Get the height of the annotation.
     *
     * @return float|int
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getHeight()
    {
        return $this->getRect()->getHeight();
    }

    /**
     * Get the width of the annotation.
     *
     * @return float|int
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getWidth()
    {
        return $this->getRect()->getWidth();
    }

    /**
     * Get the name of the annotation.
     *
     * @param string $encoding
     * @return string|null
     */
    public function getName($encoding = 'UTF-8')
    {
        $nm = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'NM');
        if (!$nm instanceof SetaPDF_Core_Type_StringValue) {
            return null;
        }

        return SetaPDF_Core_Encoding::convertPdfString($nm->getValue(), $encoding);
    }

    /**
     * Set the name of the annotation.
     *
     * The annotation name, a text string uniquely identifying it among all the annotations on its page.
     *
     * @param string|null $name
     * @param string $encoding
     */
    public function setName($name, $encoding = 'UTF-8')
    {
        $dictionary = $this->getDictionary();
        if ($name === null) {
            $dictionary->offsetUnset('NM');
            return;
        }

        $name = SetaPDF_Core_Encoding::toPdfString($name, $encoding);

        $nm = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'NM');
        if (!$nm instanceof SetaPDF_Core_Type_StringValue) {
            $dictionary->offsetSet('NM', new SetaPDF_Core_Type_String($name));
            return;
        }

        $nm->setValue($name);
    }

    /**
     * Get the contents of the annotation.
     *
     * @param string $encoding
     * @return null|string
     */
    public function getContents($encoding = 'UTF-8')
    {
        $contents = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Contents');
        if (!$contents instanceof SetaPDF_Core_Type_StringValue) {
            return null;
        }

        return SetaPDF_Core_Encoding::convertPdfString($contents->getValue(), $encoding);
    }

    /**
     * Set the contents of the annotation.
     *
     * @param string|null $contents
     * @param string $encoding
     */
    public function setContents($contents, $encoding = 'UTF-8')
    {
        $dictionary = $this->getDictionary();
        if ($contents === null) {
            $dictionary->offsetUnset('Contents');
            return;
        }

        $contents = SetaPDF_Core_Encoding::toPdfString($contents, $encoding);
        $contentsObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Contents');
        if (!$contentsObject instanceof SetaPDF_Core_Type_StringValue) {
            $dictionary->offsetSet('Contents', new SetaPDF_Core_Type_String($contents));
            return;
        }

        $contentsObject->setValue($contents);
    }

    /**
     * Get the modification date.
     *
     * @param bool $asString
     * @return mixed|null|SetaPDF_Core_DataStructure_Date
     * @throws Exception
     */
    public function getModificationDate($asString = true)
    {
        $m = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'M');
        if (!$m instanceof SetaPDF_Core_Type_StringValue) {
            return null;
        }

        if ($asString === true) {
            return $m->getValue();
        }

        return new SetaPDF_Core_DataStructure_Date($m);
    }

    /**
     * Set the modification date.
     *
     * @param SetaPDF_Core_DataStructure_Date|DateTime|string|bool $date If true is passed, the current date and time
     *                                                                   will be used.
     * @throws Exception
     */
    public function setModificationDate($date = true)
    {
        $dictionary = $this->getDictionary();
        if ($date === null) {
            $dictionary->offsetUnset('M');
            return;
        }

        if (!($date instanceof SetaPDF_Core_DataStructure_Date)) {
            if ($date instanceof DateTime) {
                $date = new SetaPDF_Core_DataStructure_Date($date);
            } else {
                $date = new SetaPDF_Core_DataStructure_Date($date !== true ? $date : null);
            }
        }

        $dictionary->offsetSet('M', $date->getValue());
    }

  /* Annotation Flags (F entry) */
    
    /**
     * Sets an annotation flag.
     *
     * @param integer $flags
     * @param boolean $set Set or unset
     */
    public function setAnnotationFlags($flags, $set = true)
    {
        if ($set === false) {
            $this->unsetAnnotationFlags($flags);
            return;
        }
    
        $dict = $this->getDictionary();

        $f = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'F');
        if ($f instanceof SetaPDF_Core_Type_Numeric) {
            $f->setValue($f->getValue() | $flags);
    
        } else {
            $dict->offsetSet('F', new SetaPDF_Core_Type_Numeric($flags));
        }
    }
    
    /**
     * Removes a field flag.
     *
     * @param integer $flags
     */
    public function unsetAnnotationFlags($flags)
    {
        $dict = $this->getDictionary();

        $f = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'F');
        if ($f instanceof SetaPDF_Core_Type_Numeric) {
            $value = $f->getValue() & ~$flags;
            if ($value) {
                $f->setValue($value);
            } else {
                $dict->offsetUnset('F');
            }
        }
    }
    
    /**
     * Checks if a specific annotation flag is set.
     *
     * @param integer $flag
     * @return boolean
     */
    public function isAnnotationFlagSet($flag)
    {
        $dictionary = $this->getDictionary();
        $f = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'F');
        if (!$f instanceof SetaPDF_Core_Type_Numeric) {
            return false;
        }
        
        return ($f->getValue() & $flag) !== 0;
    }
    
    /**
     * Checks for the "Invisible" flag.
     * 
     * PDF 32000-1:2008 - Table 165:
     * "If set, do not display the annotation if it does not belong to one of the
     * standard annotation types and no annotation handler is available. If clear,
     * display such an unknown annotation using an appearance stream specified by
     * its appearance dictionary, if any"
     * 
     * @return boolean
     */
    public function getInvisibleFlag()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::INVISIBLE);
    }
    
    /**
     * Set the "Invisible" flag.
     * 
     * @param boolean $invisible
     * @see getInvisibleFlag()
     */
    public function setInvisibleFlag($invisible = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::INVISIBLE, $invisible);
    }
    
    /**
     * Checks for the "Hidden" flag.
     *
     * PDF 32000-1:2008 - Table 165:
     * "If set, do not display or print the annotation or allow it to interact with
     * the user, regardless of its annotation type or whether an annotation handler
     * is available."
     * 
     * @return boolean
     */
    public function getHiddenFlag()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::HIDDEN);
    }
    
    /**
     * Set the "Hidden" flag.
     * 
     * @param boolean $hidden
     * @see getHiddenFlag()
     */
    public function setHiddenFlag($hidden = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::HIDDEN, $hidden);
    }
    
    /**
     * Checks for the "Print" flag.
     * 
     * PDF 32000-1:2008 - Table 165:
     * "If set, print the annotation when the page is printed. If clear, never print
     * the annotation, regardless of whether it is displayed on the screen."
     *
     * @return boolean
     */
    public function getPrintFlag()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::PRINTS);
    }
    
    /**
     * Set the "Print" flag.
     * 
     * @param boolean $print
     * @see getPrintFlag()
     */
    public function setPrintFlag($print = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::PRINTS, $print);
    }
    
    /**
     * Checks fo the "NoZoom" flag.
     * 
     * PDF 32000-1:2008 - Table 165:
     * "If set, do not scale the annotation’s appearance to match the magnification
     * of the page. The location of the annotation on the page (defined by the upper-
     * left corner of its annotation rectangle) shall remain fixed, regardless of the
     * page magnification."
     * 
     * @return boolean
     */
    public function getNoZoomFlag()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::NO_ZOOM);
    }
    
    /**
     * Set the "NoZoom" flag.
     * 
     * @param boolean $noZoom
     * @see getNoZoomFlag()
     */
    public function setNoZoomFlag($noZoom = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::NO_ZOOM, $noZoom);
    }
    
    /**
     * Checks fo the "NoRotate" flag.
     * 
     * PDF 32000-1:2008 - Table 165:
     * "If set, do not rotate the annotation’s appearance to match the rotation of the
     * page. The upper-left corner of the annotation rectangle shall remain in a fixed
     * location on the page, regardless of the page rotation."
     * 
     * @return boolean
     */
    public function getNoRotateFlag()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::NO_ROTATE);
    }
    
    /**
     * Set the "NoRotate" flag.
     * 
     * @param boolean $noRotate
     * @see getNoRotateFlag()
     */
    public function setNoRotateFlag($noRotate = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::NO_ROTATE, $noRotate);
    }
    
    /**
     * Checks for the "NoView" flag.
     * 
     * PDF 32000-1:2008 - Table 165:
     * "If set, do not display the annotation on the screen or allow it to interact
     * with the user. The annotation may be printed (depending on the setting of the
     * Print flag) but should be considered hidden for purposes of on-screen display
     * and user interaction."
     * 
     * @return boolean
     */
    public function getNoViewFlag()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::NO_VIEW);
    }
    
    /**
     * Set the "NoView" flag.
     * 
     * @param boolean $noView
     * @see getNoViewFlag()
     */
    public function setNoViewFlag($noView = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::NO_VIEW, $noView);
    }
    
    /**
     * Checks the "ReadOnly" flag.
     * 
     * PDF 32000-1:2008 - Table 165:
     * "If set, do not allow the annotation to interact with the user. The annotation
     * may be displayed or printed (depending on the settings of the NoView and Print
     * flags) but should not respond to mouse clicks or change its appearance in
     * response to mouse motions.
     * 
     * This flag shall be ignored for widget annotations; its function is subsumed by
     * the ReadOnly flag of the associated form field"
     * 
     * @return boolean
     */
    public function getReadOnlyFlag()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::READ_ONLY);
    }
    
    /**
     * Set the "ReadOnly" flag.
     * 
     * @param boolean $readOnly
     * @see getReadOnlyFlag()
     */
    public function setReadOnlyFlag($readOnly = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::READ_ONLY, $readOnly);
    }
    
    /**
     * Checks the "Locked" flag.
     *
     * PDF 32000-1:2008 - Table 165:
     * "If set, do not allow the annotation to be deleted or its properties (including
     * position and size) to be modified by the user. However, this flag does not
     * restrict changes to the annotation’s contents, such as the value of a form field."
     * 
     * @return boolean
     */
    public function getLockedFlag()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::LOCKED);
    }
    
    /**
     * Set the "Locked" flag.
     * 
     * @param boolean $locked
     * @see getLockedFlag()
     */
    public function setLocked($locked = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::LOCKED, $locked);
    }
    
    /**
     * Checks for the "ToggleNoView" flag.
     * 
     * PDF 32000-1:2008 - Table 165:
     * "If set, invert the interpretation of the NoView flag for certain events."
     * 
     * @return boolean
     */
    public function getToggleNoView()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::TOGGLE_NO_VIEW);
    }
    
    /**
     * Set the "ToggleNoView" flag.
     *
     * @param boolean $toggleNoView
     * @see getToggleNoView()
     */
    public function setToggleNoView($toggleNoView = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::TOGGLE_NO_VIEW, $toggleNoView);
    }
    
    /**
     * Checks for the "LockedContents" flag.
     *
     * PDF 32000-1:2008 - Table 165:
     * "If set, do not allow the contents of the annotation to be modified by the
     * user. This flag does not restrict deletion of the annotation or changes to
     * other annotation properties, such as position and size."
     *
     * @return boolean
     */
    public function getLockedContents()
    {
        return $this->isAnnotationFlagSet(SetaPDF_Core_Document_Page_Annotation_Flags::LOCKED_CONTENTS);
    }
    
    /**
     * Set the "LockedContents" flag.
     *
     * @param boolean $lockedContents
     * @see getLockedContents()
     */
    public function setLockedContents($lockedContents = true)
    {
        $this->setAnnotationFlags(SetaPDF_Core_Document_Page_Annotation_Flags::LOCKED_CONTENTS, $lockedContents);
    }

    /**
     * Set the color of the annotation.
     *
     * @param null|bool|int|float|string|array|SetaPDF_Core_DataStructure_Color $color
     */
    public function setColor($color)
    {
        $dictionary = $this->getDictionary();
        if ($color === null) {
            $dictionary->offsetUnset('C');
            return;
        }

        if (!$color instanceof SetaPDF_Core_DataStructure_Color) {
            $color = SetaPDF_Core_DataStructure_Color::createByComponents($color);
        }

        $dictionary->offsetSet('C', $color->getValue());
    }

    /**
     * Get the color of the annotation.
     *
     * @return null|SetaPDF_Core_DataStructure_Color
     */
    public function getColor()
    {
        $c = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'C');
        if ($c === null) {
            return null;
        }

        return SetaPDF_Core_DataStructure_Color::createByComponents($c);
    }

    /**
     * Get the annotation appearance stream.
     *
     * @param string $type
     * @param null|string $subName
     * @return null|SetaPDF_Core_XObject_Form
     *@throws InvalidArgumentException
     */
    public function getAppearance($type = 'N', $subName = null)
    {
        $ap = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'AP');
        if (!$ap instanceof SetaPDF_Core_Type_Dictionary) {
            return null;
        }

        $apType = SetaPDF_Core_Type_Dictionary_Helper::getValue($ap, $type);
        if (!$apType) {
            return null;
        }

        if ($apType instanceof SetaPDF_Core_Type_Stream) {
            if ($subName !== null) {
                throw new InvalidArgumentException('This appearance has no subdictionary. Parameter $subType cannot be used.');
            }

            return new SetaPDF_Core_XObject_Form($ap->getValue($type));
        }

        if ($subName === null) {
            throw new InvalidArgumentException('This appearance has a subdictionary and requires a subtype to be defined.');
        }

        if (!$apType instanceof SetaPDF_Core_Type_Dictionary || !$apType->offsetExists($subName)) {
            return null;
        }

        if (!SetaPDF_Core_Type_Dictionary_Helper::getValue($apType, $subName)) {
            return null;
        }

        return new SetaPDF_Core_XObject_Form($apType->getValue($subName));
    }

    /**
     * Set the annotation appearance stream.
     *
     * @param SetaPDF_Core_XObject_Form $xObject
     * @param string $type
     * @param string|null $subState
     */
    public function setAppearance(SetaPDF_Core_XObject_Form $xObject, $type = 'N', $subState = null)
    {
        $dictionary = $this->getDictionary();
        $ap = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'AP');
        if (!$ap instanceof SetaPDF_Core_Type_Dictionary) {
            $ap = new SetaPDF_Core_Type_Dictionary();
            $dictionary->offsetSet('AP', $ap);
        }

        if ($subState !== null) {
            $sub = SetaPDF_Core_Type_Dictionary_Helper::getValue($ap, $type);
            if (!$sub instanceof SetaPDF_Core_Type_Dictionary) {
                $sub = new SetaPDF_Core_Type_Dictionary();
                $ap->offsetSet($type, $sub);
            }
            $sub->offsetSet($subState, $xObject->getIndirectObject());

        } else {
            $ap->offsetSet($type, $xObject->getIndirectObject());
        }
    }

    /**
     * Gets the additional actions object instance for this annotation.
     *
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     */
    public function getAdditionalActions()
    {
        if ($this->_additionalActions === null) {
            $this->_additionalActions = new SetaPDF_Core_Document_Page_Annotation_AdditionalActions($this);
        }

        return $this->_additionalActions;
    }
}
