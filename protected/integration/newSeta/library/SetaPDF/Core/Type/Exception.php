<?php
/**
 * This file is part of the SetaPDF-Core Component
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Exception.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Type exception
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Type_Exception extends SetaPDF_Core_Exception
{
    /** Constants prefix: 0x07 **/

    const INVALID_DATA_TYPE = 0x0700;

    const INVALID_DATA_SIZE = 0x0701;
}
