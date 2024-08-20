<?php
/**
 * This file is part of the SetaPDF-FormFiller Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @subpackage Field
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id$
 */

/**
 * Field appearance callback interface
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @subpackage Field
 * @license    https://www.setasign.com/ Commercial
 */
interface SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface
extends SetaPDF_FormFiller_Field_FieldInterface
{
    /**
     * Set the appearance value callback (to e.g. format a number).
     *
     * The callback will be called with 2 arguments:
     *  1. A reference to the field instance
     *  2. The requested encoding
     *
     * It needs to return a value in the specified encoding (internal calls need UTF-16BE throughout).
     *
     * @param callback $callback
     */
    public function setAppearanceValueCallback($callback);

    /**
     * Get the appearance value.
     *
     * @param string $encoding
     * @return string
     */
    public function getAppearanceValue($encoding = 'UTF-8');

    /**
     * Returns the field specific visible value.
     *
     * This value is used for e.g. formatting while the real export value may be a different one (for e.g. combo boxes).
     *
     * @param string $encoding
     * @return mixed
     */
    public function getVisibleValue($encoding = 'UTF-8');

    /**
     * Set an individual appearance text color.
     *
     * @param SetaPDF_Core_DataStructure_Color $textColor
     */
    public function setAppearanceTextColor(SetaPDF_Core_DataStructure_Color $textColor = null);

    /**
     * Get the field dictionary.
     *
     * @return SetaPDF_Core_Type_Dictionary
     */
    public function getFieldDictionary();
}
