<?php 
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Annotations.php 1742 2022-06-20 14:29:40Z jan.slabon $
 */

/**
 * Helper class for handling annotations of a page
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotations
{
    /**
     * Constant specifying the row tab order
     *
     * @var string
     */
    const TAB_ORDER_ROW = 'R';

    /**
     * Constant specifying the column tab order
     *
     * @var string
     */
    const TAB_ORDER_COLUMN = 'C';

    /**
     * Constant specifying the structure tab order
     *
     * @var string
     */
    const TAB_ORDER_STRUCTURE = 'S';

    /**
     * Constant specifying the annotations array tab order
     *
     * @var string
     */
    const TAB_ORDER_ANNOTATIONS_ARRAY = 'A';

    /**
     * Constant specifying the widget tab order
     *
     * @var string
     */
    const TAB_ORDER_WIDGET = 'W';

    /**
     * The page object
     * 
     * @var SetaPDF_Core_Document_Page
     */
    protected $_page;
    
    /**
     * The constructor.
     * 
     * @param SetaPDF_Core_Document_Page $page
     */
    public function __construct(SetaPDF_Core_Document_Page $page)
    {
        $this->_page = $page;
    }

    /**
     * Release memory/resources.
     */
    public function cleanUp()
    {
        $this->_page = null;
    }

    /**
     * Get the page.
     *
     * @return SetaPDF_Core_Document_Page
     */
    public function getPage()
    {
        return $this->_page;
    }

    /**
     * Returns the Annots array if available or creates a new one.
     *
     * @param boolean $create
     * @return false|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getArray($create = false)
    {
        $pageDict = SetaPDF_Core_Type_Dictionary::ensureType($this->_page->getPageObject(true)->ensure(true));
        $annots = SetaPDF_Core_Type_Dictionary_Helper::getValue($pageDict, 'Annots');

        if (!$annots instanceof SetaPDF_Core_Type_Array) {
            if ($create) {
                $annots = new SetaPDF_Core_Type_Array();
                $pageDict->offsetSet('Annots', $annots);
            } else {
                return false;
            }
        }

        return $annots;
    }

    /**
     * Get all annotations of this page.
     *
     * Optionally the results can be filtered by the subtype parameter.
     *
     * @param string $subtype See {@link SetaPDF_Core_Document_Page_Annotation::TYPE_*} constants for possible values.
     * @return SetaPDF_Core_Document_Page_Annotation[]
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAll($subtype = null)
    {
        $annotations = [];
        $annotationsArray = $this->getArray();
        if ($annotationsArray === false) {
            return $annotations;
        }
    
        foreach ($annotationsArray AS $annotationValue) {
            try {
                $annotationDictionary = SetaPDF_Core_Type_Dictionary::ensureType($annotationValue);
            } catch (SetaPDF_Core_Type_Exception $e) {
                continue;
            }

            if (
                $subtype === null ||
                SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($annotationDictionary, 'Subtype', $subtype)
            ) {
                try {
                    $annotation = SetaPDF_Core_Document_Page_Annotation::byObjectOrDictionary($annotationValue);
                } catch (InvalidArgumentException $e) {
                    continue;
                }

                $annotations[] = $annotation;
            }
        }
    
        return $annotations;
    }

    /**
     * Get an annotation by its name (NM entry)
     *
     * @param string $name The name of the annotation.
     * @param string $encoding
     *
     * @return bool|SetaPDF_Core_Document_Page_Annotation
     * @throws SetaPDF_Core_Document_ObjectNotFoundException
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Type_IndirectReference_Exception
     */
    public function getByName($name, $encoding = 'UTF-8')
    {
        $annotationsArray = $this->getArray();
        if ($annotationsArray === false) {
            return false;
        }

        foreach ($annotationsArray AS $annotationValue) {
            try {
                $annotationDictionary = SetaPDF_Core_Type_Dictionary::ensureType($annotationValue);
            } catch (SetaPDF_Core_Type_Exception $e) {
                continue;
            }

            $nm = SetaPDF_Core_Type_Dictionary_Helper::getValue($annotationDictionary, 'NM');
            if (!$nm instanceof SetaPDF_Core_Type_StringValue) {
                continue;
            }

            if (SetaPDF_Core_Encoding::convertPdfString($nm->getValue(), $encoding) === $name) {
                return SetaPDF_Core_Document_Page_Annotation::byObjectOrDictionary($annotationValue);
            }
        }

        return false;
    }

    /**
     * Adds an annotation to the page.
     *
     * @param SetaPDF_Core_Document_Page_Annotation $annotation
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws SetaPDF_Core_Type_Exception
     */
    public function add(SetaPDF_Core_Document_Page_Annotation $annotation)
    {
        $annotationsArray = $this->getArray(true);
        $object = $annotation->getIndirectObject();

        $pageObject = $this->getPage()->getPageObject(true);
        if ($object === null) {
            $document = $pageObject->getOwnerPdfDocument();
            /** @var SetaPDF_Core_Type_IndirectObject $object */
            $object = $annotation->getIndirectObject($document);
        }

        $annotation->getDictionary()->offsetSet('P', $pageObject);

        $annotationsArray->offsetSet(null, $object);
        
        return $object;
    }

    /**
     * Removes an annotation from the annotation array of the page.
     *
     * @param SetaPDF_Core_Document_Page_Annotation $annotation
     * @return bool
     * @throws SetaPDF_Core_Type_Exception
     */
    public function remove(SetaPDF_Core_Document_Page_Annotation $annotation)
    {
        $annotationsArray = $this->getArray();
        if ($annotationsArray === false) {
            return false;
        }

        $object = $annotation->getIndirectObject();
        if ($object) {
            $document = $this->_page->getPageObject(true)->getOwnerPdfDocument();
            if ($document->getInstanceIdent() !== $object->getOwnerPdfDocument()->getInstanceIdent()) {
                return false;
            }

            foreach ($annotationsArray AS $key => $annotationValue) {
                if (
                    $annotationValue instanceof SetaPDF_Core_Type_IndirectObjectInterface &&
                    $annotationValue->getObjectIdent() === $object->getObjectIdent()
                ) {
                    $annotationsArray->offsetUnset($key);
                    return true;
                }
            }

        } else {
            $value = $annotation->getDictionary()->toPhp();

            foreach ($annotationsArray AS $key => $annotationValue) {
                if (
                    !$annotationValue instanceof SetaPDF_Core_Type_IndirectObjectInterface &&
                    $annotationValue->toPhp() === $value
                ) {
                    $annotationsArray->offsetUnset($key);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the tab order that shall be used for annotations on the page.
     *
     * @return string|null
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getTabOrder()
    {
        $pageDict = SetaPDF_Core_Type_Dictionary::ensureType($this->_page->getPageObject(true));
        $tabs = SetaPDF_Core_Type_Dictionary_Helper::getValue($pageDict, 'Tabs');
        if (!$tabs instanceof SetaPDF_Core_Type_Name) {
            return null;
        }

        return $tabs->getValue();
    }

    /**
     * Set the tab order that shall be used for annotations on the page.
     *
     * @param string|null $tabOrder
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setTabOrder($tabOrder)
    {
        $pageDict = SetaPDF_Core_Type_Dictionary::ensureType($this->_page->getPageObject(true));

        if ($tabOrder === null) {
            $pageDict->offsetUnset('Tabs');
            return;
        }

        $tabs = SetaPDF_Core_Type_Dictionary_Helper::getValue($pageDict, 'Tabs');
        if (!$tabs instanceof SetaPDF_Core_Type_Name) {
            $tabs = new SetaPDF_Core_Type_Name($tabOrder);
            $pageDict->offsetSet('Tabs', $tabs);
        }

        $tabs->setValue($tabOrder);
    }
}
