<?php 
/**
 * This file is part of the SetaPDF-Core Component
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: OptionalContent.php 1753 2022-06-28 14:42:54Z maximilian.kresse $
 */

/**
 * Class for handling optional content
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_OptionalContent implements IteratorAggregate
{
    /**
     * State constant
     * 
     * @var string
     */
    const STATE_ON = 'ON';
    
    /**
     * State constant
     * 
     * @var string
     */
    const STATE_OFF = 'OFF';
    
    /**
     * State constant
     * 
     * @var string
     */
    const STATE_UNCHANGED = 'Unchanged';
    
    /**
     * The documents catalog instance
     *
     * @var SetaPDF_Core_Document_Catalog
     */
    protected $_catalog;

    /**
     * The optional contents properties dictionary
     *  
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_propertiesDictionary;
    
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
     * Release resources / cycled references.
     */
    public function cleanUp()
    {
        $this->_catalog = null;
        $this->_propertiesDictionary = null;
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
     * Get and creates the optional content properties dictionary.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getOptionalContentPropertiesDictionary($create = false)
    {
        if ($this->_propertiesDictionary === null) {
            $catalog = $this->_catalog->getDictionary($create);
            if ($catalog === null) {
                return null;
            }

            $ocPropertiesDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'OCProperties');
            if ($ocPropertiesDict === null) {
                if ($create === false) {
                    return null;
                }

                $ocPropertiesDict = new SetaPDF_Core_Type_Dictionary();
                $ocPropertiesDict->offsetSet('OCGs', new SetaPDF_Core_Type_Array());
                $ocPropertiesDict->offsetSet('D', new SetaPDF_Core_Type_Dictionary([
                    'Order' => new SetaPDF_Core_Type_Array()
                ]));

                $catalog->offsetSet('OCProperties', $this->getDocument()->createNewObject($ocPropertiesDict));
            }

            $this->_propertiesDictionary = SetaPDF_Core_Type_Dictionary::ensureType($ocPropertiesDict);
        }
    
        return $this->_propertiesDictionary;
    }

    /**
     * Get the default viewing dictionary.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see PDF 32000-1:2008 - 8.11.4.2 Optional Content Properties Dictionary
     */
    public function getDefaultViewingDictionary($create = false)
    {
        $propertiesDictionary = $this->getOptionalContentPropertiesDictionary($create);
        if ($propertiesDictionary === null) {
            return null;
        }

        $d = SetaPDF_Core_Type_Dictionary_Helper::getValue($propertiesDictionary, 'D');
        if ($d === null) {
            if ($create === false) {
                return null;
            }

            $d = new SetaPDF_Core_Type_Dictionary();
            $propertiesDictionary->offsetSet('D', $d);
        }

        return $d;
    }

    /**
     * Get and/or create an array entry in the default viewing dictionary.
     *
     * @param string $name
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _getArrayFromDefaultViewing($name, $create = false)
    {
        $defaultViewingDictionary = $this->getDefaultViewingDictionary($create);
        if ($defaultViewingDictionary === null) {
            return null;
        }

        $array = SetaPDF_Core_Type_Dictionary_Helper::getValue($defaultViewingDictionary, $name);
        if ($array === null) {
            if ($create === false) {
                return null;
            }

            $array = new SetaPDF_Core_Type_Array();
            $defaultViewingDictionary->offsetSet($name, $array);
        }

        return $array;
    }

    /**
     * Get and/or create the Order array in the default viewing dictionary.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see PDF 32000-1:2008 - 8.11.4.3 Optional Content Configuration Dictionaries
     */
    public function getOrderArray($create = false)
    {
        return $this->_getArrayFromDefaultViewing('Order', $create);
    }

    /**
     * Get and/or create the ON array in the default viewing dictionary.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see PDF 32000-1:2008 - 8.11.4.3 Optional Content Configuration Dictionaries
     */
    public function getOnArray($create = false)
    {
        return $this->_getArrayFromDefaultViewing('ON', $create);
    }

    /**
     * Get and/or create the OFF array in the default viewing dictionary.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see PDF 32000-1:2008 - 8.11.4.3 Optional Content Configuration Dictionaries
     */
    public function getOffArray($create = false)
    {
        return $this->_getArrayFromDefaultViewing('OFF', $create);
    }

    /**
     * Get and/or create the AS array in the default viewing dictionary.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see PDF 32000-1:2008 - 8.11.4.3 Optional Content Configuration Dictionaries
     */
    public function getAsArray($create = false)
    {
        return $this->_getArrayFromDefaultViewing('AS', $create);
    }

    /**
     * Get the base state from the default viewing dictionary.
     *
     * @return string
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see PDF 32000-1:2008 - 8.11.4.3 Optional Content Configuration Dictionaries
     */
    public function getBaseState()
    {
        $defaultViewingDictionary = $this->getDefaultViewingDictionary();
        if ($defaultViewingDictionary === null) {
            return self::STATE_ON;
        }

        return SetaPDF_Core_Type_Dictionary_Helper::getValue(
            $defaultViewingDictionary, 'BaseState', self::STATE_ON, true
        );
    }

    /**
     * Set the base state in the default viewing dictionary.
     *
     * @param string $baseState
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see PDF 32000-1:2008 - 8.11.4.3 Optional Content Configuration Dictionaries
     */
    public function setBaseState($baseState)
    {
        $defaultViewingDictionary = SetaPDF_Core_Type_Dictionary::ensureType($this->getDefaultViewingDictionary(true));
        $defaultViewingDictionary->offsetSet('BaseState', new SetaPDF_Core_Type_Name($baseState));
    }

    /**
     * @param SetaPDF_Core_Document_OptionalContent_Group $group
     * @param bool $on
     * @return void
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _setOnOrOff(SetaPDF_Core_Document_OptionalContent_Group $group, $on = true)
    {
        if ($on) {
            $toAdd = SetaPDF_Core_Type_Array::ensureType($this->getOnArray(true));
            $toRemove = SetaPDF_Core_Type_Array::ensureType($this->getOffArray(true));
        } else {
            $toAdd = SetaPDF_Core_Type_Array::ensureType($this->getOffArray(true));
            $toRemove = SetaPDF_Core_Type_Array::ensureType($this->getOnArray(true));
        }

        /** @var SetaPDF_Core_Type_IndirectReference $indirectObject */
        $indirectObject = $group->getIndirectObject($this->getDocument());
        if ($toAdd->indexOf($indirectObject) === -1) {
            $toAdd[] = $indirectObject;
        }

        $removeIndex = $toRemove->indexOf($indirectObject);
        if ($removeIndex !== -1) {
            $toRemove->offsetUnset($removeIndex);
        }
    }

    /**
     * Set the default state of the optional content group to on.
     *
     * @param SetaPDF_Core_Document_OptionalContent_Group $group
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setOn(SetaPDF_Core_Document_OptionalContent_Group $group)
    {
        $this->_setOnOrOff($group);
    }

    /**
     * Set the default state of the optional content group to off.
     *
     * @param SetaPDF_Core_Document_OptionalContent_Group $group
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setOff(SetaPDF_Core_Document_OptionalContent_Group $group)
    {
        $this->_setOnOrOff($group, false);
    }

    /**
     * Create and add usage application dictionaries for the given optional content group.
     *
     * The usage definition in an optional content group will only apply to automatically
     * adjustment if the group is referenced by a usage application dictionary.
     *
     * <code>
     * $triangle = $optionalContent->appendGroup('Triangle');
     * // Define the usage
     * $triangle->usage()->setPrintState(SetaPDF_Core_Document_Catalog_OptionalContent::STATE_OFF);
     * // Now add it to an usage application dictionary
     * $optionalContent->addUsageApplication($triangle);
     * </code>
     *
     * @param SetaPDF_Core_Document_OptionalContent_Group $group
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see SetaPDF_Core_Document_OptionalContent_Group_Usage
     */
    public function addUsageApplication(SetaPDF_Core_Document_OptionalContent_Group $group)
    {
        $usage = $group->usage();
        $states = array_filter([
            'View' => $usage->getViewState(),
            'Print' => $usage->getPrintState(),
            'Export' => $usage->getExportState()
        ]);
        
        if (count($states) === 0) {
            return;
        }
        
        $as = SetaPDF_Core_Type_Array::ensureType($this->getAsArray(true));
        
        foreach ($states AS $name => $state) {
            $as[] = new SetaPDF_Core_Type_Dictionary([
                'Category' => new SetaPDF_Core_Type_Array([new SetaPDF_Core_Type_Name($name)]),
                'Event' => new SetaPDF_Core_Type_Name($name),
                'OCGs' => new SetaPDF_Core_Type_Array([$group->getIndirectObject($this->getDocument())]),
            ]);
        }
    }

    /**
     * Get all available content groups.
     *
     * @return SetaPDF_Core_Document_OptionalContent_Group[]
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getGroups()
    {
        $propertiesDictionary = $this->getOptionalContentPropertiesDictionary();
        if ($propertiesDictionary === null) {
            return [];
        }

        $optionalContentGroups = [];
        $_optionalContentGroups = SetaPDF_Core_Type_Dictionary_Helper::getValue($propertiesDictionary, 'OCGs');
        if ($_optionalContentGroups instanceof SetaPDF_Core_Type_Array) {
            foreach ($_optionalContentGroups AS $_optionalContentGroup) {
                $optionalContentGroups[] = new SetaPDF_Core_Document_OptionalContent_Group($_optionalContentGroup);
            }
        }
        
        return $optionalContentGroups;
    }

    /**
     * Get a group by its name.
     *
     * @param string $name The group name
     * @param string $encoding The input encoding
     * @return false|SetaPDF_Core_Document_OptionalContent_Group
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getGroup($name, $encoding = 'UTF-8')
    {
        $propertiesDictionary = $this->getOptionalContentPropertiesDictionary();
        if ($propertiesDictionary === null) {
            return false;
        }

        $optionalContentGroups = SetaPDF_Core_Type_Dictionary_Helper::getValue($propertiesDictionary, 'OCGs');
        if ($optionalContentGroups instanceof SetaPDF_Core_Type_Array) {
            foreach ($optionalContentGroups as $_optionalContentGroup) {
                $object = new SetaPDF_Core_Document_OptionalContent_Group($_optionalContentGroup);
                if ($object->getName($encoding) === $name) {
                    return $object;
                }
            }
        }
        
        return false;
    }

    /**
     * This method adds a method to the OCGs array.
     *
     * By adding a group with this method the group will not be added to the user
     * interface.
     *
     * @param SetaPDF_Core_Document_OptionalContent_Group|string $group
     * @return SetaPDF_Core_Document_OptionalContent_Group
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function addGroup($group)
    {
        if (!($group instanceof SetaPDF_Core_Document_OptionalContent_Group)) {
            $group = new SetaPDF_Core_Document_OptionalContent_Group($group);
        }
        
        $propertiesDictionary = SetaPDF_Core_Type_Dictionary::ensureType(
            $this->getOptionalContentPropertiesDictionary(true)
        );
        $optionalContentGroups = SetaPDF_Core_Type_Array::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($propertiesDictionary, 'OCGs')
        );

        /** @var SetaPDF_Core_Type_IndirectReference $indirectObject */
        $indirectObject = $group->getIndirectObject($this->getDocument());
        if ($optionalContentGroups->indexOf($indirectObject) === -1) {
            $optionalContentGroups[] = $indirectObject;
        }
        
        return $group;
    }

    /**
     * Append an optional content group to the outline structure.
     *
     * @param string|SetaPDF_Core_Document_OptionalContent_Group $group
     * @param SetaPDF_Core_Document_OptionalContent_Group $parent
     * @param integer|null $afterIndex
     * @param string|SetaPDF_Core_Document_OptionalContent_Group $nextToOrLabel
     * @param string $label
     * @return SetaPDF_Core_Document_OptionalContent_Group
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function appendGroup(
        $group,
        SetaPDF_Core_Document_OptionalContent_Group $parent = null,
        $afterIndex = null,
        $nextToOrLabel = null,
        $label = ''
    )
    {
        if ($afterIndex !== null) {
            return $this->prependGroup($group, $parent, $afterIndex + 1, $nextToOrLabel, $label);
        }
        
        $group = $this->addGroup($group);
        
        $order = SetaPDF_Core_Type_Array::ensureType($this->getOrderArray(true));
        if ($parent !== null) {
            $order = $this->_findAndPrepareOrderEntry($order, $parent);
        }
        
        if ($nextToOrLabel !== null) {
            if ($nextToOrLabel instanceof SetaPDF_Core_Document_OptionalContent_Group) {
                $order = $this->_findOrderArrayByGroup($order, $nextToOrLabel);
            }
            
            if (is_string($nextToOrLabel)) {
                $label = $nextToOrLabel;
            }
            
            if ($label !== '') {
                $element = new SetaPDF_Core_Type_Array([
                    new SetaPDF_Core_Type_String($label),
                    $group->getIndirectObject($this->getDocument())
                ]);
                
            } else {
                $element = $group->getIndirectObject($this->getDocument());
            }
            
        } else {
            $element = $group->getIndirectObject($this->getDocument());
        }
        
        $order[] = $element;
        
        return $group;
    }

    /**
     * Prepends an optional content group to the outline structure.
     *
     * If the $beforeIndex parameter is out of range the group will be appended.
     *
     * @param string|SetaPDF_Core_Document_OptionalContent_Group $group
     * @param SetaPDF_Core_Document_OptionalContent_Group $parent
     * @param integer|null $beforeIndex
     * @param string|SetaPDF_Core_Document_OptionalContent_Group $nextToOrLabel
     * @param string $label
     * @return SetaPDF_Core_Document_OptionalContent_Group
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function prependGroup(
        $group,
        SetaPDF_Core_Document_OptionalContent_Group $parent = null,
        $beforeIndex = 0,
        $nextToOrLabel = null,
        $label = ''
    )
    {
        $group = $this->addGroup($group);
    
        $order = SetaPDF_Core_Type_Array::ensureType($this->getOrderArray(true));
        if ($parent !== null) {
            $order = $this->_findAndPrepareOrderEntry($order, $parent);
        }
    
        if ($nextToOrLabel !== null) {
            if ($nextToOrLabel instanceof SetaPDF_Core_Document_OptionalContent_Group) {
                $order = $this->_findOrderArrayByGroup($order, $nextToOrLabel);
            }
            
            if (is_string($nextToOrLabel)) {
                $label = $nextToOrLabel;
            }
            
            if ($label !== '') {
                $element = new SetaPDF_Core_Type_Array([
                    new SetaPDF_Core_Type_String($label),
                    $group->getIndirectObject($this->getDocument()
                )]);
                
            } else {
                $element = $group->getIndirectObject($this->getDocument());
            }
            
        } else {
            $element = $group->getIndirectObject($this->getDocument());
        }
        
        if ($beforeIndex >= $order->count()) {
            $order[] = $element;
        } else {
            // If the current array begins with a label, we have to adjust the $beforeIndex value
            if (SetaPDF_Core_Type_AbstractType::ensureType($order->offsetGet(0)) instanceof SetaPDF_Core_Type_StringValue) {
                $beforeIndex++;
            }
                
            $order->insertBefore($element, $beforeIndex);
        }
         
        return $group;
    }

    /**
     * Finds the correct order array entry by an optional content group object.
     *
     * @param SetaPDF_Core_Type_Array $currentArray
     * @param SetaPDF_Core_Document_OptionalContent_Group $group
     * @param integer $key
     * @return SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @internal
     */
    protected function _findOrderArrayByGroup(
        SetaPDF_Core_Type_Array $currentArray,
        SetaPDF_Core_Document_OptionalContent_Group $group,
        &$key = null
    )
    {
        $originalCurrentArray = $currentArray;
        $orderArray = $this->getOrderArray();
        if ($orderArray === null) {
            return $originalCurrentArray;
        }
        
        $iterator = new RecursiveIteratorIterator($orderArray);
        $objectIdent = $group->getIndirectObject($this->getDocument())->getObjectIdent();

        /** @noinspection SuspiciousLoopInspection */
        foreach ($iterator AS $key => $value) {
            /** @var SetaPDF_Core_Type_Array $currentArray */
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $currentArray = $iterator->getInnerIterator();
            if (!($value instanceof SetaPDF_Core_Type_IndirectObjectInterface)) {
                continue;
            }
            
            if ($value->getObjectIdent() === $objectIdent) {
                return $currentArray;
            }
        }
            
        $key = null;
        
        return $originalCurrentArray;
    }

    /**
     * Finds and prepares an order array.
     *
     * @param SetaPDF_Core_Type_Array $currentArray
     * @param SetaPDF_Core_Document_OptionalContent_Group $parent
     * @return SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @internal
     */
    protected function _findAndPrepareOrderEntry(
        SetaPDF_Core_Type_Array $currentArray,
        SetaPDF_Core_Document_OptionalContent_Group $parent
    )
    {
        $originalCurrentArray = $currentArray;
        
        $key = null;
        $currentArray = $this->_findOrderArrayByGroup($currentArray, $parent, $key);
        if ($key !== null) {
            $nextItem = $currentArray->offsetGet($key + 1);

            // We are at the end of the current array
            if ($nextItem === null) {
                $newArray = new SetaPDF_Core_Type_Array();
                $currentArray[] = $newArray;
                $currentArray = $newArray;
            
                // The next item is not an array
            } elseif (!($nextItem->ensure() instanceof SetaPDF_Core_Type_Array)) {
                $newArray = new SetaPDF_Core_Type_Array();
                $currentArray->insertBefore($newArray, $key + 1);
                $currentArray = $newArray;
                 
                // Next entry is an array, so use it!
            } else {
                $currentArray = SetaPDF_Core_Type_Array::ensureType($nextItem->ensure());
            }
            
            return $currentArray;
        } 
        
        return $originalCurrentArray;
    }

    /**
     * Implementation of IteratorAggregate.
     *
     * A separate iterator is needed to receive {@link SetaPDF_Core_Document_OptionalContent_Group} objects while iterating.
     *
     * @return SetaPDF_Core_Document_OptionalContent_Iterator|EmptyIterator
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     * @see IteratorAggregate::getIterator()
     */
    #[\ReturnTypeWillChange]
    public function getIterator() {
        $orderArray = $this->getOrderArray();
        if ($orderArray === null) {
            return new EmptyIterator();
        }
        
        return new SetaPDF_Core_Document_OptionalContent_Iterator($orderArray);
    }
}
