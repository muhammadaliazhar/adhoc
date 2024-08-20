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






Yii::import('application.models.embedded.*');

/**
 * Authentication data for using an Outlook account to send email.
 *
 * Similar to EmailAccount but with certain details already filled in
 * @package application.models.embedded
 */
class OutlookEmailAccountOauth2 extends EmailAccount {

    public $email = '';
    public $imapNoValidate = false;
    public $imapPort = 993;
    public $imapSecurity = 'ssl';
    public $imapServer = 'imap-mail.outlook.com';
    public $password = '';
    public $port = 25;
    public $security = 'tls';
    public $senderName = '';
    public $server = 'smtp-mail.outlook.com';
    public $user = '';
    public $redirectUri = '';
    public $clientId = '';
    public $clientSecret = '';
    public $refreshToken = '';
    public $accessToken = '';
    public $returnCode = '';
    //have to have 2 tokens covering diffrent scopes since graph and imap are not under the same scope
    public $IMAPrefreshToken = '';
    public $IMAPaccessToken = '';
    public $IMAPreturnCode = '';

    public function modelLabel() {
        return Yii::t('app','Outlook Email Account with Oauth2');
    }

    public function attributeLabels(){
        return array(
            'senderName' => Yii::t('app', 'Sender Name'),
            'email' => Yii::t('app', 'Email address'),
            'server' => Yii::t('app', 'Server'),
            'port' => Yii::t('app', 'Port'),
            'security' => Yii::t('app', 'Security type'),
            'user' => Yii::t('app', 'User name (if different from email address)'),
            'password' => Yii::t('app', 'Password'),
            'smtpNoValidate' => Yii::t('app','Disable SSL Validation'),
            'imapPort' => Yii::t('app','IMAP Port'),
            'imapServer' => Yii::t('app','IMAP Server'),
            'imapSecurity' => Yii::t('app','IMAP Security'),
            'imapNoValidate' => Yii::t('app','Disable SSL Validation'),
            'disableInbox' => Yii::t('app','Disable Email Inbox'),
            'redirectUri' => Yii::t('app','redirectUri'),
            'clientId' => Yii::t('app','clientId'),
            'clientSecret' => Yii::t('app','clientSecret'),
            'refreshToken' => Yii::t('app','Refresh Token'),
            'accessToken' => Yii::t('app','Access Token'),
            'returnCode' => Yii::t('app','Return Code'),            
            'IMAPrefreshToken' => Yii::t('app','Refresh Token'),
            'IMAPaccessToken' => Yii::t('app','Access Token'),
            'IMAPreturnCode' => Yii::t('app','Return Code'),
        );
    }
    
    /**
     * Generate the form for the embedded model
     */
    public function renderInputs() {
        echo Yii::t('app', 'Before set up please reach out to your admin to ensure your outlook intergration is updated for email.');
        $this->password = null;
        echo CHtml::activeLabel ($this, 'senderName');
        $this->renderInput ('senderName');
        echo CHtml::activeLabel ($this, 'email');
        $this->renderInput ('email');
        echo CHtml::activeLabel ($this, 'user');
        $this->renderInput ('user');
        echo CHtml::activeLabel ($this, 'password');
        $this->renderInput ('password');

        echo CHtml::errorSummary($this);
    }
    
    
    // Hide SMTP SSL validation setting
    public function renderSmtpSslValidation() { }
    
    
    
    
    public function rules() {
        return array(
            array('port','numerical','integerOnly'=>1,'min'=>1),
            array('email','email'),
            array('user','emailUser'),
            array('server,user,email','length','min'=>1,'max'=>500,'allowEmpty'=>0),
            array('password','required'),   
            array('senderName,server,port,security,user,email,password,imapPort,imapServer,imapSecurity,smtpNoValidate,imapNoValidate,disableInbox,redirectUri,clientId,clientSecret,refreshToken,accessToken,returnCode,IMAPrefreshToken,IMAPaccessToken,IMAPreturnCode','safe'),
        );
    }
    
    
    
}

?>

