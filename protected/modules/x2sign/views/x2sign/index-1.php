<?php
/***********************************************************************************
* is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/
include("protected/modules/x2sign/x2signConfig.php");
//Yii::import('application.modules.x2sign.models.X2SignEnvelopes');



$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module', 'S2Sign Home'), 'url'=>array('x2sign/index')),
    array('label'=>Yii::t('module', 'S2Sign Report'), 'url'=>array('x2sign/report')),
    array('label'=>Yii::t('module', 'Create S2Sign Template'), 'url'=>array('docs/createSignable')),
    array('label'=>Yii::t('module', 'Quick Send'), 'url'=>array('x2sign/quickSend')),
));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
    $('.search-form').toggle();
    return false;
});
$('.search-form form').submit(function(){
    $.fn.yiiGridView.update('opportunities-grid', {
        data: $(this).serialize()
    });
    return false;
});
");

?>
<div class="search-form" style="display:none">
<?php 
//this for folder lookup
//$ajaxID = '';
    $model->status = 1;
//$this->renderPartial('_search',array(
//    'model'=>$model,
//)); ?>
</div><!-- search-form -->
<?php

$type = "";
    switch($model->status){
    case 1:
        $type = " Actions Required";
        break;
    case 2:
        $type = " Waiting for Others";
        break;

    case 3:
        $type = " Cancelled";
        break;
    case 4:
        $type = " Completed";
        break;


    }
$this->widget('X2GridView', array(
    'id'=>'X2SignEnvelopes-grid',
    'title'=>Modules::displayName(true, $moduleConfig['moduleName']) . $type,
    'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
    'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title x2-gridview-fixed-title X2SignEnvelopes">'.
        '{title}{buttons}{filterHint}'.
        //'{massActionButtons}'.
        '{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
    'dataProvider'=>isset($_GET['X2SignEnvelopes']['relatedRecords']) ? $model->searchRelatedRecords() : $model->search(),
    // 'enableSorting'=>false,
    // 'model'=>$model,
    'filter'=>$model,
    // 'columns'=>$columns,
    'modelName'=>'X2SignEnvelopes',
    'viewName'=>'X2SignEnvelopes',
    // 'columnSelectorId'=>'contacts-column-selector',
    'defaultGvSettings'=>array(
        //'gvCheckbox' => 30,
        'name'=>257,
        'assignedTo'=>105,
        'relatedRecords'=>257,
    ),
    'specialColumns'=>array(
        'name'=>array(
            'name'=>'name',
            'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
            'type'=>'raw',
        ),
        'relatedRecords'=>array(
            'name'=>'relatedRecords',
            'value'=>'$data->renderRelatedRecords()',
            'type'=>'raw',
        ),
    ),
    
    //'enableControls'=>true,
    'fullscreen'=>true,
));
 ?>
