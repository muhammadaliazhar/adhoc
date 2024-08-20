<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Popup.php 1789 2022-11-09 11:19:00Z jan.slabon $
 */

/**
 * Class representing a Pop-up annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.14
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Popup
    extends SetaPDF_Core_Document_Page_Annotation
{
    /**
     * Creates an annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createAnnotationDictionary($rect)
    {
        return parent::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_POPUP);
    }

    /**
     * The constructor.
     *
     * @param array|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface $objectOrDictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct($objectOrDictionary)
    {
        $dictionary = $objectOrDictionary instanceof SetaPDF_Core_Type_AbstractType
            ? $objectOrDictionary->ensure(true)
            : $objectOrDictionary;

        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            $args = func_get_args();
            $objectOrDictionary = $dictionary = SetaPDF_Core_Type_Dictionary::ensureType(call_user_func_array(
                [self::class, 'createAnnotationDictionary'],
                $args
            ));
            unset($args);
        }

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Popup')) {
            throw new InvalidArgumentException('The Subtype entry in a Pop-up annotation shall be "Popup".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Checks if the popup is open.
     *
     * @return boolean
     */
    public function isOpen()
    {
        $open = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Open');
        if (!$open instanceof SetaPDF_Core_Type_Boolean) {
            return false;
        }

        return $open->getValue();
    }

    /**
     * Set the open flag of the popup.
     *
     * @param boolean $open
     */
    public function setOpen($open)
    {
        $dict = $this->getDictionary();
        if (!$open) {
            $dict->offsetUnset('Open');
            return;
        }

        $openObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Open');
        if (!$openObject instanceof SetaPDF_Core_Type_Boolean) {
            $dict->offsetSet('Open', new SetaPDF_Core_Type_Boolean($open));
            return;
        }

        $openObject->setValue($open);
    }

    /**
     * Get the parent annotation.
     *
     * @return null|SetaPDF_Core_Document_Page_Annotation
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getParent()
    {
        $parent = $this->getDictionary()->getValue('Parent');
        if ($parent === null) {
            return null;
        }

        return SetaPDF_Core_Document_Page_Annotation::byObjectOrDictionary($parent);
    }

    /**
     * Set the parent annotation.
     *
     * @param SetaPDF_Core_Document_Page_Annotation $annotation
     * @throws InvalidArgumentException
     */
    public function setParent(SetaPDF_Core_Document_Page_Annotation $annotation)
    {
        $object = $annotation->getIndirectObject();
        if (!$object instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            throw new InvalidArgumentException(
                'Using an annotation object as a parent of another requires that ' .
                'the parent annotation is attached to an indirect object.'
            );
        }

        $this->getDictionary()->offsetSet('Parent', $object);
    }
}
