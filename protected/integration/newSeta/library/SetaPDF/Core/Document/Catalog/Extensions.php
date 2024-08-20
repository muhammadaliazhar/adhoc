<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Extensions.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Class for handling the catalogs extensions dictionary
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_Extensions
{
    /**
     * The catalog instance
     *
     * @var SetaPDF_Core_Document_Catalog
     */
    protected $_catalog;

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
     * Release memory and cycled references.
     */
    public function cleanUp()
    {
        $this->_catalog = null;
    }

    /**
     * Get the extensions dictionary.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getDictionary($create = false)
    {
        $root = $this->_catalog->getDictionary($create);
        if ($root === null) {
            return null;
        }

        $extensions = SetaPDF_Core_Type_Dictionary_Helper::getValue($root, 'Extensions');
        if ($extensions === null) {
            if ($create === false) {
                return null;
            }

            $extensions = new SetaPDF_Core_Type_Dictionary();
            $root->offsetSet('Extensions', $extensions);
        }

        return $extensions;
    }

    /**
     * Get all defined developer extensions.
     *
     * The method will return an array of the following structure:
     * [$name => [baseVersion => "...", extensionLevel => "..."], ...]
     *
     * @return array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getExtensions()
    {
        $result = [];
        $dictionary = $this->getDictionary();
        if ($dictionary === null) {
            return $result;
        }

        foreach ($dictionary AS $name => $values) {
            $values = SetaPDF_Core_Type_Dictionary::ensureType($values);

            $baseVersion = SetaPDF_Core_Type_Name::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($values, 'BaseVersion')
            )->getValue();

            $extensionLevel = (int)SetaPDF_Core_Type_Numeric::ensureType(
                SetaPDF_Core_Type_Dictionary_Helper::getValue($values, 'ExtensionLevel')
            )->getValue();

            $result[$name] = [
                'baseVersion' => $baseVersion,
                'extensionLevel' => $extensionLevel
            ];
        }

        return $result;
    }

    /**
     * Get a developer extension by its name.
     *
     * This method will return an array with the "baseVersion" and "extensionLevel" keys or false
     * if no extension was found.
     *
     * @param string $name
     * @return array|bool
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getExtension($name)
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null || !$dictionary->offsetExists($name)) {
            return false;
        }

        $values = SetaPDF_Core_Type_Dictionary::ensureType($dictionary->getValue($name));

        $baseVersion = SetaPDF_Core_Type_Name::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($values, 'BaseVersion')
        )->getValue();

        $extensionLevel = (int)SetaPDF_Core_Type_Numeric::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($values, 'ExtensionLevel')
        )->getValue();

        return [
            'baseVersion' => $baseVersion,
            'extensionLevel' => $extensionLevel
        ];
    }

    /**
     * Set the data of a developer extension.
     *
     * @param string $name
     * @param string $baseVersion
     * @param integer $extensionLevel
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setExtension($name, $baseVersion, $extensionLevel)
    {
        $dictionary = SetaPDF_Core_Type_Dictionary::ensureType($this->getDictionary(true));
        $dictionary->offsetSet($name, new SetaPDF_Core_Type_Dictionary([
            'BaseVersion' => new SetaPDF_Core_Type_Name($baseVersion),
            'ExtensionLevel' => new SetaPDF_Core_Type_Numeric((int)$extensionLevel)
        ]));
    }

    /**
     * Removes a developer extension from the dictionary.
     *
     * @param string $name
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function removeExtension($name)
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null || !$dictionary->offsetExists($name)) {
            return false;
        }

        $dictionary->offsetUnset($name);

        return true;
    }
}
