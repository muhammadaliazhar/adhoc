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

Yii::import ('application.components.sortableWidget.components.InlineX2SignDetailGridView');

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/inlineX2SignDetailWidget.css'
);

$x2signDataProvider = $this->getDataProvider ();

$columns = array(
    array(
        'name' => 'name',
        'header' => Yii::t("contacts", 'Name'),
        'value' => 'X2SignEnvelopes::getNameLink($data)',
        'type' => 'raw',
    ),
    array(
        'name' => 'assignedTo',
        'header' => Yii::t("contacts", 'Assigned To'),
        'value' => '$data->renderAttribute("assignedTo")',
        'type' => 'raw',
    ),
    array(
        'name' => 'createDate',
        'header' => Yii::t('contacts', 'Create Date'),
        'value' => '$data->renderAttribute("createDate")',
        'filterType' => 'date',
        'type' => 'raw'
    ),
    'status' => array (
                'name'   => 'status',
                'header' => Yii::t('x2sign', 'Status'),
                'headerHtmlOptions'=>array('style'=>'width:5%'),
                'value'  => '$data->renderAttribute("status")',
    ),
    array(
        'name' => 'c_listing',
        'header' => Yii::t('contacts', 'Listing'),
        'value' => 'X2SignEnvelopes::getListLink($data)',
        'type' => 'raw'
    ),

);

$this->widget('InlineX2SignDetailGridView', array(
    'id' => "x2sign-grid",
    'possibleResultsPerPage' => array(5, 10, 20, 30, 40, 50, 75, 100),
    'enableGridResizing' => false,
    'showHeader' => true,
    'hideFullHeader' => false,
    'resultsPerPage' => $this->getWidgetProperty ('resultsPerPage'),
    'sortableWidget' => $this,
    'defaultGvSettings' => array (
        'name'       => '80',
        'assignedTo' => '30',
        'createDate' => '30',
        'status'     => '15',
        'c_listing' => '45',
    ),
    'dataColumnClass' => 'X2DataColumnGeneric',
    'gvSettingsName' => 'inlineX2SignDetailGrid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
    'template' => '{items}{pager}',
    'dataProvider' => $x2signDataProvider,
    'columns' => $columns,
    'enableColDragging' => false,
    'rememberColumnSort' => false,
));
?>
