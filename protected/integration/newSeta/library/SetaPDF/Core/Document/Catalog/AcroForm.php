<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: AcroForm.php 1733 2022-06-02 07:39:42Z jan.slabon $
 */

/**
 * Class representing a basic AcroForm
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_AcroForm
{
    /**
     * The documents catalog instance
     *
     * @var SetaPDF_Core_Document
     */
    protected $_catalog;

    /**
     * The AcroForm dictionary
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;

    /**
     * Resolves the qualified name of a form field.
     *
     * @param SetaPDF_Core_Type_Dictionary $terminalFieldDictionary The terminal field of the form field
     * @param boolean $asArray
     * @return string|array In UTF-8 encoding
     * @throws SetaPDF_Core_Type_Exception
     */
    public static function resolveFieldName(SetaPDF_Core_Type_Dictionary $terminalFieldDictionary, $asArray = false)
    {
        $names = [];

        $p = $terminalFieldDictionary;
        while ($p instanceof SetaPDF_Core_Type_Dictionary) {
            $t = SetaPDF_Core_Type_Dictionary_Helper::getValue($p, 'T');
            if ($t) {
                /** @var SetaPDF_Core_Type_StringValue $t */
                $t = SetaPDF_Core_Type_AbstractType::ensureWithType(
                    SetaPDF_Core_Type_StringValue::class,
                    $t
                );
                $names[] = $t->getValue();
            }

            $p = SetaPDF_Core_Type_Dictionary_Helper::getValue($p, 'Parent', false);
        }

        $names = array_map(['SetaPDF_Core_Encoding', 'convertPdfString'], $names);

        if ($asArray === false) {
            return implode('.', array_reverse($names));
        }

        return array_reverse($names);
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
     * Release cycled references.
     */
    public function cleanUp()
    {
        $this->_dictionary = null;
        $this->_catalog = null;
    }

    /**
     * Get and creates the AcroForm dictionary.
     *
     * @param boolean $create
     * @return boolean|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getDictionary($create = false)
    {
        $root = $this->getDocument()->getCatalog()->getDictionary($create);
        if ($root) {
            $acroFormDict = SetaPDF_Core_Type_Dictionary_Helper::getValue($root, 'AcroForm');
            if ($acroFormDict instanceof SetaPDF_Core_Type_Dictionary) {
                $this->_dictionary = $acroFormDict;
            } else {
                $root->offsetUnset('AcroForm');
            }

            if ($create === true && $this->_dictionary === null) {
                $this->_dictionary = new SetaPDF_Core_Type_Dictionary();
                $object = $this->getDocument()->createNewObject($this->_dictionary);
                $root->offsetSet('AcroForm', $object);
            }
        }

        return $this->_dictionary === null
            ? false
            : $this->_dictionary;
    }

    /**
     * Get and creates the fields array.
     *
     * @param boolean $create
     * @return SetaPDF_Core_Type_Array|false
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getFieldsArray($create = false)
    {
        $dictionary = $this->getDictionary($create);
        if ($dictionary === false) {
            return false;
        }

        $fields = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Fields');
        if (!$fields instanceof SetaPDF_Core_Type_Array) {
            $fields = new SetaPDF_Core_Type_Array();
            $dictionary->offsetSet('Fields', $fields);
        }

        return $fields;
    }

    /**
     * Get and creates the calculation order array.
     *
     * @param boolean $create
     * @return SetaPDF_Core_Type_Array|false
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getCalculationOrderArray($create = false)
    {
        $dictionary = $this->getDictionary($create);
        if ($dictionary === false) {
            return false;
        }

        $co = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'CO');
        if (!$co instanceof SetaPDF_Core_Type_Array) {
            $co = new SetaPDF_Core_Type_Array();
            $dictionary->offsetSet('CO', $co);
        }

        return $co;
    }

    /**
     * Checks if the NeedAppearances flag is set or not.
     *
     * @return boolean
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function isNeedAppearancesSet()
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === false) {
            return false;
        }

        $needAppearances = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'NeedAppearances');
        if ($needAppearances instanceof SetaPDF_Core_Type_Boolean) {
            return $needAppearances->getValue();
        }

        return false;
    }

    /**
     * Set the NeedAppearances flag.
     *
     * This flag indicates the viewer to rerender the form field appearances.
     *
     * @param boolean $needAppearances
     * @return void
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function setNeedAppearances($needAppearances = true)
    {
        $needAppearances = (boolean)$needAppearances;
        if ($this->isNeedAppearancesSet() === $needAppearances) {
            return;
        }

        $dictionary = $this->getDictionary($needAppearances);
        if ($needAppearances === false) {
            if ($dictionary === false) {
                return;
            }

            $dictionary->offsetUnset('NeedAppearances');
        } else {
            $dictionary->offsetSet('NeedAppearances', new SetaPDF_Core_Type_Boolean(true));
        }
    }

    /**
     * Add default values and resources to the AcroForm dictionary.
     *
     * This is needed to avoid undefined behavior in adobe reader.
     * If for example base fonts are missing, the file is digital signed and
     * include links, the signature panel will never be displayed.
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function addDefaultEntriesAndValues()
    {
        $dictionary = $this->getDictionary(true);

        if (SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Fields') === null) {
            $dictionary->offsetSet('Fields', new SetaPDF_Core_Type_Array());
        }

        if (SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'CO') === null) {
            $dictionary->offsetSet('CO', new SetaPDF_Core_Type_Array());
        }

        // Create default resources and default appearance commands
        $fontDictionary = $this->getDefaultResources(true, 'Font');
        if (SetaPDF_Core_Type_Dictionary_Helper::getValue($fontDictionary, 'Helv') === null) {
            $helvetica = $this->getDocument()->createNewObject(SetaPDF_Core_Font_Standard_Helvetica::getDefaultDictionary());
            $fontDictionary->offsetSet('Helv', $helvetica);
        }

        if (SetaPDF_Core_Type_Dictionary_Helper::getValue($fontDictionary, 'ZaDb') === null) {
            $zapfDingbats = $this->getDocument()->createNewObject(SetaPDF_Core_Font_Standard_ZapfDingbats::getDefaultDictionary());
            $fontDictionary->offsetSet('ZaDb', $zapfDingbats);
        }

        $da = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'DA');
        if ($da === null) {
            $dictionary->offsetSet('DA', new SetaPDF_Core_Type_String('/Helv 0 Tf 0 g '));
        }
    }

    /**
     * Get the default resources of the AcroForm.
     *
     * @param bool $create
     * @param null $entryKey
     * @return bool|SetaPDF_Core_Type_Dictionary
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function getDefaultResources($create = false, $entryKey = null)
    {
        $dictionary = $this->getDictionary($create);
        if ($dictionary === false) {
            return false;
        }

        $resources = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'DR');
        if (!$resources instanceof SetaPDF_Core_Type_Dictionary) {
            $resources = null;
        }

        if ($resources === null) {
            if ($create === false) {
                return false;
            }

            $resources = new SetaPDF_Core_Type_Dictionary();
            $dictionary->offsetSet('DR', $resources);
        }

        if ($entryKey === null) {
            return $resources;
        }

        $entry = SetaPDF_Core_Type_Dictionary_Helper::getValue($resources, $entryKey);
        if ($entry === null) {
            if ($create === false) {
                return false;
            }

            $entry = new SetaPDF_Core_Type_Dictionary();
            $resources->offsetSet($entryKey, $entry);
        }

        return $entry;
    }

    /**
     * Adds a resource.
     *
     * @param string|SetaPDF_Core_Resource $type
     * @param null|SetaPDF_Core_Resource|SetaPDF_Core_Type_IndirectObjectInterface $object
     * @return string
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function addResource($type, $object = null)
    {
        if ($type instanceof SetaPDF_Core_Resource) {
            $object = $type->getIndirectObject($this->getDocument());
            $type = $type->getResourceType();
        }

        if ($object instanceof SetaPDF_Core_Resource) {
            $object = $object->getIndirectObject($this->getDocument());
        }

        if (!($object instanceof SetaPDF_Core_Type_IndirectObjectInterface)) {
            throw new InvalidArgumentException('$object has to be an instance of SetaPDF_Core_Type_IndirectObjectInterface or SetaPDF_Core_Resource');
        }

        $resources = $this->getDefaultResources(true, $type);

        foreach ($resources AS $name => $resourceValue) {
            if ($resourceValue instanceof SetaPDF_Core_Type_IndirectObjectInterface &&
                $resourceValue->getObjectIdent() === $object->getObjectIdent()
            ) {
                return $name;
            }
        }

        switch ($type) {
            case SetaPDF_Core_Resource::TYPE_FONT:
                $prefix = 'F';
                break;
            case SetaPDF_Core_Resource::TYPE_X_OBJECT:
                $prefix = 'I';
                break;
            case SetaPDF_Core_Resource::TYPE_EXT_G_STATE:
                $prefix = 'GS';
                break;
            case SetaPDF_Core_Resource::TYPE_COLOR_SPACE:
                $prefix = 'CS';
                break;
            case SetaPDF_Core_Resource::TYPE_PATTERN:
                $prefix = 'P';
                break;
            case SetaPDF_Core_Resource::TYPE_SHADING:
                $prefix = 'SH';
                break;
            case SetaPDF_Core_Resource::TYPE_PROPERTIES:
                $prefix = 'PR';
                break;
            case SetaPDF_Core_Resource::TYPE_PROC_SET:
                throw new InvalidArgumentException('Invalid resource type (' . $type . ')');
            default:
                $prefix = 'X';
        }

        $i = 0;
        while ($resources->offsetExists(($name = $prefix . $i))) {
            $i++;
        }

        $resources->offsetSet($name, $object);

        return $name;
    }

    /**
     * Get the terminal fields objects of a document.
     *
     * @return SetaPDF_Core_Type_IndirectObjectInterface[]
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_IndirectReference_Exception
     */
    public function getTerminalFieldsObjects()
    {
        $objects = [];
        $this->_readTerminalFieldsObjects(null, $objects);

        return array_values($objects);
    }

    /**
     * Checks if a XFA key is present.
     *
     * @return boolean
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function isXfaForm()
    {
        $dictionary = $this->getDictionary();

        return $dictionary !== false && SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'XFA') !== null;
    }

    /**
     * Removes the XFA entry if present.
     *
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    public function removeXfaInformation()
    {
        $dictionary = $this->getDictionary();
        if (
            $dictionary !== false && $dictionary->offsetExists('XFA') &&
            SetaPDF_Core_SecHandler::checkPermission(
                $this->_catalog->getDocument(),
                SetaPDF_Core_SecHandler::PERM_MODIFY
        )) {
            $dictionary->offsetUnset('XFA');
            return true;
        }

        return false;
    }

    /**
     * Read all terminal fields objects.
     *
     * @param array|null $fields
     * @param SetaPDF_Core_Type_IndirectObjectInterface[] $objects
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_IndirectReference_Exception
     */
    private function _readTerminalFieldsObjects(array $fields = null, array &$objects = null)
    {
        if ($fields === null) {
            $acroForm = $this->getDictionary();

            if ($acroForm instanceof SetaPDF_Core_Type_Dictionary) {
                $fields = SetaPDF_Core_Type_Dictionary_Helper::getValue($acroForm, 'Fields');
                if ($fields === null) {
                    return;
                }

                $this->_readTerminalFieldsObjects($fields->getValue(), $objects);
            }

            return;
        }

        foreach ($fields AS $field) {
            try {
                $fieldsDict = $field->ensure(true);
            } catch (SetaPDF_Core_Type_IndirectReference_Exception $e) {
                continue;
            }

            if (!$fieldsDict instanceof SetaPDF_Core_Type_Dictionary) {
                continue;
            }

            $kids = SetaPDF_Core_Type_Dictionary_Helper::getValue($fieldsDict, 'Kids');
            if ($kids !== null) {
                if (!$kids instanceof SetaPDF_Core_Type_Array) {
                    continue;
                }

                $this->_readTerminalFieldsObjects($kids->getValue(), $objects);

            } else {
                // Check for validity
                if (
                    SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($fieldsDict, 'FT', false) === false
                ) {
                    continue;
                }

                // Some faulty documents uses one widget annotation several times in a Kids array.
                // So simply limit this to unique object ids:
                /** @var SetaPDF_Core_Type_IndirectObjectInterface $fieldObject */
                $fieldObject = $field->getValue();
                $objectKey = $fieldObject->getObjectIdent();
                if (isset($objects[$objectKey])) {
                    continue;
                }

                $objects[$objectKey] = $fieldObject;
            }
        }
    }
}