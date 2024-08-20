<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Encoding
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: EncodingInterface.php 1744 2022-06-20 15:33:27Z jan.slabon $
 */

/**
 * Abstract class for encoding classes
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Encoding
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Core_Encoding_AbstractEncoding implements SetaPDF_Core_Encoding_EncodingInterface
{
    public static $table = [];

    /**
     * Returns the encoding table array.
     *
     * Keys are the unicode values while the values are the code
     * points in the specific encoding.
     *
     * @return array
     */
    public static function getTable()
    {
        return static::$table;
    }

    /**
     * Converts a string from UTF-16BE to the implemended encoding.
     *
     * @param string $string The input string
     * @param boolean $ignore Characters that cannot be represented in the target charset are silently discarded
     * @param boolean $translit Transliteration activated
     * @return string
     */
    public static function fromUtf16Be($string, $ignore = false, $translit = false)
    {
        return SetaPDF_Core_Encoding::fromUtf16Be(
            static::$table, $string, $ignore, $translit
        );
    }

    /**
     * Converts a string from the implemented encoding to UTF-16BE.
     *
     * @param string $string The input string
     * @param boolean $ignore Characters that cannot be represented in the target charset are silently discarded
     * @param boolean $translit Transliteration activated
     * @return string
     */
    public static function toUtf16Be($string, $ignore = false, $translit = false)
    {
        return SetaPDF_Core_Encoding::toUtf16Be(
            static::$table, $string, $ignore, $translit
        );
    }
}
