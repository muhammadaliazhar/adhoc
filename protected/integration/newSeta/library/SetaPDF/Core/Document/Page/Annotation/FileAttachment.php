<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: FileAttachment.php 1789 2022-11-09 11:19:00Z jan.slabon $
 */

/**
 * Class representing a file attachment annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.15
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_FileAttachment
    extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.15 File attachment annotations
     *
     * @var string
     */
    const ICON_GRAPH = 'Graph';

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.15 File attachment annotations
     *
     * @var string
     */
    const ICON_PUSH_PIN = 'PushPin';

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.15 File attachment annotations
     *
     * @var string
     */
    const ICON_PAPERCLIP = 'Paperclip';

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.15 File attachment annotations
     *
     * @var string
     */
    const ICON_TAG = 'Tag';

    /**
     * Ensures a valid file specification parameter.
     *
     * @param SetaPDF_Core_Type_Dictionary $dict
     * @param SetaPDF_Core_FileSpecification|SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary $fileSpecification
     * @throws SetaPDF_Core_Type_Exception
     */
    static private function _setFileSpecification(SetaPDF_Core_Type_Dictionary $dict, $fileSpecification)
    {
        if ($fileSpecification instanceof SetaPDF_Core_FileSpecification) {
            $fileSpecification = $fileSpecification->getDictionary();
        } elseif ($fileSpecification instanceof SetaPDF_Core_Type_IndirectObject) {
            $fileSpecification = new SetaPDF_Core_Type_IndirectReference($fileSpecification);
        }

        if (!$fileSpecification instanceof SetaPDF_Core_Type_AbstractType) {
            throw new InvalidArgumentException('File specification parameter cannot be resolved as a dictionary.');
        }

        try {
            $fileSpecificationDict = SetaPDF_Core_Type_Dictionary::ensureType($fileSpecification);
        } catch (SetaPDF_Core_Type_Exception $e) {
            throw new InvalidArgumentException(
                'File specification parameter cannot be resolved as a dictionary.', 0, $e
            );
        }

        if (!$fileSpecificationDict->offsetExists('F')) {
            throw new InvalidArgumentException('File specification dictionary needs to have a F entry.');
        }

        $dict->offsetSet('FS', $fileSpecification);
    }

    /**
     * Creates an annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @param SetaPDF_Core_FileSpecification|SetaPDF_Core_Type_IndirectObject|SetaPDF_Core_Type_Dictionary $fileSpecification
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public static function createAnnotationDictionary($rect, $fileSpecification)
    {
        $dict = parent::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_FILE_ATTACHMENT);
        self::_setFileSpecification($dict, $fileSpecification);

        return $dict;
    }

    /**
     * The constructor.
     *
     * @param array|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface $objectOrDictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct($objectOrDictionary)
    {
        $dictionary = $objectOrDictionary instanceof SetaPDF_Core_Type_AbstractType
            ? $objectOrDictionary->ensure(true)
            : $objectOrDictionary;

        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            $args = func_get_args();
            $objectOrDictionary = $dictionary = SetaPDF_Core_Type_Dictionary::ensureType(call_user_func_array(
                [self::class, 'createAnnotationDictionary'],
                $args
            ));
            unset($args);
        }

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'FileAttachment')) {
            throw new InvalidArgumentException('The Subtype entry in a file attachment annotation shall be "FileAttachment".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Set the file specification.
     *
     * @param SetaPDF_Core_FileSpecification|SetaPDF_Core_Type_IndirectObject|SetaPDF_Core_Type_Dictionary $fileSpecification
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setFileSpecification($fileSpecification)
    {
        self::_setFileSpecification($this->getDictionary(), $fileSpecification);
    }

    /**
     * Get the file specification.
     *
     * @return SetaPDF_Core_FileSpecification
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getFileSpecification()
    {
        $fs = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'FS');
        return new SetaPDF_Core_FileSpecification($fs);
    }

    /**
     * Get the icon name of the annotation.
     *
     * @return string
     */
    public function getIconName()
    {
        $name = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Name');
        if (!$name instanceof SetaPDF_Core_Type_Name) {
            return self::ICON_PUSH_PIN;
        }

        return $name->getValue();
    }

    /**
     * Set the name of the icon that shall be used in displaying the annotation.
     *
     * @param null|string $iconName
     */
    public function setIconName($iconName)
    {
        $dict = $this->getDictionary();
        if ($iconName === null) {
            $dict->offsetUnset('Name');
            return;
        }

        $nameObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Name');
        if ($nameObject instanceof SetaPDF_Core_Type_Name) {
            $nameObject->setValue($iconName);
            return;
        }

        $dict->offsetSet('Name', new SetaPDF_Core_Type_Name($iconName));
    }
}
