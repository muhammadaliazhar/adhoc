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
yii::setPathOfAlias('Custom',Yii::app()->request->BaseUrl.'/custom/protected/');
Yii::import('Custom.models.X2Model');

class X2SignBehaviour extends CBehavior
{
    //RT customizations start
    //function to send email and make x2sign envelope automatically
    public function createX2SignAutomatically($buyerData) {
        if(filter_var($buyerData['assignTo'], FILTER_VALIDATE_EMAIL)){
             $buyerData['assignTo']=User::model()->findByAttributes(array('username'=>$buyerData['assignTo']))->username;
        }
        $customDir = str_replace('/protected', '/custom/protected', Yii::app()->basePath);
        $controllerFile = $customDir . '/modules/x2sign/models/X2SignEnvelopes.php';
        require_once $controllerFile;
        $mediaFileName;
        $mediaId;
        $listId = Listings2::model()->findByAttributes(array ('nameId' => $buyerData['listing']));
        $media = Media::model ()->findAllByAttributes (array ('associationType' => 'Listings2','associationId'=>$listId->id,//'uploadedBy'=>$buyerData['assignTo'],
                'c_document_type'=>'NDA'));
        //adding No NDA check
        $ndaConfigure = null;

        if($listId->c_NDAauto != "Yes" && $listId->c_NDAauto != 1){
            Yii::log("Listings set to not send",'trace','application.api');

        }

        //since only listing NDA matters if no listings nda just quict
        if(empty($media)){
            Yii::log("No listings NDA set up can't send",'trace','application.api');        


        }

        //this admin area is not well done so adding a false to it to just skip it
        if(Yii::app()->params->isAdmin && false){
            if(!isset($media)){
                $prevMedia=Yii::app()->db->createCommand()
                ->select('id,fileName')
                ->from('x2_media')
                ->where('associationType=:associationTypeParam AND associationId=:associationIdParam AND . ' //uploadedBy=:upload AND
                      . ' c_document_type=:document', array(':associationTypeParam' => 'Listings2',':associationIdParam'=>$listId->id,//':upload'=>$buyerData['assignTo'],
                ':document'=>'PREV'))
                ->order('lastUpdated DESC')
                ->queryRow();
                if(!isset($prevMedia['id'])){
                       Yii::log("Document Not Found :: Admin Cannot Send NDA Document",'trace','application.api');
                       return;
                }   
                Yii::log("Got media for SignDoc",'trace','application.api'); 
                $mediaId=$prevMedia['id'];
                $mediaFileName=$prevMedia['fileName'];
            }
            else{
                Yii::log("Got media for SignDoc",'trace','application.api'); 
                $mediaId=$media->id;
                $mediaFileName=$media->fileName;
            }
        }else{
            //if($listId->assignedTo!=$buyerData['assignTo']){
            //     Yii::log("UnAuthorized User :: Cannot Send NDA Document:: Listings assignedTo is Different",'trace','application.api');
            //     return;
            //}
            if(empty($media)){
                $employee = Employees::model()->findByAttributes(array ('c_user__c' =>$buyerData['assignTo'] ));
                //check for an NDA automation first then worry about the next 2 lines
                $ndaConfigure = Ndaconfigure::model ()->findByAttributes (array ('c_employee' =>$employee->nameId));
                if(!empty($ndaConfigure)){
                    $x2signArray = json_decode($ndaConfigure->c_default_nda);
                    //now get the all the media files and put them in an array
                    $media = array();
                    foreach($x2signArray as $x2id){
                        $ndaDoc=X2SignDocs::model()->findByPk($x2id);
                        $media[] = Media::model()->findByPk($ndaDoc->mediaId);

                    } 
                }else{
                    //now check the franchise        
                    $franchise = Franchisees::model()->findByAttributes(array ('nameId' =>$employee->c_franchisee__c));
                    $media = Docs::model ()->findByAttributes (array ('nameId' =>$franchise->c_default_nda));
                    if(!isset($media)){
                        Yii::log("Document Not Found :: Agent Cannot Send NDA Document",'trace','application.api');
                        return;
                    }
                    else{
                        Yii::log("Got media for SignDoc",'trace','application.api');
                        $mediaFile = Media::model ()->findByAttributes (array ('id' =>$media->mediaId)); 
                        $mediaId=$mediaFile->id;
                        $mediaFileName=$mediaFile->fileName;
                    }
                }
            }
            else{
                Yii::log("Got media for SignDoc",'trace','application.api'); 
                //$mediaId=$media->id;
                //$mediaFileName=$media->fileName;
            }
        }
        $envelope = $this->quickSend($buyerData['assignTo']);
        //here I will set if the listing wants us to send a BLI after
        if($listId->c_BLIauto)
            $envelope->BLIsendInfo = json_encode(array("listId" => $listId->id));
        $envelope->save();
        $recipients = [
            0 =>
            [
                "firstName" => $buyerData["fname"],
                "lastName" => $buyerData["lname"],
                "email" => $buyerData["email"],
                "displayModel" => "Buyers",
                "hiddenModel" => "",
                "hiddenId" => "",
                "order" => "1",
                "viewer" => "false",
                "assignedTo"=>$buyerData['assignTo'],
                "c_listinglookup__c"=>$listId->nameId,
                "phone"=>$buyerData["phone"]
            ],

        ];
        $signDoc = 0;
        $userDoc = 1;
        // get the first from list of similar buyers
        //$result = $this->getListQuick('viewers', 'Contacts', '', 'Buyers', $recipients[0]['firstName'], $recipients[0]['lastName'], $recipients[0]['email']);
        //get the matched record with respect to matched id
        //$matchedRecord = $this->getQuickRecord('Buyers', $result['modelId']);
        /*if ($matchedRecord) {
            $recipients = [
                0 =>
                ["firstName" => $matchedRecord['firstName'],
                    "lastName" => $matchedRecord['lastName'],
                    "email" => $matchedRecord['email'],
                    "displayModel" => "Buyers",
                    "hiddenModel" => $matchedRecord['modelType'],
                    "hiddenId" => $matchedRecord['modelId'],
                    "order" => "1",
                    "viewer" => "false",
                    "assignedTo"=>$buyerData['assignTo'],
                    "c_listinglookup__c"=>$listId->nameId,
                    "phone"=>$buyerData["phone"]
                    ]
            ];
        }*/

        $fields = [
            //"name" => $mediaFileName,
            "template" => "false",
            "fieldInfo" =>
            [
                0 => [
                    "id" => "Signature-1",
                    "page" => "1",
                    "top" => "4px",
                    "left" => "725px",
                    "width" => "90",
                    "height" => "30",
                    "req" => "1",
                    "read-only" => "0",
                    "recip" => "1"],
                1 => [
                    "id" => "Name-2",
                    "page" => "1",
                    "top" => "10px",
                    "left" => "10px",
                    "value" => $buyerData["fname"].' '.$buyerData["lname"],
                    "width" => "138",
                    "height" => "30",
                    "req" => "0",
                    "read-only" => "0",
                    "recip" => "1"]
            ],
            "displayModel" => "Buyers",
            "hiddenModel" => "",
            "hiddenId" => "",
            "order" => "1",
            "viewer" => "false",
            //"mediaId"=>$mediaId
        ];

        //needed to add code incase the media is an array
        if(is_array($media)){
            $docIds = array();
            foreach($media as $med){
                $signDoc=X2SignDocs::model()->findByAttributes(array('mediaId'=>$med->id));
                $docIds[] = $signDoc->id;

            }
        }else{
            $signDoc=X2SignDocs::model()->findByAttributes(array('mediaId'=>$mediaId));
            //sign doc will be created for storing information regarding document
            $fields["name"] = $mediaFileName;
            $fields["mediaId"] = $mediaId;
            $signDocId = $this->createSignable($fields);
            $docIds = [0 => $signDoc->fieldInfo?$signDoc->id:$signDocId];
        }
        //document will be added
        $this->addOldTemps($envelope->id, $docIds);
        //the recipient's properties will be set
        $this->quickSetupTemplate($envelope->id, $signDoc, $userDoc, $recipients);
        //without recipient
        $signDocNum = $this->quickSetupTemplate($envelope->id);
        // verify Buyer Check
        $contact = Contacts::model()->findByAttributes(array('email' => $buyerData['email']));    
        if(!$contact->c_verify_buyer){
            // Notification to verify the new  buyer in crm
            $this->notifyAgent($contact,CSTM_EMAIL_NOTIFICATION_TEMPLATE);                             
            return;
        }
        //params with fieldinfo will be passed
        //$this->quickSetupEmail($envelope->id,$signDocNum,$fields,$signDocId);
        $this->quickEmailView($envelope->id);
        $emailContent = [
                    'subject' => 'NDA',
                    'message' => '',
                    "delay" => "",
                    "expireDate" => ""
                ];

            //$emailTemplate=Docs::model()->findByAttributes(array('createdBy'=>$buyerData['assignTo'],'type'=>'email'));
            //adding code to checkif we have an nda automation and if so check for email tempate
            if(isset($ndaConfigure))$emailTemplate=Docs::model()->findByAttributes(array('nameId'=>$ndaConfigure->c_NDAEmail));   
            //check to see if the listing has a template, if so use that
            $emailTemplate=Docs::model()->findByAttributes(array('id'=>$listId->autSendEmailTemp));
            if(isset($emailTemplate)){
                $emailContent = [
                    'subject' => $emailTemplate->subject,
                    'message' => $emailTemplate->text,
                    "delay" => "",
                    "expireDate" => ""
                ]; 
            }
            if(Yii::app() instanceof CConsoleApplication) {
                $envelope->c_automation_nda=1;
                $envelope->c_listing=$buyerData['listing'];
                $envelope->signDocIds = json_encode($docIds);
                $envelope->save();
            }else{
                $envelope->c_automation_nda=0;
                $envelope->c_listing=$buyerData['listing'];
                $envelope->signDocIds = json_encode($docIds);
                $envelope->save();
            }
            //envelope id, subject,message
            if($this->quickSendFinish($envelope->id, $emailContent)!=null){
                $inq = Inquiries::model()->findByAttributes(array('c_listing__c'=>$envelope->c_listing,'c_contact__c'=>$contact->nameId));
                $inq->c_NDASent = 'Yes';
                $inq->save();
                return true;
        } else {
            return false;
        }
        
        }

        protected function addOldTemps($id, $docIds=[]) {
            $envelope = X2SignEnvelopes::model()->findByPk((int)$id);
            $envelope->signDocIds = json_encode($docIds);
            $envelope->save();
        }

        protected function quickSend($agent) {
            Yii::log($agent,'trace','application.api');
            $user =  User::model()->findByAttributes(array('username'=>$agent));
            $envelope = new X2SignEnvelopes;
            $envelope->prepared = 0;
            $envelope->sequential = 1;
            $envelope->assignedTo =$user->username;
            $envelope->signDocIds = json_encode(array());
            Yii::log($user->id,'trace','application.api');
            $email = Credentials::model()->findDefault($user->id, 'email');  
            if(empty($email)){
                Yii::log("No default email credentials set. To resolve, create and set email credentials as default.",'trace','application.api');
                throw new CHttpException (403,'No default email credentials set. To resolve, create and set email credentials as default.');
            }
            else
                $email = $email->auth->email;

            $envelope->sender = $email;
            $envelope->name = date('M d, Y: ') . ' Quick Send Doc';
            $envelope->save(false);
            return $envelope;
        }
        /**
         * @param array $recipients The recipients of the envelope
         */
        protected function saveRecipients($recipients, $envelopeId) {
            if (isset($recipients)) {
                if (isset($envelopeId)) {
                    $position = 1;
                    // Delete all previous sign links
                    $signLink = X2SignLinks::model()->deleteAllByAttributes(array('envelopeId' => $envelopeId));
                    foreach($recipients as $recipient) {
                        $contact = NULL;
                        if(empty($recipient['email']))
                            throw new CHttpException(403, "Please enter a email for...");

                        if(empty($recipient['firstName']))
                            throw new CHttpException(403, "Please enter a first name for...");

                        if(empty($recipient['lastName']))
                            throw new CHttpException(403, "Please enter a last name for...");

                        if(isset($recipient['hiddenId']) && !empty($recipient['hiddenModel'])) {
                            $contact;
                            if($recipient['hiddenModel'] == 'Self'){
                                $contact = User::getMe();
                            }
                            else{
                                $contact = $recipient['hiddenModel']::model()->findByPk($recipient['hiddenId']); 
                                if (Yii::app() instanceof CConsoleApplication){
                                    $contact->assignedTo=$recipient['assignedTo'];
                                    $contact->save();     
                                }
                            } 
                        } 
                        else {
                            switch($recipient['displayModel']) {
                                case 'Contacts':
                                    $contact = X2Leads::model()->findByAttributes(array('c_email' => $recipient['email']));
                                    if(isset($contact) && $contact->assignedTo != User::getMe()->username)
                                        unset($contact);
                                    break;
                                case 'Sellers':
                                    $contact = Sellers2::model()->findByAttributes(array('email' => $recipient['email']));
                                    if(isset($contact) && $contact->assignedTo != User::getMe()->username)
                                        unset($contact);
                                    break;
                                case 'Buyers':
                                    $contact = Contacts::model()->findByAttributes(array('email' => $recipient['email']));
                                    break;
                            }
                            if(!isset($contact)) {
                                switch($recipient['displayModel']) {
                                    case 'Contacts':
                                        $contact = new X2Leads;
                                        break;
                                    case 'Buyers':
                                        $contact = new Contacts;
                                        break;
                                    case 'Sellers':
                                        $contact = new Sellers2;
                                        break;
                                }
                                if($recipient['displayModel'] == 'Sellers'){
                                    $contact->setAttributes(array(
                                        'c_leadsource' => 'Quick Send',
                                        'assignedTo' => User::getMe()->username,
                                        'name' => $recipient['firstName'] . " " . $recipient['lastName'],
                                        'email' => $recipient['email'],
                                        'visibility' => 0,
                                    ), false);

                                }else if($recipient['displayModel'] == 'Contacts'){

                                    $contact->setAttributes(array(
                                        'leadSource' => 'Quick Send',
                                        'assignedTo' => User::getMe()->username,
                                        'firstName' => $recipient['firstName'],
                                        'lastName' => $recipient['lastName'],
                                        'c_email' => $recipient['email'],
                                        'visibility' => 0,
                                    ), false);
                                }else{
                                    $buyerAssignedTo=User::getMe()->username;
                                    if (Yii::app() instanceof CConsoleApplication){
                                        $buyerAssignedTo=$recipient['assignedTo'];
                                    }
                                    $contact->setAttributes(array(
                                        'leadSource' => 'BizBuySell',
                                        'assignedTo' =>$buyerAssignedTo,
                                        'firstName' => $recipient['firstName'],
                                        'lastName' => $recipient['lastName'],
                                        'email' => $recipient['email'],
                                        'phone'=>$recipient['phone'],
                                        'c_listinglookup__c'=>$recipient['c_listinglookup__c'],
                                        'visibility' => 0,
                                    ), false);

                                }
                                $contact->save();
                                // Notification for new buyer in crm
                                $this->notifyAgent($contact,56);

                            }
                        }
                        $userOrder = 0;
                        $signLink = new X2SignLinks;
                        $signLink->setAttributes(array(
                            'signRequired' => $recipient['viewer'] == 'true' ? 0 : 1,
                            'modelType' => $recipient['hiddenModel'] == 'Self' ? 'User' : get_class($contact),
                            'envelopeId' => $envelopeId,
                            'position' => $userOrder? $recipient['order'] : $position,
                            'modelId' => $contact->id,
                            'createDate' => time(),
                            'key' => $signLink->getKey(),
                            'emailAddress' => $contact->email
                        ), false);
                        $signLink->save();
                        $position += 1;
                    }
                } else {
                    throw new CHttpException(400, "Bad Request");
                }
            }

            return;
        }

        protected function quickSetupTemplate($id, $signDocNum = 0, $userOrder=0, $recipients=[]) {

            $envelope = X2SignEnvelopes::model()->findByPk($id);
            
            if ($recipients) {
               $this->saveRecipients($recipients, $envelope->id);
               //have to make a link for the user too
               $sendUser = User::model()->findByAttributes(array('username'=>$recipients[0]['assignedTo']));
               $signLink = new X2SignLinks;
               $signLink->setAttributes(array(
                            'signRequired' =>  1,
                            'modelType' =>  'User' ,
                            'envelopeId' => $envelope->id,
                            'position' => 2,
                            'modelId' => $sendUser->id,
                            'createDate' => time(),
                            'key' => $signLink->getKey(),
                            'emailAddress' => $sendUser->emailAddress,
                            'autoSign' => 1 
                        ), false);
                $signLink->save();



            } else {
                if (!is_null($envelope)) {
                    $contacts = array();
                    $signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $envelope->id));
                    //$signLinks = $envelope->getX2SignLinks(array("signRequired" => "1"));
                    $lastDoc = false;
                    $i = 0;
                    foreach($signLinks as $signLink) {
                        $contact = $signLink->modelType::model()->findByPk($signLink->modelId);
                        $contacts[$i] =  array($signLink->emailAddress, $contact->name, $signLink->modelType);
                        $i++;
                    }

                    if ($signDocNum + 1 >= count(json_decode($envelope->signDocIds)))
                        $lastDoc = true;
                    $signDoc = X2SignDocs::model()->findByPk(json_decode($envelope->signDocIds));
                    $doc = Docs::model()->findByPk($signDoc->docId);
                    $pdf = Media::model()->findByAttributes(array('id' => $signDoc->mediaId));
                    $modelsArr = array("Contacts","Sellers2","X2Leads","User");
                    // $AttrsArr = array();
                    // foreach($modelsArr as $modelName){
                    //        $AttrsArr[$modelName] = X2Model::model($modelName)
                    //         ->getFieldsForDropdown(true, false);
                    // }
                    return $signDocNum+1;
                } else {
                    throw new CHttpException(400, "Bad Request");
                }
            }
        }

        protected function getQuickRecord($selModel = 'All Models', $id) {
            $contact = null;
            $email = '';
            if ($selModel == "Buyers") {
                $contact = Contacts::model()->findByPk($id);
                if (!isset($contact)) return false;
                $email = $contact->email;
            }
            $recordsReturn = array(
                'modelId' => $contact->id,
                'modelType' => get_class($contact),
                'email' => $email,
                'firstName'=> $contact->firstName,
                'lastName' => $contact->lastName,
                'assignedTo' => $contact->assignedTo
            );
            return $recordsReturn;
        }

        protected function getListQuick($type, $model='Contacts', $query='', $selModel = 'All Models', $first='', $last='', $email='' ) {
            if ($type == "viewers") {
                $records = [];
                $contactCriteria = new CDbCriteria;
                //$contactCriteria->addCondition('(name LIKE "%' . addslashes($first . ' ' . $last) . '%")');
                $contactCriteria->addCondition('email IS NOT NULL');
                $contactCriteria->addCondition('(email = "' . $email.'")');
                $contactCriteria->addCondition('TRIM(email) <> ""');
                $contactCriteria->mergeWith(
                Contacts::model()->getAccessCriteria('t', Yii::app()->params->modelPermissions, false), "AND");
                $contactList = Contacts::model()->search(100, $contactCriteria)->getData();
                if($selModel == 'All Models' || $selModel == 'Buyers'){
                foreach ($contactList as $contact) {
                    $email = $contact->email;
                    $name = $contact->name ? $contact->name : "$contact->firstName $contact->lastName";
                    $records[] = array(
                        'modelId' => $contact->id,
                        'modelType' => 'Contacts',
                        'viewName' => $name . " - "  . "$email" . " - " . 'Buyer',
                        'firstName'=> $contact->firstName,
                        'lastName' => $contact->lastName,
                        'viewText' => "<b>$name</b>, $email",
                        'assignedTo' => $contact->assignedTo
                    );
                }
            }

                //return $records;

            }
        }

        protected function createSignable($fields=[]) {
            $customDir = str_replace('/protected','/custom/protected',Yii::app()->basePath);
            $controllerFile = $customDir.'/modules/docs/models/DocFolders.php';
            require_once $controllerFile;
            $model = new Docs;
            $signDoc = new X2SignDocs;
            $users = User::getNames();

            if (isset($fields['fieldInfo'])) {
                /*x2customstart*/
                //TWorld x2sign specific
                //Need to have the doc visible in library/docs module
                //$lib = Yii::app()->suModel->getLibrary();
                $me = User::getMe();
                $model->setAttributes(array(
                    'name' => Fields::getPurifier()->purify($fields['name']),
                    'type' => 'x2signdoc',
                    'associationType' => 'X2SignDocs',
                    'text' => '<html><body></body></html>',
                    'assignedTo' => $me->username,
                    'visibility' => 1,  //show up in docs module
                    'folderId' => DocFolders::X2_SIGN_FOLDER_ID,
                ), false);

                if(isset($fields['selectLibrary']) && !fields($_POST['selectLibrary'])){
                    $model->libraryId = $fields['selectLibrary'];

                }else{
                     $model->libraryId = "";
                }


                if($model->save()) {
                    // Saving recipients intended for sign doc will be used to show
                    // only documents for recipients required to sign
                    $recipients = array();
                    foreach($fields['fieldInfo'] as $field) {
                        if(!in_array($field['recip'], $recipients))
                            $recipients[] = $field['recip'];
                    }
                    sort($recipients);

                    $signDoc->setAttributes(array(
                        'name' => $fields['name'],
                        'fieldInfo' => json_encode($fields['fieldInfo']),
                        'mediaId' => $fields['mediaId'],
                        'assignedTo' => $me->username,
                        'recipients' => json_encode($recipients),
                        'docId' => $model->id,
                        'visibility' => 1,
                    ), false);
                    if ($signDoc->save()) {
                        return $signDoc->id;
                    }
                }
            }
        }

        protected function sendUserEmail($addresses, $subject, $message, $attachments = null, $from = null){

            $eml = new InlineEmail();
            if(is_array($addresses) ? count($addresses)==0 : true)
                throw new Exception('Invalid argument 1 sent to x2base.sendUserEmail(); expected a non-empty array, got instead: '.var_export($addresses,1));
            // Set recipients:
            if(array_key_exists('to',$addresses) || array_key_exists('cc',$addresses) || array_key_exists('bcc',$addresses)) {
                $eml->mailingList = $addresses;
            } else
                return array('code'=>500,'message'=>'No recipients specified for email; array given for argument 1 of x2base.sendUserEmail does not have a "to", "cc" or "bcc" key.');
            // Resolve sender (use stored email credentials or system default):
            if($from === null || in_array($from,Credentials::$sysUseId)) {
                $from = (int) Credentials::model()->getDefaultUserAccount($from);
                // Set to the user's name/email if no valid defaults found:
                if($from == Credentials::LEGACY_ID)
                    $from = array('name' => Yii::app()->params->profile->fullName, 'address'=> Yii::app()->params->profile->emailAddress);
            }

            if(is_numeric($from))
                $eml->credId = $from;
            else
                $eml->from = $from;
            // Set other attributes
            $eml->subject = $subject;
            $eml->message = $message;
            $eml->attachments = $attachments;
            //set to or we get an error
            if(empty($eml->to)){
                $eml->to = array($eml->mailingList['to'][0]);

            }

            return $eml->deliver();
        }

        protected function quickSetupEmail($id, $signDocNum = 0,$fields,$signDocId='') {
            //$this->layout = '//layouts/properties';
            $envelope = X2SignEnvelopes::model()->findByPk($id);
            $recipients = array();
            if(isset($fields['fieldInfo'])){

                foreach($fields['fieldInfo'] as $field) {
                    if(!in_array($field['recip'], $recipients))
                        $recipients[] = $field['recip'];
                }
                sort($recipients);
                $temp = 0;
                if($fields['template'] == "true"){
                    $temp = 1;
                }

                $signDoc = X2SignDocs::model()->findByPk($signDocId);
                $signDoc->setAttributes(array(
                    'recipients' => json_encode($recipients),
                    'fieldInfo' => json_encode($fields['fieldInfo']),
                    'template' => $temp,
                    'assignedTo' => User::getMe()->username,
                    'name' => $fields['name'],
                ), false);

                $signDoc->save();
            }
                        
        }

        protected function quickEmailView($id) {
            $envelope = X2SignEnvelopes::model()->findByPk($id);
            $recipients = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $envelope->id));
            $templateList = array('' => '------------') + Docs::getEmailTemplates('email', 'Contacts');
            $signDocIds = json_decode($envelope->signDocIds);
            $signDocNames = array();
            $envelopeName = "";
            foreach($signDocIds as $signDocId){
                $signDocNames[] = X2SignDocs::model()->findByPk($signDocId)->name;
                $envelopeName .=  X2SignDocs::model()->findByPk($signDocId)->name . " ";
            }
            $emailAddresses = array();
            foreach($recipients as $recipient) {
                $emailAddresses[] = isset($recipient->emailAddress) ? $recipient->emailAddress : $recipient->email;
            }
            $envelopeName .= date('M d, Y: ') . ' Quick Send Doc';
            $envelope->name = $envelopeName;
            $envelope->save();
        } 

        public function emailAgent($agentEmail="") {
            $agentEmail = [
                "to"=>[
                            0=>[
                                0=>User::getMe()->firstName.' '.User::getMe()->lastName,
                                1=>User::getMe()->emailAddress]]
                ];
            $emailBody ='A new NDA Automation document request has been received in the CRM. ';
            $emailSubject = 'New NDA Automation Request - Notification';
            $credId = 2838;
        return $this->sendUserEmail($agentEmail, $emailSubject, $emailBody, null, $credId);
    } 

    protected function quickSendFinish($id, $emailContent) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        if (isset($envelope)) {
            if (isset($emailContent['subject']) && isset($emailContent['message'])) {
                $subject = $emailContent['subject'];
                $message = $emailContent['message'];
                $delay = 0;
                //we do this to set the name
                $envelopeName = date('M d, Y') . ", " . $emailContent['subject'];
                $signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $envelope->id));
                $i = 0;
                foreach($signLinks as $signLink) {
                    $contact = $signLink->modelType::model()->findByPk($signLink->modelId);
                    if($i == 0) $envelopeName .= "@ " . $contact->name;
                    else $envelopeName .= ", " . $contact->name;
                    $i++;
                }
                $envelope->name = $envelopeName;
                $envelope->createDate = time();
                $envelope->save();    
                $fails = $envelope->quickSendEmail($subject, $message, $delay,true);
                if (!(isset($fails) && count($fails) > 0)) Yii::log("Exception occured in NDA automation buyer email sending for:{$contact->email},{$contact->name}", 'trace', 'application.automation.cron');
                //return $this->emailAgent();
                return true;
            }
            else {
                    Yii::log("Email Content is Null",'trace','application.api');
                    return;
            }
        } else {
            throw new CHttpException(400, 'Bad Request');
        }
    }
    // Notify The Agent about new Buyer
    public function notifyAgent($contact,$docId){
        Yii::log("In Notify Email",'trace','application.api');
        $model = Docs::model()->findByPk($docId);
        $emailContent = array(
            'subject' => $model->subject,
            'message' => $model->text,
        );
        $assignedTo=$contact["assignedTo"];
        if(filter_var($contact["assignedTo"], FILTER_VALIDATE_EMAIL)){
            $assignedTo=$contact["assignedTo"];
        }
        else{
            $assignedTo=User::model()->findByAttributes(array('username'=>$contact["assignedTo"]))->emailAddress;
        }
        $user =  User::model()->findByAttributes(array('emailAddress'=>$assignedTo));
        Yii::log($user->id,'trace','application.api');
        $email = Credentials::model()->findDefault($user->id, 'email');
        $agentName= User::model()->findByAttributes(array('emailAddress'=>$assignedTo))->lastName;
        $agentEmail = [
            "to"=>[
                    0=>[
                        0=>$agentName,
                        1=>$assignedTo]]
        ];
        $credId =$email->id;
        Yii::log($credId,'trace','application.api');
        $replacedBody = Docs::replaceVariables($emailContent['message'], $contact,array (
            '{signature}' => Docs::replaceVariables ($user->profile->signature, $contact)));
        $replacedSubject = Docs::replaceVariables($emailContent['subject'], $contact);
        $status=$this->sendUserEmail($agentEmail, $replacedSubject, $replacedBody, null, $credId);
        return $status;
    }
    //RT customizations end
}

