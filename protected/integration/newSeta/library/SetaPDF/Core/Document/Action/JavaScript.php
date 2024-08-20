<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: JavaScript.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * Class representing a JavaScript action
 *
 * Execute a JavaScript script.
 * See PDF 32000-1:2008 - 12.6.4.16
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Action_JavaScript extends SetaPDF_Core_Document_Action
{
    /**
     * Create a JavaScript Action dictionary.
     *
     * @param string|SetaPDF_Core_Type_String|SetaPDF_Core_Type_HexString|SetaPDF_Core_Type_Stream $javaScript
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createActionDictionary($javaScript)
    {
        if (is_scalar($javaScript)) {
            $javaScript = new SetaPDF_Core_Type_String($javaScript);
        }

        if (!($javaScript instanceof SetaPDF_Core_Type_StringValue) &&
            !($javaScript instanceof SetaPDF_Core_Type_Stream)
        ) {
            throw new InvalidArgumentException(
                sprintf('Incorrect object type (%s) for JS entry in JavaScript action dictionary.', get_class($javaScript))
            );
        }

        $dictionary = new SetaPDF_Core_Type_Dictionary();
        $dictionary->offsetSet('S', new SetaPDF_Core_Type_Name('JavaScript', true));
        $dictionary->offsetSet('JS', $javaScript);

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
        if ($s === null || $s->getValue() !== 'JavaScript') {
            throw new InvalidArgumentException('The S entry in a JavaScript action shall be "JavaScript".');
        }

        $js = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'JS');
        if ($js === null) {
            throw new InvalidArgumentException('Missing JS entry in JavaScript action dictionary.');
        }

        if (!($js instanceof SetaPDF_Core_Type_StringValue) &&
            !($js instanceof SetaPDF_Core_Type_Stream)
        ) {
            throw new InvalidArgumentException(
                sprintf('Incorrect object type (%s) for JS entry in JavaScript action dictionary.', get_class($js))
            );
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Get the JavaScript code.
     *
     * @param string $encodig The expected encoding of the JavaScript code.
     * @return string
     */
    public function getJavaScript($encodig = 'UTF-8')
    {
        $js = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'JS');

        if ($js instanceof SetaPDF_Core_Type_Stream) {
            return SetaPDF_Core_Encoding::convertPdfString($js->getStream(), $encodig);
        }

        return SetaPDF_Core_Encoding::convertPdfString($js->getValue());
    }

    /**
     * Set the JavaScript code.
     *
     * @param string $javaScript JavaScript in either PDFDocEncoding or UTF-16BE including BOM.
     */
    public function setJavaScript($javaScript)
    {
        $js = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->_actionDictionary, 'JS');

        if ($js instanceof SetaPDF_Core_Type_Stream) {
            $js->setStream($javaScript);
            return;
        }

        $js->setValue($javaScript);
    }
}
