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

Yii::import ('application.components.sortableWidget.components.RelatedDocsGridView');

Yii::app()->clientScript->registerScript('relatedDocsWidget', "
    $('#link-doc').on('click', function() {

    var type = 'listings';
    var dialogTitle = 'Add Doc';

    // Dialog view
    var dia = $('<div />');
    dia.prop('title', dialogTitle);

    // Dialog form
    var form = $('<form />');

    // List view for model
    var modelListView = $('<div />');
    modelListView.prop('id', 'list');
    modelListView.css('border', '1px solid lightgrey');
    modelListView.css('padding', 5);
    modelListView.css('height', 300);
    modelListView.css('border-radius', 5);
    modelListView.css('overflow-y', 'auto');

    // Search view
    var searchView = $('<input />');
    searchView.prop('placeholder', 'search...');

    var searchButton = $('<div />');
    searchButton.addClass('x2-button');
    searchButton.text('Search');
    searchButton.on('click', function() {
        var loading = $('<div />');
        loading.text('Loading...');

        var query = searchView.val();

        modelListView.empty();
        modelListView.append(loading);

        $.ajax({
            type: 'GET',
            data: {
                docs: type,
                query: query
            },
            success: function (obj) {
                var modelList = $.parseJSON(obj)[0];

                // Create new dialog list item view for each object in model
                for (var i = 0; i < modelList.length; i++) {
                    var model = modelList[i].model;
                    var viewName = modelList[i].viewName;
                    var viewText = modelList[i].viewText;
                    var type = modelList[i].type ? modelList[i].type : '';

                    var modelView = $('<div />');
                    modelView.text(viewName);
                    modelView.addClass('not-selected');
                    modelView.css('padding', 5);
                    modelView.css('cursor', 'pointer');
                    modelView.css('word-break', 'break-word');
                    modelView.data('model', model);
                    modelView.data('viewText', viewText);
                    modelView.data('type', type);
                    modelView.on('click', function () {
                        var self = $(this);
                        self.prop('class', self.prop('class') === 'selected' ? 'not-selected' : 'selected');
                        self.css('background-color', self.prop('class') === 'selected' ? 'blue' : 'white');
                        self.css('color', self.prop('class') === 'selected' ? 'white' : 'black');
                    });

                    modelListView.append(modelView);
                }
            },
            error: function (data) {
                alert(data.responseText);
            },
            complete: function () {
                loading.remove();
            }
        });
    });

    form.append(searchView);
    form.append(searchButton);
    form.append(modelListView);

    dia.append(form);
    
    dia.dialog({
        modal: true,
        buttons: {
            'Select': function () {
                var selected = dia.find('.selected');

                var selectedArray = [];

                // Create new view for each object selected
                selected.each(function () {
                    var model = $(this).data('model');
                    var viewText = $(this).data('viewText');
                    var type = $(this).data('type');

                    selectedArray.push(model.id);
                });

                $.ajax({
                        type: 'POST',
                        data: {
                            docs: type,
                            selected: JSON.stringify(selectedArray)
                        },
                        success: function(data) {
                            location.reload();
                        },
                        error: function(error) {
                            alert(error);
                        }
                });


                $(this).dialog('close');
            },
            'Cancel': function () {
                $(this).dialog('close');
            }
        }
    });
    });
", CClientScript::POS_READY);

$dataProvider = $this->getDataProvider ();

?>

<div id="relationships-form" 
<?php  ?>
 style="<?php echo ($displayMode === 'grid' ?  '' : 'display: none;'); ?>"
<?php  ?>
 class="<?php echo ($this->getWidgetProperty ('mode') === 'simple' ? 
    'simple-mode' : 'full-mode'); ?>">

<?php

$columns = array(
    array(
        'name' => 'name',
        'header' => Yii::t("contacts", 'Name'),
        /* 'value' => '
            CHtml::link($data->renderAttribute ("name"),
            "/index.php/media/" . Media::model()->findByAttributes(array("name" => $data->name))-> id);
        ', */
        'value' => '$data->name',
        'type' => 'raw',
    ),
    array(
        'name' => 'status',
        'header' => Yii::t("contacts", 'Status'),
        'value' => '
            $data->renderAttribute ("c_status");
        ',
        'type' => 'raw',
    ),
    array(
        'name' => 'sent_document',
        'header' => Yii::t("contacts", 'Sent Document'),
        'value' => '
            isset($data->c_documentId) ?
            CHtml::link("Sent Document",
            Yii::app()->createUrl("/media/" . $data->c_documentId)) : "";
        ',
        'type' => 'raw',
    ),
    array(
        'name' => 'signed_document',
        'header' => Yii::t("contacts", 'Signed Document'),
        'value' => '
            isset($data->c_signedDocumentId) ?
            CHtml::link("DocuSign Document Ver",
            Yii::app()->createUrl("/media/" . $data->c_signedDocumentId)) : "";
        ',
        'type' => 'raw',
    ),
    array(
        'name' => 'create_date',
        'header' => Yii::t("contacts", 'Create Date'),
        'value' => '
            gmdate("m-d-Y", $data->createDate);
        ',
        'type' => 'raw',
    ),
    array(
        'name' => 'listing_name',
        'header' => Yii::t("contacts", 'Listing'),
        'value' => 'is_array(Yii::app()->db->createCommand()
                ->select("c_listing")
                ->from("x2_docusign_status")
                ->where("c_recordId =:id AND nameId = :name", array(":id" => $data->c_recordId, ":name" => $data->renderAttribute ("nameId")))
                ->order("c_listing desc")
                ->queryRow()) ? implode("" , Yii::app()->db->createCommand()
                ->select("c_listing")
                ->from("x2_docusign_status")
                ->where("c_recordId =:id AND nameId = :name", array(":id" => $data->c_recordId, ":name" => $data->renderAttribute ("nameId")))
                ->order("c_listing desc")
                ->queryRow()) : Yii::app()->db->createCommand()
                ->select("c_listing")
                ->from("x2_docusign_status")
                ->where("c_recordId =:id AND nameId = :name", array(":id" => $data->c_recordId, ":name" => $data->renderAttribute ("nameId")))
                ->order("c_listing desc")
                ->queryRow();',
        'type' => 'raw',
    ),
);

$this->widget('RelatedDocsGridView', array(
    'id' => "related-docs-grid",
    'possibleResultsPerPage' => array(5, 10, 20, 30, 40, 50, 75, 100),
    'enableGridResizing' => false,
    'showHeader' => CPropertyValue::ensureBoolean ($this->getWidgetProperty('showHeader')),
    'hideFullHeader' => CPropertyValue::ensureBoolean (
        $this->getWidgetProperty('hideFullHeader')),
    'resultsPerPage' => $this->getWidgetProperty ('resultsPerPage'),
    'sortableWidget' => $this,
    'defaultGvSettings' => array (
        'expandButton.' => '12',
        'name' => '22%',
    ),
    'htmlOptions' => array (
        'class' => 
            ($dataProvider->itemCount < $dataProvider->totalItemCount ?
            'grid-view has-pager' : 'grid-view'),
    ),
    'dataColumnClass' => 'X2DataColumnGeneric',
    'gvSettingsName' => 'relatedDocsGrid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
    'template' => '<div class="title-bar">{summary}</div>{items}{pager}',
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'enableColDragging' => false,
    'rememberColumnSort' => false,
));
?>
</div>
