<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: DescriptorInterface.php 1663 2021-11-03 07:32:26Z jan.slabon $
 */

/**
 * Interface for fonts with a font descriptor.
 *
 * @copyright  Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Font
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Core_Font_DescriptorInterface
{
    /**
     * Get the font descriptor object of this font.
     *
     * @return SetaPDF_Core_Font_Descriptor
     */
    public function getFontDescriptor();
}