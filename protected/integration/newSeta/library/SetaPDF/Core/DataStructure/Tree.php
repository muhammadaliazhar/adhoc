<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage DataStructure
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Tree.php 1778 2022-09-16 10:50:21Z jan.slabon $
 */

/**
 * Abstract data structure class for trees
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage DataStructure
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Core_DataStructure_Tree
    implements SetaPDF_Core_DataStructure_DataStructureInterface
{
    /**
     * Leaf nodes per node
     *
     * @var integer
     */
    public static $leafNodesPerNode = 64;

    /**
     * The document to which the tree depends to
     *
     * @var SetaPDF_Core_Document
     */
    protected $_document;

    /**
     * The dictionary entry in the dictionary of the root object
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_rootDictionary;

    /**
     * Is this a new tree or should we update an existing one
     *
     * @var boolean
     */
    protected $_isNew = false;

    /**
     * A cache for resolved entries
     *
     * The index is the key while the value is an array of both
     * key and value object.
     *
     * @var array
     */
    protected $_values = [];

    /**
     * Get the entries key name by tree implementation.
     *
     * @return string
     */
    abstract protected function _getEntriesKeyName();

    /**
     * Get the key class name by tree implementation.
     *
     * @return string
     */
    abstract protected function _getKeyClassName();

    /**
     * Get the key instance name by tree implementation.
     *
     * @return string
     */
    protected function _getKeyInstanceName()
    {
        return $this->_getKeyClassName();
    }

    /**
     * Get the sort type for the specific tree implementation.
     *
     * @return integer
     * @see http://www.php.net/sort
     */
    abstract protected function _getSortType();

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_Dictionary $rootDictionary
     * @param SetaPDF_Core_Document $document
     */
    public function __construct(
        SetaPDF_Core_Type_Dictionary $rootDictionary,
        SetaPDF_Core_Document $document
    )
    {
        $this->_rootDictionary = $rootDictionary->ensure();
        $this->_document = $document;

        /* Check if the dictionary is empty: Means that we could
         * Sort and balance things at writing time...
         */
        $node = $this->_rootDictionary->ensure();
        if (!$node->offsetExists('Kids') && !$node->offsetExists($this->_getEntriesKeyName())) {
            $this->_isNew = true;
            $node->registerPdfStringCallback(
                [$this, 'arrange'], 'arrangeTree'
            );
        }
    }

    /**
     * Get an object by a key.
     *
     * @param string $key
     * @param string $className
     * @return SetaPDF_Core_Type_AbstractType|false|mixed
     * @throws SetaPDF_Core_Type_Exception
     */
    public function get($key, $className = null)
    {
        if (!isset($this->_values[$key])) {
            $node = $this->_rootDictionary;
            $value = $this->_get($node, $key);
            if ($value === false) {
                return false;
            }
        }

        if ($className === null) {
            return $this->_values[$key]['value'];
        }

        return new $className($this->_values[$key]['value']->ensure());
    }

    /**
     * Gets a key value pair by a root node and a key.
     *
     * @param SetaPDF_Core_Type_Dictionary $node
     * @param string $key
     * @return array|false
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _get(SetaPDF_Core_Type_Dictionary $node, $key)
    {
        $leaveNode = $this->_findLeaveNodeByKey($node, $key);
        if ($leaveNode === false) {
            return false;
        }

        $entries = SetaPDF_Core_Type_Array::ensureType($leaveNode->offsetGet($this->_getEntriesKeyName()));

        for ($i = 0, $n = $entries->count(); $i < $n; $i += 2) {
            $currentKey = $entries->offsetGet($i);
            if ($currentKey) {
                $currentKey = $currentKey->ensure();
            }

            // we ignore the key type check here, to support "invalid" key types:
            // Sometimes Name objects are used while they have to be String values

            if ($currentKey->getValue() === $key) {
                $this->_values[$key] = [
                    'key' => $entries->offsetGet($i),
                    'value' => $entries->offsetGet($i + 1)
                ];
                return $this->_values[$key];
            }
        }

        return false;
    }

    /**
     * Find a appropriate leaf node by a key.
     *
     * @param SetaPDF_Core_Type_Dictionary $node
     * @param int $key
     * @param bool $exactMatch
     * @param array $intermediateNodes
     * @return bool|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _findLeaveNodeByKey(
        SetaPDF_Core_Type_Dictionary $node,
        $key,
        $exactMatch = true,
        &$intermediateNodes = []
    )
    {
        if (
            ($node->offsetExists($this->_getEntriesKeyName()) && $node->offsetExists('Limits')) ||
            ($node->offsetExists($this->_getEntriesKeyName()) && !$node->offsetExists('Limits') && !$node->offsetExists('Kids'))
        ) {
            return $node;
        }

        // Walk the kids
        $kids = SetaPDF_Core_Type_Dictionary_Helper::getValue($node, 'Kids');
        if ($kids) {
            $kids = SetaPDF_Core_Type_Array::ensureType($kids);
            foreach ($kids AS $kid) {
                /* pre-check 
                 */
                $_kid = SetaPDF_Core_Type_Dictionary::ensureType($kid);
                $limit = SetaPDF_Core_Type_Array::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($_kid, 'Limits'), 2
                )->toPhp(true);

                if (!(
                    ($key >= $limit[0] && $key <= $limit[1]) ||
                    ($exactMatch === false && $key < $limit[0])
                )
                ) {
                    continue;
                }

                $tmpRes = $this->_findLeaveNodeByKey($_kid, $key, $exactMatch, $intermediateNodes);
                if ($tmpRes !== false) {
                    $intermediateNodes[] = $_kid;
                    return $tmpRes;
                }
            }
        }

        return false;
    }

    /**
     * Finds the last leaf node.
     *
     * @param SetaPDF_Core_Type_Dictionary $node
     * @param array $intermediateNodes
     * @return bool|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _findLastLeafNode(SetaPDF_Core_Type_Dictionary $node, array &$intermediateNodes)
    {
        if ($node->offsetExists('Limits') && $node->offsetExists($this->_getEntriesKeyName())) {
            return $node;
        }

        $kids = SetaPDF_Core_Type_Dictionary_Helper::getValue($node, 'Kids');
        if ($kids) {
            if (!$kids instanceof SetaPDF_Core_Type_Array || $kids->count() === 0) {
                return false;
            }

            $kid = SetaPDF_Core_Type_Dictionary::ensureType($kids->offsetGet($kids->count() - 1));
            $intermediateNodes[] = $kid;
            return $this->_findLastLeafNode($kid, $intermediateNodes);
        }

        return false;
    }

    /**
     * Get all keyed objects.
     *
     * @param bool $keysOnly
     * @param null|string $className
     * @return array
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAll($keysOnly = false, $className = null)
    {
        if ($this->_isNew === false) {
            $stack = [$this->_rootDictionary];
            $entriesKeyName = $this->_getEntriesKeyName();

            while (($node = array_shift($stack)) !== null) {
                $node = SetaPDF_Core_Type_Dictionary::ensureType($node);
                $kids = SetaPDF_Core_Type_Dictionary_Helper::getValue($node, 'Kids');
                if ($kids) {
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $stack = array_merge($kids->getValue(), $stack);
                    continue;
                }

                $keys = SetaPDF_Core_Type_Dictionary_Helper::getValue($node, $entriesKeyName);
                if ($keys) {
                    $keys = SetaPDF_Core_Type_Array::ensureType($keys);
                    for ($i = 0, $n = $keys->count(); $i < $n; $i += 2) {
                        $key = $keys->offsetGet($i);
                        if ($key === null) {
                            break;
                        }

                        $key = $key->ensure();

                        // we ignore the key type check here, to support "invalid" key types:
                        // Sometimes Name objects are used while they have to be String values

                        $value = $keys->offsetGet($i + 1);
                        if ($value === null) {
                            break;
                        }

                        $_result = [
                            'key' => $key,
                            'value' => $value
                        ];

                        $this->_values[$key->getValue()] = $_result;
                    }
                }
            }
        }

        if ($keysOnly) {
            return array_keys($this->_values);
        }

        if ($className === null) {
            return $this->_values;
        }

        $values = [];
        foreach ($this->_values AS $key => $data) {
            $values[$key] = new $className($data['value']->ensure());
        }

        return $values;
    }

    /**
     * Merges another tree into this tree.
     *
     * As all items have to be unique this method will call a callback function
     * given in $alreadyExistsCallback if an item already exists.
     *
     * @param SetaPDF_Core_DataStructure_Tree $tree
     * @param null|callback $alreadyExistsCallback Will be called if a item already exists.<br/>
     *          This method can take control over the renaming of the item.
     *          The method will be called as long as it will not throw an exception of
     *          {@link SetaPDF_Core_DataStructure_Tree_KeyAlreadyExistsException}.<br/>
     *          The parameter of the callback function are: The key value and an incremental
     *          number of renaming attempts.
     * @return array An array of renamed items
     * @throws SetaPDF_Core_DataStructure_Tree_KeyAlreadyExistsException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function merge(SetaPDF_Core_DataStructure_Tree $tree, $alreadyExistsCallback = null)
    {
        $all = $tree->getAll();
        $renamed = [];
        foreach ($all AS $keyValue => $value) {
            try {
                $this->add($value['key'], $value['value']);
            } catch (SetaPDF_Core_DataStructure_Tree_KeyAlreadyExistsException $e) {
                if (is_callable($alreadyExistsCallback)) {
                    $i = 0;
                    while (++$i) {
                        $newKey = $alreadyExistsCallback($keyValue, $i);
                        try {
                            $this->add($newKey, $value['value']);
                            $renamed[$keyValue] = $newKey;
                            break;
                        } catch (SetaPDF_Core_DataStructure_Tree_KeyAlreadyExistsException $e) {
                        }
                    }
                } else {
                    throw $e;
                }
            }
        }

        return $renamed;
    }

    /**
     * Add a keyed value to the tree.
     *
     * For name trees: Make sure you pass the name in PDFDocEncoding or UTF-16BE including BOM.
     *
     * @param int|string $key Depends on the implementation
     * @param SetaPDF_Core_Type_AbstractType $value
     * @throws SetaPDF_Core_DataStructure_Tree_KeyAlreadyExistsException
     * @throws SetaPDF_Core_Type_Exception
     * @see _getKeyClassName()
     */
    public function add($key, SetaPDF_Core_Type_AbstractType $value)
    {
        $keyInstanceName = $this->_getKeyInstanceName();
        // If e.g. a Name tree uses Name objects instead of String objects we convert them here
        if ($key instanceof SetaPDF_Core_Type_ScalarValue && !($key instanceof $keyInstanceName)) {
            $key = $key->getValue();
        }

        if (!($key instanceof $keyInstanceName)) {
            $keyClassName = $this->_getKeyClassName();
            $key = new $keyClassName($key);
        }

        $keyValue = $key->ensure()->getValue();

        if ($value instanceof SetaPDF_Core_Type_IndirectObject) {
            $value = new SetaPDF_Core_Type_IndirectReference($value);
        }

        // If this name tree is new, just save the entry
        if ($this->_isNew === true) {
            if (isset($this->_values[$keyValue])) {
                throw new SetaPDF_Core_DataStructure_Tree_KeyAlreadyExistsException(
                    sprintf('Key already exists (%s).', $keyValue)
                );
            }

            $this->_values[$keyValue] = [
                'key' => $key,
                'value' => $value
            ];

            $this->_rootDictionary->notify();
            return;
        }

        /* else: update an existing tree */

        /* 1. Try to find a leaf node to which the string could be added:
         *     a) name matches between the limit values
         *     b) name is lower than the left limit value  
         * 2. If no leaf node is found the name is bigger than all leaf nodes:
         *     Simply get the last one   
         * 3. If no leaf node found at all, add new Limits and Names entry,
         *         add the value and set the Limits entry.
         */

        // 1.
        $limitsNodes = [];
        $node = $this->_findLeaveNodeByKey($this->_rootDictionary, $keyValue, false, $limitsNodes);
        if ($node === false) {
            // 2.
            $limitsNodes = [];
            $node = $this->_findLastLeafNode($this->_rootDictionary, $limitsNodes);
        }

        $entriesKeyName = $this->_getEntriesKeyName();
        $keyClassName = $this->_getKeyClassName(); // SetaPDF_Core_Type_String
        // 3.
        if ($node === false) {
            $node = $this->_rootDictionary;
            $node->offsetSet('Limits', new SetaPDF_Core_Type_Array([
                new $keyClassName($keyValue), new $keyClassName()
            ]));
            $node->offsetSet($entriesKeyName, new SetaPDF_Core_Type_Array());
        }

        $keys = SetaPDF_Core_Type_Array::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($node, $entriesKeyName)
        );

        $limits = SetaPDF_Core_Type_Dictionary_Helper::getValue($node, 'Limits');
        $limitsExists = $limits !== null;
        if ($limitsExists) {
            $limits = $limits->ensure();
        }

        $added = false;
        // Only check if the last limit is higher than the new value
        if ($limitsExists === false || $limits->offsetGet(1)->ensure()->getValue() >= $keyValue) {
            for ($i = 0, $n = $keys->count(); $i < $n; $i += 2) {
                $tmpKey = $keys->offsetGet($i);
                if ($tmpKey === null) {
                    continue;
                }

                $tmpKey = $tmpKey->ensure()->getValue();
                if ($tmpKey === $keyValue) {
                    throw new SetaPDF_Core_DataStructure_Tree_KeyAlreadyExistsException(
                        sprintf('Key already exists (%s).', $keyValue)
                    );
                }

                if ($tmpKey > $keyValue) {
                    $keys->insertBefore($value, $i);
                    $keys->insertBefore($key, $i);
                    $added = true;
                    break;
                }
            }
        }

        if ($added === false) {
            $keys->offsetSet(null, $key);
            $keys->offsetSet(null, $value);
        }

        foreach ($limitsNodes AS $limitNode) {
            $limits = $limitNode->offsetGet('Limits')->ensure();
            if ($limits->offsetGet(0)->getValue() > $keyValue) {
                $limits->offsetGet(0)->setValue($keyValue);
            } elseif ($limits->offsetGet(1)->getValue() < $keyValue) {
                $limits->offsetGet(1)->setValue($keyValue);
            }
        }
    }

    /**
     * Remove a key from the tree
     *
     * @param string $key
     * @return boolean
     * @throws SetaPDF_Core_Type_Exception
     */
    public function remove($key)
    {
        if ($this->_isNew) {
            if (!isset($this->_values[$key])) {
                return false;
            }

            unset($this->_values[$key]);
            return true;
        }

        /** @var SetaPDF_Core_Type_Dictionary[] $limitsNodes */
        $limitsNodes = [];
        $node = $this->_findLeaveNodeByKey($this->_rootDictionary, $key, true, $limitsNodes);
        if ($node === false) {
            return false;
        }

        $keys = SetaPDF_Core_Type_Array::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($node, $this->_getEntriesKeyName())
        );
        for ($i = 0, $n = $keys->count(); $i < $n; $i += 2) {
            $tmpKey = $keys->offsetGet($i);
            if ($tmpKey === null) {
                continue;
            }

            $tmpKey = $tmpKey->ensure();

            if ($tmpKey->getValue() === $key) {
                $keys->offsetUnset($i);
                // The index will be reset, because the previous unset.
                // So calling offsetUnset() on the same index is correct!
                $keys->offsetUnset($i);
                break;
            }
        }

        // If name matches a limit value, update them
        if ($i === 0 || $i === ($n - 2)) { // "-2" because values are stored by 2 entries: e.g. /Name (value)
            $newLeft = $keys->offsetGet(0);
            $newRight = $keys->offsetGet(max($keys->count() - 2, 0));

            foreach ($limitsNodes AS $limitsNode) {
                $limits = SetaPDF_Core_Type_Array::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($limitsNode, 'Limits'),
                    2
                );
                $left = SetaPDF_Core_Type_AbstractType::ensureType($limits->offsetGet(0));
                $right = SetaPDF_Core_Type_AbstractType::ensureType($limits->offsetGet(1));
                if ($left->getValue() === $key) {
                    $left->setValue($newLeft ? $newLeft->getValue() : ''); // TODO: Integer vs. Strings
                }

                if ($right->getValue() === $key) {
                    $right->setValue($newRight ? $newRight->getValue() : ''); // TODO: Integer vs. Strings
                }
            }
        }

        if (isset($this->_values[$key])) {
            unset($this->_values[$key]);
        }

        return true;
    }

    /**
     * Recreates and finalizes the tree
     *
     * This method will create a balanced tree structure and will create
     * new objects in the document.
     * This method could be used recreate the balanced tree or it is automatically
     * used if the initial tree was empty.
     *
     * @throws SetaPDF_Core_Type_Exception|SetaPDF_Core_Document_ObjectNotFoundException
     * @internal
     */
    public function arrange()
    {
        $all = $this->getAll();
        $tmpCount = count($all);
        $leafNodes = [];

        if ($tmpCount !== 0) {
            // Sort
            ksort($all, $this->_getSortType());

            $n = 0;
            $i = 0;

            // Setup initial leaf node
            $leafNodes[$i] = [
                'object' => $this->_getBlankLeafNodeObject(),
                'left' => '',
                'right' => ''
            ];

            $entriesKeyName = $this->_getEntriesKeyName();
            $currentLeafNode = SetaPDF_Core_Type_Dictionary::ensureType($leafNodes[$i]['object']);
            $currentKeys = SetaPDF_Core_Type_Dictionary_Helper::getValue($currentLeafNode, $entriesKeyName);
            $limits = SetaPDF_Core_Type_Array::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($currentLeafNode, 'Limits'),
                2
            );
            $leftLimitsSet = false;

            // Create leaf nodes
            foreach ($all AS $key => $value) {
                // set left limit
                if ($leftLimitsSet === false) {
                    SetaPDF_Core_Type_AbstractType::ensureType($limits->offsetGet(0))->setValue($key);
                    $leafNodes[$i]['left'] = $key;
                    $leftLimitsSet = true;
                }

                $currentKeys->offsetSet(null, $value['key']);
                $currentKeys->offsetSet(null, $value['value']);
                $n++;

                $isLastEntry = ($n === $tmpCount);
                if ($n % self::$leafNodesPerNode === 0 || $isLastEntry) {
                    // Set right limit 
                    SetaPDF_Core_Type_AbstractType::ensureType($limits->offsetGet(1))->setValue($key);
                    $leafNodes[$i]['right'] = $key;

                    // If this was the last entry break here
                    if ($isLastEntry) {
                        break;
                    }

                    // prepare the next leaf node
                    $i++;
                    $leafNodes[$i] = [
                        'object' => $this->_getBlankLeafNodeObject(),
                        'left' => '',
                        'right' => ''
                    ];
                    $currentLeafNode = SetaPDF_Core_Type_Dictionary::ensureType($leafNodes[$i]['object']);
                    $currentKeys = SetaPDF_Core_Type_Dictionary_Helper::getValue($currentLeafNode, $entriesKeyName);
                    $limits = SetaPDF_Core_Type_Array::ensureType(
                        SetaPDF_Core_Type_Dictionary_Helper::getValue($currentLeafNode, 'Limits'),
                        2
                    );
                    $leftLimitsSet = false;
                }
            }

            /* Now build intermediate nodes as long if they result in a 
             * bigger count than self::$leafNodesPerNode
             */
            while (count($leafNodes) > self::$leafNodesPerNode) {
                $currentLeafNodes = $leafNodes;
                $leafNodes = [];

                $tmpCount = count($currentLeafNodes);

                $i = 0;
                $n = 0;
                $leafNodes[$i] = [
                    'object' => $this->_getBlankLeafNodeObject(true),
                    'left' => '',
                    'right' => ''
                ];
                $currentLeafNode = SetaPDF_Core_Type_Dictionary::ensureType($leafNodes[$i]['object']);
                $currentKids = SetaPDF_Core_Type_Array::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($currentLeafNode, 'Kids')
                );
                $limits = SetaPDF_Core_Type_Array::ensureType(
                    SetaPDF_Core_Type_Dictionary_Helper::getValue($currentLeafNode, 'Limits'),
                    2
                );
                $leftLimitsSet = false;

                foreach ($currentLeafNodes AS $leafNode) {
                    // set left limit
                    if ($leftLimitsSet === false) {
                        SetaPDF_Core_Type_AbstractType::ensureType($limits->offsetGet(0))->setValue($leafNode['left']);
                        $leafNodes[$i]['left'] = $leafNode['left'];
                        $leftLimitsSet = true;
                    }

                    $currentKids->offsetSet(null, $leafNode['object']);
                    $n++;

                    $isLastEntry = ($n === $tmpCount);
                    if ($n % self::$leafNodesPerNode === 0 || $isLastEntry) {
                        // Set right limit
                        SetaPDF_Core_Type_AbstractType::ensureType($limits->offsetGet(1))->setValue($leafNode['right']);
                        $leafNodes[$i]['right'] = $leafNode['right'];

                        // If this was the last entry break here
                        if ($isLastEntry) {
                            break;
                        }

                        // prepare the next leaf node
                        $i++;
                        $leafNodes[$i] = [
                            'object' => $this->_getBlankLeafNodeObject(true),
                            'left' => '',
                            'right' => ''
                        ];
                        $currentLeafNode = SetaPDF_Core_Type_Dictionary::ensureType($leafNodes[$i]['object']);
                        $currentKids = SetaPDF_Core_Type_Dictionary_Helper::getValue($currentLeafNode, 'Kids');
                        $limits = SetaPDF_Core_Type_Array::ensureType(
                            SetaPDF_Core_Type_Dictionary_Helper::getValue($currentLeafNode, 'Limits'),
                            2
                        );
                        $leftLimitsSet = false;
                    }
                }
            }
        }

        // Now create/update the root node
        $rootNode = $this->_rootDictionary;

        // Clean up existing name tree
        if ($rootNode->offsetExists('Kids')) {
            $kids = SetaPDF_Core_Type_Array::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($rootNode, 'Kids')
            );
            $toDelete = [];

            $_kids = $kids->getValue();
            while (count($_kids) > 0) {
                $kid = SetaPDF_Core_Type_IndirectReference::ensureType(array_pop($_kids));
                $toDelete[] = [$kid->getObjectId(), $kid->getGen()];
                $kid = SetaPDF_Core_Type_Dictionary::ensureType($kid->ensure());
                if ($kid->offsetExists('Kids')) {
                    $additionalKids = SetaPDF_Core_Type_Array::ensureType(
                        SetaPDF_Core_Type_Dictionary_Helper::getValue($kid, 'Kids', new SetaPDF_Core_Type_Array())
                    );
                    foreach ($additionalKids as $_kid) {
                        $_kids[] = $_kid;
                    }
                }
            }

            unset($kid);

            foreach ($toDelete AS $objectDef) {
                $this->_document->deleteObjectById($objectDef[0], $objectDef[1]);
            }

            $kids->clear();

        } else {
            $kids = new SetaPDF_Core_Type_Array();
            $rootNode->offsetSet('Kids', $kids);
        }

        foreach ($leafNodes AS $leafNode) {
            $kids->offsetSet(null, $leafNode['object']);
        }

        $node = $this->_rootDictionary;
        $node->unRegisterPdfStringCallback('arrangeTree');
        $this->_isNew = false;
    }

    /**
     * Helper method to create a blank leaf node
     *
     * @param boolean $intermediate
     * @return SetaPDF_Core_Type_IndirectObject
     */
    private function _getBlankLeafNodeObject($intermediate = false)
    {
        $keyClassName = $this->_getKeyClassName();

        $node = new SetaPDF_Core_Type_Dictionary();
        $node->offsetSet('Limits', new SetaPDF_Core_Type_Array([
            new $keyClassName(), new $keyClassName(),
        ]));

        if ($intermediate === false) {
            $node->offsetSet(
                $this->_getEntriesKeyName(),
                $this->_document->createNewObject(new SetaPDF_Core_Type_Array())
            );
        } else {
            $node->offsetSet(
                'Kids',
                $this->_document->createNewObject(new SetaPDF_Core_Type_Array())
            );
        }

        return $this->_document->createNewObject($node);
    }

    /**
     * Get the root dictionary entry
     *
     * @see SetaPDF_Core_DataStructure_DataStructureInterface::getValue()
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getValue()
    {
        return $this->_rootDictionary;
    }

    /**
     * Returns an array with named keys and name values
     *
     * @return array
     * @throws SetaPDF_Core_Type_Exception
     * @see SetaPDF_Core_DataStructure_DataStructureInterface::toPhp()
     */
    public function toPhp()
    {
        $data = [];

        foreach ($this->getAll() AS $name => $nameData) {
            $data[$name] = $nameData['value']->toPhp();
        }

        return $data;
    }

    /**
     * Release objects to free memory and cycled references
     *
     * After calling this method the instance of this object is unusable!
     *
     * @return void
     */
    public function cleanUp()
    {
        $this->_rootDictionary = null;
        $this->_values = [];
        $this->_document = null;
    }
}
