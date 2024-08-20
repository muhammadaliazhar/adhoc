<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: OutputIntents.php 1740 2022-06-17 10:39:27Z jan.slabon $
 */

/**
 * Class representing the output intents entry
 *
 * @see PDF 32000-1:2008 - 14.11.5 Output Intents
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_OutputIntents
{
    /**
     * The documents catalog instance
     *
     * @var SetaPDF_Core_Document
     */
    protected $_catalog;

    /**
     * The output intents array
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_array;

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
     * Release cycled references.
     */
    public function cleanUp()
    {
        $this->_array = null;
        $this->_catalog = null;
    }

    /**
     * Get and creates the OutputIntents array.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getArray($create = false)
    {
        if ($this->_array === null) {
            $catalog = $this->_catalog->getDictionary($create);
            if ($catalog === null) {
                return null;
            }

            $outputIntents = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'OutputIntents');
            if ($outputIntents instanceof SetaPDF_Core_Type_Array) {
                $this->_array = $outputIntents;
                return $this->_array;
            }

            if ($create) {
                $this->_array = new SetaPDF_Core_Type_Array();

                $object = $this->getDocument()->createNewObject($this->_array);
                $catalog->offsetSet('OutputIntents', $object);
            }
        }

        return $this->_array;
    }

    /**
     * Get an array of available output intents.
     *
     * @return SetaPDF_Core_OutputIntent[]
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getOutputIntents()
    {
        $result = [];
        $array = $this->getArray();
        if ($array === null) {
            return $result;
        }

        foreach ($array AS $outputIntentDictionary) {
            $result[] = new SetaPDF_Core_OutputIntent(SetaPDF_Core_Type_Dictionary::ensureType($outputIntentDictionary));
        }

        return $result;
    }

    /**
     * Add an output intent.
     *
     * @param SetaPDF_Core_OutputIntent $outputIntent
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function addOutputIntent(SetaPDF_Core_OutputIntent $outputIntent)
    {
        /** @var SetaPDF_Core_Type_Array $array */
        $array = $this->getArray(true);
        $array->push($outputIntent->getDictionary());
    }
}
