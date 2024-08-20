<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: DeviceN.php 1776 2022-09-16 09:37:36Z jan.slabon $
 */

/**
 * DeviceN Color Space
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage ColorSpace
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_ColorSpace_DeviceN
    extends SetaPDF_Core_ColorSpace_Resource
{
    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_AbstractType $definition
     * @throws InvalidArgumentException|SetaPDF_Core_Exception
     */
    public function __construct(SetaPDF_Core_Type_AbstractType $definition)
    {
        parent::__construct($definition);

        if (!$this->_value instanceof SetaPDF_Core_Type_Array) {
            throw  new InvalidArgumentException('DeviceN color space needs to be defined by an array of at least 4 values.');
        }

        if ($this->getFamily() !== 'DeviceN') {
            throw new InvalidArgumentException('DeviceN color space has to be named "DeviceN".');
        }

        if ($this->getPdfValue()->count() < 4) {
            throw new InvalidArgumentException('DeviceN color spaces definition has to be defined by at least 4 values.');
        }
    }

    /**
     * Get the names specifying the individual color components.
     *
     * @return array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getNames()
    {
        $namesObject = $this->getPdfValue()->offsetGet(1);
        return SetaPDF_Core_Type_Array::ensureType($namesObject)->toPhp(true);
    }

    /**
     * Set the names specifying the individual color components.
     *
     * @param array $names
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setNames(array $names)
    {
        $namesObject = $this->getPdfValue()->offsetGet(1);
        $value = SetaPDF_Core_Type_Array::ensureType($namesObject);
        $value->clear();

        foreach ($names AS $name) {
            if (!$name instanceof SetaPDF_Core_Type_Name) {
               $name = new SetaPDF_Core_Type_Name($name);
            }

            $value->push($name);
        }
    }

    /**
     * Get the alternate color space.
     *
     * @return SetaPDF_Core_ColorSpace|SetaPDF_Core_ColorSpace_DeviceCmyk|SetaPDF_Core_ColorSpace_DeviceGray|SetaPDF_Core_ColorSpace_DeviceRgb|SetaPDF_Core_ColorSpace_IccBased|SetaPDF_Core_ColorSpace_Separation
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAlternateColorSpace()
    {
        /** @var SetaPDF_Core_Type_Array|SetaPDF_Core_Type_Name $alternate */
        $alternate = $this->getPdfValue()->offsetGet(2);

        return SetaPDF_Core_ColorSpace::createByDefinition($alternate);
    }

    /**
     * Alias for getAlternateColorSpace()
     *
     * @return SetaPDF_Core_ColorSpace|SetaPDF_Core_ColorSpace_DeviceCmyk|SetaPDF_Core_ColorSpace_DeviceGray|SetaPDF_Core_ColorSpace_DeviceRgb|SetaPDF_Core_ColorSpace_IccBased|SetaPDF_Core_ColorSpace_Separation
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @deprecated
     */
    public function getAlternateSpace()
    {
        return $this->getAlternateColorSpace();
    }

    /**
     * Set the alternate color space.
     *
     * @param SetaPDF_Core_ColorSpace $colorSpace
     */
    public function setAlternateColorSpace(SetaPDF_Core_ColorSpace $colorSpace)
    {
        $value = $this->getPdfValue();
        if ($colorSpace instanceof SetaPDF_Core_Resource) {
            $value->offsetSet(2, $colorSpace->getIndirectObject());
            return;
        }

        $value->offsetSet(2, $colorSpace->getPdfValue());
    }

    /**
     * Set the tint transformation function.
     *
     * @param SetaPDF_Core_Type_AbstractType $tintTransform
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setTintTransform(SetaPDF_Core_Type_AbstractType $tintTransform)
    {
        $dict = $tintTransform->ensure();
        if ($dict instanceof SetaPDF_Core_Type_Stream) {
            $dict = $dict->getValue();
        }

        $dict = SetaPDF_Core_Type_Dictionary::ensureType($dict);

        if (!$dict->offsetExists('FunctionType')) {
            throw new InvalidArgumentException('$tintTransformation shall be a PDF function.');
        }

        $this->getPdfValue()->offsetSet(3, $tintTransform);
    }

    /**
     * Get the tint transformation function.
     *
     * @return SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_Stream
     * @throws SetaPDF_Core_Type_IndirectReference_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getTintTransform()
    {
        $value = $this->getPdfValue()->offsetGet(3);
        if ($value !== null) {
            $value = $value->ensure();
        }

        if ($value instanceof SetaPDF_Core_Type_Dictionary || $value instanceof SetaPDF_Core_Type_Stream) {
            return $value;
        }

        throw new SetaPDF_Core_Type_Exception('Invalid data type for tint transform parameters.');
    }

    /**
     * Get the default decode array of this color space.
     *
     * @return array
     * @throws SetaPDF_Core_Exception
     */
    public function getDefaultDecodeArray()
    {
        $result = [];
        for ($i = 0; $i < $this->getColorComponents(); $i++) {
            $result[] = 0.;
            $result[] = 1.;
        }

        return $result;
    }
}
