<?php 
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Iterator.php 1407 2020-01-28 08:56:29Z jan.slabon $
 */

/**
 * Optional content iterator
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_OptionalContent_Iterator extends RecursiveIteratorIterator
{
    /**
     * Return the current value as an SetaPDF_Core_Document_OptionalContent_Group object if possible.
     * 
     * @return SetaPDF_Core_Type_AbstractType|SetaPDF_Core_Document_OptionalContent_Group
     * @see RecursiveIteratorIterator::current()
     */
    public function current()
    {
        $current = parent::current();
        
        if ($current->ensure() instanceof SetaPDF_Core_Type_Dictionary) {
            return new SetaPDF_Core_Document_OptionalContent_Group($current);
        }
        
        return $current;
    }
}