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


/**
 * @author Justin Toyomitsu <justin@x2engine.com> 
 */


include("protected/modules/x2sign/x2signConfig.php");

$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module', 'X2Sign Home')),
    array('label'=>Yii::t('module', 'X2Sign Report'), 'url'=>array('x2sign/report')),
    array('label'=>Yii::t('module', 'Create X2Sign Template'), 'url'=>array('docs/createSignable'))
));

// editor javascript files
Yii::app()->clientScript->registerPackage ('emailEditor');

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/index.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/FolderManager.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/x2sign.js');

Yii::app()->clientScript->registerScript('docsIndexJS',"
x2.folderManager = new x2.FolderManager (".CJSON::encode (array (
    'translations' => array (
        'createFolder' => Yii::t('docs', 'Create Folder'),
        'deleteFolderConf' =>
            Yii::t('docs', 'Are you sure you want to delete this folder and all of its contents?'),
        'deleteDocConf' => Yii::t('docs','Are you sure you want to delete this Doc?'),
        'folderDeleted' => Yii::t('docs', 'Folder deleted.'),
        'docDeleted' => Yii::t('docs', 'Doc deleted.'),
        'permissionsMissing' =>
            Yii::t('docs', 'You do not have permission to delete that Doc or folder.'),
    ),
    'urls' => array (
        'moveFolder' => Yii::app()->controller->createUrl('/x2sign/moveFolder'),
        'index' => Yii::app()->controller->createUrl('/x2sign/index'),
    ),
)).");
", CClientScript::POS_END);

Yii::app()->clientScript->registerCss('X2SignCreateModal',"

#x2sign-send {
    display: display:inline-block;
    background: #007FCF;
    color: white;
}

");

$columns = array (
    array (
        'name' => 'icon',
        'width' => '30px',
        'header' => '',
        'type' => 'raw',
        'value' => '$data->getColorIcon();',
    ),
    array (
        'name' => 'name',
        'header' => Yii::t('docs', 'Name'),
        'type' => 'raw',
        'value' => 'X2SignEnvelopes::renderName($data);',
        'width' => '30%',
        'htmlOptions' => array (
            'id' => 'php:$data->id . "-file-system-object"',
            'data-type' => 'php:$data->type',
            'data-id' => 'php:$data->objId',
            'class' => 'php:"view file-system-object".'.
                "(\$data->type=='folder'?' file-system-object-folder':' file-system-object-doc')",
        )

    ),
    array (
        'name' => 'lastUpdated',
        'header' => Yii::t('docs', 'Last Updated'),
        'type' => 'raw',
        'value' => '$data->getLastUpdateInfo ();',
        'width' => '25%',
        'htmlOptions' => array (
            'class' => 'file-system-object-last-updated',
        ),
    ),
    array (
        'name' => 'visibility',
        'header' => Yii::t('docs', 'Visibility'),
        'type' => 'raw',
        'value' => '$data->getVisibility ();',
        'width' => '20%',
        'htmlOptions' => array (
            'class' => 'file-system-object-visibility',
        ),  
    ),  
    array (
        'name' => 'itemNum',
        'header' => Yii::t('docs', 'Count'),
        'type' => 'raw',
        'value' => '$data->getItemNum()',
        'width' => '20%',
        'htmlOptions' => array (
            'class' => 'file-system-object-owner',
        ),
    ),
); 

$listView = $this->widget('X2GridViewGeneric', array(
    'dataProvider' => $dataProvider,
    //'itemView' => '_viewFileSystemObject',
    'id' => 'folder-contents',
    //'htmlOptions' => array('class'=>'x2-list-view list-view'),
    'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/listview',
    'columns' => $columns,
    'template' => '<div class="page-title rounded-top icon docs"><h2>' . Yii::t('apps', 'X2Sign Envelopes') . '</h2>' .
                  '<button id="x2sign-send" onclick="openX2SignDialog()">' . X2Html::fa('fa-paper-plane')  . ' Send Document</button>' .
                  '{summary}' . '</div>{items}{pager}',
    'afterGridViewUpdateJSString' => 'x2.folderManager.setUpDragAndDrop ();',
    'dataColumnClass' => 'X2DataColumnGeneric',
    'rowHtmlOptionsExpression' => 'array (
        "class" => ($data->validDraggable() ? " draggable-file-system-object" : "").
                   ($data->validDroppable() ? " droppable-file-system-object" : ""),
    )',
    'enableColDragging' => false,
    'enableGridResizing' => false,
    'rememberColumnSort' => false,
));
?>
