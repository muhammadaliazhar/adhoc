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
$submitButton = isset($submitButton) ? $submitButton : true;
$htmlOptions = !isset($htmlOptions) ? array() : $htmlOptions;
$namespace = !isset($namespace) ? null : $namespace;
$form = $this->beginWidget('CalendarEventActiveForm', array(
    'formModel' => $model,
    'htmlOptions' => $htmlOptions,
    'namespace' => $namespace,
        ));
echo $form->textArea($model, 'actionDescription');
?>
<div class="row">
    <div class="cell" style='display: none;'>
        <?php
        if (empty($model->calendarId)) {
            $model->calendarId = Yii::app()->params->profile->defaultCalendar;
        }
        echo $form->label($model, 'calendarId');
        $guest_calendar = array();
        if(isset($_GET['id']) || $model->calendarId != null){
            if($model->calendarId != null){
                $calendarId = $model->calendarId;
            }else{
                $ids = explode(',', $_GET['id']);
                $calendarId = $ids[0];
            }
            $user_chosen_calendar = X2Model::model('Calendar')->findByPk($calendarId);
            $guest_calendar = array($calendarId => $user_chosen_calendar->name);
        }
        //echo $form->dropDownList($model, 'calendarId', X2CalendarPermissions::getEditableUserCalendarNames());
        echo $form->dropDownList($model, 'calendarId', $guest_calendar);
        ?>
    </div>
    <div class="cell" style='display: none;'>
        <?php
            echo $form->label($model, 'allDay');
            echo $form->renderInput($model, 'allDay');
        ?>
    </div>
    <div class="cell" style='display: none;'>
        <?php 
            echo $form->label($model, 'invite');
            echo $form->checkBox($model, 'invite');
        ?>
    </div>
    <div class="cell" style='display: none;'>
        <?php
            echo $form->label ($model, 'reminder');
            echo $form->checkBox($model, 'reminder');
        ?>
    </div>
</div>
<div class='row'>
    <div class='cell'>
        <div class='cell'>
            <?php
            echo $form->dateRangeInput($model, 'dueDate', 'completeDate', array('timeTracker' => false));
            ?>
        </div>
        <div class='cell' style='display: none;'>
            <?php
            echo '<div class="clearfix"></div>';
            echo $form->label($model, 'assignedTo');
            
            $guest_user = array();
            if(isset($_GET['user']) || isset($user_chosen_calendar)){
                if($user_chosen_calendar->createdBy != null){
                    $user = X2Model::model('User')->findByAttributes(array('username' => $user_chosen_calendar->createdBy));
                }else{
                    $user = X2Model::model('User')->findByPk($_GET['user']);
                }
                //echo $form->renderInput($model, 'assignedTo');
                $guest_user = array($user->username => $user->firstName . ' ' . $user->lastName);
            }
            //echo $form->renderInput($model, 'assignedTo');
            echo $form->dropDownList($model, 'assignedTo', $guest_user);
            ?>

        </div>
    </div>
    <div class='cell' style='display: none;'>
        <div class='cell'>
            <?php
            echo $form->label($model, 'priority');
            echo $form->renderInput($model, 'priority');
            echo $form->label($model, 'color');
            echo $form->renderInput($model, 'color');
            ?>
        </div>
        <div class='cell'>
            <?php
            echo $form->label($model, 'eventSubtype');
            echo $form->renderInput($model, 'eventSubtype');
            echo $form->label($model, 'eventStatus');
            echo $form->renderInput($model, 'eventStatus');
            
            echo $form->label($model, 'visibility');
            echo $form->renderInput($model, 'visibility');
            ?>
        </div>
        <div class='cell'>
            <?php
            if (empty($model->associationId)) {
                echo $form->label($model, 'associationType');
                echo $form->renderInput($model, 'associationType');
            } else {
                echo $form->hiddenField($model, 'associationType');
                echo $form->hiddenField($model, 'associationId');
                echo $form->hiddenField($model, 'associationName');
            }
            echo CHtml::hiddenField('modelName', 'calendar');
            ?>
        </div>
    </div>
</div>
<div id="email-invites" class="row" style="display:none;">
    <br>
    <div class="cell" style="width:100%; max-width:640px;">
        <?php 
        $model->emailAddresses = $email;
        echo $form->label($model,'emailAddresses');
        echo $form->textArea($model, 'emailAddresses');
        ?>
    </div>
</div>
<?php echo $model->renderReminderConfig(); ?>

<?php if (CCaptcha::checkRequirements()) { ?>
<div class="row captcha-row" style="border-style: groove;">
    <div id="captcha-container">
    <?php
        $this->widget('CCaptcha', array(
		      'captchaAction' => '/actions/actions/captcha',
                      'clickableImage' => true,
                      'showRefreshButton' => false,
                      'imageOptions' => array(
                           'id' => 'captcha-image',
                           'style' => 'display:block;cursor:pointer;',
                      )
        ));
    ?>
    </div>
    <p class="hint"><?php echo Yii::t('app', 'Please enter the letters in the image above.'); ?></p>
    <?php echo $form->textField($model, 'verifyCode'); ?>
</div>
<?php } ?>

<?php
if ($submitButton)
    echo $form->submitButton();
$this->endWidget();
Yii::app()->clientScript->registerScript('email-invites',"
    $('#CalendarEventFormModel_invite').on('click',function(){
        if($(this).is(':checked') && $('#email-invites').is(':hidden')){
            $('#email-invites').slideDown();
        } else if (!$(this).is(':checked') && $('#email-invites').is(':visible')){
            $('#email-invites').slideUp();
        }
    });
    $('#CalendarEventFormModel_reminder').on('click',function(){
        if($(this).is(':checked') && $('.reminder-config').is(':hidden')){
            $('.reminder-config').slideDown();
        } else if (!$(this).is(':checked') && $('.reminder-config').is(':visible')){
            $('.reminder-config').slideUp();
        }
    });
");
?>
