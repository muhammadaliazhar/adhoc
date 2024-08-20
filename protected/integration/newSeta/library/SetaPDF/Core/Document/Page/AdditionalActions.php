<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: AdditionalActions.php 1717 2022-05-12 14:26:58Z jan.slabon $
 */

/**
 * Class representing a pages additional-actions dictionary
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_AdditionalActions
{
    /**
     * The catalog instance
     *
     * @var SetaPDF_Core_Document_Page
     */
    protected $_page;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document_Page $page
     */
    public function __construct(SetaPDF_Core_Document_Page $page)
    {
        $this->_page = $page;
    }

    /**
     * Release memory/cycled references.
     */
    public function cleanUp()
    {
        $this->_page = null;
    }

    /**
     * Get the additional actions dictionary.
     *
     * @param bool $create Pass true to automatically create the dictionary
     * @return null|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary($create = false)
    {
        $pageDictionary = $this->_page->getPageObject(true)->ensure();
        if (!$pageDictionary instanceof SetaPDF_Core_Type_Dictionary) {
            return null;
        }

        $aa = SetaPDF_Core_Type_Dictionary_Helper::getValue($pageDictionary, 'AA');
        if ($aa === null) {
            if ($create === false) {
                return null;
            }

            $aa = new SetaPDF_Core_Type_Dictionary();
            $pageDictionary->offsetSet('AA', $aa);
        }

        return $aa;
    }

    /**
     * Get the action that shall be performed when the page is opened.
     *
     * @return null|SetaPDF_Core_Document_Action
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getOpen()
    {
        return $this->_getAction('O');
    }

    /**
     * Set the action that shall be performed when the page is opened.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_AdditionalActions Returns the {@link SetaPDF_Core_Document_Page_AdditionalActions}
     *                                                      object for method chaining.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setOpen(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('O', $action);

        return $this;
    }

    /**
     * Get the action that shall be performed when the page is closed.
     *
     * @return null|SetaPDF_Core_Document_Action
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getClose()
    {
        return $this->_getAction('C');
    }

    /**
     * Set the action that shall be performed when the page is closed.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_AdditionalActions Returns the {@link SetaPDF_Core_Document_Page_AdditionalActions}
     *                                                      object for method chaining.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setClose(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('C', $action);

        return $this;
    }

    /**
     * Get the action.
     *
     * @param string $name
     * @return null|SetaPDF_Core_Document_Action
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _getAction($name)
    {
        $dictionary = $this->getDictionary();
        if (!$dictionary instanceof SetaPDF_Core_Type_Dictionary) {
            return null;
        }

        $action = $dictionary->getValue($name);
        if ($action === null) {
            return null;
        }

        return SetaPDF_Core_Document_Action::byObjectOrDictionary($action);
    }

    /**
     * Set the action.
     *
     * @param string $name
     * @param SetaPDF_Core_Document_Action $action
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _setAction($name, SetaPDF_Core_Document_Action $action = null)
    {
        if ($action !== null) {
            $dictionary = SetaPDF_Core_Type_Dictionary::ensureType($this->getDictionary(true));
            $dictionary->offsetSet($name, $action->getIndirectObject() ?: $action->getPdfValue());
        } else {
            $action = $this->_getAction($name, false);
            if ($action === null) {
                return;
            }

            $dictionary = SetaPDF_Core_Type_Dictionary::ensureType($this->getDictionary());
            $dictionary->offsetUnset($name);
        }
    }
}
