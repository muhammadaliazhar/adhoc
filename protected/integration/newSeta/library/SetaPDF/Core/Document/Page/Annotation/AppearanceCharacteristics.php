<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: AppearanceCharacteristics.php 1715 2022-05-06 10:18:19Z jan.slabon $
 */

/**
 * Class representing annotations appearance characteristics
 *
 * See PDF 32000-1:2008 - 12.5.6.19 Widget Annotations
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_AppearanceCharacteristics
{
    /**
     * The dictionary
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * @var SetaPDF_Core_Type_IndirectObjectInterface
     */
    protected $_indirectReference;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_AbstractType $objectOrDictionary
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct(SetaPDF_Core_Type_AbstractType $objectOrDictionary)
    {
        if ($objectOrDictionary instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            $this->_indirectReference = $objectOrDictionary;
        }

        $this->_dictionary = SetaPDF_Core_Type_Dictionary::ensureType($objectOrDictionary);
    }

    /**
     * Get the dictionary of it.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        return $this->_dictionary;
    }

    /**
     * Get the rotation value.
     *
     * @return int|float
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getRotation()
    {
        $rotation = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'R', new SetaPDF_Core_Type_Numeric(0));
        return SetaPDF_Core_Type_Numeric::ensureType($rotation)->getValue();
    }

    /**
     * Set the rotation value.
     *
     * @param null|int|float $rotation
     * @return self
     */
    public function setRotation($rotation)
    {
        if ($rotation === null) {
            $this->_dictionary->offsetUnset('R');
            return null;
        }

        $rotationObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'R');
        if ($rotationObject instanceof SetaPDF_Core_Type_Numeric) {
            $rotationObject->setValue($rotation);
        } else {
            $this->_dictionary->offsetSet('R', new SetaPDF_Core_Type_Numeric($rotation));
        }

        return $this;
    }

    /**
     * Get the border color.
     *
     * @return null|SetaPDF_Core_DataStructure_Color
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getBorderColor()
    {
        $bc = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'BC');
        if ($bc === null) {
            return null;
        }

        $bc = SetaPDF_Core_Type_Array::ensureType($bc);
        if ($bc->count() === 0) {
            return null;
        }

        return SetaPDF_Core_DataStructure_Color::createByComponents($bc);
    }

    /**
     * Set the border color.
     *
     * @param null|array|int|float|SetaPDF_Core_DataStructure_Color $borderColor
     * @return self
     */
    public function setBorderColor($borderColor)
    {
        if ($borderColor === null) {
            $this->_dictionary->offsetUnset('BC');
            return null;
        }

        if (!$borderColor instanceof SetaPDF_Core_DataStructure_Color) {
            $borderColor = SetaPDF_Core_DataStructure_Color::createByComponents($borderColor);
        }

        $this->_dictionary->offsetSet('BC', $borderColor->getValue());

        return $this;
    }

    /**
     * Get the background color.
     *
     * @return null|SetaPDF_Core_DataStructure_Color
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getBackgroundColor()
    {
        $bg = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'BG');
        if ($bg === null) {
            return null;
        }

        $bg = SetaPDF_Core_Type_Array::ensureType($bg);
        if ($bg->count() === 0) {
            return null;
        }

        return SetaPDF_Core_DataStructure_Color::createByComponents($bg);
    }

    /**
     * Set the background color.
     *
     * @param null|array|int|float|SetaPDF_Core_DataStructure_Color $backgroundColor
     * @return self
     */
    public function setBackgroundColor($backgroundColor)
    {
        if ($backgroundColor === null) {
            $this->_dictionary->offsetUnset('BG');
            return null;
        }

        if (!$backgroundColor instanceof SetaPDF_Core_DataStructure_Color) {
            $backgroundColor = SetaPDF_Core_DataStructure_Color::createByComponents($backgroundColor);
        }

        $this->_dictionary->offsetSet('BG', $backgroundColor->getValue());

        return $this;
    }
}
