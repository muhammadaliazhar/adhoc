<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Http.php 1789 2022-11-09 11:19:00Z jan.slabon $
 */

/**
 * A writer class for HTTP delivery
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Writer
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Writer_Http
    extends SetaPDF_Core_Writer_TempStream
{
    /**
     * Chunk size that is output at once.
     *
     * @var int
     */
    protected $_outputChunkSize = 4 * 1024 * 1024;

    /**
     * Encodes the file name for the http header.
     *
     * @param string $filename
     * @return string
     */
    public static function encodeFilenameForHttpHeader($filename)
    {
        // simulate basename()
        preg_match('~[^/\\\\]*$~D', $filename, $matches);
        $filename = str_replace(['"', "\n", "\r"], ['_', '', ''], end($matches));

        // see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
        // see https://tools.ietf.org/html/rfc5987#section-4.2
        return sprintf('filename="%s"', SetaPDF_Core_Encoding::convert($filename, 'UTF-8', 'ISO-8859-1')) . '; ' .
            sprintf("filename*=utf-8''%s", rawurlencode($filename));
    }

    /**
     * The document filename
     *
     * @var string
     */
    protected $_filename = 'document.pdf';

    /**
     * Flag saying that the file should be displayed inline or not
     *
     * @var boolean
     */
    protected $_inline = false;

    /**
     * The constructor.
     *
     * @param string $filename The document filename in UTF-8 encoding
     * @param boolean $inline Defines if the document should be displayed inline or if a download should be forced
     */
    public function __construct($filename = 'document.pdf', $inline = false)
    {
        $this->_filename = $filename;
        $this->_inline = $inline;
        parent::__construct();
    }

    /**
     * Set the chunk size of the data that is read from the internal stream and echoed.
     *
     * The default value is 4 MB. You may increase this which may speed up the delivery of large files but
     * will also increase the memory usage respectively.
     *
     * @param int $outputChunkSize Size in bytes.
     */
    public function setOutputChunkSize($outputChunkSize)
    {
        $this->_outputChunkSize = (int)$outputChunkSize;
    }

    /**
     * This method is called when the writing process is finished.
     *
     * It sends the HTTP headers and send the buffer to the client.
     *
     * @throws SetaPDF_Core_Writer_Exception
     */
    public function finish()
    {
        if (PHP_SAPI !== 'cli' && headers_sent($filename, $line)) {
            throw new SetaPDF_Core_Writer_Exception(
                sprintf('Headers already been send in %s on line %s', $filename, $line)
            );
        }

        if (ob_get_length() > 0) {
            // Check at least for UTF-8 BOM and white spaces
            if (preg_match('/^(\xEF\xBB\xBF)?\s*$/', ob_get_contents())) {
                ob_clean();
            } else {
                throw new SetaPDF_Core_Writer_Exception(
                    "Some data has already been output. PDF couldn't be send."
                );
            }
        }

        parent::finish();

        $length = $this->getPos();

        if (PHP_SAPI !== 'cli') {
            header('Content-Type: application/pdf');
            if ($this->_inline === true) {
                header('Content-Disposition: inline; ' . self::encodeFilenameForHttpHeader($this->_filename));
            } else {
                header('Content-Disposition: attachment; ' . self::encodeFilenameForHttpHeader($this->_filename));
            }

            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $length);
            header('Accept-Ranges: none');
        }

        $fh = $this->getHandle();
        rewind($fh);

        while (!feof($fh)) {
            echo fread($fh, min($length, $this->_outputChunkSize));
        }

        flush();
    }
}