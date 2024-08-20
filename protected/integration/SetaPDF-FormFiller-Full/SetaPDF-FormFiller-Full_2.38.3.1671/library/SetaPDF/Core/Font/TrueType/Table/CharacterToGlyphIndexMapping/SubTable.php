<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: SubTable.php 1665 2021-11-30 16:24:58Z maximilian.kresse $
 */

/**
 * A class representing a subtable of a Character To Glyph Index Mapping Table.
 *
 * @copyright  Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Font_TrueType_Table_CharacterToGlyphIndexMapping_SubTable extends SetaPDF_Core_Font_TrueType_Table
{
    /**
     * The entries in this subtable
     *
     * @var array
     */
    protected $_entries = [
        'format' => [0, SetaPDF_Core_BitConverter::USHORT],
        'length' => [2, SetaPDF_Core_BitConverter::USHORT],
        'language' => [4, SetaPDF_Core_BitConverter::USHORT],
    ];

    /**
     * Get the format of this subtable.
     *
     * @return int
     */
    public function getFormat()
    {
        return $this->_get('format');
    }

    /**
     * Get the length of this subtable.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->_get('length');
    }

    /**
     * Get the language of this subtable.
     *
     * @return int
     */
    public function getLanguage()
    {
        return $this->_get('language');
    }

    /**
     * Get the glyph index by a character code.
     *
     * @param int $charCode
     * @return int
     * @throws SetaPDF_Exception_NotImplemented
     */
    public function getGlyphIndex($charCode)
    {
        throw new SetaPDF_Exception_NotImplemented(
            'The mapping table (format ' . $this->getFormat() . ') is not implemented yet.'
        );
    }
}