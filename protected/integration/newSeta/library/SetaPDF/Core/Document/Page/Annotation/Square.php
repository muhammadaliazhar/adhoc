<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Square.php 1789 2022-11-09 11:19:00Z jan.slabon $
 */

/**
 * Class representing a square annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.8
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Square
    extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    /**
     * @var SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    protected $_borderStyle;

    /**
     * Creates a square annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createAnnotationDictionary($rect)
    {
        return parent::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_SQUARE);
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

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Square')) {
            throw new InvalidArgumentException('The Subtype entry in a square annotation shall be "Square".');
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
     * Set the interior color.
     *
     * @param null|array|SetaPDF_Core_DataStructure_Color $color
     */
    public function setInteriorColor($color)
    {
        $dict = $this->getDictionary();
        if (!$color) {
            $dict->offsetUnset('IC');
            return;
        }

        if (!$color instanceof SetaPDF_Core_DataStructure_Color) {
            $color = SetaPDF_Core_DataStructure_Color::createByComponents($color);
        }

        $dict->offsetSet('IC', $color->getValue());
    }

    /**
     * Get the interior color.
     *
     * @return null|SetaPDF_Core_DataStructure_Color
     */
    public function getInteriorColor()
    {
        $ic = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'IC');
        if ($ic === null) {
            return null;
        }

        return SetaPDF_Core_DataStructure_Color::createByComponents($ic);
    }

    /**
     * Get the border effect object.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Document_Page_Annotation_BorderEffect
     */
    public function getBorderEffect($create = false)
    {
        $dict = $this->getDictionary();
        $be = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'BE');
        if (!$be instanceof SetaPDF_Core_Type_Dictionary) {
            if ($create === false) {
                return null;
            }

            $be = new SetaPDF_Core_Type_Dictionary();
            $dict->offsetSet('BE', $be);
        }

        return new SetaPDF_Core_Document_Page_Annotation_BorderEffect($be);
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
