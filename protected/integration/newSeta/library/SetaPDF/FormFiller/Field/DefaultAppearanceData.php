<?php
/**
 * This file is part of the SetaPDF-FormFiller Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @subpackage Field
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: DefaultAppearanceData.php 1785 2022-10-19 09:51:16Z jan.slabon $
 */

/**
 * Helper class to parse data from the default apperance entry of a form field.
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @subpackage Field
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_FormFiller_Field_DefaultAppearanceData
{
    /**
     * The font name.
     *
     * @var string
     */
    protected $_fontName;

    /**
     * The font size.
     *
     * @var float
     */
    protected $_fontSize = .0;

    /**
     * The text color.
     *
     * @var SetaPDF_Core_DataStructure_Color
     */
    protected $_textColor;

    /**
     * The constuctor.
     *
     * @param string $daString The default appearance string.
     * @throws SetaPDF_Core_Exception
     * @throws SetaPDF_Core_Parser_Pdf_InvalidTokenException
     */
    public function __construct($daString)
    {
        $parser = new SetaPDF_Core_Parser_Content($daString);
        $parser->registerOperator(['g', 'rg', 'k'], [$this, '_onNonstrokingColor']);
        $parser->registerOperator('Tf', [$this, '_onTextFont']);
        $parser->process();
    }

    /**
     * Callback of the content parser which is called if a nonstroking color operator is matched (g, rg, k).
     *
     * @param array $arguments
     */
    public function _onNonstrokingColor($arguments)
    {
        $this->_textColor = SetaPDF_Core_DataStructure_Color::createByComponents($arguments);
    }

    /**
     * Callback of the content parser which is called if the text font operator is matched (Tf).
     *
     * @param array $arguments
     * @throws SetaPDF_FormFiller_Field_Exception
     */
    public function _onTextFont($arguments)
    {
        $c = count($arguments);
        $this->_fontName = isset($arguments[$c - 2]) ? $arguments[$c - 2]->getValue() : null;
        $this->_fontSize = isset($arguments[$c - 1]) ? $arguments[$c - 1]->getValue() : .0;
    }

    /**
     * Get the font name.
     *
     * @return string
     */
    public function getFontName()
    {
        return $this->_fontName;
    }

    /**
     * Get the font size.
     *
     * @return float
     */
    public function getFontSize()
    {
        return $this->_fontSize;
    }

    /**
     * Get the text color.
     *
     * @return SetaPDF_Core_DataStructure_Color
     */
    public function getTextColor()
    {
        if ($this->_textColor === null) {
            return new SetaPDF_Core_DataStructure_Color_Gray(0);
        }

        return $this->_textColor;
    }
}
