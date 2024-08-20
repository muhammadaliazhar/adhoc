<?php
/**
 * This file is part of the SetaPDF-FormFiller Component
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @subpackage Field
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Button.php 1706 2022-03-28 10:40:28Z jan.slabon $
 */

/**
 * A checkbox button
 * 
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @subpackage Field
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_FormFiller_Field_Button
extends SetaPDF_FormFiller_Field_AbstractField
implements SetaPDF_FormFiller_Field_FieldInterface
{
    /**
     * The key/name for the "On"-state of the button
     * 
     * @var string
     */
    protected $_onStateName = null;
    
    /**
     * The button group object to which this button is attached to (if so)
     * 
     * @var SetaPDF_FormFiller_Field_ButtonGroup
     */
    protected $_buttonGroup = null;
    
    /**
     * Set the button group to which this button is attached to.
     * 
     * @param SetaPDF_FormFiller_Field_ButtonGroup $buttonGroup
     * @return void
     * @see SetaPDF_FormFiller_Field_ButtonGroup::addButton()
     */
    public function setButtonGroup(SetaPDF_FormFiller_Field_ButtonGroup $buttonGroup)
    {
        $this->_buttonGroup = $buttonGroup;
    }
    
    /**
     * Gets whether the button is checked or not.
     * 
     * @return boolean
     */
    public function isChecked()
    {
        $as = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'AS');
        if ($as) {
            return $as->getValue() == $this->getOnStateName();
        }
        
        /* 
         * This will only happens in one special situation:
         * It's allowed to leave the Off state from the AP dictionary (it's optional)
         * So only in that case the AS entry could be left or set to NULL
         */
        return false;
    }
    
    /**
     * Checks the button.
     * 
     * @return void
     */
    public function check()
    {
        $this->_checkPermission();

        if (!$this->_hasAppearance()) {
            $this->recreateAppearance();
        }

        if (null !== $this->_buttonGroup && false == $this->_buttonGroup->isSetValueActive()) {
            $this->_buttonGroup->setValue($this);
            return;
        }
        
        if ($this->isChecked()) {
            return;
        }

        $this->_fields->forwardValueToRelated(true, $this);

        $value = $this->getOnStateName();
        $this->_fieldDictionary->offsetSet('AS', new SetaPDF_Core_Type_Name($value));

        /* Resolve dictionary with T entry, because V values in terminal fields are ignored
         * if a parent element with an V entry is available
        */
        $dict = SetaPDF_Core_Type_Dictionary_Helper::resolveDictionaryByAttribute($this->_fieldDictionary, 'T');
        // TODO: check if it will be faster, to update an existing V entry
        $dict->offsetSet('V', new SetaPDF_Core_Type_Name($value));

        // clean up malformed structures
        if ($dict !== $this->_fieldDictionary) {
            $this->_fieldDictionary->offsetUnset('V');
        }

        if (!$this->_hasAppearance()) {
            $this->recreateAppearance();
        }
    }
    
    /**
     * Alias for check().
     * 
     * @return void
     * @see SetaPDF_FormFiller_Field_Button::check()
     */
    public function push()
    {
        $this->check();
    }

    /**
     * Uncheck the button.
     * 
     * @return void
     */
    public function uncheck()
    {
        $this->_checkPermission();

        if (!$this->_hasAppearance()) {
            $this->recreateAppearance();
        }

        if (null !== $this->_buttonGroup && false == $this->_buttonGroup->isSetValueActive()) {
            // TODO: Handling of "NoToggleToOff"-flag (bit 15). Not possible to simulate with Acrobat
            $this->_buttonGroup->setValue(null);
            return;
        }
        
        if (!$this->isChecked()) {
            return;
        }

        $this->_fields->forwardValueToRelated(false, $this);

        $this->_fieldDictionary->offsetSet('AS', new SetaPDF_Core_Type_Name('Off', true));
        
        // Resolve dictionary with FT entry
        $dict = SetaPDF_Core_Type_Dictionary_Helper::resolveDictionaryByAttribute($this->_fieldDictionary, 'T');
        // Remove the V-entry if button is not active
        if ($dict->offsetExists('V')) {
            $dict->offsetUnset('V');
        }

        // clean up malformed structures
        if ($dict !== $this->_fieldDictionary) {
            $this->_fieldDictionary->offsetUnset('V');
        }

        if (!$this->_hasAppearance()) {
            $this->recreateAppearance();
        }
    }
    
    /**
     * Alias for uncheck().
     * 
     * @return void
     * @see SetaPDF_FormFiller_Field_Button::uncheck()
     */
    public function pull()
    {
        $this->uncheck();
    }
    
    /**
     * Gets whether the button is checked or not.
     * 
     * @return boolean
     * @see SetaPDF_FormFiller_Field_Button::isChecked()
     */
    public function getValue()
    {
        return $this->isChecked();
    }
    
    /**
     * Check or un-check the button.
     *
     * @return void
     *
     * @param boolean|string $value The value can be a boolean value or a value that will be compared to the
     *                              {@link getExportValue() export value} to evaluate to a boolean whether to check
     *                              or un-check the check box.
     * @param string $encoding The input encoding if $value is a string
     */
    public function setValue($value, $encoding = 'UTF-8')
    {
        if (!is_bool($value)) {
            $tmpValue = $value;
            $value = $value == $this->getExportValue($encoding);

            /* If true and same named buttons are available this will be handled by
             * the SetaPDF_FormFiller_Fields::forwardSetValueToRelated() method -
             * otherwise we search for the right value here.
             */
            if ($value === false) {
                // check if button has same named buttons
                $relatedButtons = $this->_fields->getRelatedFields($this);
                foreach ($relatedButtons AS $relatedButton) {
                    if ($tmpValue == $relatedButton->getExportValue($encoding)) {
                        $relatedButton->setValue(true);
                        return;
                    }
                }

                $relatedButtons = $this->_fields->getRelatedFields($this, false);
                foreach ($relatedButtons AS $relatedButton) {
                    $relatedButton->setValue(false);
                }
                return;
            }
        }

        if (true === $value) {
            $this->check();
        } else {
            $this->uncheck();
        }
    }
    
    /**
     * Recreate or creates the Appearance of the form field.
     * 
     * @return void
     * @internal
     */
    public function recreateAppearance()
    {
        $appearanceObject = $this->getNormalAppearanceObject();
        $xObject = SetaPDF_Core_XObject::get($appearanceObject, 'Form');
        $canvas = new SetaPDF_Core_Canvas($xObject);

        if ($this->isFieldFlagSet(SetaPDF_FormFiller_Field_Flags::RADIO)) {
            $appearanceGlyph = chr(108);
        } else {
            $appearanceGlyph = chr(52); // Check
        }

        $annotation = $this->getAnnotation();
        $appearanceCharacteristics = $annotation->getAppearanceCharacteristics();
        if ($appearanceCharacteristics) {
            $appearanceGlyph = SetaPDF_Core_Type_Dictionary_Helper::getValue(
                $appearanceCharacteristics->getDictionary(),
                'CA',
                $appearanceGlyph,
                true
            );
        }

        $isCircle = ($this->isFieldFlagSet(SetaPDF_FormFiller_Field_Flags::RADIO) && $appearanceGlyph === chr(108));
        $this->_recreateAppearance($isCircle, $canvas);

        if ($this->isChecked()) {
            list($borderWidth, $borderStyle) = $this->_getBorderWidthAndStyle();
            $borderDoubled = (
                $isCircle ||
                $borderStyle === SetaPDF_Core_Document_Page_Annotation_BorderStyle::BEVELED ||
                $borderStyle === SetaPDF_Core_Document_Page_Annotation_BorderStyle::INSET
            );

            $clipOffset = max(1, $borderWidth * ($borderDoubled ? 2 : 1));
            $offset = max(1, $borderWidth) * ($borderDoubled ? 8 : 4);

            $width = $annotation->getWidth();
            $height = $annotation->getHeight();

            // if there's no DA entry it will fallback to the global DA which will not use ZaDb as its font
            $da = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'DA');
            if ($da === null) {
                $this->_fieldDictionary->offsetSet(
                    'DA',
                    new SetaPDF_Core_Type_String('/ZaDb 0 Tf 0 g')
                );
            }

            $font = $this->getAppearanceFont();
            $fontSize = $this->getAppearanceFontSize();

            if ($fontSize === 0 || $isCircle) {
                $fontSize = $xObject->getHeight() - $offset;
                $fontSize *= 1.038; // fuzzy value
            }

            // IDEA/TODO: Draw cross manually as Acrobat does
            //if ($appearanceGlyph === chr(56)) {
                // a cross does not use a glyph but the cross is drawn
            //}

            $_glyphWidth = $font->getGlyphWidth($appearanceGlyph, SetaPDF_Core_Encoding::ZAPF_DINGBATS) / 1000;
            $glyphWidth = ($fontSize * $_glyphWidth);
            if ($glyphWidth > ($width - $offset)) {
                $f = ($width - $offset) / $glyphWidth;
                $fontSize = $f * $fontSize;
                $glyphWidth = ($fontSize * $_glyphWidth);
            }

            // let's keep a min size of 4pt
            if ($fontSize < 4) {
                $fontSize = 4;
                $glyphWidth = ($fontSize * $_glyphWidth);
            }

            $glyphHeight = ($fontSize * $font->getFontBBox()[3] / 1000);

            // The first part is sime fuzzy logic which ends in an almost good result
            $y = (($height - 1) / $height) + ($height / 2 - $glyphHeight / 2);
            $x = $width / 2 - $glyphWidth / 2;

            $canvas->saveGraphicState();
            // Clip
            $canvas->path()->rect(
                $clipOffset,
                $clipOffset,
                $width - $clipOffset * 2,
                $height - $clipOffset * 2
            )->clip()->endPath();

            $colorSpace = $this->getAppearanceTextColorSpace();
            if ($colorSpace !== null) {
                $canvas->setNonStrokingColorSpace($colorSpace);
            }

            $this->getAppearanceTextColor()->draw($canvas, false);

            $text = $canvas->text()
                ->begin()
                ->moveToNextLine($x, $y)
                ->setFont($font, $fontSize)
                ->showText($appearanceGlyph);
            $text->end();

            $canvas->restoreGraphicState();
        }
    }
    
    /**
     * Get the appearance reference of the current state.
     * 
     * @return SetaPDF_Core_Type_IndirectObject|false
     * @see SetaPDF_FormFiller_Field_AbstractField::_getAppearanceReference()
     */
    protected function _getAppearanceReference()
    {
        $ap = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'AP');
        if ($ap === null || !SetaPDF_Core_Type_Dictionary_Helper::getValue($ap, 'N')) {
            if (!$this->isChecked()) {
                return false;
            }

            $this->recreateAppearance();
            $ap = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'AP');
        }

        // get the N entry
        $n = $ap->offsetGet('N')->ensure();
        $stateName = $this->isChecked() ? $this->getOnStateName() : 'Off';

        if (!$n->offsetExists($stateName)) {
            return false;
        }
        
        return $n->offsetGet($stateName)->getValue();
    }
    
    /**
     * Get the default value (DV entry or value in the Opt array) of the field.
     *
     * @param string $encoding
     * @return mixed
     * @see SetaPDF_FormFiller_Field_AbstractField::getDefaultValue()
     */
    public function getDefaultValue($encoding = 'UTF-8')
    {
        $dv = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'DV');
        if (!$dv) {
            return null;
        } 
        
        $defaultValue = $dv->getValue();
        
        $opt = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'Opt');
        if ($opt !== null) {
            $value = $opt->offsetGet((int)$defaultValue)->getValue();
            return SetaPDF_Core_Encoding::convertPdfString($value, $encoding);
        }
        
        return SetaPDF_Core_Encoding::convertPdfString($defaultValue, $encoding);
    }

    /**
     * Set the default value of the field.
     *
     * @param boolean|string $value
     * @param string $encoding
     */
    public function setDefaultValue($value, $encoding = 'UTF-8')
    {
        $this->_checkPermission(SetaPDF_Core_SecHandler::PERM_MODIFY);

        $tmpValue = $value;
        if (!is_bool($value)) {
            $value = $value == $this->getExportValue($encoding);

            /* If true and same named buttons are available this will be handled by
             * the SetaPDF_FormFiller_Fields::forwardValueToRelated() method -
             * otherwise we search for the right value here.
             */
            if ($value === false) {
                // check if button has same named buttons
                $relatedButtons = $this->_fields->getRelatedFields($this);
                foreach ($relatedButtons AS $relatedButton) {
                    if ($tmpValue == $relatedButton->getExportValue($encoding)) {
                        $relatedButton->setDefaultValue(true);
                        return;
                    }
                }
            }
        }

        $dict = SetaPDF_Core_Type_Dictionary_Helper::resolveDictionaryByAttribute($this->_fieldDictionary, 'T');
        if ($value === true) {
            /*
            if (null !== $this->_buttonGroup && false == $this->_buttonGroup->isSetValueActive()) {
                $this->_buttonGroup->setValue($this);
                return;
            }
            */

            $value = $this->getOnStateName();
            $dict->offsetSet('DV', new SetaPDF_Core_Type_Name($value));
        } else {
            $dict->offsetUnset('DV');
        }

        $this->_fields->forwardValueToRelated($tmpValue, $this, $encoding, 'setDefaultValue');
    }
    
    /**
     * Returns the export value of the button.
     *
     * @param string $encoding
     * @return string
     */
    public function getExportValue($encoding = 'UTF-8')
    {
        $onStateName = $this->getOnStateName();
        
        // Check for an Opt array to get the real export value
        $opt = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'Opt');
        if ($opt !== null) {
            // create a key based on the position in the kids array:
            $index = 0;
            $parent = $this->_fieldDictionary->getValue('Parent');
            if ($parent) {
                /** @var SetaPDF_Core_Type_Dictionary $parent */
                $parent = $parent->ensure();
                $kids = $parent->getValue('Kids');
                if ($kids instanceof SetaPDF_Core_Type_Array) {
                    foreach ($kids as $i => $kid) {
                        /** @var SetaPDF_Core_Type_IndirectReference $ref */
                        $ref = $kid->getValue();
                        if ($ref->getObjectIdent() === $this->_fieldObject->getObjectIdent()) {
                            $index = $i;
                            break;
                        }
                    }
                }
            }

            if ($opt->offsetExists($index)) {
                $value = $opt->offsetGet($index)->getValue();
                return SetaPDF_Core_Encoding::convertPdfString($value, $encoding);
            }
        }
        
        return SetaPDF_Core_Encoding::convertPdfString($onStateName, $encoding);
    }
    
    /**
     * This method is automatically called after the field is deleted.
     * 
     * It makes sure, that a related button group will be notified if
     * the button is deleted, so it is able to update its button collection. 
     * 
     * @return void
     * @see SetaPDF_FormFiller_Field_AbstractField::_postDelete()
     */
    protected function _postDelete()
    {
        if ($this->_buttonGroup === null) {
            parent::_postDelete();
        } else {
            $this->_buttonGroup->onFieldDeleted($this);
        }
    } 
    
    /**
     * Resolves the key/name for the "On"-state of the button.
     * 
     * @return string
     */
    public function getOnStateName()
    {
        if ($this->_onStateName === null) {
            $ap = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'AP');
            if ($ap === null || !SetaPDF_Core_Type_Dictionary_Helper::getValue($ap, 'N')) {
                return 'Yes';
            }

            $n = SetaPDF_Core_Type_Dictionary_Helper::getValue($ap, 'N');
            foreach ($n AS $key => $value) {
                $key = (string)$key;
                if ($key !== 'Off') {
                    $this->_onStateName = $key;
                    break;
                }
            }

            if ($this->_onStateName === null) {
                $this->_onStateName = 'Yes';
            }
        }

        return $this->_onStateName;
    }

    /**
     * Checks whether an default appearance for the current state exists.
     *
     * @return bool
     */
    protected function _hasAppearance()
    {
        $subname = $this->isChecked() ? $this->getOnStateName() : 'Off';

        $ap = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'AP');
        if ($ap === null) {
            return false;
        }

        $n = SetaPDF_Core_Type_Dictionary_Helper::getValue($ap, 'N');
        if ($n === null) {
            return false;
        }

        $sub = SetaPDF_Core_Type_Dictionary_Helper::getValue($n, $subname);

        return !($sub === null);
    }

    /**
     * Get or create the normal appearance object (the object referenced in the N entry).
     *
     * @param boolean $createNew Pass true to force a recreation
     * @return SetaPDF_Core_Type_IndirectObject
     */
    public function getNormalAppearanceObject($createNew = false)
    {
        $subname = $this->isChecked() ? $this->getOnStateName() : 'Off';
        $formFiller = $this->_fields->getFormFiller();
        $document = $formFiller->getDocument();

        // get or create AP entry
        $ap = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($this->_fieldDictionary, 'AP');
        if ($ap === null) {
            $ap = new SetaPDF_Core_Type_Dictionary();
            $this->_fieldDictionary->offsetSet('AP', $ap);
        }

        // get or create N entry
        $n = SetaPDF_Core_Type_Dictionary_Helper::getValue($ap, 'N');

        if ($n === null) {
            $n = new SetaPDF_Core_Type_Dictionary();
            $ap->offsetSet('N', $n);
        }

        // get or create N entry
        $sub = $n->ensure()->getValue($subname);
        if ($createNew || null === $sub || !($sub->ensure() instanceof SetaPDF_Core_Type_Stream)) {
            $subDictionary = new SetaPDF_Core_Type_Dictionary();
            $subDictionary->offsetSet('Type', new SetaPDF_Core_Type_Name('XObject', true));
            $subDictionary->offsetSet('Subtype', new SetaPDF_Core_Type_Name('Form', true));
            $subDictionary->offsetSet('FormType', new SetaPDF_Core_Type_Numeric(1));

            $annotation = $this->getAnnotation();

            // Set the BBox
            $subDictionary->offsetSet('BBox', new SetaPDF_Core_Type_Array([
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric(0),
                new SetaPDF_Core_Type_Numeric($annotation->getWidth()),
                new SetaPDF_Core_Type_Numeric($annotation->getHeight())
            ]));

            $sub = $document->createNewObject(
                new SetaPDF_Core_Type_Stream($subDictionary)
            );

            $n->ensure()->offsetSet($subname, $sub);
        }

        return $sub;
    }
}