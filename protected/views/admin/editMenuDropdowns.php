<?php
/***********************************************************************************
* Copyright (C) 2011-2019 X2 Engine Inc. All Rights Reserved.
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




Yii::app()->clientScript->registerPackage ('multiselect');
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl ().'/js/ManageMenuItemsMultiselect.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/views/admin/manageModules.css');
Yii::app()->clientScript->registerScript('renderMultiSelect',"
$(document).ready(function() {
	 $('#module-multiselect').manageMenuItemsMultiselect ({
        searchable: false,
        deletableOptions: ".CJSON::encode ($deletableOptions).",
        translations: ".CJSON::encode (array (
            'deleteModule' => Yii::t('app', 'Delete top bar link'),
            'message' => Yii::t('app', 'Are you sure you want to delete this top bar link?'),
            'title' => Yii::t('app', 'Delete top bar link?'),
            'confirm' => Yii::t('app', 'Delete'),
        ))."
    });
});
",CClientScript::POS_HEAD);
?>

<div class="page-title"><h2><?php 
    echo CHtml::encode (Yii::t('admin','Create Drop Down for top bar')); 
?></h2></div>
<?php 
$form = $this->beginWidget ('CActiveForm', array (
	'id'=>'manage-modules',
	'enableAjaxValidation'=>false,
)); 
X2Html::getFlashes ();
?>
<div class="form" id='manage-menu-items-form-outer'>
<?php 
echo CHtml::encode (Yii::t('admin','Re-order, add, or remove top bar module links:')); 
?>
<br><br>
<?php
echo CHtml::textField ('Menu Label','',array(
				));
echo CHtml::hiddenField('formSubmit','1');
echo CHtml::dropDownList(
    'menuItems[]',
    $selectedItems,
    $menuItems,array(
        'class'=>'x2-multiselect',
        'id'=>'module-multiselect',
        'multiple'=>'multiple',
        'data-skip-auto-init'=>'1',
        'size'=>8
    )
);
?>
<br>
<div class="row buttons">
	<?php 
    echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button')); 
    ?>
</div>
</div>
<?php 
$this->endWidget(); 
?>
