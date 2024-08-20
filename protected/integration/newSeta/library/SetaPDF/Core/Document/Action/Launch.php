<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Launch.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Class representing a Launch action
 *
 * Launch an application, usually to open a file.
 * See PDF 32000-1:2008 - 12.6.4.5
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Action_Launch extends SetaPDF_Core_Document_Action
{
    /**
     * Create a Launch Action dictionary.
     *
     * @param string|SetaPDF_Core_FileSpecification|SetaPDF_Core_Type_Dictionary $fileSpecification
     * @param null|boolean $newWindow
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createActionDictionary($fileSpecification, $newWindow = null)
    {
        if ($fileSpecification instanceof SetaPDF_Core_FileSpecification) {
            $fileSpecification = $fileSpecification->getDictionary();
        }

        if (!$fileSpecification instanceof SetaPDF_Core_Type_Dictionary) {
            $fileSpecification = SetaPDF_Core_FileSpecification::createDictionary($fileSpecification);
        }

        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('S', new SetaPDF_Core_Type_Name('Launch', true));
        $dictionary->offsetSet('F', $fileSpecification);

        if ($newWindow !== null) {
            $dictionary->offsetSet('NewWindow', new SetaPDF_Core_Type_Boolean($newWindow));
        }

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
            $args = func_get_args();
            $dictionary = $objectOrDictionary = SetaPDF_Core_Type_Dictionary::ensureType(call_user_func_array(
                ['SetaPDF_Core_Document_Action_Launch', 'createActionDictionary'],
                $args
            ));
            unset($args);
        }

        $s = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'S');
        if ($s === null || $s->getValue() !== 'Launch') {
            throw new InvalidArgumentException('The S entry in a launch action shall be "Launch".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Get the file specification.
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
     * Set the file specification.
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
     * Get the NewWindow flag specifying whether to open the destination document in a new window.
     *
     * @return null|boolean
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getNewWindow()
    {
        $newWindow = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'NewWindow');
        if ($newWindow === null) {
            return null;
        }

        return SetaPDF_Core_Type_Boolean::ensureType($newWindow)->getValue();
    }

    /**
     * Set the NewWindow flag specifying whether to open the destination document in a new window.
     *
     * @param boolean $newWindow
     */
    public function setNewWindow($newWindow)
    {
        if ($newWindow === null) {
            $this->_actionDictionary->offsetUnset('NewWindow');
            return;
        }

        $this->_actionDictionary->offsetSet('NewWindow', new SetaPDF_Core_Type_Boolean($newWindow));
    }
}
