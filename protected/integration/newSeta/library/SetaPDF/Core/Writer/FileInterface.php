<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: FileInterface.php 1732 2022-05-31 15:17:55Z jan.slabon $
 */

/**
 * An interface for writer classes which work with files.
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_Core_Writer_FileInterface extends SetaPDF_Core_Writer_WriterInterface
{
    /**
     * Get the path of the file.
     *
     * @return string
     */
    public function getPath();
}
