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

class Listings2Controller extends x2base {

    public $modelClass = 'Listings2';

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            'MobileControllerBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.MobileControllerBehavior'
            ),
            'MobileActionHistoryBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.MobileActionHistoryBehavior'
            ),
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
            ),
        ));
    }

    public function accessRules() {
        $parent = parent::accessRules();
        $parent[] = array('allow',
                'actions' => array('public'),
                'users' => array('*'),
        );
        return $parent;
    }

    public function actions() {
        $actions = array_merge(parent::actions(), array(
            'public' => array(
                'class' => 'application.modules.listings2.components.actions.PublicPageAction',
            ),
        ));
        return $actions;
    }


    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public function actionGetItems($term){
        LinkableBehavior::getItems ($term, 'name', 'id', null);
    }


    public function actionGetItemsSubCategories(){
        $categoryTerms = null;
        $term=null;
        if (isset($_GET['categoryTerm']))
            $categoryTerms = json_decode($_GET['categoryTerm']);
        LinkableBehavior::getItems ($term, 'name', 'id', null,$categoryTerms);        
    }


    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $type='listings2';
        $model=$this->loadModel($id);
        //if($id == 49733) $model->sendListingToMandA();
        if (isset($_GET['docs']) && $_GET['docs'] === 'listings') {
            $docList = Media::model()->findAll('fileName <> "" and name <> "" and name like "%' . $_GET['query'] . '%"');
            $docs = array();
            foreach ($docList as $doc) {
                if ($doc->getPath()) {
                     $docs[] = array(
                         'model' => $doc,
                         'viewName' => $doc->name,
                         'viewText' => $doc->name,
                    );
                }
            }
            echo CJSON::encode(array($docs));
            Yii::app()->end(); 
        } else if (isset($_POST['docs']) && $_POST['docs'] === 'listings') {
            $selected = CJSON::decode($_POST['selected']);
            $already = empty($model->c_docs) || $model->c_docs === '[]' ? array() : CJSON::decode($model->c_docs);
            foreach ($selected as $item) {
                if (in_array((string) $item, $already) || in_array($item, $already)) {
                    continue;
                }
                $already[] = $item;
            }

            $model->c_docs = CJSON::encode($already);
            $model->save();

            Yii::app()->end();
        }

        $mediaCriteria = new CDbCriteria();
        $mediaCriteria->condition = "associationId = :associationId AND associationType = :associationType AND mimetype LIKE 'image/%'";
        $mediaCriteria->params = array(
            ':associationId' => $model->id,
            ':associationType' => $type,
        );
        $acceptedFiles = '';


        //this will be the check if mobile user and if so change view


        if(Yii::app()->params->profile->useMobileVer()){
             $this->layout = '//layouts/propertiesMobilew';
            $this->render('mobileView',array(
                'model'=> $model,
                //'doc_dataProvider' => $doc_dataProvider,
                //'view_dataProvider' => $view_dataProvider,
                //'background'=>Properties::getBackgroundImage($model->getMainMedia(), $model->street, $model->city, $model->state, null, true),
                //'signDocs' => CJSON::encode($signDocs),
                //'activities' => $activities,
                //'viewerCount' => $viewerCount,
                //'offerCount' => $offerCount,
                //'downloadCount' => $downloadCount,
            ));


        }else{
            parent::view($model, $type, array('acceptedFiles' => $acceptedFiles));
        }

    }

    

    /**
     * Generates an email template to share Contact data
     * @param int $id The ID of the Contact
     */
    /*
    public function actionShare($id) {
        $model = $this->loadModel($id);

        if (isset($_POST['publicHash'])) {
            $publicHash = $_POST['publicHash'];
            $url = '';
            if ($publicHash === 'new') {
                $str = str_replace(' ', '_', strtolower($model->name));
                $url = Yii::app()->createExternalUrl('/listings2/public?name=' . $str);
                $model->c_publicHash = $url;

            } else if ($publicHash === 'remove') {
                $model->c_publicHash = '';
            }
            $model->save();
            echo $url;
            Yii::app()->end();
        }

        $this->render('share', array(
            'model' => $model,
        ));
    }
    */

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new Listings2;
        $users=User::getNames();

        if(isset($_POST['Listings2'])) {
            $shareArray = array();
            if(isset($_POST['Listings2']['sharedTo']))
                 $shareArray = $_POST['Listings2']['sharedTo'];
            $uIDs = array();
            foreach($shareArray as $uname){
                $user = User::model()->findByAttributes(array('username' => $uname));
                if(isset($user))$uIDs[] = $user->id;
            }

            unset($_POST['Listings2']['sharedTo']);

            $temp = $model->attributes;
            $model->setX2Fields($_POST['Listings2']);
            $model->sharedTo = json_encode($uIDs);

	    $model->assignedTo = User::getMe()->username;
            if(isset($_POST['x2ajax'])){
                $ajaxErrors = $this->quickCreate ($model);
            } else{
                if ($model->save ()) {
                    $this->redirect(array('view', 'id' => $model->id));
                }
            }
        }


        if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create',array(
                'model'=>$model,
                'users'=>$users,
            ));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionClone($id) {
        $model = $this->loadModel($id);
        $modelClone = new Listings2;
        foreach($model->attributes as $key => $value) {
            $modelClone->$key = $value;
        }
	$match = addcslashes($model->name, '%_'); // escape LIKE's special characters
	$q = new CDbCriteria( array(
	    'condition' => "name LIKE :match",         // no quotes around :match
	    'params'    => array(':match' => "$match (%)")  // Aha! Wildcards go here
	) );
	$listingsThatAreClones = Listings2::model()->findAll( $q );     // works!
	$cloneCount = count($listingsThatAreClones)+1;
	// null out unique columns
	$modelClone->name = $model->name . " (" . (string)$cloneCount . ")";
        $modelClone->id = NULL;
        $modelClone->nameId = NULL;
        $modelClone->c_listing_number__c = NULL;
	    //$modelClone->c_SALESFORCEID = NULL;
        $modelClone->c_ad_headline__c = uniqid();
	//test(remove after)
	Yii::app()->user->setFlash('error', "Cloning was not successful. The following fields must be filled: 1)LeadGeneratedBY  2)Lead Source");
	if ($modelClone->save()) {
	    $this->redirect(array('listings2/view','id'=>$modelClone->id));
        }
	/* else{
	//(Justin) If the Cloning does not work this will activate
	Yii::app()->user->setFlash('error', "Cloning was not successful. The following fields must be filled: 1)LeadGeneratedBY  2)Lead Source");
	} */
	$model=new Listings2('search');
        $this->render('index', array('model'=>$model));
    } 

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users = User::getNames();

        if(isset($_POST['Listings2'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['Listings2']);
            parent::update($model,$temp,'0');
        }

        //check if mobile ver
        if(Yii::app()->params->profile->useMobileVer()){
            $this->layout = '//layouts/propertiesMobilew';
            $this->render('mobileUpdate',array(
                     'model'=>$model,
                    'users'=>$users,
            ));
        }else{

            $this->render('update',array(
                'model'=>$model,
                'users'=>$users,
            ));
        }


    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if(Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $model=$this->loadModel($id);
            $this->cleanUpTags($model);
            $model->delete();

            /* if AJAX request (triggered by deletion via admin grid view), we should not redirect 
               the browser */
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        } else {
            throw new CHttpException(
                400,'Invalid request. Please do not repeat this request again.');
        }
    }


    public function actionMobileCreatePost($atts){
        $attsArray = json_decode($atts, 1);
        //check to make sure we got an id
        $model = new Listings2;
        $sellerArray = array();
        foreach($attsArray as $key => $value){
            if($key == 'id') continue;
            $sellerArray[$key] = $value;
            //$model->$key = $value;
        }
        $model->setX2Fields($sellerArray);
        $model->save();
        $this->redirect(array('listings2/view','id'=>$model->id));

    }

    /**
     * Updates a particular model. This will be for the mobile website
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */

    public function actionMobileUpdate($atts){
        $attsArray = json_decode($atts, 1);
        //check to make sure we got an id
        if(empty($attsArray['id']))throw new CHttpException(403, Yii::t('app', 'No ID found'));
        $model = $this->loadModel($attsArray['id']);
        //set the attributres
        $listingArray = array();
        foreach($attsArray as $key => $value){
            if($key == 'id') continue;
            $listingArray[$key] = $value;
            //$model->$key = $value;
        }
        $model->setX2Fields($listingArray);
        $model->save();
        $this->redirect(array('listings2/view','id'=>$model->id));

    }


    /**
     * Lists all models.
     */
    public function actionIndex() {
        $model=new Listings2('search');

        //this will be the check if mobile user and if so change view


        if(Yii::app()->params->profile->useMobileVer()){
             $this->layout = '//layouts/propertiesMobilew';
            $this->render('mobileIndex',array(
                'model'=> $model,
                'user' =>User::getMe(),
                'profile'=>Profile::model()->findByPk(User::getMe()->id),

                //'doc_dataProvider' => $doc_dataProvider,
                //'view_dataProvider' => $view_dataProvider,
                //'background'=>Properties::getBackgroundImage($model->getMainMedia(), $model->street, $model->city, $model->state, null, true),
                //'signDocs' => CJSON::encode($signDocs),
                //'activities' => $activities,
                //'viewerCount' => $viewerCount,
                //'offerCount' => $offerCount,
                //'downloadCount' => $downloadCount,
            ));

        }else{

            $this->render('index', array('model'=>$model));
        }

    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model=new Listings2('search');
        $this->render('admin', array('model'=>$model));
    }

    public function actionExportInquiryListings() {
        // Export Listings using Inquiry criteria
        if (isset($_GET['contactId'])) {
            $criteria = new CDBCriteria();
            $criteria->alias = 'l';
            $criteria->select = 'l.*';
            $criteria->join = 'JOIN x2_contacts b JOIN x2_inquiries i ON b.nameId = i.c_contact__c AND l.nameId = i.c_listing__c';
            $criteria->addCondition('b.id = :contactId');
            $criteria->params = array(':contactId'=>$_GET['contactId']);

            // Only export what the user can access
            $conditions = Listings2::model()->getAccessConditions(4, 'l');
            foreach ($conditions as $arr) {
                if (isset($arr['params']))
                    continue; // If there are params then the legacy regex is getting passed (which we don't want)
                $criteria->addCondition($arr['condition']);
            }

            $_SESSION['exportModelCriteria'] = $criteria;
            $_SESSION['modelExportFile'] = User::getMe()->username . "_listing_inquiry_export.csv";
            $_SESSION['modelExportMeta'] = array_keys(X2Model::model('Listings2')->attributes);
            $_SESSION['includeTags'] = 1;
            $_SESSION['exportFormat']['exportDestination'] = 'download';
            $_SESSION['exportFormat']['compressOutput'] = 0;
            $_SESSION['writeCSVHeader'] = 1;
            echo CJSON::encode($_SESSION['exportModelCriteria']);
            return;
        }

    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='listings2-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function actionPreviewBli($id) {
        $id = Yii::app()->request->getPost('id');
        $checked = Yii::app()->request->getPost('checked');
        $docName = $checked === 'true' ? 'SA-BLI.docx' : 'BLI_Template.docx';
        $listing = Listings2::model()->findByPk($id);;
        if (!$listing) { echo "No related listing found for this record"; return; }
        $docusign = DocusignBehavior::createDocusignInstance();
        $doc = Media::model()->findByAttributes(array('name' => $docName));
        $subject = "Confidential Business Profile - " . $listing->c_listing_number__c;
        echo $docusign->requestPreview(array($doc), array(), $subject, $id, null, null, true, 'Listings2');
    }

    public function actionCreateBuyerMatchList($listingId){
        $query = "SELECT distinct(buyerId)  FROM x2_buyer_match_history WHERE listingId = " . $listingId;

        $command = Yii::app()->db->createCommand($query);
        $result = $command->queryAll();

        ///get the listing record   
        $listing = Listings2::model()->findByPk($listingId);


        //make the list first

        $list = new X2List;
        $list->name =  $listing->name . " Buyer Match List " . date(DATE_RFC2822);
        $list->modelName = "Contacts";
        $list->type = 'static';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;
        $list->createDate = time();
        $list->lastUpdated = time();
        $list->count = count($result);
        $list->save();

        //now make the items
        foreach($result as $rec){
                $item = new X2ListItem;
                $item->contactId = $rec['buyerId'];
                $item->listId = $list->id;
                $item->save();

        }
        $listURL = $this->createUrl('/contacts/list/', array('id'=>$list->id));
        $this->redirect($listURL);




    }

    //function for auto send of bli and to set it to send
    public function actionUpdateBli($id){
        $listing = Listings2::model()->findByPk($id);
        //if($listing->c_BLIauto == 1)$listing->c_BLIauto = 0;
        //else $listing->c_BLIauto = 1;
        switch ($_POST['option']) {
            case "bli":
                $listing->c_BLIauto = 1;
                $listing->c_cim = 0;
                break;
            case "cim":
                $listing->c_BLIauto = 0;
                $listing->c_cim = 1;
                break;
            case "both":
                $listing->c_BLIauto = 1;
                $listing->c_cim = 1;
                break;
            default:
                $listing->c_BLIauto = 0;
                $listing->c_cim = 0;
                break;


        }

        if (!$listing->save()) {
            throw new CHttpException(500, CJSON::encode($listing->getErrors()));
        }

        //$listURL = $this->createUrl('/listings2/getPortal/', array('id'=>$id));
        //$this->redirect($listURL);
        echo "done";



    }




}
