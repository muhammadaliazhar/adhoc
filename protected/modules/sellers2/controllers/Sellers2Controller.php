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

class Sellers2Controller extends x2base {

    public $modelClass = 'Sellers2';

    public function behaviors(){
        return array_merge(parent::behaviors(), array(

      'ModelConversionBehavior' => array(
                'class' => 'application.components.behaviors.ModelConversionBehavior',
            ),
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

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public function actionGetItems($term){
        LinkableBehavior::getItems ($term);
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
        $type='sellers2';
        $model=$this->loadModel($id);

            $docusign = DocusignBehavior::createDocusignInstance();

            if (isset($_GET['envelopeId']) && isset($_GET['event'])) {
                $envelopeId = $_GET['envelopeId'];
                $event = $_GET['event'];

                $foundTemplate = DocusignEnvelope::model()->findByAttributes(array('envelopeId' => $envelopeId));

                if (isset($foundTemplate)) {
                    $docusign->createTemplate($envelopeId, 'Sellers2');
                }
            }

            if (isset($_GET['docusign']) && !empty($_GET['docusign'])) {
                if ($_GET['docusign'] === 'documents') {
                    $par = $_GET['query'];
                    $docList = Media::model()->findAllBySql('select * from x2_media where (uploadedBy = "' . User::getMe()->username . '" or sharedTo like "%\"' . User::getMe()->username . '\"%") and fileName <> "" and name <> "" and name like "%' . $par . '%" and name not like "ReplacedFields_%" and name not like "Signed_%" and name not like "%.jpg" order by name asc');
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
                } else if ($_GET['docusign'] === 'recipients') {
                    $query = $_GET['query'];

                    $records = array();

                    $contactCriteria = new CDbCriteria;
                    $contactCriteria->compare('name', $query, true);
                    $contactCriteria->addCondition('email IS NOT NULL');
                    $contactCriteria->addCondition('TRIM(email) <> ""');

                    $sellerCriteria = new CDbCriteria;
                    $sellerCriteria->compare('name', $query, true);
                    $sellerCriteria->addCondition('c_email IS NOT NULL');
                    $sellerCriteria->addCondition('TRIM(c_email) <> ""');

                    $contactList = Contacts::model()->searchAll(100, $contactCriteria)->getData();
                    $sellerList = Sellers2::model()->search(100, $sellerCriteria)->getData();

                    foreach ($contactList as $contact) {
                        $records[] = array(
                            'model' => $contact,
                            'viewName' => "$contact->firstName $contact->lastName, Buyer",
                            'viewText' => "<b>$contact->firstName $contact->lastName</b>, $contact->email, Buyer",
                            'type' => "buyer",
                            'assignedTo' => $contact->assignedTo
                        );
                    }

                    foreach ($sellerList as $seller) {
                        $records[] = array(
                            'model' => $seller,
                            'viewName' => "$seller->name, Seller",
                            'viewText' => "<b>$seller->name</b>, $seller->c_email, Seller",
                            'type' => "seller",
                            'assignedTo' => $seller->assignedTo
                        );
                    }

                    echo CJSON::encode(array($records));
                } else if ($_GET['docusign'] === 'folders') {
                    $folderList = DocFolders::model()->findAll();
                    $folders = array();

                    foreach ($folderList as $folder) {
                        if ($folder->id < 0 || !Yii::app()->params->isAdmin && !X2PermissionsBehavior::isOwnedByMe($folder->assignedTo))
                            continue;

                        $children = $folder->getChildren();
                        $fileList = $children['docs'];

                        $files = array();
                        foreach($fileList as $file) {
                            if (isset($file->mediaId)) {
                                $media = Media::model()->findByPk($file->mediaId);

                                if (!isset($media->name) || empty($media->name) || !isset($media->fileName) || empty($media->fileName))
                                    continue;

                                $files[] = array(
                                    'model' => $media,
                                    'viewName' => $media->name,
                                    'viewText' => $media->name,
                                );
                            }
                        }

                        $foldersToCheck = $children['folders'];
                        $foldersToWorkWith = array();
                        $filesToWorkWith = array();
                        while (!empty($foldersToCheck)) {
                            $foldersToWorkWith = array();
                            $filesToWorkWith = array();
                            foreach($foldersToCheck as $folderToCheck) {
                                $newChildren = $folderToCheck->getChildren();
                                $foldersToWorkWith = array_merge($foldersToWorkWith, $newChildren['folders']);
                            }

                            $foldersToCheck = $foldersToWorkWith;

                            foreach($filesToWorkWith as $fileToWorkWith) {
                                if (isset($fileToWorkWith->mediaId)) {
                                    $media = Media::model()->findByPk($fileToWorkWith->mediaId);

                                    if (!isset($media->name) || empty($media->name) || !isset($media->fileName) || empty($media->fileName))
                                        continue;

                                    $files[] = array(
                                        'model' => $media,
                                        'viewName' => $media->name,
                                        'viewText' => $media->name,
                                    );
                                }
                            }

                        }

                        $folders[] = array(
                            'id' => $folder->id,
                            'name' => $folder->name,
                            'files' => $files,
                        );
                    }

                    echo CJSON::encode(array($folders));
                }


                Yii::app()->end();
            }
            // If document sent
            if (isset($_POST['recipients']) && !empty($_POST['recipients'])) {
                $emailSubject = isset($_POST['emailSubject']) ? $_POST['emailSubject'] : '';

                $documentList = CJSON::decode($_POST['documents']);
                $documents = array();
                foreach ($documentList as $documentId) {
                    $document = Media::model()->findByPk($documentId);
                    if (isset($document) && !empty($document)) {
                        $documents[] = $document;
                    }
                }

                $recipientList = CJSON::decode($_POST['recipients']);
                $recipients = array();
                foreach ($recipientList as $recipientItem) {
                    $recipientId = $recipientItem['id'];
                    $recipientType = $recipientItem['type'];
                    if ($recipientType === 'buyer') {
                        $recipient = Contacts::model()->findByPk($recipientId);
                        if (isset($recipient) && !empty($recipient)) {
                            $recipients[] = $recipient;
                        }
                    }
                    if ($recipientType === 'seller') {
                        $recipient = Sellers2::model()->findByPk($recipientId);
                        if (isset($recipient) && !empty($recipient)) {
                            $recipients[] = $recipient;
                        }
                    }
                }

                $docusign = DocusignBehavior::createDocusignInstance();
                echo $docusign->requestAccess($documents, $recipients, $emailSubject, $model->id);

                Yii::app()->end();
            }
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
            parent::view($model, $type);
        }

    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new Sellers2;
        $users=User::getNames();

        if(isset($_POST['Sellers2'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['Sellers2']);
        $model->c_createdbyid = User::getMe()->username;
            if(isset($_POST['x2ajax'])){
                $ajaxErrors = $this->quickCreate ($model);
            } else{
                if ($model->save ()) {
                    if (Yii::app()->params->profile->useMobileVer()) {
                        $this->redirect(array('mobileView', 'id' => $model->id));
                    } else {
                        $this->redirect(array('view', 'id' => $model->id));
                    }
                }
            }
        }

        //this will be for the create mobile page
        if(Yii::app()->params->profile->useMobileVer()){
             $this->layout = '//layouts/propertiesMobilew';
            $this->render('mobileCreate',array(
                'model'=> $model,
                'users'=>$users,
                //'doc_dataProvider' => $doc_dataProvider,
                //'view_dataProvider' => $view_dataProvider,
                //'background'=>Properties::getBackgroundImage($model->getMainMedia(), $model->street, $model->city, $model->state, null, true),
                //'signDocs' => CJSON::encode($signDocs),
                //'activities' => $activities,
                //'viewerCount' => $viewerCount,
                //'offerCount' => $offerCount,
                //'downloadCount' => $downloadCount,
            ));
        }else if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create',array(
                'model'=>$model,
                'users'=>$users,
            ));
        }

    }

    /**
     * Clones a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionClone($id) {
        $model = $this->loadModel($id);
        $modelClone = new Sellers2;
        foreach($model->attributes as $key => $value) {
            $modelClone->$key = $value;
        }
	$match = addcslashes($model->name, '%_'); // escape LIKE's special characters
	$q = new CDbCriteria( array(
	    'condition' => "name LIKE :match",         // no quotes around :match
	    'params'    => array(':match' => "$match (%)")  // Aha! Wildcards go here
	) );
	 
	$sellersThatAreClones = Sellers2::model()->findAll( $q );     // works!
	$cloneCount = count($sellersThatAreClones)+1;
	$modelClone->name = $model->name . " (" . (string)$cloneCount . ")";
        $modelClone->id = NULL;
        $modelClone->nameId = NULL;
        if ($modelClone->save()) {
            $this->redirect(array('sellers2/view','id'=>$modelClone->id));
        }
    }


    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users = User::getNames();

        if(isset($_POST['Sellers2'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['Sellers2']);
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




    public function actionMobileCreatePost($atts){
        $attsArray = json_decode($atts, 1);
        //check to make sure we got an id
        $model = new Sellers2;
        $sellerArray = array();
        foreach($attsArray as $key => $value){
            if($key == 'id') continue;
            $sellerArray[$key] = $value;
            //$model->$key = $value;
        }
        $model->setX2Fields($sellerArray);
        $model->save();
        $this->redirect(array('sellers2/view','id'=>$model->id));

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
        $sellerArray = array();
        foreach($attsArray as $key => $value){
            if($key == 'id') continue;
            $sellerArray[$key] = $value;
            //$model->$key = $value;
        }
        $model->setX2Fields($sellerArray);
        $model->save();
        $this->redirect(array('sellers2/view','id'=>$model->id));

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

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $model=new Sellers2('search');
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
        $model=new Sellers2('search');
        $this->render('admin', array('model'=>$model));
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='sellers2-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function actionSendBli($id) {
        $id = Yii::app()->request->getPost('id');
        $checked = Yii::app()->request->getPost('checked');
        $confidential = Yii::app()->request->getPost('confidential');
        $docName = $checked === 'true' ? 'SA-BLI.docx' : 'Seller_BLI_Template.docx';
        $docName = $docName === 'BLI_Template.docx' && $confidential === 'true' ? 'BLI_Confidential_Template.docx' : $docName;
        $contact = $this->loadModel($id);
        $listing = Listings2::model()->findByAttributes(array('nameId' => $contact->c_listinglookup__c));
        if (!$listing) { echo "No related listing found for this record"; return; }
        $docusign = DocusignBehavior::createDocusignInstance();
        $doc = Media::model()->findByAttributes(array('name' => $docName));
        $subject = "Confidential Business Profile - " . $listing->c_listing_number__c;
        echo $docusign->requestAccess(array($doc), array($contact), $subject, $id, null, null, true, 'Sellers2');
    }

    public function actionPreviewBli($id) {
        $id = Yii::app()->request->getPost('id');
        $checked = Yii::app()->request->getPost('checked');
        $docName = $checked === 'true' ? 'SA-BLI.docx' : 'Seller_BLI_Template.docx';
        $contact = $this->loadModel($id);
        $listing = Listings2::model()->findByAttributes(array('nameId' => $contact->c_listinglookup__c));
        if (!$listing) { echo "No related listing found for this record"; return; }
        $docusign = DocusignBehavior::createDocusignInstance();
        $doc = Media::model()->findByAttributes(array('name' => $docName));
        $subject = "Confidential Business Profile - " . $listing->c_listing_number__c;
        echo $docusign->requestPreview(array($doc), array($contact), $subject, $id, null, null, true, 'Sellers2');
    }

   // Displays all visible Contact Lists
    public function actionLists() {
        $filter = new X2List('search');
        $criteria = new CDbCriteria();
        $criteria->addCondition('modelName = "Sellers2"');
        $criteria->addCondition('type="static" OR type="dynamic"');
        if (!Yii::app()->params->isAdmin) {
            $condition = 'assignedTo="' . Yii::app()->user->getName() . '"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()
                            ->select('groupId')
                            ->from('x2_group_to_user')
                            ->where('userId=' . Yii::app()->user->getId())->queryColumn();
            if (!empty($groupLinks))
                $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';

            $condition .= 'OR (visibility=2 AND assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId 
                     FROM x2_group_to_user 
                     WHERE userId=' . Yii::app()->user->getId() . ')
                )
            )';
            $criteria->addCondition($condition);
        }

        $perPage = Profile::getResultsPerPage();

        //$criteria->offset = isset($_GET['page']) ? $_GET['page'] * $perPage - 3 : -3;
        //$criteria->limit = $perPage;
        $criteria->order = 'createDate DESC';
        $filter->compareAttributes($criteria);

        $contactLists = X2Model::model('X2List')->findAll($criteria);

        $totalContacts = X2Model::model('Sellers2')->count();
        $totalMyContacts = X2Model::model('Sellers2')->count('assignedTo="' . Yii::app()->user->getName() . '"');
        $totalNewContacts = X2Model::model('Sellers2')->count('assignedTo="' . Yii::app()->user->getName() . '" AND createDate >= ' . mktime(0, 0, 0));

        $allContacts = new X2List;
        $allContacts->attributes = array(
            'id' => 'all',
            'name' => Yii::t('contacts', 'All {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $newContacts = new X2List;
        $newContacts->attributes = array(
            'id' => 'new',
            'assignedTo' => Yii::app()->user->getName(),
            'name' => Yii::t('contacts', 'New {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalNewContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $myContacts = new X2List;
        $myContacts->attributes = array(
            'id' => 'my',
            'assignedTo' => Yii::app()->user->getName(),
            'name' => Yii::t('contacts', 'My {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalMyContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $contactListData = array(
            $allContacts,
            $myContacts,
            $newContacts,
        );

        $filteredPseudoLists = $filter->filter($contactListData);
        $lists = array_merge($filteredPseudoLists, $contactLists);
        $dataProvider = new CArrayDataProvider($lists, array(
            'pagination' => array('pageSize' => $perPage),
            'sort' => array(
                'attributes' => array(
                    'name' => array(
                        'asc' => 'name asc, id desc',
                        'desc' => 'name desc, id desc',
                    ),
                    // secondary order is needed to fix https://github.com/yiisoft/yii/issues/2082
                    'type' => array(
                        'asc' => 'type asc, id desc',
                        'desc' => 'type desc, id desc',
                    ),
//                    'count' => array (
//                        'asc' => 'count asc, id desc',
//                        'desc' => 'count desc, id desc',
//                    ),
                    'assignedTo' => array(
                        'asc' => 'assignedTo asc, id desc',
                        'desc' => 'assignedTo desc, id desc',
                    ),
                )),
            'totalItemCount' => count($contactLists) + 3,
        ));

        $this->render('listIndex', array(
            'contactLists' => $dataProvider,
            'filter' => $filter,
        ));
    }

    /**
     * Return a JSON encoded list of Contact lists
     */
    public function actionGetLists() {
        if (!Yii::app()->user->checkAccess('ContactsAdminAccess')) {
            $condition = ' AND (visibility="1" OR assignedTo="Anyone"  OR assignedTo="' . Yii::app()->user->getName() . '"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId=' . Yii::app()->user->getId())->queryColumn();
            if (!empty($groupLinks)) {
                $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';
            }

            $condition .= ' OR (visibility=2 AND assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                (SELECT groupId FROM x2_group_to_user WHERE userId=' . Yii::app()->user->getId() . '))))';
        } else {
            $condition = '';
        }
        // Optional search parameter for autocomplete
        $qterm = isset($_GET['term']) ? $_GET['term'] . '%' : '';
        $static = isset($_GET['static']) && $_GET['static'];
        $weblist = isset($_GET['weblist']) && $_GET['weblist'];
        $result = Yii::app()->db->createCommand()
                ->select('id,name as value')
                ->from('x2_lists')
                ->where(
                        ($static ? 'type="static" AND ' : '') .
                        ($weblist ? 'type="weblist" AND ' : '') .
                        'modelName="Sellers2" AND type!="campaign" 
                    AND name LIKE :qterm' . $condition, array(':qterm' => $qterm))
                ->order('name ASC')
                ->queryAll();
        echo CJSON::encode($result);
    }

    // Shows contacts in the specified list
    public function actionList($id = null) {
        $list = X2List::load($id);

        if (!isset($list)) {
            Yii::app()->user->setFlash(
                    'error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('lists'));
        }

        $model = new Sellers2('search');
        Yii::app()->user->setState('vcr-list', $id);
        $dataProvider = $model->searchList($id);
        $list->count = $dataProvider->totalItemCount;
        $list->runWithoutBehavior('FlowTriggerBehavior', function () use ($list) {
            $list->save();
        });

        X2Flow::trigger('RecordViewTrigger', array('model' => $list));
        $this->render('list', array(
            'listModel' => $list,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ));
    }

    public function actionCreateList($ajax = false) {
        $list = new X2List;
        $list->modelName = 'Sellers2';
        $list->type = 'dynamic';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;

        $contactModel = new Sellers2;
        $comparisonList = X2List::getComparisonList();
        if (isset($_POST['X2List'])) {
            $list->type = $_POST['X2List']['type'];
            $list->attributes = $_POST['X2List'];
            $list->modelName = 'Sellers2';
            $list->createDate = time();
            $list->lastUpdated = time();

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {
                    $list->modelName = 'Sellers2';
                    $list->lastUpdated = time();
                }
            }
            if (!$list->hasErrors() && $list->save()) {
                if ($ajax) {
                    echo CJSON::encode($list->attributes);
                    return;
                }
                $this->redirect(array('/sellers2/sellers2/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new X2ListCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        }

        if ($ajax) {
            $html = $this->renderPartial('createList', array(
                'model' => $list,
                'criteriaModels' => $criteriaModels,
                'users' => User::getNames(),
                // 'attributeList'=>$attributeList,
                'comparisonList' => $comparisonList,
                'listTypes' => array(
                    'dynamic' => Yii::t('sellers2', 'Dynamic'),
                    'static' => Yii::t('sellers2', 'Static')
                ),
                'itemModel' => $contactModel,
                    ), false);
            echo $this->processOutput($html);
            return;
        }

        $this->render('createList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            // 'attributeList'=>$attributeList,
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('sellers2', 'Dynamic'),
                'static' => Yii::t('sellers2', 'Static')
            ),
            'itemModel' => $contactModel,
        ));
    }
    
    public function actionUpdateList($id) {
        $list = X2List::model()->findByPk($id);

        if (!isset($list))
            throw new CHttpException(400, Yii::t('app', 'This list cannot be found.'));

        if (!$this->checkPermissions($list, 'edit'))
            throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));

        $contactModel = new Sellers2;
        $comparisonList = X2List::getComparisonList();
        $fields = $contactModel->getFields(true);

        if ($list->type == 'dynamic') {
            $criteriaModels = X2ListCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

                    $list->attributes = $_POST['X2List'];
                    $list->modelName = 'Sellers2';
                    $list->lastUpdated = time();

                    if (!$list->hasErrors() && $list->save()) {
                        $this->redirect(array('/sellers2/sellers2/list', 'id' => $list->id));
                    }
                }
            }
        } else { //static or campaign lists
            if (isset($_POST['X2List'])) {
                $list->attributes = $_POST['X2List'];
                $list->modelName = 'Sellers2';
                $list->lastUpdated = time();
                $list->save();
                $this->redirect(array('/sellers2/sellers2/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new X2ListCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        } else {
            if ($list->type = 'dynamic') {
                foreach ($criteriaModels as $criM) {
                    if (isset($fields[$criM->attribute])) {
                        if ($fields[$criM->attribute]->type == 'link') {
                            $criM->value = implode(',', array_map(function($c) {
                                        list($name, $id) = Fields::nameAndId($c);
                                        return $name;
                                    }, explode(',', $criM->value)
                                    )
                            );
                        }
                    }
                }
            }
        }

        $this->render('updateList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            // 'attributeList'=>$attributeList,
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('sellers2', 'Dynamic'),
                'static' => Yii::t('sellers2', 'Static')
            ),
            'itemModel' => $contactModel,
        ));
    }
    
    public function actionDeleteList() {

        $id = isset($_GET['id']) ? $_GET['id'] : 'all';

        if (is_numeric($id))
            $list = X2Model::model('X2List')->findByPk($id);
        if (isset($list)) {

            // check permissions
            if ($this->checkPermissions($list, 'edit'))
                $list->delete();
            else
                throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));
        }
        $this->redirect(array('/sellers2/sellers2/lists'));
    }
    
    public function actionGetTerms(){
        $sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result); exit;
    }

            // Lists all contacts assigned to this user
    public function actionMySellers2() {
        $model = new Sellers2('search');
        Yii::app()->user->setState('vcr-list', 'myContacts');
        $this->render('index', array('model' => $model));
    }

    // Lists all contacts assigned to this user
    public function actionSellers2() {
        $model = new Sellers2('search');
        Yii::app()->user->setState('vcr-list', 'newContacts');
        $this->render('index', array('model' => $model));
    }



}
