<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Separation.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * Lab Color Space
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage ColorSpace
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_ColorSpace_Lab
    extends SetaPDF_Core_ColorSpace_Resource
{
    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_AbstractType $definition
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Exception
     */
    public function __construct(SetaPDF_Core_Type_AbstractType $definition)
    {
        parent::__construct($definition);

        if (!$this->_value instanceof SetaPDF_Core_Type_Array) {
            throw  new InvalidArgumentException('Lab space needs to be defined by an array of 2 values.');
        }

        if ($this->getFamily() !== 'Lab') {
            throw new InvalidArgumentException('Indexed color space has to be named "Lab".');
        }

        if ($this->getPdfValue()->count() !== 2) {
            throw new InvalidArgumentException('Indexed color spaces definition has to be defined by 2 values.');
        }
    }

    /**
     * @return SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_Type_Exception
     */
    private function _getDictionary()
    {
        $dict = $this->getPdfValue()->offsetGet(1);
        return SetaPDF_Core_Type_Dictionary::ensureType($dict);
    }

    /**
     * Get the WhitePoint value.
     *
     * @return array
     * @throws SetaPDF_Core_Exception
     */
    public function getWhitePoint()
    {
        $dict = $this->_getDictionary();

        $whitePoint = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'WhitePoint', false);
        if ($whitePoint === false) {
            throw new SetaPDF_Core_Exception('WhitePoint is missing in Lab color space dictionary.');
        }

        return SetaPDF_Core_Type_Array::ensureType($whitePoint)->toPhp(true);
    }

    /**
     * Set the WhitePoint values.
     *
     * @param int|float|int[] $x
     * @param int|float $y
     * @param int|float $z
     * @return void
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setWhitePoint($x, $y = null, $z = null)
    {
        if (is_array($x)) {
            list($x, $y, $z) = $x;
        }

        $dict = $this->_getDictionary();

        $whitePoint = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'WhitePoint', false);
        if ($whitePoint === false) {
            throw new SetaPDF_Core_Exception('WhitePoint is missing in Lab color space dictionary.');
        }

        $whitePoint[0] = new SetaPDF_Core_Type_Numeric($x);
        $whitePoint[1] = new SetaPDF_Core_Type_Numeric($y);
        $whitePoint[2] = new SetaPDF_Core_Type_Numeric($z);
    }

    /**
     * Get the BlackPoint values.
     *
     * @return array|float[]
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getBlackPoint()
    {
        $dict = $this->_getDictionary();

        $blackPoint = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'BlackPoint', false);
        if ($blackPoint === false) {
            return [.0, .0, .0];
        }

        return SetaPDF_Core_Type_Array::ensureType($blackPoint)->toPhp(true);
    }

    /**
     * Set the BlackPoint value.
     *
     * @param int|float|int[] $x
     * @param int|float $y
     * @param int|float $z
     * @return void
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setBlackPoint($x, $y = null, $z = null)
    {
        if (is_array($x)) {
            list($x, $y, $z) = $x;
        }

        $dict = $this->_getDictionary();

        $blackPoint = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'BlackPoint', false);
        if ($blackPoint === false) {
            $dict->offsetSet('BlackPoint', new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric($x),
                new SetaPDF_Core_Type_Numeric($y),
                new SetaPDF_Core_Type_Numeric($z)
            ]));

            return;
        }

        $blackPoint[0] = new SetaPDF_Core_Type_Numeric($x);
        $blackPoint[1] = new SetaPDF_Core_Type_Numeric($y);
        $blackPoint[2] = new SetaPDF_Core_Type_Numeric($z);
    }

    /**
     * Get the Range values.
     *
     * @return array|int[]
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getRange()
    {
        $dict = $this->_getDictionary();

        $range = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Range', false);
        if ($range === false) {
            return [-100, 100, -100, 100];
        }

        return SetaPDF_Core_Type_Array::ensureType($range, 4)->toPhp(true);
    }

    /**
     * Set the Range values.
     *
     * @param int|float|int[] $aMin
     * @param int|float $aMax
     * @param int|float $bMin
     * @param int|float $bMax
     * @return void
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setRange($aMin, $aMax = null, $bMin = null, $bMax = null)
    {
        if (is_array($aMin)) {
            list($aMin, $aMax, $bMin, $bMax) = $aMin;
        }

        $dict = $this->_getDictionary();

        $range = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Range', false);
        if (!$range instanceof SetaPDF_Core_Type_Array || $range->count() !== 4) {
            $dict->offsetSet('Range', new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric($aMin),
                new SetaPDF_Core_Type_Numeric($aMax),
                new SetaPDF_Core_Type_Numeric($bMin),
                new SetaPDF_Core_Type_Numeric($bMax)
            ]));

            return;
        }

        $range[0] = new SetaPDF_Core_Type_Numeric($aMin);
        $range[1] = new SetaPDF_Core_Type_Numeric($aMax);
        $range[2] = new SetaPDF_Core_Type_Numeric($bMin);
        $range[3] = new SetaPDF_Core_Type_Numeric($bMax);
    }
}
