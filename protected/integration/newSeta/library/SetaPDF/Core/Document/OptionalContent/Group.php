<?php 
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Group.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * An optional content group
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_OptionalContent_Group
    implements SetaPDF_Core_Resource
{
    /**
     * The optional content group dictionary
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_dictionary;
    
    /**
     * The indirect object of this group
     * 
     * @var SetaPDF_Core_Type_IndirectObjectInterface
     */
    protected $_indirectObject;
    
    /**
     * A usage helper class
     * 
     * @var SetaPDF_Core_Document_OptionalContent_Group_Usage
     */
    protected $_usage;
    
    /**
     * Creates an optional content group dictionary.
     * 
     * @param string $name
     * @param string $encoding
     * @return SetaPDF_Core_Type_Dictionary
     */
    public static function createOCGDictionary($name, $encoding = 'UTF-8')
    {
        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('Type', new SetaPDF_Core_Type_Name('OCG', true));
        $dictionary->offsetSet('Name', new SetaPDF_Core_Type_String(
            SetaPDF_Core_Encoding::toPdfString($name, $encoding))
        );
        
        return $dictionary;
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary|string $ocgDictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     * @see createOCGDictionary()
     */
    public function __construct($ocgDictionary)
    {
        if (is_scalar($ocgDictionary)) {
            $args = func_get_args();
            $ocgDictionary = SetaPDF_Core_Type_Dictionary::ensureType(
                call_user_func_array(
                    ['SetaPDF_Core_Document_OptionalContent_Group', 'createOCGDictionary'],
                    $args
                )
            );
            unset($args);
        }
        
        if ($ocgDictionary instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            $this->_indirectObject = $ocgDictionary;
            $ocgDictionary = $ocgDictionary->ensure();
        }

        if (!$ocgDictionary instanceof SetaPDF_Core_Type_Dictionary) {
            throw new InvalidArgumentException(
                'Parameter has to be type of SetaPDF_Core_Type_Dictionary'
            );
        }

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($ocgDictionary, 'Type', 'OCG')) {
            throw new InvalidArgumentException('Invalid or missing type value for optional content group.');
        }
        
        $this->_dictionary = $ocgDictionary;
    }

    /**
     * Release memory / cycled references.
     */
    public function cleanUp()
    {
        if ($this->_usage !== null) {
            $this->_usage->cleanUp();
            $this->_usage = null;
        }
    }
    
    /**
     * Gets the usage helper class.
     *
     * @return SetaPDF_Core_Document_OptionalContent_Group_Usage
     */
    public function usage()
    {
        if ($this->_usage === null) {
            $this->_usage = new SetaPDF_Core_Document_OptionalContent_Group_Usage($this);
        }
    
        return $this->_usage;
    }

    /**
     * Get the name of the optional content group.
     *
     * @param string $encoding
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getName($encoding = 'UTF-8')
    {
        $name = SetaPDF_Core_Type_AbstractType::ensureWithType(
            SetaPDF_Core_Type_StringValue::class,
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Name')
        );

        return SetaPDF_Core_Encoding::convertPdfString($name->getValue(), $encoding);
    }

    /**
     * Set the name of the optional content group.
     *
     * @param string $name
     * @param string $encoding
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setName($name, $encoding = 'UTF-8')
    {
        $nameObject = SetaPDF_Core_Type_AbstractType::ensureWithType(
            SetaPDF_Core_Type_StringValue::class,
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Name')
        );

        $nameObject->setValue(SetaPDF_Core_Encoding::toPdfString($name, $encoding));
    }
    
    /**
     * Get the dictionary of the optional content group.
     * 
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary()
    {
        return $this->_dictionary;
    }
    
    /**
     * Get an indirect object for this optional content group.
     *
     * @see SetaPDF_Core_Resource::getIndirectObject()
     * @param SetaPDF_Core_Document $document
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     * @throws InvalidArgumentException
     */
    public function getIndirectObject(SetaPDF_Core_Document $document = null)
    {
        if ($this->_indirectObject === null) {
            if ($document === null) {
                throw new InvalidArgumentException('To initialize a new object $document parameter is not optional!');
            }
            
            $this->_indirectObject = $document->createNewObject($this->getDictionary());
        }
        
        return $this->_indirectObject;
    }
    
    /**
     * Get the resource type for optional content groups.
     *
     * @see SetaPDF_Core_Resource::getResourceType()
     * @return string
     */
    public function getResourceType()
    {
        return SetaPDF_Core_Resource::TYPE_PROPERTIES;
    }
}
