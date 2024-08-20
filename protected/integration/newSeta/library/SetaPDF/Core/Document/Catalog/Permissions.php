<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Permissions.php 1740 2022-06-17 10:39:27Z jan.slabon $
 */

/**
 * Class representing a helper object for the Perms entry in the document catalog.
 *
 * @see PDF 32000-1:2008 - 12.8.4 Permissions
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_Permissions
{
    /**
     * The documents catalog instance
     *
     * @var SetaPDF_Core_Document
     */
    protected $_catalog;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Document_Catalog $catalog
     */
    public function __construct(SetaPDF_Core_Document_Catalog $catalog)
    {
        $this->_catalog = $catalog;
    }

    /**
     * Release cycled references.
     */
    public function cleanUp()
    {
        $this->_catalog = null;
    }

    /**
     * Checks if usage rights are defined for this document.
     *
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function hasUsageRights()
    {
        $catalog = $this->_catalog->getDictionary();
        if ($catalog === null) {
            return false;
        }

        $perms = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'Perms');
        if (!$perms instanceof SetaPDF_Core_Type_Dictionary) {
            return false;
        }

        $ur3 = SetaPDF_Core_Type_Dictionary_Helper::getValue($perms, 'UR3');
        return $ur3 !== null;
    }

    /**
     * Removes the usage rights if they are defined for this document.
     *
     * @return bool
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function removeUsageRights()
    {
        $catalog = $this->_catalog->getDictionary();
        if ($catalog === null) {
            return false;
        }

        $perms = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'Perms');
        if (!$perms instanceof SetaPDF_Core_Type_Dictionary) {
            return false;
        }

        if ($perms->offsetExists('UR3')) {
            $perms->offsetUnset('UR3');
            return true;
        }

        return false;
    }
}
