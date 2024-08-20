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
 * X2FlowAction that adds a comment to a record
 *
 * @package application.components.x2flow.actions
 */
class X2FlowX2SignSend extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Send X2Sign';
    public $info = 'Send a X2Sign envelope to a contact or related contact';

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        $assignmentOptions = array('None' => '{' . Yii::t('studio', 'None') . '}') + array('{assignedTo}' => '{' . Yii::t('studio', 'Owner of Record') . '}') + 
                X2Model::getAssignmentOptions(false, true);
        $realAssignmentOptions = array('{assignedTo}' => '{' . Yii::t('studio', 'Owner of Record') . '}') + 
                X2Model::getAssignmentOptions(false, true);
        $sendToOptions = array('{this}' => '{' . Yii::t('studio', 'This Record') . '}');
        $sendToOptions['None'] = Yii::t('studio', 'None');
        //get all the related fields that are contacts
        $contactFields = Fields::model()->findAllByAttributes(array('type' => 'link', 'linkType' => 'Contacts'));
        $linkFields = array();
        foreach($contactFields as $field){
            $linkFields[$field->id] = $field->modelName . ":" . $field->attributeLabel;
        }
        $linkFields = $sendToOptions + $linkFields;
        if(Yii::app()->isInSession){
            $credOptsDict = Credentials::getCredentialOptions(null, true);
            $credOpts = $credOptsDict['credentials'];
            $selectedOpt = $credOptsDict['selectedOption'];
            foreach($credOpts as $key => $val){
                if($key == $selectedOpt){
                    $credOpts = array($key => $val) + $credOpts; // move to beginning of array
                    break;
                }
            }
        }else{
            $credOpts = array();
        }
        //we should only use docs that are in the docs folder
        
        $criteria = new CDbCriteria();
        $criteria->addCondition('type = "x2signdoc"'); // Need to figure out another way to check for sign docs in the X2SignTemplates folder since that folder can have child folders
        $data = Docs::model()->searchBase($criteria, 2000, false, true)->getData();
        //now we make an array of the ids
        $docids = array_map(function($e){return $e->id;}, $data);
        $idString = "(" . implode(",", $docids) . ")";       


        $x2signDocs = X2SignDocs::model()->findAllByAttributes(array(), "docId in " . $idString);
        $signOptions = array();
        foreach($x2signDocs as $doc){
            $signOptions[$doc->id] = $doc->name;
        }
        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelRequired' => 1,
            'options' => array(
                array(
                    'name' => 'envName',
                    'label' => Yii::t('studio', 'Name'),
                    'type' => 'varchar',
                ),
                array(
                    'name' => 'from', 
                    'label' => Yii::t('studio', 'Send As:'),
                    'type' => 'dropdown',
                    'options' => $credOpts
                ),
                array(
                    'name' => 'assignedTo', 
                    'label' => Yii::t('studio', 'Envelope Assignment'),
                    'type' => 'dropdown',
                    'options' => $realAssignmentOptions
                ),
                array(
                    'name' => 'subject',
                    'label' => Yii::t('studio', 'Subject'),
                    'type' => 'varchar',
                ),
                array(
                    'name' => 'template',
                    'label' => Yii::t('studio', 'Email Template'),
                    'type' => 'dropdown',
                    'defaultVal' => '',
                    'options' => array('' => Yii::t('studio', 'Custom')) +
                        Docs::getEmailTemplates('email')
                ),
                array(
                    'name' => 'body',
                    'label' => Yii::t('studio', 'Email Message'),
                    'optional' => 1,
                    'type' => 'richtext'
                ),
                array(
                    'name' => 'Firstsigner', 
                    'label' => Yii::t('studio', 'First Signer'),
                    'type' => 'dropdown',
                    'options' => $linkFields
                ),
                array(
                    'name' => 'Secondsigner', 
                    'label' => Yii::t('studio', 'Second Signer'),
                    'type' => 'dropdown',
                    //'options' => $assignmentOptions
                    'options' => $linkFields
                ),
                array(
                    'name' => 'SignDoc', 
                    'label' => Yii::t('studio', 'Sign Doc'),
                    'type' => 'dropdown',
                    'options' => $signOptions
                ),
            )
        ));
    }
    
    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        //make the envelope
        $x2SginEnv = new X2SignEnvelopes;
        $mainModel = $params['model'];
        $options = $this->config['options'];
        //First get the Cred
        $emailCred = $this->parseOption('from', $params);
        $subject = $this->parseOption('subject', $params);
        //assigned the envelope
        if($options['assignedTo']['value'] == "{assignedTo}"){
            $x2SginEnv->assignedTo = $mainModel->assignedTo;
        }else{
            $assignedTo = $this->parseOption('assignedTo', $params);
            $x2SginEnv->assignedTo = $assignedTo;
        }
        $x2SginEnv->name = $this->parseOption('envName', $params);
        //$x2SginEnv->name = "workFlowSend " . time();
        $x2SginEnv->prepared = 0;
        $x2SginEnv->sequential = 1;

        $x2SginEnv->signDocIds =  json_encode(array($options['SignDoc']['value']));
        $x2SginEnv->save();
        //make the signLink
        $signLink = new X2SignLinks;
        $contactRecord;
        if($options['Firstsigner']['value'] == "{this}"){
            $contactRecord = $mainModel;
        }else{
            //get the related contact
            $contactField = Fields::model()->findByPk($options['Firstsigner']['value']);
            $fieldName = $contactField->fieldName;
            $ContactNameId = $mainModel->$fieldName;
            $contactRecord = Contacts::model()->findByAttributes(array("nameId" => $ContactNameId));
        }
        //check to make sure the contact got set
        if(empty($contactRecord) || get_class($contactRecord) != "Contacts")
               return array(false, Yii::t('app', 'Record to send to was not set or not a contact'));
        //get sender 
        $email = Credentials::model()->findByPk($options['from']['value']);
        if(empty($email))
            throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
        else
            $email = $email->auth->email;
        $x2SginEnv->sender = $email;
        $x2SginEnv->save();
        
        $signLink = new X2SignLinks;
        $signLink->setAttributes(array(
                        'signRequired' =>  1,
                        'modelType' => get_class($contactRecord),
                        'envelopeId' => $x2SginEnv->id,
                        'position' => 1,
                        'modelId' => $contactRecord->id,
                        'createDate' => time(),
                        'key' => $signLink->getKey(),
                        'emailAddress' => $contactRecord->email
                    ), false);
        $signLink->save();
        //make a second link if needed
        if($options['Secondsigner']['value'] != 'None'){
            if($options['Secondsigner']['value'] == "{this}"){
                $contactRecord = $mainModel;
            }else{
                //get the related contact
                $contactField = Fields::model()->findByPk($options['Secondsigner']['value']);
                $fieldName = $contactField->fieldName;
                $ContactNameId = $mainModel->$fieldName;
                $contactRecord = Contacts::model()->findByAttributes(array("nameId" => $ContactNameId));
            }
            //check to make sure the contact got set
            if(empty($contactRecord) || get_class($contactRecord) != "Contacts")
                   return array(false, Yii::t('app', 'Record to send to was not set or not a contact'));
            //get sender 
            $email = Credentials::model()->findByPk($options['from']['value']);
            if(empty($email))
                throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
            else
                $email = $email->auth->email;
            $x2SginEnv->sender = $email;
            $x2SginEnv->save();

            $signLink = new X2SignLinks;
            $signLink->setAttributes(array(
                            'signRequired' =>  1,
                            'modelType' => get_class($contactRecord),
                            'envelopeId' => $x2SginEnv->id,
                            'position' => 2,
                            'modelId' => $contactRecord->id,
                            'createDate' => time(),
                            'key' => $signLink->getKey(),
                            'emailAddress' => $contactRecord->email
                        ), false);
            $signLink->save();
            /* $secondLink = new X2SignLinks;
            //get the user
            $userName = $this->parseOption('Secondsigner', $params);
            $userRecord = User::model()->findByAttributes(array('username' => $userName));
            $signLink->setAttributes(array(
                        'signRequired' =>  1,
                        'modelType' => get_class($userRecord),
                        'envelopeId' => $x2SginEnv->id,
                        'position' => 2,
                        'modelId' => $userRecord->id,
                        'createDate' => time(),
                        'key' => $signLink->getKey(),
                        'emailAddress' => $contactRecord->email
                    ), false);
            $secondLink->save(); */
            
        }
        //$subject = '';
        $message = $this->parseOption('body', $params);
        $fails = $x2SginEnv->quickSendEmail($subject, $message);
        if (isset($fails) && count($fails) > 0) throw new CHttpException(500, json_encode($fails));
        return array(true, "");        
    }

}
