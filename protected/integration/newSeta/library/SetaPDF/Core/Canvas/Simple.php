<?php
/**
 * This file is part of the SetaPDF-Core Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Simple.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * A class representing minimum functions to access a Canvas.
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_Core
 * @subpackage Canvas
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_Core_Canvas_Simple
{
    /**
     * The main dictionary of the canvas
     *
     * @var SetaPDF_Core_Canvas_ContainerInterface
     */
    protected $_canvasContainer;

    /**
     * The constructor.
     *
     * @param SetaPDF_Core_Canvas_ContainerInterface $canvasContainer
     */
    public function __construct(SetaPDF_Core_Canvas_ContainerInterface $canvasContainer)
    {
        $this->_canvasContainer = $canvasContainer;
    }

    /**
     * Release memory and cycled references.
     */
    public function cleanUp()
    {
        $this->_canvasContainer = null;
    }

    /**
     * Get the whole byte stream of the canvas.
     *
     * @see SetaPDF_Core_Canvas_StreamProxyInterface::getStream()
     * @return string
     */
    public function getStream()
    {
        return $this->_canvasContainer->getStreamProxy()->getStream();
    }

    /**
     * Get the container of the canvas (origin object).
     *
     * @return SetaPDF_Core_Canvas_ContainerInterface
     */
    public function getContainer()
    {
        return $this->_canvasContainer;
    }

    /**
     * Returns the resources dictionary or an entry of it.
     *
     * If no resource dictionary exists it is possible to automatically
     * create it and/or the desired entry.
     *
     * @param boolean $inherited Check for a resources dictionary in parent nodes
     * @param boolean $create Create dictionary/ies if they do not exists
     * @param string $entryKey The entries key (Font, XObject,...)
     * @return false|SetaPDF_Core_Type_Dictionary|SetaPDF_Core_Type_Array Returns the resources dictionary, a dictionary by an entry key or false if none was found.
     * @throws SetaPDF_Core_Type_Exception
     */
    public function getResources($inherited = true, $create = false, $entryKey = null)
    {
        $mainDict = $this->_canvasContainer->getObject(true)->ensure(true);
        if ($mainDict instanceof SetaPDF_Core_Type_Stream) {
            $mainDict = $mainDict->getValue();
        }

        $dict = SetaPDF_Core_Type_Dictionary::ensureType($mainDict);
        $resources = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Resources');

        while ($inherited && $resources === null) {
            $parent = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Parent');
            if ($parent === null) {
                break;
            }

            $dict = SetaPDF_Core_Type_Dictionary::ensureType($parent);
            $resources = SetaPDF_Core_Type_Dictionary_Helper::getValue($dict, 'Resources');
        }

        if (!$resources instanceof SetaPDF_Core_Type_Dictionary) {
            if ($create === false) {
                return false;
            }

            $mainDict->offsetSet('Resources', $resources = new SetaPDF_Core_Type_Dictionary([
                new SetaPDF_Core_Type_Dictionary_Entry(
                    new SetaPDF_Core_Type_Name(SetaPDF_Core_Resource::TYPE_PROC_SET, true),
                    new SetaPDF_Core_Type_Array([
                        new SetaPDF_Core_Type_Name('PDF', true),
                        new SetaPDF_Core_Type_Name('Text', true),
                        new SetaPDF_Core_Type_Name('ImageB', true),
                        new SetaPDF_Core_Type_Name('ImageC', true),
                        new SetaPDF_Core_Type_Name('ImageI', true)
                    ])
                )
            ]));
        }

        // Get all resources
        if ($entryKey === null) {
            return $resources;
        }

        $value = SetaPDF_Core_Type_Dictionary_Helper::getValue($resources, $entryKey);

        if ($value === null) {
            if ($create === false) {
                return false;
            }

            if ($entryKey === 'ProcSet') {
                $resources->offsetSet($entryKey, $value = new SetaPDF_Core_Type_Array());
            } else {
                $resources->offsetSet($entryKey, $value = new SetaPDF_Core_Type_Dictionary());
            }
        }

        if ($entryKey === 'ProcSet') {
            return SetaPDF_Core_Type_Array::ensureType($value);
        }

        return SetaPDF_Core_Type_Dictionary::ensureType($value);
    }
}
