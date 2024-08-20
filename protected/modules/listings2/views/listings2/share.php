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

Yii::app()->clientScript->registerCss('flowIndexCss','
#embed-row {
  height: 25px;
}
#embed-row #embed-code {
  box-sizing: border-box;
  width: 200px;
  height: 100%;
  float: left;
  margin: 0px;
  border: 1px solid #B9B9B9;
  background: #F6F6F6;
  color: #666;
  box-shadow: 0 1px 0 white inset 0 1px 1px rgba(0, 0, 0, 0.17);
  border-radius: 3px 0px 0px 3px;
}
#embed-row #embed-code:focus {
  border-color: #4496E7;
  color: #444;
  background: white;
  outline: 0;
}
#embed-row #clipboard {
  box-sizing: border-box;
  height: 100%;
  margin: 0px;
  border-left: none;
  border-radius: 0px 3px 3px 0px;
}
');

$url = $model->c_publicHash;

Yii::app()->clientScript->registerScript('init', "
    var embed = $('#embed-code');
    embed.val('$url');

    $('#generate').on('click', function() {
        $.ajax({
            type: 'POST',
            data: {
                publicHash: 'new'
            },
            success: function(data) {
                embed.val(data);
            }
        });
    });
    $('#remove').on('click', function() {
        $.ajax({
            type: 'POST',
            data: {
                publicHash: 'remove'
            },
            success: function (data) {
                embed.val(data);
            }
        });
    });

", CClientScript::POS_READY);

$authParams['X2Model'] = $model;
$this->actionMenu = $this->formatMenu(array(
    array('label' => Yii::t('module', '{X} List', array('{X}' => Modules::itemDisplayName())), 'url' => array('index')),
    array('label' => Yii::t('module', 'Create {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('create')),
    array('label' => Yii::t('module', 'View {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('view', 'id' => $model->id)),
    array('label' => Yii::t('module', 'Edit {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('update', 'id' => $model->id)),
    array('label' => Yii::t('module', 'Delete {X}', array('{X}' => Modules::itemDisplayName())), 'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'))),
    array('label' => Yii::t('module', 'Share {X}', array('{X}' => Modules::itemDisplayName()))),
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
                'modelClass' => "Listings2",
                'id' => $model->id,
                'pageTitle' =>
                Yii::t('app', '{X}', array('{X}' => Modules::itemDisplayName())) . ': ' . $model->name
            )) . "');"
        ),
    ),
    RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
), array ('X2Model' => $model));
?>
<div class="page-title icon contacts"><h2><span class="no-bold"><?php echo Yii::t('contacts','Share {module}', array('{module}'=>Modules::displayName(false)));?>:</span> <?php echo CHtml::encode($model->name);?></h2></div>

<div class="form">
    <h2 sytle="margin: 0px 0px 5px 0px">Public Share Url</h2>
    <div id='embed-row'>
        <input readonly type="text" id="embed-code" style='width: 700px;' placeholder='' />
        <span class='x2-button' id='clipboard' title='Select Text'>
            <i class='fa fa-clipboard'></i>
        </span>
        <span style='display:none'id='copy-help'>
            <p class='fieldhelp'>
                <?php $help = Auxlib::isMac() ? "⌘-c to copy" : "ctrl-c to copy"; ?>
                <?php echo Yii::t('app', $help) ?>
            </p>
        </span>
    </div>
    <br>
    <div id="generate" class="x2-button">Generate Share Url</div>
    <br>
    <div id="remove" style="margin-top: 5px;" class="x2-button">Remove Share Url</div>
</div>
