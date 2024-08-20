<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: TextMarkup.php 1742 2022-06-20 14:29:40Z jan.slabon $
 */

/**
 * Abstract class representing a text markup annotation.
 *
 * See PDF 32000-1:2008 - 12.5.6.10
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
abstract class SetaPDF_Core_Document_Page_Annotation_TextMarkup
    extends SetaPDF_Core_Document_Page_Annotation_Markup
{
    use SetaPDF_Core_Document_Page_Annotation_QuadPointsTrait;

    /**
     * Creates a highlight annotation dictionary.
     *
     * @param SetaPDF_Core_DataStructure_Rectangle|array $rect
     * @param string $subtype
     * @return SetaPDF_Core_Type_Dictionary
     * @throws InvalidArgumentException
     * @throws SetaPDF_Core_Type_Exception
     */
    protected static function _createAnnotationDictionary($rect, $subtype)
    {
        $dictionary = parent::_createAnnotationDictionary($rect, $subtype);

        $rect = new SetaPDF_Core_DataStructure_Rectangle(
            SetaPDF_Core_Type_Array::ensureType(SetaPDF_Core_Type_Dictionary_Helper::getValue($dictionary, 'Rect'))
        );
        // TopLeft, TopRight, BottomLeft, BottomRight
        $dictionary['QuadPoints'] = new SetaPDF_Core_Type_Array([
            new SetaPDF_Core_Type_Numeric($rect->getLlx()),
            new SetaPDF_Core_Type_Numeric($rect->getUry()),
            new SetaPDF_Core_Type_Numeric($rect->getUrx()),
            new SetaPDF_Core_Type_Numeric($rect->getUry()),
            new SetaPDF_Core_Type_Numeric($rect->getLlx()),
            new SetaPDF_Core_Type_Numeric($rect->getLly()),
            new SetaPDF_Core_Type_Numeric($rect->getUrx()),
            new SetaPDF_Core_Type_Numeric($rect->getLly()),
        ]);

        return $dictionary;
    }
}
