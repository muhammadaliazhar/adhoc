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
 * This is the model class for table "x2_opportunities".
 *
 * @package application.modules.opportunities.models
 */
class Opportunity extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Opportunity the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_opportunities'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'opportunities'
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		));
	}

    /**
     * Defines contact rules
     * 
     * @return array validation rules for model attributes.
     */
    public function rules() {
        $rules = array_merge(parent::rules(), array(
            /* x2customstart */ /* JLaw Jun20 2019 */
            // call validator during import
            array(
                'salesStage', 'checkCommission', 'except' => 'skipCheckCommission',
            ),
            /* x2customend */
        ));
        return $rules;
    }

    /* x2customstart */ /* Jack Dec 29 2020 */
    // validate that commission total makes sense
    public function checkCommission() {
        if ($this->salesStage != "Sold") return;

        if ($this->c_cobroke && $this->c_actual_commission_c != ($this->c_Sell_side_actual + $this->c_Buy_side_actual )) {
                $this->addError('salesStage', 'The Actual Commission is not equal to the Buy Side and Sell Side Commissions.');
            }

        if (empty($this->c_sold_price_) || empty($this->c_actual_commission_c) || empty($this->c_actual_commission_percent_c) ||
                    empty($this->c_actual_close_date_c) || empty($this->c_deal_summary)){
            $this->addError('salesStage', 'Please make sure "Sold Price", "Actual Commission($)", "Actual Commission(%)", "Actual Close Date", and "Deal Summary" are filled.');
        }


    }


	/**
	 * Formats data for associatedContacts before saving
	 * @return boolean whether or not to save
	 */
	public function beforeSave() {
		if(isset($this->associatedContacts))
			$this->associatedContacts = self::parseContacts($this->associatedContacts);


                if(!$this->isNewRecord) {

                   $user = User::getMe();


                   if(isset($this->oldAttributes) && array_key_exists('assignedTo', $this->oldAttributes)) {
                      $oldAssignedTo = User::model()->findByAttributes(array('username' => $this->oldAttributes['assignedTo']));
                   }

                   //check if this is a franchise trying to change an assignedTo then don't set assignedTo
                   if(isset($oldAssignedTo) && $oldAssignedTo->username != $this->assignedTo) {
                        $this->pushToSharedTo($oldAssignedTo->id ,false);

                   } elseif(isset($user) && (Groups::userHasRole($user->id, 'Agent') || Groups::userHasRole($user->id, 'Franchise'))) { // If user is an agent
                      //$this->assignedTo = $user->username;

                      // Add the user to the sharedTo field if they aren't already
                      if(isset($oldAssignedTo) && Groups::userHasRole($oldAssignedTo->id, 'Agent')) {
                         $this->pushToSharedTo(array($oldAssignedTo->id, $user->id), false);
                      } else {
                         $this->pushToSharedTo($user->id, false);
                      }

                   } else {
                      if(isset($oldAssignedTo) && (Groups::userHasRole($oldAssignedTo->id, 'Agent') || Groups::userHasRole($oldAssignedTo->id, 'Franchise'))) {
                         $this->pushToSharedTo($oldAssignedTo->id, false);
                      }

                      $newAssignedTo = User::model()->findByAttributes(array('username' => $this->assignedTo));
                      if(isset($newAssignedTo) && isset($this->oldAttributes['assignedTo']) && $this->oldAttributes['assignedTo'] !== $this->assignedTo) {
                           $this->pushToSharedTo($newAssignedTo->id, false);
                      }
                   }
                   //if it is not an admin user, keep all users in sharedTo
                  if(!Yii::app()->params->isAdmin && isset($this->oldAttributes["sharedTo"])){
                        $this->pushToSharedTo(json_decode($this->oldAttributes["sharedTo"]), false);
                  }


                }

            //this will check if we need to put into workflow
            if($this->attributeChanged ('salesStage') && $this->salesStage == "Sold" && !empty($this->c_listinglookup__c)){
                $list = Listings2::model()->findByAttributes(array('nameId'=> $this->c_listinglookup__c));
                if(isset($list)){
                    $list->c_tom_marketing_status__c = "DO NOT MARKET";
                    $list->save();
                    if($list->c_WorkFlowTrack ==  "ListEmailSent")$this->c_WorkFlowTrack = "EmailSent";
                }
            }




		return parent::beforeSave();
	}


        public function afterSave(){
                if($this->attributeChanged ('assignedTo') || $this->attributeChanged ('c_buyer_agent_c') || $this->attributeChanged ('c_selleragent')
                    || $this->attributeChanged ('salesStage')|| $this->attributeChanged ('c_actual_commission_c')){
                    if(isset($this->assignedTo)){
                            $employee = X2Model::model('Employees')->findByAttributes(array('c_user__c' => $this->assignedTo));
                            if (!is_null($employee))
                                 $employee->setSalesValues();
                    }
                    if(isset($this->c_buyer_agent_c)){
                            $employee = X2Model::model('Employees')->findByAttributes(array('nameId' => $this->c_buyer_agent_c));
                            if (!is_null($employee))
                            $employee->setSalesValues();
                    }
                    if(isset($this->c_selleragent)){
                            $employee = X2Model::model('Employees')->findByAttributes(array('nameId' => $this->c_selleragent));
                            if (!is_null($employee))
                                $employee->setSalesValues();
                    }


                }
                 parent::afterSave();



        }

    /**
     * Save new ids to this Contact's sharedTo field.
     *
     * @param mixed $ids int or int array of ids
     * @param boolean $save toggle model save
     * @return true if successful, false otherwise
     */
    public function pushToSharedTo($ids, $save=true) {
        if (!isset($ids)) return true;
        $ids = is_array($ids) ? $ids : [$ids];
        $sharedTo = json_decode($this->sharedTo) ? json_decode($this->sharedTo) : [];
        //$mergedValues = array_values(array_unique(array_merge($ids, $sharedTo)));
        $mergedValues = $sharedTo;
        foreach ($ids as $id) {
            if (!is_numeric($id) || Groups::userHasRole($id, 'Application Admin') || $id == 4143 || $id == 1) continue;
            if (!in_array($id, $mergedValues))$mergedValues[] = (string) $id;
        }
        $this->sharedTo = json_encode($mergedValues);
        if (!$save) return true;
        if (!$this->save(true, ['sharedTo'])) {
            Yii::log("Contact $this->id: Could not add IDs to sharedTo.", 'error', 'application');
            return false;
        }
        $this->refresh();
        if (json_decode($this->sharedTo) === null) {
            Yii::log("Contact $this->id: JSON format error for sharedTo.", 'error', 'application');
            $this->sharedTo = json_encode($sharedTo);
            $this->save(true, ['sharedTo']);
            return false;
        }
        return true;
    }




	public static function getNames() {
		$arr = Opportunity::model()->findAll();
		$names = array(0=>'None');
		foreach($arr as $opportunity)
			$names[$opportunity->id] = $opportunity->name;

		return $names;
	}

	public static function parseUsers($userArray){
		return implode(', ',$userArray);
	}

	public static function parseUsersTwo($arr){
		$str="";
        if(is_array($arr)){
            $arr=array_keys($arr);
            $str=implode(', ',$arr);
        }

		return $str;
	}

	public static function parseContacts($contactArray){
        if(is_array($contactArray)){
            return implode(' ',$contactArray);
        }else{
            return $contactArray;
        }
	}

	public static function parseContactsTwo($arr){
		$str="";
		foreach($arr as $id=>$contact){
			$str.=$id." ";
		}
		return $str;
	}

	public static function getOpportunityLinks($accountId) {

		$allOpportunities = X2Model::model('Opportunity')->findAllByAttributes(array('accountName'=>$accountId));

		$links = array();
		foreach($allOpportunities as $model) {
			$links[] = CHtml::link($model->name,array('/opportunities/opportunities/view','id'=>$model->id));
		}
		return implode(', ',$links);
	}

	public static function editContactArray($arr, $model) {

        $rels=Relationships::model()->findAllByAttributes(array('firstType'=>'Contacts','secondType'=>'Opportunity','secondId'=>$model->id));
        $pieces=array();
        foreach($rels as $relationship){
            $contact=X2Model::model('Contacts')->findByPk($relationship->firstId);
            if(isset($contact)){
                $pieces[$relationship->firstId]=$contact->name;
            }
        }
		unset($arr[0]);
		foreach($pieces as $id=>$contact){
			if(isset($arr[$id])){
                unset($arr[$id]);
            }
		}

		return $arr;
	}

	public static function editUserArray($arr, $model) {

		$pieces=explode(', ',$model->assignedTo);
		unset($arr['Anyone']);
		unset($arr['admin']);
		foreach($pieces as $user){
			if(array_key_exists($user,$arr)){
				unset($arr[$user]);
			}
		}
		return $arr;
	}

	public static function editUsersInverse($arr) {

		$data=array();

		foreach($arr as $username){
			if($username!='' && !is_numeric($username))
				$data[]=User::model()->findByAttributes(array('username'=>$username));
			elseif(is_numeric($username))
				$data[]=Groups::model()->findByPK($username);
		}

		$temp=array();
		if(isset($data)){
			foreach($data as $item){
				if(isset($item)){
					if($item instanceof User)
						$temp[$item->username]=$item->firstName.' '.$item->lastName;
					else
						$temp[$item->id]=$item->name;
				}
			}
		}
		return $temp;
	}

	public static function editContactsInverse($arr) {
		$data=array();

		foreach($arr as $id){
			if($id!='')
				$data[]=X2Model::model('Contacts')->findByPk($id);
		}
		$temp=array();

		foreach($data as $item){
			$temp[$item->id]=$item->firstName.' '.$item->lastName;
		}
		return $temp;
	}

	public function search($resultsPerPage=null, $uniqueId=null) {
		$criteria=new CDbCriteria;
		// $parameters=array("condition"=>"salesStage='Working'",'limit'=>ceil(Profile::getResultsPerPage()));
		$parameters=array('limit'=>ceil(Profile::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria, $resultsPerPage);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}

        private $quick = false;
        public function setQuick($set) {
            $this->quick = $set;
        }

        public function isQuick() {
            return $this->quick;
        }


}
