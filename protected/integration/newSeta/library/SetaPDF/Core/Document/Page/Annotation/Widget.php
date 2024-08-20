<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Widget.php 1789 2022-11-09 11:19:00Z jan.slabon $
 */

/**
 * Class representing a widget annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.19
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Widget
    extends SetaPDF_Core_Document_Page_Annotation
{
    /**
     * @var SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    protected $_borderStyle;

    /**
     * Creates a widget annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createAnnotationDictionary($rect)
    {
        return SetaPDF_Core_Document_Page_Annotation::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_WIDGET);
    }

    /**
     * The constructor.
     *
     * A widget annotation instance can be created by an existing dictionary, indirect object/reference or by passing
     * the same parameter as for {@link createAnnotationDictionary()}.
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

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Widget')) {
            throw new InvalidArgumentException('The Subtype entry in a widget annotation shall be "Widget".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Release memory/cycled references.
     */
    public function cleanUp()
    {
        parent::cleanUp();

        if ($this->_borderStyle !== null) {
            $this->_borderStyle->cleanUp();
            $this->_borderStyle = null;
        }
    }

    /**
     * Get the height of the annotation.
     *
     * If the annotation is rotated width and height will be changed accordingly. This can be affected by the
     * $ignoreRotation parameter.
     *
     * @param boolean $ignoreRotation
     * @return float|int
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getHeight($ignoreRotation = false)
    {
        $rect = $this->getRect();
        $mk = $this->getAppearanceCharacteristics();
        if ($ignoreRotation || $mk === null) {
            return $rect->getHeight();
        }

        $rotation = $mk->getRotation();
        $interchange = ($rotation / 90) % 2;

        return $interchange ? $rect->getWidth() : $rect->getHeight();
    }

    /**
     * Get the width of the annotation.
     *
     * If the annotation is rotated width and height will be changed accordingly. This can be affected by the
     * $ignoreRotation parameter.
     *
     * @param boolean $ignoreRotation
     * @return float|int
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getWidth($ignoreRotation = false)
    {
        $rect = $this->getRect();
        $mk = $this->getAppearanceCharacteristics();
        if ($ignoreRotation || $mk === null) {
            return $rect->getWidth();
        }

        $rotation = $mk->getRotation();
        $interchange = ($rotation / 90) % 2;

        return $interchange ? $rect->getHeight() : $rect->getWidth();
    }

    /**
     * Get the action of the annotation.
     *
     * If no action is defined false will be returned.
     *
     * @return bool|SetaPDF_Core_Document_Action
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAction()
    {
        $a = $this->getDictionary()->getValue('A');
        if ($a === null) {
            return false;
        }

        return SetaPDF_Core_Document_Action::byObjectOrDictionary($a);
    }

    /**
     * Set the action of the annotation.
     *
     * The action could be an instance of {@link SetaPDF_Core_Document_Action} or a plain dictionary representing
     * the action.
     *
     * @throws InvalidArgumentException
     * @param SetaPDF_Core_Document_Action|SetaPDF_Core_Type_Dictionary $action
     */
    public function setAction($action)
    {
        if ($action instanceof SetaPDF_Core_Document_Action) {
            $action = $action->getActionDictionary();
        }

        if (!($action instanceof SetaPDF_Core_Type_Dictionary) || !$action->offsetExists('S')) {
            throw new InvalidArgumentException('Invalid $action parameter. SetaPDF_Core_Document_Action or SetaPDF_Core_Type_Dictionary with an S key needed.');
        }

        $this->getDictionary()->offsetSet('A', $action);
    }

    /**
     * Get the appearance characteristics object.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Document_Page_Annotation_AppearanceCharacteristics
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAppearanceCharacteristics($create = false)
    {
        $dict = $this->getDictionary();
        $mk = $dict->getValue('MK');
        if ($mk === null) {
            if (!$create) {
                return null;
            }

            $mk = new SetaPDF_Core_Type_Dictionary();
            $dict->offsetSet('MK', $mk);
        }

        return new SetaPDF_Core_Document_Page_Annotation_AppearanceCharacteristics($mk);
    }

    /**
     * Gets the additional actions object instance for this annotation.
     *
     * @return SetaPDF_Core_Document_Page_Annotation_Widget_AdditionalActions
     */
    public function getAdditionalActions()
    {
        if ($this->_additionalActions === null) {
            $this->_additionalActions = new SetaPDF_Core_Document_Page_Annotation_Widget_AdditionalActions($this);
        }

        return $this->_additionalActions;
    }

    /**
     * Get the border style object.
     *
     * @return SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    public function getBorderStyle()
    {
        if ($this->_borderStyle === null) {
            $this->_borderStyle = new SetaPDF_Core_Document_Page_Annotation_BorderStyle($this);
        }

        return $this->_borderStyle;
    }
}
