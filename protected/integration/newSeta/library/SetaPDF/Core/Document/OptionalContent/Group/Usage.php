<?php 
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Usage.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * A helper class for an optional content group object to manage the usage dictionary
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_OptionalContent_Group_Usage
{
    /**
     * The optional content group object
     * 
     * @var SetaPDF_Core_Document_OptionalContent_Group
     */
    protected $_group;
    
    /**
     * The constructor .
     * 
     * @param SetaPDF_Core_Document_OptionalContent_Group $group
     */
    public function __construct(SetaPDF_Core_Document_OptionalContent_Group $group)
    {
        $this->_group = $group;
    }
    
    /**
     * Release resources / cycled references.
     */
    public function cleanUp()
    {
        $this->_group = null;
    }
    
    /**
     * Gets and/or creates the usage dictionary.
     *
     * Method is normally only used internally.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary($create = false)
    {
        $groupDictionary = $this->_group->getDictionary();
        $dictionary = SetaPDF_Core_Type_Dictionary_Helper::getValue($groupDictionary, 'Usage');
        if (!$dictionary instanceof SetaPDF_Core_Type_Dictionary) {
            if ($create === false) {
                return null;
            }

            $dictionary = new SetaPDF_Core_Type_Dictionary();
            $groupDictionary->offsetSet('Usage', $dictionary);
        }

        return $dictionary;
    }
    
    /**
     * Set the print state.
     * 
     * @param string|false $state
     * @see PDF 32000-1:2008 - 8.11.4.4 Usage and Usage Application Dictionaries
     */
    public function setPrintState($state = SetaPDF_Core_Document_Catalog_OptionalContent::STATE_ON)
    {
        $this->_setState('Print', $state);
    }
    
    /**
     * Get the print state.
     *
     * @return string|null
     * @see PDF 32000-1:2008 - 8.11.4.4 Usage and Usage Application Dictionaries
     */
    public function getPrintState()
    {
        return $this->_getState('Print');
    }
    
    /**
     * Set the view state.
     *
     * @param string|false $state
     * @see PDF 32000-1:2008 - 8.11.4.4 Usage and Usage Application Dictionaries
     */
    public function setViewState($state = SetaPDF_Core_Document_Catalog_OptionalContent::STATE_ON)
    {
        $this->_setState('View', $state);
    }
    
    /**
     * Get the view state.
     *
     * @return string|null
     * @see PDF 32000-1:2008 - 8.11.4.4 Usage and Usage Application Dictionaries
     */
    public function getViewState()
    {
        return $this->_getState('View');
    }
    
    /**
     * Set the export state.
     *
     * @param string|false $state
     * @see PDF 32000-1:2008 - 8.11.4.4 Usage and Usage Application Dictionaries
     */
    public function setExportState($state = SetaPDF_Core_Document_Catalog_OptionalContent::STATE_ON)
    {
        $this->_setState('Export', $state);
    }
    
    /**
     * Get the export state.
     *
     * @return string|null
     * @see PDF 32000-1:2008 - 8.11.4.4 Usage and Usage Application Dictionaries
     */
    public function getExportState()
    {
        return $this->_getState('Export');
    }
    
    /**
     * Set a state by name.
     * 
     * @param string $name
     * @param string $state
     */
    protected function _setState($name, $state)
    {
        /** @var SetaPDF_Core_Type_Dictionary $usageDict */
        $usageDict = $this->getDictionary(true);
        if ($state === false) {
            $usageDict->offsetUnset($name);
            return;
        }


        $valueDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($usageDict, $name);
        if (!$valueDict instanceof SetaPDF_Core_Type_Dictionary) {
            $valueDict = new SetaPDF_Core_Type_Dictionary();
            $usageDict->offsetSet($name, $valueDict);
        }

        $valueDict->offsetSet($name . 'State', new SetaPDF_Core_Type_Name($state));
    }
    
    /**
     * Get a state by name.
     *
     * @param string $name
     * @return string
     */
    protected function _getState($name)
    {
        $usageDict = $this->getDictionary();
        if (!$usageDict instanceof SetaPDF_Core_Type_Dictionary) {
            return null;
        }

        $valueDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($usageDict, $name);
        if (!$valueDict instanceof SetaPDF_Core_Type_Dictionary) {
            return null;
        }

        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($valueDict, $name . 'State');
        if (!$value instanceof SetaPDF_Core_Type_Name) {
            return null;
        }

        return $value->getValue();
    }
}
