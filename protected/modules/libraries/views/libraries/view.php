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

$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
        $('.search-form').toggle();
        return false;
});

$(function() {
    $('.filters').children().children().each(function() {
        var self = this;
        $(self).prop('placeholder', 'Search');
    });
});

$('.search-form form').submit(function(){
console.log($(this).serialize());
        $.fn.yiiGridView.update('libraries-grid', {
                data: $(this).serialize()
        });
        return false;
});
");

Yii::app()->clientScript->registerCss('contactRecordViewCss', "

#content {
    background: none !important;
    border: none !important;
}
");

Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');

include("protected/modules/libraries/librariesConfig.php");

$actionMenuViewItem = RecordViewLayoutManager::getViewActionMenuListItem ($model->id);
if (isset ($actionMenuViewItem['url'])) unset ($actionMenuViewItem['url']);

$this->actionMenu = $this->formatMenu(array(
    array('label' => Yii::t('module', '{X} List', array('{X}' => Modules::itemDisplayName())), 'url' => array('index')),
    array('label' => Yii::t('module', 'Create {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('create')),
    $actionMenuViewItem,
    array('label' => Yii::t('module', 'Edit {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('update', 'id' => $model->id)),
    array('label' => Yii::t('module', 'Delete {X}', array('{X}' => Modules::itemDisplayName())), 'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'))),
    array(
        'label' => Yii::t('app', 'Send Email'), 'url' => '#',
        'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')),
    ModelFileUploader::menuLink(),
    array('label' => Yii::t('quotes', 'Quotes/Invoices'), 'url' => 'javascript:void(0)', 'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')),
    array(
        'label' => Yii::t('app', 'Print Record'),
        'url' => '#',
        'linkOptions' => array(
            'onClick' => "window.open('" .
            Yii::app()->createUrl('/site/printRecord', array(
                'modelClass' => "Libraries",
                'id' => $model->id,
                'pageTitle' =>
                Yii::t('app', '{X}', array('{X}' => Modules::itemDisplayName())) . ': ' . $model->name
            )) . "');"
        ),
    ),
    RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
), array ('X2Model' => $model));

$modelType = json_encode("Libraries");
$modelId = json_encode($model->id);

Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
?>
<?php
$this->widget('InlineEmailForm', array(
    'attributes' => array(
        'to' => implode(', ', $model->getRelatedContactsEmails()),
        'modelName' => get_class($model),
        'modelId' => $model->id,
    ),
    'insertableAttributes' =>
    array(
        Yii::t('module', '{modelName} Attributes', array('{modelName}' => get_class($model))) =>
        $model->getEmailInsertableAttrs($model)
    ),
    'startHidden' => true,
        )
);
?>
    <div id="quote-form-wrapper">
    <?php
    $this->widget('InlineQuotes', array(
        'startHidden' => true,
        'contactId' => $model->id,
        'modelName' => X2Model::getModuleModelName()
    ));
    ?>
    </div>
    <div class="Libraries">
    <div class="search-form" style="display:none">
  </div><!-- search-form -->
    <?php
    /*$this->widget('X2GridView', array(
        'id' => 'libraries-grid',
        'title'=>Yii::t('media','Library'),
        'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
        'template'=>
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
          'class="page-title icon library x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        '{items}{pager}',
        'dataProvider' =>$media,
        'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
        'fixedHeader'=>true,
        'filter'=>Media::model(),
        'gvSettingsName' => 'library-index',
        'defaultGvSettings'=>array(
                'gvCheckbox' => 30,
                'fileName' => 285,
                'name' => 114,
                'associationType' => 85,
                'createDate' => 94,
                'uploadedBy' => 114,
                'filesize' => 75,
        ),
        'modelName'=>'Media',
        'specialColumns' => array(
                'fileName' => array(
                        'name' => 'fileName',
                        'header' => Yii::t('media','File Name'),
                        'type' => 'raw',
                        'value' => '$data["drive"]?CHtml::link($data["name"],array("view","id"=>$data->id), array("class" => "media-name")):CHtml::link(CHtml::encode($data["fileName"]), array("media/view","id"=>$data->id), array("class" => "media-name"))',
                ),
        ),
        'fullscreen'=>true,
    ));
    $this->widget ('ModelFileUploader', array(
        'associationType' => 'libraries',
        'associationId' => $model->id,
    ));*/
    ?>
    </div>
    <div class="page-title-placeholder"></div>
    <div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
        <div class="page-title libraries">
            <h2>
                <?php echo Yii::t('module', 'View {X}', array('{X}' => Modules::itemDisplayName())); ?>: <?php
                echo $model->renderAttribute ('name');
                ?>
            </h2>
            <?php
            echo X2Html::editRecordButton($model);
            echo X2Html::emailFormButton();
            echo X2Html::inlineEditButtons();
            ?>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
            <?php
            $this->widget ('DetailView', array(
                'model' => $model
            ));
            //$this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $model, 'modelName' => 'libraries')); ?>
</div>
<?php
/* $this->widget('X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'model' => $model,
    )); */
