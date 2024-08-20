<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: BorderEffect.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Class representing annotations border effect dictionary
 *
 * See PDF 32000-1:2008 - 12.5.4 Border Styles
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_BorderEffect
{
    /**
     * Border effect
     *
     * @var string
     */
    const NONE = 'S';

    /**
     * Border effect
     *
     * @var string
     */
    const CLOUDY = 'C';

    /**
     * The dictionary
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_Dictionary $dictionary
     */
    public function __construct(SetaPDF_Core_Type_Dictionary $dictionary)
    {
        $this->_dictionary = $dictionary;
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
     * Get the border effect name.
     *
     * @return string
     */
    public function getName()
    {
        return SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'S', self::NONE, true);
    }

    /**
     * Set the border effect name.
     *
     * @param null|string $name
     * @return self
     */
    public function setName($name)
    {
        if ($name === null) {
            $this->_dictionary->offsetUnset('S');
            return null;
        }

        $nameObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'S');
        if ($nameObject instanceof SetaPDF_Core_Type_Name) {
            $nameObject->setValue($name);
        } else {
            $this->_dictionary->offsetSet('S', new SetaPDF_Core_Type_Name($name));
        }

        if ($name === self::NONE) {
            $this->_dictionary->offsetUnset('I');
        }

        return $this;
    }

    /**
     * Get the intensity of the effect.
     *
     * @return int|float
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getIntensity()
    {
        $i = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'I');
        if ($i === null) {
            return 0;
        }

        return SetaPDF_Core_Type_Numeric::ensureType($i)->getValue();
    }

    /**
     * Set the border width.
     *
     * @param null|int|float $intensity
     * @return self
     */
    public function setIntensity($intensity)
    {
        if ($intensity === null) {
            $this->_dictionary->offsetUnset('I');
            return null;
        }

        $iObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'I');
        if ($iObject instanceof SetaPDF_Core_Type_Numeric) {
            $iObject->setValue($intensity);
        } else {
            $this->_dictionary->offsetSet('I', new SetaPDF_Core_Type_Numeric($intensity));
        }

        return $this;
    }
}
