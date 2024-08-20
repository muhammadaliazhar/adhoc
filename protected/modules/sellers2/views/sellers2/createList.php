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





$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

$menuOptions = array(
    'all', 'lists', 'create', 'createList','helpGuide',
);
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';

$moduleConfig['moduleName'] = "sellers2";
if (Yii::app()->params->isAdmin) {
    $this->actionMenu = $this->formatMenu(array(
        array('label' => Yii::t('module', '{X} List', array('{X}' => Modules::itemDisplayName()))),
        array('label' => Yii::t('module', 'Create {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('create')),
        array('label' => Yii::t('module', 'Import {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('admin/importModels', 'model' => ucfirst($moduleConfig['moduleName']))),
        array('label' => Yii::t('module', 'Export {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('admin/exportModels', 'model' => ucfirst($moduleConfig['moduleName'])))
    ));
} else {
    $this->actionMenu = $this->formatMenu(array(
        array('label' => Yii::t('module', '{X} List', array('{X}' => Modules::itemDisplayName()))),
        array('label' => Yii::t('module', 'Create {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('create')),
        array('label' => Yii::t('module', 'Export {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('site/exportModels', 'model' => ucfirst($moduleConfig['moduleName'])))
    ));
}



?>
<div class="page-title icon contacts"><h2><?php echo Yii::t('contacts','Create List'); ?></h2></div>

<?php 
echo $this->renderPartial('_listForm', array(
	'model'=>$model,
	'criteriaModels'=>$criteriaModels,
	// 'attributeList'=>$attributeList,
	'comparisonList'=>$comparisonList,
	'users'=>$users,
	'listTypes'=>$listTypes,
	'itemModel'=>$itemModel,
)); 
?> 


