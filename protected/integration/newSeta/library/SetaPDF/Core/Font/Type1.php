<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Type1.php 1767 2022-08-18 08:19:35Z jan.slabon $
 */

/**
 * Class for Type1 fonts
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Font_Type1
    extends SetaPDF_Core_Font_Simple
    implements SetaPDF_Core_Font_DescriptorInterface
{
    /**
     * The font descriptor object
     *
     * @var SetaPDF_Core_Font_Descriptor
     */
    protected $_fontDescriptor;

    /**
     * Glyph widths
     *
     * @var null|array
     */
    protected $_widths;

    /**
     * The UTF-16BE unicode value for a substitute character
     *
     * @var null|string
     */
    protected $_substituteCharacter;

    /**
     * The font bounding box
     *
     * @var null|array
     */
    protected $_fontBBox;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary $indirectObjectOrDictionary
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct($indirectObjectOrDictionary)
    {
        $dictionary = $indirectObjectOrDictionary->ensure();
        foreach (['Widths', 'FontDescriptor'] AS $key) {
            if (!$dictionary->offsetExists($key)) {
                throw new SetaPDF_Core_Font_Exception(sprintf('Missing "%s" entry in font dictionary.', $key));
            }
        }

        $firstCharExists = $dictionary->offsetExists('FirstChar');
        $lastCharExists = $dictionary->offsetExists('LastChar');
        // Try to replair the font if one of these items is missing
        if (!$firstCharExists || !$lastCharExists) {
            $widthsCount = count(SetaPDF_Core_Type_Array::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Widths')
            ));
            if (!$firstCharExists && !$lastCharExists && $widthsCount === 256) {
                $dictionary['FirstChar'] = new SetaPDF_Core_Type_Numeric(0);
                $dictionary['LastChar'] = new SetaPDF_Core_Type_Numeric(255);
            } elseif (!$firstCharExists && $lastCharExists) {
                $lastChar = SetaPDF_Core_Type_Numeric::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'LastChar')
                )->getValue();
                $dictionary['FirstChar'] = new SetaPDF_Core_Type_Numeric($lastChar - $widthsCount + 1);
            } elseif ($firstCharExists && !$lastCharExists) {
                $firstChar = SetaPDF_Core_Type_Numeric::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'FirstChar')
                )->getValue();
                $dictionary['LastChar'] = new SetaPDF_Core_Type_Numeric($firstChar + $widthsCount - 1);
            } else {
                throw new SetaPDF_Core_Font_Exception(
                    'Missing "FirstChar" or "LastChar" entry in font dictionary.'
                );
            }
        }

        parent::__construct($indirectObjectOrDictionary);
    }

    /**
     * Get the font descriptor object.
     *
     * @return SetaPDF_Core_Font_Descriptor
     */
    public function getFontDescriptor()
    {
        if ($this->_fontDescriptor === null) {
            /** @var SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary $fontDescriptor */
            $fontDescriptor = $this->_dictionary->getValue('FontDescriptor');
            $this->_fontDescriptor = new SetaPDF_Core_Font_Descriptor($fontDescriptor);
        }

        return $this->_fontDescriptor;
    }

    /**
     * Get the font name.
     *
     * @return string
     */
    public function getFontName()
    {
        return SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_dictionary, 'BaseFont', '', true);
    }

    /**
     * Get the font family.
     *
     * @return string
     * @throws SetaPDF_Core_Font_Exception
     */
    public function getFontFamily()
    {
        return $this->getFontDescriptor()->getFontFamily();
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
     * @throws SetaPDF_Core_Font_Exception
     */
    public function isItalic()
    {
        return $this->getFontDescriptor()->getItalicAngle() != 0;
    }

    /**
     * Checks if the font is monospace.
     *
     * @return boolean
     * @throws SetaPDF_Core_Font_Exception
     */
    public function isMonospace()
    {
        return ($this->getFontDescriptor()->getFlags() & 1) === 1;
    }

    /**
     * Returns the font bounding box.
     *
     * @return array
     */
    public function getFontBBox()
    {
        if ($this->_fontBBox === null) {
            $this->_fontBBox = $this->getFontDescriptor()->getFontBBox();
        }
        return $this->_fontBBox;
    }

    /**
     * Returns the italic angle.
     *
     * @return float
     * @throws SetaPDF_Core_Font_Exception
     */
    public function getItalicAngle()
    {
        return $this->getFontDescriptor()->getItalicAngle();
    }

    /**
     * Returns the distance from baseline of highest ascender (Typographic ascent).
     *
     * @return float
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
     */
    public function getAvgWidth($calculateIfUndefined = false)
    {
        $default = SetaPDF_Core_Font::getAvgWidth();
        $avgWidth = $this->getFontDescriptor()->getAvgWidth();
        if ($calculateIfUndefined && $default === $avgWidth) {
            return parent::getAvgWidth(true);
        }

        return $avgWidth;
    }

    /**
     * Get the max glyph width.
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

        for ($i = $firstChar; $i <= $lastChar; $i++) {
            $charCode = chr($i);
            if (isset($widths[$i - $firstChar])) {
                $width = $widths[$i - $firstChar];
            } else {
                $width = $this->getMissingWidth();
            }

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
     * Get the base encoding of the font.
     *
     * If no BaseEncoding entry is available we use the
     * Standard encoding for now. This should be extended
     * to get the fonts build in encoding later.
     *
     * @return array
     */
    public function getBaseEncodingTable()
    {
        return SetaPDF_Core_Encoding_Standard::$table;
    }
}
