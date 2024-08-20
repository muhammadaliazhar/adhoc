<?php
/***********************************************************************************
* Copyright (C) 2011-2018 X2 Engine Inc. All Rights Reserved.
*
* X2 Engine Inc.
* P.O. Box 610121
* Redwood City, California 94061 USA
* Company website: http://www.x2engine.com
*
* X2 Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
* to install and use this Software for your internal business purposes only
* for the number of users purchased by you. Your use of this Software for
* additional users is not covered by this license and requires a separate
* license purchase for such users. You shall not distribute, license, or
* sublicense the Software. Title, ownership, and all intellectual property
* rights in the Software belong exclusively to X2 Engine. You agree not to file
* any patent applications covering, relating to, or depicting this Software
* or modifications thereto, and you agree to assign any patentable inventions
* resulting from your use of this Software to X2 Engine.
*
* THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
* EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
***********************************************************************************/
Yii::app()->clientScript->registerScript('x2ConditionListViewJS'.$this->id,"
;(function () {
    var condList = new x2.RelitiveConditionList ({
        containerSelector: '#$this->id',
        name: '$this->name',
        modelClass: '".get_class ($this->model)."',
        options: ".CJSON::encode ($this->attributes).",
        
        visibilityOptions: ".CJSON::encode(array(
            array(1, Yii::t('app', 'Public')),
            array(0, Yii::t('app', 'Private')),
            array(2, Yii::t('app', 'User\'s Groups'))
        )).",
        allTags: ".CJSON::encode(Tags::getAllTags()).",
        value: ".CJSON::encode ($this->value)."
    });
    // add cond list object to element data to allow access from outside this scope
    $('#$this->id').data ('x2ConditionList', condList);
}) ();
", CClientScript::POS_END);
?>
<div id='<?php echo $this->id ?>'>
    <div class="x2-cond-list"><ol></ol></div>
    <div class='x2fields-template' style='display: none;'>
        <ol>
            <li>
                <div class="handle"></div>
                <fieldset></fieldset>
                <a href="javascript:void(0)" class="del"></a>
            </li>
        </ol>
        <div class="cell x2fields-attribute">
            <!--<label><?php echo Yii::t('studio', 'Attribute'); ?></label>-->
            <select disabled='disabled' 
             name="<?php echo $this->name . '[i][name]'; ?>"></select>
        </div>
        <div class="cell x2fields-operator" style="display: none">
            <!--<label><?php echo Yii::t('studio', 'Comparison'); ?></label>-->
            <select disabled='disabled' 
             name="<?php echo $this->name . '[i][operator]'; ?>"></select>
        </div>
        <div class="cell x2fields-value">
            <!--<label><?php echo Yii::t('studio', 'Value'); ?></label>-->
            <input disabled='disabled' type="text" 
             name="<?php echo $this->name . '[i][value]'; ?>" />
        </div>
    </div>
    <button class='add-condition-button x2-button x2-small-button'><?php 
        echo Yii::t('app', 'Add Condition'); ?></button>
</div>
