<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage DataStructure
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Rectangle.php 1733 2022-06-02 07:39:42Z jan.slabon $
 */

/**
 * Data structure class for rect objects
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage DataStructure
 * @license    https://www.setasign.com/ Commercial
 * @property integer|float $llx
 * @property integer|float $lly
 * @property integer|float $urx
 * @property integer|float $ury
 */
class SetaPDF_Core_DataStructure_Rectangle
    implements SetaPDF_Core_DataStructure_DataStructureInterface
{
    /**
     * An array representing a rectangle [llx lly urx ury]
     *
     * @var SetaPDF_Core_Type_Array
     */
    protected $_array;

    /**
     * Create a rectangle by a specific argument.
     *
     * @param array|SetaPDF_Core_Geometry_Rectangle|SetaPDF_Core_DataStructure_Rectangle $rectangle
     * @return SetaPDF_Core_DataStructure_Rectangle
     * @throws InvalidArgumentException
     */
    public static function create($rectangle)
    {
        if (is_array($rectangle)) {
            return self::byArray($rectangle);
        }

        if ($rectangle instanceof SetaPDF_Core_Geometry_Rectangle || $rectangle instanceof self) {
            return self::byRectangle($rectangle);
        }

        throw new InvalidArgumentException('Parameter needs to be of a Rectangle type or an array.');
    }

    /**
     * Create a rect object or array from a php array.
     *
     * @param array $phpArray
     * @param boolean $getValue
     * @throws InvalidArgumentException
     * @return SetaPDF_Core_Type_Array|SetaPDF_Core_DataStructure_Rectangle
     */
    public static function byArray(array $phpArray, $getValue = false)
    {
        $phpArray = array_values($phpArray);
        if (count($phpArray) !== 4) {
            throw new InvalidArgumentException('A rect should have at least 4 points.');
        }

        $array = new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Numeric($phpArray[0]),
            new SetaPDF_Core_Type_Numeric($phpArray[1]),
            new SetaPDF_Core_Type_Numeric($phpArray[2]),
            new SetaPDF_Core_Type_Numeric($phpArray[3])
        ]);

        if ($getValue === false) {
            return new self($array);
        }

        return $array;
    }

    /**
     * Create an instance by another rectangle instance.
     *
     * @param SetaPDF_Core_Geometry_Rectangle|SetaPDF_Core_DataStructure_Rectangle $rectangle
     * @return SetaPDF_Core_DataStructure_Rectangle
     */
    public static function byRectangle($rectangle)
    {
        if ($rectangle instanceof SetaPDF_Core_Geometry_Rectangle) {
            $ll = $rectangle->getLl();
            $ur = $rectangle->getUr();

            return self::byArray([
                $ll->getX(), $ll->getY(), $ur->getX(), $ur->getY()
            ]);
        }

        if ($rectangle instanceof self) {
            return self::byArray($rectangle->toPhp());
        }

        throw new InvalidArgumentException('Parameter needs to be of a Rectangle type.');
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_Array $array
     * @throws InvalidArgumentException
     */
    public function __construct(SetaPDF_Core_Type_Array $array = null)
    {
        if ($array === null) {
            $array = new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(0)
            ]);
        }

        if ($array->count() !== 4) {
            throw new InvalidArgumentException(
                sprintf(
                    'A rectangle should be an array of 4 numeric values. %s given.',
                    $array->count()
                )
            );
        }

        $this->_array = $array;
    }

    /**
     * Implementation of clone.
     */
    public function __clone()
    {
        $this->_array = clone $this->_array;
    }

    /**
     * Get handler.
     *
     * @param string $name
     * @return float|integer
     * @throws InvalidArgumentException
     */
    public function __get($name)
    {
        $values = $this->_array->toPhp(true);

        switch ($name) {
            case 'llx':
                return min($values[0], $values[2]);

            case 'lly':
                return min($values[1], $values[3]);

            case 'urx':
                return max($values[0], $values[2]);

            case 'ury':
                return max($values[1], $values[3]);
        }

        throw new InvalidArgumentException(
            'Undefined property: SetaPDF_Core_DataStructure_Rectangle::' . $name
        );
    }

    /**
     * Set handler.
     *
     * @param string $name
     * @param float|integer $value
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __set($name, $value)
    {
        $values = $this->_array->toPhp(true);

        switch ($name) {
            case 'llx':
                $index = $values[0] > $values[2] ? 2 : 0;
                break;

            case 'lly':
                $index = $values[1] > $values[3] ? 3 : 1;
                break;

            case 'urx':
                $index = $values[0] > $values[2] ? 0 : 2;
                break;

            case 'ury':
                $index = $values[1] > $values[3] ? 1 : 3;
                break;

            default:
                throw new InvalidArgumentException(
                    'Undefined property: SetaPDF_Core_DataStructure_Rectangle::' . $name
                );
        }

        SetaPDF_Core_Type_Numeric::ensureType($this->_array->offsetGet($index))->setValue($value);
    }

    /**
     * Set the lower left x-coordinate.
     *
     * @param integer|float $llx
     */
    public function setLlx($llx)
    {
        $this->llx = $llx;
    }

    /**
     * Get the lower left x-coordinate.
     *
     * @return integer|float
     */
    public function getLlx()
    {
        return $this->llx;
    }

    /**
     * Set the lower left y-coordinate.
     *
     * @param integer|float $lly
     */
    public function setLly($lly)
    {
        $this->lly = $lly;
    }

    /**
     * Get the lower left y-coordinate.
     *
     * @return integer|float
     */
    public function getLly()
    {
        return $this->lly;
    }

    /**
     * Set the upper right x-coordinate.
     *
     * @param integer|float $urx
     */
    public function setUrx($urx)
    {
        $this->urx = $urx;
    }

    /**
     * Get the upper right x-coordinate.
     *
     * @return integer|float
     */
    public function getUrx()
    {
        return $this->urx;
    }

    /**
     * Set the upper right y-coordinate.
     *
     * @param integer|float $ury
     */
    public function setUry($ury)
    {
        $this->ury = $ury;
    }

    /**
     * Get the upper right y-coordinate.
     *
     * @return integer|float
     */
    public function getUry()
    {
        return $this->ury;
    }

    /**
     * Set all coordinates.
     *
     * @param float|integer $llx Lower left x value
     * @param float|integer $lly Lower left y value
     * @param float|integer $urx Upper right x value
     * @param float|integer $ury Upper right y value
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setAll($llx, $lly, $urx, $ury)
    {
        foreach (func_get_args() AS $key => $value) {
            SetaPDF_Core_Type_Numeric::ensureType($this->_array->offsetGet($key))->setValue($value);
        }
    }

    /**
     * Get the width of the rect.
     *
     * @return integer|float
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getWidth()
    {
        $llx = SetaPDF_Core_Type_Numeric::ensureType($this->_array->offsetGet(0))->getValue();
        $urx = SetaPDF_Core_Type_Numeric::ensureType($this->_array->offsetGet(2))->getValue();

        return abs($urx - $llx);
    }

    /**
     * Get the height of the rect.
     *
     * @return integer|float
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getHeight()
    {
        $lly = SetaPDF_Core_Type_Numeric::ensureType($this->_array->offsetGet(1))->getValue();
        $ury = SetaPDF_Core_Type_Numeric::ensureType($this->_array->offsetGet(3))->getValue();

        return abs($ury - $lly);
    }

    /**
     * Get the PDF value object.
     *
     * @return SetaPDF_Core_Type_Array
     * @see SetaPDF_Core_DataStructure_DataStructureInterface::getValue()
     */
    public function getValue()
    {
        return $this->_array;
    }

    /**
     * Get the data as a PHP value.
     *
     * @return array
     */
    public function toPhp()
    {
        return $this->_array->toPhp(true);
    }

    /**
     * Return this rectangle as a {@link SetaPDF_Core_Geometry_Rectangle} instance.
     *
     * @return SetaPDF_Core_Geometry_Rectangle
     */
    public function getRectangle()
    {
        $values = $this->toPhp();
        return new SetaPDF_Core_Geometry_Rectangle($values[0], $values[1], $values[2], $values[3]);
    }

    /**
     * Checks whether this rectangle contains another rectangle.
     *
     * @param SetaPDF_Core_Geometry_Rectangle|SetaPDF_Core_DataStructure_Rectangle $otherRect
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function contains($otherRect)
    {
        if ($otherRect instanceof self) {
            $otherRect = $otherRect->getRectangle();
        }

        if (!($otherRect instanceof SetaPDF_Core_Geometry_Rectangle)) {
            throw new InvalidArgumentException('$otherRect need to be instance of SetaPDF_Core_Geometry_Rectangle or SetaPDF_Core_DataStructure_Rectangle');
        }

        $thisRect = $this->getRectangle();
        return $thisRect->contains($otherRect);
    }

    /**
     * Checks whether this rectangle intersects another rectangle.
     *
     * @param SetaPDF_Core_Geometry_Rectangle|SetaPDF_Core_DataStructure_Rectangle $otherRect
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function intersect($otherRect)
    {
        if ($otherRect instanceof self) {
            $otherRect = $otherRect->getRectangle();
        }

        if (!($otherRect instanceof SetaPDF_Core_Geometry_Rectangle)) {
            throw new InvalidArgumentException('$otherRect need to be instance of SetaPDF_Core_Geometry_Rectangle or SetaPDF_Core_DataStructure_Rectangle');
        }

        $thisRect = $this->getRectangle();
        return $thisRect->intersect($otherRect);
    }
}
