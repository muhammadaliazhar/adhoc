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
Yii::app()->clientScript->registerCss('convertRecordsCSS',"
#fields-grid {
    border: 1px solid rgb(200, 200, 200);
    margin: 0 12px;
    border-radius: 4px;
}
#fields-grid-page-title {
    margin-bottom: -5px;
    border-bottom: none !important;
    border-radius: 4px 4px 0 0;
    -moz-border-radius: 4px 4px 0 0;
    -webkit-border-radius: 4px 4px 0 0;
    -o-border-radius: 4px 4px 0 0;
}
#fields-form {
    padding-left: 0;
    padding-right: 0;
}
#remove-field-button {
    margin-left: 9px;
}
#createUpdateField-loading {
    display: none;
    position: absolute;
    opacity: 0.5;
    z-index: 100;
    background: white url(".Yii::app()->theme->baseUrl."/images/loading.gif) no-repeat center scroll;
}
.field-option.field-modified {
    background-color: #B3CFF5;
}
.field-option.field-custom {
    background-color: #C4F59D; 
}
");
Yii::app()->clientScript->registerMain();
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/recordEditor.js', CClientScript::POS_HEAD);
$loadUrl = Yii::app()->createUrl('/admin/createUpdateRecord');
Yii::app()->clientScript->registerScript('recordEditor-config', 'x2.recordEditor.loadUrl = '.json_encode($loadUrl).';', CClientScript::POS_READY);
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Manage Fields'); ?></h2></div>
<div class="form">
    <?php echo Yii::t('admin', 'This page has a list of all fields that have been modified, and allows you to add or remove your own fields, as well as customizing the pre-set fields.'); ?>
</div>
<div class="form" id="fields-form">
    <?php
    $this->widget('X2GridViewGeneric', array(
        'id' => 'fields-grid',
        'title'=>Yii::t('accounts','Modified Fields'),
        'buttons'=>array('clearFilters','columnSelector','autoResize'),
        'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
        'template' => '<div class="page-title">{title}{summary}</div>{items}{pager}',
        'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
        'filter' => null,
        'dataProvider' => $dataProvider,
        'gvSettingsName' => 'manageFields',
        'defaultGvSettings' => array (
            'modelName' => 100,
            'fieldName' => 100,
            'attributeLabel' => 100,
            'required' => 60,
            'type' => 80,
            'uniqueConstraint' => 50,
            'defaultValue' => 90,
        ),
        'columns' => array(
            array (
                'name' => 'modelFrom',
                'type' => 'raw',
            ),
            array (
                'name' => 'modelTo',
                'type' => 'raw',
            ),
            array (
                'name' => 'fieldFrom',
                'type' => 'raw',
            ),
            array (
                'name' => 'fieldTo',
                'type' => 'raw',
            ),
        ),
    ));
    ?>

    <br>
    <a href="javascript:void(0);" onclick="$('#createUpdateField').show();x2.recordEditor.load('create')" class="x2-button" id="remove-field-button"><?php echo Yii::t('admin','Create Mapping');?></a>
    <a href="javascript:void(0);" onclick="$('#createUpdateField').show();x2.recordEditor.load('remove')" class="x2-button"><?php echo Yii::t('admin','Remove Mapping');?></a>
    <br>
    <br>
    <div id="createUpdateField-loading"></div>
    <div id="createUpdateField" style="display:none">
    </div>


</div>
