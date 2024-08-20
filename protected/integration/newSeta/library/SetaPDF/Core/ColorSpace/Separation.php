<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Separation.php 1776 2022-09-16 09:37:36Z jan.slabon $
 */

/**
 * Separation Color Space
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage ColorSpace
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_ColorSpace_Separation
    extends SetaPDF_Core_ColorSpace_Resource
{
    /**
     * Creates a spot color color-space.
     *
     * @param SetaPDF_Core_Document $document
     * @param string $name
     * @param int|float|array $c If is array $m, $y, $k will be ignored. The array need 4 entries.
     * @param int|float $m
     * @param int|float $y
     * @param int|float $k
     * @return SetaPDF_Core_ColorSpace_Separation
     * @throws SetaPDF_Core_Exception
     */
    public static function createSpotColor(SetaPDF_Core_Document $document, $name, $c, $m = null, $y = null, $k = null)
    {
        if (is_array($c)) {
            list($c, $m, $y, $k) = $c;
        }

        $function = $document->createNewObject(new SetaPDF_Core_Type_Dictionary([
            'FunctionType' => new SetaPDF_Core_Type_Numeric(2),
            'Domain' => new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(1)
            ]),
            'N' => new SetaPDF_Core_Type_Numeric(1),
            'Range' => new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(1),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(1),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(1),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(1),
            ]),
            'C0' => new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(0),
            ]),
            'C1' => new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric($c),
                new SetaPDF_Core_Type_Numeric($m),
                new SetaPDF_Core_Type_Numeric($y),
                new SetaPDF_Core_Type_Numeric($k),
            ])
        ]));

        $object = $document->createNewObject(new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Name('Separation', true),
            new SetaPDF_Core_Type_Name($name),
            new SetaPDF_Core_Type_Name('DeviceCMYK ', true),
            $function
        ]));

        return new self($object);
    }

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
            throw  new InvalidArgumentException('Separation color space needs to be defined by an array of 4 values.');
        }

        if ($this->getFamily() !== 'Separation') {
            throw new InvalidArgumentException('Separation color space has to be named "Separation".');
        }

        if ($this->getPdfValue()->count() !== 4) {
            throw new InvalidArgumentException('Separation color spaces definition has to be defined by 4 values.');
        }
    }

    /**
     * Get the name of the colorant that this Separation color space is intended to represent.
     *
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getName()
    {
        $nameObject = $this->getPdfValue()->offsetGet(1);
        return SetaPDF_Core_Type_Name::ensureType($nameObject)->getValue();
    }

    /**
     * Set the name of the colorant that this Separation color space is intended to represent.
     *
     * @param string $name
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setName($name)
    {
        $nameObject = $this->getPdfValue()->offsetGet(1);
        SetaPDF_Core_Type_Name::ensureType($nameObject)->setValue($name);
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
        /** @var SetaPDF_Core_Type_Name|SetaPDF_Core_Type_Array $alternate */
        $alternate = $this->getPdfValue()->offsetGet(2);

        return SetaPDF_Core_ColorSpace::createByDefinition($alternate);
    }

    /**
     * Get the alternate color space.
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
}
