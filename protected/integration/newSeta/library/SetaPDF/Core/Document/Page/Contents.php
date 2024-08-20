<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Contents.php 1742 2022-06-20 14:29:40Z jan.slabon $
 */

/**
 * A class representing a pages content
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Contents
    implements Countable, SetaPDF_Core_Canvas_StreamProxyInterface
{
    /**
     * The page object to which this helper instance depends on
     * 
     * @var SetaPDF_Core_Document_Page
     */
    protected $_page;
    
    /**
     * The current content stream offset
     * 
     * @var int
     */
    protected $_currentOffset;
    
    /**
     * The current active content stream
     * 
     * @var SetaPDF_Core_Type_Stream
     */
    protected $_currentStream;
    
    /**
     * Flag saying if the content is already encapsulated in a graphic state
     *  
     * @var bool
     */
    protected $_encapsulatedInGraphicState = false;

    /**
     * The constructor.
     * 
     * @param SetaPDF_Core_Document_Page $page
     */
    public function __construct(SetaPDF_Core_Document_Page $page)
    {
        $this->_page = $page;
    }

    /**
     * Release memory/resources.
     */
    public function cleanUp()
    {
        $this->_page = null;
        $this->_currentStream = null;
    }

    /**
     * Writes a string to the stream object.
     *
     * @param string $bytes
     * @throws SetaPDF_Core_Type_Exception
     */
    public function write($bytes)
    {
        $stream = $this->getStreamObject(true);
        $stream->write($bytes);
    }

    /**
     * Clears the stream object.
     */
    public function clear()
    {
        for ($offset = 0, $count = $this->count(); $offset < $count; $offset++) {
            $stream = $this->getStreamObjectByOffset($offset);
            if ($stream !== false) {
                $stream->clear();
            }
        }
    }
    
    /**
     * Gets the count of contents streams available for this page.
     * 
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        $contents = $this->_page->getAttribute('Contents', false);
        if ($contents === null) {
            return 0;
        }
    
        $contents = $contents->ensure();
        if ($contents instanceof SetaPDF_Core_Type_Array) {
            return $contents->count();
        }
    
        return 1;
    }

    /**
     * Get the stream object.
     *
     * @param bool $create
     * @return bool|SetaPDF_Core_Type_Stream
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getStreamObject($create = false)
    {
        if ($this->_currentStream === null) {
            $stream = $this->getLastStreamObject($create);
            if ($stream === false) {
                return false;
            }
        }

        return $this->_currentStream;
    }

    /**
     * Get the stream content.
     *
     * @return string
     */
    public function getStream()
    {
        $streams = [];
        
        for ($offset = 0, $count = $this->count(); $offset < $count; $offset++) {
            $streamObject = $this->getStreamObjectByOffset($offset);
            if ($streamObject !== false) {
                $streams[] = $streamObject->getStream();
            }
        }
        
        return implode("\n", $streams);
    }
    
    /**
     * Get a stream by offset in the contents array.
     *
     * @param int $offset
     * @param bool $setActive
     * @return bool|SetaPDF_Core_Type_Stream
     */
    public function getStreamObjectByOffset($offset = 0, $setActive = true)
    {
        $contents = $this->_page->getAttribute('Contents', false);
        
        if ($contents === null) {
            return false;
        }
        
        $contents = $contents->ensure(true);
        if (!($contents instanceof SetaPDF_Core_Type_Array)) {
            if ($offset !== 0) {
                return false;
            }
            
            $stream = $contents;
        } else {
            $stream = $contents->offsetGet($offset);
            if ($stream === null) {
                return false;
            }

            $stream = $stream->ensure(true);
        }

        if (!($stream instanceof SetaPDF_Core_Type_Stream)) {
            return false;
        }

        if ($setActive) {
            $this->_currentOffset = $offset;
            $this->_currentStream = $stream;
        }

        return $stream;
    }

    /**
     * Get and/or create the last stream.
     *
     * @param bool $create
     * @param bool $setActive
     * @return bool|SetaPDF_Core_Type_Stream
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getLastStreamObject($create = false, $setActive = true)
    {
        $count = $this->count();
        if ($count === 0 || ($stream = $this->getStreamObjectByOffset($count - 1, $setActive)) === false) {
            if ($create === false) {
                return false;
            }
            return SetaPDF_Core_Type_Stream::ensureType($this->pushStream($setActive));
        }

        return $stream;
    }
    
    /**
     * Checks if the last content stream is active.
     * 
     * @return bool
     */
    public function isLastStreamActive()
    {
        return $this->_currentOffset === ($this->count() - 1);
    }

    /**
     * Method for adding streams to the Contents entry.
     *
     * @param int|null $beforeIndex
     * @param bool $setActive
     * @param SetaPDF_Core_Type_IndirectObjectInterface $streamObject
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _addStream($beforeIndex, $setActive = true, SetaPDF_Core_Type_IndirectObjectInterface $streamObject = null)
    {
        $pageDict = SetaPDF_Core_Type_Dictionary::ensureType($this->_page->getPageObject(true));
        $contents = $this->_page->getAttribute('Contents', false);
        if ($contents === null) {
            $contents = new SetaPDF_Core_Type_Array();
            $pageDict->offsetSet('Contents', $contents);
        } elseif (!$contents->ensure(true) instanceof SetaPDF_Core_Type_Array) {
            $contents = new SetaPDF_Core_Type_Array([clone $contents->getValue()]);
            $pageDict->offsetSet('Contents', $contents);
        } else {
            $contents = $contents->ensure(true);
        }
        
        if ($streamObject === null) {
            $document = $this->_page->getPageObject(true)->getOwnerPdfDocument();
            $stream = new SetaPDF_Core_Type_Stream();
            $stream->getValue()->offsetSet('Filter', new SetaPDF_Core_Type_Name('FlateDecode', true));
            $streamObject = $document->createNewObject($stream);
        }
        
        if ($beforeIndex === null) {
            $contents->push($streamObject);
        } else {
            $contents->insertBefore($streamObject, $beforeIndex);
        }

        if ($setActive === true) {
            $this->_currentOffset = $beforeIndex === null ? $contents->count() - 1 : $beforeIndex;
            $this->_currentStream = $streamObject->ensure();
        }
        
        return $streamObject;
    }

    /**
     * Append a stream to the end of the Contents array.
     *
     * @param bool $setActive
     * @param SetaPDF_Core_Type_IndirectObjectInterface $streamObject
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws SetaPDF_Core_Type_Exception
     */
    public function pushStream($setActive = true, SetaPDF_Core_Type_IndirectObjectInterface $streamObject = null)
    {
        return $this->_addStream(null, $setActive, $streamObject);
    }

    /**
     * Prepend a stream to the beginning of the Contents array.
     *
     * @param bool $setActive
     * @param SetaPDF_Core_Type_IndirectObjectInterface $streamObject
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws SetaPDF_Core_Type_Exception
     */
    public function prependStream($setActive = true, SetaPDF_Core_Type_IndirectObjectInterface $streamObject = null)
    {
        return $this->_addStream(0, $setActive, $streamObject);
    }

    /**
     * Encapsulate the existing content stream(s) in separate graphic state operators.
     *
     * @param bool $force
     * @throws SetaPDF_Core_Type_Exception
     */
    public function encapsulateExistingContentInGraphicState($force = false)
    {
        if ($this->_encapsulatedInGraphicState === false || $force === true) {
            $document = $this->_page->getPageObject(true)->getOwnerPdfDocument();
            if (!$document->hasCache(SetaPDF_Core_Document::CACHE_ENCAPSULATED_CONTENT_STREAMS, 'prepend')) {
                $stream = $this->prependStream();

                $document->addCache(SetaPDF_Core_Document::CACHE_ENCAPSULATED_CONTENT_STREAMS, 'prepend', $stream);
                $stream = SetaPDF_Core_Type_Stream::ensureType($stream);
                $stream->setStream(' q ');
            } else {
                $this->prependStream(true,
                    $document->getCache(SetaPDF_Core_Document::CACHE_ENCAPSULATED_CONTENT_STREAMS, 'prepend')
                );
            }
            
            SetaPDF_Core_Type_Stream::ensureType($this->pushStream())->setStream(' Q ');
            
            $this->_encapsulatedInGraphicState = true;
        }
    }
}
