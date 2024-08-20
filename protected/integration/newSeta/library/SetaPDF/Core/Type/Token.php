<?php
/**
 * This file is part of the SetaPDF-Core Component
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Token.php 1733 2022-06-02 07:39:42Z jan.slabon $
 */

/**
 * Class representing a token
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Type
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Type_Token extends SetaPDF_Core_Type_AbstractType
    implements SetaPDF_Core_Type_ScalarValue
{
    /**
     * The token value
     * 
     * @var boolean|string
     */
    protected $_value = false;

    /**
     * Parses a string value to a pdf token string and writes it into a writer.
     *
     * @param SetaPDF_Core_WriteInterface $writer
     * @param null|string $value
     * @return string|void
     */
    public static function writePdfString(SetaPDF_Core_WriteInterface $writer, $value)
    {
        $writer->write($value !== null ? ' ' . $value : ' null');
    }

    /**
     * Ensures that the passed value is a SetaPDF_Core_Type_Token instance.
     *
     * @param mixed $token
     * @return self
     * @throws SetaPDF_Core_Type_Exception
     */
    public static function ensureType($token)
    {
        return self::_ensureType(self::class, $token, 'Token value expected.');
    }

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * The constructor.
     * 
     * @param string $value
     */
    public function __construct($value = null)
    {
        unset($this->_observed);
        
        if (null !== $value) {
            $this->_value = (string)$value;
        }
    }
    
    /**
     * Set the token value.
     * 
     * @param string $value
     * @see SetaPDF_Core_Type_AbstractType::setValue()
     */
    public function setValue($value)
    {
        if (null !== $value) {
            $value = (string)$value;
        }
            
        if ($this->_value === $value) {
            return;
        }
            
        $this->_value = $value;
        
        if (isset($this->_observed)) {
            $this->notify();
        }
    }
    
    /**
     * Get the token value.
     * 
     * @return string
     * @see SetaPDF_Core_Type_AbstractType::getValue()
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * Returns the type as a formatted PDF string.
     *
     * @param SetaPDF_Core_Document|null $pdfDocument
     * @return string
     */
    public function toPdfString(SetaPDF_Core_Document $pdfDocument = null)
    {
        return $this->_value !== null ? ' ' . $this->_value : ' null';
    }

    /**
     * Writes the type as a formatted PDF string to the document.
     *
     * @param SetaPDF_Core_Document $pdfDocument
     */
    public function writeTo(SetaPDF_Core_Document $pdfDocument)
    {
        $pdfDocument->write($this->toPdfString($pdfDocument));
    }

    /**
     * Converts the PDF data type to a PHP data type and returns it.
     *
     * @return string
     */
    public function toPhp()
    {
        return $this->_value;
    }
}