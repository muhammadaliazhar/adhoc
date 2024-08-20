<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Echo.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * A writer class which uses simple echo calls
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Writer_Echo
    extends SetaPDF_Core_Writer_AbstractWriter
    implements SetaPDF_Core_Writer_WriterInterface
{
    /**
     * The current position
     *
     * @var integer
     */
    protected $_pos = 0;

    /**
     * Echo the string.
     *
     * @param string $s
     */
    public function write($s)
    {
        echo $s;
        $this->_pos += strlen($s);
        flush();
    }

    /**
     * Returns the current position.
     *
     * @return integer
     */
    public function getPos()
    {
        return $this->_pos;
    }
}