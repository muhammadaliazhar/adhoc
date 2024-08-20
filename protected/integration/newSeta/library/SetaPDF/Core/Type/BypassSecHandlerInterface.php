<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: StringValue.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Interface for types that can bypass a security handler
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 * @see SetaPDF_Core_Type_String, SetaPDF_Core_Type_HexString
 */
interface SetaPDF_Core_Type_BypassSecHandlerInterface
{
    /**
     * Bypass the security handler or not.
     *
     * @param boolean $bypassSecHandler
     */
    public function setBypassSecHandler($bypassSecHandler = true);
}
