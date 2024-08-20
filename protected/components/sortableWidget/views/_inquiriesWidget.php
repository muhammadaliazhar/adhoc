<?php

/* * *********************************************************************************
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
 * ******************************************************************************** */

//Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/css/viewInquiries.css');
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/inquiriesWidget.css'
);

$template = '{items}{pager}';
$columns = array(
	array(
            'name' => 'name',
            'header' => Yii::t('admin', 'Contact Roles'),
            'value' => '$data->name ? CHtml::link(CHtml::encode($data->name),Yii::app()->controller->createUrl("inquiries/".$data->id)) : CHtml::encode($data->name)',
            'type' => 'raw',
	    'htmlOptions' => array('width' => '5%'),
        ),
	array(
            'name' => 'c_contact__c',
            'header' => Yii::t('admin', 'Buyer'),
            'value' => '$data->c_contact__c ? CHtml::link(CHtml::encode(substr($data->c_contact__c, 0, strpos($data->c_contact__c, "_"))), Yii::app()->controller->createAbsoluteUrl("/contacts/" . substr($data->c_contact__c, strpos($data->c_contact__c, "_") + 1))) : CHtml::encode(substr($data->c_contact__c, 0, strpos($data->c_contact__c, "_")))',
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        ),
	array(
            'name' => 'c_email__c',
            'header' => Yii::t('admin', 'Email'),
            'value' => '$data->c_email__c ? CHtml::encode($data->c_email__c) : ""', //CHtml::link(CHtml::encode($data->c_email__c), Yii::app()->controller->createUrl("#")) : ""',     
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%', 'onclick' => 'toggleEmailForm(); return false;'),
        ),
	array(
            'name' => 'c_listing_owner__c',
            'header' => Yii::t('admin', 'Listing Owner'),
            'value' => '$data->c_email__c ? CHtml::encode($data->c_listing_owner__c) : ""', //CHtml::link(CHtml::encode($data->c_email__c), Yii::app()->controller->createUrl("#")) : ""',     
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        ),
	/*array(
            'name' => 'c_contact_phone__c',
            'header' => Yii::t('admin', 'Phone Number'),
            'value' => '$data->c_contact_phone__c ? CHtml::encode($data->c_contact_phone__c) : ""',
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        ),*/
    );

if(Yii::app()->controller->module->getName() == "contacts") {
	array_push($columns, array(
            'name' => 'c_listing_number__c',
            'header' => Yii::t('admin', 'Listing Number'),
            'value' => '$data->c_listing_number__c ? CHtml::encode($data->c_listing_number__c) : ""',
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        ),
	array(
            'name' => 'c_listing__c',
            'header' => Yii::t('admin', 'Listing'),
            'value' => '$data->c_listing__c ? CHtml::link(CHtml::encode(substr($data->c_listing__c, 0, strpos($data->c_listing__c, "_"))), Yii::app()->controller->createAbsoluteUrl("/listings2/" . substr($data->c_listing__c, strpos($data->c_listing__c, "_") + 1))) : ""',
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        ));
}

array_push($columns, array(
            'name' => 'createDate',
            'header' => Yii::t('admin', 'Create Date'),
            'value' => 'isset($data->createDate) && $data->createDate !== "" ? date("m/d/Y", strtotime(Formatter::formatLongDateTime($data->createDate))) : "n/a"',
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
)
//array(
//    'name' => 'c_docusign_status',
//    'header' => Yii::t('admin', 'Docusign Status'),
//    'value' => '$data->c_docusign_status ? $data->c_docusign_status : ""',
//    'type' => 'raw',
//    'htmlOptions' => array('width' => '5%'),
//)
);

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'inquiries-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/gridview',
    'template' => $template,
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
    'dataProvider' => $model->search($this->model->id,$this->model),
    'filter' => $model,
    'columns' => $columns,
));
echo "<br>";
