<?php

/* * *********************************************************************************
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
 * ******************************************************************************** */

/* @edition:ent */
//require_once(Yii::app()->basePath.'/integration/SetaPDF_Core/library/SetaPDF/Autoload.php');

/**
 * A behavior for interacting with the twitter API
 * @package application.components.behaviors
 */
class DocusignBehavior extends CBehavior {

    private $accountId;

    /**
     * Creates an instance of DocusignBehavior
     * 
     * @param array params: Parameters for behavior fields
     * @return DocusignBehavior: Instance of TwitterBehavior
     */
    public static function createDocusignInstance() {
        $docusign = Yii::app()->controller->attachBehavior('DocusignBehavior', new DocusignBehavior);
        return $docusign;
    }

    public function getHeader() {
        $userId = isset(Yii::app()->user->id) ? Yii::app()->user->id : 1;
        $id = Credentials::model()->getDefaultUserAccount($userId, 'docs');
        $credentials = Credentials::model()->findByPk($id);
        $check = $id === Credentials::LEGACY_ID;

        $user = $check ? '' : $credentials->auth->username;
        $pass = $check ? '' : $credentials->auth->password;
        $integratorKey = Yii::app()->settings->docusignId;

        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Media-Type: application/json',
            'X-DocuSign-Authentication: {'
            . '"Username": "' . $user . '",'
            . '"Password": "' . $pass . '",'
            . '"IntegratorKey": "' . ($integratorKey === NULL ? '' : $integratorKey) . '"',
        );

        return $header;
    }

    public function get($url) {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $this->getHeader(),
        );

	return CJSON::decode($this->makeRequest($options));
    }

    public function post($url, $json) {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $this->getHeader(),
            CURLOPT_POSTFIELDS => $json
        );

	return CJSON::decode($this->makeRequest($options));
    }

    public function put($url, $json) {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => $this->getHeader(),
            CURLOPT_POSTFIELDS => $json
        );

	return CJSON::decode($this->makeRequest($options));
    }

    /**
     * Replaces shortcodes and makes document available for preview
     */
    public function requestPreview(array $documents, array $recipients, $emailSubject, $contactId, $emailSubLine = NULL, $Asso_Listing = NULL, $bli = false,  $type2 = 'Contacts') {
        $header = $this->getHeader();
	
        $signerList = array();
        $signerCounter = 1;
	
        $type = 'Contacts';
        foreach ($recipients as $recipient) {
            if ($signerCounter === 1) {
                $type = isset($recipient->firstName) && isset($recipient->lastName) ? $type : 'Sellers2';
            }
            $signer = array(
                'roleName' => 'Signer',
                'name' => isset($recipient->firstName) && isset($recipient->lastName) ? $recipient->firstName . ' ' . $recipient->lastName : $recipient->name,
                'email' => isset($recipient->email) ? $recipient->email : $recipient->c_email,
                'recipientId' => (string) $recipient->id,
                'routingOrder' => (string) $signerCounter,
                'type' => get_class($recipient),
            );

            if (isset($recipient->company) && $recipient->company !== '') {
                $signer['company'] = $recipient->company;
            }
            if (isset($recipient->title) && $recipient->title !== '') {
                $signer['title'] = $recipient->title;
            }

            $signerList[] = $signer;
            $signerCounter++;
        }

        $documentList = array();
        foreach ($documents as $document) {
            $nameParts = explode('.', $document->fileName);

            if (count($nameParts) < 2) {
                continue;
            }

            $documentType = $nameParts[count($nameParts) - 1];

            $user = User::getMe();

            $result = null;
            $result = $this->replaceBliShortcodes($document, $contactId, true , $type2,$recipients);
            return CJSON::encode($result);
        }

    }






   /**
   *this function will download the updated document from docusign
   */
   public function getUpdateDocs($documentId){
        //have to do a lot of work in house since this funcion will be called from non user areas
        $envelope = Docusign_status::model()->findByAttributes(array('c_documentId' => $documentId));
        if(!isset($envelope)){
                return;
        }
         $userId = Profile::model()->findByAttributes(array('username'=>$envelope->c_owner))->id;
        $id = Credentials::model()->getDefaultUserAccount($userId, 'docs');
        $credentials = Credentials::model()->findByPk($id);
        $check = $id === Credentials::LEGACY_ID;

        $user = $check ? '' : $credentials->auth->username;
        $pass = $check ? '' : $credentials->auth->password;
        $integratorKey = Yii::app()->settings->docusignId;

        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Media-Type: application/json',
            'X-DocuSign-Authentication: {'
            . '"Username": "' . $user . '",'
            . '"Password": "' . $pass . '",'
            . '"IntegratorKey": "' . ($integratorKey === NULL ? '' : $integratorKey) . '"',
        );


        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'https://www.docusign.net/restapi/v2/login_information',
            CURLOPT_HTTPHEADER => $header,
        );


          $info = CJSON::decode($this->makeRequest($options));

         if (isset($info['loginAccounts']) && isset($info ['loginAccounts'][0])) {
            $login = $info['loginAccounts'][0];

        }else{
                return;
        }


        $logininfo = $login;
        $baseUrl = $logininfo['baseUrl'];

        //go on if we foun the Docusign status
        if(isset($envelope)){
                $url = $baseUrl . '/envelopes/' . $envelope->c_envelopeId . '/documents/' . $envelope->c_documentId;
                $options = array(
                            CURLOPT_RETURNTRANSFER => true,
                             CURLOPT_URL => $url,
                            CURLOPT_HTTPHEADER => $header,
                );

                // return CJSON::decode($this->makeRequest($options));
                //$envelopeResponse = $this->get($baseUrl . '/envelopes/' . $envelope->c_envelopeId . '/documents/' . $envelope->c_documentId);
                $file = $this->makeRequest($options);

                //now to make the media record and put the file in to X2

                $oldFile = Media::model()->findByPk($envelope->c_documentId);
                $newName = '(Docusign)' . preg_replace('/\\.[^.\\s]{3,4}$/', '', $oldFile->fileName) . '.pdf';
 
                if(!isset($envelope->c_signedDocumentId)){
                        $model = new Media;
                        $model->associationType = $oldFile->associationType;
                        $model->fileName = $newName;
                        $model->name = $newName;
                        $model->mimetype = $oldFile->mimetype;
                        $model->accessKey = $oldFile->accessKey;
                        $model->uploadedBy = $oldFile->uploadedBy;
                }
                //save file and link to it
                //check to make sure there is a folder to save to, if not ,make folder
                if(!file_exists('/var/www/transworld_crm/uploads/protected/media/' . $envelope->c_owner . '/')){
                        mkdir('/var/www/transworld_crm/uploads/protected/media/' . $envelope->c_owner . '/',0777);
                }
                file_put_contents('/var/www/transworld_crm/uploads/protected/media/' . $envelope->c_owner . '/' . $newName , $file);
		$envelope->c_status = "completed";
		$envelope->save();	
                if(!isset($envelope->c_signedDocumentId)){
                        $model->save();
                        $envelope->c_signedDocumentId = $model->id;
			$envelope->c_status = "completed";
                        $envelope->save();
                }
        }

   }


  /**
   *this function will get the list of changed documents
   */
   public function getUpdateDocsList($UserName, $StartDate=NULL){
        //have to do a lot of work in house since this funcion will be called from non user areas

         $userId = Profile::model()->findByAttributes(array('username'=>$UserName))->id;
        $id = Credentials::model()->getDefaultUserAccount($userId, 'docs');
        $credentials = Credentials::model()->findByPk($id);
        $check = $id === Credentials::LEGACY_ID;

        $user = $check ? '' : $credentials->auth->username;
        $pass = $check ? '' : $credentials->auth->password;
        $integratorKey = Yii::app()->settings->docusignId;

        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Media-Type: application/json',
            'X-DocuSign-Authentication: {'
            . '"Username": "' . $user . '",'
            . '"Password": "' . $pass . '",'
            . '"IntegratorKey": "' . ($integratorKey === NULL ? '' : $integratorKey) . '"',
        );


        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'https://www.docusign.net/restapi/v2/login_information',
            CURLOPT_HTTPHEADER => $header,
        );

        $info = CJSON::decode($this->makeRequest($options));

         if (isset($info['loginAccounts']) && isset($info ['loginAccounts'][0])) {
            $login = $info['loginAccounts'][0];

        }else{
                return;
        }


        $logininfo = $login;
        $baseUrl = $logininfo['baseUrl'];

        //go on if we foun the Docusign status
        if($id){
                $url = $baseUrl . "/envelopes?status=completed&from_date=2019-03-26";
                $options = array(
                            CURLOPT_RETURNTRANSFER => true,
                             CURLOPT_URL => $url,
                            CURLOPT_HTTPHEADER => $header,
                );
                // return CJSON::decode($this->makeRequest($options));
                //$envelopeResponse = $this->get($baseUrl . '/envelopes/' . $envelope->c_envelopeId . '/documents/' . $envelope->c_documentId);
                                                                                                                                                                                                         $OutPut = $this->makeRequest($options);

                return $OutPut;
                }




        }



    public function saveFileDownload($file, $DocumentSatus){


                $oldFile = Media::model()->findByPk($DocumentSatus->c_documentId);
                $newName = '(Docusign)' . preg_replace('/\\.[^.\\s]{3,4}$/', '', $oldFile->fileName) . '.pdf';
                if(!isset($envelope->c_signedDocumentId)){
                        $model = new Media;
                        $model->associationType = $oldFile->associationType;
                        $model->fileName = $newName;
                        $model->name = $model->fileName;
                        $model->mimetype = $oldFile->mimetype;
                        $model->accessKey = $oldFile->accessKey;
                        $model->uploadedBy = $oldFile->uploadedBy;
                }
                //save file and link to it
                //check to make sure there is a folder to save to, if not ,make folder
                if(!file_exists('/var/www/transworld_crm/uploads/protected/media/' . $DocumentSatus->c_owner . '/')){
                        mkdir('/var/www/transworld_crm/uploads/protected/media/' . $DocumentSatus->c_owner . '/',0777);
                }
                file_put_contents('/var/www/transworld_crm/uploads/protected/media/' . $DocumentSatus->c_owner . '/' . $newName , $file);
                if(!isset($DocumentSatus->c_signedDocumentId)){
                        $model->save();
                        $DocumentSatus->c_signedDocumentId = $model->id;
                        $DocumentSatus->save();
                }





    }




    public function DealWithDocXML($XML) {
        $XMLelm = simplexml_load_string($XML);
        $EnvelopeID = (string)$XMLelm->EnvelopeStatus->EnvelopeID;
        $DocStat = Docusign_status::model()->findByAttributes(array('c_envelopeId' => $EnvelopeID));
        //check to make sure we have an Docusign status before we contiune
        if(isset($DocStat)){
               
                $DocStat->c_status = (string)$XMLelm->EnvelopeStatus->Status;
                $DocStat->save();
                //will download the docusign ver
                if($DocStat->c_status != "Sent"){
                        $this->saveFileDownload(base64_decode((string)$XMLelm->DocumentPDFs->DocumentPDF[0]->PDFBytes) , $DocStat);
                }
                //this will change the attributes from docusign
                if($DocStat->c_status == "Completed"){

                        $recordType = $DocStat->c_recordType;
                        $record = X2Model::model($recordType)->findByPk($DocStat->c_recordId);
                        $recipient = $XMLelm->EnvelopeStatus->RecipientStatuses->RecipientStatus[0];

			//****CUSTOM JUSTIN July 2019 - De-Duplication Project -****
                        if( ($DocStat->name === "Exc_Waiver.docx") && (isset($DocStat)) && ($DocStat->c_recordType == "Contacts") ){

                           if(isset($record) && (!$record->private) && ($record->c_status__c != "PEG")){

                               $record->private = 1; //set Buyer to private
                               $sharedTo = $record->sharedTo;
                               $oldAssignedTo = User::model()->findByAttributes(array("username" => $record->assignedTo));
                               if(isset($oldAssignedTo)){

                                   $sharedTo = CJSON::decode($record->sharedTo);
                                   if(is_array($sharedTo) && !in_array($oldAssignedTo->id, $sharedTo)){ //not empty
                                       $sharedTo[] = $oldAssignedTo->id;
                                       $record->sharedTo = CJSON::encode($sharedTo);
                                   }
				   if(!isset($record->sharedTo)){
                                       $sharedTo = array();
				       $sharedTo[] = $oldAssignedTo->id;
                                       $record->sharedTo = CJSON::encode($sharedTo);
                                   }
                               }
                               $record->assignedTo = $DocStat->c_owner; //set new assignedTo
                               $record->save();
                           }
                        }
                        //****CUSTOM END****

                            foreach ($recipient->TabStatuses->TabStatus as $tab) {
                                $tabLabel = substr($tab->TabLabel, 2);


				if (isset($record) && $record->hasAttribute($tabLabel)) {
                                //if (isset($record) && isset($record->$tabLabel)){ 
				    
                                    $record->$tabLabel = (string)$tab->TabValue;
				    //test to see if numeric 
				    if(is_numeric($tab->TabValue)) $record->$tabLabel = intval((string)$tab->TabValue);
				    //test to see if currency field, if so take out any commas
				    $curCheck  = Fields::model()->findByAttributes(array( 'type' => 'currency' , 'modelName' => $recordType , 'fieldName' => $tabLabel)); 
				    if(isset($curCheck))  $record->$tabLabel = intval(str_replace( ',', '', ((string)$tab->TabValue) ));



				    //if($tabLabel == "c_totalcash") printR($tab->TabValue,1); 
                                    $record->save();
				    //reload incase of errors
				    $record = X2Model::model($recordType)->findByPk($DocStat->c_recordId);
				    //file_put_contents("/tmp/TESTerror.txt" ,   json_encode($record->getErrors()), FILE_APPEND);
                                }
                            }

                }

        }

    }






    /**
     * Requests access for docusign if cookie not already present
     * Note: contactId can represent either a contact XOR a listing
     */
    public function requestAccess(array $documents, array $recipients, $emailSubject, $contactId, $emailSubLine = NULL, $Asso_Listing = NULL, $bli = false, $type = 'Contacts') {
        $header = $this->getHeader();

        $signerList = array();
        $signerCounter = 1;
        
        //for getting listing
        $firstRun = true;
        $SavedModel;
        foreach ($recipients as $recipient) {
            
            if($type == 'Contacts' &&  $firstRun == true){
                $SavedModel = Contacts::model()->findByPk($recipient->id);
                $firstRun = false;

            }
        
            $signer = array(
                'roleName' => 'Signer',
                'name' => $type == 'Contacts' ? $recipient->firstName . ' ' . $recipient->lastName : $recipient->name,
                'email' => isset($recipient->email) ? $recipient->email : $recipient->c_email,
                'recipientId' => (string) $recipient->id,
                'routingOrder' => (string) $signerCounter,
                'type' => get_class($recipient),
            );
            
            if (isset($recipient->company) && $recipient->company !== '') {
                $signer['company'] = $recipient->company;
            }
            if (isset($recipient->title) && $recipient->title !== '') {
                $signer['title'] = $recipient->title;
            }
            
            $signerList[] = $signer;
            $signerCounter++;
        }
        
        $documentList = array();
        
        foreach ($documents as $document) {
            $nameParts = explode('.', $document->fileName);

            if (count($nameParts) < 2) {
                continue;
            }

            $documentType = $nameParts[count($nameParts) - 1];

            $user = User::getMe();

            $result = null;
            if ($bli) {
                $result = $this->replaceBliShortcodes($document, $contactId, null, $type);
            } else if ($documentType === 'docx' && $type === 'Contacts') { 
                $result = $this->replaceShortcodes($document, $contactId, $type);
            } else {
                $result = base64_encode(file_get_contents($document->getPath()));
            }

            $encodedText = is_array($result) ? $result['encodedText'] : $result;
            $editedId = is_array($result) ? $result['editedId'] : $document->id;

            $documentList[] = array(
                'documentId' => $editedId,
                'name' => $document->fileName,
                'fileExtension' => $documentType,
                'documentBase64' => $encodedText,
                'originalId' => $document->id,
            );
        }

        /*$logininfo = $this->getLoginInfo($header);

        if (empty($logininfo)) {
            return 'Login failed, check credentials';
        }

        $baseUrl = $logininfo['baseUrl'];

        // Check if list of documents in table
        $checkDocuments = array();
        foreach ($documentList as $document) {
            $checkDocuments[] = $document['documentId'];
        }
        $documentJson = CJSON::encode($checkDocuments);
	    // Make envelope request
        $envelopeJson = $this->createEnvelopeJson(X2Model::model($type)->findByPk($contactId), $type, $emailSubject, $signerList, $documentList, $signerCounter, $emailSubLine);
        $envelopeResponse = $this->post($baseUrl . '/envelopes', $envelopeJson);*/
        $email = Credentials::model()->findDefault($user->id, 'email');
        if(empty($email))
            throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
        else
            $email = $email->auth->email;
        
        // Create X2Sign Envelopes
        $envelope = new X2SignEnvelopes;
        $enName = 'BLI';
        if($emailSubject == "Buyer Profile- Action required") $enName = "Buyer Profile Document"; 
        $envelope->setAttributes(array(
            'name' => date('M d, Y - ') . $enName,
            'assignedTo' => $user->username,
            'signDocIds' => json_encode($result['signDoc']),
            'sender' => $email,
            'email' => json_encode(array(
                'subject' => 'X2DocSign Document - ' . date('M d, Y'),
                'body' =>   $emailSubject, /* . "\n" .$user->firstName . ' has sent you a document to sign. Click the link below to review and sign the document.' . "\n"  
                            . 'By clicking the link below you agree to the collection of your metadata for the ' . "\n" . 'purpose of verifying your identity.' 
                            . "\n\n" . "<b>Please Do Not Share the Link</b>" . "\n" 
                            . "We generate a secure link for this email and it should not be shared with others to ensure security." 
                            ."\n\n". "<b>About X2Sign</b>" . "\n" 
                            . "X2sign allows its users to create and send legal documents for signing." . "\n" 
                            . "Easily track the status of every sent document with X2Sign." . "\n\n"
                            . "<b>Questions about the Document?</b>" . "\n" 
                            . "If you have any questions about the document, please email the sender directly." . " \n", */
                'reminders' => '',
            )),     
        ), false);
        if(isset($SavedModel)) $envelope->c_listing = $SavedModel->c_listinglookup__c;
        if(!empty($emailSubject)) $envelope->emailSubject = $emailSubject; 
        $envelope->save();

        if($emailSubject == "Buyer Profile- Action required"){
            $doc = X2SignDocs::model()->findByPk($result['signDoc']);
            $fields = json_decode($doc->fieldInfo, 1);
            foreach($fields as $field){
                $X2SignField = new X2SignFields;
                $hold = '';
                if(isset($field['value'])) {
                        $hold = Docs::replaceVariables($field['value'], $recipients[0]);
                        // Specifically for Transworld, strip $ from monetary values since it'll throw
                        // signing error when updating the record with their inputs
                        if(strpos($hold, '$') !== false){ 
                             $hold = str_replace('$', '', $hold);
                             $hold = str_replace(',', '', $hold);
                        }
                        $x2Field = Fields::model()->findByAttributes(array('modelName'=>"Contacts", 'fieldName'=>$field['value']));
                        if (isset($x2Field)) $x2FieldType = $x2field->type;
                }

                $X2SignField->setAttributes(array(
                    'signDocId' => $doc->id,
                    'envelopeId' => $envelope->id,
                    'fieldId' => $field['id'],
                    'value' => $hold,
                    'modelFieldType' => isset($x2FieldType) ? $x2FieldType : null,
                ), false);

                $fieldArray[] = $X2SignField;

            }


            foreach($fieldArray as $xField){
                $xField->save();
            }
        }


        /*$signLink = new X2SignLinks;
        $signLink->setAttributes(array(
            'signRequired' => 1,
            'emailAddress' => $recipient->email,
            'modelType' => $type,
            'modelId' => $recipient->id,
            'envelopeId' => $envelope->id,
            'position' => 1,
            'createDate' => time(),
        ), false);
        $signLink->save();*/
           

       $envRecip = new stdClass();
       $envRecip->id = $recipients[0]['id'];
       $envRecip->modelType = $type;
       $err = $this->sendEnvelope(array($envRecip), array(), $type, $envelope);
       if (count($err) > 0) {
           return json_encode(array("errorCode" => "400", "message" => $err));
       }
       /* if (!isset($envelopeResponse['uri'])) {
            return json_encode($envelopeResponse);
        }

	    foreach ($recipients as $recipient) {
	        foreach ($documentList as $document) {
		        $envelope = new Docusign_status;
                $envelope->name = $document['name'];
		        $envelope->c_status = 'Started';
		        $envelope->c_recordId = $recipient->id;
		        $envelope->c_recordType = get_class($recipient);
                $envelope->c_recordEmail = $type == 'Contacts' ? $recipient->email : $recipient->c_email;
		        $envelope->c_documentId = $document['documentId'];
		        $envelope->c_envelopeId = $envelopeResponse['envelopeId'];
		        $envelope->c_owner = User::getMe()->username;
		        $envelope->c_listing = $Asso_Listing;
		    
		        $envelope->save();
            }   
	    }*/
    }


    public function resendLink($link, $envelope=null) {
        if (!$link instanceof X2SignLinks)
            throw new Exception ('X2SignLinks expected');
        $link->renewKey();
        if (!$link->save())
            throw new Exception ("Could not renew link $link->id.");
        $envelope = $envelope ?? X2SignEnvelopes::model()->findByPk($link->envelopeId);
        return $this->sendDocsEmailHelper([$link], $envelope);

    }

    private function getLoginInfo($header) {
        $demoUrl = 'https://demo.docusign.net/restapi/v2/login_information';
        $productionUrl = 'https://www.docusign.net/restapi/v2/login_information';

        // Extract base url from login info
        $demoDecoded = $this->get($demoUrl);

        if (isset($demoDecoded['loginAccounts']) && isset($demoDecoded['loginAccounts'][0])) {
            $login = $demoDecoded['loginAccounts'][0];
            return $login;
        }

        // Extract base url from login info
        $productionDecoded = $this->get($productionUrl);

        if (isset($productionDecoded['loginAccounts']) && isset($productionDecoded['loginAccounts'][0])) {
            $login = $productionDecoded['loginAccounts'][0];
            return $login;
        }

        return array();
    }

    private function createEnvelopeJson($model, $type, $emailSubject, array $signerList = array(), array $documentList = array(), $senderOrder, $emailSubLine = null) {
        // Json data container
        $data = array();

        // Email data
        $currentDate = date('F j, Y');
        $data['emailSubject'] = isset($emailSubLine) && !empty($emailSubLine) ? $emailSubLine  : "Docusign Document - $currentDate";
        $data['emailBlurb'] = isset($emailSubject) && !empty($emailSubject) ? $emailSubject : "Please sign the Docusign Document,\n\nThank you";

        // Document container
        $data['documents'] = array();
        $documents = &$data['documents'];

        // Set documents based on list
        foreach ($documentList as $document) {
            $toAdd = array();

            $toAdd['documentId'] = $document['documentId'];
            $toAdd['name'] = $document['name'];
            $toAdd['fileExtension'] = $document['fileExtension'];
            $toAdd['documentBase64'] = $document['documentBase64'];

            $documents[] = $toAdd;
        }

        $data['envelopeTemplateDefinition'] = array(
            'name' => 'Test',
            'shared' => 'true',
            'description' => '',
            'pageCount' => count($documentList),
        );

        // Recipient container
        $data['recipients'] = array();
        $recipients = &$data['recipients'];

        // Signer container
        $recipients['signers'] = array();
        $signers = &$recipients['signers'];

        $tabTypes = array(
            'textTabs' => array('Text', 'X2 Field'),
            'signHereTabs' => array('Sign'),
            'initialHereTabs' => array('Initial'),
            'dateSignedTabs' => array('Today\'s Date'),
            'dateTabs' => array('Date'),
        );

        $labelTypes = array(
            'Text', 'Sign', 'Initial', 'Today\'s Date', 'Date'
        );

        // Request status
        $data['status'] = 'sent';

        $templates = array();

        // Set signers based on list
        foreach ($signerList as $signer) {
            foreach ($documentList as $document) {
                $documentId = $document['originalId'];
                $fields = array();

                $media = Media::model()->findByPk($documentId);
                $template = Docusign_templates::model()->findByAttributes(array('nameId' => $media->template));

                if (isset($template) && !empty($template)) {
                    $templates[] = $template;

                    $tabFields = Docusign_fields::model()->findAllByAttributes(array('c_template' => $template->nameId, 'c_isSenderField' => 0));

                    foreach ($tabTypes as $tabType => $fieldTypes) {
                        $fields[$tabType] = array();
                        $tabs = &$fields[$tabType];

                        foreach ($tabFields as $tabField) {
                            if (!isset($tabField->c_anchor_text) || empty($tabField->c_anchor_text)) continue;
                            foreach ($fieldTypes as $fieldType) {
                                if ($fieldType !== $tabField->c_type) {
                                    continue;
                                }

                                $tab = array();

                                $tab['anchorString'] = $tabField->c_anchor_text;
                                $tab['anchorXOffset'] = (string) $tabField->c_offset_right;
                                $tab['anchorYOffset'] = (string) ($tabField->c_offset_top * -1);
                                $tab['anchorIgnoreIfNotPresent'] = 'true';
                                $tab['anchorUnits'] = 'mms';

                                $check = true;
                                switch ($fieldType) {
                                    case 'Text':
                                        $tab['tabLabel'] = $tabField->name;
                                        $tab['value'] = '';
                                        $tab['locked'] = false;
                                        $tab['required'] = boolval($tabField->c_mandatory);
                                        break;
                                    case 'Sign':
                                        $tab['optional'] = !boolval($tabField->c_mandatory);
                                        break;
                                    case 'Initial':
                                        $tab['optional'] = !boolval($tabField->c_mandatory);
                                        break;
                                    case 'X2 Field':
                                        if (!isset($tabField->c_fieldName) || empty($tabField->c_fieldName)) {
                                            $check = false;
                                            break;
                                        }

                                        $recordField = $tabField->c_fieldName;
                                        $record = X2Model::model($signer['type'])->findByPk((int) $signer['recipientId']);

                                        if (!isset($record) || empty($record)) {
                                            $check = false;
                                            break;
                                        }

                                        $recordFieldValue = $this->replaceDocusignCode($signer['type'], $recordField, $record->id);

                                        $checkJson = json_decode($recordFieldValue);
                                        if (strlen($recordFieldValue) > 1 && substr($recordFieldValue, 0, 1) === "[" && isset($checkJson)) {
                                            $tokens = explode(",", $recordFieldValue);
                                            $sum = "";
                                            $first = true;
                                            foreach ($tokens as $oldtoken) {
                                                $token = str_replace('"', '', $oldtoken);
                                                $token = str_replace(' ', '', $token);
                                                $token = str_replace('[', '', $token);
                                                $token = str_replace(']', '', $token);
                                                if ($first === false) {
                                                    $sum = $sum . ", ";
                                                }
                                                $sum = $sum . $token;
                                                $first = false;
                                            }
                                            $recordFieldValue = $sum;
                                        }

                                        $tab['tabLabel'] = $tabField->c_fieldName;
                                        $tab['value'] = $recordFieldValue;
                                        $tab['locked'] = !boolval($tabField->c_isEditable);
                                        $tab['required'] = boolval($tabField->c_mandatory);
                                        break;
                                    case 'Date': case 'Today\'s Date': default:
                                        break;
                                }

                                if (!$check) {
                                    continue;
                                }

                                $tabs[] = $tab;
                            }
                        }
                    }
                }
		}
                $signers[] = array(
                    'roleName' => $signer['roleName'],
                    'name' => $signer['name'],
                    'email' => $signer['email'],
                    'recipientId' => $signer['recipientId'],
                    'routingOrder' => $signer['routingOrder'],
                    'tabs' => !empty($fields) ? $fields : array('textTabs' => array()),
                );
            
        }

        $me = User::getMe();
        $email = $me->emailAddress;

        $senderFields = array();

        if (isset($template)) {
            foreach ($templates as $template) {
                $templateFields = Docusign_fields::model()->findAllByAttributes(array('c_template' => $template->nameId, 'c_isSenderField' => 1));
                $senderFields = array_merge($senderFields, $templateFields);
            }
        }

        $fields = array();

        foreach ($tabTypes as $tabType => $fieldTypes) {
            $fields[$tabType] = array();
            $tabs = &$fields[$tabType];

            foreach ($senderFields as $tabField) {
                foreach ($fieldTypes as $fieldType) {
                    if ($fieldType !== $tabField->c_type) {
                        continue;
                    }

                    $tab = array();

                    $tab['anchorString'] = $tabField->c_anchor_text;
                    $tab['anchorXOffset'] = (string) $tabField->c_offset_right;
                    $tab['anchorYOffset'] = (string) ($tabField->c_offset_top * -1);
                    $tab['anchorIgnoreIfNotPresent'] = 'true';
                    $tab['anchorUnits'] = 'mms';

                    $check = true;
                    switch ($fieldType) {
                        case 'Text':
                            $tab['tabLabel'] = $tabField->name;
                            $tab['value'] = '';
                            $tab['locked'] = false;
                            $tab['required'] = boolval($tabField->c_mandatory);
                            break;
                        case 'Sign':
                            $tab['optional'] = !boolval($tabField->c_mandatory);
                            break;
                        case 'Initial':
                            $tab['optional'] = !boolval($tabField->c_mandatory);
                            break;
                        case 'X2 Field':
                            if (!isset($tabField->c_fieldName) || empty($tabField->c_fieldName)) {
                                $check = false;
                                break;
                            }

                            $recordField = $tabField->c_fieldName;
                            $record = X2Model::model($signer['type'])->findByPk((int) $signer['recipientId']);

                            if (!isset($record) || empty($record)) {
                                $check = false;
                                break;
                            }

                            $recordFieldValue = isset($record->$recordField) ? $record->$recordField : '';

                            $tab['tabLabel'] = $tabField->c_fieldName;
                            $tab['value'] = $recordFieldValue;
                            $tab['locked'] = false;
                            $tab['required'] = boolval($tabField->c_mandatory);
                            break;
                        case 'Date': case 'Today\'s Date': default:
                            break;
                    }

                    if (!$check) {
                        continue;
                    }

                    $tabs[] = $tab;
                }
            }
        }

        $signers[] = array(
            'roleName' => 'Signer',
            'name' => $me->firstName . ' ' . $me->lastName,
            'email' => $me->emailAddress,
            'recipientId' => $me->id,
            'routingOrder' => $senderOrder,
            'tabs' => !empty($fields) ? $fields : array('textTabs' => array()),
        );

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    private function makeRequest(array $options) {
        $curl = curl_init();

        curl_setopt_array($curl, $options);

        $resp = curl_exec($curl);

        curl_close($curl);

        return $resp;
    }

    private function replaceDocusignCode($type, $field, $contactId) {
        if ($type === 'Contacts') {
                $contact = Contacts::model()->findByPk((int) $contactId);
                foreach (Contacts::model()->attributeLabels() as $fieldName => $label) {
                    if (isset($contact) && $field === 'b_' . $fieldName)
                        return $contact->$fieldName;
                }

                $me = User::getMe();
                $listing = Listings2::model()->findByAttributes(array('nameId' => $contact->c_listinglookup__c));
                $employee = Employees::model()->findByAttributes(array('c_user__c' => $me->username));
                $franchisee = null;
                if (isset($employee)) {
                    $franchisee = Franchisees::model()->findByAttributes(array('nameId' => $employee->c_franchisee__c));
                }

                foreach(Listings2::model()->attributeLabels() as $fieldName => $label) {
                    $check = str_replace('l_' . $fieldName, $fieldName, $field);
                    if (isset($listing) && $check === $fieldName)
                        return $listing->$fieldName;
                }

                if (isset($listing)) {
                    $owner = User::model()->findByAttributes(array('username' => $listing->c_ownerid));

                    if (isset($owner) && $field === 'w_firstName')
                        return $owner->firstName;
                    if (isset($owner) && $field === 'w_lastName')
                        return $owner->lastName;
                }

                if (isset($franchisee)) {
                    $state = States::model()->findByAttributes(array('name' => $franchisee->c_state__c));
                    $county = Counties::model()->findByAttributes(array('name' => $franchisee->c_county__c));

                    if (isset($state) && $field === 'f_c_state__c')
                        return $state->name;
                    if (isset($county) && $field === 'f_c_county__c')
                        return $county->name;

                    $owner = User::model()->findByAttributes(array('username' => $franchisee->c_franchisee_owner__c));
                    $office = Offices::model()->findByAttributes(array('c_franchisee__c' => $franchisee->nameId));

                    if (isset($owner) && $field === 'u_firstName')
                        return $owner->firstName;
                    if (isset($owner) && $field === 'u_lastName')
                        return $owner->lastName;

                    foreach(Offices::model()->attributeLabels() as $fieldName => $label) {
                        $check = str_replace('o_' . $fieldName, $fieldName, $field);
                        if (isset($office) && $check === $fieldName)
                            return $office->$fieldName;
                    }
                }

                foreach (Franchisees::model()->attributeLabels() as $fieldName => $label) {
                    $check = str_replace('f_' . $fieldName, $fieldName, $field);
                    if (isset($franchisee) && $check === $fieldName)
                        return $franchisee->$fieldName;
                }

            } else if ($type === 'Sellers2') {
                $contact = Sellers2::model()->findByPk((int) $contactId);
            foreach (Sellers2::model()->attributeLabels() as $fieldName => $label) {
                if (isset($contact) && $field === 's_' . $fieldName)
                    return $contact->$fieldName;
            }

            $me = User::getMe();
            $listing = Listings2::model()->findByAttributes(array('c_Seller' => $contact->nameId));
            $employee = Employees::model()->findByAttributes(array('c_user__c' => $me->username));
            $franchisee = null;
            if (isset($employee)) {
                $franchisee = Franchisees::model()->findByAttributes(array('nameId' => $employee->c_franchisee__c));
            }

            foreach(Listings2::model()->attributeLabels() as $fieldName => $label) {
                $check = str_replace('l_' . $fieldName, $fieldName, $field);
                if (isset($listing) && $check === $fieldName)
                    return $listing->$fieldName;
            }

            if (isset($listing)) {
                $owner = User::model()->findByAttributes(array('username' => $listing->c_ownerid));

                if (isset($owner) && $field === 'w_firstName')
                    return $owner->firstName;
                if (isset($owner) && $field === 'w_lastName')
                    return $owner->lastName;
            }

            if (isset($franchisee)) {
                $state = States::model()->findByAttributes(array('name' => $franchisee->c_state__c));
                $county = Counties::model()->findByAttributes(array('name' => $franchisee->c_county__c));

                if (isset($state) && $field === 'f_c_state__c')
                    return $state->name;
                if (isset($county) && $field === 'f_c_county__c')
                    return $county->name;

                $owner = User::model()->findByAttributes(array('username' => $franchisee->c_franchisee_owner__c));
                $office = Offices::model()->findByAttributes(array('c_franchisee__c' => $franchisee->nameId));

                if (isset($owner) && $field === 'u_firstName')
                    return $owner->firstName;
                if (isset($owner) && $field === 'u_lastName')
                    return $owner->lastName;

                foreach(Offices::model()->attributeLabels() as $fieldName => $label) {
                    $check = str_replace('o_' . $fieldName, $fieldName, $field);
                    if (isset($office) && $check === $fieldName)
                        return $office->$fieldName;
                }
            }

            foreach (Franchisees::model()->attributeLabels() as $fieldName => $label) {
                $check = str_replace('f_' . $fieldName, $fieldName, $field);
                if (isset($franchisee) && $check === $fieldName)
                    return $franchisee->$fieldName;
            }
        }
        return '';
    }



    //check to see if the field is a number based one and if so we will add commas later as needed
    public function checkNumberField($modelType , $fieldName){
        //check for int or currency field if so add commas
        
        $attr = Yii::app()->db->createCommand()
                 ->select('fieldName, attributeLabel')
                  ->from('x2_fields')
                    ->where('modelName = "'. $modelType . '" AND fieldName = "' . $fieldName .'" AND (type in ("currency", "int", "float"))')
                      ->queryAll();
        if(is_array($attr) && count($attr) > 0){
		return TRUE;
        }
	return false;

    }



    public function replaceBliShortcodes($document, $contactId, $preview = false, $type = 'Contacts', $recipients = array()) {
        if($type != "Listings2" && !empty($contactId)){
            $contact = $type::model()->findByPk($contactId);
            $listing = Listings2::model()->findByAttributes(array('nameId' => $contact->c_listinglookup__c));
        } else if($type != "Listings2"){
            $contact = $recipients[0];
            $listing = Listings2::model()->findByAttributes(array('nameId' => $contact->c_listinglookup__c));
        } else {
            $listing = Listings2::model()->findByPk($contactId);
        }

        $me = User::getMe();

        $oldFileName = $document->fileName;
        $oldUser = $document->uploadedBy;
        $newFileName = 'ReplacedFields_' . (string) time() . '.docx';
        $oldPath = $document->getPath();
        $newPath = str_replace($oldFileName, $newFileName, $oldPath);
        $newPath = str_replace($oldUser, $me->username, $newPath);

        copy($oldPath, $newPath);

        $zip = new ZipArchive();

        // Replace shortcodes
        if ($zip->open($newPath, ZipArchive::CREATE) !== TRUE) {
            throw new CHttpException(500, "Cannot open $document->fileName");
        }

        $toEdit = array(
            'word/document.xml',
            'word/header1.xml',
            'word/header2.xml',
            'word/header3.xml',
            'word/footer1.xml',
            'word/footer2.xml',
            'word/footer3.xml',
        );

        $tempArray = array();

        foreach($toEdit as $file) {
            $tempArray[$file] = $xml = $zip->getFromName($file);

            // need to protected against listings since we have custom code for listings down below
            if($type != "Listings2"){
                foreach ($type::model()->attributeLabels() as $fieldName => $label) {
                    $value = $this->sanitizeField($contact->$fieldName);
                    if($this->checkNumberField($type, $fieldName) && !empty($value))
                        $value  = number_format($value);        
                    elseif(empty($value))
                        $value = "N/A";
                    $xml = str_replace('b_' . $fieldName, $value, $xml);

                    // if field is first name look for b_firstName since we had a mistake on the BLI
                    // and it was faster to not deal with .docx and just look for that one string
                    if($fieldName === "firstName")
                        $xml = str_replace('b_firstNane', $value, $xml);
                }
            }

            $xml = str_replace("c_get_date", date("l, F jS Y"), $xml);
            $me = User::getMe();
            $employee = Employees::model()->findByAttributes(array('c_user__c' => $me->username));
            $franchisee = null;
            if (isset($employee)) {
                $franchisee = Franchisees::model()->findByAttributes(array('nameId' => $employee->c_franchisee__c));
                foreach (Employees::model()->attributeLabels() as $fieldName => $label) {
                    if ($fieldName === "c_site_url__c")
                        $xml = str_replace('e_' . $fieldName, "https://www.tworld.com/agent/" . $this->sanitizeField($employee->$fieldName), $xml);
            
                    $value = $this->sanitizeField($employee->$fieldName);
                    if($this->checkNumberField("Employees", $fieldName) && !empty($value))
                        $value  = number_format($value);
                    elseif(empty($value))
                        $value = "N/A";
                   //adding this line since e_c_ is the start of all the fields that the field c_ will cause issues
                   if($fieldName != "c_")
                        $xml = str_replace('e_' . $fieldName, $value, $xml);
                }
            }

            foreach(Listings2::model()->attributeLabels() as $fieldName => $label) {
                if (isset($listing)) {
                    //removed $fieldName === "c_relocatable" since not on form 
                    if ($fieldName === "c_relocatable__c" || $fieldName === "c_franchisee_operation__c" || $fieldName === "c_lender_prequalified__c" || $fieldName === "c_home_based" || $fieldName === "c_accounts_receivable_incl" || $fieldName === "c_ff_e_included" || $fieldName === "c_leasehold_improvements_incl" || $fieldName === "c_Inventory_Included_c" || $fieldName === "c_real_estate_included__c")
                        $xml = $listing->$fieldName == 1 ? str_replace('l_' . $fieldName, "Y ", $xml) : str_replace('l_' . $fieldName, "N", $xml);
                    
                    //if field is first name look for b_firstNane since we had a mistake on the BLI
                    //and it was faster to not deal with .docx and just look for that one string
                    if($fieldName === "c_relocatable" || $fieldName === "c_relocatable__c")
                        $xml = $listing->c_relocatable__c == 1 ? str_replace('l_c_relocatable', "Y ", $xml) : str_replace('l_c_relocatable', "N", $xml);
        
                    if ($fieldName === "c_subcategory_1__c")
                        $xml = str_replace('l_' . $fieldName, preg_replace("/_.*/", "", $this->sanitizeField($listing->$fieldName)), $xml);
                    if ($fieldName === "c_accounts_receivable")
                        $xml = $listing->c_accounts_receivable_incl == 1 ? str_replace('l_c_accounts_receivable_incl', "Y ", $xml) : str_replace('l_c_accounts_receivable_incl', "N", $xml);

                    $value = $this->sanitizeField($listing->$fieldName);
                    if($this->checkNumberField("Listings2", $fieldName) && !empty($value))
                        $value  = number_format($value);
                    elseif(empty($value))
                        $value = "N/A";
                    
                    if($me->id == 5235 && $fieldName == "c_business_description__c") $value = strip_tags($value);
    
                    //this is a protection since the field c_business_hours_of_operation and c_business_hours_of_operation__c are on listtings
                    if($fieldName == "c_business_hours_of_operation")
                        $xml = str_replace('l_c_business_hours_of_operation__c', $this->sanitizeField($listing->c_business_hours_of_operation__c), $xml);
            
                    $xml = str_replace('l_' . $fieldName, $value, $xml);
                } else {
                    $xml = str_replace('l_' . $fieldName, "", $xml);
                }
            }

            if (isset($listing)) {
                //$owner = User::model()->findByAttributes(array('username' => $listing->c_ownerid));

                if (isset($owner)) {
                    $xml = str_replace('w_firstName', $owner->firstName, $xml);
                    $xml = str_replace('w_lastName', $owner->lastName, $xml);
                } else {
                    $xml = str_replace('w_firstName', "", $xml);
                    $xml = str_replace('w_lastName', "", $xml);
                }
            }

            if (isset($franchisee)) {
                $state = States::model()->findByAttributes(array('name' => $franchisee->c_state__c));
                $county = Counties::model()->findByAttributes(array('name' => $franchisee->c_county__c));

                if (isset($state))
                    $xml = str_replace('f_c_state__c', $state->name, $xml);
                else
                    $xml = str_replace('f_c_state__c', "", $xml);

                if (isset($county))
                    $xml = str_replace('f_c_county__c', $county->name, $xml);
                else
                    $xml = str_replace('f_c_county__c', "", $xml);

                $owner = User::model()->findByAttributes(array('username' => $franchisee->c_franchisee_owner__c));
                $office = Offices::model()->findByAttributes(array('c_franchisee__c' => $franchisee->nameId));

                if (isset($owner)) {
                    $xml = str_replace('u_firstName', $owner->firstName, $xml);
                    $xml = str_replace('u_lastName', $owner->lastName, $xml);
                } else {
                    $xml = str_replace('u_firstName', "", $xml);
                    $xml = str_replace('u_lastName', "", $xml);
                }

                foreach(Offices::model()->attributeLabels() as $fieldName => $label) {
                    if (isset($office))
                        $xml = str_replace('o_' . $fieldName, $this->sanitizeField($office->$fieldName), $xml);
                    else
                        $xml = str_replace('o_' . $fieldName, "", $xml);
                }
            } else {
                $xml = strtr($xml, array(
                    'f_c_state__c' => '',
                    'f_c_county__c' => '',
                    'u_firstName' => '',
                    'u_lastName' => '',
                ));

                foreach(Offices::model()->attributeLabels() as $fieldName => $label) {
                    if (!isset($office))
                        $xml = str_replace('o_' . $fieldName, "", $xml);
                }
            }

            foreach (Franchisees::model()->attributeLabels() as $fieldName => $label) {
                if (isset($franchisee))
                    $xml = str_replace('f_' . $fieldName, $this->sanitizeField($franchisee->$fieldName), $xml);
                else
                    $xml = str_replace('f_' . $fieldName, "", $xml);
            }
                
            if (!$zip->addFromString($file, $xml))
                throw new CHttpException(500, "File not written");
        }

        $zip->close();

        // Encode the new changes for request
        $encodedText = base64_encode(file_get_contents($newPath));

        // Save replaced 
        $model = new Media;
        $model->fileName = $newFileName;
        $model->createDate = time();
        $model->lastUpdated = time();
        $model->uploadedBy = Yii::app()->user->name;
        $model->associationType = 'none';
        $model->save();
        
        $pdf = $model->docxToPdf();

        $signDoc = new X2SignDocs;
        $signDoc->setAttributes(array(
            'name' => substr($model->name, 0, strpos($model->name, '.docx')),
            'fieldInfo' => json_encode(array(array(
                'id' => 'Signature-1',
                'page' => '1',
                'top' => '925px',
                'left' => '425px',
                'width' => '150',
                'height' => '30',
                'req' => '1',
                'read-only' => '0',
                'recip' => '1'
            ))),
            'mediaId' => $pdf->id,
            'recipients' => json_encode(array('1')),
            'docId' => -1,
            'visibility' => 0, 
        ), false);
        $signDoc->save();

        if ($preview)
            return $model;

        return array(
            'encodedText' => $encodedText,
            'editedId' => $model->id,
            'signDoc' => array($signDoc->id),
        );
    }
              

    public function replaceShortcodes($document, $contactId, $type) {
        $me = User::getMe();
        $oldFileName = $document->fileName;
        $oldUser = $document->uploadedBy;
        $newFileName = 'ReplacedFields_' . (string) time() . '.docx';
        $oldPath = $document->getPath();
        $newPath = str_replace($oldFileName, $newFileName, $oldPath);
        $newPath = str_replace($oldUser, $me->username, $newPath);

        copy($oldPath, $newPath);

        $zip = new ZipArchive();

        // Replace shortcodes
        if ($zip->open($newPath, ZipArchive::CREATE) !== TRUE) {
            throw new CHttpException(500, "Cannot open $document->fileName");
        }

        $toEdit = array(
            'word/document.xml',
            'word/header1.xml',
            'word/header2.xml',
            'word/header3.xml',
            'word/footer1.xml',
            'word/footer2.xml',
            'word/footer3.xml',
        );


        $tempArray = array();

        foreach($toEdit as $file) {

            $tempArray[$file] = $xml = $zip->getFromName($file);
            //$xml = str_replace("&", "and", $xml);

            if ($type === 'Contacts') {
                $contact = Contacts::model()->findByPk($contactId);
                foreach (Contacts::model()->attributeLabels() as $fieldName => $label) {
                    $xml = str_replace('b_' . $fieldName, $contact->$fieldName, $xml);
                }

                $me = User::getMe();
                $listing = Listings2::model()->findByAttributes(array('nameId' => $contact->c_listinglookup__c));
                $employee = Employees::model()->findByAttributes(array('c_user__c' => $me->username));
                $franchisee = null;
                if (isset($employee)) {
                    $franchisee = Franchisees::model()->findByAttributes(array('nameId' => $employee->c_franchisee__c));
                }

                foreach(Listings2::model()->attributeLabels() as $fieldName => $label) {
                    if (isset($listing)) {
                        $xml = str_replace('l_' . $fieldName, $this->sanitizeField($listing->$fieldName), $xml);
                    } else {
                        $xml = str_replace('l_' . $fieldName, "", $xml);
                    }
                }

                if (isset($listing)) {
                    //$owner = User::model()->findByAttributes(array('username' => $listing->c_ownerid));

                    if (isset($owner)) {
                        $xml = str_replace('w_firstName', $owner->firstName, $xml);
                        $xml = str_replace('w_lastName', $owner->lastName, $xml);
                    } else {
                        $xml = str_replace('w_firstName', "", $xml);
                        $xml = str_replace('w_lastName', "", $xml);
                    }
                }

                if (isset($franchisee)) {
                    $state = States::model()->findByAttributes(array('name' => $franchisee->c_state__c));
                    $county = Counties::model()->findByAttributes(array('name' => $franchisee->c_county__c));

                    if (isset($state)) {
                        $xml = str_replace('f_c_state__c', $state->name, $xml);
                    } else {
                        $xml = str_replace('f_c_state__c', "", $xml);
                    }

                    if (isset($county)) {
                        $xml = str_replace('f_c_county__c', $county->name, $xml);
                    } else {
                        $xml = str_replace('f_c_county__c', "", $xml);
                    }

                    $owner = User::model()->findByAttributes(array('username' => $franchisee->c_franchisee_owner__c));
                    $office = Offices::model()->findByAttributes(array('c_franchisee__c' => $franchisee->nameId));
 
                    if (isset($owner)) {
                        $xml = str_replace('u_firstName', $owner->firstName, $xml);
                        $xml = str_replace('u_lastName', $owner->lastName, $xml);
                    } else {
                        $xml = str_replace('u_firstName', "", $xml);
                        $xml = str_replace('u_lastName', "", $xml);
                    }

                    foreach(Offices::model()->attributeLabels() as $fieldName => $label) {
                        if (isset($office)) {
                            $xml = str_replace('o_' . $fieldName, $office->$fieldName, $xml);
                        } else {
                            $xml = str_replace('o_' . $fieldName, "", $xml);
                        }
                    }
                    
                } else {
                    $xml = str_replace('f_c_state__c', "", $xml);
                    $xml = str_replace('f_c_county__c', "", $xml);
                    $xml = str_replace('u_firstName', "", $xml);
                    $xml = str_replace('u_lastName', "", $xml);
                    foreach(Offices::model()->attributeLabels() as $fieldName => $label) {
                        if (!isset($office)) {
                            $xml = str_replace('o_' . $fieldName, "", $xml);
                        }
                    }
                }

                foreach (Franchisees::model()->attributeLabels() as $fieldName => $label) {
                    if (isset($franchisee)) {
                        $xml = str_replace('f_' . $fieldName, $franchisee->$fieldName, $xml);
                    } else {
                        $xml = str_replace('f_' . $fieldName, "", $xml);
                    }
                }

            } else if ($type === 'Sellers2') {
                $contact = Sellers2::model()->findByPk($contactId);
                foreach (Sellers2::model()->attributeLabels() as $fieldName => $label) {
                    $xml = str_replace('s_' . $fieldName, $this->sanitizeField($contact->$fieldName), $xml);
                }

                $me = User::getMe();
                $listing = Listings2::model()->findByAttributes(array('c_Seller' => $contact->nameId));
                $employee = Employees::model()->findByAttributes(array('c_user__c' => $me->username));
                $franchisee = null;
                if (isset($employee)) {
                    $franchisee = Franchisees::model()->findByAttributes(array('nameId' => $employee->c_franchisee__c));
                    foreach (Employees::model()->attributeLabels() as $fieldName => $label) {
                        //adding this line since e_c_ is the start of all the fields that the field c_ will cause issues
                        if($fieldName != "c_")
                            $xml = str_replace('e_' . $fieldName, $employee->$fieldName, $xml);
                    }
                }

                foreach(Listings2::model()->attributeLabels() as $fieldName => $label) {
                    if (isset($listing)) {
                        $xml = str_replace('l_' . $fieldName, $this->sanitizeField($listing->$fieldName), $xml);
                    } else {
                        $xml = str_replace('l_' . $fieldName, "", $xml);
                    }
                }

                if (isset($listing)) {
                    $owner = User::model()->findByAttributes(array('username' => $listing->c_ownerid));

                    if (isset($owner)) {
                        $xml = str_replace('w_firstName', $owner->firstName, $xml);
                        $xml = str_replace('w_lastName', $owner->lastName, $xml);
                    } else {
                        $xml = str_replace('w_firstName', "", $xml);
                        $xml = str_replace('w_lastName', "", $xml);
                    }
                }

                if (isset($franchisee)) {
                    $state = States::model()->findByAttributes(array('name' => $franchisee->c_state__c));
                    $county = Counties::model()->findByAttributes(array('name' => $franchisee->c_county__c));

                    if (isset($state)) {
                        $xml = str_replace('f_c_state__c', $state->name, $xml);
                    } else {
                        $xml = str_replace('f_c_state__c', "", $xml);
                    }

                    if (isset($county)) {
                        $xml = str_replace('f_c_county__c', $county->name, $xml);
                    } else {
                        $xml = str_replace('f_c_county__c', "", $xml);
                    }

                    $owner = User::model()->findByAttributes(array('username' => $franchisee->c_franchisee_owner__c));
                    $office = Offices::model()->findByAttributes(array('c_franchisee__c' => $franchisee->nameId));

                    if (isset($owner)) {
                        $xml = str_replace('u_firstName', $owner->firstName, $xml);
                        $xml = str_replace('u_lastName', $owner->lastName, $xml);
                    } else {
                        $xml = str_replace('u_firstName', "", $xml);
                        $xml = str_replace('u_lastName', "", $xml);
                    }

                    foreach(Offices::model()->attributeLabels() as $fieldName => $label) {
                        if (isset($office)) {
                            $xml = str_replace('o_' . $fieldName, $office->$fieldName, $xml);
                        } else {
                            $xml = str_replace('o_' . $fieldName, "", $xml);
                        }
                    }

                } else {
                    $xml = str_replace('f_c_state__c', "", $xml);
                    $xml = str_replace('f_c_county__c', "", $xml);
                    $xml = str_replace('u_firstName', "", $xml);
                    $xml = str_replace('u_lastName', "", $xml);
                    foreach(Offices::model()->attributeLabels() as $fieldName => $label) {
                        if (!isset($office)) {
                            $xml = str_replace('o_' . $fieldName, "", $xml);
                        }
                    }
                }

                foreach (Franchisees::model()->attributeLabels() as $fieldName => $label) {
                    if (isset($franchisee)) {
                        $xml = str_replace('f_' . $fieldName, $franchisee->$fieldName, $xml);
                    } else {
                        $xml = str_replace('f_' . $fieldName, "", $xml);
                    }
                }

            }

            if (!$zip->addFromString($file, $xml)) {
                throw new CHttpException(500, "File not written");
            }

        }

        $zip->close();


        // Encode the new changes for request
        $encodedText = base64_encode(file_get_contents($newPath));

        $model = new Media;
        $model->fileName = $newFileName;
        $model->createDate = time();
        $model->lastUpdated = time();
        $model->uploadedBy = Yii::app()->user->name;
        $model->associationType = 'none';
        $model->mimetype = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document; charset=binary';
        $model->save();
        
        $pdf = $model->docxToPdf();
        
        $signDoc = new X2SignDocs;
        if($oldFileName !== "New_Buyer_Profile.docx")
            $signDoc->setAttributes(array(
                'name' => substr($model->name, 0, strpos($model->name, '.docx')),
                'fieldInfo' => json_encode(array(
                    array(
                        'id' => 'Signature-1',
                        'page' => '1',
                        'top' => '925px',
                        'left' => '425px',
                        'width' => '150',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1'
                    )
                )),
                'mediaId' => $pdf->id,
                'recipients' => json_encode(array('1')),
                'visibility' => 0,
                'docId' => -1,
            ), false);
        else
            $signDoc->setAttributes(array(
                'name' => substr($model->name, 0, strpos($model->name, '.docx')),
                'fieldInfo' => json_encode(array(
                    array(
                        'id' => 'Name-1',
                        'page' => '1',
                        'top' => '233px',
                        'left' => '107px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{name}'
                    ),
                    array(
                        'id' => 'Signature-3',
                        'page' => '2',
                        'top' => '865px',
                        'left' => '161px',
                        'width' => '150',
                        'height' => '41',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1'
                    ),
                    array(
                        'id' => 'Email-4',
                        'page' => '1',
                        'top' => '315px',
                        'left' => '136px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{email}'
                    ),
                    array(
                        'id' => 'Text-5',
                        'page' => '1',
                        'top' => '226px',
                        'left' => '525px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{phone}'
                    ),
                    array(
                        'id' => 'Text-6',
                        'page' => '1',
                        'top' => '257px',
                        'left' => '522px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_mobilephone}'
                    ),
                    array(
                        'id' => 'Text-7',
                        'page' => '1',
                        'top' => '284px',
                        'left' => '92px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{address}'
                    ),
                    array(
                        'id' => 'Text-8',
                        'page' => '1',
                        'top' => '288px',
                        'left' => '377px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{city}'
                    ),
                    array(
                        'id' => 'Text-9',
                        'page' => '1',
                        'top' => '288px',
                        'left' => '519px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{state}'
                    ),
                    array(
                        'id' => 'Text-10',
                        'page' => '1',
                        'top' => '287px',
                        'left' => '662px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{zipcode}'
                    ),
                    array(
                        'id' => 'Text-11',
                        'page' => '1',
                        'top' => '348px',
                        'left' => '274px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_typeofbusiness}'
                    ),
                    array(
                        'id' => 'Text-12',
                        'page' => '1',
                        'top' => '388px',
                        'left' => '236px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_Howlong}'
                    ),
                    array(
                        'id' => 'Text-13',
                        'page' => '1',
                        'top' => '419px',
                        'left' => '316px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_whattypeslooked}'
                    ),
                    array(
                        'id' => 'Text-14',
                        'page' => '1',
                        'top' => '449px',
                        'left' => '315px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_whattypedont}'
                    ),
                    array(
                        'id' => 'Text-15',
                        'page' => '1',
                        'top' => '475px',
                        'left' => '107px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_why}'
                    ),
                    array(
                        'id' => 'Text-16',
                        'page' => '1',
                        'top' => '511px',
                        'left' => '314px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_howsoon}'
                    ),
                    array(
                        'id' => 'Text-17',
                        'page' => '1',
                        'top' => '605px',
                        'left' => '240px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_Motivation}'
                    ),
                    array(
                        'id' => 'Text-18',
                        'page' => '1',
                        'top' => '644px',
                        'left' => '381px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_visapurposes}'
                    ),
                    array(
                        'id' => 'Text-19',
                        'page' => '1',
                        'top' => '679px',
                        'left' => '384px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_whichcountry}'
                    ),
                    array(
                        'id' => 'Text-20',
                        'page' => '1',
                        'top' => '703px',
                        'left' => '382px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_previousownership}'
                    ),
                    array(
                        'id' => 'Text-21',
                        'page' => '1',
                        'top' => '724px',
                        'left' => '346px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_locationpreference}'
                    ),
                    array(
                        'id' => 'Text-22',
                        'page' => '1',
                        'top' => '749px',
                        'left' => '402px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_whowillassist}'
                    ),
                    array(
                        'id' => 'Text-23',
                        'page' => '1',
                        'top' => '765px',
                        'left' => '474px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_minimumownerbenefit}'
                    ),
                    array(
                        'id' => 'Text-24',
                        'page' => '2',
                        'top' => '66px',
                        'left' => '341px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_howtoplantopay}'
                    ),
                    array(
                        'id' => 'Text-25',
                        'page' => '2',
                        'top' => '120px',
                        'left' => '329px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_Cashdown}'
                    ),
                    array(
                        'id' => 'Text-26',
                        'page' => '2',
                        'top' => '173px',
                        'left' => '447px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_minimumcash}'
                    ),
                    array(
                        'id' => 'Text-27',
                        'page' => '2',
                        'top' => '223px',
                        'left' => '243px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_firstyear}'
                    ),
                    array(
                        'id' => 'Text-28',
                        'page' => '2',
                        'top' => '276px',
                        'left' => '270px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_annuallythereafter}'
                    ),
                    array(
                        'id' => 'Text-29',
                        'page' => '2',
                        'top' => '384px',
                        'left' => '422px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_totalcash}'
                    ),
                    array(
                        'id' => 'Text-30',
                        'page' => '2',
                        'top' => '418px',
                        'left' => '421px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_Investments}'
                    ),
                    array(
                        'id' => 'Text-31',
                        'page' => '2',
                        'top' => '447px',
                        'left' => '418px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_retirementfunds}'
                    ),
                    array(
                        'id' => 'Text-32',
                        'page' => '2',
                        'top' => '476px',
                        'left' => '417px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_homeequity}'
                    ),
                    array(
                        'id' => 'Text-33',
                        'page' => '2',
                        'top' => '503px',
                        'left' => '420px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_otherassets}'
                    ),
                    array(
                        'id' => 'Text-34',
                        'page' => '2',
                        'top' => '533px',
                        'left' => '419px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_otherliabilities}'
                    ),
                    array(
                        'id' => 'Text-35',
                        'page' => '2',
                        'top' => '564px',
                        'left' => '420px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_totalnet}'
                    ),
                    array(
                        'id' => 'Text-36',
                        'page' => '2',
                        'top' => '600px',
                        'left' => '418px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_annualincome}'
                    ),
                    array(
                        'id' => 'Text-37',
                        'page' => '2',
                        'top' => '741px',
                        'left' => '45px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_othercomments}'
                    ),
                    array(
                        'id' => 'Text-38',
                        'page' => '2',
                        'top' => '650px',
                        'left' => '120px',
                        'width' => '115',
                        'height' => '30',
                        'req' => '1',
                        'read-only' => '0',
                        'recip' => '1',
                        'value' => '{c_creditscore}'
                    ),
 
                )), 
                'mediaId' => $pdf->id,
                'recipients' => json_encode(array('1')),
                'docId' => '-1',
            ), false);

        $signDoc->save();

        return array(
            'encodedText' => $encodedText,
            'editedId' => $model->id,
            'signDoc' => array($signDoc->id),
        );
    }

    private function sanitizeField($field) {
	//$field = str_replace("&amp;", "and", $field);
        /*$field = str_replace("&", "&amp;", $field);
        $field = str_replace("[\"", "", $field);
        $field = str_replace("\"]", "", $field);
        $field = str_replace("<br>", "<w:br />", $field);
        $field = str_replace("\"", "", $field);
        $field = str_replace(",", ", ", $field);*/
	
        $field = strtr($field, array(
            "&" => "&amp;",
            "[\"" => "",
            "\"]" => "",
            "<br>" => "<w:br />",
            "\"" => "",
            "," => ", ",
        ));
        return $field;
    }


    private function genEmailHeader($link, $envelope,$body = "") {
        $header = '
<table style="border-collapse:collapse;background-color:#ffffff;width:100%">
    <tbody>
        <tr>
            <td style="padding:10px 24px; "><img alt="SydneyDocSign" class="CToWUd" src="https://sydney.tworld.com/index.php/media/media/getFile/id/200844043/key/c992e96aa02a6063bd75f9db8dab7f4de04749e40133cbf743fcb5377c76ede6" style="border: medium none; width: 250px; height: 65px;" /></td>
        </tr>
        <tr>
            <td style="padding:0px 24px 30px 24px">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"  width="100%">
                <tbody>
                    <tr>
                        <td align="center" style="padding:28px 36px 36px 36px;border-radius:2px;background-color:#24652c;color:#ffffff;font-size:16px;font-family:Helvetica,Arial,Sans Serif;width:60%;text-align:center"><img class="CToWUd" height="75" src="https://dedupe.sydney.x2developer.com/doc_logo.png" />


                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                                <tr>
                                    <td align="center" style="padding-top:30px">
                                    <div>
                                    <table cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td align="center" height="44" style="font-size:15px;color:#333333;background-color:#ffc423;font-family:Helvetica,Arial,Sans Serif;font-weight:bold;text-align:center;text-decoration:none;border-radius:2px;background-color:#ffc423;display:block"><a href="{link}" style="font-size:15px;color:#333333;background-color:#ffc423;font-family:Helvetica,Arial,Sans Serif;font-weight:bold;text-align:center;text-decoration:none;border-radius:2px;background-color:#ffc423;display:inline-block" target="_blank"><span style="padding:0px 24px;line-height:44px">REVIEW DOCUMENT </span></a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </td>
                    </tr>
                    <tr>
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                                <tr>
                                    <td style="padding-top:24px;font-size:18px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:unset;">{body}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;">{username} has sent you a document to sign. Click the link above to review and sign the document.</td>
                                 </tr>
                                <tr>
                                  <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;"> By clicking the link above you agree to the collection of your metadata for the  purpose of verifying your identity.</td>
                                </tr>
                                <tr>       
                                    <td></td>
                                 </tr>
                                 <tr> 
                                    <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;"><b>Please Do Not Share the Link</b></td>
                                </tr>
                                 <tr>

                                    <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;">We generate a secure link for this email and it should not be shared with others to ensure security.</td>
                                 <tr>
                                    <td></td>
                                 </tr>
                                 <tr>
                                    <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;"><b>About S2Sign</b></td>
                                 </tr>
                                    <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;">S2Sign allows its users to create and send legal documents for signing.</td>
                                 <tr>
                                    <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;">Easily track the status of every sent document with S2Sign.</td>
                                 </tr>
                                 <tr>
                                    <td></td>
                                 </tr>
                                 <tr>
                                    <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;"><b>Questions about the Document?</b></td>
                                 </tr>
                                 <tr>
                                    <td align="center" style="padding-top:24px;font-size:12px;font-family:Helvetica,Arial,Sans Serif;border:none;text-align:center;">If you have any questions about the document, please email the sender directly.</td>
                                </tr>
                            </tbody>
                        </table>
                    </tr>
                </tbody>
            </table>
            </td>
        </tr>
    </tbody>
</table>
';
        $user = User::model()->findByAttributes(array('username' => $envelope->assignedTo));
        $header = str_replace('{username}', isset($user) ? $user->firstName . " " . $user->lastName : $envelope->sender, $header);
        $header = str_replace('{link}', $link, $header);
         $header = str_replace('{body}', $body, $header);
        return $header;
    }


    /**
     * @author Justin Law <jlaw@x2engine.com>, Clifton Chiang <clifton@x2engine.com>
     *
     * Iterates over recipients. Handles mailing and creating associated records.
     */
    private function sendDocsLinkHelper($recipients, $modelType, $envelope, $signRequired) {
        $links = [];
        foreach ($recipients as $idKey => $blob) {
            if (is_numeric($blob->id)) {
                $model = $blob->modelType;
                $recipient = $model::model()->findByPk($blob->id);
                $email = $recipient->email;
            } else if ($blob->id == 'SELF') {
                $model = 'User';
                $blob->id = Yii::app()->suId;
                $email = $envelope->sender;
            } else {
                throw new Exception ('invalid recipient element');
            }

            // create link
            $linkModel = new X2SignLinks;
            $linkModel->setAttributes(array(
                'signRequired' => $signRequired,
                'emailAddress' => $email,
                'modelType' => $model,
                'modelId' => $blob->id,
                'envelopeId' => $envelope->id,
                'position' => ++$idKey,
                'createDate' => time(),
            ), false);

            if (!$linkModel->save()) {
                throw new Exception ('Could not create link: ' . json_encode($linkModel->getErrors()));
            }
            $links[] = $linkModel;
        }
        return $links;
    }

    /**
     * Handles sending envelope mail
     * @param array $links list of X2SignLinks representing recipients
     * @param X2SignEnvelope $envelope package to be delivered
     * @return array list of failed emails
     */
    public function sendDocsEmailHelper($links, $envelope) {
        $subject = $envelope->emailSubject;
        $body = $envelope->emailBody;

        $fails = [];
        $skipSignees = false;
        foreach ($links as $linkModel) {
            if ($linkModel->signRequired && $skipSignees) continue;
            try {
                // skip other signees if envelope is sequential
                if ($envelope->sequential && $linkModel->signRequired) $skipSignees = true;

                // renew key to avoid sending expired/expiring keys
                $linkModel->renewKey();

                // email setup
                $recipient = $linkModel->recipient;
                if($recipient instanceof User) {
                    $envelope->status = $envelope::ACTIONS_REQUIRED;
                    $envelope->saveAttributes(array('status'));
                }


                $to = ['to' => [[$recipient->name, $linkModel->emailAddress]]];
                $key = $linkModel->key;
                //$url = Yii::app()->controller->createAbsoluteUrl('x2sign/signDocs', ['key' => $key]);
                $url = 'https://staging.tworld.x2developer.com/index.php' . '/x2sign/signDocs?key='. $key;
                $link = CHtml::link('Signing Page', $url);

                // send mail
                $replacedSubject = Docs::replaceVariables($subject, $recipient);
                $replacedBody = Docs::replaceVariables($body, $recipient);
                $header = $this->genEmailHeader($url, $envelope, $replacedBody);
                $sender = User::model()->findByAttributes(['username'=>$envelope->assignedTo]);
                $credId = Credentials::model()->getDefaultUserAccount($sender->id);
                $trackUrl = Yii::app()->createExternalUrl('/actions/actions/emailOpened', array('uid' => $linkModel->id, 'type' => 'SignOpen'));
                if(isset($trackUrl)){
                    $trackingImage = '<img src="'.$trackUrl.'"/>';
                    $header = $header . " " . $trackingImage;
                }

                $status = Yii::app()->controller->sendUserEmail($to, $replacedSubject, "$header", null, $credId);
                if ($status['code'] != 200) throw $status['exception'];

                // mark sent
                $linkModel->sent = 1;
                $linkModel->save();

                // create reminder
                if (isset($envelope->reminders) && $linkModel->signRequired) {
                    $reminders = json_decode($envelope->reminders);
                    $count = $reminders->count;
                    $interval = $reminders->interval * 86400;
                    $data = array(
                        'link' => $linkModel->id,
                        'maxExecutions' => $count
                    );
                    $event = new CronEvent;
                    $event->type = 'x2sign_reminder';
                    $event->interval = $interval;
                    $event->data = json_encode($data);
                    $event->time = time() + $interval;
                    $event->associationId = $linkModel->id;
                    $event->associationType = 'X2SignLinks';
                    $event->save();
                }
            } catch (Exception $e) {
                $fails[] = $recipient->email;
                continue;
            }
        }
        return $fails;
    }

    /**
     * Sends out an envelope to a list of recipients
     * @param array $signees, array $viewers (list of ids)
     * @param string modelType the model the signee/viewer ids pertain to
     * @param X2SignEnvelopes the envelope to be sent
     * @return array of failed emails
     */
    public function sendEnvelope($signees, $viewers, $modelType, $envelope) {
        $links = array_merge(
            $this->sendDocsLinkHelper($signees, $modelType, $envelope, 1),
            $this->sendDocsLinkHelper($viewers, $modelType, $envelope, 0)
        );

        $fails =  $this->sendDocsEmailHelper($links, $envelope);
        if (!empty($fails)) {
            $envelope->failed = 1;
            $envelope->save();
        }

        return $fails;
    }

    /**
     * Retry sending emails
     * @param X2SignEnvelope envelope
     */
    public function resendEnvelope($envelope) {
        //$links = $envelope->getX2SignLinks(['sent'=>0]);
        $links = $envelope->getX2SignLinks(['signedDate'=>NULL, 'signRequired'=>1]);
        $fails = $this->sendDocsEmailHelper($links, $envelope);

        $envelope->failed = !empty($fails);
        $envelope->save();

        return $fails;
    }

    public function sendCompletePdf($envelope) {
        $signLinks = X2SignLinks::model()->findAllByAttributes(array(
            'envelopeId' => $envelope->id,
            //'signRequired' => 1,
        ));
        $completePdf = Media::model()->findByPk($envelope->completedDoc);

        //generate the certificate


        $html = $envelope->generateCertificateHtml();
        $behavior = MPDFBehavior::createInstance();
        $pdf = $behavior->newPdf();
        $pdf->WriteHTML($html);

            $fileName = "Certificate_{$envelope->id}_Complete.pdf";
            $filePath = "uploads/protected/x2sign/$fileName";


            $pdf->Output($filePath, \Mpdf\Output\Destination::FILE);

            $certificate = new Media;
            $certificate->fileName = $fileName;
            $certificate->uploadedBy = $envelope->assignedTo;
            if (Yii::app()->settings->amazonS3CredentialsId) {
                //post to s3
                $certificate->s3 = 1;
                $key = $certificate->getAccessKey();
                $body = $pdf->Output('','S');
                AmazonS3Behavior::createInstance()->put($key, $body);
                unlink($model->path);
            }
            $certificate->save();

        $attachments = array(array(
            'filename' => $completePdf->fileName,
            'folder' => $completePdf->uploadedBy,
            'type' => 'media',
            'id' => $completePdf->id,
            'model' => $completePdf,
        ),
            array(
            'filename' => $certificate->fileName,
            'folder' => $certificate->uploadedBy,
            'type' => 'media',
            'id' => $certificate->id,
            'model' => $certificate,
        )
            );

        $userInList = false;
        // Send completed PDF to each recipient of the envelope 
        foreach($signLinks as $signLink) {
            //check if user
            if($signLink->modelType == "User") $userInList = True;
            // email setup
            $recipient = $signLink->recipient;

            $to = array('to' => array(array($recipient->name, $signLink->emailAddress)));
            // Send out the completed PDF to all recipients of the envelope
            $sender = User::model()->findByAttributes(array('username' => $envelope->assignedTo));
            $body = "All signers completed " . $envelope->name;
            $credId = Credentials::model()->getDefaultUserAccount($sender->id);
            $status = Yii::app()->controller->sendUserEmail($to, "Completed: " . $envelope->name, $body, $attachments, $credId);
            if ($status['code'] != 200) throw $status['exception'];
        }
        //this will be where we send an email to the agent if we sent a bli or buyer profile
        $docs = json_decode($envelope->signDocIds);
        if((count($docs) == 1 && count($signLinks) == 1) || !$userInList){


            // Send out the completed PDF to all recipients of the envelope
            $sender = User::model()->findByAttributes(array('username' => $envelope->assignedTo));
            $to = array('to' => array(array($sender->name, $envelope->sender)));
            $body = "All signers completed " . $envelope->name;
            $credId = Credentials::model()->getDefaultUserAccount($sender->id);
            $status = Yii::app()->controller->sendUserEmail($to, "Completed: " . $envelope->name, $body, $attachments, $credId);
            if ($status['code'] != 200) throw $status['exception'];


        }

    }

    /**
     * Sends out reminder for specified sign link
     * @param int $linkId id of X2SignLinks record
     * @return bool true if sent, false otherwise
     */
    public function sendReminder($linkId) {
        //link check
        $link = X2SignLinks::model()->findByPk((int)$linkId);
        if (!$link || $link->signedDate)
            return false;
        
        //envelope check
        $envelope = X2SignEnvelopes::model()->findByPk($link->envelopeId);
        if (!$envelope || $envelope->status == X2SignEnvelopes::CANCELLED)
            return false;

        $recipient = $link->recipient;
        if(!isset($recipient ))return false;
        //adding this since some users have broken email
        if($envelope->assignedTo == "rmcunio@tworld.com" || $envelope->assignedTo ==  "peterrook") return true;


        $to = ['to' => [[$recipient->name, $link->emailAddress]]];

        $email = json_decode($envelope->email);
        $sub = $email->subject;
        $bod = $email->body;

        $sub = Docs::replaceVariables($sub, $recipient);
        $bod = Docs::replaceVariables($bod, $recipient);

        $subject = "Signing Reminder re: $sub";

        $url = Yii::app()->controller->createAbsoluteUrl('x2sign/signDocs?key='. $link->key);
        $header = $this->genEmailHeader($url, $envelope, $bod);
        $message = "This is an automated reminder to view/sign your documents.";
        $message .= "<br><br> Original message: $header";

        $sender = User::model()->findByAttributes(['username'=>$envelope->assignedTo]);
        $credId = Credentials::model()->getDefaultUserAccount($sender->id);

        $status = Yii::app()->controller->sendUserEmail($to, $subject, $message, null, $credId);
        if ($status['code'] != 200) return false;

        return true;
    }

}
