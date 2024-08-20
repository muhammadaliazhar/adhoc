<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Text.php 1789 2022-11-09 11:19:00Z jan.slabon $
 */

/**
 * Class representing a Text annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.14
 *
 * A text annotations icon will display a static predefined icon which will not resize if the
 * document is zoomed. It will be aligned to the upper left corner of the Rect.
 *
 * By setting the no rotate flag ({@link SetaPDF_Core_Document_Page_Annotation::setNoRotateFlag})
 * and the no-zoom flag ({@link SetaPDF_Core_Document_Page_Annotation::setNoZoomFlag}) the fixed
 * size can be disabled and will allow you to define the size of the annotation your own. Anyhow
 * the annotation is still not zoomable.
 *
 * The aspect ratio of default icons are:
 * Comment: 20 x 18
 * Key: 18 x 17
 * Note: 18 x 20
 * Help: 20 x 20
 * NewParagraph: 13 x 20
 * Paragraph: 11 x 20
 * Insert: 20 x 17
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Text
    extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.4 Text Annotations
     *
     * @var string
     */
    const ICON_COMMENT = 'Comment'; // Default Size: 20 x 18

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.4 Text Annotations
     *
     * @var string
     */
    const ICON_KEY = 'Key'; // Default Size: 18 x 17

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.4 Text Annotations
     *
     * @var string
     */
    const ICON_NOTE = 'Note'; // Default Size: 18 x 20

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.4 Text Annotations
     *
     * @var string
     */
    const ICON_HELP = 'Help'; // Default Size: 20 x 20

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.4 Text Annotations
     *
     * @var string
     */
    const ICON_NEW_PARAGRAPH = 'NewParagraph'; // Default Size: 13 x 20

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.4 Text Annotations
     *
     * @var string
     */
    const ICON_PARAGRAPH = 'Paragraph'; // Default Size: 11 x 20

    /**
     * Icon name defined in PDF 32000-1:2008 - 12.5.6.4 Text Annotations
     *
     * @var string
     */
    const ICON_INSERT = 'Insert'; // Default Size: 20 x 17

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_CIRCLE = 'Circle';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_CHECK = 'Check';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_CHECKMARK = 'Checkmark';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_CROSS = 'Cross';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_CROSS_HAIRS = 'CrossHairs';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_RIGHT_ARROW = 'RightArrow';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_RIGHT_POINTER = 'RightPointer';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_STAR = 'Star';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_UP_ARROW = 'UpArrow';

    /**
     * Icon name supported by Adobe Acrobat
     *
     * @var string
     */
    const ICON_UP_LEFT_ARROW = 'UpLeftArrow';

    /**
     * State model name
     *
     * @var string
     */
    const STATE_MODEL_MARKED = 'Marked';

    /**
     * State model name
     *
     * @var string
     */
    const STATE_MODEL_REVIEW = 'Review';

    /**
     * State model name
     *
     * @var string
     */
    const STATE_MODEL_MIGRATION_STATUS = 'MigrationStatus';

    /**
     * Creates an text annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createAnnotationDictionary($rect)
    {
        return parent::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_TEXT);
    }

    /**
     * The constructor.
     *
     * @param array|SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_IndirectObjectInterface $objectOrDictionary The annotation dictionary or a rect value
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
                [self::class, 'createAnnotationDictionary'],
                $args
            ));
            unset($args);
        }

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Text')) {
            throw new InvalidArgumentException('The Subtype entry in a Text annotation shall be "Text".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Checks if the annotation shall initially be displayed open.
     *
     * @return bool
     */
    public function isOpen()
    {
        $open = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Open');
        if (!$open instanceof SetaPDF_Core_Type_Boolean) {
            return false;
        }

        return $open->getValue();
    }

    /**
     * Sets whether the annotation shall initially be displayed open or not.
     *
     * @param bool $open
     */
    public function setOpen($open)
    {
        $dict = $this->getDictionary();
        if (!$open) {
            $dict->offsetUnset('Open');
            return;
        }

        $openObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Open');
        if (!$openObject instanceof SetaPDF_Core_Type_Boolean) {
            $dict->offsetSet('Open', new SetaPDF_Core_Type_Boolean($open));
            return;
        }

        $openObject->setValue($open);
    }

    /**
     * Get the icon name of the annotation.
     *
     * @return string
     */
    public function getIconName()
    {
        $name = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Name');
        if (!$name instanceof SetaPDF_Core_Type_Name) {
            return self::ICON_NOTE;
        }

        return $name->getValue();
    }

    /**
     * Set the name of the icon that shall be used in displaying the annotation.
     *
     * @param null|string $iconName
     */
    public function setIconName($iconName)
    {
        $dict = $this->getDictionary();
        if (!$iconName) {
            $dict->offsetUnset('Name');
            return;
        }

        $name = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Name');
        if (!$name instanceof SetaPDF_Core_Type_Name) {
            $dict->offsetSet('Name', new SetaPDF_Core_Type_Name($iconName));
            return;
        }

        $name->setValue($iconName);
    }

    /**
     * Get the state model.
     *
     * @see PDF 32000-1:2008 - 12.5.6.3 Annotation States
     * @return mixed|null
     */
    public function getStateModel()
    {
        $stateModel = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'StateModel');
        if (!$stateModel instanceof SetaPDF_Core_Type_StringValue) {
            return null;
        }

        return $stateModel->getValue();
    }

    /**
     * Set the annotation model.
     *
     * @see PDF 32000-1:2008 - 12.5.6.3 Annotation States
     * @param string $stateModel
     */
    public function setStateModel($stateModel)
    {
        $dict = $this->getDictionary();
        if (!$stateModel) {
            $dict->offsetUnset('StateModel');
            return;
        }

        $stateModel = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'StateModel');
        if (!$stateModel instanceof SetaPDF_Core_Type_StringValue) {
            $dict->offsetSet('StateModel', new SetaPDF_Core_Type_String($stateModel));
            return;
        }

        $stateModel->setValue($stateModel);
    }

    /**
     * Get the annotation state.
     *
     * @see PDF 32000-1:2008 - 12.5.6.3 Annotation States
     * @return mixed|null
     */
    public function getState()
    {
        $state = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'State');
        if (!$state instanceof SetaPDF_Core_Type_StringValue) {
            return null;
        }

        return $state->getValue();
    }

    /**
     * Set the annotation state.
     *
     * This annotation should be a reply to another one and following annotation flags has to be set:
     * <code>
     * $annotation->setAnnotationFlags(
     *     SetaPDF_Core_Document_Page_Annotation_Flags::HIDDEN |
     *     SetaPDF_Core_Document_Page_Annotation_Flags::NO_ROTATE |
     *     SetaPDF_Core_Document_Page_Annotation_Flags::NO_ZOOM |
     *     SetaPDF_Core_Document_Page_Annotation_Flags::PRINTS
     * );
     * </code>
     * Otherwise Acrobat/Reader will not display the state in the comments panel.
     *
     * @see PDF 32000-1:2008 - 12.5.6.3 Annotation States
     * @param string $state
     */
    public function setState($state)
    {
        $dict = $this->getDictionary();
        if (!$state) {
            $dict->offsetUnset('State');
            return;
        }

        $stateObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'State');
        if (!$stateObject instanceof SetaPDF_Core_Type_StringValue) {
            $dict->offsetSet('State', new SetaPDF_Core_Type_String($state));
            return;
        }

        $stateObject->setValue($state);
    }
}
