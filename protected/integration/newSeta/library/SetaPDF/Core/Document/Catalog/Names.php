<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Names.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Class for handling Names in a PDF document
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_Names
{
    /**
     * Name/Category key
     *
     * @var string
     */
    const DESTS = 'Dests';

    /**
     * Name/Category key
     *
     * @var string
     */
    const AP = 'AP';

    /**
     * Name/Category key
     *
     * @var string
     */
    const JAVA_SCRIPT = 'JavaScript';

    /**
     * Name/Category key
     *
     * @var string
     */
    const PAGES = 'Pages';

    /**
     * Name/Category key
     *
     * @var string
     */
    const TEMPLATES = 'Templates';

    /**
     * Name/Category key
     *
     * @var string
     */
    const IDS = 'IDS';

    /**
     * Name/Category key
     *
     * @var string
     */
    const URLS = 'URLS';

    /**
     * Name/Category key
     *
     * @var string
     */
    const EMBEDDED_FILES = 'EmbeddedFiles';

    /**
     * Name/Category key
     *
     * @var string
     */
    const ALTERNATE_PRESENTATIONS = 'AlternatePresentations';

    /**
     * Name/Category key
     *
     * @var string
     */
    const RENDITIONS = 'Renditions';

    /**
     * Name/Category key
     *
     * @var string
     */
    const XFA_RESOURCES = 'XFAResources';

    /**
     * The catalog instance
     *
     * @var SetaPDF_Core_Document_Catalog
     */
    protected $_catalog;

    /**
     * The Names dictionary
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_namesDictionary;

    /**
     * @var array
     */
    protected $_nameTrees = [];

    /**
     * @var SetaPDF_Core_Document_Catalog_Names_EmbeddedFiles
     */
    protected $_embeddedFiles;

    /**
     * Returns all available category keys of possible name trees.
     *
     * @return array
     */
    public static function getAvailableCategoryKeys()
    {
        return [
            self::DESTS, self::AP, self::JAVA_SCRIPT, self::PAGES,
            self::TEMPLATES, self::IDS, self::URLS, self::EMBEDDED_FILES,
            self::ALTERNATE_PRESENTATIONS, self::RENDITIONS, self::XFA_RESOURCES
        ];
    }

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
     * Get a name tree by its name.
     *
     * @param string $name
     * @param boolean $create
     * @return SetaPDF_Core_DataStructure_NameTree|null
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getTree($name, $create = false)
    {
        if (isset($this->_nameTrees[$name])) {
            return $this->_nameTrees[$name];
        }

        $names = $this->getNamesDictionary($create);
        if ($names === null) {
            return null;
        }

        $treeRootDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($names, $name);

        if ($treeRootDict === null) {
            if ($create === false) {
                return null;
            }

            SetaPDF_Core_SecHandler::checkPermission($this->getDocument(), SetaPDF_Core_SecHandler::PERM_MODIFY);

            $treeRootDict = new SetaPDF_Core_Type_Dictionary();
            $names->offsetSet($name, $this->getDocument()->createNewObject($treeRootDict));
        }

        $this->_nameTrees[$name] = new SetaPDF_Core_DataStructure_NameTree($treeRootDict, $this->getDocument());

        return $this->_nameTrees[$name];
    }

    /**
     * Get all available name trees.
     *
     * @return array Array of SetaPDF_Core_DataStructure_NameTree objects
     * @throws SetaPDF_Core_SecHandler_Exception
     * @see getAvailableCategoryKeys()
     */
    public function getTrees()
    {
        foreach (self::getAvailableCategoryKeys() AS $key) {
            $this->getTree($key);
        }

        return $this->_nameTrees;
    }

    /**
     * Returns the Names dictionary in the document's catalog.
     *
     * @param boolean $create
     * @return null|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getNamesDictionary($create = false)
    {
        if ($this->_namesDictionary === null) {
            $catalog = $this->getDocument()->getCatalog()->getDictionary($create);
            if ($catalog === null) {
                return null;
            }

            $namesDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'Names');
            if ($namesDict === null) {
                if ($create === false) {
                    return null;
                }

                $namesDict = new SetaPDF_Core_Type_Dictionary();
                $catalog->offsetSet('Names', $this->getDocument()->createNewObject($namesDict));
            }

            $this->_namesDictionary = $namesDict;
        }

        return $this->_namesDictionary;
    }

    /**
     * Release objects to free memory and cycled references.
     *
     * After calling this method the instance of this object is unusable!
     *
     * @return void
     */
    public function cleanUp()
    {
        foreach ($this->_nameTrees AS $nameTree) {
            $nameTree->cleanUp();
        }
        $this->_nameTrees = [];

        if ($this->_embeddedFiles !== null) {
            $this->_embeddedFiles->cleanUp();
        }

        $this->_catalog = null;
    }

    /**
     * Get the embedded files helper.
     *
     * @return SetaPDF_Core_Document_Catalog_Names_EmbeddedFiles
     */
    public function getEmbeddedFiles()
    {
        if ($this->_embeddedFiles === null) {
            $this->_embeddedFiles = new SetaPDF_Core_Document_Catalog_Names_EmbeddedFiles($this);
        }

        return $this->_embeddedFiles;
    }
}
