<?php 
/**
 * This file is part of the SetaPDF-Core Component
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Outlines.php 1739 2022-06-16 13:56:09Z jan.slabon $
 */

/**
 * Class for handling a documents outline
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_Outlines implements IteratorAggregate, ArrayAccess
{
    /**
     * The documents catalog instance.
     *
     * @var SetaPDF_Core_Document_Catalog
     */
    protected $_catalog;
    
    /**
     * The root outlines dictionary.
     * 
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_outlinesDictionary;
    
    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document_Catalog $catalog
     */
    public function __construct(SetaPDF_Core_Document_Catalog $catalog)
    {
        $this->_catalog = $catalog;
    }
    
    /**
     * Get the document instance.
     *
     * @return SetaPDF_Core_Document
     */
    public function getDocument()
    {
        return $this->_catalog->getDocument();
    }
    
    /**
     * Release memory / Cycled references.
     */
    public function cleanUp()
    {
        $this->_outlinesDictionary = null;
        $this->_catalog = null;
    }

    /**
     * Get and creates the Outlines dictionary.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @internal
     */
    public function getOutlinesDictionary($create = false)
    {
        if ($this->_outlinesDictionary === null) {
            $catalog = $this->getDocument()->getCatalog()->getDictionary($create);
            if ($catalog === null) {
                return null;
            }

            $outlines = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'Outlines');
            if ($outlines instanceof SetaPDF_Core_Type_Dictionary) {
                $this->_outlinesDictionary = $outlines;
                return $this->_outlinesDictionary;
            }

            if ($create) {
                $this->_outlinesDictionary = new SetaPDF_Core_Type_Dictionary();
                $this->_outlinesDictionary->offsetSet('Type', new SetaPDF_Core_Type_Name('Outlines', true));

                $outlines = $this->getDocument()->createNewObject($this->_outlinesDictionary);
                $catalog->offsetSet('Outlines', $outlines);
            }
        }
        
        return $this->_outlinesDictionary;
    }

    /**
     * Get an item instance of the item referenced in the 'First' key.
     *
     * @return bool|SetaPDF_Core_Document_OutlinesItem The first item of the outlines root dictionary or false if no
     *                                                    item is set.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getFirstItem()
    {
        $dict = $this->getOutlinesDictionary();
        if ($dict && $dict->offsetExists('First')) {
            /** @var SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary $first */
            $first = $dict->getValue('First');
            return new SetaPDF_Core_Document_OutlinesItem($first);
        }
        
        return false;
    }

    /**
     * Get an item instance of the item referenced in the 'Last' key.
     *
     * @return bool|SetaPDF_Core_Document_OutlinesItem The last item of the outlines root dictionary or false if no
     *                                                    item is set.
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getLastItem()
    {
        $dict = $this->getOutlinesDictionary();
        if ($dict && $dict->offsetExists('Last')) {
            /** @var SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary $last */
            $last = $dict->getValue('Last');
            return new SetaPDF_Core_Document_OutlinesItem($last);
        }
    
        return false;
    }

    /**
     * Get the iterator reference for the outlines.
     *
     * @return EmptyIterator|RecursiveIteratorIterator A reference to the iterator
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see IteratorAggregate::getIterator()
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $firstItem = $this->getFirstItem();
        if ($firstItem === false) {
            return new EmptyIterator();
        }

        return new RecursiveIteratorIterator(
            $firstItem,
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * Append an item to the outline.
     *
     * @param SetaPDF_Core_Document_OutlinesItem $item The outline item that should be appended
     * @return SetaPDF_Core_Document_Catalog_Outlines
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function appendChild(SetaPDF_Core_Document_OutlinesItem $item)
    {
        $lastItem = $this->getLastItem();
        
        if ($lastItem === false) {
            $value = $item->getReferenceTo();
            $dict = SetaPDF_Core_Type_Dictionary::ensureType($this->getOutlinesDictionary(true));
            $dict->offsetSet('First', $value);
            $dict->offsetSet('Last', $value);
            $count = 1; // the root item is visible throughout
            foreach ($item as $next) {
                $count += $next->getCount();
            }

            $dict->offsetSet('Count', new SetaPDF_Core_Type_Numeric($count));

            $catalog = SetaPDF_Core_Type_Dictionary::ensureType($this->getDocument()->getCatalog()->getDictionary(true));
            $value->ensure()->offsetSet('Parent', $catalog->getValue('Outlines'));

        } else {
            $lastItem->append($item);
        }
        
        return $this;
    }

    /**
     * Append a copy of an item or outline to this outline.
     *
     * @param SetaPDF_Core_Document_OutlinesItem|SetaPDF_Core_Document_Catalog_Outlines $item The item or root outlines dictionary
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function appendChildCopy($item)
    {
        if ($item instanceof self) {
            $iterator = $item->getIterator();
            if ($iterator instanceof RecursiveIteratorIterator) {
                $iterator->setMaxDepth(0);
            }

            $childs = [];
            foreach ($iterator AS $_item) {
                $childs[] = $_item;
            }
            
            foreach ($childs AS $_item) {
                $this->appendChildCopy($_item);
            }
            
            if ($iterator instanceof RecursiveIteratorIterator) {
                $iterator->setMaxDepth(-1);
            }
            return;
        }
        
        $lastItem = $this->getLastItem();
        if ($lastItem === false) {
            $root = SetaPDF_Core_Document_OutlinesItem::copyItem($this->getDocument(), $item);
            $this->appendChild($root);
            if ($item->hasFirstItem()) {
                foreach ($item->getFirstItem() AS $_item) {
                    $root->appendChildCopy($_item, $this->getDocument());
                }
            }
        } else {
            $lastItem->appendCopy($item, $this->getDocument());
        }
    }
    
  /* ArrayAccess Implementation */

    /**
     * Checks if an item exists at a specific position.
     *
     * @param string $offset
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see ArrayAccess::offsetExists()
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        try {
            $this->offsetGet($offset);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Set an item at a specific position.
     *
     * @param null|string $offset
     * @param SetaPDF_Core_Document_OutlinesItem $value
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see ArrayAccess::offsetSet()
     * @see append()
     * @see appendChild()
     * @see remove()
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $lastItem = $this->getLastItem();
            if ($lastItem === false) {
                $this->appendChild($value);
                return;
            }
            $lastItem->append($value);
            return;
        }
        
        $current = $this->offsetGet($offset);
        $prev = $current->getPrevious();
        if ($prev) {
            $current->remove();
            $prev->append($value);
            return;
        } 
        
        $next = $current->getNext();
        $current->remove();

        if ($next) {
            $next->prepend($value);
            return;
        }

        $this->appendChild($value);
    }

    /**
     * Get an item by a specific position.
     *
     * @param string $offset
     * @return SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see ArrayAccess::offsetGet()
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (is_numeric($offset)) {
            $item = $this->getFirstItem();
            for ($n = 0; $n < $offset && $item !== false; $n++) {
                $item = $item->getNext();
            }
        } elseif ($offset === 'first') {
            $item = $this->getFirstItem();
        } elseif ($offset === 'last') {
            $item = $this->getLastItem();
        } else {
            $item = false;
        }

        if ($item === false) {
            throw new InvalidArgumentException(sprintf('No item at offset "%s" found.', $offset));
        }
        
        return $item;
    }

    /**
     * Removes an item at a specific position.
     *
     * @param string $offset
     * @return SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see ArrayAccess::offsetUnset()
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $item = $this->offsetGet($offset);
        $item->remove();
        
        return $item;
    }
}
