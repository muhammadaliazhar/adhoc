<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Highlight.php 1052 2017-05-18 09:49:46Z jan.slabon $
 */

/**
 * Class representing a free text annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.6
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_FreeText
extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    /**
     * Intent constant
     *
     * @var string
     */
    const INTENT_FREE_TEXT = 'FreeText';

    /**
     * Intent constant
     *
     * @var string
     */
    const INTENT_FREE_TEXT_CALLOUT = 'FreeTextCallout';

    /**
     * Intent constant
     *
     * @var string
     */
    const INTENT_FREE_TEXT_TYPE_WRITER = 'FreeTextTypeWriter';

    /**
     * @var SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    protected $_borderStyle;

    /**
     * Creates an annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @param string $fontName The font name, which is registered in the AcroForm DR dictionary.
     * @param int|float $fontSize The font size
     * @param int|float|string|array|SetaPDF_Core_Type_Array|SetaPDF_Core_DataStructure_Color $color See
     *                                  {@link SetaPDF_Core_DataStructure_Color::createByComponents()} for more details.
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     */
    public static function createAnnotationDictionary($rect, $fontName, $fontSize = 12, $color = 0)
    {
        $dict = parent::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_FREE_TEXT);

        $writer = new SetaPDF_Core_Writer();
        SetaPDF_Core_Type_Name::writePdfString($writer, $fontName);
        SetaPDF_Core_Type_Numeric::writePdfString($writer, $fontSize);
        $writer->write(' Tf');

        if (!($color instanceof SetaPDF_Core_DataStructure_Color)) {
            $color = SetaPDF_Core_DataStructure_Color::createByComponents($color);
        }
        $color->draw($writer, false);
        $dict->offsetSet('DA', new SetaPDF_Core_Type_String((string)$writer));

        return $dict;
    }

    /**
     * The constructor.
     *
     * If the parameter cannot be resolved as a dictionary all parameters were passed to the
     * {@link self::createAnnotationDictionary()} method.
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

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'FreeText')) {
            throw new InvalidArgumentException('The Subtype entry in a free text annotation shall be "FreeText".');
        }

        parent::__construct($objectOrDictionary);
    }

    /**
     * Release cycled references.
     */
    public function cleanUp()
    {
        $this->_borderStyle->cleanUp();
        $this->_borderStyle = null;
        parent::cleanUp();
    }

    /**
     * Set the form of quadding (justification / align) that shall be used in displaying the annotation’s text.
     *
     * @see SetaPDF_Core_Text::ALIGN_LEFT
     * @see SetaPDF_Core_Text::ALIGN_CENTER
     * @see SetaPDF_Core_Text::ALIGN_RIGHT
     * @param string $align
     */
    public function setAlign($align)
    {
        $allowed = [
            SetaPDF_Core_Text::ALIGN_LEFT,
            SetaPDF_Core_Text::ALIGN_CENTER,
            SetaPDF_Core_Text::ALIGN_RIGHT
        ];

        if (!in_array($align, $allowed, true)) {
            throw new InvalidArgumentException('Invalid align parameter "' . $align . '".');
        }

        $this->getDictionary()->offsetSet('Q', new SetaPDF_Core_Type_Numeric(array_search($align, $allowed, true)));
    }

    /**
     * Get the form of quadding (justification / align) that shall be used in displaying the annotation’s text.
     *
     * @return mixed|string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getAlign()
    {
        $align = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Q');
        if ($align === null) {
            return SetaPDF_Core_Text::ALIGN_LEFT;
        }

        $align = (int)SetaPDF_Core_Type_Numeric::ensureType($align)->getValue();
        $values = [
            SetaPDF_Core_Text::ALIGN_LEFT,
            SetaPDF_Core_Text::ALIGN_CENTER,
            SetaPDF_Core_Text::ALIGN_RIGHT
        ];

        if (!isset($values[$align])) {
            return SetaPDF_Core_Text::ALIGN_LEFT;
        }

        return $values[$align];
    }

    /**
     * Get the name describing the intent of the free text annotation.
     *
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getIntent()
    {
        $intent = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'IT');
        if ($intent === null) {
            return self::INTENT_FREE_TEXT;
        }

        return SetaPDF_Core_Type_Name::ensureType($intent)->getValue();
    }

    /**
     * Set the name describing the intent of the free text annotation.
     *
     * @param string $intent
     */
    public function setIntent($intent)
    {
        $dict = $this->getDictionary();

        if (!$intent || $intent === self::INTENT_FREE_TEXT) {
            $dict->offsetUnset('IT');
            return;
        }

        $allowed = [
            self::INTENT_FREE_TEXT_CALLOUT,
            self::INTENT_FREE_TEXT_TYPE_WRITER
        ];

        if (!in_array($intent, $allowed)) {
            throw new InvalidArgumentException('Invalid intent parameter "' . $intent . '".');
        }

        $intentObject = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'IT');
        if ($intentObject instanceof SetaPDF_Core_Type_Name) {
            $intentObject->setValue($intent);
            return;
        }

        $dict->offsetSet('IT', new SetaPDF_Core_Type_Name($intent));
    }

    /**
     * Set the array of four or six numbers specifying a callout line attached to the free text annotation.
     *
     * <code>
     * 1    2
     * <---\
     *      \
     *       \
     *        3
     * </code>
     *
     * @param array<int|float>|false $x1OrPoints
     * @param null|int|float $y1
     * @param null|int|float $x2
     * @param null|int|float $y2
     * @param null|int|float $x3
     * @param null|int|float $y3
     */
    public function setCalloutLine($x1OrPoints, $y1 = null, $x2 = null, $y2 = null, $x3 = null, $y3 = null)
    {
        $dict = $this->getDictionary();
        if ($x1OrPoints === false) {
            $dict->offsetUnset('CL');
            return;
        }

        if (!is_array($x1OrPoints)) {
            $x1OrPoints = func_get_args();
        }

        $count = count($x1OrPoints);
        if ($count !== 4 && $count !== 6) {
            throw new InvalidArgumentException('Method needs 4 or 6 arguments or an array of 4 or 6 values.');
        }

        $points = array_map(static function($value) {
            return new SetaPDF_Core_Type_Numeric($value);
        }, $x1OrPoints);

        $dict->offsetSet('CL', new SetaPDF_Core_Type_Array($points));
    }

    /**
     * Get the array of four or six numbers specifying a callout line attached to the free text annotation.
     *
     * @return array|null
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getCalloutLine()
    {
        $calloutLine = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'CL');
        if ($calloutLine === null) {
            return null;
        }

        return SetaPDF_Core_Type_Array::ensureType($calloutLine)->toPhp(true);
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

    /**
     * Get the border effect object.
     *
     * @param bool $create
     * @return null|SetaPDF_Core_Document_Page_Annotation_BorderEffect
     */
    public function getBorderEffect($create = false)
    {
        $dict = $this->getDictionary();
        $be = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'BE');
        if ($be === null) {
            if ($create === false) {
                return null;
            }

            $be = new SetaPDF_Core_Type_Dictionary();
            $dict->offsetSet('BE', $be);
        }

        return new SetaPDF_Core_Document_Page_Annotation_BorderEffect($be);
    }

    /**
     * Get the rectangle describing the difference between the Rect entry and the inner text rectangle.
     *
     * @return null|SetaPDF_Core_DataStructure_Rectangle
     */
    public function getDifferencesRect()
    {
        $differencesRect = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'RD');
        if ($differencesRect === null) {
            return null;
        }

        return new SetaPDF_Core_DataStructure_Rectangle($differencesRect);
    }

    /**
     * Set the rectangle describing the difference between the Rect entry and the inner text rectangle.
     *
     * PDF 32000-1:2008 - Table 174
     * <cite>
     * The four numbers correspond to the differences in default user space between the left, top, right, and bottom
     * coordinates of Rect and those of the inner rectangle, respectively. Each value shall be greater than or equal to
     * 0. The sum of the top and bottom differences shall be less than the height of Rect, and the sum of the left and
     * right differences shall be less than the width of Rect.
     * </cite>
     *
     * @param SetaPDF_Core_DataStructure_Rectangle $differencesRect
     */
    public function setDifferencesRect(SetaPDF_Core_DataStructure_Rectangle $differencesRect)
    {
        if (array_sum($differencesRect->toPhp()) == 0) {
            $this->_annotationDictionary->offsetUnset('RD');
            return;
        }

        $this->_annotationDictionary->offsetSet('RD', $differencesRect->getValue());
    }


    /**
     * Get the line ending style.
     *
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getLineEndingStyle()
    {
        $le = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'LE');
        if ($le === null) {
            return SetaPDF_Core_Document_Page_Annotation_LineEndingStyle::NONE;
        }

        return SetaPDF_Core_Type_Name::ensureType($le)->getValue();
    }

    /**
     * Set the line ending styles.
     *
     * @see SetaPDF_Core_Document_Page_Annotation_LineEndingStyle
     * @param string $lineEndingStyle
     */
    public function setLineEndingStyle($lineEndingStyle)
    {
        $dict = $this->getDictionary();

        if (
            $lineEndingStyle === false ||
            $lineEndingStyle === SetaPDF_Core_Document_Page_Annotation_LineEndingStyle::NONE
        ) {
            $dict->offsetUnset('LE');
            return;
        }

        $allowed = SetaPDF_Core_Document_Page_Annotation_LineEndingStyle::getAll();

        if (!in_array($lineEndingStyle, $allowed, true)) {
            throw new InvalidArgumentException('Invalid line ending style parameter "' . $lineEndingStyle . '".');
        }

        $dict->offsetSet('LE', new SetaPDF_Core_Type_Name($lineEndingStyle));
    }
}
