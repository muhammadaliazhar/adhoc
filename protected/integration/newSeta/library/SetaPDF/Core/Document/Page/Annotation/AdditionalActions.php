<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: AdditionalActions.php 1742 2022-06-20 14:29:40Z jan.slabon $
 */

/**
 * Class representing an annotations additional-actions dictionary
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_AdditionalActions
{
    /**
     * The annotation instance
     *
     * @var SetaPDF_Core_Document_Page_Annotation
     */
    protected $_annotation;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document_Page_Annotation $annotation
     */
    public function __construct(SetaPDF_Core_Document_Page_Annotation $annotation)
    {
        $this->_annotation = $annotation;
    }

    /**
     * Release memory/cycled references.
     */
    public function cleanUp()
    {
        $this->_annotation = null;
    }

    /**
     * Get the additional actions dictionary.
     *
     * @param bool $create Pass true to automatically create the dictionary
     * @return null|SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary($create = false)
    {
        $dictionary = $this->_annotation->getDictionary();

        $aa = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'AA');
        if (!$aa instanceof SetaPDF_Core_Type_Dictionary) {
            if ($create === false) {
                return null;
            }

            $aa = new SetaPDF_Core_Type_Dictionary();
            $dictionary->offsetSet('AA', $aa);
        }

        return $aa;
    }

    /**
     * Get the action that shall be performed when the cursor enters the annotation’s active area.
     *
     * @return null|SetaPDF_Core_Document_Action
     */
    public function getCursorEnter()
    {
        return $this->_getAction('E');
    }

    /**
     * Set the action that shall be performed when the cursor enters the annotation’s active area.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setCursorEnter(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('E', $action);

        return $this;
    }

    /**
     * Get the action that shall be performed when the cursor exits the annotation’s active area.
     *
     * @return null|SetaPDF_Core_Document_Action
     */
    public function getCursorExit()
    {
        return $this->_getAction('X');
    }

    /**
     * Set the action that shall be performed when the cursor exits the annotation’s active area.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setCursorExit(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('X', $action);

        return $this;
    }

    /**
     * Get the action that shall be performed when the mouse button is pressed inside the annotation’s active area.
     *
     * @return null|SetaPDF_Core_Document_Action
     */
    public function getMouseDown()
    {
        return $this->_getAction('D');
    }

    /**
     * Set the action that shall be performed when the mouse button is pressed inside the annotation’s active area.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setMouseDown(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('D', $action);

        return $this;
    }

    /**
     * Get the action that shall be performed when the mouse button is released inside the annotation’s active area.
     *
     * @return null|SetaPDF_Core_Document_Action
     */
    public function getMouseUp()
    {
        return $this->_getAction('U');
    }

    /**
     * Set the action that shall be performed when the mouse button is released inside the annotation’s active area.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setMouseUp(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('U', $action);

        return $this;
    }

    /**
     * Get the action that shall be performed when the page containing the annotation is opened.
     *
     * @return null|SetaPDF_Core_Document_Action
     */
    public function getPageOpen()
    {
        return $this->_getAction('PO');
    }

    /**
     * Set the action that shall be performed when the page containing the annotation is opened.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPageOpen(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('PO', $action);

        return $this;
    }

    /**
     * Get the action that shall be performed when the page containing the annotation is closed.
     *
     * @return null|SetaPDF_Core_Document_Action
     */
    public function getPageClose()
    {
        return $this->_getAction('PC');
    }

    /**
     * Set the action that shall be performed when the page containing the annotation is closed.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPageClose(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('PC', $action);

        return $this;
    }

    /**
     * Get the action that shall be performed when the page containing the annotation becomes visible.
     *
     * @return null|SetaPDF_Core_Document_Action
     */
    public function getPageVisible()
    {
        return $this->_getAction('PV');
    }

    /**
     * Set the action that shall be performed when the page containing the annotation becomes visible.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPageVisiable(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('PV', $action);

        return $this;
    }

    /**
     * Get the action that shall be performed when the page containing the annotation is no longer visible in the
     * conforming reader’s user interface.
     *
     * @return null|SetaPDF_Core_Document_Action
     */
    public function getPageInvisible()
    {
        return $this->_getAction('PI');
    }

    /**
     * Set the action that shall be performed when the page containing the annotation is no longer visible in the
     * conforming reader’s user interface.
     *
     * @param SetaPDF_Core_Document_Action $action
     * @return SetaPDF_Core_Document_Page_Annotation_AdditionalActions
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPageInvisiable(SetaPDF_Core_Document_Action $action)
    {
        $this->_setAction('PI', $action);

        return $this;
    }

    /**
     * Get the action.
     *
     * @param string $name
     * @param boolean $instance
     * @return null|SetaPDF_Core_Document_Action
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _getAction($name, $instance = true)
    {
        $dictionary = $this->getDictionary();
        if ($dictionary === null) {
            return null;
        }


        $action = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, $name);
        if ($action === null) {
            return null;
        }

        if ($instance) {
            return SetaPDF_Core_Document_Action::byObjectOrDictionary($action);
        }

        return $action;
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
