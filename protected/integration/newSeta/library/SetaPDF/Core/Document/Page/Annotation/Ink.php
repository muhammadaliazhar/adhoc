<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Ink.php 1789 2022-11-09 11:19:00Z jan.slabon $
 */

/**
 * Class representing an ink annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.13
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Ink
    extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    /**
     * @var SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    protected $_borderStyle;

    /**
     * Creates an ink annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createAnnotationDictionary($rect)
    {
        $dictionary = parent::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_INK);
        $dictionary->offsetSet('InkList', new SetaPDF_Core_Type_Array());

        return $dictionary;
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

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Ink')) {
            throw new InvalidArgumentException('The Subtype entry in an ink annotation shall be "Ink".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Release memory/cycled references.
     */
    public function cleanUp()
    {
        parent::cleanUp();

        if ($this->_borderStyle !== null) {
            $this->_borderStyle->cleanUp();
            $this->_borderStyle = null;
        }
    }

    /**
     * Add an ink list.
     *
     * @param float[] $inkList
     */
    public function addInkList(array $inkList)
    {
        $dict = $this->getDictionary();
        $inkList = array_map(static function($value) {
            return new SetaPDF_Core_Type_Numeric($value);
        }, $inkList);

        $inkLists = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'InkList');
        if (!$inkLists instanceof SetaPDF_Core_Type_Array) {
            $inkLists = new SetaPDF_Core_Type_Array();
            $dict->offsetSet('InkList', $inkLists);
        }

        $inkLists[] = new SetaPDF_Core_Type_Array($inkList);
    }

    /**
     * Get an ink list at a specific index.
     *
     * @param int $index
     * @return null|float[]
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getInkList($index)
    {
        $inkLists = SetaPDF_Core_Type_Array::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'InkList')
        );

        $inkList = $inkLists->offsetGet($index);

        return $inkList === null
            ? null
            : SetaPDF_Core_Type_Array::ensureType($inkList)->toPhp(true);
    }

    /**
     * Set ink lists.
     *
     * @param array[] $inkLists
     */
    public function setInkLists(array $inkLists)
    {
        $dict = $this->getDictionary();
        $_inkLists = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'InkList');
        if (!$_inkLists instanceof SetaPDF_Core_Type_Array) {
            $_inkLists = new SetaPDF_Core_Type_Array();
            $dict->offsetSet('InkList', $_inkLists);
        }

        $_inkLists->clear();

        foreach ($inkLists as $inkList) {
            if (!is_array($inkList)) {
                throw new InvalidArgumentException('Parameter needs to be an array of float/numeric arrays.');
            }

            $this->addInkList($inkList);
        }
    }

    /**
     * Get all ink lists.
     *
     * @return array[]
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getInkLists()
    {
        $inkList = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'InkList');
        if (!$inkList instanceof SetaPDF_Core_Type_Array) {
            return [];
        }

        return SetaPDF_Core_Type_Array::ensureType($inkList)->toPhp(true);
    }

    /**
     * Get the border style object.
     *
     * @return SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    public function getBorderStyle()
    {
        if ($this->_borderStyle === null) {
            $this->_borderStyle = new SetaPDF_Core_Document_Page_Annotation_BorderStyle($this);
        }

        return $this->_borderStyle;
    }
}
