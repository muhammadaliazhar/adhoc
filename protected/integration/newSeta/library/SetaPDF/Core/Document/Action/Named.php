<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Named.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Class representing a Named action
 *
 * Execute an action predefined by the conforming reader.
 * See PDF 32000-1:2008 - 12.6.4.11
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Action_Named extends SetaPDF_Core_Document_Action
{
    /**
     * Name defined in PDF 32000-1:2008
     *
     * @var string
     */
    const NEXT_PAGE = 'NextPage';

    /**
     * Name defined in PDF 32000-1:2008
     *
     * @var string
     */
    const PREV_PAGE = 'PrevPage';

    /**
     * Name defined in PDF 32000-1:2008
     *
     * @var string
     */
    const FIRST_PAGE = 'FirstPage';

    /**
     * Name defined in PDF 32000-1:2008
     *
     * @var string
     */
    const LAST_PAGE = 'LastPage';

    /* Acrobat specific */

    /**
     * Additional names used by Adobe Acrobat: Print
     *
     * @var string
     */
    const PRINT_DOCUMENT = 'Print';

    /**
     * Additional names used by Adobe Acrobat
     *
     * @var string
     */
    const GO_TO_PAGE = 'GoToPage';

    /**
     * Additional names used by Adobe Acrobat: Previous View
     *
     * @var string
     */
    const GO_BACK = 'GoBack';

    /**
     * Create a named action dictionary.
     *
     * @param string $name
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createActionDictionary($name)
    {
        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('S', new SetaPDF_Core_Type_Name('Named', true));
        $dictionary->offsetSet('N', new SetaPDF_Core_Type_Name($name));

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
        if ($s === null || $s->getValue() !== 'Named') {
            throw new InvalidArgumentException('The S entry in a named action shall be "Named".');
        }

        $n = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'N');
        if (!($n instanceof SetaPDF_Core_Type_Name)) {
            throw new InvalidArgumentException('Missing or incorrect type of N entry in named action dictionary.');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Get the name.
     *
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getName()
    {
        return SetaPDF_Core_Type_Name::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'N')
        )->getValue();
    }

    /**
     * Set the name.
     *
     * @param string $name
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setName($name)
    {
        SetaPDF_Core_Type_Name::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'N')
        )->setValue($name);
    }
}
