<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: ViewerPreferences.php 1745 2022-06-21 05:52:15Z jan.slabon $
 */

/**
 * Class representing the access to the ViewerPreferences dictionary of a document
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Document
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Document_Catalog_ViewerPreferences
{
    /**
     * Constant value specifying how to display the document on exiting full-screen mode.
     *
     * Neither document outline nor thumbnail images visible.
     *
     * @var string
     */
    const NON_FULL_SCREEN_PAGE_MODE_USE_NONE = 'UseNone';

    /**
     * Constant value specifying how to display the document on exiting full-screen mode.
     *
     * Document outline visible.
     *
     * @var string
     */
    const NON_FULL_SCREEN_PAGE_MODE_USE_OUTLINES = 'UseOutlines';

    /**
     * Constant value specifying how to display the document on exiting full-screen mode.
     *
     * Thumbnail images visible.
     *
     * @var string
     */
    const NON_FULL_SCREEN_PAGE_MODE_USE_THUMBS = 'UseThumbs';

    /**
     * Constant value specifying how to display the document on exiting full-screen mode.
     *
     * Optional content group panel visible.
     *
     * @var string
     */
    const NON_FULL_SCREEN_PAGE_MODE_USE_OC = 'UseOC';

    /**
     * Constant value for predominant reading order for text.
     *
     * Left to right.
     *
     * @var string
     */
    const DIRECTION_L2R = 'L2R';

    /**
     * Constant value for predominant reading order for text.
     *
     * Right to left.
     *
     * @var string
     */
    const DIRECTION_R2L = 'R2L';

    /**
     * Constant value of the the page scaling option that shall be selected when a print dialog is displayed for this document.
     *
     * No page scaling.
     *
     * @var string
     */
    const PRINT_SCALING_NONE = 'None';

    /**
     * Constant value of the the page scaling option that shall be selected when a print dialog is displayed for this document.
     *
     * Reader’s default print scaling.
     *
     * @var string
     */
    const PRINT_SCALING_APP_DEFAULT = 'AppDefault';

    /**
     * Constant value of the paper handling option that shall be used when printing the file from the print dialog.
     *
     * Print single-sided.
     *
     * @var string
     */
    const DUPLEX_SIMPLEX = 'Simplex';

    /**
     * Constant value of the paper handling option that shall be used when printing the file from the print dialog.
     *
     * Duplex and flip on the short edge of the sheet.
     *
     * @var string
     */
    const DUPLEX_FLIP_SHORT_EDGE = 'DuplexFlipShortEdge';

    /**
     * Constant value of the paper handling option that shall be used when printing the file from the print dialog.
     *
     * Duplex and flip on the long edge of the sheet.
     *
     * @var string
     */
    const DUPLEX_FLIP_LONG_EDGE = 'DuplexFlipLongEdge';

    /**
     * The catalog instance
     *
     * @var SetaPDF_Core_Document_Catalog
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
     * Get the document instance.
     *
     * @return SetaPDF_Core_Document
     */
    public function getDocument()
    {
        return $this->_catalog->getDocument();
    }

    /**
     * Release memory and cycled references.
     */
    public function cleanUp()
    {
        $this->_catalog = null;
    }

    /**
     * Set the flag specifying whether to hide the conforming reader’s tool bars when the document is active.
     *
     * @param boolean $value A boolean value defining whether to hide the tool bars or not.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setHideToolbar($value = true)
    {
        $this->_setBooleanValue('HideToolbar', $value);
    }

    /**
     * Get the flag specifying whether to hide the conforming reader’s tool bars when the document is active.
     *
     * @return boolean
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getHideToolbar()
    {
        return $this->_getValue('HideToolbar');
    }

    /**
     * Set the flag specifying whether to hide the conforming reader’s menu bar when the document is active.
     *
     * Does not affect the display through a browser plugin.
     *
     * @param boolean $value A boolean value defining whether to hide the menu bar or not.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setHideMenubar($value = true)
    {
        $this->_setBooleanValue('HideMenubar', $value);
    }

    /**
     * Get the flag specifying whether to hide the conforming reader’s menu bar when the document is active.
     *
     * @return boolean
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getHideMenubar()
    {
        return $this->_getValue('HideMenubar');
    }

    /**
     * Set flag specifying whether to hide user interface elements in the document’s window
     * (such as scroll bars and navigation controls), leaving only the document’s contents displayed.
     *
     * @param boolean $value A boolean value defining whether to hide user interface elements in the document's windows.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setHideWindowUI($value = true)
    {
        $this->_setBooleanValue('HideWindowUI', $value);
    }

    /**
     * Get flag specifying whether to hide user interface elements in the document’s window
     * (such as scroll bars and navigation controls), leaving only the document’s contents displayed.
     *
     * @return boolean
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getHideWindowUI()
    {
        return $this->_getValue('HideWindowUI');
    }

    /**
     * Set the flag specifying whether to resize the document’s window to fit the size of the first displayed page.
     *
     * @param boolean $value A boolean value defining whether to resize the document’s window.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setFitWindow($value = true)
    {
        $this->_setBooleanValue('FitWindow', $value);
    }

    /**
     * Get the flag specifying whether to resize the document’s window to fit the size of the first displayed page.
     *
     * @return boolean
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getFitWindow()
    {
        return $this->_getValue('FitWindow');
    }

    /**
     * Set the flag specifying whether to position the document’s window in the center of the screen.
     *
     * @param boolean $value A boolean value defining whether to position the document’s window in the center.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setCenterWindow($value = true)
    {
        $this->_setBooleanValue('CenterWindow', $value);
    }

    /**
     * Get the flag specifying whether to position the document’s window in the center of the screen.
     *
     * @return boolean
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getCenterWindow()
    {
        return $this->_getValue('CenterWindow');
    }

    /**
     * Set the flag whether the title or the filename of the document should be displayed in the window’s title bar.
     *
     * @param boolean $value The value defining whether if the title of the document should be displayed in the
     *                       window’s title bar (true) or the filename (false).
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setDisplayDocTitle($value = true)
    {
        $this->_setBooleanValue('DisplayDocTitle', $value);
        if ($value) {
            $this->getDocument()->setMinPdfVersion('1.4');
        }
    }

    /**
     * Get the flag whether the title or the filename of the document should be displayed in the window’s title bar.
     *
     * @return boolean
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getDisplayDocTitle()
    {
        return $this->_getValue('DisplayDocTitle');
    }

    /**
     * Set the document's page mode, specifying how to display the document on exiting full-screen mode.
     *
     * @param string|false $name A constant value of
     *                     {@link SetaPDF_Core_Document_Catalog_ViewerPreferences::NON_FULL_SCREEN_PAGE_MODE_XXX} or
     *                     false to remove the entry.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setNonFullScreenPageMode($name = self::NON_FULL_SCREEN_PAGE_MODE_USE_NONE)
    {
        if (!$name) {
            $this->_removeKey('NonFullScreenPageMode');
            return;
        }

        // TODO: Check for allowed values
        $this->_setNameValue('NonFullScreenPageMode', $name);
    }

    /**
     * Get the document's page mode, specifying how to display the document on exiting full-screen mode.
     *
     * @return string
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getNonFullScreenPageMode()
    {
        return $this->_getValue('NonFullScreenPageMode', self::NON_FULL_SCREEN_PAGE_MODE_USE_NONE);
    }

    /**
     * Set the predominant reading order for text.
     *
     * @param string|false $name A constant value of {@link SetaPDF_Core_Document_Catalog_ViewerPreferences::DIRECTION_XXX}
     *                           or false to remove this entry.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setDirection($name)
    {
        if (!$name) {
            $this->_removeKey('Direction');
            return;
        }

        // TODO: Check for allowed values
        $this->_setNameValue('Direction', $name);
        $this->getDocument()->setMinPdfVersion('1.3');
    }

    /**
     * Get the predominant reading order for text.
     *
     * @return string
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getDirection()
    {
        return $this->_getValue('Direction', self::DIRECTION_L2R);
    }

    /**
     * Set the page boundary representing the area of a page that shall be displayed when
     * viewing the document on the screen.
     *
     * @param string|false $boundaryName A boundary name as defined as a constant in {@link SetaPDF_Core_PageBoundaries}
     *                                   or false to remove this entry.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setViewArea($boundaryName)
    {
        if (!$boundaryName) {
            $this->_removeKey('ViewArea');
            return;
        }

        if (!SetaPDF_Core_PageBoundaries::isValidName($boundaryName)) {
            throw new InvalidArgumentException(sprintf("'%s' is an invalid page boundary.", $boundaryName));
        }
        $this->_setNameValue('ViewArea', $boundaryName);
        $this->getDocument()->setMinPdfVersion('1.4');
    }

    /**
     * Get the page boundary representing the area of a page that shall be displayed when viewing the document on the screen.
     *
     * @return string
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getViewArea()
    {
        return $this->_getValue('ViewArea', SetaPDF_Core_PageBoundaries::CROP_BOX);
    }

    /**
     * Set the name of the page boundary to which the contents of a page shall be clipped when
     * viewing the document on the screen.
     *
     * @param string $boundaryName A boundary name as defined as a constant in {@link SetaPDF_Core_PageBoundaries}.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setViewClip($boundaryName)
    {
        if (!$boundaryName) {
            $this->_removeKey('ViewClip');
            return;
        }

        if (!SetaPDF_Core_PageBoundaries::isValidName($boundaryName)) {
            throw new InvalidArgumentException(sprintf("'%s' is an invalid page boundary.", $boundaryName));
        }
        $this->_setNameValue('ViewClip', $boundaryName);
        $this->getDocument()->setMinPdfVersion('1.4');
    }

    /**
     * Get the name of the page boundary to which the contents of a page shall be clipped when
     * viewing the document on the screen.
     *
     * @return string
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getViewClip()
    {
        return $this->_getValue('ViewClip', SetaPDF_Core_PageBoundaries::CROP_BOX);
    }

    /**
     * Set the name of the page boundary representing the area of a page that shall be rendered
     * when printing the document.
     *
     * @param string|false $boundaryName A boundary name as defined as a constant in {@link SetaPDF_Core_PageBoundaries}
     *                                   or false to remove that entry.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPrintArea($boundaryName)
    {
        if (!$boundaryName) {
            $this->_removeKey('PrintArea');
            return;
        }

        if (!SetaPDF_Core_PageBoundaries::isValidName($boundaryName)) {
            throw new InvalidArgumentException(sprintf("'%s' is an invalid page boundary.", $boundaryName));
        }
        $this->_setNameValue('PrintArea', $boundaryName);
        $this->getDocument()->setMinPdfVersion('1.4');
    }

    /**
     * Get the name of the page boundary representing the area of a page that shall be rendered
     * when printing the document.
     *
     * @return string
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getPrintArea()
    {
        return $this->_getValue('PrintArea', SetaPDF_Core_PageBoundaries::CROP_BOX);
    }

    /**
     * Set the name of the page boundary to which the contents of a page shall be clipped
     * when printing the document.
     *
     * @param string|false $boundaryName A boundary name as defined as a constant in {@link SetaPDF_Core_PageBoundaries}
     *                                   or false to remove that entry.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPrintClip($boundaryName)
    {
        if (!$boundaryName) {
            $this->_removeKey('PrintClip');
            return;
        }

        if (!SetaPDF_Core_PageBoundaries::isValidName($boundaryName)) {
            throw new InvalidArgumentException(sprintf("'%s' is an invalid page boundary.", $boundaryName));
        }
        $this->_setNameValue('PrintClip', $boundaryName);
        $this->getDocument()->setMinPdfVersion('1.4');
    }

    /**
     * Get the name of the page boundary to which the contents of a page shall be clipped
     * when printing the document.
     *
     * @return string
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getPrintClip()
    {
        return $this->_getValue('PrintClip', SetaPDF_Core_PageBoundaries::CROP_BOX);
    }

    /**
     * Set the page scaling option that shall be selected when a print dialog is displayed for this document.
     *
     * @param string|false $name A constant value of {@link SetaPDF_Core_Document_Catalog_ViewerPreferences::PRINT_SCALING_XXX}
     *                           or false to remove that entry.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPrintScaling($name)
    {
        if (!$name) {
            $this->_removeKey('PrintScaling');
            return;
        }

        $this->_setNameValue('PrintScaling', $name);
        $this->getDocument()->setMinPdfVersion('1.6');
    }

    /**
     * Get the page scaling option that shall be selected when a print dialog is displayed for this document.
     *
     * @return string
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getPrintScaling()
    {
        return $this->_getValue('PrintScaling', self::PRINT_SCALING_APP_DEFAULT);
    }

    /**
     * Set the paper handling option that shall be used when printing the file from the print dialog.
     *
     * @param string|false $name A constant value of {@link SetaPDF_Core_Document_Catalog_ViewerPreferences::DUPLEX_XXX}
     *                           or false to remove that entry.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setDuplex($name)
    {
        if (!$name) {
            $this->_removeKey('Duplex');
            return;
        }

        // TODO: Check for allowed values
        $this->_setNameValue('Duplex', $name);
    }

    /**
     * Get the paper handling option that shall be used when printing the file from the print dialog.
     *
     * @return string|null
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getDuplex()
    {
        return $this->_getValue('Duplex', null);
    }

    /**
     * Set the flag specifying whether the PDF page size shall be used to select the input paper tray.
     *
     * @param boolean $value A boolean value
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPickTrayByPdfSize($value = true)
    {
        $this->_setBooleanValue('PickTrayByPDFSize', $value);
        $this->getDocument()->setMinPdfVersion('1.7');
    }

    /**
     * Get the flag specifying whether the PDF page size shall be used to select the input paper tray.
     *
     * @param null|boolean $defaultValue
     * @return bool|mixed
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getPickTrayByPdfSize($defaultValue = null)
    {
        return $this->_getValue('PickTrayByPDFSize', $defaultValue);
    }

    /**
     * Set the page numbers used to initialize the print dialog box when the file is printed.
     *
     * @param array|null $pageRange An array of even number of integer values to be interpreted in pairs. Each pair
     *                              represents the first and last pages in a sub-range of pages.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setPrintPageRange(array $pageRange = null)
    {
        if ($pageRange === null || count($pageRange) === 0) {
            $this->_removeKey('PrintPageRange');
            return;
        }

        $count = count($pageRange);
        $pageRange = array_map('intval', $pageRange);
        if (($count % 2) !== 0) {
            $pageRange[] = $pageRange[$count - 1];
        }

        $value = new SetaPDF_Core_Type_Array();
        foreach ($pageRange AS $pageNumber) {
            $value->offsetSet(null, new SetaPDF_Core_Type_Numeric($pageNumber - 1));
        }

        $this->_setValue('PrintPageRange', $value);
        $this->getDocument()->setMinPdfVersion('1.7');
    }

    /**
     * Get the page numbers used to initialize the print dialog box when the file is printed.
     *
     * @param array $defaultValue A default value that will be returned if no preference is defined.
     * @return array
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getPrintPageRange(array $defaultValue = [])
    {
        $value = $this->_getValue('PrintPageRange', $defaultValue, true);
        if ($value instanceof SetaPDF_Core_Type_Array) {
            $value = array_map(
                static function($v) {
                    return $v + 1;
                },
                $value->toPhp(true)
            );
        }

        return $value;
    }

    /**
     * Set the number of copies that shall be printed when the print dialog is opened for this file.
     *
     * @param integer|false $numCopies The number of copies or false to remove this entry.
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function setNumCopies($numCopies)
    {
        if (!$numCopies) {
            $this->_removeKey('NumCopies');
            return;
        }

        $this->_setValue('NumCopies', new SetaPDF_Core_Type_Numeric((int)$numCopies));
    }

    /**
     * Get the number of copies that shall be printed when the print dialog is opened.
     *
     * @param int $defaultValue A default value, to be used if no preference is defined.
     * @return bool|mixed
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getNumCopies($defaultValue = 1)
    {
        return $this->_getValue('NumCopies', $defaultValue);
    }


  /* Helper methods to get and set common types */

    /**
     * Helper method to get a value of the ViewerPreferences dictionary.
     *
     * @param string $key
     * @param mixed $default
     * @param boolean $pdfObject
     * @return mixed
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _getValue($key, $default = false, $pdfObject = false)
    {
        $catalog = $this->_catalog->getDictionary();
        if ($catalog === null) {
            return $default;
        }

        $viewerPreferences = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'ViewerPreferences');
        if (!$viewerPreferences instanceof SetaPDF_Core_Type_Dictionary) {
            return $default;
        }

        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($viewerPreferences, $key);
        if ($value === null) {
            return $default;
        }

        if ($pdfObject === false) {
            return $value->getValue();
        }

        return $value;
    }

    /**
     * Helper method for setting boolean values.
     *
     * @param string $key
     * @param boolean $value
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _setBooleanValue($key, $value)
    {
        $this->_setValue($key, new SetaPDF_Core_Type_Boolean($value));
    }

    /**
     * Helper method for setting a name value.
     *
     * @param string $key
     * @param string $name
     * @throws SetaPDF_Core_SecHandler_Exception
     * @throws SetaPDF_Core_Type_Exception
     */
    protected function _setNameValue($key, $name)
    {
        $this->_setValue($key, new SetaPDF_Core_Type_Name($name));
    }

    /**
     * Helper method for setting a value.
     *
     * @param string $key
     * @param SetaPDF_Core_Type_AbstractType $value
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    protected function _setValue($key, SetaPDF_Core_Type_AbstractType $value)
    {
        $catalog = SetaPDF_Core_Type_Dictionary::ensureType($this->_catalog->getDictionary(true));
        $viewerPreferences = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'ViewerPreferences');
        if (!$viewerPreferences instanceof SetaPDF_Core_Type_Dictionary) {
            $viewerPreferences = new SetaPDF_Core_Type_Dictionary();
            $catalog->offsetSet('ViewerPreferences', $viewerPreferences);
        }

        $viewerPreferences->offsetSet($key, $value);
    }

    /**
     * Helper method for removing a key from the ViewerPreferences dictionary.
     *
     * @param string $key
     * @throws SetaPDF_Core_Type_Exception
     * @throws SetaPDF_Core_SecHandler_Exception
     */
    protected function _removeKey($key)
    {
        $catalog = SetaPDF_Core_Type_Dictionary::ensureType($this->_catalog->getDictionary(true));
        $viewerPreferences = SetaPDF_Core_Type_Dictionary_Helper::getValue($catalog, 'ViewerPreferences');
        if (!$viewerPreferences instanceof SetaPDF_Core_Type_Dictionary) {
            return;
        }

        $viewerPreferences->offsetUnset($key);
    }
}
