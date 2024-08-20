<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id$
 */

/**
 * Class representing the access to the StructTreeRoot dictionary of a document
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_StructTreeRoot
{
    /**
     * The catalog instance
     *
     * @var SetaPDF_Core_Document_Catalog
     */
    protected $_catalog;

    /**
     * @var SetaPDF_Core_DataStructure_NumberTree
     */
    protected $_parentTree;

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
     * Release memory and cycled references.
     */
    public function cleanUp()
    {
        $this->_catalog = null;
    }

    /**
     * Gets and creates the indirect object of the StructTreeRoot entry.
     *
     * @param bool $create
     * @return SetaPDF_Core_Type_IndirectObject|null
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getObject($create = false)
    {
        $catalogDict = $this->_catalog->getDictionary($create);
        if (!$catalogDict instanceof SetaPDF_Core_Type_Dictionary ||
            (!$catalogDict->offsetExists('StructTreeRoot') && $create === false)
        ) {
            return null;
        }

        $object = $catalogDict->getValue('StructTreeRoot');

        if (!$object instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            if ($create === false) {
                return null;
            }

            $dictionary = new SetaPDF_Core_Type_Dictionary([
                'Type' => new SetaPDF_Core_Type_Name('StructTreeRoot', true)
            ]);
            $object = $this->getDocument()->createNewObject($dictionary);

            $catalogDict->offsetSet('StructTreeRoot', $object);
        }

        return $object;
    }

    /**
     * Get and creates the dictionary of the StructTreeRoot entry.
     *
     * @param bool $create
     * @return SetaPDF_Core_Type_Dictionary|null
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getDictionary($create = false)
    {
        $object = $this->getObject($create);
        if ($object === null) {
            return null;
        }

        $dictionary = SetaPDF_Core_Type_Dictionary::ensureType($object);

        if (SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Type', null, true) !== 'StructTreeRoot') {
            throw new SetaPDF_Core_Type_Exception('Invalid /Type value. Expected "StructTreeRoot".');
        }

        return $dictionary;
    }

    /**
     * Checks whether the StructTreeRoot dictionary exists or not.
     *
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function exists()
    {
        return $this->getDictionary() !== null;
    }

    /**
     * Get the immediate children of the structure tree root.
     *
     * @return array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getChilds()
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null) {
            return [];
        }

        $k = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'K');
        if ($k === null) {
            return [];
        }

        if ($k instanceof SetaPDF_Core_Type_Dictionary) {
            return [$k];
        }

        if ($k instanceof SetaPDF_Core_Type_Array) {
            $childs = [];
            foreach ($k->getValue() as $value) {
                $childs[] = SetaPDF_Core_Type_Dictionary::ensureType($value);
            }

            return $childs;
        }

        throw new SetaPDF_Core_Type_Exception('Invalid data type for children of the structure tree root.');
    }

    /**
     * Gets and create the parent tree.
     *
     * @param bool $create
     * @return SetaPDF_Core_DataStructure_NumberTree|null
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getParentTree($create = false)
    {
        if ($this->_parentTree === null) {
            $dictionary = $this->getDictionary($create);
            if (!$dictionary instanceof SetaPDF_Core_Type_Dictionary ||
                (!$dictionary->offsetExists('ParentTree') && $create === false)
            ) {
                return null;
            }

            $object = $dictionary->getValue('ParentTree');

            if (!$object instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
                if ($create === false) {
                    return null;
                }

                $object = $this->getDocument()->createNewObject(new SetaPDF_Core_Type_Dictionary([
                    'Nums' => new SetaPDF_Core_Type_Array([])
                ]));

                $dictionary->offsetSet('ParentTree', $object);
            }

            $this->_parentTree = new SetaPDF_Core_DataStructure_NumberTree(
                SetaPDF_Core_Type_Dictionary::ensureType($object), $this->getDocument()
            );
        }

        return $this->_parentTree;
    }

    /**
     * Get and sets the next key in the parent tree (if it not already exists).
     *
     * @return int
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getParentTreeNextKey()
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null) {
            return 0;
        }

        $parentTreeNextKey = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'ParentTreeNextKey');

        if (!$parentTreeNextKey instanceof SetaPDF_Core_Type_Numeric) {
            /** @var SetaPDF_Core_DataStructure_NumberTree $parentTree */
            $parentTree = $this->getParentTree(true);
            $allKeys = $parentTree->getAll(true);
            $lastKey = count($allKeys) > 0 ? max($allKeys) : -1;
            $parentTreeNextKey = new SetaPDF_Core_Type_Numeric($lastKey + 1);
            $dictionary->offsetSet('ParentTreeNextKey', $parentTreeNextKey);
        }

        return $parentTreeNextKey->getValue();
    }

    /**
     * Get the current next key for the parent tree and increase it.
     *
     * @return int
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAndIncrementParentTreeNextKey()
    {
        /** @var SetaPDF_Core_Type_Dictionary $dictionary */
        $dictionary = $this->getDictionary(true);
        $current = $this->getParentTreeNextKey();
        $parentTreeNextKey = SetaPDF_Core_Type_Numeric::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'ParentTreeNextKey')
        );

        $parentTreeNextKey->setValue($current + 1);

        return $current;
    }

    /**
     * Add a child element.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface $object
     * @param null $beforeIndex
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function addChild(SetaPDF_Core_Type_IndirectObjectInterface $object, $beforeIndex = null)
    {
        /** @var SetaPDF_Core_Type_Dictionary $dictionary */
        $dictionary = $this->getDictionary(true);
        $k = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'K');
        if ($k === null) {
            $k = new SetaPDF_Core_Type_Array();
            $dictionary->offsetSet('K', $this->getDocument()->createNewObject($k));
        }

        if (!$k instanceof SetaPDF_Core_Type_Array) {
            $k = new SetaPDF_Core_Type_Array([$k]);
            $dictionary->offsetSet('K', $this->getDocument()->createNewObject($k));
        }

        if ($beforeIndex !== null) {
            /** @noinspection PhpParamsInspection */
            $k->insertBefore($object, $beforeIndex);
        } else {
            $k[] = $object;
        }
    }
}
