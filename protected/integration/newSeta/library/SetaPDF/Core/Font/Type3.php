<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Type3.php 1776 2022-09-16 09:37:36Z jan.slabon $
 */

/**
 * Class representing a Type3 font.
 *
 * This class is only useable by existing MMType1 fonts.
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Font_Type3 extends SetaPDF_Core_Font
{
    /**
     * The font name
     *
     * @var string
     */
    protected $_fontName;

    /**
     * The to unicode table.
     *
     * @var SetaPDF_Core_Font_Cmap
     */
    protected $_toUnicodeTable;

    /**
     * The encoding table.
     *
     * @var array
     */
    protected $_encodingTable;

    /**
     * Glyph widths
     *
     * @var array
     */
    protected $_widths;

    /**
     * The font bounding box
     *
     * @var null|array
     */
    protected $_fontBBox;

    /**
     * @var null|array
     */
    protected $_calculatedFontBBox;

    /**
     * The average width of glyphs in the font.
     *
     * @var int|float
     */
    protected $_avgWidth;

    /**
     * The font matrix
     *
     * @var SetaPDF_Core_Geometry_Matrix
     */
    protected $_fontMatrix;

    /**
     * The font descriptor object
     *
     * @var SetaPDF_Core_Font_Descriptor
     */
    protected $_fontDescriptor;

    /**
     * @return SetaPDF_Core_Font_Cmap|boolean
     * @throws SetaPDF_Core_Font_Exception
     * @internal
     */
    protected function _getCharCodesTable()
    {
        if ($this->_toUnicodeTable === null) {
            $toUnicodeStream = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'ToUnicode');
            if ($toUnicodeStream instanceof SetaPDF_Core_Type_Stream) {
                $stream = $toUnicodeStream->getStream();
                $this->_toUnicodeTable = SetaPDF_Core_Font_Cmap::create(new SetaPDF_Core_Reader_String($stream));

                return $this->_toUnicodeTable;
            }
        } else {
            return $this->_toUnicodeTable;
        }

        return false;
    }

    /**
     * Get the encoding table based on the Encoding dictionary and it's Differences entry (if available).
     *
     * @return array
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _getEncodingTable()
    {
        if ($this->_encodingTable === null) {
            /* 1. Check for an existing encoding which
             *    overwrites the fonts build in encoding
             */
            $baseEncoding = false;
            $diff = [];

            $encoding = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'Encoding');
            if ($encoding instanceof SetaPDF_Core_Type_Name) {
                $baseEncoding = $encoding->getValue();
            } elseif ($encoding instanceof SetaPDF_Core_Type_Dictionary) {
                $baseEncoding = SetaPDF_Core_Type_Dictionary_Helper::getValue($encoding, 'BaseEncoding', false, true);

                $diff = SetaPDF_Core_Type_Array::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($encoding, 'Differences', new SetaPDF_Core_Type_Array())
                )->toPhp(true);
            }

            if ($baseEncoding) {
                $baseEncoding = substr($baseEncoding, 0, strpos($baseEncoding, 'Encoding'));
                $className = 'SetaPDF_Core_Encoding_' . $baseEncoding;

                if (is_callable([$className, 'getTable'])) {
                    $baseEncodingTable = call_user_func([$className, 'getTable']);
                } else {
                    $baseEncodingTable = $this->getBaseEncodingTable();
                }
            } else {
                $baseEncodingTable = $this->getBaseEncodingTable();
            }

            $newBaseEncodingTable = [];

            $currentCharCode = null;
            $touchedChars = [];

            foreach ($diff AS $value) {
                if (is_float($value) || is_int($value)) {
                    $currentCharCode = $value;
                    continue;
                }

                $utf16BeCodePoint = SetaPDF_Core_Font_Glyph_List::byName($value);
                if ($utf16BeCodePoint !== '') {
                    $currentChar = chr($currentCharCode);
                    if (isset($newBaseEncodingTable[$utf16BeCodePoint])) {
                        if (!is_array($newBaseEncodingTable[$utf16BeCodePoint])) {
                            $newBaseEncodingTable[$utf16BeCodePoint] = [$newBaseEncodingTable[$utf16BeCodePoint]];
                        }
                        $newBaseEncodingTable[$utf16BeCodePoint][] = $currentChar;
                    } else {
                        $newBaseEncodingTable[$utf16BeCodePoint] = $currentChar;
                    }

                    $touchedChars[] = $currentChar;
                }
                $currentCharCode++;
            }

            // remove touched chars from existing encoding:
            foreach ($baseEncodingTable AS $uni => $value) {
                if (count($touchedChars) === 0) {
                    break;
                }

                foreach ($touchedChars AS $key => $charCode) {
                    if ($value === $charCode) {
                        unset($baseEncodingTable[$uni], $touchedChars[$key]);
                    } elseif (is_array($value)) {
                        foreach ($value AS $_key => $_value) {
                            if ($_value === $charCode) {
                                unset($baseEncodingTable[$uni][$_key], $touchedChars[$key]);
                            }
                        }

                        if (count($value) === 1) {
                            $value = current($value);
                        }
                    }
                }
            }

            foreach ($baseEncodingTable AS $key => $value) {
                if (!isset($newBaseEncodingTable[$key])) {
                    $newBaseEncodingTable[$key] = $value;
                } else {
                    if (!is_array($newBaseEncodingTable[$key])) {
                        $newBaseEncodingTable[$key] = [$newBaseEncodingTable[$key]];
                    }

                    if (is_array($value)) {
                        $newBaseEncodingTable[$key] = array_merge($newBaseEncodingTable[$key], $value);
                    } else {
                        $newBaseEncodingTable[$key][] = $value;
                    }
                }
            }

            $this->_encodingTable = array_merge(
                array_filter($newBaseEncodingTable, 'is_array'),
                array_filter($newBaseEncodingTable, 'is_string')
            );

            // Try to get the "?" as substitute character
            $this->_substituteCharacter = SetaPDF_Core_Encoding::fromUtf16Be($this->_encodingTable, "\x00\x3F", true);
        }

        return $this->_encodingTable;
    }

    /**
     * Get the font name.
     *
     * @return string
     */
    public function getFontName()
    {
        if ($this->_fontName === null) {
            $name = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'Name', '', true);
            if ($name !== '') {
                $this->_fontName = $name;
                return $this->_fontName;
            }

            $this->_fontName = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'BaseFont', '', true);
        }

        return $this->_fontName;
    }

    /**
     * Get the font family.
     *
     * @return false A type 3 font does not have a font family.
     */
    public function getFontFamily()
    {
        return false;
    }

    /**
     * Checks if the font is bold.
     *
     * @return boolean
     * @throws SetaPDF_Core_Font_Exception
     */
    public function isBold()
    {
        $fontWeight = $this->getFontDescriptor()->getFontWeight() ?: 400;
        return $fontWeight >= 700;
    }

    /**
     * Checks if the font is italic.
     *
     * @return boolean
     */
    public function isItalic()
    {
        try {
            return $this->getFontDescriptor()->getItalicAngle() != 0;
        } catch (SetaPDF_Core_Font_Exception $e) {
            return false;
        }
    }

    /**
     * Checks if the font is monospace.
     *
     * @return boolean
     */
    public function isMonospace()
    {
        try {
            return ($this->getFontDescriptor()->getFlags() & 1) === 1;
        } catch (SetaPDF_Core_Font_Exception $e) {
            return false;
        }
    }

    /**
     * Get the font matrix.
     *
     * @return SetaPDF_Core_Geometry_Matrix
     * @throws SetaPDF_Core_Exception
     */
    public function getFontMatrix()
    {
        if ($this->_fontMatrix === null) {
            $fontMatrix = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'FontMatrix');
            if (!$fontMatrix instanceof SetaPDF_Core_Type_Array) {
                throw new SetaPDF_Core_Exception('No FontMatrix entry found!');
            }

            $this->_fontMatrix = new SetaPDF_Core_Geometry_Matrix($fontMatrix->toPhp(true));
        }

        return $this->_fontMatrix;
    }

    /**
     * Returns the font bounding box.
     *
     * @return array
     * @throws SetaPDF_Core_Exception
     * @internal
     */
    public function getFontBBox()
    {
        if ($this->_fontBBox !== null) {
            return $this->_fontBBox;
        }

        $fontBBox = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'FontBBox');
        if (!$fontBBox instanceof SetaPDF_Core_Type_Array) {
            throw new SetaPDF_Core_Exception('No FontBBox entry found!');
        }

        $this->_fontBBox = $fontBBox->toPhp(true);
        return $this->_fontBBox;
    }

    /**
     * @inheritDoc
     * @return array|null
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Parser_Pdf_InvalidTokenException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function recalculateFontBBox()
    {
        if ($this->_calculatedFontBBox) {
            return $this->_calculatedFontBBox;
        }

        $procs = SetaPDF_Core_Type_Dictionary::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'CharProcs')
        );
        $llx = $lly = $urx = $ury = 0;

        foreach ($procs as $proc) {
            $stream = substr(SetaPDF_Core_Type_Stream::ensureType($proc)->getStream(), 0, 35);
            $parser = new SetaPDF_Core_Parser_Content($stream);
            $parser->registerOperator('d0', static function ($args) use (&$llx, &$lly, &$urx, &$ury) {
                $urx = max($urx, $llx + $args[0]->getValue());
                $ury = max($ury, $lly + $args[1]->getValue());
            });

            $parser->registerOperator('d1', static function ($args) use (&$llx, &$lly, &$urx, &$ury) {
                $llx = min($llx, $args[2]->getValue());
                $lly = min($lly, $args[3]->getValue());
                $urx = max($urx, $args[4]->getValue());
                $ury = max($ury, $args[5]->getValue());
            });

            $parser->process();
        }

        $this->_calculatedFontBBox =  [$llx, $lly, $urx, $ury];

        return $this->_calculatedFontBBox;
    }

    /**
     * Get the font descriptor object.
     *
     * @return SetaPDF_Core_Font_Descriptor
     */
    public function getFontDescriptor()
    {
        if ($this->_fontDescriptor === null) {
            $value = SetaPDF_Core_Type_Dictionary_Helper::getValue(
                $this->_dictionary,
                'FontDescriptor',
                new SetaPDF_Core_Type_Dictionary()
            );

            $this->_fontDescriptor = new SetaPDF_Core_Font_Descriptor($value);
        }

        return $this->_fontDescriptor;
    }

    /**
     * Returns the italic angle.
     *
     * @return float
     */
    public function getItalicAngle()
    {
        try {
            return $this->getFontDescriptor()->getItalicAngle();
        } catch (SetaPDF_Core_Font_Exception $e) {
            return 0;
        }
    }

    /**
     * Returns the distance from baseline of highest ascender (Typographic ascent).
     *
     * @return float|false
     * @throws SetaPDF_Core_Font_Exception
     */
    public function getAscent()
    {
        return $this->getFontDescriptor()->getAscent();
    }

    /**
     * Returns the distance from baseline of lowest descender (Typographic descent).
     *
     * @return float
     * @throws SetaPDF_Core_Font_Exception
     * @internal
     */
    public function getDescent()
    {
        return $this->getFontDescriptor()->getDescent();
    }

    /**
     * Get the average glyph width.
     *
     * @param boolean $calculateIfUndefined
     * @return integer|float
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAvgWidth($calculateIfUndefined = false)
    {
        if ($this->_avgWidth === null) {
            $avgWidth = $this->getFontDescriptor()->getAvgWidth();
            if ($calculateIfUndefined === false || ($calculateIfUndefined && $avgWidth !== parent::getAvgWidth())) {
                return $avgWidth;
            }

            if ($this->_widths === null) {
                $this->_getWidths();
            }

            $widths = array_filter($this->_widths);
            if (count($widths) === 0) {
                return $this->getMissingWidth();
            }

            $this->_avgWidth = array_sum($widths) / count($widths);
        }

        return $this->_avgWidth;
    }

    /**
     * Get the max. glyph width.
     *
     * @return integer|float
     * @throws SetaPDF_Core_Font_Exception
     */
    public function getMaxWidth()
    {
        return $this->getFontDescriptor()->getMaxWidth();
    }

    /**
     * Get the missing glyph width.
     *
     * @return integer|float
     * @throws SetaPDF_Core_Font_Exception
     */
    public function getMissingWidth()
    {
        return $this->getFontDescriptor()->getMissingWidth();
    }

    /**
     * Resolves the width values from the font descriptor and fills the {@link $_width}-array.
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_Font_Exception
     */
    protected function _getWidths()
    {
        $firstChar = (int)SetaPDF_Core_Type_Numeric::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'FirstChar')
        )->getValue();
        $lastChar = (int)SetaPDF_Core_Type_Numeric::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'LastChar')
        )->getValue();

        $widths = SetaPDF_Core_Type_Array::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'Widths')
        )->toPhp(true);

        $table = $this->_getCharCodesTable();
        if ($table === false) {
            $table = $this->_getEncodingTable();
        }

        $this->_widths = [];
        $this->_widthsByCharCode = [];

        for ($i = $firstChar ; $i <= $lastChar; $i++) {
            $charCode = chr($i);
            $width = $widths[$i - $firstChar];

            $this->_widthsByCharCode[$charCode] = $width;

            $utf16BeCodePoint = SetaPDF_Core_Encoding::toUtf16Be($table, $charCode, false, true);
            if (!isset($this->_widths[$utf16BeCodePoint])) {
                $this->_widths[$utf16BeCodePoint] = $width;
            }
        }
    }

    /**
     * Get the width of a glyph/character.
     *
     * @param string $char
     * @param string $encoding The input encoding
     * @return float|int
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see SetaPDF_Core_Font::getGlyphWidth()
     */
    public function getGlyphWidth($char, $encoding = 'UTF-16BE')
    {
        if ($this->_widths === null) {
            $this->_getWidths();
        }

        return parent::getGlyphWidth($char, $encoding);
    }

    /**
     * Get the width of a glpyh by its char code.
     *
     * @param string $charCode
     * @return float|int
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getGlyphWidthByCharCode($charCode)
    {
        if ($this->_widthsByCharCode === null) {
            $this->_getWidths();
        }

        return parent::getGlyphWidthByCharCode($charCode);
    }

    /**
     * Converts char codes from the font specific encoding to another encoding.
     *
     * @param string $charCodes The char codes in the font specific encoding.
     * @param string $encoding The resulting encoding
     * @param bool $asArray
     * @return string|array
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getCharsByCharCodes($charCodes, $encoding = 'UTF-8', $asArray = true)
    {
        $table = $this->_getCharCodesTable();
        if ($table === false) {
            $table = $this->_getEncodingTable();
        }

        $chars = SetaPDF_Core_Encoding::toUtf16Be($table, $charCodes, false, true);

        if ($encoding !== 'UTF-16BE') {
            $chars = SetaPDF_Core_Encoding::convert($chars, 'UTF-16BE', $encoding);
        }

        if ($asArray) {
            $chars = SetaPDF_Core_Encoding::strSplit($chars, $encoding);
        }

        return $chars;
    }

    /**
     * Get the base encoding of the font.
     *
     * The base encoding for Type3 fonts is undefined but most readers/viewers use an ascii like table.
     *
     * @return array
     */
    public function getBaseEncodingTable()
    {
        $encoding = [];
        for ($key = 0; $key <= 255; $key++) {
            $utf16 = (string)SetaPDF_Core_Encoding::unicodePointToUtf16Be($key);
            $encoding[$utf16] = chr($key);
        }

        return $encoding;
    }
}
