<?php

class MyListings2Controller extends Listings2Controller {
    
    public function actionCreateList($ajax = false) {
        $list = new X2List;
        $list->modelName = 'Listings2';
        $list->type = 'dynamic';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;
        $listingModel = new Listings2;
        $comparisonList = X2List::getComparisonList();
        if (isset($_POST['X2List'])) {
            $list->type = $_POST['X2List']['type'];
            $list->attributes = $_POST['X2List'];
            $list->modelName = 'Listings2';
            $list->createDate = time();
            $list->lastUpdated = time();
            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {
                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];
                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {
                    $list->modelName = 'Listings2';
                    $list->lastUpdated = time();
                }
            }
            if (!$list->hasErrors() && $list->save()) {
                if ($ajax) {
                    echo CJSON::encode($list->attributes);
                    return;
                }
                $this->redirect(array('/listings2/listings2/list', 'id' => $list->id));
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
                'comparisonList' => $comparisonList,
                'listTypes' => array(
                    'dynamic' => Yii::t('listings2', 'Dynamic'),
                    'static' => Yii::t('listings2', 'Static')
                ),
                'itemModel' => $listingModel,
                    ), false);
            echo $this->processOutput($html);
            return;
        }
        $this->render('createList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('listings2', 'Dynamic'),
                'static' => Yii::t('listings2', 'Static')
            ),
            'itemModel' => $listingModel,
        ));
    }

    // Lists all listings assigned to this user
    public function actionMyListings2() {
        $model = new Listings2('search');
        $data=$model->searchMyListings();
        Yii::app()->user->setState('vcr-list', 'myListings2');
        $this->render('index', array('model' => $data));
        Yii::log("End", 'trace', 'application.api');
    }

    public function actionDeleteDocument($id) {
        Yii::app()->db->createCommand()->delete('x2_media', 'id=:id', array(':id'=>$id));
        return;
    }

    public function actionGetPortal($id) {
        $this->layout = '//layouts/column1';

        $model = $this->loadModel($id);

        $doc_dataProvider = new CActiveDataProvider('Media', array(
            'criteria' => array(
                'condition' => "mimetype LIKE 'application/pdf%' AND c_document_type='NDA' AND associationType = 'Listings2' AND associationId = " . $id,
                //'order' => $order,
            ),
            'pagination' => false
        ));

        $cim_dataProvider = new CActiveDataProvider('Media', array(
            'criteria' => array(
                'condition' => "associationType = 'listingCIM' AND associationId = " . $id,
                //'order' => $order,
                'limit' => 1,
            ),
            'pagination' => false
        ));

        //get related NDAs
        $NDAmodel=new Ndaconfigure('search');
        //criteria to only show NDAs with docs
        $criteria = new CDBCriteria();
        $criteria->addCondition("c_default_nda is not NULL AND c_default_nda != ''");
        //get group mates
        if(!Yii::app()->params->isAdmin){
            $groupMates = Groups::getGroupmates(Yii::app()->user->id);
            foreach($groupMates as $mate){
                $criteria->addCondition("assignedTo = '" . $mate  . "'");
            }
        }
        //printR($groupMates,1);
        $ndaData = $NDAmodel->search(null, $criteria);
        $ndaRecs = $ndaData->getData();
        //here I will get an array of sign docs 
        $criteria = new CDbCriteria();
        $criteria->addCondition('name NOT LIKE "ReplacedFields%"');
        $criteria->addCondition('type = "x2signdoc"');
        if (!Yii::app()->params->isAdmin){
              $libId = Yii::app()->suModel->getLibrary()->id ?? "NULL";
              //$accessCond .= " AND libraryId = $libId";
              $criteria->addCondition('libraryId = ' . $libId . ' OR assignedTo = "' .  Yii::app()->user->getName() .'"');
        }
    
        $data = Docs::model()->searchBase($criteria, 1000, false, true)->getData();

        //for no return 
        if(count($data) == 0){
        }
        $ids = array_map(function($e){return $e->id;}, $data);
        $criteria = new CDbCriteria();
        foreach ($ids as $id) {
              $criteria->compare('docId', $id, false, 'OR');
        }
        $provider = new CActiveDataProvider('X2SignDocs');
        $criteria->order = 'name ASC';
        $provider->setCriteria($criteria);
        $provider->pagination = false;
        //if the list of ids is empty just return a empty array
        if(empty($ids)){
            $X2SignDocs = array();
        }else{
            $X2SignDocs = $provider->getData();
        }
        //printR(Docs::getEmailTemplates('email', 'Contacts'),1);
        //$EmailTemplates = CHtml::listData (Docs::getEmailTemplates2('email', 'Contacts'), 'id', 'name');
        $EmailTemplates = Docs::getEmailTemplates('email', 'Contacts');
        //$EmailTemplates[0] = Yii::t('marketing',"Select an email template");

        $numOfBliSent = Yii::app()->db->createCommand()
                            ->select('COUNT(*)')
                            ->from('x2_sign_envelopes')
                            ->where("c_listing = '". addSlashes($model->nameId) ."' AND name LIKE '%BLI'")
                            ->queryAll();
        $numOfNdaSent = Yii::app()->db->createCommand()
                            ->select('COUNT(*)')
                            ->from('x2_sign_envelopes')
                            ->where("c_listing = '". addSlashes($model->nameId) ."' AND name NOT LIKE '%BLI'")
                            ->queryAll();
        $numOfNdaSigned = Yii::app()->db->createCommand()
                            ->select('COUNT(*)')
                            ->from('x2_sign_envelopes')
                            ->where("c_listing = '". addSlashes($model->nameId) ."' AND name NOT LIKE '%BLI' AND status = 4")
                            ->queryAll();


        $validated = true;
        //check that there is an associated NDA
        $media = Media::model()->findByAttributes(array('c_document_type' => 'NDA', 'associationType' => 'Listings2', 'associationId' => $model->id), "mimetype LIKE 'application/pdf%'");
        if(empty($media)){
            $validated = false;
        }
        //now check that if there is bli checked then check the bli drop down is set
        if($model->c_BLIauto){
            if(empty($model->autoBliType)){
                $validated = false;
            }

        }
        $this->render('portalView',array(
            'model'=> $model,
            'doc_dataProvider' => $doc_dataProvider,
            'cim_dataProvider' => $cim_dataProvider,
            'background'=>Listings2::getBackgroundImage(),
            'ndaRecs' => $ndaRecs,
            'X2SignDocs' => $X2SignDocs,
            'EmailTemplates' => $EmailTemplates,
            'numOfBliSent' => $numOfBliSent[0]['COUNT(*)'],
            'numOfNdaSent' => $numOfNdaSent[0]['COUNT(*)'],
            'numOfNdaSigned' => $numOfNdaSigned[0]['COUNT(*)'],
            'validated' => $validated
        ));

    }

    // Lists all listings 
    public function actionNewListings2() {
        $model = new Listings2('search');
        Yii::app()->user->setState('vcr-list', 'newListings2');
        $this->render('index', array('model' => $model));
    }
    // loads all previous lists
    public function actionList($id = null) {
        $list = X2List::load($id);
         if (!isset($list)) {
             Yii::app()->user->setFlash(
                     'error', Yii::t('app', 'The requested page does not exist.'));
             $this->redirect(array('lists'));
         }
         $model = new Listings2('search');
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
    // for editing the list
     public function actionUpdateList($id) {
        $list = X2List::model()->findByPk($id);
        if (!isset($list))
            throw new CHttpException(400, Yii::t('app', 'This list cannot be found.'));
        if (!$this->checkPermissions($list, 'edit'))
            throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));
        $listingModel = new Listings2;
        $comparisonList = X2List::getComparisonList();
        $fields = $listingModel->getFields(true);
        if ($list->type == 'dynamic') {
            $criteriaModels = X2ListCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));
            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {
                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];
                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {
                    $list->attributes = $_POST['X2List'];
                    $list->modelName = 'Listings2';
                    $list->lastUpdated = time();
                    if (!$list->hasErrors() && $list->save()) {
                        $this->redirect(array('/listings2/listings2/list', 'id' => $list->id));
                    }
                }
            }
        } else { //static or campaign lists
            if (isset($_POST['X2List'])) {
                $list->attributes = $_POST['X2List'];
                $list->modelName = 'Listings2';
                $list->lastUpdated = time();
                $list->save();
                $this->redirect(array('/listings2/listings2/list', 'id' => $list->id));
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
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('listings2', 'Dynamic'),
                'static' => Yii::t('listings2', 'Static')
            ),
            'itemModel' => $listingModel,
        ));
    }

     public function actionDeleteList() {
        $id = isset($_GET['id']) ? $_GET['id'] : 'all';
        Yii::log($id, 'trace', 'application.api');
        if (is_numeric($id))
            $list = X2Model::model('X2List')->findByPk($id);
        if (isset($list)) {

            // check permissions
            if ($this->checkPermissions($list, 'delete'))
                $list->delete();
            else
                throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));
        }
        $this->redirect(array('/listings2/listings2/lists'));
    }

        // Displays all visible Contact Lists
        public function actionLists() {
            $filter = new X2List('search');
            $criteria = new CDbCriteria();
        $criteria->addCondition('modelName = "Listings2"');
            $criteria->addCondition('type="static" OR type="dynamic"');
            if (!Yii::app()->params->isAdmin) {
                $condition = 'visibility="1" OR assignedTo="Anyone" OR 
                     assignedTo="' . Yii::app()->user->getName() . '"';
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
            $criteria->order = 'createDate DESC';
            $filter->compareAttributes($criteria);
            $contactLists = X2Model::model('X2List')->findAll($criteria);
            $totalContacts = X2Model::model('Listings2')->count();
            $totalMyContacts = X2Model::model('Listings2')->count('assignedTo="' . Yii::app()->user->getName() . '"');
            $totalNewContacts = X2Model::model('Listings2')->count('assignedTo="' . Yii::app()->user->getName() . '" AND createDate >= ' . mktime(0, 0, 0));
            $allContacts = new X2List;
            $allContacts->attributes = array(
                'id' => 'all',
                'name' => Yii::t('listings2', 'All {module}', array('{module}' => Modules::displayName())),
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
                'name' => Yii::t('listings2', 'New {module}', array('{module}' => Modules::displayName())),
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
                'name' => Yii::t('listings2', 'My {module}', array('{module}' => Modules::displayName())),
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

            // function for getting the Nda sent count
    public function actionGetNdaCount($id,$status){

        $nameId = Yii::app()->db->createCommand()
        ->select('nameId')
        ->from('x2_listings2')
        ->where('id='.$id)
        ->queryRow();
        $countData = Yii::app()->db->createCommand()
        ->select('COUNT(id)')
        ->from('x2_sign_envelopes')
        ->where('status='.$status.' AND c_listing=:listingParam',array(':listingParam'=>$nameId['nameId']))
        ->queryScalar();
         echo $countData;
    }
    // function for getting the Bli sent count
    public function actionGetBliSentCount($id,$like='- BLI'){

        $nameId = Yii::app()->db->createCommand()
        ->select('nameId')
        ->from('x2_listings2')
        ->where('id='.$id)
        ->queryRow();
        $countData = Yii::app()->db->createCommand()
        ->select('COUNT(id)')
        ->from('x2_sign_envelopes')
        ->where('name like "% '.$like.'" AND c_listing=:listingParam',array(':listingParam'=>$nameId['nameId']))
        ->queryScalar();
         echo $countData;
    }

    public function actionGetTotalOffersMade($id){

        $nameId = Yii::app()->db->createCommand()
        ->select('nameId')
        ->from('x2_listings2')
        ->where('id='.$id)
        ->queryRow();
        $countData = Yii::app()->db->createCommand()
        ->select('COUNT(id)')
        ->from('x2_inquiries')
        ->where('c_contact_type__c is not null AND c_buyerstage="Offer"  AND c_listing__c=:listingParam',array(':listingParam'=>$nameId['nameId']))
        ->queryScalar();
         echo $countData;
    }
   // function for CIM count asked Peter for credientials to explore the functionality.
    public function actionGetCimSent($id){
    }

    public function actionGetOffersMadeByBuyer($id){
        
        $nameId = Yii::app()->db->createCommand()
        ->select('nameId')
        ->from('x2_listings2')
        ->where('id='.$id)
        ->queryRow();
        $countData = Yii::app()->db->createCommand()
        ->select('COUNT(id)')
        ->from('x2_inquiries')
        ->where('(c_contact_type__c="Buyer" OR c_contact_type__c="Buyer & Seller")AND c_buyerstage="Offer"  AND c_listing__c=:listingParam',array(':listingParam'=>$nameId['nameId']))
        ->queryScalar();
         echo $countData;
    }
    public function actionDownloadNda($id) {
        $zip = new ZipArchive();
        $filename = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            'NDA_download-'.time().'.zip'
        ));
        $doc_dataProvider = new CActiveDataProvider('Media', array(
            'criteria' => array(
                'condition' => "mimetype LIKE 'application/pdf%' AND c_document_type = 'NDA' AND associationType = 'Listings2' AND associationId = " . $id,
        )));
        $data = $doc_dataProvider->getData();
        if(empty($data)){
            $this->redirect(array('getPortal?id='.$id));
        }
        if(isset($data) && count($data) > 0) {
            $event = new Events;
            $event->associationType = 'listings2';
            $event->associationId = $id;
            $event->type = 'listings2';
            $event->subtype = 'download';
            $event->text = "Someone has downloaded documents" ;
            $event->timestamp = time();
            $event->lastUpdated = time();
            $event->save();

            if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
                exit("cannot open <$filename>\n");
            } 
            foreach($data as $document) {
                $filePath = $document->getPath();
                if ($filePath != null) { 
                    $new_filename = substr($filePath,strrpos($filePath,'/') + 1);
                    $zip->addFile($filePath,$new_filename);
                }
            }
            $zip->close();
            $file = Yii::app()->file->set($filename);
            if ($file->exists) $file->send();

            unlink($filename);  
        }
    }
}
?>
