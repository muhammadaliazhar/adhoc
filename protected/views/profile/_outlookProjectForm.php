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






Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/views/profile/googleProjectForm.css');

echo CHtml::openTag ('div', array ('id' => 'outlook-project-form'));

$admin = Yii::app()->settings;
echo CHtml::activeCheckbox ($admin, 'outlookIntegration');
echo CHtml::activeLabel ($admin, 'outlookIntegration', array('style'=>'display:inline;'));
echo '<br>';
echo '<br>';
?>
<div class='integration-description'>
<?php
echo Yii::t('app', 'Activating Outlook Integration enables the following features:');
echo X2Html::unorderedList (array (
    CHtml::encode (Yii::t('app', 'Outlook Calendar sync'))
));

?>
</div>
<?php

echo CHtml::tag ('h3', array (), Yii::t('app', 'SETUP Outlook Integration'));
?>
<hr>
<?php
echo X2Html::orderedList (array (
    Yii::t('app', 'Visit {here} and sign in.', array (
        '{here}' =>
            '<a href="https://portal.azure.com">portal.azure.com</a>'
    )),
    Yii::t('app', 'Under Azure services, click on <b>"App Registration"</b>'),
    Yii::t('app', 'Create a <b>new registration</b>, or select an <b>existing one</b>.').
        X2Html::orderedList(array (
            Yii::t('app', '<b><font color="red">If new registration</font></b>, enter a name and leave the other choices default for now.')
        )),
        Yii::t('app', 'On the <b>left panel</b>, navigate to <b>"Authentication"</b>.').
        X2Html::orderedList (array (
            CHtml::encode (
                Yii::t('app', 'Set Redirect URL to the url:')).
                CHtml::tag (
                    'textarea', array ('readonly' => 'readonly', 'style' => 'display: block', 'class'=>'authorized-js-origins'),
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
                        Yii::app()->controller->createUrl('/calendar/outlooksync')."\n"
                ),
            CHtml::encode (
                Yii::t('app', 'Set Logout URL to the url:')).
                CHtml::tag (
                    'textarea', array ('readonly' => 'readonly', 'style' => 'display: block'),
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
                ),
        ), array ('style' => 'list-style-type: lower-latin;')),
        Yii::t('app', 'On the <b>left panel</b> nagivate to <b>"API permissions"</b>.').
        X2Html::orderedList (array (
                Yii::t('app', 'Delegated Permissions: <font color="blue">Calendars.ReadWrite</font>, <font color="blue">User.ReadWrite</font>.'),
                Yii::t('app', 'Application Permissions: <font color="blue">Calendars.ReadWrite</font>, <font color="blue">Mail.ReadWrite</font>, <font color="blue">Mail.Send</font>.'),
        ), array ('style' => 'list-style-type: lower-latin;')),
), array ('class' => 'config-instructions'));
echo '<hr>';

/**
 * Link to X2CRM page.
 */
echo CHtml::tag ('h3', array (), Yii::t('app', 'Link to X2CRM'));
echo '<hr>';
echo X2Html::orderedList (array (
    Yii::t('app', 'Navigate to the <b>overview</b>.').
    X2Html::orderedList(array (
        Yii::t('app', 'Save the <b><font color="red">Application ID</font></b> in the <b><font color="red">Outlook ID</font></b> field below.')
    ), array ('style' => 'list-style-type: lower-latin;')),
    Yii::t('app', 'On the <b>left panel</b>, nagivate to <b>"Certificates and secrets"</b>.').
    X2Html::orderedList(array (
        Yii::t('app', 'Generate a new client secret and save it in the <b><font color="red">Outlook Secret</font></b> field below.')
    ), array ('style' => 'list-style-type: lower-latin;')),
), array ('class' => 'config-instructions'));

echo X2Html::fragmentTarget ('oauth-2.0');
echo CHtml::tag ('h3', array (
    'class' => 'oauth-header'
), Yii::t('app', 'OAuth 2.0 Credentials'));
echo X2Html::hint2 (
    Yii::t('app', 'Needed for Outlook Calendar sync, Microsoft login.'));
echo '<hr />';

//clientId -> outlookId
echo CHtml::activeLabel($model, 'outlookId');
$model->renderProtectedInput ('outlookId');
echo CHtml::activeLabel($model, 'outlookSecret');
$model->renderProtectedInput ('outlookSecret');

echo CHtml::errorSummary($model);
echo '<br>';
echo '<br>';

echo CHtml::closeTag ('div', array ('id' => 'outlook-project-form'));
?>
