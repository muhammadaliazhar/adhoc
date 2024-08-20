<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Separation.php 1409 2020-01-30 14:40:05Z jan.slabon $
 */

/**
 * Lab Color Space
 *
 * @copyright  Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage ColorSpace
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_ColorSpace_Lab
    extends SetaPDF_Core_ColorSpace
    implements SetaPDF_Core_Resource
{
    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_AbstractType $definition
     * @throws InvalidArgumentException
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

    public function getWhitePoint()
    {
        $dict = $this->getPdfValue()->offsetGet(1);

        $whitePoint = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'WhitePoint', false);
        if ($whitePoint === false) {
            throw new SetaPDF_Core_Exception('WhitePoint is missing in Lab color space dictionary.');
        }

        return [
            $whitePoint[0]->ensure()->getValue(),
            $whitePoint[1]->ensure()->getValue(),
            $whitePoint[2]->ensure()->getValue(),
        ];
    }

    public function setWhitePoint($x, $y = null, $z = null)
    {
        if (is_array($x)) {
            list($x, $y, $z) = $x;
        }

        $dict = $this->getPdfValue()->offsetGet(1);

        $whitePoint = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'WhitePoint', false);
        if ($whitePoint === false) {
            throw new SetaPDF_Core_Exception('WhitePoint is missing in Lab color space dictionary.');
        }

        $whitePoint[0] = new SetaPDF_Core_Type_Numeric($x);
        $whitePoint[1] = new SetaPDF_Core_Type_Numeric($y);
        $whitePoint[2] = new SetaPDF_Core_Type_Numeric($z);
    }

    public function getBlackPoint()
    {
        $dict = $this->getPdfValue()->offsetGet(1);

        $blackPoint = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'BlackPoint', false);
        if ($blackPoint === false) {
            return [.0, .0, .0];
        }

        return [
            $blackPoint[0]->ensure()->getValue(),
            $blackPoint[1]->ensure()->getValue(),
            $blackPoint[2]->ensure()->getValue(),
        ];
    }

    public function setBlackPoint($x, $y = null, $z = null)
    {
        if (is_array($x)) {
            list($x, $y, $z) = $x;
        }

        $dict = $this->getPdfValue()->offsetGet(1);

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

    public function getRange()
    {
        $dict = $this->getPdfValue()->offsetGet(1);

        $range = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Range', false);
        if ($range === false) {
            return [-100, 100, -100, 100];
        }

        return [
            $range[0]->ensure()->getValue(),
            $range[1]->ensure()->getValue(),
            $range[2]->ensure()->getValue(),
            $range[3]->ensure()->getValue()
        ];
    }

    public function setRange($aMin, $aMax = null, $bMin = null, $bMax = null)
    {
        if (is_array($aMin)) {
            list($aMin, $aMax, $bMin, $bMax) = $aMin;
        }

        $dict = $this->getPdfValue()->offsetGet(1);

        $range = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Range', false);
        if ($range === false) {
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

    /**
     * Gets an indirect object for this color space dictionary.
     *
     * @see SetaPDF_Core_Resource::getIndirectObject()
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws InvalidArgumentException
     */
    public function getIndirectObject(SetaPDF_Core_Document $document = null)
    {
        if (null === $this->_indirectObject) {
            if (null === $document) {
                throw new InvalidArgumentException('To initialize a new object $document parameter is not optional!');
            }

            $this->_indirectObject = $document->createNewObject($this->getPdfValue());
        }

        return $this->_indirectObject;
    }

    /**
     * Get the resource type of an implementation.
     *
     * @return string
     */
    public function getResourceType()
    {
        return SetaPDF_Core_Resource::TYPE_COLOR_SPACE;
    }
}
