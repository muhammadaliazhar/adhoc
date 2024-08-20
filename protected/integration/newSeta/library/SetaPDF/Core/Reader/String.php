<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Reader
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: String.php 1781 2022-09-27 10:50:21Z jan.slabon $
 */

/**
 * Class for a string reader
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Reader
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Reader_String
    extends SetaPDF_Core_Reader_Stream
{
    /**
     * The complete string.
     *
     * @var string
     */
    protected $_string = '';

    /**
     * The constructor.
     *
     * @param string $string
     * @throws SetaPDF_Core_Reader_Exception
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct($string)
    {
        $this->setString($string);
    }

    /**
     * @throws SetaPDF_Core_Reader_Exception
     */
    public function __sleep()
    {
        $this->_string = $this->getString();
        return ['_string'];
    }

    /**
     * @throws SetaPDF_Core_Reader_Exception
     */
    public function __wakeup()
    {
        $this->setString($this->_string);
        $this->_string = '';
    }

    /**
     * Returns the complete string.
     *
     * @return string
     * @throws SetaPDF_Core_Reader_Exception
     */
    public function __toString()
    {
        return $this->getString();
    }

    /**
     * Set the string.
     *
     * @param string $string
     * @throws SetaPDF_Core_Reader_Exception
     */
    public function setString($string)
    {
        $stream = fopen('php://temp', 'wb+');
        fwrite($stream, $string);

        $this->_setStream($stream);
    }

    /**
     * Get the complete string.
     *
     * @return string
     * @throws SetaPDF_Core_Reader_Exception
     */
    public function getString()
    {
        $pos = $this->getPos();
        rewind($this->_stream);

        $string = stream_get_contents($this->_stream);

        $this->reset($pos);

        return $string;
    }

    /**
     * Close the file handle.
     *
     * @see SetaPDF_Core_Reader_ReaderInterface::cleanUp()
     */
    public function cleanUp()
    {
        if (is_resource($this->_stream)) {
            fclose($this->_stream);
        }
    }
}
