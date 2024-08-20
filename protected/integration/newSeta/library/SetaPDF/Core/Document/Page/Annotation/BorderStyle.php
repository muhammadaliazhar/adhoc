<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: BorderStyle.php 1715 2022-05-06 10:18:19Z jan.slabon $
 */

/**
 * Class representing annotations border style dictionary
 *
 * See PDF 32000-1:2008 - 12.5.4 Border Styles
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Page_Annotation_BorderStyle
{
    /**
     * Border style
     *
     * @var string
     */
    const SOLID = 'S';

    /**
     * Border style
     *
     * @var string
     */
    const DASHED = 'D';

    /**
     * Border style
     *
     * @var string
     */
    const BEVELED = 'B';

    /**
     * Border style
     *
     * @var string
     */
    const INSET = 'I';

    /**
     * Border style
     *
     * @var string
     */
    const UNDERLINE = 'U';

    /**
     * The dictionary
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
     * Get the dictionary of it.
     *
     * @param boolean $create Defines whether the dictionary should be created if it doesn't exists
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getDictionary($create = false)
    {
        $dictionary = $this->_annotation->getDictionary();
        if ($dictionary === null) {
            return null;
        }

        $bs = SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'BS');
        if (!$bs instanceof SetaPDF_Core_Type_Dictionary) {
            if ($create === false) {
                return null;
            }

            $bs = new SetaPDF_Core_Type_Dictionary();
            $dictionary->offsetSet('BS', $bs);
        }

        return $bs;
    }

    /**
     * Get the border width.
     *
     * @return int|float
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getWidth()
    {
        $dictionary = $this->getDictionary();
        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            return 0;
        }

        return SetaPDF_Core_Type_Numeric::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'W', new SetaPDF_Core_Type_Numeric(0))
        )->getValue();
    }

    /**
     * Set the border width.
     *
     * @param null|int|float $width
     * @return self
     */
    public function setWidth($width)
    {
        $dictionary = $this->getDictionary($width !== null);
        if ($width === null) {
            if ($dictionary instanceof SetaPDF_Core_Type_Dictionary) {
                $dictionary->offsetUnset('W');
            }
            return null;
        }

        $dictionary->offsetSet('W', new SetaPDF_Core_Type_Numeric($width));

        return $this;
    }

    /**
     * Get the border style.
     *
     * @return string
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getStyle()
    {
        $dictionary = $this->getDictionary();
        if (!($dictionary instanceof SetaPDF_Core_Type_Dictionary)) {
            return self::SOLID;
        }

        return SetaPDF_Core_Type_Name::ensureType(
            SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'S', new SetaPDF_Core_Type_Name(self::SOLID))
        )->getValue();
    }

    /**
     * Set the border style.
     *
     * @param null|string $style
     * @return self
     */
    public function setStyle($style)
    {
        $dictionary = $this->getDictionary($style !== null);
        if ($style === null) {
            if ($dictionary instanceof SetaPDF_Core_Type_Dictionary) {
                $dictionary->offsetUnset('S');
            }
            return null;
        }

        $dictionary->offsetSet('S', new SetaPDF_Core_Type_Name($style));

        return $this;
    }

    /**
     * Get the dash pattern.
     *
     * @return array|null
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getDashPattern()
    {
        $default = $this->getStyle() === self::DASHED
            ? new SetaPDF_Core_Type_Array([new SetaPDF_Core_Type_Numeric(3)])
            : SetaPDF_Core_Type_Null::getInstance();

        $dictionary = $this->getDictionary();
        if (!$dictionary instanceof SetaPDF_Core_Type_Dictionary) {
            return $default->toPhp();
        }

        return SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'D', $default)->toPhp(true);
    }

    /**
     * Set the dash pattern.
     *
     * @param array|SetaPDF_Core_Type_Array $pattern
     * @return self
     */
    public function setDashPattern($pattern)
    {
        $dictionary = $this->getDictionary($pattern !== null);
        if ($pattern === null) {
            if ($dictionary instanceof SetaPDF_Core_Type_Dictionary) {
                $dictionary->offsetUnset('D');
            }
            return null;
        }

        if (!$pattern instanceof SetaPDF_Core_Type_Array) {
            $_pattern = (array)$pattern;
            $pattern = new SetaPDF_Core_Type_Array();
            foreach ($_pattern AS $dash) {
                $pattern->offsetSet(null, new SetaPDF_Core_Type_Numeric($dash));
            }
        }

        $dictionary->offsetSet('D', $pattern);

        return $this;
    }
}
