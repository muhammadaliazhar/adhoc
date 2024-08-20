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

/**
 * @author: Justin Toyomitsu <justin@x2engine.com>
 */




$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/x2sign.js?123456789');
Yii::app()->clientScript->registerCss('contactRecordViewCss', "

#content {
    background: none !important;
    border: none !important;
}
");

Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');

include("protected/modules/x2sign/x2signConfig.php");

$actionMenuViewItem = RecordViewLayoutManager::getViewActionMenuListItem ($model->id);
if (isset ($actionMenuViewItem['url'])) unset ($actionMenuViewItem['url']);

$menuItems = array(
    array('label' => Yii::t('module', 'X2Sign Home'), 'url' => array('index')),
    array('label'=>Yii::t('module', 'X2Sign Report'), 'url'=>array('x2sign/report')),
    $actionMenuViewItem,
);

if($model->hostedEnvelope==1){
    $signLinks=Yii::app()->db->createCommand()
    ->select('key')
    ->from('x2_sign_links')
    ->where('envelopeId=:envelopeId AND signedDate is null', array(':envelopeId' => $model->id))
    ->queryAll();
    if(!empty($signLinks)){
        $menuItems[] = array('label' => "Hosted Sign", 'url' => array('x2sign/signDocs', 'key'=>$signLinks[0]['key'],'inPerson'=>1));
    }
}

//if ($model->failed || $model->status == $model::WAITING_FOR_OTHERS)
//    $menuItems[] = array('label' => Yii::t('x2sign', 'Resend Emails'), 'url' => '#', 'linkOptions' => ['onclick' => "$.post(yii.baseUrl+'/index.php/x2sign/resend/$model->id').done((data)=>{alert(data);location.reload()})"]);

if ($model->failed)
    $menuItems[] = array('label' => Yii::t('x2sign', 'Resend Failed Emails'), 'url' => '#', 'linkOptions' => ['onclick' => "$.post(yii.baseUrl+'/index.php/x2sign/resend/$model->id').done((data)=>{alert(data);location.reload()})"]);


if($model->status == $model::FINISH_LATER)
    $menuItems[] = array('label' => Yii::t('x2sign', 'Finish Envelope'), 'url' => array('quickSetupRecipients', 'id' => $model->id));

if($model->status != $model::FINISH_LATER)
    $menuItems[] = array('label' => Yii::t('module', 'Clone {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('cloneAndSend', 'id' => $model->id));


if($model->status == $model::COMPLETED)
    $menuItems[] = array('label' => Yii::t('x2sign', 'Download Certificate'), 'url' => array('certificate', 'id' => $model->id));

if($model->status != $model::CANCELLED) {
    $menuItems[] = array('label' => Yii::t('module', 'Edit & Resend {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('editAndSend', 'id' => $model->id));
    $menuItems[] = array('label' => Yii::t('module', 'Cancel {X}', array('{X}' => Modules::itemDisplayName())), 'url' => '#', 'linkOptions' => array('submit' => array('cancel', 'id' => $model->id)));
}

if ($model->status == $model::COMPLETED && Yii::app()->params->isAdmin) {
    //$menuItems[] = array('label' => Yii::t('x2sign', 'Regenerate Envelope'), 'url' => array('regen', 'id' => $model->id));
    $menuItems[] = array('label' => Yii::t('x2sign', 'Regenerate Signature'), 'url' => array('regenSig', 'id' => $model->id));
}



$this->actionMenu = $this->formatMenu($menuItems, array ('X2Model' => $model));

$modelType = json_encode("X2SignEnvelopes");
$modelId = json_encode($model->id);
$email = json_decode($model->email);
$subject = isset($model->emailSubject) ? $model->emailSubject : '';
$body = isset($model->emailBody) ? $model->emailBody : '';
$body = str_replace("\n", "<br>", $body);

Yii::app()->clientScript->registerScript('widgetShowData', "
    $(function() {
        $('body').data('modelType', $modelType);
        $('body').data('modelId', $modelId);
    });
");
?>
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
        <div class="page-title x2sign">
            <h2 style="display: inline-block; width: 700px; text-overflow: ellipsis; overflow: hidden;">
                <?php echo Yii::t('module', 'View {X}', array('{X}' => Modules::itemDisplayName())); ?>: <?php
                echo $model->renderAttribute ('name');
                ?>
            </h2>
            <?php
                echo X2Html::editRecordButton($model);
                echo X2Html::emailFormButton();
                if(isset($model) && $model->status == 1)
                    echo X2Html::signX2SignButton($model);
                echo X2Html::inlineEditButtons();
                echo X2Html::X2SignClone($model->id);
                if ($model->failed || $model->status == $model::WAITING_FOR_OTHERS)
                    echo X2Html::X2SignResend($model->id, $subject, $body); 
                if ($model->status != $model::CANCELLED)
                    echo X2Html::X2SignEdit($model->id);
            ?>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
            <?php 
            $this->widget ('DetailView', array(
                'model' => $model
            ));

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
    'associationType' => 'x2sign',
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
<?php
$this->widget('X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'model' => $model,
    ));
