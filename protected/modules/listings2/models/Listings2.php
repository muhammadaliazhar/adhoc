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
class Listings2 extends X2Model {

	/**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_listings2'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'listings2'
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
        if (isset($_GET['name'])) {
            $name = $_GET['name'];
            $cond = isset($name) ? "name LIKE '%$name%'" : '';
            $criteria = new CDbCriteria(array('condition' => $cond, 'order' => 'lastUpdated DESC'));
        }
		return $this->searchBase($criteria, $pageSize);
	}

        /**
         * Defines listing rules
         *
         * @return array validation rules for model attributes.
         */
         public function rules() {
            $rules = array_merge(parent::rules(), array(
                array(
                'c_ad_headline__c',
                'length',
                'allowEmpty'=>false,
                'min'=>1,
                'max'=>65,
                'tooShort'=>'Ad Headline must be at least 1 character',
                'tooLong'=>'Ad Headline cannot contain more than 65 characters',
                ),
            ));
            return $rules;
        }

        /**
         * Sets tracking key before save
         * Transworld: Sets various fields in order for 
         * 
         * @return boolean whether or not to save
         */
        public function beforeSave() {
            $name = $this->sanitizeUrlFormula($this->c_ad_headline__c);
            $officeNum = 0;
	        $me = User::getMe();
            if (empty($this->c_office_number__c) && isset($_POST['Listings2'])) {
	            $officeNum = $this->getOfficeNum();
                $this->c_office_number__c = $officeNum;
            } else if (!is_numeric($this->c_office_number__c)){
                $officeNum = $this->getOfficeNum();
                $this->c_office_number__c = $officeNum;
            } else {
                $officeNum = $this->c_office_number__c;
            }
            $officeRecord = X2Model::model('Offices')->findByAttributes(array('c_office_number__c' => $officeNum));

            if (isset($name) && trim($name) !== '' && isset($officeRecord)) {
                $name = str_replace(' ', '-', $name);
		        $name = str_replace('/', '-', $name);
                $officeName = str_replace(" ", "-", $officeRecord->c_site_shortname__c);
                $listingUrlBase = $this->generateUrlBase($officeRecord);
                $listingUrl = $listingUrlBase . "locations/" . $officeName . "/" . "listings/" . $name;
                $this->c_url_formula__c = $name;
                $this->c_listing_url__c = $listingUrl;
          
            }

	    
            //if the office changed update the listing number
            if($this->attributeChanged ('c_office_number__c') && !empty($this->c_office_number__c) && !empty($this->c_listing_number__c)){
                $oldNumber = explode("-", $this->c_listing_number__c);
                $this->c_listing_number__c =  $this->c_office_number__c . "-" . $oldNumber[1];

            }


            if (!isset($this->c_office_number__c) || empty($this->c_office_number__c))
                $this->c_office_number__c = $officeNum;

            if (!isset($this->c_listing_number__c) || empty($this->c_listing_number__c))
                $this->c_listing_number__c = $this->genListNum();

	    if ((!isset($this->c_office_location_shortname__c) || empty($this->c_office_location_shortname__c)) && !empty($officeRecord) && !empty($officeRecord->c_site_shortname__c)) {
                $this->c_office_location_shortname__c = $officeRecord->c_site_shortname__c;
	    }

            // Auto-populate employee and franchisee fields
            $currentUser = User::getMe();
            if ($currentUser){
                $employee = Employees::model()->findByAttributes(array('c_user__c' => $currentUser->username));
            if (isset($employee)) {
              if (!isset($this->c_employee__c) || empty($this->c_employee__c))
                  $this->c_employee__c = $employee->nameId;

              if (!isset($this->c_franchisee__c) || empty($this->c_franchisee__c))
                  $this->c_franchisee__c = $employee->c_franchisee__c;
            }
	  }

           //check if there is a seller, if so get it's values
            if(isset($this->c_Seller)){
                $seller = Sellers2::model()->findByAttributes(array('nameId'=>$this->c_Seller));
                if(isset($seller)){
                        $this->c_lead_source__c = $seller->c_leadsource;
                        $this->c_lead_generated_by = $seller->c_lead_generated_by__c;


                }

            }

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


            $this->zipCountyCheck();

            return parent::beforeSave();
        }


        //this will be a function to check for some address where we want county to = city
        public function zipCountyCheck(){

            $zipArray = array(22301,20111,20112,20115,20117,20119,20120,20121,20124,20129,20132,20135,20136,20137,20141,20143,20144,20147,20148,20149,20151,20152,20155,20158,20164,20165,20166,20166,20169,20170,20171,20175,20176,20180,20181,20184,20186,20187,20189,20190,20191,20192,20194,20196,20197,20198,20598,20598,20598,20598,20598,20598,20598,20598,20598,22003,22015,22025,22026,22027,22302,22031,22032,22033,22034,22035,22036,22037,22041,22042,22043,22044,22304,22060,22066,22067,22079,22081,22082,22095,22096,22101,22102,22107,22108,22109,22118,22119,22124,22134,22135,22150,22151,22152,22153,22156,22158,22159,22160,22161,22172,22180,22181,22182,22185,22191,22192,22193,22201,22202,22203,22204,22205,22206,22207,22209,22212,22213,22214,22217,22225,22226,22227,22230,22240,22241,22242,22243,22244,22245,22246,22305,22311,22303,22314,22331,22306,22307,22308,22309,22310,22332,22312,22333,22315,22334,22350,24201,24205,24416,23030,22405,22406,22407,22408,22412,22427,22428,22432,22433,22435,22436,22437,22438,22443,22448,22454,22460,22469,22473,22476,22480,22482,22485,22488,22501,22503,22508,22511,22514,22520,22534,22535,22538,22539,22542,22546,22551,22553,22554,22556,22560,22567,22572,22576,22578,22580,22902,22602,22603,22610,22611,22620,22624,22625,22627,22630,22637,22639,22641,22642,22643,22644,22645,22649,22650,22652,22654,22655,22656,22657,22660,22663,22664,22701,22712,22713,22714,22715,22718,22719,22720,22723,22724,22726,22727,22728,22729,22730,22731,22732,22733,22734,22735,22737,22738,22740,22741,22742,22743,22747,22903,22904,22907,22810,22811,22812,22815,22820,22821,22824,22827,22830,22831,22834,22835,22840,22841,22842,22843,22844,22845,22846,22847,22849,22850,22851,22853,22901,22908,22910,23320,23321,23322,22909,23323,22911,22920,22922,22923,22931,22932,22935,22936,22937,22938,22939,22940,22942,22943,22946,22947,22948,22949,22952,22958,22959,22960,22963,22964,22967,22968,22969,22971,22972,22973,22974,22976,23324,23002,23004,23005,23009,23011,23015,23022,23024,23027,23325,23032,23035,23038,23039,23040,23043,23047,23050,23055,23056,23059,23060,23061,23062,23063,23065,23068,23069,23070,23071,23072,23076,23083,23084,23085,23086,23089,23092,23093,23102,23103,23106,23109,23110,23111,23112,23113,23114,23116,23117,23119,23120,23123,23124,23125,23126,23128,23129,23130,23138,23139,23140,23141,23146,23148,23149,23150,23153,23156,23163,23326,23169,23834,23175,23176,23177,23181,24426,24540,24541,23192,23847,22030,22046,23604,23651,23851,22401,23226,23227,23228,23229,23230,23231,24333,23233,23234,23235,23236,23237,23238,23630,23250,23661,23663,23664,23666,23667,23668,23669,23681,23288,22801,22802,22807,23273,23860,23294,24450,23297,24501,23301,23302,23303,23306,23308,23310,23314,23315,24502,24503,24504,24513,24514,24515,20110,23336,23337,23350,23354,23356,23357,23359,23395,23396,23404,23405,23410,23415,23416,23417,23418,23420,23421,23426,23430,24112,23601,23602,23603,23605,23605,23606,23442,23607,23608,23628,23502,23503,23504,23505,23507,23508,23509,23510,23511,23513,23515,23517,23487,23488,23518,23519,23523,23529,23551,24273,23803,23805,23662,23701,23702,23703,23704,23707,23708,23709,23173,23219,23220,23221,23222,23223,23224,23225,23232,23249,23269,23274,23276,23278,23279,23282,23286,23665,23289,23290,23291,23292,23293,23690,23691,23692,23693,23696,23295,23298,24011,24012,24013,24014,24015,23801,24016,24017,24040,23821,23824,23827,23828,23829,23830,23831,23832,23833,24042,23836,23837,23838,23839,23840,23841,23842,23843,23844,23845,24043,23850,24401,23856,23857,23432,23866,23867,23868,23870,23872,23874,23875,23876,23878,23879,23881,23882,23883,23885,23887,23888,23889,23890,23891,23893,23894,23897,23898,23901,23909,23915,23917,23919,23920,23921,23922,23923,23924,23927,23930,23934,23936,23937,23938,23942,23944,23947,23950,23950,23952,23954,23958,23959,23960,23962,23963,23964,23966,23967,23968,23970,23974,23976,23433,23434,23435,23436,23437,23438,23168,24018,24019,23451,23452,23453,24050,24053,24054,24055,24059,24060,24061,24064,24065,24066,24067,24069,24070,24072,24073,24076,24077,24078,24079,24082,24083,24084,24085,24086,24087,24088,24089,24090,24091,24092,24093,24095,24101,24102,24104,24105,23454,24120,24121,24122,24124,24127,24128,24130,24131,24133,24134,24136,24137,24138,24139,24141,24147,24148,24149,24150,24151,24153,24155,24157,24162,24165,24167,24168,24171,24174,24175,24176,24179,24184,24185,23455,24202,23456,24210,24211,24216,24219,24220,24221,24224,24225,24226,24228,24230,24236,24237,24239,24243,24244,24245,24248,24251,24256,24258,24260,24263,24265,24266,24270,24271,24272,23457,24277,24279,24280,24281,24282,24283,24292,24293,24301,24311,24312,24313,24314,24315,24316,24317,24318,24319,24323,24324,24325,24326,24328,24330,23459,24340,24343,24347,24348,24350,24351,24352,24354,24360,24361,24363,24366,24368,24370,24374,24375,24377,24378,24380,24381,24382,23460,24413,23461,24421,24422,23462,24430,24431,24432,24433,24435,24439,24440,24441,24442,24445,23463,24458,24459,24460,24464,24465,24467,24471,24472,24473,24476,24477,24479,24482,24483,24484,24485,24486,24487,23464,23465,23479,23806,22980,23185,23186,24517,24520,24521,24522,24523,24526,24527,24528,24529,24530,24531,24534,24536,24538,24539,23188,22601);
            //above is the list of arrays we want to have the county count
            if(in_array($this->c_postalcode, $zipArray)){
                $this->c_county__c = $this->c_city__c . " City";
            }




        }





        // The css styling of the background image same as in Real-Esate CRM.
        // Using a temporary image here for this implementation phase
        public static function getBackgroundImage() {
            $backgroundImage = 'style="background-size:0% 0%; height: 0px; background-repeat:no-repeat;';
            $gradCss = 'linear-gradient(to bottom, rgba(0,0,0,0) 50%, rgba(0,0,0,1)),';
            $backgroundImage .= 'background-image:' . $gradCss . 'url('.Yii::app()->baseUrl.'/themes/x2engine/images/portalListing.png);';
            return $backgroundImage . "\"";
        }


        public static function getDocuments($data = null) {
            if(!isset($data))
                return '';

            if(!isset($data->name) && !isset($data->fileName))
                $name = 'No Name';
            elseif(!isset($data->name))
                $name = $data->fileName;
            elseif(!isset($data->fileName))
                $name = $data->name;
            else
                $name = $data->name;

            $row = '<div class="document-thumbnail">';
            $canvasUrl=$data->getPublicUrl();
            if ($data->isPdf()) {
                $row .= "
                <script>
                    (function () {
                        let canvas = $('<canvas/>').css({'width':'100%'});
                        let pdfPromise = pdfjsLib.getDocument('".$canvasUrl."').promise;
                        pdfPromise.then(function(pdf){
                            pdf.getPage(1).then(function(page){
                                let vp = page.getViewport({scale:1});
                                canvas[0].width = vp.width;
                                canvas[0].height = vp.height/2;
                                page.render({canvasContext: canvas[0].getContext('2d'), viewport: vp});
                            });
                        });
                        $(document.currentScript).replaceWith(canvas);
                    })();
                </script>";
            } else if ($data->isImage()) {
                $row .= $data->getImage();
            }


            $row .= '</div>';
            $row .= '<div class="document-info">';
            $row .= '<div class="document-title">' . $name . '</div>';
            $row .= '<div class="document-detail">'
                    .  $data->fmtSize . ' | '
                    . (isset($data->createDate) ? 'Uploaded On ' . Formatter::formatDate($data->createDate) : 'Unknown Upload Date')
                    . '</div>';
            $property = Listings2::model()->findByPk($data->associationId);
            //this code does not work
            if (false && array_key_exists($data->id, CJSON::decode($property->id))) {
                $signDocId = CJSON::decode($property->signDocs)[$data->id];
                $signDoc = X2SignDocs::model()->findByPk($signDocId);
                $docId = $signDoc->docId;
                $mediaId = $signDoc->mediaId;
                $fieldInfo = $signDoc->fieldInfo;
            }
           $signDoc = X2SignDocs::model()->findByAttributes(array("mediaId"=> $data->id));
           if(empty($signDoc))return $row;
           $docId = $signDoc->docId;
           $mediaId = $signDoc->mediaId;
           $fieldInfo = $signDoc->fieldInfo;



            $docs = Docs::model()->findByAttributes(array('mediaId'=>$data->id));
            $options = ['class'=>'btn mx-1', 'style' => 'color: white !important; background-color: #104220;', 'id'=>'viewBtn-'.$docId, 'data-docid'=>$docId,];
            $row .= '<div class="pl-2 pt-2">';
            $row .= CHtml::link('View', '#', array_merge($options, array('onclick'=>'viewDoc('.$data->id.','.$fieldInfo.')')));
                if(!Yii::app()->user->isGuest) {
                    $row .= CHtml::link('Edit', Yii::app()->createUrl("docs/updateSignable", ['id'=>$docs->id]), $options);
                    $row .= '<button class="btn" style="color: white; background-color: #104220;" onclick="delete_document('.$data->id.', this)" data-docid="'.$docId.'">Delete</button>';
                }

            $row .= '</div></div>';
            return $row;
        }




        public function afterSave(){
                if(isset($this->c_employee__c)&& ($this->attributeChanged ('c_total_commission__c') || $this->attributeChanged ('c_deal_stage__c') 
                        || $this->attributeChanged ('assignedTo') || $this->attributeChanged ('c_employee__c')) ){
                        $employee = X2Model::model('Employees')->findByAttributes(array('nameId' => $this->c_employee__c));
                        if (!is_null($employee))
                            $employee->setSalesValues();
                 }

                 if (isset($this->c_Seller)) {
                     $seller = Sellers2::model()->findByAttributes(array('nameId' => $this->c_Seller));
		     if(isset($seller)){
                     	$seller->c_listinglookup__c = $this->nameId;
                     	$seller->save();
		     }
                 }
                    //look over buyer prefs
                 if($this->buyerPrefTest()){
                    $this->buyerPrefMatch();
                 }

                //send the listing to m and A
                if($this->checkMA()){
                    //$this->sendListingToMandA();

                }

                 parent::afterSave();
        }


    //function to test if we need to 
    private function buyerPrefTest(){
        if (!isset($this->c_state__c) || empty($this->c_state__c)) return false;
        //check to see if fields have changed
        if($this->c_client_status__c == "Active" &&  ($this->attributeChanged ('c_client_status__c') ||
            $this->attributeChanged ('c_listing_price__c') || 
            $this->attributeChanged ('c_down_payment_requested__c'))){
                return true;

         
        }
        return false;
    }


    //if buyer pref test returned true we generate matches for match history
    private function buyerPrefMatch(){
        return;
        //generate like condition
        $likeCon = "(TRUE)";
        $indus = json_decode($this->c_category__c);
        if(isset($indus) && is_array($indus)){
            foreach($indus as $in){
                if($likeCon == "(TRUE)"){
                    $likeCon = "industry_subcartegory LIKE '%" . str_replace("\\" , "\\\\" , json_encode($in)) . ":%' ESCAPE '|' ";
                }else{
                    $likeCon .= " OR industry_subcartegory LIKE '%" . str_replace("\\" , "\\\\" ,  json_encode($in)) . ":%' ESCAPE '|' ";
                }
             
            }

        }

        if(empty($indus)) return;        
        $emptyGeog = '[""]';
        $emptyIndus = '{"":[""]}';

        $query = "SELECT * FROM x2_buyer_prefs WHERE (geography LIKE '%" . $this->c_state__c . ":" . $this->c_county__c .
               "%' OR geography LIKE '%\"" . $this->c_state__c ."\"%' OR  geography = '[\"all\"]' OR geography LIKE '%\"" . $this->c_state__c .":\"%') AND (down_payment >= " . $this->c_down_payment_requested__c  . " AND price >= " . $this->c_listing_price__c .
             "  AND (" . $likeCon .
              " OR industry_subcartegory = '[\"all\"]') AND minimum_seller_discretionary_earnings <= " . $this->c_seller_discretionary_earnings__c  . ") "
                . "AND country = '" . addslashes($this->c_country__c) .  "'";
        $command = Yii::app()->db->createCommand($query);
        $result = $command->queryAll();
        //now that we have matches go and make match history


        //do to name id's will remove iid from name IDs
        $fixedCat = array();

        if(!empty($this->c_subcategory_1__c)) $fixedCat[] =  substr($this->c_subcategory_1__c, 0, strpos($this->c_subcategory_1__c, "_"));
        if(!empty($this->c_subcategory_2__c)) $fixedCat[] =  substr($this->c_subcategory_2__c, 0, strpos($this->c_subcategory_2__c, "_"));
        if(!empty($this->c_subcategory_3__c)) $fixedCat[] =  substr($this->c_subcategory_3__c, 0, strpos($this->c_subcategory_3__c, "_"));
        if(!empty($this->c_subcategory_4__c)) $fixedCat[] =  substr($this->c_subcategory_4__c, 0, strpos($this->c_subcategory_4__c, "_"));
        foreach($result as $row){

            // check for duplicate matches, do not proceed if some are found
            $dupeSql = "SELECT * FROM x2_buyer_match_history WHERE buyerId = " . $row['buyerId'] . " AND" . " listingId = $this->id" . ";";
            $results = Yii::app()->db->createCommand($dupeSql)->queryAll();
            if (!empty($results)) continue;

            //only check if it is not set to all
            if($row['industry_subcartegory'] != '["all"]'){
            //check if they have a prefrence on sub cartegory
            $indusArray = json_decode($row['industry_subcartegory'], true);
            foreach($indusArray as $key => $value){
                foreach($value as $key1 => $value1){
                    $indusArray[$key][$key1] =  substr($value1, 0, strpos($value1, "_"));
                }
            }
            //if they have set subcategory check to make sure they match
            if(isset($indus) && is_array($indus)){
                foreach($indus as $in){
                    if(isset($indusArray[$in]) && is_array($indusArray[$in]) && count($indusArray[$in]) != 0 && (count($indusArray[$in]) != 1 || !empty($indusArray[$in][0]))){
                        //we have sub category check to see if we have any of them in the listting, if not return
                        $catMatch = false;
                        if(!empty($fixedCat[0]) && in_array($fixedCat[0], $indusArray[$in])) $catMatch = true;
                        if(!empty($fixedCat[1]) && in_array($fixedCat[1], $indusArray[$in])) $catMatch = true;
                        if(!empty($fixedCat[2]) && in_array($fixedCat[2], $indusArray[$in])) $catMatch = true;
                        if(!empty($fixedCat[3]) && in_array($fixedCat[3], $indusArray[$in])) $catMatch = true;
                        if(!$catMatch)continue 2;
                    }

                }
            }
            }
            $match = array();
            //go and check each match potential
            $match['payment'] = $row['down_payment'];
            $match['price'] = $row['price'];
            $match['industry'] = $row['industry_subcartegory'];
            $match['minimumSellerDiscretionaryEarnings'] = $row['minimum_seller_discretionary_earnings'];


            //check to see inactive buyer set
            $buyer = null;
            $buyer = Contacts::model()->findByPk($row['buyerId']);
            if(!isset($buyer))continue;
            if($buyer->c_remove_from_bizmatch == 1) continue;


            $sql = "INSERT INTO x2_buyer_match_history (buyerId , listingId , prefMatch , createDate) VALUES (" .
                        $row['buyerId'] . ", " . $this->id . ", '" . json_encode($match) . "' , " . time() . ")";
             Yii::app()->db->createCommand($sql)->execute();
            //$this->sendBuyerPrefEmail(1, $row['buyerId']);

            if(isset($buyer)){
                $mainProfile = Profile::model()->findByAttributes(
                         array('username' => $buyer->assignedTo));
                if(isset($buyer->sharedTo)){
                    $decodeShare = json_decode($buyer->sharedTo);
                    if(count($decodeShare) == 1 && $decodeShare[0] == $mainProfile->id){
                         //only one person send from them
                        continue;
                     }else{
                         //go get listing owner
                         $mainProfile = Profile::model()->findByAttributes(
                             array('username' => $this->assignedTo));
                         if(isset($buyer->private) && $buyer->private != 1){
                            $buyer->assignedTo = $this->assignedTo;
                            $buyer->save();
                         }

                     }
               }
           }else{
                continue;
            }

            /*
           $actionModel = new Actions;
           $actionModel->associationType = "contacts";
           $actionModel->associationId = $row['buyerId'];
           $actionModel->associationName = $buyer->name;
           $actionModel->assignedTo = $mainProfile->username;
           $actionModel->completedBy = $mainProfile->username;
           $actionModel->terminatedBy = $mainProfile->username;
           $actionModel->visibility = 1;
           $actionModel->priority = 3;
           $actionModel->createDate = time();
           $actionModel->completeDate = time();
           $actionModel->subject = "New Buyer Match";
           $actionModel->actionDescription = "Buyer name: " . $buyer->name . PHP_EOL . "Listing: " . $this->name . PHP_EOL . "Listing number: " .  $this->c_listing_number__c . PHP_EOL . "Buyer Email: " . $buyer->email;
           $actionModel->save();
           */ 

        }
    }


   //function for sending out the listing emails
    public function buyerSendPref($startTime){
            $query = "SELECT distinct(buyerId)  FROM x2_buyer_match_history WHERE createDate < ".$startTime
            . " AND createDate > " . ($startTime - 60*60*24) . " AND listingId = " . $this->id;

            $command = Yii::app()->db->createCommand($query);
            $result = $command->queryAll();
            foreach($result as $row){
                //we will now go in and load each buyer match
                $buyer = null;
                $buyer = Contacts::model()->findByPk($row['buyerId']);
                if(isset($buyer)){ 
                    $mainProfile = Profile::model()->findByAttributes(
                                array('username' => $buyer->assignedTo));
                   //now set up for the 3 cases
                    //first case if buyer is exclusive
                    if(isset($buyer->private) && $buyer->private == 1){
                        $this->sendBuyerPrefEmail($mainProfile->id, $buyer->id);
                        return;
                    }   
                    //second case if there is a shareTo and has more then just the assigned to
                    if(isset($buyer->sharedTo)){
                        $decodeShare = json_decode($buyer->sharedTo);
                        if(count($decodeShare) == 1 && $decodeShare[0] == $mainProfile->id){
                            //only one person send from them
                            $this->sendBuyerPrefEmail($mainProfile->id, $buyer->id);
                            return;
                        }else{
                            //go get listing owner
                            $mainProfile = Profile::model()->findByAttributes(
                                array('username' => $this->assignedTo));
                            $this->sendBuyerPrefEmail($mainProfile->id, $buyer->id);
                            return;
                        }
                    }else{
                        //in this case just use assigned to user
                        $this->sendBuyerPrefEmail($mainProfile->id, $buyer->id);
                        return;
                    }
                }
            }


    }


    //function for sending the email for buyer pref
    private function sendBuyerPrefEmail($profileId , $buyerId){
        //makeing it so we just make a cron event
        $data = array(
             'listId' => $this->id,
             'buyerId' => $buyerId
        );
        $event = new CronEvent;
        $event->type = 'buyerMatchSend';

        $event->data = json_encode($data);
        $event->time = time();
        $event->associationId = $this->id;
        $event->associationType = 'Listings2';
        $event->save();
        return;



        $eml = new InlineEmail();
        $profile = Profile::model()->findByPk($profileId);
        $buyer = Contacts::model()->findByPk($buyerId);
        //check to make sure both ot set

        $postfields = array("buyer_id"=>$buyerId,
                            "listing_id"=> $this->id);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_USERPWD, "admin:72hfovb28f0smid");
        curl_setopt($ch, CURLOPT_URL , "https://www.tworld.com/bizmatch/api/match/create.php");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));

        $result = curl_exec($ch);
        curl_close($ch);
        return;
        if(isset($profile) && isset($buyer) && isset($profile->buyerPrefTemplate)){

            $eml->getTemplateModel($profile->buyerPrefTemplate);
            $eml->scenario = 'template';
            $eml->setUserProfile($profile);
            $eml->targetModel = $this;
            $eml->to = $buyer->email;
            $prepared = $eml->prepareBody();

            $result = $eml->send();
        }
    }



    //get link for the buyer and name
    public static function getBuyerLink($data = null) {
            if(is_null($data)) {
                return '';
            }else {
                $list = Contacts::model()->findByAttributes(array('id' => $data->buyerId));
                if(isset($list)){
                    return $list->getLink();
                }
                return '';
            }
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




        private function generateUrlBase($office) {
            switch($office->c_country__c) {
                case 'United States':
                    return 'https://tworld.com/';
                case 'Australia':
                    return 'https://www.tworld.com.au/';
                case 'South Africa':
                    return 'https://www.tworldba.co.za/';
                case 'New Zealand':
                    return 'https://www.tworldba.co.nz/';
                default:
                    return 'https://tworld.com/';
            }
        }

        private $quick = false;
        public function setQuick($set) {
            $this->quick = $set;
        }

        public function isQuick() {
            return $this->quick;
        }

        /**
         * @return string generated listing number in Transworld format
         */
        private function genListNum() {
            if (isset($this->c_office_number__c) && !empty($this->c_office_number__c))
                return $this->c_office_number__c . "-" . $this->genUniqueNum();
            $officeNumber = $this->getOfficeNum();
            $listingNumber = $officeNumber . "-" . $this->genUniqueNum();
            return "$listingNumber";
        }

        private function getOfficeNum() {
            $postArray = array();
            foreach ($_POST as $key => $value) {
                $postArray[$key] = $value;
            }
            $userRecord = NULL;
            $userRecord = X2Model::model('User')->findByAttributes(array('username' => $this->assignedTo));
            if (!isset($userRecord) && array_key_exists('Listings2',$postArray) && array_key_exists('assignedTo',$postArray['Listings2']))
                $userRecord = X2Model::model('User')->findByAttributes(array('username' => $_POST['Listings2']['assignedTo']));
	        if (!isset($userRecord) || empty($userRecord)) {
                return NULL;
            }
            $userEmployeeRecord = X2Model::model('Employees')->findByAttributes(array('c_email__c'=>$userRecord->emailAddress, 'c_active__c'=>"True"));
            if (!isset($userEmployeeRecord) || empty($userEmployeeRecord)) {
                return NULL;
            }
            $officeLocation = substr($userEmployeeRecord->c_office_location__c, 0, strpos($userEmployeeRecord->c_office_location__c, '_'));
            if (!isset($officeLocation) || empty($officeLocation)) {
                return NULL;
            }
            $officeRecord = X2Model::model('Offices')->findByAttributes(array('c_office_location__c' => $officeLocation));
            if (!isset($officeRecord) || empty($officeRecord)) {
                return NULL;
            }
            return $officeRecord->c_office_number__c;
        }

        private function genUniqueNum() {
            return mt_rand(100000, 999999);

        }

        private function sanitizeUrlFormula($formula) {
            $formula = str_replace(' ', '-', $formula);
            $formula = str_replace('/', '-', $formula);
            $formula = str_replace('.', '-', $formula);
            $formula = str_replace('!', '-', $formula);
            $formula = str_replace('&', '-', $formula);
            $formula = str_replace('?', '-', $formula);
            $formula = str_replace('\"', '-', $formula);
            $formula = str_replace(',', '-', $formula);
            $formula = str_replace('#', '-', $formula);
            $formula = str_replace('%', '-', $formula);
            $formula = str_replace("'", '-', $formula);
            $formula = str_replace("`", '-', $formula);
            $formula = str_replace("$", '', $formula);
            return $formula;
        }


        //this fuction will be to check if we should send over listings to M&A

        public function checkMA(){

            if($this->c_state__c == "Florida" && ($this->c_listing_price__c > 5000000 && $this->c_total_sales__c > 2500000 && $this->c_net_income__c > 500000 && $this->c_seller_discretionary_earnings__c > 500000)){
                return true;
            }

            if($this->c_listing_price__c > 5000000 && $this->c_total_sales__c > 5000000 && $this->c_net_income__c > 1000000 && $this->c_seller_discretionary_earnings__c > 1000000){
                return true;
            }
            if(in_array($this->c_employee__c, array("Peter Berg_159", "Mike Ertel_1202", "Roy Sutton, III_1719", "Lawrence Sandidge_2073", "Len Russek_1199"))){
                return true;
            }


            return false;
        }


        //function to send listings to M&A

        public function sendListingToMandA(){

            $listAttriibutes = $this->attributes;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic YWRtaW46am9DdnhZaDlqOHE0TFl5SmtGSlRBbTZWYUNZc3ZSVGQ==', 'Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_URL , "https://sydneyv2.tworld.com/index.php/api2/UpdateOrCreateListing");
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_FAILONERROR,0);
            //curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_ANY);
            //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($listAttriibutes));
            $result = curl_exec($ch);
            curl_close($ch);
             //printR($result,1);
            return;

        }

}
