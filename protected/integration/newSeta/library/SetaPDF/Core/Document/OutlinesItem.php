<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: OutlinesItem.php 1753 2022-06-28 14:42:54Z maximilian.kresse $
 */

/**
 * Class representing an outline item
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_OutlinesItem implements RecursiveIterator, ArrayAccess
{
    /**
     * Configuration key
     *
     * @var string
     */
    const DEST = 'destination';

    /**
     * Configuration key
     *
     * @var string
     */
    const ACTION = 'action';

    /**
     * Configuration key
     *
     * @var string
     */
    const COLOR = 'color';

    /**
     * Configuration key
     *
     * @var string
     */
    const BOLD = 'bold';

    /**
     * Configuration key
     *
     * @var string
     */
    const ITALIC = 'italic';

    /**
     * Configuration key
     *
     * @var string
     */
    const TITLE = 'title';

    /**
     * Append mode value
     *
     * @var string
     */
    const APPEND_MODE_COPY = 'copy';

    /**
     * Append mode value
     *
     * @var string
     */
    const APPEND_MODE_MOVE = 'move';

    /**
     * Append mode value
     *
     * @var null
     */
    const APPEND_MODE_NONE = null;

    /**
     * Move mode value
     *
     * @var string
     */
    const MOVE_MODE_APPEND = 'append';

    /**
     * Move mode value
     *
     * @var string
     */
    const MOVE_MODE_PREPEND = 'prepend';

    /**
     * Move mode value
     *
     * @var string
     */
    const MOVE_MODE_APPEND_CHILD = 'appendChild';

    /**
     * The item dictionary
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * The indirect reference for this item (if available)
     *
     * @var SetaPDF_Core_Type_IndirectObjectInterface
     */
    protected $_indirectReference;

    /**
     * The current iterator item
     *
     * @var SetaPDF_Core_Document_OutlinesItem|false
     */
    protected $_current;

    /**
     * Current iterator key
     *
     * @var integer
     */
    protected $_key = 0;

    /**
     * Creates an outline item.
     *
     * The configuration array could hold keyed values:
     * <code>
     * $config = array(
     *     SetaPDF_Core_Document_OutlinesItem::TITLE => string,
     *     SetaPDF_Core_Document_OutlinesItem::DEST => SetaPDF_Core_Document_Destination|SetaPDF_Core_Type_Array|string,
     *     SetaPDF_Core_Document_OutlinesItem::ACTION => SetaPDF_Core_Document_Action|SetaPDF_Core_Type_Dictionary,
     *     SetaPDF_Core_Document_OutlinesItem::COLOR => SetaPDF_Core_DataStructure_Color_Rgb|array
     *     SetaPDF_Core_Document_OutlinesItem::BOLD => boolean,
     *     SetaPDF_Core_Document_OutlinesItem::ITALIC => boolean,
     * );
     * </code>
     *
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be used
     * @param string|array $titleOrConfig The title or a configuration array
     * @param array $config A configuration array
     * @return SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_Type_Exception
     */
    public static function create(SetaPDF_Core_Document $document, $titleOrConfig, $config = [])
    {
        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $item = new self($document->createNewObject($dictionary));

        if (!is_array($titleOrConfig)) {
            $item->setTitle($titleOrConfig);
        } else {
            $config = $titleOrConfig;
        }

        foreach ($config AS $key => $value) {
            switch ($key) {
                case self::DEST:
                case self::ACTION:
                case self::COLOR:
                case self::BOLD:
                case self::ITALIC:
                case self::TITLE:
                    $method = 'set' . ucfirst($key);
                    $item->$method($value);
            }
        }

        return $item;
    }

    /**
     * Copies an item.
     *
     * This method internally removes all relations to its parents or neighboring items.
     *
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be copied
     * @param SetaPDF_Core_Document_OutlinesItem $item The item to copy
     * @return SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_Type_Exception
     */
    public static function copyItem(SetaPDF_Core_Document $document, self $item)
    {
        $dictionary = clone $item->_dictionary;
        $dictionary->offsetUnset('Parent');
        $dictionary->offsetUnset('Next');
        $dictionary->offsetUnset('Prev');
        $dictionary->offsetUnset('First');
        $dictionary->offsetUnset('Last');

        return new self($document->createNewObject($dictionary));
    }

    /**
     * Copies an item including all sub-items to another item.
     *
     * @param SetaPDF_Core_Document $document
     * @param self $rootItem
     * @param SetaPDF_Core_Document_OutlinesItem|SetaPDF_Core_Document_Catalog_Outlines $targetItem
     * @param string $appendMethod
     * @param boolean $first
     * @return SetaPDF_Core_Document_OutlinesItem
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     * @internal
     */
    private static function _copy(
        SetaPDF_Core_Document $document,
        self $rootItem,
        $targetItem,
        $appendMethod,
        $first = true
    )
    {
        if ($first && $rootItem->contains($targetItem, $appendMethod === 'appendChild')) {
            throw new InvalidArgumentException(
                'A cyclic reference is detected while copying outline items.'
            );
        }

        // this iterates at least over itself
        foreach ($rootItem AS $item) {
            $newItem = self::copyItem($document, $item);
            $targetItem->$appendMethod($newItem);
            if ($item->hasChildren()) {
                self::_copy($document, $item->getChildren(), $newItem, 'appendChild', false);
            }
            if ($first) {
                break;
            }
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $newItem;
    }

    /**
     * Get a hash of an outline item.
     *
     * Used for checking cyclic references.
     *
     * @see SetaPDF_Core_Document_OutlinesItem::contains()
     * @param self $item
     * @return string
     */
    private static function _getHash(self $item)
    {
        return md5(serialize($item->_dictionary->toPhp()));
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary $objectOrDictionary
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct($objectOrDictionary)
    {
        if ($objectOrDictionary instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            $this->_indirectReference = $objectOrDictionary;
        }

        $this->_dictionary = SetaPDF_Core_Type_Dictionary::ensureType($objectOrDictionary);
        $this->_current = $this;
    }

    /**
     * Omit cloning.
     *
     * @throws BadMethodCallException
     */
    public function __clone()
    {
        throw new BadMethodCallException(
            'Outline item objects are not cloneable. Use SetaPDF_Core_Document_OutlinesItem::' .
                'copy() or the SetaPDF_Core_Document_OutlinesItem::(append|prepend|appendChild)Copy()-methods instead.'
        );
    }

    /**
     * Get the reference to this item.
     *
     * @return SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary
     */
    public function getReferenceTo()
    {
        if ($this->_indirectReference !== null) {
            return $this->_indirectReference;
        }

        return $this->_dictionary;
    }

    /**
     * Get the item title.
     *
     * @param string $encoding The output encoding
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getTitle($encoding = 'UTF-8')
    {
        $title = SetaPDF_Core_Type_AbstractType::ensureWithType(
            SetaPDF_Core_Type_StringValue::class,
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Title')
        );
        return SetaPDF_Core_Encoding::convertPdfString($title->getValue(), $encoding);
    }

    /**
     * Set the item title.
     *
     * @param string $title The item title
     * @param string $encoding The input encoding
     * @return SetaPDF_Core_Document_OutlinesItem Returns the SetaPDF_Core_Document_OutlinesItem object for method chaining.
     */
    public function setTitle($title, $encoding = 'UTF-8')
    {
        $dictionary = $this->getDictionary();
        $titleObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Title');
        if (!$titleObject instanceof SetaPDF_Core_Type_StringValue) {
            $titleObject = new SetaPDF_Core_Type_String();
            $dictionary->offsetSet('Title', $titleObject);
        }

        $titleObject->setValue(SetaPDF_Core_Encoding::toPdfString($title, $encoding));

        return $this;
    }

    /**
     * Checks whether a flag in the F entry is set or not.
     *
     * @param int $flag
     * @return bool
     */
    protected function _isFlagSet($flag)
    {
        $f = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'F');
        if (!$f instanceof SetaPDF_Core_Type_Numeric) {
            return false;
        }

        return ($f->getValue() & $flag) !== 0;
    }

    /**
     * Set or unset a flag in the F entry.
     *
     * @param boolean $value
     * @param int $flag
     * @return $this|void
     */
    protected function _setFlag($value, $flag)
    {
        $dictionary = $this->getDictionary();

        $f = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'F');
        if (!$f instanceof SetaPDF_Core_Type_Numeric) {
            $dictionary->offsetSet('F', new SetaPDF_Core_Type_Numeric($flag));
            return $this;
        }

        if ($value) {
            $f->setValue($f->getValue() | $flag);
        } else {
            $f->setValue($f->getValue() & ~$flag);
        }
    }

    /**
     * Checks if the item should be displayed bold.
     *
     * @return boolean
     */
    public function isBold()
    {
        return $this->_isFlagSet(2);
    }

    /**
     * Sets whether the item should be displayed bold or not.
     *
     * @param boolean $bold True to display the item bold
     * @return SetaPDF_Core_Document_OutlinesItem Returns the SetaPDF_Core_Document_OutlinesItem object for method chaining.
     */
    public function setBold($bold = true)
    {
        if ($bold === $this->isBold()) {
            return $this;
        }

        $this->_setFlag($bold, 2);

        return $this;
    }

    /**
     * Checks if the item should be displayed italic.
     *
     * @return boolean
     */
    public function isItalic()
    {
        return $this->_isFlagSet(1);
    }

    /**
     * Sets whether the item should be displayed italic or not.
     *
     * @param boolean $italic True to display the item italic
     * @return SetaPDF_Core_Document_OutlinesItem Returns the SetaPDF_Core_Document_OutlinesItem object for method chaining.
     */
    public function setItalic($italic = true)
    {
        if ($italic === $this->isItalic()) {
            return $this;
        }

        $this->_setFlag($italic, 1);

        return $this;
    }

    /**
     * Get the color of the item.
     *
     * @return false|SetaPDF_Core_DataStructure_Color A color object or false if no color is defined.
     */
    public function getColor()
    {
        $c = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'C');
        if (!$c instanceof SetaPDF_Core_Type_Array) {
            return false;
        }

        return SetaPDF_Core_DataStructure_Color::createByComponents($c);
    }

    /**
     * Set the color of the item.
     *
     * @param array|SetaPDF_Core_DataStructure_Color_Rgb|float $colorOrR An array of 3 integer values representing the
     *                                                                   rgb components (between 0 and 1), an instance
     *                                                                   of {@link SetaPDF_Core_DataStructure_Color_Rgb}
     *                                                                   or the red component value (between 0 and 1).
     * @param float $g The green component value (between 0 and 1)
     * @param float $b The blue component value (between 0 and 1)
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method chaining.
     */
    public function setColor($colorOrR, $g = null, $b = null)
    {
        if (!($colorOrR instanceof SetaPDF_Core_DataStructure_Color_Rgb)) {
            $colorOrR = new SetaPDF_Core_DataStructure_Color_Rgb($colorOrR, $g, $b);
        }

        $values = $colorOrR->getValue();
        $dictionary = $this->getDictionary();

        $c = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'C');
        if (!$c instanceof SetaPDF_Core_Type_Array) {
            $dictionary->offsetSet('C', $values);
            return $this;
        }

        $c->setValue($values);

        return $this;
    }

    /**
     * Get the destination of the item.
     *
     * @param SetaPDF_Core_Document $document The main document is needed to automatically resolve the destination
     * @return SetaPDF_Core_Document_Destination|false A destination instance or false if none was defined.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getDestination(SetaPDF_Core_Document $document = null)
    {
        $dest = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Dest');
        if ($dest === null) {
            return false;
        }

        if ($dest instanceof SetaPDF_Core_Type_StringValue || $dest instanceof SetaPDF_Core_Type_Name) {
            if ($document === null) {
                throw new BadMethodCallException('To resolve a named destination the $document parameter has to be set.');
            }

            return SetaPDF_Core_Document_Destination::findByName($document, $dest->getValue());
        }

        return new SetaPDF_Core_Document_Destination($dest);
    }

    /**
     * Set the destination of the item.
     *
     * @param SetaPDF_Core_Document_Destination|SetaPDF_Core_Type_Array|SetaPDF_Core_Type_StringValue|SetaPDF_Core_Type_Name|string|null $destination The destination
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method chaining.
     * @throws InvalidArgumentException
     */
    public function setDestination($destination)
    {
        $dictionary = $this->getDictionary();

        if (!$destination) {
            $dictionary->offsetUnset('Dest');
            return $this;
        }

        if ($destination instanceof SetaPDF_Core_Document_Destination) {
            $destination = $destination->getDestinationArray();
        }

        if (is_scalar($destination)) {
            $destination = new SetaPDF_Core_Type_String($destination);
        }

        if (!($destination instanceof SetaPDF_Core_Type_Array) &&
            !($destination instanceof SetaPDF_Core_Type_StringValue) &&
            !($destination instanceof SetaPDF_Core_Type_Name)
        ) {
            throw new InvalidArgumentException('Only valid destination values allowed (SetaPDF_Core_Type_Array, SetaPDF_Core_Type_StringValue, SetaPDF_Core_Type_Name or SetaPDF_Core_Document_Destination)');
        }

        $dictionary->offsetSet('Dest', $destination);
        $dictionary->offsetUnset('A');

        return $this;
    }

    /**
     * Get the action of the item.
     *
     * @return bool|SetaPDF_Core_Document_Action The action instance or false if no action is defined.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAction()
    {
        $dictionary = $this->getDictionary();
        $a = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'A');
        if (!$a instanceof SetaPDF_Core_Type_AbstractType) {
            return false;
        }

        return SetaPDF_Core_Document_Action::byObjectOrDictionary($dictionary->getValue('A'));
    }

    /**
     * Set the action of the item.
     *
     * The action could be an instance of {@link SetaPDF_Core_Document_Action}, a dictionary or a reference to a
     * dictionary representing the action.
     *
     * @param SetaPDF_Core_Document_Action|SetaPDF_Core_Type_IndirectObject|SetaPDF_Core_Type_IndirectReference|SetaPDF_Core_Type_Dictionary|null $action The action to execute
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method chaining.
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setAction($action)
    {
        $dictionary = $this->getDictionary();

        if (!$action) {
            $dictionary->offsetUnset('A');
            return $this;
        }

        if (!$action instanceof SetaPDF_Core_Document_Action) {
            $action = SetaPDF_Core_Document_Action::byObjectOrDictionary($action);
        }

        $dictionary->offsetSet('A', $action->getIndirectObject() ?: $action->getActionDictionary());
        $dictionary->offsetUnset('Dest');

        return $this;
    }

    /**
     * Checks whether the item is open or not or if the item does not holds any descendants.
     *
     * @return null|boolean Returns true if the item is open, false if it is closed or null if the item does not holds
     *                      any descendants.
     */
    public function isOpen()
    {
        $value = $this->getCount();
        if ($value === 0) {
            return null;
        }

        return $value > 0;
    }

    /**
     * Open or close the item.
     *
     * @param boolean $open A boolean value specifying if the item is opened or not
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method chaining.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setOpen($open = true)
    {
        // only possible if the item has any descendants
        $firstItem = $this->getFirstItem();
        if (!$firstItem) {
            return $this;
        }

        $open = (boolean)$open;
        if ($open === $this->isOpen()) {
            return $this;
        }

        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            $firstItem,
            RecursiveIteratorIterator::SELF_FIRST
        );
        $iterator->setMaxDepth(0);

        foreach ($iterator AS $childItem) {
            $count++;
            $childCount = $childItem->getCount();
            if ($childCount > 0) {
                $count += $childCount;
            }
        }

        $dictionary = $this->getDictionary();
        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Count');

        $count *= ($open ? 1 : -1);
        $value->setValue($count);

        /**
         * @var SetaPDF_Core_Type_Dictionary $parentPdfDict
         */
        $parentPdfDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Parent');
        while ($parentPdfDict instanceof SetaPDF_Core_Type_Dictionary) {
            $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPdfDict, 'Count');
            $currentCount = SetaPDF_Core_Type_Numeric::ensureType($value)->getValue();
            $value->setValue($currentCount + ($count * ($currentCount < 0 ? -1 : 1)));

            $tmpParentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPdfDict, 'Parent');
            $parentPdfDict = $currentCount > 0 && $tmpParentDict instanceof SetaPDF_Core_Type_Dictionary
                ? $tmpParentDict
                : false;
        }

        return $this;
    }

    /**
     * Close the item.
     *
     * Alias for {@link SetaPDF_Core_Document_OutlinesItem::setOpen()} with false as its argument.
     *
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method chaining.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function close()
    {
        return $this->setOpen(false);
    }

    /**
     * Open the item.
     *
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method chaining.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function open()
    {
        return $this->setOpen();
    }

    /**
     * Remove the item from the outline.
     *
     * @return SetaPDF_Core_Document_OutlinesItem The removed item
     * @throws SetaPDF_Core_Type_Exception
     */
    public function remove()
    {
        $dictionary = $this->getDictionary();
        $currentNext = $dictionary->offsetGet('Next');
        $currentPrevious = $dictionary->offsetGet('Prev');
        $next = $currentNext ? $currentNext->ensure() : false;
        $previous = $currentPrevious ? $currentPrevious->ensure() : false;

        $parentPdfDict = SetaPDF_Core_Type_Dictionary::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Parent')
        );

        // Change Next and Prev values by the surrounding items
        if ($next instanceof SetaPDF_Core_Type_Dictionary && $previous instanceof SetaPDF_Core_Type_Dictionary) {
            $next->offsetSet('Prev', $currentPrevious->getValue());
            $previous->offsetSet('Next', $currentNext->getValue());

            // if no next is available unset next in the previous and set the parents last entry to the previous
        } elseif (!$next && $previous instanceof SetaPDF_Core_Type_Dictionary) {
            $previous->offsetUnset('Next');
            $parentPdfDict->offsetSet('Last', $currentPrevious->getValue());

            // if no previous is available unset prev in the next and set the parents first entry to the next
        } elseif (!$previous && $next instanceof SetaPDF_Core_Type_Dictionary) {
            $next->offsetUnset('Prev');
            $parentPdfDict->offsetSet('First', $currentNext->getValue());

            // If this was the only item unset the parents first and last entries
        } else {
            $parentPdfDict->offsetUnset('First');
            $parentPdfDict->offsetUnset('Last');
        }

        // Unset all reference values
        $dictionary->offsetUnset('Parent');
        $dictionary->offsetUnset('Prev');
        $dictionary->offsetUnset('Next');
        $dictionary->offsetUnset('First');
        $dictionary->offsetUnset('Last');

        // descendants + current (1)
        $removedCount = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Count', 0, true) + 1;

        while ($parentPdfDict instanceof SetaPDF_Core_Type_Dictionary) {
            $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPdfDict, 'Count');
            $currentCount = SetaPDF_Core_Type_Numeric::ensureType($value)->getValue();
            $value->setValue($currentCount - ($removedCount * ($currentCount < 0 ? -1 : 1)));

            $tmpParentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPdfDict, 'Parent');
            $parentPdfDict = $currentCount > 0 && $tmpParentDict instanceof SetaPDF_Core_Type_Dictionary
                ? $tmpParentDict
                : false;
        }

        return $this;
    }

    /**
     * Prepend another item to this item.
     *
     * The $mode parameter can be used to specify the way the item will be
     * prepended: moved or copied.
     *
     * @param SetaPDF_Core_Document_OutlinesItem $item The item to prepend
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be used
     * @param string $mode The append mode. See {@link SetaPDF_Core_Document_OutlinesItem::APPEND_MODE_XXX} constants for
     *                     details.
     * @return SetaPDF_Core_Document_OutlinesItem Returns the passed item or the last node in the item structure.
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function prepend(
        self $item,
        SetaPDF_Core_Document $document = null,
        $mode = null
    ) {
        if ($item->getParent() !== false) {
            if (!$document instanceof SetaPDF_Core_Document) {
                throw new InvalidArgumentException(
                    'Item is already attached to an outline. This requires a SetaPDF_Core_Document instance as the 2nd parameter.'
                );
            }

            switch ($mode) {
                case self::APPEND_MODE_COPY:
                    return self::_copy($document, $item, $this, 'prepend');

                case self::APPEND_MODE_MOVE:
                    $newItem = self::_copy($document, $item, $this, 'prepend');
                    $item->remove();
                    return $newItem;
            }

            throw new InvalidArgumentException(
                'Item is already attached to an outline. Define the handling of these items by the $mode parameter.'
            );
        }

        $dictionary = $this->getDictionary();

        $currentPrevious = $dictionary->offsetGet('Prev');

        $parentPdfDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Parent');
        if (!$parentPdfDict instanceof SetaPDF_Core_Type_Dictionary) {
            throw new LogicException('This item needs at least to be attached to an outline or other item.');
        }

        $item->_dictionary->offsetSet('Next', $this->getReferenceTo());

        // Change prev value of the next item to the new $item reference
        if ($currentPrevious) {
            SetaPDF_Core_Type_Dictionary::ensureType($currentPrevious)->offsetSet('Next', $item->getReferenceTo());
            $item->_dictionary->offsetSet(null, clone $currentPrevious);

        } else {
            $parentPdfDict->offsetSet('First', $item->getReferenceTo());
        }

        $item->_dictionary->offsetSet('Parent', $this->_dictionary->getValue('Parent'));
        $this->_dictionary->offsetSet('Prev', $item->getReferenceTo());

        $this->_updateParentCount($parentPdfDict);

        return $item;
    }

    /**
     * Append another item to this item.
     *
     * The $mode parameter can be used to specify the way the item will be
     * appended: moved or copied.
     *
     * @param SetaPDF_Core_Document_OutlinesItem $item The item to append
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be used
     * @param string $mode The append mode. See {@link SetaPDF_Core_Document_OutlinesItem::APPEND_MODE_XXX} constants for
     *                     details.
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method
     *                                            chaining.
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function append(self $item, SetaPDF_Core_Document $document = null, $mode = null)
    {
        if ($item->getParent() !== false) {
            if (!$document instanceof SetaPDF_Core_Document) {
                throw new InvalidArgumentException(
                    'Item is already attached to an outline. This requires a SetaPDF_Core_Document instance as the 2nd parameter.'
                );
            }

            switch ($mode) {
                case self::APPEND_MODE_COPY:
                    return self::_copy($document, $item, $this, 'append');

                case self::APPEND_MODE_MOVE:
                    $newItem = self::_copy($document, $item, $this, 'append');
                    $item->remove();
                    return $newItem;
            }

            throw new InvalidArgumentException(
                'Item is already attached to an outline. Define the handling of these items by the $mode parameter.'
            );
        }

        $dictionary = $this->getDictionary();

        $currentNext = $dictionary->offsetGet('Next');

        $parentPdfDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Parent');
        if (!$parentPdfDict instanceof SetaPDF_Core_Type_Dictionary) {
            throw new LogicException('This item needs at least to be attached to an outline or other item.');
        }

        $item->_dictionary->offsetSet('Prev', $this->getReferenceTo());

        // Change prev value of the next item to the new $item reference
        if ($currentNext) {
            SetaPDF_Core_Type_Dictionary::ensureType($currentNext)->offsetSet('Prev', $item->getReferenceTo());
            $item->_dictionary->offsetSet(null, clone $currentNext);

        } else {
            $parentPdfDict->offsetSet('Last', $item->getReferenceTo());
        }

        $item->_dictionary->offsetSet('Parent', $dictionary->getValue('Parent'));
        $dictionary->offsetSet('Next', $item->getReferenceTo());

        $this->_updateParentCount($parentPdfDict);

        return $this;
    }

    /**
     * Append another item as a child of this item.
     *
     * The $mode parameter can be used to specify the way the item will be appended: moved or copied.
     *
     * @param self $item The item to append
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be used
     * @param string $mode The append mode. See {@link SetaPDF_Core_Document_OutlinesItem::APPEND_MODE_XXX} constants for
     *                     details.
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method
     *                                            chaining.
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function appendChild($item, SetaPDF_Core_Document $document = null, $mode = null)
    {
        if ($item->getParent() !== false) {
            if (!$document instanceof SetaPDF_Core_Document) {
                throw new InvalidArgumentException(
                    'Item is already attached to an outline. This requires a SetaPDF_Core_Document instance as the 2nd parameter.'
                );
            }

            switch ($mode) {
                case self::APPEND_MODE_COPY:
                    return $this->appendChildCopy($item, $document);

                case self::APPEND_MODE_MOVE:
                    $newItem = $this->appendChildCopy($item, $document);
                    $item->remove();
                    return $newItem;
            }

            throw new InvalidArgumentException(
                'Item is already attached to an outline. Define the handling of these items by the $mode parameter.'
            );
        }

        /* check if item already have descendant outline items:
         * if so, just get the last item and call append on it.
         */
        $lastItem = $this->getLastItem();
        if ($lastItem) {
            $lastItem->append($item);
            return $this;
        }

        $dictionary = $this->getDictionary();
        $value = $item->getReferenceTo();
        $dictionary->offsetSet('First', $value);
        $dictionary->offsetSet('Last', $value);
        $dictionary->offsetSet('Count', new SetaPDF_Core_Type_Numeric(1));
        $value->ensure()->offsetSet('Parent', $this->getReferenceTo());

        $parentPdfDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Parent');
        $this->_updateParentCount($parentPdfDict);

        return $this;
    }

    /**
     * Increases or decreases the count value in the parent items.
     *
     * @param SetaPDF_Core_Type_Dictionary|false $parentPdfDict
     * @return void
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _updateParentCount($parentPdfDict)
    {
        while ($parentPdfDict instanceof SetaPDF_Core_Type_Dictionary) {
            $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPdfDict, 'Count');
            $currentCount = SetaPDF_Core_Type_Numeric::ensureType($value)->getValue();
            $value->setValue($currentCount + ($currentCount < 0 ? -1 : 1));

            $tmpParentDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($parentPdfDict, 'Parent');
            $parentPdfDict = $currentCount > 0 && $tmpParentDict instanceof SetaPDF_Core_Type_Dictionary
                ? $tmpParentDict
                : false;
        }
    }

    /**
     * Move this item to another item or root outline.
     *
     * The $mode parameter can be used to specify how the item will be moved.
     *
     * @param SetaPDF_Core_Document_Catalog_Outlines|SetaPDF_Core_Document_OutlinesItem $target The target item
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be used
     * @param string $mode The move mode. See {@link SetaPDF_Core_Document_OutlinesItem::MOVE_MODE_XXX} constants for details.
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method
     *                                            chaining.
     * @throws InvalidArgumentException
     */
    public function move($target, SetaPDF_Core_Document $document, $mode = 'appendChild')
    {
        switch ($mode) {
            case self::MOVE_MODE_APPEND:
            case self::MOVE_MODE_PREPEND:
            case self::MOVE_MODE_APPEND_CHILD:
                return $target->$mode($this, $document, self::APPEND_MODE_MOVE);
        }

        throw new InvalidArgumentException(sprintf('Unknown move mode (%s)', $mode));
    }

    /**
     * Appends a copy of another item to this item.
     *
     * @param SetaPDF_Core_Document_OutlinesItem $originalItem The original item
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be used
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method
     *                                            chaining.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function appendCopy(self $originalItem, SetaPDF_Core_Document $document)
    {
        return self::_copy($document, $originalItem, $this, 'append');
    }

    /**
     * Prepends a copy of another item to this item.
     *
     * @param SetaPDF_Core_Document_OutlinesItem $originalItem The original item
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be used
     * @return SetaPDF_Core_Document_OutlinesItem Returns the {@link SetaPDF_Core_Document_OutlinesItem} object for method
     *                                            chaining.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function prependCopy(self $originalItem, SetaPDF_Core_Document $document)
    {
        return self::_copy($document, $originalItem, $this, 'prepend');
    }


    /**
     * Appends a copy of another item or outline as a child to this item.
     *
     * @param SetaPDF_Core_Document_Catalog_Outlines|SetaPDF_Core_Document_OutlinesItem $originalItem
     *              The original item or root outlines dictionary
     * @param SetaPDF_Core_Document $document The document instance in which context the item will be used
     * @return null|SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function appendChildCopy($originalItem, SetaPDF_Core_Document $document)
    {
        if ($originalItem instanceof SetaPDF_Core_Document_Catalog_Outlines) {
            $iterator = $originalItem->getIterator();

            if ($iterator instanceof RecursiveIteratorIterator) {
                $iterator->setMaxDepth(0);
            }

            foreach ($iterator AS $_item) {
                $this->appendChildCopy($_item, $document);
            }

            if ($iterator instanceof RecursiveIteratorIterator) {
                $iterator->setMaxDepth(-1);
            }
            return null;
        }

        return self::_copy($document, $originalItem, $this, 'appendChild');
    }

    /**
     * Checks if an item is specified in any descendants of this item.
     *
     * @param SetaPDF_Core_Document_OutlinesItem $item The item to check for
     * @param boolean $checkAgainstThis True to check this instance against the item
     * @return boolean Whether the item is specified in any descendants of this item.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function contains(self $item, $checkAgainstThis = true)
    {
        $mainItemHash = self::_getHash($item);
        if ($checkAgainstThis && $mainItemHash === self::_getHash($this)) {
            return true;
        }

        if ($this->hasFirstItem() === false) {
            return false;
        }

        $children = $this->getFirstItem();

        $iterator = new RecursiveIteratorIterator(
            $children,
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator AS $childItem) {
            $childItemHash = self::_getHash($childItem);
            if ($mainItemHash === $childItemHash) {
                return true;
            }
        }

        return false;
    }

  /* Access-methods for navigating in the linked list */

    /**
     * Get an item instance of the item referenced in the Prev key.
     *
     * @return boolean|SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getPrevious()
    {
        if (!$this->_dictionary->offsetExists('Prev')) {
            return false;
        }

        /** @var SetaPDF_Core_Type_IndirectReference|SetaPDF_Core_Type_Dictionary $prev */
        $prev = $this->_dictionary->getValue('Prev');
        return new self($prev);
    }

    /**
     * Get an item instance of the item referenced in the Next key.
     *
     * @return boolean|SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getNext()
    {
        if (!$this->_dictionary->offsetExists('Next')) {
            return false;
        }

        /** @var SetaPDF_Core_Type_IndirectReference|SetaPDF_Core_Type_Dictionary $next */
        $next = $this->_dictionary->getValue('Next');
        return new self($next);
    }

    /**
     * Get an item instance of the item referenced in the Parent key.
     *
     * @return boolean|SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getParent()
    {
        if (!$this->_dictionary->offsetExists('Parent')) {
            return false;
        }

        /** @var SetaPDF_Core_Type_IndirectReference|SetaPDF_Core_Type_Dictionary $parent */
        $parent = $this->_dictionary->getValue('Parent');
        return new self($parent);
    }

    /**
     * Get an item instance of the item referenced in the First key.
     *
     * @return boolean|SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getFirstItem()
    {
        if (!$this->_dictionary->offsetExists('First')) {
            return false;
        }

        /** @var SetaPDF_Core_Type_IndirectReference|SetaPDF_Core_Type_Dictionary $first */
        $first = $this->_dictionary->getValue('First');
        return new self($first);
    }

    /**
     * Checks if this item has a 'First' value set.
     *
     * The first item is the first item structured under this item.
     * With this method you can check if the item has child items.
     *
     * @return bool True if a child item exists - false otherwise.
     */
    public function hasFirstItem()
    {
        return $this->_dictionary->offsetExists('First');
    }

    /**
     * Get an item instance of the item referenced in the 'Last' key.
     *
     * @return bool|SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getLastItem()
    {
        if (!$this->_dictionary->offsetExists('Last')) {
            return false;
        }

        /** @var SetaPDF_Core_Type_IndirectReference|SetaPDF_Core_Type_Dictionary $last */
        $last = $this->_dictionary->getValue('Last');
        return new self($last);
    }

    /**
     * Get the number of visible outline items (value of the Count key).
     *
     * Total number of visible outline items at all levels of the outline.
     *
     * @return int
     */
    public function getCount()
    {
        $count = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Count');
        if (!$count instanceof SetaPDF_Core_Type_Numeric) {
            return 0;
        }

        return (int)$count->getValue();
    }

    /**
     * Get the dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        return $this->_dictionary;
    }

  /* RecursiveIterator implementations */

    /**
     * Checks if this item has descendants.
     *
     * @see RecursiveIterator::hasChildren()
     */
    #[\ReturnTypeWillChange]
    public function hasChildren()
    {
        return $this->_current->hasFirstItem();
    }

    /**
     * Get the first descendant item.
     *
     * @return SetaPDF_Core_Document_OutlinesItem|false
     * @throws SetaPDF_Core_Type_Exception
     * @see RecursiveIterator::getChildren()
     */
    #[\ReturnTypeWillChange]
    public function getChildren()
    {
        if ($this->_current->hasFirstItem()) {
            return $this->_current->getFirstItem();
        }

        return false;
    }

    /**
     * Get the current item.
     *
     * @see RecursiveIterator::current()
     * @return SetaPDF_Core_Document_OutlinesItem
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->_current;
    }

    /**
     * Get the next item.
     *
     * @see RecursiveIterator::next()
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->_current = $this->_current->getNext();
        $this->_key++;
    }

    /**
     * Get the iterator key.
     *
     * @see RecursiveIterator::key()
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->_key;
    }

    /**
     * Checks whether the pointer of the iterator is valid or not.
     *
     * @see RecursiveIterator::valid()
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->_current !== false;
    }

    /**
     * Reset the iterator.
     *
     * @see RecursiveIterator::rewind()
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->_current = $this;
        $this->_key = 0;
    }

  /* ArrayAccess Implementation */

    /**
     * Checks if an item exists at a specific position.
     *
     * @see ArrayAccess::offsetExists()
     * @param mixed $offset The index being checked
     * @return bool
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
     * @param null|string $offset The index being set
     * @param mixed $value The new value for the index
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
            $this->appendChild($value);
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
        if ($next) {
            $current->remove();
            $next->prepend($value);
            return;
        }

        $current->remove();
        $this->appendChild($value);
    }

    /**
     * Get an item by a specific position.
     *
     * @see ArrayAccess::offsetGet()
     * @param mixed $offset The index with the value
     * @return SetaPDF_Core_Document_OutlinesItem The value at the specified index.
     * @throws InvalidArgumentException if no item is at the offset.
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
     * @param mixed $offset The index being unset
     * @return SetaPDF_Core_Document_OutlinesItem
     * @throws SetaPDF_Core_Type_Exception
     * @see ArrayAccess::offsetUnset()
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $item = $this->offsetGet($offset);
        return $item->remove();
    }
}
