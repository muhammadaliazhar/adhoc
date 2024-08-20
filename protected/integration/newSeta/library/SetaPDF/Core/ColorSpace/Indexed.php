<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Indexed.php 1776 2022-09-16 09:37:36Z jan.slabon $
 */

/**
 * Indexed Color Space
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage ColorSpace
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_ColorSpace_Indexed
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
            throw  new InvalidArgumentException('Indexed color space needs to be defined by an array of 4 values.');
        }

        if ($this->getFamily() !== 'Indexed') {
            throw new InvalidArgumentException('Indexed color space has to be named "Indexed".');
        }

        if ($this->getPdfValue()->count() !== 4) {
            throw new InvalidArgumentException('Indexed color spaces definition has to be defined by 4 values.');
        }
    }

    /**
     * Get the base color space.
     *
     * @return SetaPDF_Core_ColorSpace|SetaPDF_Core_ColorSpace_DeviceCmyk|SetaPDF_Core_ColorSpace_DeviceGray|SetaPDF_Core_ColorSpace_DeviceRgb|SetaPDF_Core_ColorSpace_IccBased|SetaPDF_Core_ColorSpace_Separation
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getBase()
    {
        /** @var SetaPDF_Core_Type_Name|SetaPDF_Core_Type_Array $base */
        $base = $this->getPdfValue()->offsetGet(1);

        return SetaPDF_Core_ColorSpace::createByDefinition($base);
    }

    /**
     * Get the maximum valid index value (hival).
     *
     * @return integer
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getHival()
    {
        $hival = $this->getPdfValue()->offsetGet(2);
        return SetaPDF_Core_Type_Numeric::ensureType($hival)->getValue();
    }

    /**
     * Get the lookup table.
     *
     * @return array
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getLookupTable()
    {
        $lookup = $this->getPdfValue()->offsetGet(3);
        if ($lookup !== null) {
            $lookup = $lookup->ensure();
        }

        if ($lookup instanceof SetaPDF_Core_Type_Stream) {
            $lookup = $lookup->getStream();
        } else {
            $lookup = SetaPDF_Core_Type_AbstractType::ensureWithType(
                SetaPDF_Core_Type_StringValue::class, $lookup
            )->getValue();
        }

        $base = $this->getBase();

        $numOfComponents = $base->getColorComponents();
        $table = [];
        for ($i = 0, $a = 0, $len = strlen($lookup); $i < $len; $i += $numOfComponents, $a++) {
            $table[$a] = substr($lookup, $i, $numOfComponents);
            if (strlen($table[$a]) < $numOfComponents) {
                $table[$a] .= str_repeat("\0", $numOfComponents - strlen($table[$a]));
            }
        }

        return $table;
    }

    /**
     * Get the color components of this color space.
     *
     * @return integer
     */
    public function getColorComponents()
    {
        return 1;
    }

    /**
     * Get the default decode array of this color space.
     *
     * @param int $bitsPerComponent
     * @return array
     */
    public function getDefaultDecodeArray($bitsPerComponent = null)
    {
        if ($bitsPerComponent === null) {
            throw new InvalidArgumentException('Missing bitsPerComponent parameter.');
        }

        return [0, ((2 ** $bitsPerComponent) - 1)];
    }
}
