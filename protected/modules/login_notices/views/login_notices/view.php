<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/





$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerCss('contactRecordViewCss', "

#content {
    background: none !important;
    border: none !important;
}
");

Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');

include("protected/modules/login_notices/login_noticesConfig.php");

$actionMenuViewItem = RecordViewLayoutManager::getViewActionMenuListItem ($model->id);
if (isset ($actionMenuViewItem['url'])) unset ($actionMenuViewItem['url']);

$menuItems = array(
    array('label' => Yii::t('module', '{X} List', array('{X}' => Modules::itemDisplayName())), 'url' => array('index')),
    $actionMenuViewItem,
);

if (Yii::app()->params->isAdmin) {
    $menuItems[] = array('label' => Yii::t('module', 'Create {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('create'));
    $menuItems[] = array('label' => Yii::t('module', 'Edit {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('update', 'id' => $model->id));
    $menuItems[] = array('label' => Yii::t('module', 'Delete {X}', array('{X}' => Modules::itemDisplayName())), 'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => Yii::t('app', 'Are you sure you want to delete this item?')));
}

$this->actionMenu = $this->formatMenu($menuItems, array ('X2Model' => $model));

$modelType = json_encode("Login_notices");
$modelId = json_encode($model->id);

Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
?>
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
        <div class="page-title login_notices">
            <h2>
                <?php echo Yii::t('module', 'View {X}', array('{X}' => Modules::itemDisplayName())); ?>: <?php
                echo $model->renderAttribute ('name');
                ?>
            </h2>
            <?php
            if (Yii::app()->params->isAdmin) {
                echo X2Html::editRecordButton($model);
                echo X2Html::emailFormButton();
                echo X2Html::inlineEditButtons();
            }
            ?>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
            <?php 
            $this->widget ('DetailView', array(
                'model' => $model
            ));
            //$this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $model, 'modelName' => 'login_notices')); ?>

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


$this->widget ('ModelFileUploader', array(
    'associationType' => 'login_notices',
    'associationId' => $model->id,
));

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
</div>

<style>
    tr.userFilter td input {
        border: none;
        width: auto;
    }
</style>

<?php
if (Yii::app()->params->isAdmin) {
$this->widget('X2WidgetList',
    array(
        'layoutManager' => $layoutManager,
        'model' => $model,
    ));

//$notSeenbyUser = Yii::app()->db->createCommand('SELECT * FROM x2_users WHERE id NOT IN ( SELECT firstId FROM x2_relationships WHERE secondType ="custom_Login_notices" AND secondId = '.$model->id.');')->queryAll();

$dataProvider=new CArrayDataProvider($notSeenbyUser, array(
    'id'=>'notSeenByUserDataProvider',
    'sort'=>array(
        'attributes'=>array('firstName', 'lastName', 'username'),
    ),
    'pagination'=>array(
        'pageSize'=>50,
    ),
));

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$dataProvider,
    'filter' => $filtersForm,
    'filterCssClass' => 'userFilter',
    'columns'=>array(
        array(
            'name'=>'firstName',
            'header'=>'First Name',
            'value'=>'$data["firstName"]',
            'sortable' => true,
        ),
        array(
            'name'=>'lastName',
            'header' => 'Last Name',
            'value'=>'$data["lastName"]'
        ),
        array(
            'name'=>'username',
            'header'=>'Username',
            'value'=>'CHtml::link(CHtml::encode($data["username"]),array("/users/users/view","id"=>$data["id"]))',
            'type' => 'raw',
        ),
        array(
            'name' => 'Last Login',
            'value' => '$data["lastLogin"] ? Formatter::formatDateTime($data["lastLogin"]) : "n/a"',
            'type' => 'raw',
        ),
    ),
));
}
?>

