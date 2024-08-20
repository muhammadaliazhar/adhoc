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
class Employees extends X2Model {

	/**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_employees'; }

        public function beforeSave() {
             $mediaAssociatedWithEmployee = X2Model::model('Media')->findBySQL('SELECT * FROM x2_media WHERE associationType="employees" AND associationId=:id AND (mimetype="image/jpeg" OR mimetype="image/png" OR mimetype="image/gif" OR mimetype="image/jpg") AND name NOT LIKE "%NDA%" AND fileName NOT LIKE "%NDA%"',array(':id'=>$this->id));

            if (!empty($mediaAssociatedWithEmployee)) {
                 $employeeImageUrl = $mediaAssociatedWithEmployee->getPublicUrl();
                if (empty($this->c_employee_imagetemp__c))
                   $this->c_employee_imagetemp__c = $employeeImageUrl;
            }

            return parent::beforeSave();
        }

        public function afterSave() {
            // Update franchise and region field of related use record
            $user = User::model()->findByAttributes(array('username' => $this->c_user__c));
            $office = Offices::model()->findByAttributes(array('nameId' => $this->c_office_location__c));
            $franchise = Franchisees::model()->findByAttributes(array('nameId' => $this->c_franchisee__c));

            if (!isset($user) || !isset($office) || !isset($franchise)) {
                $this->log("Could not find either user/office/franchise of employee: $this->name");
                return parent::afterSave();
            }

            $user->franchise = $franchise->name;
            $user->region = $office->c_region;
            if(!$user->save())
                $this->log("Could not save user during employee update: $user->username");
            
            return parent::afterSave();
        }


	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'employees'
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
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($pageSize = null) {
		$criteria=new CDbCriteria;
		return $this->searchBase($criteria, $pageSize);
	}

        private $quick = false;
        public function setQuick($set) {
            $this->quick = $set;
        }

        public function isQuick() {
            return $this->quick;
        }
	
	   public function setNewListings(){
        $relatedID = $this->c_user__c;
        $startYear = strtotime('01/01/'.date("Y"));
        $newListNum = X2Model::model('Listings2')->findAll(array(
                'condition'=>'c_client_agreement_date__c>=:createDate AND assignedTo=:assignedTo',
                'params'=>array(':createDate'=>$startYear, ':assignedTo'=>$relatedID),
        ));
        $this->c_2024_Actual_Listings = count($newListNum);
        return;
    }

    public function linkFranch(){
        $franch = Franchisees::model()->findByAttributes(array('nameId'=>$this->c_franchisee__c));
        if(isset($franch)){
            return CHtml::link($franch->name,array("/franchisees/".$franch->id));

        }
    }



    /**
    * Returns related employee field soldDeals (does not save in Profile model)
    */
    public function setSoldDeals(){
        $relatedID = $this->c_user__c;
        $startYear = strtotime('01/01/'.date("Y"));
        //$newListNum = X2Model::model('opportunities')->findAll(array(
        //        'condition'=>'c_actual_close_date_c >=:createDate AND assignedTo=:assignedTo AND salesStage = "Sold"',
        //        'params'=>array(':createDate'=>$startYear, ':assignedTo'=>$relatedID),
        //));
        //$this->c_2020_Actual_Sold_Deals = count($newListNum);

        $sellerRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','c_selleragent'=>$this->nameId, 'c_cobroke'=>1), 'c_actual_close_date_c > ' . $startYear );

        $buyerRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','c_buyer_agent_c'=>$this->nameId, 'c_cobroke'=>1), 'c_actual_close_date_c > ' . $startYear);

        $assignedRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','assignedTo'=>$relatedID, 'c_cobroke'=>0), 'c_actual_close_date_c > ' . $startYear);


        $this->c_2024_Actual_Sold_Deals = count($sellerRecords) + count($buyerRecords) + count($assignedRecords);


        $startYear23 = strtotime('01/01/'.date("Y"));
        $startYear22 = strtotime('01/01/'.(date("Y") - 1 ));

        $sellerRecords22 = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','c_selleragent'=>$this->nameId, 'c_cobroke'=>1), 'c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23 );

        $buyerRecords22 = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','c_buyer_agent_c'=>$this->nameId, 'c_cobroke'=>1), 'c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23);

        $assignedRecords22 = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','assignedTo'=>$relatedID, 'c_cobroke'=>0), 'c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23);

        $this->c_2023_Actual_Sold_Deals = count($sellerRecords22) + count($buyerRecords22) + count($assignedRecords22);

        return;
    }

    /**
    * Returns related employee field grossCommission (does not save in Profile model)
    */
    public function setGrossCommission(){
        $relatedID = $this->c_user__c;
        $startYear = strtotime('01/01/'.date("Y"));
        $newListNum = X2Model::model('Listings2')->findAll(array(
                'condition'=>'c_date_listing_closed_sold__c>=:createDate AND assignedTo=:assignedTo AND c_deal_stage__c = "Sold"
                AND c_commission_rate__c IS NOT NULL AND c_sold_price__c IS NOT NULL',
                'params'=>array(':createDate'=>$startYear, ':assignedTo'=>$relatedID),
        ));
        $count = count($newListNum);
        $totalCommission = 0;
        if ($count > 0){
                foreach($newListNum as $listing){

                    if(isset($listing->c_total_commission__c)){
                          //$totalCommission += $listing->c_GrossCommission;
                    }else{
                          //$totalCommission += ($listing->c_sold_price__c * ($listing->c_commission_rate__c / 100));
                    }
                }
        }

        //now get the deals 
        $sellerRecords = Opportunity::model()->findAllByAttributes(array('c_selleragent'=>$this->nameId, 'c_cobroke'=>1), 'salesStage in ("Sold","Other Income") AND c_actual_close_date_c > ' . $startYear );
        $count = count($sellerRecords);
        if ($count > 0){
                foreach($sellerRecords as $deal){

                          $totalCommission += $deal->c_Sell_side_actual;
                }
        }

        $buyerRecords = Opportunity::model()->findAllByAttributes(array('c_buyer_agent_c'=>$this->nameId, 'c_cobroke'=>1), 'salesStage in ("Sold","Other Income") AND c_actual_close_date_c > ' . $startYear);

        $count = count($buyerRecords);
        if ($count > 0){
                foreach($buyerRecords as $deal){

                          $totalCommission += $deal->c_Buy_side_actual;
                }
        }

        $assignedRecords = Opportunity::model()->findAllByAttributes(array('assignedTo'=>$relatedID, 'c_cobroke'=>0), 'salesStage in ("Sold","Other Income") AND c_actual_close_date_c > ' . $startYear);
        $count = count($assignedRecords);
        if ($count > 0){
                foreach($assignedRecords as $deal){

                          $totalCommission += $deal->c_actual_commission_c;
                }
        }


        $this->c_2024_Actual_Gross_Commisions = (int)$totalCommission;

        //this code will be for getting the 2022 gross since agents are still adding deals
        $totalCommission2022 = 0;
        $startYear23 = strtotime('01/01/'.date("Y"));
        $startYear22 = strtotime('01/01/'.(date("Y") - 1 ));

        //now get the deals 
        $sellerRecords = Opportunity::model()->findAllByAttributes(array('c_selleragent'=>$this->nameId, 'c_cobroke'=>1), 'salesStage in ("Sold","Other Income") AND c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23);
        $count = count($sellerRecords);
        if ($count > 0){
                foreach($sellerRecords as $deal){

                          $totalCommission2022 += $deal->c_Sell_side_actual;
                }
        }

        $buyerRecords = Opportunity::model()->findAllByAttributes(array('c_buyer_agent_c'=>$this->nameId, 'c_cobroke'=>1), 'salesStage in ("Sold","Other Income") AND c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23);

        $count = count($buyerRecords);
        if ($count > 0){
                foreach($buyerRecords as $deal){

                          $totalCommission2022 += $deal->c_Buy_side_actual;
                }
        }

        $assignedRecords = Opportunity::model()->findAllByAttributes(array('assignedTo'=>$relatedID, 'c_cobroke'=>0), 'salesStage in ("Sold","Other Income") AND c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23);
        $count = count($assignedRecords);
        if ($count > 0){
                foreach($assignedRecords as $deal){

                          $totalCommission2022 += $deal->c_actual_commission_c;
                }
        }

         $this->c_2023_Actual_Gross_Commisions = (int)$totalCommission2022;

        //adding code to also get the last year deal count
        $sellerRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','c_selleragent'=>$this->nameId, 'c_cobroke'=>1), 'c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23 );

        $buyerRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','c_buyer_agent_c'=>$this->nameId, 'c_cobroke'=>1), 'c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23);

        $assignedRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','assignedTo'=>$relatedID, 'c_cobroke'=>0), 'c_actual_close_date_c > ' . $startYear22 . ' AND c_actual_close_date_c < ' . $startYear23);


        $this->c_2023_Actual_Sold_Deals = count($sellerRecords) + count($buyerRecords) + count($assignedRecords);




        //this will be used to get the total for the contest
        $startContest = strtotime('07/01/'.date("Y"));
        $newListNum = X2Model::model('Listings2')->findAll(array(
                'condition'=>'c_date_listing_closed_sold__c>=:createDate AND assignedTo=:assignedTo AND c_deal_stage__c = "Sold"
                AND c_commission_rate__c IS NOT NULL AND c_sold_price__c IS NOT NULL AND (c_CurrentDeal IS NULL OR c_CurrentDeal = "")',
                'params'=>array(':createDate'=>$startContest, ':assignedTo'=>$relatedID),
        ));
        $count = count($newListNum);
        $totalContest = 0;
        //if ($count > 0){
        //        foreach($newListNum as $listing){

        //            if(isset($listing->c_total_commission__c)){
        //                  $totalContest += $listing->c_GrossCommission;
        //            }else{
        //                  $totalContest += ($listing->c_sold_price__c * ($listing->c_commission_rate__c / 100));
        //           }
        //        }
        //}
        //now get the deals 
        $sellerRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','c_selleragent'=>$this->nameId, 'c_cobroke'=>1), 'c_actual_close_date_c > ' . $startContest );
        $count = count($sellerRecords);
        if ($count > 0){
                foreach($sellerRecords as $deal){

                          $totalContest += $deal->c_Sell_side_actual;
                }
        }

        $buyerRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','c_buyer_agent_c'=>$this->nameId, 'c_cobroke'=>1), 'c_actual_close_date_c > ' . $startContest);

        $count = count($buyerRecords);
        if ($count > 0){
                foreach($buyerRecords as $deal){

                          $totalContest += $deal->c_Buy_side_actual;
                }
        }

        $assignedRecords = Opportunity::model()->findAllByAttributes(array('salesStage'=>'Sold','assignedTo'=>$relatedID, 'c_cobroke'=>0), 'c_actual_close_date_c > ' . $startContest);
        $count = count($assignedRecords);
        if ($count > 0){
                foreach($assignedRecords as $deal){

                          $totalContest += $deal->c_actual_commission_c;
                }
        }

    $this->c_contestTotal = $totalContest;


        return;
    }

    public function setSalesValues(){
        $this->setNewListings();
        $this->setSoldDeals();
        $this->setGrossCommission();
        $this->save();

        return;
    }


}
