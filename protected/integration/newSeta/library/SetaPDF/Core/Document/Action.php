<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Action.php 1737 2022-06-02 13:53:43Z jan.slabon $
 */

/**
 * Class representing a PDF action
 *
 * See PDF 32000-1:2008 - 12.6
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Action
{
    /**
     * The action dictionary
     *
     * @var SetaPDF_Core_Type_Dictionary
     */
    protected $_actionDictionary;

    /**
     * @var SetaPDF_Core_Type_IndirectObjectInterface
     */
    protected $_indirectReference;

    /**
     * Creates an action object by an action dictionary.
     *
     * @param SetaPDF_Core_Type_IndirectObject|SetaPDF_Core_Type_IndirectReference|SetaPDF_Core_Type_Dictionary $objectOrDictionary
     * @return SetaPDF_Core_Document_Action
     * @throws SetaPDF_Core_Type_Exception
     * @throws InvalidArgumentException
     */
    public static function byObjectOrDictionary(SetaPDF_Core_Type_AbstractType $objectOrDictionary)
    {
        $dictionary = SetaPDF_Core_Type_Dictionary::ensureType($objectOrDictionary);

        $s = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'S');
        if (!$s instanceof SetaPDF_Core_Type_Name) {
            throw new InvalidArgumentException('An action dictionary needs at least an S entry.');
        }

        $sValue = $s->getValue();

        switch ($sValue) {
            case 'GoTo':
                return new SetaPDF_Core_Document_Action_GoTo($objectOrDictionary);

            case 'JavaScript':
                return new SetaPDF_Core_Document_Action_JavaScript($objectOrDictionary);

            case 'Named':
                return new SetaPDF_Core_Document_Action_Named($objectOrDictionary);

            case 'URI':
                return new SetaPDF_Core_Document_Action_Uri($objectOrDictionary);

            case 'Launch':
                return new SetaPDF_Core_Document_Action_Launch($objectOrDictionary);

            case 'SubmitForm':
                return new SetaPDF_Core_Document_Action_SubmitForm($objectOrDictionary);

            case 'ResetForm':
                return new SetaPDF_Core_Document_Action_ResetForm($objectOrDictionary);

            case 'ImportData':
                return new SetaPDF_Core_Document_Action_ImportData($objectOrDictionary);

            default:
                return new self($objectOrDictionary);
        }
    }

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Type_AbstractType $objectOrDictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    public function __construct(
        SetaPDF_Core_Type_AbstractType $objectOrDictionary
    )
    {
        if ($objectOrDictionary instanceof SetaPDF_Core_Type_IndirectObjectInterface) {
            $this->_indirectReference = $objectOrDictionary;
        }

        $objectOrDictionary = SetaPDF_Core_Type_Dictionary::ensureType($objectOrDictionary);

        if (!$objectOrDictionary->offsetExists('S')) {
            throw new InvalidArgumentException('An action dictionary needs at least an S entry.');
        }

        $this->_actionDictionary = $objectOrDictionary;
    }

    /**
     * Set the indirect object of this annotation.
     *
     * @param SetaPDF_Core_Type_IndirectObjectInterface $indirectReference
     */
    public function setIndirectObject(SetaPDF_Core_Type_IndirectObjectInterface $indirectReference)
    {
        $this->_indirectReference = $indirectReference;
    }

    /**
     * Get the indirect object of this annotation or creates it in the specific document context.
     *
     * @param SetaPDF_Core_Document $document The document instance
     * @return SetaPDF_Core_Type_IndirectObjectInterface
     */
    public function getIndirectObject(SetaPDF_Core_Document $document = null)
    {
        if ($document !== null && $this->_indirectReference === null) {
            $this->_indirectReference = $document->createNewObject($this->getPdfValue());
        }

        return $this->_indirectReference;
    }

    /**
     * Gets the PDF value of the next entry.
     *
     * @return false|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_Array|SetaPDF_Core_Type_IndirectReference
     */
    public function getNext()
    {
        $dictionary = $this->getActionDictionary();
        $next = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Next');
        if (!$next instanceof SetaPDF_Core_Type_Dictionary && !$next instanceof SetaPDF_Core_Type_Array) {
            return false;
        }

        $value = $dictionary->getValue('Next');
        if (
            $value instanceof SetaPDF_Core_Type_Dictionary ||
            $value instanceof SetaPDF_Core_Type_Array ||
            $value instanceof SetaPDF_Core_Type_IndirectReference
        ) {
            return $value;
        }

        return false;
    }

    /**
     * Set the next action which should be executed after this one.
     *
     * @param SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Document_Action|SetaPDF_Core_Type_IndirectObjectInterface $next
     */
    public function setNext($next)
    {
        if ($next instanceof self) {
            $next = $next->getIndirectObject() ?: $next->getActionDictionary();
        }

        $this->getActionDictionary()->offsetSet('Next', $next);
    }

    /**
     * Add an additional action to the next value of this action.
     *
     * @param SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Document_Action $next
     * @throws SetaPDF_Core_Type_Exception
     */
    public function addNext($next)
    {
        if (!$next instanceof self) {
            $next = self::byObjectOrDictionary($next);
        }

        $currentNext = $this->getNext();
        if (!SetaPDF_Core_Type_AbstractType::is(SetaPDF_Core_Type_Array::class, $currentNext)) {
            $currentNext = new SetaPDF_Core_Type_Array([clone $currentNext]);
            $this->_actionDictionary->offsetSet('Next', $currentNext);
        }

        if ($currentNext === false) {
            $this->setNext($next);
            return;
        }

        $currentNext->push($next->getIndirectObject() ?: $next->getActionDictionary());
    }

    /**
     * Get the action dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getActionDictionary()
    {
        return $this->_actionDictionary;
    }

    /**
     * Get the PDF value of this action.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getPdfValue()
    {
        return $this->getActionDictionary();
    }

    /**
     * Get the action type specified in the S key.
     *
     * @return string
     */
    public function getType()
    {
        return SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getActionDictionary(), 'S', '', true);
    }
}
