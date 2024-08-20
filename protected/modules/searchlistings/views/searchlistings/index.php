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
include("protected/modules/searchlistings/searchlistingsConfig.php");

$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module','{X} List',array('{X}'=>Modules::itemDisplayName()))),
));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
/* $('.search-form form').submit(function(){
	$.fn.yiiGridView.update('opportunities-grid', {
		data: $(this).serialize()
	});
	return false;
}); */
");

?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
));
?>
</div><!-- search-form -->
<?php
$this->widget('X2GridView', array(
	'id'=>'searchlistings-grid',
	'title'=>Modules::displayName(true, $moduleConfig['moduleName']),
	//'buttons'=>array('advancedSearch','autoResize','showHidden'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title x2-gridview-fixed-title searchlistings">'.
        '{title}{buttons}'.
        '{summary}{topPager}{items}{pager}',
        'fixedHeader'=>true,
	'dataProvider'=>$listingDP,
	'enableSorting'=>true,
	'filter'=>$model,
	'modelName'=>'Listings2',
	'viewName'=>'Listings2',
	'specialColumns'=>array(
		/*'name'=>array(
			'name'=>'name',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->name',
			'type'=>'raw',
		),*/
'c_listing_status__c'=>array(
			'name'=>'c_listing_status__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_listing_status__c',
			'type'=>'raw',
		),
'c_listing_number__c'=>array(
			'name'=>'c_listing_number__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_listing_number__c',
			'type'=>'raw',
		),
'c_ad_headline__c'=>array(
			'name'=>'c_ad_headline__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_ad_headline__c',
			'type'=>'raw',
		),
'c_listing_price__c'=>array(
			'name'=>'c_listing_price__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_listing_price__c',
			'type'=>'raw',
		),
'c_total_sales__c'=>array(
			'name'=>'c_total_sales__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_total_sales__c',
			'type'=>'raw',
		),
'c_seller_discretionary_earnings__c'=>array(
			'name'=>'c_seller_discretionary_earnings__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_seller_discretionary_earnings__c',
			'type'=>'raw',
		),
'c_listing_category__c'=>array(
			'name'=>'c_listing_category__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_listing_category__c',
			'type'=>'raw',
		),
'c_subcategory_1__c'=>array(
			'name'=>'c_subcategory_1__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_subcategory_1__c',
			'type'=>'raw',
		),
'assignedTo'=>array(
			'name'=>'assignedTo',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->assignedTo',
			'type'=>'raw',
		),
'c_franchisee__c'=>array(
			'name'=>'c_franchisee__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_franchisee__c',
			'type'=>'raw',
		),
'c_ttl_listing_state_province__c'=>array(
			'name'=>'c_ttl_listing_state_province__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_ttl_listing_state_province__c',
			'type'=>'raw',
		),
'c_ttl_listing_county__c'=>array(
			'name'=>'c_ttl_listing_county__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_ttl_listing_county__c',
			'type'=>'raw',
		),
'c_ttl_listing_city__c'=>array(
			'name'=>'c_ttl_listing_city__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_ttl_listing_city__c',
			'type'=>'raw',
		),
'c_deal_stage__c'=>array(
			'name'=>'c_deal_stage__c',
			//'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
		        'value'=>'$data->c_deal_stage__c',
			'type'=>'raw',
		),
		'description'=>array(
			'name'=>'description',
			'header'=>Yii::t('app','Description'),
			'value'=>'Formatter::trimText($data->description)',
			'type'=>'raw',
		),
'c_Seller'=>array(
                        'name'=>'c_Seller',
                        //'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
                        'value'=>'$data->c_Seller',
                        'type'=>'raw',
                ),
'c_employee__c'=>array(
                        'name'=>'c_employee__c',
                        //'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
                        'value'=>'$data->c_employee__c',
                        'type'=>'raw',
                ),

	),
	//'enableControls'=>true,
	'fullscreen'=>true,
));
 ?>
