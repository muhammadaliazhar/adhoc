<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: ResetForm.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Class representing a reset-form action
 *
 * Set fields to their default values.
 * See PDF 32000-1:2008 - 12.7.5.3 Reset-Form Action
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Action_ResetForm extends SetaPDF_Core_Document_Action_Form
{
    /**
     * Action flag
     */
    const FLAG_EXCLUDE = 0x01; // 1

    /**
     * Create a Named Action dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createActionDictionary()
    {
        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('S', new SetaPDF_Core_Type_Name('ResetForm', true));

        return $dictionary;
    }

    /**
     * The constructor.
     *
     * @param string|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface $objectOrDictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct($objectOrDictionary = null)
    {
        $dictionary = $objectOrDictionary instanceof SetaPDF_Core_Type_AbstractType
            ? $objectOrDictionary->ensure(true)
            : $objectOrDictionary;

        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            $dictionary = $objectOrDictionary = self::createActionDictionary();
        }

        $s = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'S');
        if ($s === null || $s->getValue() !== 'ResetForm') {
            throw new InvalidArgumentException('The S entry in a reset-form action shall be "ResetForm".');
        }

        parent::__construct($objectOrDictionary);
    }
}
