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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_template".
 * @package application.modules.template.models
 */
class Inquiries extends X2Model {

	/**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
	 */
	public static function model($className=__CLASS__) { 
		return parent::model($className); 
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { 
		return 'x2_inquiries'; 
	}

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'inquiries'
			),
 	                'ERememberFiltersBehavior' => array(
				'class'=>'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
			'InlineEmailModelBehavior' => array(
				'class'=>'application.components.behaviors.InlineEmailModelBehavior',
			)
		));
	}

	/**
     	* Sets tracking key before save
   	*
   	* @return boolean whether or not to save
     	*/
    	public function beforeSave() {
            $newName = '';
               $i = 0;
               if($this->isNewRecord) {
                  $newName = uniqid('IN-', true);
                  $checkName = X2Model::model('Inquiries')->findByAttributes(array('name' => $newName));
                  while(isset($checkName)) {
                       if($i == 100){ //Check to see if we've created a name that already exists 100 times
                          exit();
                       }
                       $newName = uniqid('IN-', true);
                       $checkName = X2Model::model('Inquiries')->findByAttributes(array('name' => $newName));
                       $i++;
                  }
                  $this->name = $newName;
                  $this->c_contact_name__c = substr($this->c_contact__c, 0, strpos($this->c_contact__c, '_'));
                  $listing = Yii::app()->db->createCommand()
                               ->select('*')
                               ->from('x2_listings2')
                               ->where('nameId = ' . "'" . strtr($this->c_listing__c, array("'" => "\'", '"' => '\"')) . "'")
                               ->queryAll();
                  $buyerOwner = Yii::app()->db->createCommand()
                               ->select('*')
                               ->from('x2_contacts')
                               ->where('nameId = ' . "'" . strtr($this->c_contact__c, array("'" => "\'", '"' => '\"')) . "'")
                               ->queryAll();
                  $this->c_listing_owner__c = $listing ? $this->c_listing_agent__c = $listing[0]['assignedTo'] : NULL;
                  $this->c_listing_name__c = $listing ? $listing[0]['name'] : NULL;
                  $this->c_listing_number__c = $listing ? $listing[0]['c_listing_number__c'] : NULL;
                  //$this->assignedTo = $buyerOwner ? $buyerOwner[0]['assignedTo'] : NULL;
		  $user = User::getMe();
                  if(isset($user) && (Groups::userHasRole($user->id, 'Agent') || Groups::userHasRole($user->id, 'Franchise'))) {
                     $this->assignedTo = $user->username;
                     $this->updatedBy = $user->username;
                  } elseif(isset($buyerOwner[0])){
                     $this->assignedTo = $buyerOwner[0]['assignedTo'];
                     $this->updatedBy = $buyerOwner[0]['assignedTo'];

		  }else{
                     $this->assignedTo = $listing ? $listing[0]['assignedTo'] : NULL;
                     $this->updatedBy = $listing ? $listing[0]['assignedTo'] : NULL;
                  }
               }
               return parent::beforeSave();
    	}

        /**
         * Retrieves a list of models based on the current search/filter conditions.
         * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
         */
        public function search($modelId = null, $model = null, $pageSize = null) {
                $criteria = new CDbCriteria;
                $parameters = array('limit'=>ceil(Profile::getResultsPerPage()));
                $criteria->scopes = array('findAll'=>array($parameters));
                if(isset(Yii::app()->controller->module)) {
                        $moduleName = Yii::app()->controller->module->getName();
                        $modelName = X2Model::getModelName($moduleName);
                        if(($moduleName == 'listings2') || ($moduleName == 'contacts')) {
                                $recordName = Yii::app()->db->quoteValue($model['nameId']);
                                if($modelName == 'Listings2') {
                                        $criteria->condition = 'c_listing__c = ' . $recordName;
                                } else {
                                        $criteria->condition = 'c_contact__c = ' . $recordName;
                                }
                                return new SmartActiveDataProvider(get_class($this), array(
                                        'sort'=>array(
                                                'defaultOrder'=>'name ASC',
                                        ),
                                        'pagination'=>array(
                                                'pageSize'=>Profile::getResultsPerPage(),
                                        ),
                                        'criteria'=>$criteria,
                                ));
                        }
                }
                if (!Yii::app()->params->isAdmin) {
                    $accessLevel = Yii::app()->user->checkAccess('InquiriesView') ? 1 : 0;
                    $conditions = $this->getAccessConditions($accessLevel);
                    foreach ($conditions as $arr) {
                        $criteria->addCondition($arr['condition'], $arr['operator']);
                        $criteria->params = array_merge($criteria->params, $arr['params']);
                    }
                }
                return $this->searchBase($criteria, $pageSize);
        }

}
