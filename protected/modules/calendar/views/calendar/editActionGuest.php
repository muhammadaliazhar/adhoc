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
?>

<?php
$users = User::getNames();
$form = $this->beginWidget('CActiveForm', array(
    'enableAjaxValidation' => false,
        ));
?>

<style type="text/css">
    .dialog-label {
        font-weight: bold;
        display: block;
    }
    .cell {
        float: left;
    }
    .dialog-cell {
        padding: 5px;
    }
    #schedule-confirm {
        height: 92px;
        width: 70%;
        padding: 5px;
    }
    #schedule-first {
        padding: 5px 0px 5px 0px;
        font-size: 13px;
    }
    .event-delete-button {
        display: none !important;
    }
    .event-copy-button {
        display: none !important;
    }
</style>


<div class="row">
    <div class="text-area-wrapper">
        <?php echo $form->textArea($model, 'actionDescription', array('readOnly' => 'readOnly', 'rows' => 3, 'cols' => 40, 'onChange' => 'giveSaveButtonFocus();')); ?>
    </div>
</div>

<div class="row">
    <div class="cell dialog-cell">
        <?php
        echo $form->label($model, ($isEvent ? 'startDate' : 'dueDate'), array('class' => 'dialog-label'));
        $defaultDate = Formatter::formatDate($model->dueDate, 'medium');
        $model->dueDate = Formatter::formatDateTime($model->dueDate); //format date from DATETIME
        Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
        $this->widget('CJuiDateTimePicker', array(
            'model' => $model, //Model object
            'attribute' => 'dueDate', //attribute name
            'mode' => 'datetime', //use "time","date" or "datetime" (default)
            'options' => array(
                'dateFormat' => Formatter::formatDatePicker('medium'),
                'timeFormat' => Formatter::formatTimePicker(),
                'defaultDate' => $defaultDate,
                'ampm' => Formatter::formatAMPM(),
            ), // jquery plugin options
            'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
            'htmlOptions' => array(
                'onClick' => "$('#ui-datepicker-div').css('z-index', '10020');", // fix datepicker so it's always on top
                'id' => 'dialog-Actions_dueDate',
                'readonly' => 'readonly',
                'onChange' => 'giveSaveButtonFocus();',
            ),
        ));
        if ($isEvent) {
            echo $form->label($model, 'endDate', array('class' => 'dialog-label'));
            $defaultDate = Formatter::formatDate($model->completeDate, 'medium');
            $model->completeDate = Formatter::formatDateTime($model->completeDate); //format date from DATETIME
            $this->widget('CJuiDateTimePicker', array(
                'model' => $model, //Model object
                'attribute' => 'completeDate', //attribute name
                'mode' => 'datetime', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker('medium'),
                    'timeFormat' => Formatter::formatTimePicker(),
                    'defaultDate' => $defaultDate,
                    'ampm' => Formatter::formatAMPM(),
                ), // jquery plugin options
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                'htmlOptions' => array(
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '10020');", // fix datepicker so it's always on top
                    'id' => 'dialog-Actions_startDate',
                    'readonly' => 'readonly',
                    'onChange' => 'giveSaveButtonFocus();',
                ),
            ));
        }
        ?>
    </div>
    <div class="cell" id="schedule-confirm">
        <div id="schedule-first">
            <center> Schedule an <font color="red">Appointment</font> for this Day? </center>
            <center> Enter Your <font color="red">Email</font>: <input name="customer_email"> </center>
            <center> Enter Your <font color="red">Full Name </font>: <input name ="customer_fullName"> </center>
        </div>
    </div>
    <input type="hidden" name="isEvent" value="<?php echo $isEvent ?>">
</div>

<?php $this->endWidget(); ?>
