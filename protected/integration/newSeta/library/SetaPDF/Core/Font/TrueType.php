<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: TrueType.php 1778 2022-09-16 10:50:21Z jan.slabon $
 */

/**
 * Class for TrueType fonts
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Font_TrueType
    extends SetaPDF_Core_Font_Type1
{
    /**
     * The TTF/OTF parser
     *
     * @var SetaPDF_Core_Font_TrueType_File
     */
    protected $_ttfParser;

    /**
     * Flag for handling automatic encoding
     *
     * @var boolean
     */
    protected $_autoEncoding = false;

    /**
     * A temporary encoding table holding used character codes
     *
     * This array is only used if the _autoEncoding property is used.
     *
     * @var array
     */
    protected $_tmpEncodingTable = [];

    /**
     * @var array
     */
    protected $_calculatedFontBBox;

    /**
     * The TTF/OTF parser of the embedded font file.
     *
     * @var SetaPDF_Core_Font_TrueType_File
     */
    protected $_streamParser;

    /**
     * Creates a font object based on a TrueType font file.
     *
     * @param SetaPDF_Core_Document $document The document instance in which the font will be used
     * @param string $fontFile A path to the TTF font file
     * @param string $baseEncoding The base encoding
     * @param array|string $diffEncoding A translation table to adjust individual char codes to different glyphs or
     *                                   "auto" to build this table dynamically.
     * @param boolean $embedded Defines if the font program will be embedded in the document or not
     * @param bool $ignoreLicenseRestrictions Can be used to disable the font license check
     * @return SetaPDF_Core_Font_TrueType The {@link SetaPDF_Core_Font_TrueType} instance
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Exception_NotImplemented
     * @throws SetaPDF_Core_Exception
     */
    public static function create(
        SetaPDF_Core_Document $document,
        $fontFile,
        $baseEncoding = SetaPDF_Core_Encoding::WIN_ANSI,
        $diffEncoding = [],
        $embedded = true,
        $ignoreLicenseRestrictions = false
    )
    {
        $ttfParser = new SetaPDF_Core_Font_TrueType_File($fontFile);

        // Check if embedding is allowed
        if ($embedded && $ignoreLicenseRestrictions === false && $ttfParser->isEmbeddable() === false) {
            throw new SetaPDF_Core_Font_Exception(
                sprintf(
                    'Due to license restrictions it is not allowed to embed this font file (%s).',
                    basename($fontFile)
                )
            );
        }

        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('Type', new SetaPDF_Core_Type_Name('Font', true));
        $dictionary->offsetSet('Subtype', new SetaPDF_Core_Type_Name('TrueType', true));

        /** @var SetaPDF_Core_Font_TrueType_Table_Name $nameTable */
        $nameTable = $ttfParser->getTable(SetaPDF_Core_Font_TrueType_Table_Tags::NAME);

        // Windows platform
        $postScriptName = $nameTable->getName(3, 1, 0x0409, SetaPDF_Core_Font_TrueType_Table_Name::POSTSCRIPT_NAME); // United States
        if ($postScriptName === false) {
            throw new SetaPDF_Core_Font_Exception(
                'Missing name record for postscript name (platformId = 3, encodingId = 1)'
            );
        }

        $baseFont = SetaPDF_Core_Encoding::convert($postScriptName, 'UTF-16BE', 'UTF-8');

        $dictionary->offsetSet('BaseFont', new SetaPDF_Core_Type_Name($baseFont));
        $dictionary->offsetSet('FirstChar', new SetaPDF_Core_Type_Numeric(0));
        $dictionary->offsetSet('LastChar', $lastCharObject = new SetaPDF_Core_Type_Numeric(0));

        $baseEncodingTable = SetaPDF_Core_Encoding::getPredefinedEncodingTable($baseEncoding);

        /** @var SetaPDF_Core_Font_TrueType_Table_Header $headerTbl */
        $headerTbl = $ttfParser->getTable(SetaPDF_Core_Font_TrueType_Table_Tags::HEADER);
        $factor = 1000 / $headerTbl->getUnitsPerEm();
        $f = static function($value) use ($factor) {
            return round($value * $factor);
        };

        $encoding = new SetaPDF_Core_Type_Dictionary();
        $encoding->offsetSet('Type', new SetaPDF_Core_Type_Name('Encoding', true));
        $encoding->offsetSet('BaseEncoding', new SetaPDF_Core_Type_Name($baseEncoding));

        if ($diffEncoding === 'auto' || count($diffEncoding) > 0) {
            $differences = new SetaPDF_Core_Type_Array();
            $encoding->offsetSet('Differences', $differences);

            $currentCode = null;
            if (is_array($diffEncoding)) {
                foreach ($diffEncoding AS $code => $name) {
                    if ($currentCode === null || $code !== $currentCode) {
                        $differences[] = new SetaPDF_Core_Type_Numeric($code);
                        $currentCode = $code;
                    }

                    $differences[] = new SetaPDF_Core_Type_Name($name);
                    $currentCode++;
                }
            }
        }

        $dictionary->offsetSet('Encoding', $encoding);

        /**
         * @var SetaPDF_Core_Font_TrueType_Table_HorizontalMetrics $hmtxTable
         */
        $hmtxTable = $ttfParser->getTable(SetaPDF_Core_Font_TrueType_Table_Tags::HORIZONTAL_METRICS);
        $missingWidth = $hmtxTable->getAdvanceWidth(0);

        $widthsArray = new SetaPDF_Core_Type_Array();
        if ($diffEncoding !== 'auto') {
            $firstChar = 0;
            $lastChar = 255;
            $lastCharObject->setValue($lastChar);

            $chars = [];
            for ($i = $firstChar; $i <= $lastChar; $i++) {
                if (isset($diffEncoding[$i])) {
                    $utf16 = SetaPDF_Core_Font_Glyph_List::byName($diffEncoding[$i]);
                } else {
                    $utf16 = array_search(chr($i), $baseEncodingTable, true);
                    if ($utf16 === false) {
                        foreach ($baseEncodingTable AS $_utf16 => $char) {
                            // The table is sorted, so that array'ed values are at the top
                            if (!is_array($char)) {
                                break;
                            }

                            if (in_array(chr($i), $char, true)) {
                                $utf16 = $_utf16;
                                break;
                            }
                        }
                    }
                }
                $chars[$i] = $utf16;
            }

            foreach ($chars AS $char) {
                if ($char === false) {
                    $width = $missingWidth;
                } else {
                    $width = $ttfParser->getWidth($char);
                }

                $widthsArray[] = new SetaPDF_Core_Type_Numeric($f($width));
            }
        }

        $widthsObject = $document->createNewObject($widthsArray);
        $dictionary->offsetSet('Widths', $widthsObject);

        /* Handle/Prepare ToUnicode
         * This is required by the Adobe Reader if a predefined encoding is changed 
         */
        if ($diffEncoding === 'auto' || count($diffEncoding) > 0) {
            $toUnicodeStream = new SetaPDF_Core_Type_Stream();
            $toUnicodeStream->getValue()->offsetSet('Filter', new SetaPDF_Core_Type_Name('FlateDecode', true));
            $streamObject = $document->createNewObject($toUnicodeStream);
            $dictionary->offsetSet('ToUnicode', $streamObject);
            if ($diffEncoding !== 'auto') {
                /** @noinspection PhpUndefinedVariableInspection */
                $toUnicodeStream->setStream(self::_createToUnicodeStream($chars));
            }
        }

        $fontKey = $fontFile . ($embedded ? '1' : '0');
        if (!$document->hasCache(SetaPDF_Core_Document::CACHE_FONT_DESCRIPTOR, $fontKey)) {
            $fontDescriptor = new SetaPDF_Core_Type_Dictionary();
            $fontDescriptor->offsetSet('Type', new SetaPDF_Core_Type_Name('FontDescriptor', true));
            $fontDescriptor->offsetSet('FontName', new SetaPDF_Core_Type_Name($baseFont));

            // Unicode platform
            $fontFamily = $nameTable->getName(3, 1, 0x0409, SetaPDF_Core_Font_TrueType_Table_Name::FAMILY_NAME); // United States

            if ($fontFamily === false) {
                throw new SetaPDF_Core_Font_Exception(
                    'Missing name record for font family (platformId = 3, encodingId = 1)'
                );
            }

            $fontDescriptor->offsetSet('FontFamily', new SetaPDF_Core_Type_String(
                SetaPDF_Core_Encoding::convert($fontFamily, 'UTF-16BE', 'UTF-8')
            ));

            $flags = 0;
            /** @var SetaPDF_Core_Font_TrueType_Table_Post $postTbl */
            $postTbl = $ttfParser->getTable(SetaPDF_Core_Font_TrueType_Table_Tags::POST);
            if ($postTbl->isFixedPitch() !== 0) {
                $flags |= 1 << 0;
            } // 1 = FixedPitch

            if ($headerTbl->hasMacStyle(SetaPDF_Core_Font_TrueType_Table_Header::MAC_STYLE_ITALIC)) {
                $flags |= 1 << 6; // 7 = Italic
            }

            $os2Table = $ttfParser->getTable(SetaPDF_Core_Font_TrueType_Table_Tags::OS2);
            if (!$os2Table instanceof SetaPDF_Core_Font_TrueType_Table_Os2) {
                throw new SetaPDF_Core_Font_TrueType_Subset_Exception(
                    'TrueType does not contain mandatory OS/2 table.'
                );
            }

            $familyClass = $os2Table->getFamilyClass();

            switch ($familyClass[0]) {
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 7:
                    // TODO: Nonsymblic flag should be set when font is embedded/written
                    $flags |= 1 << 5; // 6 = Nonsymbolic
                    $flags |= 1 << 1; // 2 = Serif
                    break;
                case 10:
                    $flags |= 1 << 5; // 6 = Nonsymbolic
                    $flags |= 1 << 3; // 4 = Script
                    break;
                case 12:
                    $flags |= 1 << 2; // 3 = Symbolic
                    break;
                default:
                    $flags |= 1 << 5; // 6 = Nonsymbolic
            }

            $fontDescriptor->offsetSet('Flags', new SetaPDF_Core_Type_Numeric($flags));
            $fontBBox = array_map($f, $headerTbl->getBoundingBox());

            $fontDescriptor->offsetSet('FontBBox', SetaPDF_Core_DataStructure_Rectangle::byArray($fontBBox, true));
            $fontDescriptor->offsetSet('Ascent', new SetaPDF_Core_Type_Numeric($fontBBox[3]));
            $fontDescriptor->offsetSet('Descent', new SetaPDF_Core_Type_Numeric($fontBBox[1]));
            $fontDescriptor->offsetSet('CapHeight', new SetaPDF_Core_Type_Numeric($f($os2Table->getCapHeight())));
            $fontDescriptor->offsetSet('ItalicAngle', new SetaPDF_Core_Type_Numeric($f($postTbl->getItalicAngle())));
            $weightClass = $os2Table->getWeightClass();
            $fontDescriptor->offsetSet('FontWeight', new SetaPDF_Core_Type_Numeric($weightClass));

            /*
             * There seems to be no official way to receive the StemV value of a TTF font.
             * Some set it to 0 (unknown) or use their own ways/fuzzy forms (as we do).
             */
            $stemV = 50 + (int)(($weightClass / 65) ** 2);
            $fontDescriptor->offsetSet('StemV', new SetaPDF_Core_Type_Numeric($stemV));

            $fontDescriptor->offsetSet('MissingWidth', new SetaPDF_Core_Type_Numeric($f($missingWidth)));

            if ($embedded) {
                $streamDict = new SetaPDF_Core_Type_Dictionary();
                $streamDict->offsetSet('Length1', new SetaPDF_Core_Type_Numeric(filesize($fontFile)));
                $streamDict->offsetSet('Filter', new SetaPDF_Core_Type_Name('FlateDecode', true));
                $stream = new SetaPDF_Core_Type_Stream($streamDict);
                $stream->setStream(file_get_contents($fontFile));

                $fontStreamObject = $document->createNewObject($stream);
                $fontDescriptor->offsetSet('FontFile2', $fontStreamObject);
            }

            $document->addCache(
                SetaPDF_Core_Document::CACHE_FONT_DESCRIPTOR, $fontKey, $document->createNewObject($fontDescriptor)
            );
        }

        $dictionary->offsetSet(
            'FontDescriptor',
            $document->getCache(SetaPDF_Core_Document::CACHE_FONT_DESCRIPTOR, $fontKey)
        );

        $fontObject = $document->createNewObject($dictionary);

        $font = new self($fontObject);
        $font->_ttfParser = $ttfParser;
        if ($diffEncoding === 'auto') {
            $font->_autoEncoding = true;
            $dictionary->registerPdfStringCallback([$font, 'updateAutoEncoding'], 'updateAutoEncoding');
        }

        return $font;
    }

    /**
     * Creates the /ToUnicode stream for this TrueType font.
     *
     * @param array $chars
     * @return string
     */
    protected static function _createToUnicodeStream($chars)
    {
        $_chars = [];
        foreach ($chars as $key => $char) {
            $_chars[SetaPDF_Core_BitConverter::formatToUInt8($key)] = $char;
        }

        return (new SetaPDF_Core_Font_ToUnicode($_chars))->create();
    }

    /**
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Exception_NotImplemented
     * @inheritdoc
     */
    public function getGlyphWidthByCharCode($charCode)
    {
        if ($this->_autoEncoding) {
            $charCodeByte = SetaPDF_Core_BitConverter::formatFromUInt8($charCode);
            if (!isset($this->_tmpEncodingTable[$charCodeByte])) {
                return $this->getMissingWidth();
            }

            return $this->getGlyphWidth($this->_tmpEncodingTable[$charCodeByte]);
        }

        return parent::getGlyphWidthByCharCode($charCode);
    }

    /**
     * @inheritdoc
     * @throws SetaPDF_Core_Font_Exception
     */
    public function getCharByCharCode($charCode, $encoding = 'UTF-8')
    {
        if ($this->_autoEncoding){
            $charCodeByte = SetaPDF_Core_BitConverter::formatFromUInt8($charCode);
            if (!isset($this->_tmpEncodingTable[$charCodeByte])) {
                throw new SetaPDF_Core_Font_Exception('Char code is not registered.');
            }

            $char =  $this->_tmpEncodingTable[$charCodeByte];

            if ($encoding !== 'UTF-16BE') {
                $char = SetaPDF_Core_Encoding::convert($char, 'UTF-16BE', $encoding);
            }

            return $char;
        }

        return parent::getCharByCharCode($charCode, $encoding);
    }

    /**
     * Get the glyph width.
     *
     * This method is a proxy method if the width-array is not initialized and
     * the font is build from a TTF font.
     *
     * @param string $char
     * @param string $encoding The input encoding
     * @return float|int
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Exception_NotImplemented|SetaPDF_Core_Type_Exception
     * @see SetaPDF_Core_Font_Type1::getGlyphWidth()
     */
    public function getGlyphWidth($char, $encoding = 'UTF-16BE')
    {
        if ($this->_ttfParser === null) {
            return parent::getGlyphWidth($char, $encoding);
        }

        if ($encoding !== 'UTF-16BE') {
            $char = SetaPDF_Core_Encoding::convert($char, $encoding, 'UTF-16BE');
        }

        /** @var SetaPDF_Core_Font_TrueType_Table_Header $headerTbl */
        $headerTbl = $this->_ttfParser->getTable(SetaPDF_Core_Font_TrueType_Table_Tags::HEADER);
        $factor = 1000 / $headerTbl->getUnitsPerEm();

        return round($this->_ttfParser->getWidth($char) * $factor);
    }

    /**
     * Get the final character code of a single character.
     *
     * If the font is based on a TTF file and the $diffEncoding is set to 'auto'
     * this method will build the differences from the encoding automatically.
     * It will simply recreate a completely new encoding starting at 0.
     *
     * @param string $char The character
     * @param string $encoding
     * @return string
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Exception_NotImplemented
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getCharCode($char, $encoding = 'UTF-16BE')
    {
        if ($this->_ttfParser === null || $this->_autoEncoding === false) {
            return parent::getCharCode($char, $encoding);
        }

        if ($encoding !== 'UTF-16BE') {
            $char = SetaPDF_Core_Encoding::convert($char, $encoding, 'UTF-16BE');
        }

        $offset = 0;
        $code = array_search($char, $this->_tmpEncodingTable, true);
        if ($code === false) {
            $code = count($this->_tmpEncodingTable);
            $position = $code + $offset;

            if ($position > 255) {
                throw new SetaPDF_Core_Font_Exception(
                    'Font with auto-encoding reaches chars limit of 255 chars!'
                );
            }

            if ($this->_ttfParser->isCharCovered($char) === false) {
                throw new SetaPDF_Core_Font_Exception(
                    sprintf(
                        'Font (%s) does not cover the needed glyph (%s).',
                        $this->getFontName(),
                        SetaPDF_Core_Font_Glyph_List::byUtf16Be($char)
                    )
                );
            }

            $this->_tmpEncodingTable[] = $char;

            $dictionary = $this->getDictionary();
            $widths = SetaPDF_Core_Type_Array::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Widths')
            );
            /** @var SetaPDF_Core_Font_TrueType_Table_Header $headerTbl */
            $headerTbl = $this->_ttfParser->getTable(SetaPDF_Core_Font_TrueType_Table_Tags::HEADER);
            $factor = 1000 / $headerTbl->getUnitsPerEm();
            $widths->offsetSet(null, new SetaPDF_Core_Type_Numeric(
                round($this->_ttfParser->getWidth($char) * $factor))
            );
            SetaPDF_Core_Type_Numeric::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'LastChar')
            )->setValue($position);

            $encodingDict = SetaPDF_Core_Type_Dictionary::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Encoding')
            );
            $differences = SetaPDF_Core_Type_Array::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($encodingDict, 'Differences')
            );

            if ($differences->count() === 0) {
                $differences[] = new SetaPDF_Core_Type_Numeric($offset);
            }

            $differences[] = new SetaPDF_Core_Type_Name(SetaPDF_Core_Font_Glyph_List::byUtf16Be($char));
        }

        return chr($offset + $code);
    }

    /**
     * A callback function which will update font data before it is written to the final PDF file.
     *
     * This method should not be called manually. It is registered as a callback of the
     * font object, which was created in the create()-method.
     *
     * @throws SetaPDF_Core_Type_Exception
     */
    public function updateAutoEncoding()
    {
        if ($this->_autoEncoding === false) {
            throw new BadMethodCallException(
                'This method is only callable if the font encoding is set to "auto".'
            );
        }

        $this->_updateToUnicodeStream();
    }

    /**
     * A function which will create the current ToUnicode CMap.
     *
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _updateToUnicodeStream()
    {
        $dictionary = $this->getDictionary();
        if (count($this->_tmpEncodingTable) === 0) {
            $dictionary->offsetUnset('ToUnicode');
            return;
        }

        $toUnicodeStream = SetaPDF_Core_Type_Stream::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'ToUnicode')
        );
        $toUnicodeStream->setStream(self::_createToUnicodeStream($this->_tmpEncodingTable));
    }

    /**
     * Get the base encoding for a TrueType font.
     *
     * See PDF 32000-1:2008 - 9.6.6.4 Encodings for TrueType Fonts:
     * "[...]A nonsymbolic font should specify MacRomanEncoding or WinAnsiEncoding as the
     * value of its Encoding entry, with no Differences array[...]"
     *
     * @return array
     * @throws SetaPDF_Core_Font_Exception
     */
    public function getBaseEncodingTable()
    {
        /* PDF 32000-1:2008 - Table 114: "Otherwise, for a nonsymbolic font, it shall be StandardEncoding, and for a
         * symbolic font, it shall be the font’s built-in encoding."
         */
        $fontDescriptor = $this->getFontDescriptor();
        if ($fontDescriptor instanceof SetaPDF_Core_Font_Descriptor && ($fontDescriptor->getFlags() & 4) === 4) {
            return SetaPDF_Core_Encoding_WinAnsi::$table;
        }

        return SetaPDF_Core_Encoding_Standard::$table;
    }

    /**
     * @inheritDoc
     *
     * @throws SetaPDF_Core_Font_Exception
     * @throws SetaPDF_Exception_NotImplemented
     * @throws SetaPDF_Core_Exception
     */
    public function recalculateFontBBox()
    {
        if ($this->_calculatedFontBBox) {
            return $this->_calculatedFontBBox;
        }

        $parser = $this->getStreamParser();
        if ($parser) {
            /** @var SetaPDF_Core_Font_TrueType_Table_Header $headerTbl */
            $headerTbl = $parser->getTable(SetaPDF_Core_Font_TrueType_Table_Tags::HEADER);
            $f = 1000 / $headerTbl->getUnitsPerEm();
            $this->_calculatedFontBBox = array_map(static function ($v) use ($f) {
                return $v * $f;
            }, $headerTbl->getBoundingBox(true));

            return $this->_calculatedFontBBox;
        }
        return false;
    }

    /**
     * Get the TTF/OTF parser for the embedded font programm.
     *
     * @return false|SetaPDF_Core_Font_TrueType_File
     * @throws SetaPDF_Core_Exception
     */
    public function getStreamParser()
    {
        if ($this->_streamParser === null) {
            $fontDescriptor = $this->getFontDescriptor();
            if (!$fontDescriptor) {
                return false;
            }

            $fontFile2 = $fontDescriptor->getFontFile2();
            if ($fontFile2) {
                try {
                    $fontFile2 = new SetaPDF_Core_Reader_Binary(new SetaPDF_Core_Reader_String(
                        $fontFile2->getStream()
                    ));
                    $this->_streamParser = new SetaPDF_Core_Font_TrueType_File($fontFile2);
                } catch (SetaPDF_Exception_NotImplemented $e) {
                    return false;
                }
            // TODO FontFile3 / OTF
            } else {
                return false;
            }
        }

        return $this->_streamParser;
    }

    /**
     * @inheritdoc
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Exception_NotImplemented
     */
    protected function _ensureUnderlinePosition()
    {
        $parser = $this->getStreamParser();
        if ($parser === false) {
            return parent::_ensureUnderlinePosition();
        }

        /** @var SetaPDF_Core_Font_TrueType_Table_Post $postTbl */
        $postTbl = $parser->getTable(SetaPDF_Core_Font_TrueType_Table_Post::TAG);
        return $postTbl->getUnderlinePosition();
    }

    /**
     * @inheritdoc
     * @throws SetaPDF_Exception_NotImplemented
     * @throws SetaPDF_Core_Exception
     */
    protected function _ensureUnderlineThickness()
    {
        $parser = $this->getStreamParser();
        if ($parser === false) {
            return parent::_ensureUnderlineThickness();
        }

        /** @var SetaPDF_Core_Font_TrueType_Table_Post $postTbl */
        $postTbl = $parser->getTable(SetaPDF_Core_Font_TrueType_Table_Post::TAG);
        return $postTbl->getUnderlineThickness();
    }
}
