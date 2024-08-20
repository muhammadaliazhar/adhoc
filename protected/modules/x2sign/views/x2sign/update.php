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






include("protected/modules/x2sign/x2signConfig.php");

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('create')),
	array('label'=>Yii::t('module','View {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Edit {X}',array('{X}'=>Modules::itemDisplayName()))),
	array('label'=>Yii::t('module','Delete {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
), array ('X2Model' => $model));
?>
<div class="page-title x2sign"><h2><?php echo Yii::t('module','Update {X}',array('{X}'=>Modules::itemDisplayName())); ?> <?php echo $model->name; ?></h2></div>

<?php 
$this->widget ('FormView', array(
	'model' => $model
));
//echo $this->renderPartial('application.components.views.@FORMVIEW', array('model'=>$model,'users'=>$users, 'modelName'=>'x2sign')); ?>
