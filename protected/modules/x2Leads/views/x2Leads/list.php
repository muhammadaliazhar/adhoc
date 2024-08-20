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





$heading = $listModel->name; //Yii::t('contacts','All Contacts');

$authParams['X2Model'] = $listModel;

 //these hidden field is here to stop google auto fill from filling in the grid
$ConFields = X2Model::model("X2Leads")->getFields();
foreach($ConFields as $field){
    echo '<input type="hidden" id="X2Leads[' . $field->fieldName . ']" name="X2Leads[' . $field->fieldName . ']">';      
}  

$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

$menuOptions = array(
    'all', 'lists', 'create', 'createList', 'viewList', 'editList', 'deleteList','helpGuide',
);
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions, $listModel, $authParams);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').unbind('click').click(function(){
    $('.search-form').toggle();
    return false;
});
$('.search-form form').submit(function(){
    $.fn.yiiGridView.update('contacts-grid', {
        data: $(this).serialize()
    });
    return false;
});

$('#content').on('mouseup','#contacts-grid a',function(e) {
    document.cookie = 'vcr-list=".$listModel->id."; expires=0; path=/';
});

$('#createList').unbind('click').click(function() {
    var selectedItems = $.fn.yiiGridView.getChecked('contacts-grid','C_gvCheckbox');
    if(selectedItems.length > 0) {
        var listName = prompt('".addslashes(Yii::t('app','What should the list be named?'))."','');

        if(listName != '' && listName != null) {
            $.ajax({
                url:'".$this->createUrl('/contacts/contacts/createListFromSelection')."',
                type:'post',
                data:{listName:listName,modelName:'Contacts',gvSelection:selectedItems},
                success:function(response) { if(response != '') window.location.href=response; }
            });
        }
    }
    return false;
});
$('#addToList').unbind('click').click(function() {
    var selectedItems = $.fn.yiiGridView.getChecked('contacts-grid','C_gvCheckbox');

    var targetList = $('#addToListTarget').val();

    if(selectedItems.length > 0) {
        $.ajax({
            url:'".$this->createUrl('/contacts/contacts/addToList')."',
            type:'post',
            data:{listId:targetList,gvSelection:selectedItems},
            success:function(response) { if(response=='success') alert('".addslashes(Yii::t('app','Added items to list.'))."'); else alert(response); }
        });
    }
    return false;
});
$('#removeFromList').unbind('click').click(function() {
    var selectedItems = $.fn.yiiGridView.getChecked('contacts-grid','C_gvCheckbox');
    if(selectedItems.length > 0) {
        var confirmRemove = confirm('".addslashes(Yii::t('app','Are you sure you want to remove these items from the list?'))."');

        if(confirmRemove) {
            $.ajax({
                url:'".$this->createUrl('/contacts/contacts/removeFromList')."',
                type:'post',
                data:{listId:".$listModel->id.",gvSelection:selectedItems},
                success:function(response) { if(response=='success') $.fn.yiiGridView.update('contacts-grid'); else alert(response); }
            });
        }
    }
    return false;
});
");

?>

<div class="search-form" style="display:none">
<?php /* $this->renderPartial('_search',array(
    'model'=>$model,
        'users'=>User::getNames(),
)); */ ?>
</div><!-- search-form -->
<?php

$massActions = array(
    'MassTag', 'MassTagRemove', 'MassUpdateFields', 'MassAddToList', 
    'NewListFromSelection', 'MassExecuteMacro'
);

if ($listModel->type === 'static') {
    $massActions[] = 'MassRemoveFromList';
}

$this->widget('X2GridView', array(
    'id'=>'contacts-grid',
    'enableQtips' => true,
    'qtipManager' => array (
        'X2GridViewQtipManager',
        'loadingText'=> addslashes(Yii::t('app','loading...')),
        'qtipSelector' => ".contact-name"
    ),
    'title'=>$heading,
    'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
    'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title x2-gridview-fixed-title">{title}{buttons}{massActionButtons}'
            .(Yii::app()->user->checkAccess('AdminExportModels',array('module'=>'contacts')) ? 
			CHtml::link(
                    Yii::t('app','Export'),
                    array('/admin/exportModels','model'=>'X2Leads', 'listId'=>$listModel->id),
                    array('class'=>'x2-button')
                ) : null)
            .CHtml::link(
                Yii::t('marketing','Email List'), 
                Yii::app()->createUrl('/marketing/marketing/create',array('Campaign[listId]'=>$listModel->id)),
                array('class'=>'x2-button')
            )
        .'{filterHint}{summary}{items}{pager}',
    'fixedHeader'=>true,
    'dataProvider'=>$dataProvider,
    // 'enableSorting'=>false,
    // 'model'=>$model,
    'filter'=>$model,
    'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
    // 'columns'=>$columns,
    'modelName'=>'X2Leads',
    'viewName'=>'contacts_list'.$listModel->id,
    // 'columnSelectorId'=>'contacts-column-selector',
    'defaultGvSettings'=>array(
        'gvCheckbox' => 30,
        'name' => 125,
        'lastActivity' => 78,
        'gvControls' => 73,
    ),
    'selectableRows'=>2,
    'specialColumns'=>array(
        'name'=>array(
            'name'=>'name',
            'header'=>Yii::t('contacts','Name'),
            'value'=>'CHtml::link($data->name,array("view","id"=>$data->id), array("class" => "contact-name"))',
            'type'=>'raw',
        ),
    ),
    'massActions'=>$massActions,
    'enableControls'=>true,
    'enableTags'=>true,
    'fullscreen'=>true,
    'enableSelectAllOnAllPages' => false,
    'enableCheckboxColumn' => true
));

