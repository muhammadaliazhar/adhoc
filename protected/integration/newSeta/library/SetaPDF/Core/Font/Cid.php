<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Cid.php 1748 2022-06-21 15:36:06Z jan.slabon $
 */

/**
 * Abstract class representing a CID font
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Core_Font_Cid implements SetaPDF_Core_Font_DescriptorInterface
{
    /**
     * The indirect object of the CID font
     *
     * @var SetaPDF_Core_Type_IndirectObjectInterface
     */
    protected $_indirectObject;

    /**
     * The dictionary of the CID font
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * The font descriptor object
     *
     * @var SetaPDF_Core_Font_Descriptor
     */
    protected $_fontDescriptor;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface $indirectObjectOrDictionary
     */
    public function __construct($indirectObjectOrDictionary)
    {
        if ($indirectObjectOrDictionary instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            $this->_indirectObject = $indirectObjectOrDictionary;
        }

        $this->_dictionary = $indirectObjectOrDictionary->ensure();
    }

    /**
     * Gets an indirect object for this font.
     *
     * @see SetaPDF_Core_Resource::getIndirectObject()
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws InvalidArgumentException
     */
    public function getIndirectObject(SetaPDF_Core_Document $document = null)
    {
        if ($this->_indirectObject === null) {
            if ($document === null) {
                throw new InvalidArgumentException('To initialize a new object $document parameter is not optional!');
            }

            $this->_indirectObject = $document->createNewObject($this->_dictionary);
        }

        return $this->_indirectObject;
    }

    /**
     * Get the font dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        return $this->_dictionary;
    }

    /**
     * Get the Subtype entry of the font dictionary.
     *
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getType()
    {
        $type = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Subtype');
        return SetaPDF_Core_Type_Name::ensureType($type)->getValue();
    }

    /**
     * Get the font name.
     *
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getFontName()
    {
        $baseFont = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'BaseFont');
        return SetaPDF_Core_Type_Name::ensureType($baseFont)->getValue();
    }

    /**
     * Get an array with entries that define the character collection of the CIDFont.
     *
     * @return array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getCidSystemInfo()
    {
        $cidSystemInfo = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'CIDSystemInfo');
        $result = [];
        foreach (SetaPDF_Core_Type_Dictionary::ensureType($cidSystemInfo) as $key => $value) {
            $result[$key] = $value->ensure()->getValue();
        }

        return $result;
    }

    /**
     * Get the default width for glyphs in the CIDFont.
     *
     * @return integer
     */
    public function getDefaultWidth()
    {
        $dw = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'DW');
        if (!$dw instanceof SetaPDF_Core_Type_Numeric) {
            return 1000;
        }

        return (int)$dw->getValue();
    }

    /**
     * Get the vertical metrics in the CIDFont.
     *
     * @return int[]
     */
    public function getVerticalMetrics()
    {
        $dw2 = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'DW2');
        if (!$dw2 instanceof SetaPDF_Core_Type_Array) {
            return [880, -1000];
        }

        return $dw2->toPhp(true);
    }

    /**
     * Get the font descriptor object.
     *
     * @return SetaPDF_Core_Font_Descriptor
     * @throws SetaPDF_Core_Font_Exception
     */
    public function getFontDescriptor()
    {
        if ($this->_fontDescriptor === null) {
            /** @var SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary $fontDescriptor */
            $fontDescriptor = $this->getDictionary()->getValue('FontDescriptor');
            $this->_fontDescriptor = new SetaPDF_Core_Font_Descriptor($fontDescriptor);
        }

        return $this->_fontDescriptor;
    }

    /**
     * Get the width of a glyph/character.
     *
     * @param integer $cid
     * @return float|int
     */
    public function getGlyphWidth($cid)
    {
        /* Note: This method does not use any caching but is that fast (or faster) than with caching.
         */
        $w = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'W');
        if ($w instanceof SetaPDF_Core_Type_Array) {
            for ($i = 0, $c = count($w); $i < $c;) {
                if ($w[$i + 1]->ensure() instanceof SetaPDF_Core_Type_Array) {
                    $start  = $w[$i++]->ensure()->getValue();
                    $widths = $w[$i++]->ensure();

                    if ($cid >= $start && $cid < $start + count($widths)) {
                        return $widths[$cid - $start]->ensure()->getValue();
                    }
                } else {
                    $start = $w[$i++]->ensure()->getValue();
                    $end   = $w[$i++]->ensure()->getValue();
                    $width = $w[$i++]->ensure()->getValue();
                    if ($cid >= $start && $cid <= $end) {
                        return $width;
                    }
                }
            }
        }

        return $this->getDefaultWidth();
    }
}
