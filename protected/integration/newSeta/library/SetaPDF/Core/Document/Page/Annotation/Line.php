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
 * Class representing a line annotation
 *
 * See PDF 32000-1:2008 - 12.5.6.7
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_Line
    extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    /**
     * Intent constant
     *
     * @var string
     */
    const INTENT_LINE_ARROW = 'LineArrow';

    /**
     * Intent constant
     *
     * @var string
     */
    const INTENT_LINE_DIMENSION = 'LineDimension';

    /**
     * Caption position constant
     *
     * @var string
     */
    const CAPTION_POSITION_INLINE = 'Inline';

    /**
     * Caption position constant
     *
     * @var string
     */
    const CAPTION_POSITION_TOP = 'Top';

    /**
     * @var SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    protected $_borderStyle;

    /**
     * Creates an annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     *
     * @param float|int|array $x1OrPoints The x-value of the first point. Or an array of 4 values [x1, y1, x2, y2].
     * @param float|int $y1 The y-value of the first point.
     * @param float|int $x2 The x-value of the second point.
     * @param float|int $y2 The y-value of the seconf point.
     * @return SetaPDF_Core_Type_Dictionary
     */
    public static function createAnnotationDictionary($rect, $x1OrPoints, $y1 = null, $x2 = null, $y2 = null)
    {
        $dict = parent::_createAnnotationDictionary($rect, SetaPDF_Core_Document_Page_Annotation::TYPE_LINE);
        if (!is_array($x1OrPoints)) {
            $x1OrPoints = [$x1OrPoints, $y1, $x2, $y2];
        }

        $x1OrPoints = array_values($x1OrPoints);
        $count = count($x1OrPoints);
        if ($count !== 4) {
            throw new InvalidArgumentException(
                'The line annotation needs 4 values (' . $count . ' given) to create 2 points.'
            );
        }

        /** @noinspection OffsetOperationsInspection */
        $dict->offsetSet('L', new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Numeric($x1OrPoints[0]),
            new SetaPDF_Core_Type_Numeric($x1OrPoints[1]),
            new SetaPDF_Core_Type_Numeric($x1OrPoints[2]),
            new SetaPDF_Core_Type_Numeric($x1OrPoints[3]),
        ]));

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

        if (!SetaPDF_Core_Type_Dictionary_Helper::keyHasValue($dictionary, 'Subtype', 'Line')) {
            throw new InvalidArgumentException('The Subtype entry in a line annotation shall be "Line".');
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
     * Get the border style object.
     *
     * @return null|SetaPDF_Core_Document_Page_Annotation_BorderStyle
     */
    public function getBorderStyle()
    {
        if ($this->_borderStyle === null) {
            $this->_borderStyle = new SetaPDF_Core_Document_Page_Annotation_BorderStyle($this);
        }

        return $this->_borderStyle;
    }

    /**
     * Set the line ending styles.
     *
     * @see SetaPDF_Core_Document_Page_Annotation_LineEndingStyle
     * @param string $first
     * @param string $last
     */
    public function setLineEndingStyles($first, $last)
    {
        $dict = $this->getDictionary();
        $le = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'LE');
        if (!$le instanceof SetaPDF_Core_Type_Array) {
            $le = new SetaPDF_Core_Type_Array();
            $dict->offsetSet('LE', $le);
        }

        $allowed = SetaPDF_Core_Document_Page_Annotation_LineEndingStyle::getAll();
        if (!in_array($first, $allowed, true)) {
            throw new InvalidArgumentException('Invalid line ending style parameter "' . $first . '".');
        }

        if (!in_array($last, $allowed, true)) {
            throw new InvalidArgumentException('Invalid line ending style parameter "' . $last . '".');
        }

        $le->clear();
        $le[] = new SetaPDF_Core_Type_Name($first);
        $le[] = new SetaPDF_Core_Type_Name($last);
    }

    /**
     * Get the line ending styles.
     *
     * @return array
     */
    public function getLineEndingStyles()
    {
        $le = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'LE');
        if (!$le instanceof SetaPDF_Core_Type_Array) {
            return [
                SetaPDF_Core_Document_Page_Annotation_LineEndingStyle::NONE,
                SetaPDF_Core_Document_Page_Annotation_LineEndingStyle::NONE
            ];
        }

        return $le->toPhp(true);
    }

    /**
     * Set the interior color.
     *
     * @param null|int|array|SetaPDF_Core_DataStructure_Color $color
     */
    public function setInteriorColor($color)
    {
        $dict = $this->getDictionary();
        if ($color === null) {
            $dict->offsetUnset('IC');
            return;
        }

        if (!$color instanceof SetaPDF_Core_DataStructure_Color) {
            $color = SetaPDF_Core_DataStructure_Color::createByComponents($color);
        }

        $dict->offsetSet('IC', $color->getValue());
    }

    /**
     * Get the interior color.
     *
     * @return null|SetaPDF_Core_DataStructure_Color
     */
    public function getInteriorColor()
    {
        $ic = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'IC');
        if ($ic === null) {
            return null;
        }

        return SetaPDF_Core_DataStructure_Color::createByComponents($ic);
    }

    /**
     * Set the length of leader lines.
     *
     * @param int|float $length
     */
    public function setLeaderLine($length)
    {
        $dict = $this->getDictionary();
        if (!$length) {
            $dict->offsetUnset('LL');
            return;
        }

        $dict->offsetSet('LL', new SetaPDF_Core_Type_Numeric($length));
    }

    /**
     * Get the length of leader lines.
     *
     * @return int|float
     */
    public function getLeaderLine()
    {
        $length = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'LL');
        if (!$length instanceof SetaPDF_Core_Type_Numeric) {
            return 0;
        }

        return $length->getValue();
    }

    /**
     * Set the length of leader line extensions.
     *
     * @param int|float $length
     */
    public function setLeaderLineExtension($length)
    {
        $dict = $this->getDictionary();
        if (!$length) {
            $dict->offsetUnset('LLE');
            return;
        }

        $dict->offsetSet('LLE', new SetaPDF_Core_Type_Numeric($length));
    }

    /**
     * Get the length of leader line extensions.
     *
     * @return int|float
     */
    public function getLeaderLineExtension()
    {
        $length = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'LLE');
        if (!$length instanceof SetaPDF_Core_Type_Numeric) {
            return 0;
        }

        return $length->getValue();
    }

    /**
     * Set whether a caption should be shown or not.
     *
     * The value can be defined through the {@link self::setContent()} method.
     *
     * @param boolean $caption
     */
    public function setCaption($caption)
    {
        $dict = $this->getDictionary();
        if (!$caption) {
            $dict->offsetUnset('Cap');
            return;
        }

        $dict->offsetSet('Cap', new SetaPDF_Core_Type_Boolean(true));
    }

    /**
     * Get whether a caption should be shown or not.
     *
     * @return boolean
     */
    public function getCaption()
    {
        $cap = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'Cap');
        if (!$cap instanceof SetaPDF_Core_Type_Boolean) {
            return false;
        }

        return $cap->getValue();
    }

    /**
     * Set the name describing the intent of the free text annotation.
     *
     * @param string $intent
     */
    public function setIntent($intent)
    {
        $allowed = [
            self::INTENT_LINE_ARROW,
            self::INTENT_LINE_DIMENSION
        ];

        if (!in_array($intent, $allowed, true)) {
            throw new InvalidArgumentException('Invalid intent parameter "' . $intent . '".');
        }

        $this->getDictionary()->offsetSet('IT', new SetaPDF_Core_Type_Name($intent));
    }

    /**
     * Get the name describing the intent of the line annotation.
     *
     * @return string
     */
    public function getIntent()
    {
        $intent = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'IT');
        if (!$intent instanceof SetaPDF_Core_Type_Name) {
            return null;
        }

        return $intent->getValue();
    }

    /**
     * Set length of the leader line offset.
     *
     * @param int|float $offset A non-negative number.
     */
    public function setLeaderLineOffset($offset)
    {
        $dict = $this->getDictionary();
        if (!$offset || $offset < 0) {
            $dict->offsetUnset('LLO');
            return;
        }

        $dict->offsetSet('LLO', new SetaPDF_Core_Type_Numeric($offset));
    }

    /**
     * Get length of the leader line offset.
     *
     * @return int|float
     */
    public function getLeaderLineOffset()
    {
        $offset = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'LLO');
        if (!$offset instanceof SetaPDF_Core_Type_Numeric) {
            return null;
        }

        return $offset->getValue();
    }

    /**
     * Set the caption position.
     *
     * See self::CAPTION_* constants for possible values.
     *
     * @param string $captionPosition
     */
    public function setCaptionPosition($captionPosition)
    {
        $dict = $this->getDictionary();
        if (!$captionPosition || $captionPosition === self::CAPTION_POSITION_INLINE) {
            $dict->offsetUnset('CP');
            return;
        }

        if ($captionPosition !== self::CAPTION_POSITION_TOP) {
            throw new InvalidArgumentException('Invalid caption position "' . $captionPosition . '".');
        }

        $dict->offsetSet('CP', new SetaPDF_Core_Type_Name($captionPosition));
    }

    /**
     * Get the caption position.
     *
     * @return string
     */
    public function getCaptionPosition()
    {
        $captionPosition = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'CP');
        if (!$captionPosition instanceof SetaPDF_Core_Type_Name) {
            return self::CAPTION_POSITION_INLINE;
        }

        return $captionPosition->getValue();
    }

    /**
     * Set the offset of the caption text.
     *
     * @param int|float $x
     * @param int|float $y
     */
    public function setCaptionOffset($x, $y)
    {
        $dict = $this->getDictionary();
        if (($x + $y) == 0) {
            $dict->offsetUnset('CO');
            return;
        }

        $co = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'CO');
        if (!$co instanceof SetaPDF_Core_Type_Array) {
            $co = new SetaPDF_Core_Type_Array();
            $dict->offsetSet('CO', $co);
        }

        $co->clear();
        $co[] = new SetaPDF_Core_Type_Numeric($x);
        $co[] = new SetaPDF_Core_Type_Numeric($y);
    }

    /**
     * Get the offset of the caption text.
     *
     * @return int[]|float[]
     */
    public function getCaptionOffset()
    {
        $co = SetaPDF_Core_Type_Dictionary_Helper::getValue($this->getDictionary(), 'CO');
        if (!$co instanceof SetaPDF_Core_Type_Array) {
            return [0, 0];
        }

        return $co->toPhp(true);
    }
}
