<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: CidType2.php 1748 2022-06-21 15:36:06Z jan.slabon $
 */

/**
 * Class representing a Type 2 CID font
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Font_CidType2 extends SetaPDF_Core_Font_Cid
{
    /**
     * Get the mapping from CIDs to glyph indices.
     *
     * @return string
     */
    public function getCidToGidMap()
    {
        $cidToGidMap = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'CIDToGIDMap');
        if ($cidToGidMap === null) {
            return 'Identity';
        }

        if ($cidToGidMap instanceof SetaPDF_Core_Type_Stream) {
            return $cidToGidMap->getStream();
        }

        return $cidToGidMap->getValue();
    }
}
