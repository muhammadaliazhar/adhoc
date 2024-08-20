<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Polygon.php 1789 2022-11-09 11:19:00Z jan.slabon $
 */

/**
 * Class representing a polygon annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.13
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Polygon
    extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    /**
     * @var SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    protected $_borderStyle;

    /**
     * Creates a polygon annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createAnnotationDictionary($rect)
    {
        $dictionary = parent::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_POLYGON);
        $dictionary->offsetSet('Vertices', new SetaPDF_Core_Type_Array());

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

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Polygon')) {
            throw new InvalidArgumentException('The Subtype entry in an polygon annotation shall be "Polygon".');
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
     * Set the vertices.
     *
     * @param float[] $vertices
     */
    public function setVertices(array $vertices)
    {
        $dict = $this->getDictionary();
        $_vertices = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Vertices');
        if (!$_vertices instanceof SetaPDF_Core_Type_Array) {
            $_vertices = new SetaPDF_Core_Type_Array();
            $dict->offsetSet('Vertices', $_vertices);
        }

        $_vertices->clear();

        foreach ($vertices AS $value) {
            $_vertices[] = new SetaPDF_Core_Type_Numeric($value);
        }
    }

    /**
     * Get the vertices.
     *
     * @return array
     */
    public function getVertices()
    {
        $vertices = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Vertices');
        if (!$vertices instanceof SetaPDF_Core_Type_Array) {
            return [];
        }

        return $vertices->toPhp(true);
    }

    /**
     * Set the interior color.
     *
     * @param null|int|array|SetaPDF_Core_DataStructure_Color $color
     */
    public function setInteriorColor($color)
    {
        $dict = $this->getDictionary();
        if ($color === null) {
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
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getBorderEffect($create = false)
    {
        if (get_class($this) !== 'SetaPDF_Core_Document_Page_Annotation_Polygon') {
            throw new BadMethodCallException('This method is only useable for polygon annotations.');
        }

        $dict = $this->getDictionary();
        $be = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'BE');
        if ($be === null) {
            if ($create === false) {
                return null;
            }

            $be = new SetaPDF_Core_Type_Dictionary();
            $dict->offsetSet('BE', $be);
        }

        return new SetaPDF_Core_Document_Page_Annotation_BorderEffect(SetaPDF_Core_Type_Dictionary::ensureType($be));
    }

    /**
     * Get the border style object.
     *
     * @return null|SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    public function getBorderStyle()
    {
        if ($this->_borderStyle === null) {
            $this->_borderStyle = new SetaPDF_Core_Document_Page_Annotation_BorderStyle($this);
        }

        return $this->_borderStyle;
    }
}
