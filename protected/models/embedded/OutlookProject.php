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






class OutlookProject extends JSONEmbeddedModel implements AdminOwnedCredentials {

    public static function getAdminProperty () {
        return 'outlookCredentialsId'; 
    }

    /**
     * @var string Used for OAuth 2.0 (needed for Calendar sync and login)
     */
    public $outlookId = '';

    /**
     * @var string Used for OAuth 2.0 (needed for Calendar sync and login)
     */
    public $outlookSecret = '';

     
    /**
     *  @var string Used for Google+ and Google APIs integration
     */
    public $apiKey = '';
    
    /**
     *  @var string the path for the Service Account key file used for Google+ and Google APIs integration
     */
    public $serviceAccountKeyFileContents = '';
    
    /**
     *  @var string project id used for Google+ and Google APIs integration
     */
    public $projectId = '';

    // pseudo-attributes which belong to Admin. These don't get enrypted with the other attributes,
    // but do get rendered in the same form
    private $_gaTracking_internal;
    private $_gaTracking_public;

    public function getProtectedFields () {
        return array ('outlookId', 'outlookSecret', 'apiKey', 'serviceAccountKeyFileContents', 'projectId');
    }

    public function renderForm () {
        Yii::app()->controller->renderPartial (
            'application.views.profile._outlookProjectForm', array (
                'model' => $this
            ));
    }

    public function getMetaData () {
        return array (
            'private' => 1,
            'userId' => Credentials::SYS_ID,
            'name' => 'Outlook project',
        );
    }

    public function rules(){
        return array(      
            array('apiKey', 'safe'),          
            array('projectId', 'safe'),         
            array ('serviceAccountKeyFileContents', 'safe'),          
            array('outlookId,outlookSecret', 'safe'),
        );
    }

    /**
     * Ensure that if one of the OAuth 2.0 fields are set, both are
     */
//    public function validateOAuthCreds ($attr) {
//        if ($this->$attr === null) return;
//        if ($attr === 'clientSecret' && $this->clientId === null) {
//            $this->addError ('clientId', Yii::t('app', 'Field required'));
//        } elseif ($attr === 'clientId' && $this->clientSecret === null) {
//            $this->addError ('clientSecret', Yii::t('app', 'Field required'));
//        }
//    }

    public function setGaTracking_internal ($gaTracking_internal) {
        $this->_gaTracking_internal = $gaTracking_internal;
    }

    public function setGaTracking_public ($gaTracking_public) {
        $this->_gaTracking_public = $gaTracking_public;
    }

    public function getGaTracking_public () {
        return $this->_gaTracking_public;
    }

    public function getGaTracking_internal () {
        return $this->_gaTracking_internal;
    }

    public function getPageTitle () {
        return $this->modelLabel ();
    }

    public function modelLabel() {
        return Yii::t('app','Outlook Integration');
    }

    public function attributeLabels(){
        return array(
            'outlookId' => Yii::t('app','Outlook ID'),
            'outlookSecret' => Yii::t('app','Outlook Secret'),
             
            'apiKey' => Yii::t('app','API Key'),
            'serviceAccountKeyFileContents' => Yii::t('app','Service Account File Key'),
            'projectId' => Yii::t('app','Google API Project ID'),
             
            'gaTracking_public' => Yii::t('app','Outlook Analytics Property ID (public)'),
            'gaTracking_internal' => Yii::t('app','Outlook Analytics Property ID (internal)'),
        );
    }

    public function htmlOptions ($name, $options=array ()) {
        return X2Html::mergeHtmlOptions (
            parent::htmlOptions ($name, $options), array ('class' => 'outlook-credential-input'));
    }

}

?>
