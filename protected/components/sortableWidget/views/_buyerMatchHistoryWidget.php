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
            'name' => 'createDate',
            'header' => Yii::t('admin', 'Create Date'),
            'value' => 'date("Y-m-d H:i:s", $data->createDate)',     
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        ),
    array(
            'name' => 'prefMatch',
            'header' => Yii::t('admin', 'Matched Preference'),
            'value' => '$data->prefMatch',
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        ),
    );

if(Yii::app()->controller->module->getName() == "contacts") {
    array_push($columns, array(
            'name' => 'listingNumber',
            'header' => Yii::t('admin', 'Listing Id'),
            'value' => 'CHtml::link($data->listingId, "https://sydney.tworld.com/index.php/listings2/".$data->listingId)',
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        )
    );
} else {
    array_push($columns, array(
            'name' => 'buyerName',
            'header' => Yii::t('admin', 'Buyer Name'),
            'value' => 'Listings2::getBuyerLink($data)',
            'type' => 'raw',
            'htmlOptions' => array('width' => '5%'),
        )
    );
}

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'buyer-match-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/gridview',
    'template' => $template,
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
    'dataProvider' => $model->search($this->model->id,$this->model),
    'filter' => $model,
    'columns' => $columns,
));
echo "<br>";
echo CHtml::link(
        Yii::t('admin', X2Html::fa('fa-share fa-lg') . 'Export Buyer Match History'), '#', array(
    'class' => 'x2-button export-buyHistory',
    'id' => 'export-buyHistory',
));

if(get_class($this->model) == "Listings2"){
    echo CHtml::link(
            Yii::t('admin','Create Buyer List'),  $this->controller->createUrl('/listings2/createBuyerMatchList') . "?listingId=" . $this->model->id, array(
        'class' => 'x2-button ',
        'id' => 'BuyerListCreate',
    ));
}


Yii::app()->clientScript->registerScript('buyHistory-js', '
    function refreshQtipHistory(){
        $(".x2-hint").qtip();
    }

    $("#export-buyHistory").click(function(evt) {
        evt.preventDefault();
        $.ajax ({
            url: "https://sydney.tworld.com/index.php/admin/ExportBuyerMatch",
            data: { 
                "model": "' . get_class($this->model) . '", 
                "id": ' . $this->model->id . '
            },
            success: function(resp) {
                var message = JSON.parse(resp.message);
                window.location.href = "' .  $this->controller->createUrl('/admin/downloadData') . '" + "?file=" + message.dlUrl;
            } 
            
            
        });
    });
', CClientScript::POS_END);


