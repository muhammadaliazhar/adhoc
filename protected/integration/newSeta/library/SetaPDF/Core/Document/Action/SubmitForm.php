<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: SubmitForm.php 1753 2022-06-28 14:42:54Z maximilian.kresse $
 */

/**
 * Class representing a submit-form action
 *
 * Send data to a uniform resource locator.
 * See PDF 32000-1:2008 - 12.7.5.2 Submit-Form Action
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Action_SubmitForm extends SetaPDF_Core_Document_Action_Form
{
    /**
     * Action flag
     */
    const FLAG_EXCLUDE = 0x01; // 1

    /**
     * Action flag
     */
    const FLAG_INCLUDE_NO_VALUE_FIELDS = 0x02; // 2

    /**
     * Action flag
     */
    const FLAG_EXPORT_FORMAT = 0x04; // 3

    /**
     * Action flag
     */
    const FLAG_GET_METHOD = 0x08; // 4

    /**
     * Action flag
     */
    const FLAG_SUBMIT_COORDINATES = 0x10; // 5

    /**
     * Action flag
     */
    const FLAG_XFDF = 0x20; // 6

    /**
     * Action flag
     */
    const FLAG_INCLUDE_APPEND_SAVES = 0x40; // 7

    /**
     * Action flag
     */
    const FLAG_INCLUDE_ANNOTATIONS = 0x80; // 8

    /**
     * Action flag
     */
    const FLAG_SUBMIT_PDF = 0x100; // 9

    /**
     * Action flag
     */
    const FLAG_CANONICAL_FORMAT = 0x400; // 10

    /**
     * Action flag
     */
    const FLAG_EXCL_NON_USER_ANNOTS = 0x800; // 11

    /**
     * Action flag
     */
    const FLAG_EXCL_FKEY = 0x1000; // 12

    /**
     * Action flag
     */
    const FLAG_EMBED_FORM = 0x4000; // 14

    /**
     * Create a SubmitForm Action dictionary.
     *
     * @param string|SetaPDF_Core_FileSpecification|SetaPDF_Core_Type_IndirectObjectInterface|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_StringValue $fileSpecification
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public static function createActionDictionary($fileSpecification)
    {
        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('S', new SetaPDF_Core_Type_Name('SubmitForm', true));

        if (!$fileSpecification instanceof SetaPDF_Core_FileSpecification) {
            $fileSpecification = new SetaPDF_Core_FileSpecification($fileSpecification);
        }

        $dictionary->offsetSet('F', $fileSpecification->getDictionary());

        return $dictionary;
    }

    /**
     * The constructor.
     *
     * @param string|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface $objectOrDictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct($objectOrDictionary)
    {
        $dictionary = $objectOrDictionary instanceof SetaPDF_Core_Type_AbstractType
            ? $objectOrDictionary->ensure(true)
            : $objectOrDictionary;

        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            $dictionary = $objectOrDictionary = self::createActionDictionary($dictionary);
        }

        $s = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'S');
        if ($s === null || $s->getValue() !== 'SubmitForm') {
            throw new InvalidArgumentException('The S entry in a submit-form action shall be "SubmitForm".');
        }

        if (!$dictionary->offsetExists('F')) {
            throw new InvalidArgumentException('Missing or incorrect type of F entry in submit-form action dictionary.');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Get the file specification object.
     *
     * @return SetaPDF_Core_FileSpecification
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getFileSpecification()
    {
        $f = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'F');
        if ($f === null) {
            return null;
        }

        return new SetaPDF_Core_FileSpecification($f);
    }

    /**
     * Seta a file specification object.
     *
     * @param string|SetaPDF_Core_FileSpecification $fileSpecification
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setFileSpecification($fileSpecification)
    {
        if (!$fileSpecification instanceof SetaPDF_Core_FileSpecification) {
            $fileSpecification = new SetaPDF_Core_FileSpecification($fileSpecification);
        }

        $this->_actionDictionary->offsetSet('F', $fileSpecification->getDictionary());
    }

    /**
     * Set the char set in which the data should be transfered (PDF 2.0)
     *
     * Possible values include: utf-8, utf-16, Shift-JIS, BigFive, GBK, or UHC.
     *
     * @param string|SetaPDF_Core_Type_StringValue|false $charSet
     */
    public function setCharSet($charSet)
    {
        if (!$charSet) {
            $this->_actionDictionary->offsetUnset('CharSet');
            return;
        }

        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'CharSet');
        if (!$value instanceof SetaPDF_Core_Type_StringValue) {
            $this->_actionDictionary->offsetSet('CharSet', new SetaPDF_Core_Type_String($charSet));
        } else {
            $value->setValue($charSet);
        }
    }

    /**
     * Get the char set in which the data should be transfered (PDF 2.0)
     *
     * @return mixed|null
     */
    public function getCharSet()
    {
        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'CharSet');
        if ($value === null) {
            return null;
        }

        return $value->getValue();
    }
}
