<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
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

$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));
$contactModule = Modules::model()->findByAttributes(array('name'=>'contacts'));

$menuOptions = array(
    'index', 'create', 'import',// 'export',
);
if ($accountModule->visible && $contactModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions);
$Datejs = "jQuery('#startDate').datetimepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['en_us']));" .
            "jQuery('#endDate').datetimepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['en_us']));";


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



//this will be for the royalty report popup
$('#NewReport').click(function(){
                var buyPrefBox = $('<div />');
                buyPrefBox.prop('id', 'docusign');
                buyPrefBox.prop('title', 'Create Royalty Report');
                buyPrefBox.css('height', 'auto');
                buyPrefBox.css('width', '1000px');
                buyPrefBox.css('padding', 20);
                buyPrefBox.css('overflow-y', 'auto');
                buyPrefBox.css('overflow-x', 'auto');
                buyPrefBox.dialog({ width: 450, height:'auto',autoResize:true, draggable: false});
                var startForm = $('<form id=\"royalRepForm\" action=\"/index.php/royaltyreport/createFromDeals\"></form>');
                var nameBox  = $('<label style=\"font-weight: bold;\">Name: </label><input class=\"datePicker x2-gridview-filter-datepicker\" type=\"text\" id=\"reportName\" name=\"reportName\"><div><small style=\"font-style: italic; font-size:10px;\">Title of the report you are running, suggestion: Month and Year-Franchise</small></div><br>');
                var startBox = $('<label style=\"font-weight: bold;\">Start Date: </label><input class=\"datePicker x2-gridview-filter-datepicker\" type=\"text\" id=\"startDate\" name=\"startDate\"><br>');
                var endBox = $('<label style=\"font-weight: bold;\">End Date: </label><input class=\"datePicker x2-gridview-filter-datepicker\" type=\"text\" id=\"endDate\" name=\"endDate\"><div><small style=\"font-style: italic; font-size:10px;\">Date frame of sold deals you want included in the report. The date is based on the Actual Closed Date</small></div><br>');
                //now add the save button 
                var saveBut = $('<input type=\"button\" id=\"reportCreate\" value=\"Create\"></input>');
                //var endForm = $('</form>');
                startForm.append(nameBox, startBox, endBox, saveBut);
                buyPrefBox.append(startForm);
                " . $Datejs ."

                // this will be for checking the values are filled
                $('#reportCreate').click(function(){
                    if($('#reportName').val()  == ''){
                        alert('Name must be filled');
                        return;
                    }
                    if($('#endDate').val()  == ''){
                        alert('End Date must be filled');
                        return;
                    }
                    if($('#startDate').val()  == ''){
                        alert('Start Date must be filled');
                        return;
                    }

                    $('#royalRepForm').submit(); 

                });


});


");
?>

<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php

$royaltyButton = "";
$user = User::getMe();
if(Groups::userHasRole($user->id, 'Franchise') || Yii::app()->params->isAdmin){
    $royaltyButton = '<div class="x2-button-group x2-grid-view-controls-buttons"><a style="width:120px" title="Royalty Report" id="NewReport" class="x2-button clicked" href="javascript:void(0);">generate royalty report</a></div>';
}

$this->widget('X2GridView', array(
	'id'=>'opportunities-grid',
    'title'=>Yii::t('opportunities','{opportunities}', array(
        '{opportunities}'=>Modules::displayName(),
    )),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
	'template'=>
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon opportunities x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.$royaltyButton.
        '{massActionButtons}'.
        '{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	// 'columns'=>$columns,
	'modelName'=>'Opportunity',
	'viewName'=>'opportunities',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
        'gvCheckbox' => 30,
		'name' => 164,
		'quoteAmount' => 95,
		'probability' => 77,
		'expectedCloseDate' => 125,
		'createDate' => 78,
		'lastActivity' => 79,
		'assignedTo' => 119,
	),
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'header'=>Yii::t('opportunities','Name'),
			'value'=>'CHtml::link($data->renderAttribute("name"),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
        'id'=>array(
			'name'=>'id',
			'header'=>Yii::t('opportunities','ID'),
			'value'=>'CHtml::link($data->id,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
	'fullscreen'=>true,
));

?>
