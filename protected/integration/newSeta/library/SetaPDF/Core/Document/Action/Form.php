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
 * Abstract class for form actions
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Core_Document_Action_Form extends SetaPDF_Core_Document_Action
{
    /**
     * Set which fields to reset or which to exclude from resetting, depending on the setting of the Include/Exclude flag.
     *
     * @see setFlags()
     * @param array $fields An array of fully qualified names or an indirect object to a field dictionary
     * @param string $encoding The input encoding
     */
    public function setFields(array $fields = null, $encoding = 'UTF-8')
    {
        if ($fields === null) {
            $this->_actionDictionary->offsetUnset('Fields');
            return;
        }

        $array = new SetaPDF_Core_Type_Array();
        foreach ($fields AS $fieldname) {
            if ($fieldname instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
                $array[] = $fieldname;
            } else {
                $array[] = new SetaPDF_Core_Type_String(SetaPDF_Core_Encoding::toPdfString($fieldname, $encoding));
            }
        }

        $this->_actionDictionary->offsetSet('Fields', $array);
    }

    /**
     * Get the fields to include or exclude in the submission.
     *
     * @param string $encoding The output encoding
     * @return array|null An array of field names in the specific encoding
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getFields($encoding = 'UTF-8')
    {
        $array = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'Fields');
        if ($array === null) {
            return null;
        }

        $fieldnames = [];
        foreach (SetaPDF_Core_Type_Array::ensureType($array) AS $field) {
            if ($field instanceof SetaPDF_Core_Type_StringValue) {
                $fieldnames[] = SetaPDF_Core_Encoding::convertPdfString($field->getValue(), $encoding);
            } elseif ($field instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
                $field = SetaPDF_Core_Type_Dictionary::ensureType($field->ensure());
                $fieldname = SetaPDF_Core_Document_Catalog_AcroForm::resolveFieldName($field);
                $fieldnames[] = SetaPDF_Core_Encoding::convert($fieldname, 'UTF-8', $encoding);
            }
        }

        return $fieldnames;
    }

    /**
     * Sets a flag or flags.
     *
     * @param integer $flags
     * @param boolean|null $add Add = true, remove = false, set = null
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setFlags($flags, $add = true)
    {
        if ($add === false) {
            $this->unsetFlags($flags);
            return;
        }

        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'Flags');
        if ($value === null) {
            $value = new SetaPDF_Core_Type_Numeric($flags);
            $this->_actionDictionary->offsetSet('Flags', $value);
        }

        $value = SetaPDF_Core_Type_Numeric::ensureType($value);
        if ($add === true) {
            $value->setValue($value->getValue() | $flags);
        } else {
            $value->setValue($flags);
        }
    }

    /**
     * Removes a flag or flags.
     *
     * @param integer $flags
     * @throws SetaPDF_Core_Type_Exception
     */
    public function unsetFlags($flags)
    {
        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'Flags');
        if ($value === null) {
            return;
        }

        $value = SetaPDF_Core_Type_Numeric::ensureType($value);
        $value->setValue($value->getValue() & ~$flags);
    }

    /**
     * Returns the current flags.
     *
     * @return integer
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getFlags()
    {
        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'Flags');
        if ($value === null) {
            return 0;
        }

        return SetaPDF_Core_Type_Numeric::ensureType($value)->getValue();
    }

    /**
     * Checks if a specific flag is set.
     *
     * @param integer $flag
     * @return boolean
     * @throws SetaPDF_Core_Type_Exception
     */
    public function isFlagSet($flag)
    {
        return ($this->getFlags() & $flag) === $flag;
    }
}
