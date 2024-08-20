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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_contacts".
 *
 * @package application.modules.contacts.models
 */
class Contacts extends X2Model {

    public $name;
    public $verifyCode; // CAPTCHA for weblead form

    /**
     * Returns the static model of the specified AR class.
     * @return Contacts the static model class
     */

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array_merge(parent::relations(), array(
            'fingerprint' => array(self::BELONGS_TO, 'Fingerprint', 'fingerprintId'),
        ));
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_contacts';
    }

    /**
     * Gets contact behaviors
     * 
     * @return type
     */
    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'contacts',
            ),
            'FingerprintBehavior' => array(
                'class' => 'FingerprintBehavior',
            ),
            'ModelConversionBehavior' => array(
                'class' => 'application.components.behaviors.ModelConversionBehavior',
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'AddressBehavior' => array(
                'class' => 'application.components.behaviors.AddressBehavior',
            ),
            'DuplicateBehavior' => array(
                'class' => 'application.components.behaviors.DuplicateBehavior',
            ),
            'ContactsNameBehavior' => array(
                'class' => 'application.components.behaviors.ContactsNameBehavior',
            ),
            'MappableBehavior' => array(
                'class' => 'application.components.behaviors.MappableBehavior',
            ),
        ));
    }

    /**
     * Defines contact rules
     * 
     * @return array validation rules for model attributes.
     */
    public function rules() {
        $rules = array_merge(parent::rules(), array(
            array(
                'verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements(),
                'on' => 'webFormWithCaptcha', 'captchaAction' => 'site/webleadCaptcha')
        ));
        return $rules;
    }

    public function duplicateFields() {
        return array_merge(parent::duplicateFields(), array(
            'email',
        ));
    }

    public function afterDelete() {
        parent::afterDelete();
        // Remove associated X2ListItems
        Yii::app()->db->createCommand()
            ->delete('x2_list_items', 'contactId = :id', array(':id' => $this->id));
    }

    /**
     * Updates tracking key after find
     */
    public function afterFind() {
        parent::afterFind();
        if ($this->trackingKey === null && self::$autoPopulateFields) {
            $this->trackingKey = self::getNewTrackingKey();
            $this->update(array('trackingKey'));
        }
    }

    /**
     * Sets tracking key before save
     * 
     * @return boolean whether or not to save
     */
    public function beforeSave() {
        //set code to set owener email
        $hold =  Yii::app()->user->getName();

	if($hold != 'Guest' && $hold != 'admin'){
        	Yii::app()->db->createCommand('UPDATE x2_contacts SET c_listing_email_owner__c = :person WHERE email = :email')
                        ->execute(array( ':person' => $hold , ':email' => $this->email));
	}



        if ($this->trackingKey === null) {
            $this->trackingKey = self::getNewTrackingKey();
        }

        if (User::getMe() !== null)
            $employee = Employees::model()->findByAttributes(array('c_user__c'=>User::getMe()->username));
        $userCountry = isset($employee) ? $employee->c_country__c : '';

        if (isset($this->c_mobilephone) && ($userCountry === 'United States' || $userCountry === 'United States_5')) {
            $this->c_mobilephone = preg_replace('/[^0-9]/', '', $this->c_mobilephone);
            if (strlen($this->c_mobilephone) <= 15 && strlen($this->c_mobilephone) >= 10) {
                $lastFour = substr($this->c_mobilephone, -4);
                $middleThree = substr($this->c_mobilephone, -7,-4);
                $areaCode = substr($this->c_mobilephone,-10,-7);
                $countryCode = substr($this->c_mobilephone,0,-10);
                $this->c_mobilephone = $countryCode . " (" . $areaCode . ") " . $middleThree . "-" . $lastFour;
            }
        }
        if (isset($this->phone) && ($userCountry === 'United States' || $userCountry === 'United States_5')) {
            $this->phone = preg_replace('/[^0-9]/', '', $this->phone);
            if (strlen($this->phone) <= 15 && strlen($this->phone) >= 10) {
                $lastFour = substr($this->phone, -4);
                $middleThree = substr($this->phone, -7,-4);
                $areaCode = substr($this->phone,-10,-7);
                $countryCode = substr($this->phone,0,-10);
                $this->phone = $countryCode . " (" . $areaCode . ") " . $middleThree . "-" . $lastFour;
            }
        }

         // Update related Inquiries
        /*  if (isset($this->assignedTo)) {
            $inquiries = Inquiries::model()->findAllByAttributes(array("c_contact__c"=>$this->nameId));
            foreach ($inquiries as $inquiry) {
                $inquiry->assignedTo = $this->assignedTo;
                $inquiry->save();
            }
        } */

            //this line is to check if the number is in the spot where nameId should be
            if(isset($this->c_listing_number_lookup)) $listing = Listings2::model()->findByAttributes(array('c_listing_number__c' => $this->c_listing_number_lookup));
            if(isset($listing)) $this->c_listing_number_lookup = $listing->nameId;

               if(isset($this->c_listing_number_lookup) && !empty($this->c_listing_number_lookup)){
                                $listingModel = Listings2::model()->findByAttributes(array("nameId"=>$this->c_listing_number_lookup));
			if(isset($listingModel) && ($this->c_listing_number_lookup === $listingModel->nameId)) {
				$this->c_listing_number_lookup = $listingModel->nameId;
                                $this->c_listinglookup__c = $listingModel->nameId;
                                $this->c_listing_number = $listingModel->c_listing_number__c;
			}
                } else if (isset($this->c_listinglookup__c) && !empty($this->c_listinglookup__c)) {
                                $listingModel = Listings2::model()->findByAttributes(array("nameId"=>(string)$this->c_listinglookup__c));
			if (isset($listingModel)) {
                        $this->c_listing_number_lookup = $listingModel->nameId;
                        $this->c_listing_number = $listingModel->c_listing_number__c;
			}
                } else if (isset($this->c_listing_number)) {
                                $listingModel = Listings2::model()->findByAttributes(array("c_listing_number__c"=>(string)$this->c_listing_number));
			
			if (isset($listingModel)) {
                        $this->c_listing_number_lookup = $listingModel->nameId;
                        $this->c_listinglookup__c = $listingModel->nameId;
			}
                }
        return parent::beforeSave();
    }

    /**
     * Responds when {@link X2Model::afterUpdate()} is called (record saved, but
     * not a new record). Sends a notification to anyone subscribed to this contact.
     *
     * Before executing this, the model must check whether the contact has the
     * "changelog" behavior. That is because the behavior is disabled
     * when checking for duplicates in {@link ContactsController}
     */
    public function afterUpdate() {
        if (!Yii::app()->params->noSession && $this->asa('changelog') &&
                $this->asa('changelog')->enabled) {//$this->scenario != 'noChangelog') {
            // send subscribe emails if anyone has subscribed to this contact
            $result = Yii::app()->db->createCommand()
                    ->select('user_id')
                    ->from('x2_subscribe_contacts')
                    ->where('contact_id=:id', array(':id' => $this->id))
                    ->queryColumn();

            $datetime = Formatter::formatLongDateTime(time());
            $modelLink = CHtml::link($this->name, Yii::app()->controller->createAbsoluteUrl('/contacts/' . $this->id));
            $subject = 'X2Engine: ' . $this->name . ' updated';
            $message = "Hello,<br>\n<br>\n";
            $message .= 'You are receiving this email because you are subscribed to changes made to the contact ' . $modelLink . ' in X2Engine. ';
            $message .= 'The following changes were made on ' . $datetime . ":<br>\n<br>\n";

            foreach ($this->getChanges() as $attribute => $change) {
                if ($attribute != 'lastActivity') {
                    $old = $change[0] == '' ? '-----' : $change[0];
                    $new = $change[1] == '' ? '-----' : $change[1];
                    $label = $this->getAttributeLabel($attribute);
                    $message .= "$label: $old => $new<br>\n";
                }
            }

            $message .= "<br>\nYou can unsubscribe to these messages by going to $modelLink and clicking Unsubscribe.<br>\n<br>\n";

            $adminProfile = Yii::app()->params->adminProfile;
            foreach ($result as $subscription) {
                $subscription = array();
                if (isset($subscription['user_id'])) {
                    $profile = X2Model::model('Profile')->findByPk($subscription['user_id']);
                    if ($profile && $profile->emailAddress && $adminProfile && $adminProfile->emailAddress) {
                        $to = array('to' => array(array($profile->fullName, $profile->emailAddress)));
                        Yii::app()->controller->sendUserEmail($to, $subject, $message, null, Credentials::$sysUseId['systemNotificationEmail']);
                    }
                }
            }
        }

        parent::afterUpdate();
    }
    
    public function findById($id) {
        return X2Model::model('Contacts')->findByPk($id);
    }

    /**
     * Gets an array of names for an assignment dropdown menu
     * 
     * @return type
     */
    public static function getNames() {
        $contactArray = X2Model::model('Contacts')->findAll();
        $names = array(0 => 'None');
        foreach ($contactArray as $user) {
            $first = $user->firstName;
            $last = $user->lastName;
            $name = $first . ' ' . $last;
            $names[$user->id] = $name;
        }
        return $names;
    }

    /**
     * Gets all public contacts.
     * @return $names An array of strings containing the names of contacts.
     */
    public static function getAllNames() {
        $contactArray = X2Model::model('Contacts')->findAll($condition = 'visibility=1');
        $names = array(0 => 'None');
        foreach ($contactArray as $user) {
            $first = $user->firstName;
            $last = $user->lastName;
            $name = $first . ' ' . $last;
            $names[$user->id] = $name;
        }
        return $names;
    }

    /**
     * Gets contact links
     * 
     * @param type $contacts
     * @return type
     */
    public static function getContactLinks($contacts) {
        if (!is_array($contacts)) {
            $contacts = explode(' ', $contacts);
        }

        $links = array();
        foreach ($contacts as &$id) {
            if ($id != 0) {
                $model = X2Model::model('Contacts')->findByPk($id);
                if (isset($model)) {
                    $links[] = CHtml::link($model->name, array('/contacts/contacts/view', 'id' => $id));
                }
                //$links.=$link.', ';
            }
        }
        return implode(', ', $links);
    }

    /**
     * Gets contact mailing list
     * 
     * @param type $criteria
     * @return type
     */
    public static function getMailingList($criteria) {
        $mailingList = array();

        $arr = X2Model::model('Contacts')->findAll();
        foreach ($arr as $contact) {
            $i = preg_match("/$criteria/i", $contact->backgroundInfo);
            if ($i >= 1) {
                $mailingList[] = $contact->email;
            }
        }
        return $mailingList;
    }

    /**
     * An alias for search ()
     */
    public function searchAll($pageSize = null, CDbCriteria $criteria = null) {
        return $this->search($pageSize, $criteria);
    }

    /**
     * Searches in current user's contacts
     * 
     * @return type
     */
    public function searchMyContacts() {
        $criteria = new CDbCriteria;

        $accessLevel = Yii::app()->user->checkAccess('ContactsView') ? 1 : 0;
        $conditions = $this->getAccessConditions($accessLevel);
        foreach ($conditions as $arr) {
            $criteria->addCondition($arr['condition'], $arr['operator']);
            $criteria->params = array_merge($criteria->params, $arr['params']);
        }

        // $condition = 'assignedTo="'.Yii::app()->user->getName().'"';
        // $parameters=array('limit'=>ceil(Profile::getResultsPerPage()));
        // $parameters['condition']=$condition;
        // $criteria->scopes=array('findAll'=>array($parameters));

        return $this->searchBase($criteria);
    }

    /**
     * Searches newest contacts
     * 
     * @return type
     */
    public function searchNewContacts() {
        $criteria = new CDbCriteria;
        $condition = 't.createDate > ' . mktime(0, 0, 0);
        $accessLevel = Yii::app()->user->checkAccess('ContactsView') ? 1 : 0;
        $conditions = $this->getAccessConditions($accessLevel);
        foreach ($conditions as $arr) {
            $criteria->addCondition($arr['condition'], $arr['operator']);
            $criteria->params = array_merge($criteria->params, $arr['params']);
        }

        $parameters = array('limit' => ceil(Profile::getResultsPerPage()));

        $parameters['condition'] = $condition;
        $criteria->scopes = array('findAll' => array($parameters));

        return $this->searchBase($criteria);
    }

    /**
     * Adds tag filtering to search base 
     */
    public function search($pageSize = null, CDbCriteria $criteria = null) {
        if ($criteria === null) {
            $criteria = new CDbCriteria;
        }

        return $this->searchBase($criteria, $pageSize);
    }

    public function searchAdmin() {
        $criteria = new CDbCriteria;
        return $this->searchBase($criteria);
    }

    /**
     * Searches for an account given an id
     * 
     * @param type $id
     * @return type
     */
    public function searchAccount($id) {
        $criteria = new CDbCriteria;
        $criteria->compare('company', $id);

        return $this->searchBase($criteria);
    }

    /**
     * Gets a DataProvider for all the contacts in the specified list,
     * using this Contact model's attributes as a search filter
     */
    public function searchList($id, $pageSize = null) {
        $list = X2List::model()->findByPk($id);

        if (isset($list)) {
            $search = $list->queryCriteria();

            $this->compareAttributes($search);

            return new SmartActiveDataProvider('Contacts', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC'    // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else {    //if list is not working, return all contacts
            return $this->searchBase();
        }
    }

    /**
     * Generates a random tracking key and guarantees uniqueness
     * @return String $key a unique random tracking key
     */
    public static function getNewTrackingKey() {

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        // try up to 100 times to guess a unique key
        for ($i = 0; $i < 100; $i++) {
            $key = '';
            for ($j = 0; $j < 32; $j++) {// generate a random 32 char alphanumeric string
                $key .= substr($chars, rand(0, strlen($chars) - 1), 1);
            }

            // check if this key is already used
            if (X2Model::model('Contacts')->exists('trackingKey="' . $key . '"')) {
                continue;
            } else {
                return $key;
            }
        }
        return null;
    }

    /**
     * Sets values of attributes with values of corresponding attributes in the anon contact record.
     * Also migrates over actions and notifications associated with the anon contact. Finally,
     * the anonymous contact is deleted.
     * 
     * @param AnonContact $anonContact The anonymous contact record whose attributes will be
     *  merged in with this contact
     */
    public function mergeWithAnonContact(AnonContact $anonContact) {
        $fingerprintRecord = $anonContact->fingerprint;

        // Migrate over existing AnonContact data
        if (!isset($this->leadscore)) {
            $this->leadscore = $anonContact->leadscore;
        }
        if (!isset($this->email)) {
            $this->email = $anonContact->email;
        }
        if (!isset($this->reverseIp)) {
            $this->reverseIp = $anonContact->reverseIp;
        }
        $fingerprintRecord->anonymous = false;
        $fingerprintRecord->update('anonymous');
        $this->mergeRelatedRecords($anonContact);
        $this->fingerprintId = $fingerprintRecord->id;
        // Update the fingerprintId so that the Fingerprint is not deleted
        // by afterDelete() when the AnonContact is deleted.
        $this->update(array('fingerprintId'));
        $anonContact->delete();
    }

    private $quick = false;
    public function setQuick($set) {
        $this->quick = $set;
    }

    public function isQuick() {
        return $this->quick;
    }

    public function DeDupBuyers ($firstChar){



        $EmailList = Yii::app()->db
                        ->createCommand("SELECT distinct(email) from x2_contacts where email LIKE '" . strtoupper($firstChar) . "%'
                                        AND email LIKE '" . strtolower($firstChar) . "%'"  )
                        ->queryAll();

        foreach($EmailList as $emailEl){

                $email = $emailEl["email"];



                        $this->EmailDe($email);
                unset( $email);
        }

        unset($EmailList);

    }

    public function EmailDe($email){


                $transaction = Yii::app()->db->beginTransaction();
                try {

                $Buyers = Contacts::model()->findAllByAttributes(array('email' => $email));
                //only need to run if more then one record for that email
                if(count($Buyers) > 1 && !empty($email) && isset($email)){
                        $leadBuyer = $Buyers[0];
                        $BuyerIDs = array();
                        $BuyerNameIds = array();
                        //get newest buyer
                        $userIds = array();
                        $comments = "";
                        $descrip = "";
			//this will be used to see if do not email or exclude 
			$isDoNotEmail = False;
			$isExclude = False;
                        foreach($Buyers as $buyer){

                                if(isset($buyer->c_comments__c)) $comments .= $buyer->c_comments__c . " " ;
                                if(isset($buyer->c_description)) $descrip .= $buyer->c_description . " ";
				//check to see if there is a do not email set
				if($buyer->doNotEmail == 1) $isDoNotEmail = True;
				if($buyer->c_exclude_from_listings_mailing__c == 1) $isExclude = True;
                                $BuyerIDs[] = $buyer->id;
                                $BuyerNameIds[] = addslashes($buyer->nameId);
                                if($buyer->createDate > $leadBuyer->createDate){
                                        $leadBuyer = $buyer;
                                }
                                $profile = Profile::model()->findByAttributes(
                                array('username' => $buyer->assignedTo));
                                if(isset($profile) && !in_array(  $profile->id,$userIds))
                                        $userIds[] = $profile->id;
                                unset($profile);

                        }

                        //remove the lead buyer from the list of ID
                        $BuyerIDs = array_diff( $BuyerIDs, array($leadBuyer->id) );
                        $BuyerNameIds = array_diff( $BuyerNameIds, array($leadBuyer->nameId) );
                        file_put_contents("/tmp/MergeLog.txt" , "Lead ID: " . $leadBuyer->id  . " CloneIds: " . json_encode($BuyerIDs) . PHP_EOL , FILE_APPEND);
                        //merge documents
                        $this->mergeDocs($leadBuyer->id , $BuyerIDs , "Contacts");
                        //merge actions
                        $this->mergeActionsCon($leadBuyer->id , $BuyerIDs , "Contacts");
                        //merge inquireies
                        $this->mergeInquireies($leadBuyer->nameId , $BuyerNameIds );
                        //merge Docusign
                        $this->mergeDocusign($leadBuyer->id , $BuyerIDs , "Contacts");
                        //merge relationships
                        $this->mergeRelation($leadBuyer->id , $BuyerIDs , "Contacts");
                        //merge attributes
                        $this->mergeAttr($leadBuyer->id , $BuyerIDs , "Contacts");
			//have to reload leadbuyer
                        $leadBuyer = Contacts::model()->findByPk($leadBuyer->id);

                        //delete extra records now
                        $this->mergeDelete($leadBuyer->id , $BuyerIDs , "Contacts");
                        $leadBuyer->sharedTo = json_encode($userIds);
                        $leadBuyer->c_comments__c = $comments;
                        $leadBuyer->c_description = $descrip;
			//if one record had do not email set it for lead buyer

			if($isDoNotEmail == True)$leadBuyer->doNotEmail = 1;
			if($isExclude == True)$leadBuyer->c_exclude_from_listings_mailing__c = 1;			

                        $leadBuyer->save();
                        unset($leadBuyer);
                        unset($BuyerIDs);
                        unset($BuyerNameIds);
                        unset($userIds);




                }
                   $transaction->commit();
                   // actions to do on success (redirect, alert, etc.)
                } catch (Exception $e) {
                    $transaction->rollBack();
                    // other actions to perform on fail (redirect, alert, etc.)
                    echo $e->getTraceAsString();
                }
                unset($transaction);
                unset($Buyers);


    }

    private function mergeDocs($leadId , $cloneIds , $modelType){

        // create sql part for IN condition by imploding comma after each id
        $in = '(' . implode(',', $cloneIds) .')';

        Yii::app()->db
            ->createCommand('UPDATE x2_media SET associationId = ' . $leadId . ' WHERE associationType = "' . $modelType . '" AND  associationId IN ' . $in)->execute();
        unset($in);
    }

    private function mergeActionsCon($leadId , $cloneIds , $modelType){

        // create sql part for IN condition by imploding comma after each id
        $in = '(' . implode(',', $cloneIds) .')';

        $actions = Actions::model()->findAll('associationType = "' . $modelType . '" AND  associationId IN' . $in);
        //have to each record one of a time since we have keys to keep unique

        foreach($actions as $act){
                //see if lead has action already
                $dupACT =  Actions::model()->findByAttributes(array('associationId'=> $leadId , 'associationType' => $act->associationType ,
                                'workflowId' => $act->workflowId , 'stageNumber' => $act->stageNumber ));
                if(!isset($dupACT) || (empty($act->stageNumber) && empty($act->workflowId))){
                        file_put_contents('/tmp/MergeLog.txt','Action will be merged id: ' . $act->id . PHP_EOL,FILE_APPEND);
                        Yii::app()->db
                        ->createCommand('UPDATE x2_actions SET associationId = ' . $leadId . ' WHERE id = ' . $act->id)->execute();
                unset($dupACT);
                }

        }
        unset($actions);
        unset($in);
    }

    private function mergeInquireies($leadNameId , $cloneNameIds){

        // create sql part for IN condition by imploding comma after each id
        $in = '("' . implode('","', $cloneNameIds) .'")';

        Yii::app()->db
            ->createCommand('UPDATE x2_inquiries SET c_contact__c = "' . addslashes($leadNameId) . '" WHERE  c_contact__c IN' . $in)->execute();
        unset($in);
    }

    private function mergeDocusign($leadId , $cloneIds , $modelType){

        // create sql part for IN condition by imploding comma after each id
        $in = '(' . implode(',', $cloneIds) .')';
        //first envelopes then the status
        file_put_contents("/tmp/MergeLog.txt" , 'Merge Docusign: ' .'UPDATE x2_docusign_envelopes SET recordId = ' . $leadId . ' WHERE recordType = "' . $modelType . '" AND  recordId IN' . $in . PHP_EOL , FILE_APPEND);

        file_put_contents("/tmp/MergeLog.txt" , 'UPDATE x2_docusign_status SET c_recordId = ' . $leadId . ' WHERE c_recordType = "' . $modelType . '" AND  c_recordId IN' . $in . PHP_EOL , FILE_APPEND);

        Yii::app()->db
            ->createCommand('UPDATE x2_docusign_envelopes SET recordId = ' . $leadId . ' WHERE recordType = "' . $modelType . '" AND  recordId IN' . $in)->execute();
        Yii::app()->db
            ->createCommand('UPDATE x2_docusign_status SET c_recordId = ' . $leadId . ' WHERE c_recordType = "' . $modelType . '" AND  c_recordId IN' . $in)->execute();
        unset($in);
    }

    private function mergeRelation($leadId , $cloneIds , $modelType){

        // create sql part for IN condition by imploding comma after each id
        $in = '(' . implode(',', $cloneIds) .')';
        //have to run twice since the id could be in the first or second spot

        Yii::app()->db
            ->createCommand('UPDATE x2_relationships SET firstId = ' . $leadId . ' WHERE firstType = "' . $modelType . '" AND  firstId IN' . $in)->execute();
        Yii::app()->db
            ->createCommand('UPDATE x2_relationships SET secondId = ' . $leadId . ' WHERE secondType = "' . $modelType . '" AND  secondId IN' . $in)->execute();

        unset($in);
    }

    private function mergeAttr($leadId , $cloneIds , $modelType){

        // create sql part for IN condition by imploding comma after each id
        $in = '(' . implode(',', $cloneIds) .')';

        //get our lead record
        $leadRecord = $modelType::model()->findByPk($leadId);

        //get attributes
        $listOfAtts = $leadRecord->attributeNames();
        //for each attribute to see who has the newest changlog


        foreach($listOfAtts as $att){
                //find newest changeLog

                $change = Changelog::model()->findAll(array("condition" => "type = '" . $modelType . "' AND fieldName = '" . $att . "'  AND itemId IN " . $in . ""
                        ,"order" => "timestamp DESC", "limit" => 1));
                //check to make sure not same record and timestamp is older then record created
                if(isset($change[0]) && $leadRecord->createDate < $change[0]->timestamp && $leadId != $change[0]->itemId){

                        $leadRecord->$att = $change[0]->newValue;

                }


                //if the field is still null check the other records 
                if(!isset($leadRecord->$att) || empty($leadRecord->$att)){
                        $value = NULL;
                        $recordDate = 0;
                        $recID = NULL;
                        foreach($cloneIds as $cId){
                                $clone = $modelType::model()->findByPk($cId);
                                if(isset($clone->$att) && !empty($clone->$att) && $recordDate < $clone->createDate){

                                        $value = $clone->$att;
                                        $recordDate = $clone->createDate;
                                        $recID = $cId;
                                 
                                }
				unset($clone);

                        }
                        //if we have a value save it
                        if(isset($value)){
                                $leadRecord->$att = $value;
                        }
                 
                }


                unset($change);


        }
        //now move all the reocrds
        Yii::app()->db
                        ->createCommand('UPDATE x2_changelog SET itemId = ' . $leadId . ' WHERE type = "' . $modelType . '" AND  itemId IN' . $in)->execute();
        //save changes
        $leadRecord->assignedTo = $leadRecord->c_listing_email_owner__c;
        $leadRecord->save();
        unset($leadRecord);
        unset($in);
        unset($listOfAtts);

    }

    private function mergeDelete($leadId , $cloneIds , $modelType){
        //take each id and delete
        foreach($cloneIds as $ID){
                $modelType::model()->findByPk($ID)->delete();
        }
    }



   public function checkShareLog(){
        
	$listChanges = Yii::app()->db->createCommand('select itemId , fieldName, count(*) from x2_changelog where fieldName = "sharedTo" AND type = "Contacts"  AND timestamp < 1563354081 group by itemId having count(*) > 1')->queryAll();
	echo count($listChanges) . PHP_EOL;
	
        
       
        foreach($listChanges as $change){
		$transaction = Yii::app()->db->beginTransaction();

		try {
                echo $change["itemId"] . PHP_EOL;
                $shareAr = array();
                $changes = Changelog::model()->findAll(array("condition" => "type = 'Contacts' AND fieldName = 'sharedTo'  AND itemId = '" . $change["itemId"] . "'"));
                foreach($changes as $change){
			echo $change->newValue . PHP_EOL;
			if(is_array(json_decode($change->newValue)))
                        	$shareAr = array_merge($shareAr , json_decode($change->newValue));
                }
		$buy = Contacts::model()->findByPk($change["itemId"]);
		if(isset($buy)){
			echo $buy->email . PHP_EOL;
			$oldAr = json_decode($buy->sharedTo);
			if(is_array($oldAr)){
				echo json_encode($oldAr) . PHP_EOL;
				 $shareAr = array_merge($shareAr , $oldAr);
			}
	                $buy->sharedTo = json_encode(array_values(array_unique($shareAr)));
			echo $buy->sharedTo . PHP_EOL;
		
	               	 $buy->save();
               }
                  $transaction->commit();
                   // actions to do on success (redirect, alert, etc.)
                } catch (Exception $e) {
                    $transaction->rollBack();
                    // other actions to perform on fail (redirect, alert, etc.)
                    echo $e->getTraceAsString();
                }

        }




    }


}
