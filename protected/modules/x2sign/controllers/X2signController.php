<?php
/***********************************************************************************
* Copyright (C) 2011-2019 X2 Engine Inc. All Rights Reserved.
*
* X2 Engine Inc.
* P.O. Box 610121
* Redwood City, California 94061 USA
* Company website: http://www.x2engine.com
*
* X2 Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
* to install and use this Software for your internal business purposes only
* for the number of users purchased by you. Your use of this Software for
* additional users is not covered by this license and requires a separate
* license purchase for such users. You shall not distribute, license, or
* sublicense the Software. Title, ownership, and all intellectual property
* rights in the Software belong exclusively to X2 Engine. You agree not to file
* any patent applications covering, relating to, or depicting this Software
* or modifications thereto, and you agree to assign any patentable inventions
* resulting from your use of this Software to X2 Engine.
*
* THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
* EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
***********************************************************************************/






class X2signController extends x2base {

    public $modelClass = 'X2SignEnvelopes';
    public $savedValues = array();
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow',
                'actions' => array(),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index', 'view', 'create', 'update', 'delete', 'report'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

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

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public function actionGetItems($term){
        LinkableBehavior::getItems ($term);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $model = $this->loadModel($id);
        if($this->checkPermissions($model,'view')){
            parent::view($model, 'x2SignEnvelopes');
        }else{
             $this->denied();
            //$this->redirect(Yii::app()->getBaseUrl(true));
        }
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new X2SignEnvelopes;
        $users=User::getNames();

        if(isset($_POST['X2SignEnvelopes'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['X2SignEnvelopes']);

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
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users = User::getNames();

        if(isset($_POST['X2SignEnvelopes'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['X2SignEnvelopes']);
            parent::update($model,$temp,'0');
        }

        $this->render('update',array(
            'model'=>$model,
            'users'=>$users,
        ));
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
     * Cancels a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionCancel($id) {
        if(Yii::app()->request->isPostRequest) {
            // we only allow cancellation via POST request
            $model = $this->loadModel($id);
            $this->cleanUpTags($model);
            $model->status = X2SignEnvelopes::CANCELLED;
            $model->save();

            /* if AJAX request (triggered by deletion via admin grid view), we should not redirect 
               the browser */
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        } else {
            throw new CHttpException(
                400,'Invalid request. Please do not repeat this request again.');
        }
    }


    //doing this to avoid post check
    public function actionCancel2($id) {
            $model = $this->loadModel($id);
            $this->cleanUpTags($model);
            $model->status = X2SignEnvelopes::CANCELLED;
            $model->save();

            /* if AJAX request (triggered by deletion via admin grid view), we should not redirect 
               the browser */
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }



    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     * Lists all models.
     */
/*
    public function actionIndex($id = null) {

        $envelope = new X2SignEnvelopes;

        $files = $envelope->getFiles(Yii::app()->user->name, $id);

        $view = $envelope->folderX2SignView(Yii::app()->user->name, $id, $files);

        $dataProvider = new FileSystemObjectDataProvider($view, array(
            'id' => 'root-folder-contents',
        ));

        $model=new X2SignEnvelopes('search');
        $this->render('index', array(
            'dataProvider'=> $dataProvider,
            'folderId' => $id,
        ));
    }
*/

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     * Lists all models.
     */
    public function actionIndex($id = null) {
        $this->layout = '//layouts/x2signMain';
        $envelope = new X2SignEnvelopes;
        $files = $envelope->getFiles(Yii::app()->user->name, $id);
        $view = $envelope->folderX2SignView(Yii::app()->user->name, $id, $files);
        $model = new X2SignEnvelopes('search');
        $rowCount = Profile::getResultsPerPage ();
        //if(empty($rowCount)) 
        $rowCount = 15;
        $dataProvider = !empty($_GET['X2SignEnvelopes']['relatedRecords']) ? $model->searchRelatedRecords($rowCount) : $model->searchAll($rowCount);//$model->searchAll(15);

        $folders = Yii::app()->db->createCommand()
            ->select('id, name')
            ->from('x2_sign_folders')
            ->where('createdBy = "' . User::getMe()->username . '"')
            ->queryAll();

        $this->render('index', array(
            'dataProvider'=> $dataProvider,
            'model' => $model,
            'folders' => $folders,
        ));
    }

    public function actionIndex2($id = null) {

        $model=new X2SignEnvelopes('search');

        if(isset($_GET['data-id'])){
            switch($_GET['data-id']){
            case 1:
                $this->redirect('indexs1', array('model'=>$model));
                break;
            case 2:
                $this->redirect('indexs2', array('model'=>$model));
                break;

            case 3:
                $this->redirect('indexs3', array('model'=>$model));
                break;
            case 4:
                $this->redirect('indexs4', array('model'=>$model));
                break;


            }
        }

    }

    // Index actions for envelope gridviews
    public function actionIndexS1($id = null) {
         $model=new X2SignEnvelopes('search');
         $this->render('index-1', array('model'=>$model));
    }

    public function actionIndexS2($id = null) {
         $model=new X2SignEnvelopes('search');
            $this->render('index-2', array('model'=>$model));
    }

    public function actionIndexS3($id = null) {
         $model=new X2SignEnvelopes('search');
            $this->render('index-3', array('model'=>$model));
    }

    public function actionIndexS4($id = null) {
         $model=new X2SignEnvelopes('search');
            $this->render('index-4', array('model'=>$model));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model=new X2SignEvents('search');
        $this->render('admin', array('model'=>$model));
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='x2sign-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     * Returns columns and rows to display on report(X2Sign)
     * @array
     */
    protected function getColumnAndRows($type = null) {

        $columns = array (
           'status' => array (
                'name'   => 'status',
                'header' => Yii::t('x2sign', 'Status'),
                'headerHtmlOptions'=>array('style'=>'width:5%'),
                'value'  => '$data->renderAttribute("status")',
            ),
            'type' => array(
                'name'   => 'type',
                'header' => Yii::t('x2sign', 'Event Type'),
                'headerHtmlOptions' =>array('style'=>'width:10%'),
                'value'  => '$data->renderAttribute("type")',
            ),
            'envelopeId' => array(
                'name'   => 'envelopeId',
                'header' => Yii::t('x2sign', 'Envelope'),
                'headerHtmlOptions' =>array('style'=>'width:15%'),
                'value'  => '$data->renderAttribute("envelopeId")',
                'type'   => 'raw'
            ),
            'documentId' => array(
                'name'   => 'documentId',
                'header' => Yii::t('x2sign', 'Document'),
                'headerHtmlOptions' =>array('style'=>'width:15%'),
                'value'  => '$data->renderAttribute("documentId")',
                'type'   => 'raw',
            ),
            'name' => array (
                'name'   => 'name',
                'header' => Yii::t('x2sign', 'Envelope Name'),
                'headerHtmlOptions'=>array('style'=>'width:15%'),
                'value'  => 'X2SignEnvelopes::getNameLink($data)',
                'type'   => 'raw'
            ),
            'sender' => array (
                'name'   => 'sender',
                'header' => Yii::t('x2sign', 'Sender'),
                'headerHtmlOptions'=>array('style'=>'width:15%'),
                'value'  => '$data->sender'
            ),
            'createDate' => array (
                'name'   => 'createDate',
                'header' => Yii::t('x2sign', 'Sent On'),
                'headerHtmlOptions'=>array('style'=>'width:15%'),
                'value'  => 'Formatter::formatDate($data->createDate)',
                'type'   => 'raw'
            ),
            'completeDate' => array (
                'name'   => 'completeDate',
                'header' => Yii::t('x2sign', 'Completed On'),
                'headerHtmlOptions'=>array('style'=>'width:15%'),
                'value'  => '$data->renderAttribute("completeDate")',
                'type'   => 'raw'
            ),
            'completeTime' => array (
                'name'   => 'completeTime',
                'header' => Yii::t('x2sign', 'CompleteTime'),
                'headerHtmlOptions'=>array('style'=>'width:15%'),
                'value'  => 'X2SignEnvelopes::getCompleteTime($data)',
                'type'   => 'raw'
            ),
        );

        if($type == 'activity' || $type == 'velocity'){
            $columns['createDate']['header'] = Yii::t('x2sign', 'Date');
        }

        return $columns;
    }

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     * Default Report View
     */
    public function actionReportDefault() {
        $model = new X2SignEnvelopes();
        $dataProvider = $model->search(15);

        $report_title = 'Envelope Default Report';
        $columns = $this->getColumnAndRows('default');
        $this->render('report', array(
            'report_title' => $report_title,
            'model' => $model,
            'columns' => $columns,
            'dataProvider' => $dataProvider,
        ));
    }
 
    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     */
    public function actionReportActivity() {
        $model = new X2SignEvents();
        $dataProvider = $model->search(15);

        $report_title = 'Envelope Activity Report';
        $columns = $this->getColumnAndRows('activity');
        $this->render('reportEvents', array(
            'report_title' => $report_title,
            'model' => $model,
            'columns' => $columns,
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     */
    public function getVelocityConditions () {
        $each_condition = array(
            "thisWeek"   => strtotime('this week 12:00 AM'),
            "lastWeek"   => strtotime('last week 12:00 AM') . ' AND createDate <' . strtotime('this week 12:00 AM'),
            "thisMonth"  => strtotime('first day of this month 12:00 AM'),
            "lastMonth"  => strtotime('first day of last month 12:00 AM') . ' AND createDate <' . strtotime('first day of this month 12:00 AM'),
            "last6Month" => strtotime('first day of -6 month 12:00 AM') . ' AND createDate <'. strtotime('first day of this month 12:00 AM'),
            "thisYear"   => strtotime('first day of this year 12:00 AM')
        );
        return $each_condition;
    }

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     */
    public function getVelocityDialogue($event_list) {

            $border_right = 'border-right:2px solid #dcdcdc;';
            $border_bottom = 'border-bottom: 2px solid #dcdcdc;';

            $message = '<br><table stype="width:100%">';
            $message .= '<tr><td><center>'. X2Html::logo ('menu', array (
                        'id' => 'your-logo',
                        'class' => 'default-logo',
                        )) . '</center></td></tr>';

            $event_table = '<tr><td><hr></td></tr><tr><td><table>';
            $event_table .= 
               '<thead>
                   <th style="' . $border_right . $border_bottom . 'text-align:center;width:10%; height:20px;">Event Type</th>
                   <th style="' . $border_right . $border_bottom . 'text-align:center;width:20%;">Date</th>
                   <th style="' . $border_right . $border_bottom . 'text-align:center;width:20%;">Envelope</th>
                   <th style="' . $border_bottom . 'text-align:center;width:20%;">Document</th>
               </thead>';
            $event_table .= '<tbody>';
            foreach($event_list as $event) {
                $event_table .= 
                       '<tr>
                       <td style="height:25px;text-align:center; ' . $border_right . $border_bottom . '"><span>' . $event->renderAttribute('type')  . '</span></td>
                       <td style="text-align:center;' . $border_right . $border_bottom . '"><span>' . Formatter::formatDate($event->createDate) . '</span></td>
                       <td style="text-align:center;' . $border_right . $border_bottom . '"><span>' . $event->renderAttribute('envelopeId') . '</span></td>
                       <td style="text-align:center;' . $border_bottom . '"><span>' . $event->renderAttribute('documentId') . '</span></td>
                       </tr>';
            }
            $event_table .= '</tbody>';
            $event_table .= '</table></td></tr>';

            $message .= $event_table;
            $message .= '</table>';
            return $message;
    }

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     */
    public function actionVelocityDialogue($type) {
        $timePeriod = $this->getVelocityConditions();

        $event_list = X2SignEvents::model()->findAllByAttributes(
            array(),
            "createDate>=" . $timePeriod[$type] . " AND " . "type=1"
        );

        $message = $this->getVelocityDialogue($event_list);
        echo json_encode(array('message' => $message));

    }

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     */
    public function actionReportVelocity() {
        $each_condition = $this->getVelocityConditions();

        $velocity_dates = array(
           "thisWeek"    => Formatter::formatDate(strtotime('this week 12:00 AM')) . ' ~~ Today',
           "lastWeek"    => Formatter::formatDate(strtotime('last week 12:00 AM')) . ' ~~ ' . Formatter::formatDate(strtotime('this week 12:00 AM')),
           "thisMonth"   => Formatter::formatDate(strtotime('first day of this month 12:00 AM')) . ' ~~ Today',
           "lastMonth"   => Formatter::formatDate(strtotime('first day of last month 12:00 AM')) . ' ~~ ' . Formatter::formatDate(strtotime('first day of this month 12:00 AM')),
           "last6Month"  => Formatter::formatDate(strtotime('first day of -6 month 12:00 AM')) . ' ~~ ' . Formatter::formatDate(strtotime('first day of this month 12:00 AM')),
           "thisYear"    => Formatter::formatDate(strtotime('first day of this year 12:00 AM')) . ' ~~ Today',
        );

        $velocity_count = array();
        $user = User::getMe();
        foreach($each_condition as $key => $value) {
            $velocity_count[$key] = X2SignEnvelopes::model()->count("createDate>=" . $value . ' AND ' . " assignedTo = '" . $user->username . "'");
        }
        $report_title = 'Envelope Velocity Report';
        $this->render('velocity', array(
            'report_title'   => $report_title,
            'velocity_dates' => $velocity_dates,
            'velocity_count' => $velocity_count,
        ));
    }

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     */
    public function actionReportCompleted() {
        $model = new X2SignEnvelopes;
        $model->status = 4;
        $dataProvider = $model->search(15);

        $report_title = 'Envelope Completed Report';
        $columns = $this->getColumnAndRows('completed');
        $this->render('report', array(
            'report_title' => $report_title,
            'model' => $model,
            'columns' => $columns,
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     */
    public function actionReport() {
        $this->render('reportList'); 
    }


    public function actionGetQuickRecord($selModel = 'All Models', $id){
        $contact = null;
        $email = '';
        switch($selModel){
            case 'Contacts':
                $contact = X2Leads::model()->findByPk($id);
                if (!isset($contact)) throw new CHttpException(500, 'No contact found matching that information');
                $email = $contact->c_email;
                break;
            case 'Buyers': 
                $contact = Contacts::model()->findByPk($id);
                if (!isset($contact)) throw new CHttpException(500, 'No buyer found matching that information');
                $email = $contact->email;
                break;
            case 'Sellers':
                $contact = Sellers2::model()->findByPk($id);
                if (!isset($contact)) throw new CHttpException(500, 'No seller found matching that information');
                $email = $contact->c_email;
                break;
        }

        if($selModel == 'Sellers'){
            $nameParts = explode(" ",$contact->name);

            $recordsReturn = array(
                'modelId' => $contact->id,
                'modelType' => get_class($contact),
                'email' => $email,
                'firstName'=> isset($nameParts[0]) ? $nameParts[0] : "",
                'lastName' => isset($nameParts[1]) ? $nameParts[1] : "",
                'assignedTo' => $contact->assignedTo
            );
        } else {
            $recordsReturn = array(
                'modelId' => $contact->id,
                'modelType' => get_class($contact),
                'email' => $email,
                'firstName'=> $contact->firstName,
                'lastName' => $contact->lastName,
                'assignedTo' => $contact->assignedTo
            );
        }
        echo CJSON::encode(array($recordsReturn));
    }

    /**
     * Fetch recipients/documents/ for quick create
     */
    public function actionGetListQuick($type, $model='Contacts', $query='', $selModel = 'All Models', $first='', $last='', $email='' ) {
        switch ($type) {
            case 'signees':
            case 'viewers':

                //since listtings do not have email pull contacts
                //if($model == "Listings2" || $model == "Opportunity") $model = "Sellers2";

                $records = [];
                $contactCriteria = new CDbCriteria;
                $contactCriteria->addCondition('(name LIKE "%' . addslashes($first . ' ' . $last) . '%")');
                $contactCriteria->addCondition('email IS NOT NULL');
                $contactCriteria->addCondition('TRIM(email) <> ""');
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

                $contactCriteria = new CDbCriteria;
                $contactCriteria->addCondition('(firstName LIKE "%' . addslashes($first) . '%" OR lastName LIKE "%' . addslashes($last) . '%")');
                $contactCriteria->addCondition('email IS NOT NULL');
                $contactCriteria->addCondition('TRIM(email) <> ""');
                $contactList = X2Leads::model()->search(100, null, $contactCriteria, true)->getData();
                if($selModel == 'All Models' || $selModel == 'Contacts'){
                     foreach ($contactList as $contact) {
                        $email = $contact->c_email;
                        $name = $contact->name ? $contact->name : "$contact->firstName $contact->lastName";
                        $records[] = array(
                            'modelId' => $contact->id,
                            'modelType' => 'X2Leads',
                            'viewName' => $name . " - "  . "$email" . " - " . 'Contact',
                            'firstName'=> $contact->firstName,
                            'lastName' => $contact->lastName,

                            'viewText' => "<b>$name</b>, $email",
                            'assignedTo' => $contact->assignedTo
                        );
                    }
                }
                $contactCriteria = new CDbCriteria;
                 $contactCriteria->addCondition('(name LIKE "%' . addslashes($first . ' ' . $last) . '%")');
                $contactCriteria->addCondition('c_email IS NOT NULL');
                $contactCriteria->addCondition('TRIM(email) <> ""');
                $contactList =  Sellers2::model()->search(100, $contactCriteria)->getData();
                if($selModel == 'All Models' || $selModel == 'Sellers'){
                    foreach ($contactList as $contact) {
                        $email = $contact->c_email;
                        $name = $contact->name ? $contact->name : "$contact->firstName $contact->lastName";
                        $nameParts = explode(" ",$name);
                        $records[] = array(
                            'modelId' => $contact->id,
                            'modelType' => 'Sellers2',
                            'viewName' => $name . " - "  . "$email" . " - " . 'Seller',
                            'firstName'=> isset($nameParts[0]) ? $nameParts[0] : "",
                            'lastName' => isset($nameParts[1]) ? $nameParts[1] : "",
                            'viewText' => "<b>$name</b>, $email",
                            'assignedTo' => $contact->assignedTo
                        );
                    }
                }
                echo CJSON::encode(array($records));
                break;
        }
    }



    /**
     * Fetch recipients/documents
     */
    public function actionGetList($type, $model='Contacts', $query='', $selModel = 'All Models') {
        switch ($type) {
            case 'documents':
                //manual addition of conditions since X2SignDocs are not the main X2Model of the x2sign module
                $criteria = new CDbCriteria();
                $criteria->addCondition('name NOT LIKE "ReplacedFields%"');
                $criteria->addCondition('type = "x2signdoc"');
                if (!Yii::app()->params->isAdmin){
                    $libId = Yii::app()->suModel->getLibrary()->id ?? "NULL";
                    //$accessCond .= " AND libraryId = $libId";
                    $criteria->addCondition('libraryId = ' . $libId . ' OR assignedTo = "' .  Yii::app()->user->getName() .'"');
                }else{
                    $criteria->compare('name', $query, true, 'AND');
                }
                $data = Docs::model()->searchBase($criteria, 2000, false, true)->getData();
                //for no return 
                if(count($data) == 0){
                    echo CJSON::encode(array());
                    break;
                }
                $ids = array_map(function($e){return $e->id;}, $data);
                $criteria = new CDbCriteria();
                foreach ($ids as $id) {
                    $criteria->compare('docId', $id, false, 'OR');
                }
                $provider = new CActiveDataProvider('X2SignDocs');
                $criteria->compare('name', $query, true, 'AND');
                $criteria->order = 'name ASC';
                $provider->setCriteria($criteria);
                $provider->pagination = false;
                $X2SignDocs = $provider->getData(); 
                $templates = array();
                foreach($X2SignDocs as $X2SignDoc)
                    $templates[] = array(
                        'modelId' => $X2SignDoc->id,
                        'viewName' => "$X2SignDoc->name",
                        'viewText' => $X2SignDoc->name,
                        'signerCount' => count(json_decode($X2SignDoc->recipients)),
                    );

                echo CJSON::encode(array($templates));
                break;
            case 'signees':
            case 'viewers':

                //since listtings do not have email pull contacts
                //if($model == "Listings2" || $model == "Opportunity") $model = "Sellers2";

                $records = [];
                $contactCriteria = new CDbCriteria;
                $contactCriteria->addCondition('(name LIKE "%' . addslashes($query) . '%")');
                $contactCriteria->addCondition('email IS NOT NULL');
                $contactCriteria->addCondition('TRIM(email) <> ""');
                $contactList = Contacts::model()->search(100, $contactCriteria)->getData();
                if($selModel == 'All Models' || $selModel == 'Buyers'){
                    foreach ($contactList as $contact) {
                        $email = $contact->email;
                        $name = $contact->name ? $contact->name : "$contact->firstName $contact->lastName";
                        $records[] = array(
                            'modelId' => $contact->id,
                            'modelType' => 'Contacts',
                            'viewName' => $name . " - "  . "$email" . " - " . 'Buyer',
                            'viewText' => "<b>$name</b>, $email",
                            'assignedTo' => $contact->assignedTo
                        );
                    }
                }

                $contactCriteria = new CDbCriteria;
                $contactCriteria->addCondition('(firstName LIKE "%' . addslashes($query) . '%" OR lastName LIKE "%' . addslashes($query) . '%")');
                $contactCriteria->addCondition('email IS NOT NULL');
                $contactCriteria->addCondition('TRIM(email) <> ""');
                $contactList = X2Leads::model()->search(100, null, $contactCriteria, true)->getData();
                if($selModel == 'All Models' || $selModel == 'Contacts'){
                     foreach ($contactList as $contact) {
                        $email = $contact->c_email;
                        $name = $contact->name ? $contact->name : "$contact->firstName $contact->lastName";
                        $records[] = array(
                            'modelId' => $contact->id,
                            'modelType' => 'X2Leads',
                            'viewName' => $name . " - "  . "$email" . " - " . 'Contact',
                            'viewText' => "<b>$name</b>, $email",
                            'assignedTo' => $contact->assignedTo
                        );
                    }
                }


                $contactCriteria = new CDbCriteria;
                $contactCriteria->compare('name', $query, true);
                $contactCriteria->addCondition('c_email IS NOT NULL');
                $contactCriteria->addCondition('TRIM(email) <> ""');
                $contactList = Sellers2::model()->search(100, $contactCriteria)->getData();
                if($selModel == 'All Models' || $selModel == 'Sellers'){
                    foreach ($contactList as $contact) {
                        $email = $contact->c_email;
                        $name = $contact->name ? $contact->name : "$contact->firstName $contact->lastName";
                        $records[] = array(
                            'modelId' => $contact->id,
                            'modelType' => 'Sellers2',
                            'viewName' => $name . " - "  . "$email" . " - " . 'Seller',
                            'viewText' => "<b>$name</b>, $email",
                            'assignedTo' => $contact->assignedTo
                        );
                    }
                }

                echo CJSON::encode(array($records));
                break;
        }
    }

    /**
     * Author: Justin Law <jlaw@x2engine.com>
     * Send email with link to recipients leading to signing page
     */
    public function actionSendDocs() {
        // check required post keys
        $postKeys = ['modelType', 'emailSubject','emailBody', 'documents', 'signees', 'viewers'];
        foreach ($postKeys as $key) {
            if (!array_key_exists($key, $_POST))
                throw new Exception ('Bad request');
        }
        //check documents meets required signee count
        $docs = X2SignDocs::model()->findAllByPk(json_decode($_POST['documents']));
        $countSignees = count(json_decode($_POST['signees']));
        $countNeeded = max(array_map(function($e){return count(json_decode($e->recipients));}, $docs));
        if ($countSignees < $countNeeded) throw new Exception ("Insufficient number of signees: $countNeeded required.");
        
        // create envelope from document templates
        $user = User::getMe();
        $email = Credentials::model()->findDefault($user->id, 'email', TRUE);
        if(empty($email))
            throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
        else
            $email = $email->auth->email;

        $signees = json_decode($_POST['signees']);
        $viewers = json_decode($_POST['viewers']);
        $modelType = $_POST['modelType'];
        $models = array();
        if($modelType == "Listings2") $modelType = "Contacts"; 
        $names = [];
        $models = array();
        foreach (json_decode($_POST['signees'], false) as $blob) {
            if ($blob->id == 'SELF'){
                $models[] = User::getMe();
            }else{
                $names[] = $blob->modelType::model()->findByPk($blob->id)->name;
                $models[] = $blob->modelType::model()->findByPk($blob->id);
            }
        }

        $mainRecip = null;
        foreach (json_decode($_POST['signees'], false) as $blob) {
            if ($blob->id == 'SELF'){
                continue;
            }else{
                $mainRecip = $blob->modelType::model()->findByPk($blob->id);
                break;
            }
        }



       $names = implode(', ', $names);

        $envelope = new X2SignEnvelopes;
        $envelope->setAttributes(array(
            'name' => date('M d, Y: ') . Docs::replaceVariables("$_POST[emailSubject] @: $names", $mainRecip),
            'assignedTo' => $user->username,
            'signDocIds' => $_POST['documents'],
            'sender' => $email,
            'email' => json_encode([
                'subject' => Docs::replaceVariables("$_POST[emailSubject]", $mainRecip),
                'body' => $_POST['emailBody'],
                'reminders' => json_decode($_POST['reminders'] ?? ''),
                ]),
            'emailSubject' => Docs::replaceVariables("$_POST[emailSubject]", $mainRecip),
            'emailBody' => $_POST['emailBody'],
            'sequential' => json_decode($_POST['sequential']) ?? $envelope->sequential,
            'reminders' => $_POST['reminders'] ?? NULL,
        ), false);
        
        //save the listing if the x2sign doc is sent to a contact
        if( $modelType == "Contacts" || $modelType == "Sellers2"){
            $contactid;
            if(json_decode($_POST['signees'])[0]->id == 'SELF'){
                $contactid = json_decode($_POST['signees'])[1]->id;    
            }else{
                $contactid = json_decode($_POST['signees'])[0]->id;
            }
            $tempHold = $modelType::model()->findByPk($contactid);
            if(isset($tempHold))
                $envelope->c_listing = $tempHold->c_listinglookup__c;

        }

        if (!$envelope->save())
            throw new Exception ('Could not create envelope');


        $fieldArray = array();
        
        foreach($docs as $doc){
            $fields = json_decode($doc->fieldInfo, 1);
            foreach($fields as $field){
                $X2SignField = new X2SignFields;
                $hold = '';
                if(isset($field['value'])){
                         $hold = Docs::replaceVariables($field['value'], $models[$field['recip'] - 1]);
                          if($field['value'] == "{e_name}"){
                               $employee = Employees::model()->findByAttributes(array('c_user__c' => $envelope->assignedTo));
                                if(isset($employee)){
                                    $hold = $employee->name ;
                                }
                            }
                    if(strpos($hold, '$') !== false){
                        $hold= str_replace('$', '', $hold);
                        $hold= str_replace(',', '', $hold);
                    }


                }

                
                $X2SignField->setAttributes(array(
                    'signDocId' => $doc->id,
                    'envelopeId' => $envelope->id,
                    'fieldId' => $field['id'],
                    'value' => $hold
                ), false);
                
                $fieldArray[] = $X2SignField;
                
            }
            
        }
        foreach($fieldArray as $xField){
            $xField->save();
        }
        

        // Send Envelope
        try {
            $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
            $fails = $behavior->sendEnvelope($signees, $viewers, $modelType, $envelope);
        } catch (Exception $e) {
            // without all links, we should not proceed; destroy the evidence
            X2SignLinks::model()->deleteAllByAttributes(array(
                'envelopeId' => $envelope->id,
            ));
            $envelope->delete();
            foreach($fieldArray as $xField){
                $xField->delete();
            }
            echo 'Issue creating links: Please contact support.';
            return;
        }

        if (empty($fails)) {
        //create event
            $event = new X2SignEvents;
            $event->setAttributes(array(
                'createDate' => time(),
                'assignedTo' => $envelope->assignedTo,
                'envelopeId' => $envelope->id,
                'type' => X2SignEvents::SENT,
            ), false);
            $event->save();
            echo 'Success!';
        } else {
            echo "Could not send to: " . json_encode($fails);
        }
    }


    //this will be for the mobile send 

    public function actionMobileSend(){

        //first get the signeees
        $signees = json_decode($_POST['recips']);
        //got to get the records
        $signModels = array();
        $names =array();
        foreach ($signees as $sign) {
            if ($sign[0] == 'myself'){
                $models[] = User::getMe();
                $signModels[] = json_decode(json_encode(array('id'=>'SELF', 'modelType'=> 'SELF') , FALSE));
            }else{
                $names[] = $sign[0]::model()->findByPk($sign[1])->name;
                $models[] = $sign[0]::model()->findByPk($sign[1]);
                $signModels[] = json_decode(json_encode(array('id'=>$sign[1], 'modelType'=> $sign[0]), FALSE));
            }
        }

        // create envelope from document templates
        $user = User::getMe();
        $email = Credentials::model()->findDefault($user->id, 'email', TRUE);
        if(empty($email))
            throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
        else
            $email = $email->auth->email;


        $names = implode(', ', $names);

        $envelope = new X2SignEnvelopes;
        $envelope->setAttributes(array(
            'name' => date('M d, Y: ') . Docs::replaceVariables("$_POST[mailSubject] @: $names", $models[0]),
            'assignedTo' => $user->username,
            'signDocIds' => $_POST['docs'],
            'sender' => $email,
            'email' => json_encode([
                'subject' => Docs::replaceVariables("$_POST[mailSubject]", $models[0]),
                'body' => $_POST['mailBody'],
                //'reminders' => json_decode($_POST['reminders'] ?? ''),
                ]),
            'emailBody' => $_POST['mailBody'],
            'emailSubject'  => Docs::replaceVariables("$_POST[mailSubject]", $models[0]),
            'sequential' => 1,
        ), false);

        //get the docs now
        $docs = X2SignDocs::model()->findAllByPk(json_decode($_POST['docs']));

        //set the fields
        $fieldArray = array();
        if(empty($docs)) throw new Exception ('No documents attached');
        foreach($docs as $doc){
            $fields = json_decode($doc->fieldInfo, 1);
            foreach($fields as $field){
                $X2SignField = new X2SignFields;
                $hold = '';
                if(isset($field['value'])) {
                    $hold = Docs::replaceVariables($field['value'], $models[$field['recip'] - 1]);
                    $x2Field = Fields::model()->findByAttributes(array('modelName'=>get_class($models[$field['recip'] - 1]), 'fieldName'=>$field['value']));
                    if (isset($x2Field)) $x2FieldType = $x2field->type;
                    if(strpos($hold, '$') !== false){
                        $hold= str_replace('$', '', $hold);
                        $hold= str_replace(',', '', $hold);
                    }
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

        }
        foreach($fieldArray as $xField){
            $xField->save();
        }

        if (!$envelope->save())
            throw new Exception ('Could not create envelope');

       // Send Envelope
        try {
            $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
            $fails = $behavior->sendEnvelope($signModels, array(), get_class($models[0]), $envelope);
        } catch (Exception $e) {
            // without all links, we should not proceed; destroy the evidence
            X2SignLinks::model()->deleteAllByAttributes(array(
                'envelopeId' => $envelope->id,
            ));
            $envelope->delete();
            foreach($fieldArray as $xField){
                $xField->delete();
            }
            echo 'Issue creating links: Please contact support.';
            return;
        }

        if (empty($fails)) {
        //create event
            $event = new X2SignEvents;
            $event->setAttributes(array(
                'createDate' => time(),
                'assignedTo' => $envelope->assignedTo,
                'envelopeId' => $envelope->id,
                'type' => X2SignEvents::SENT,
            ), false);
            $event->save();
            echo 'Success!';
        } else {
            echo "Could not send to: " . json_encode($fails);
        }


    }



    /**
     * For signing an owned envelope through in-app link.
     */
    public function actionSignFromEnvelope($id) {
        $envelope = X2SignEnvelopes::model()->findByAttributes(
            array('id'=>(int)$id, 'assignedTo'=>Yii::app()->user->name));
        if (!isset($envelope->assignedTo))
            throw new CHttpException (403);

        $link = X2SignLinks::model()->findByAttributes([
            'envelopeId'=>$envelope->id,
            'modelType'=>'User',
            'modelId'=>Yii::app()->suId]);

        if (!isset($link))
            throw new CHttpException (500, 'Signature not expected');

        if (time() >= $link->createDate + 1209600) {
            $link->renewKey();
            $link->sent = 1;
            $link->save();
        }

        $this->redirect($this->createUrl('signDocs', ['key'=>$link->key]));
    }

     /**
     * @author Jack Lento
      * this function will get passed the doc id, envelope and fieldId
      * will return value if we have a saved one, will return null if not
     */   
    
/*    private function getSavedValue($envelopeId, $signDocId, $fieldId){

        $field = X2SignFields::model()->findByAttributes([
            'envelopeId'=>$envelopeId,
            'signDocId'=>$signDocId,
            'fieldId'=> $fieldId]);
        if(isset($field)){
            return $field->value;
        }else{
            return NULL;
        }
    }
*/


    private function getSavedValue($envelopeId, $signDocId, $fieldId){

        if(count($this->savedValues) == 0){
            $this->savedValues['$envelopeId'] = array();
            $this->savedValues['$envelopeId']['$signDocId'] = array();



            $fields =  X2SignFields::model()->findAllByAttributes(array(
                'envelopeId' => $envelopeId,
                'signDocId' => $signDocId,
            ));


            foreach($fields as $field){
                $this->savedValues['$envelopeId']['$signDocId'][$field->fieldId] = $field->value;


            }

        }
//        $field = X2SignFields::model()->findByAttributes(array(
//            'envelopeId' => $envelopeId,
//            'signDocId' => $signDocId,
//            'fieldId' => $fieldId
//        ));

        if(isset($this->savedValues['$envelopeId']['$signDocId'][$fieldId]))
            return $this->savedValues['$envelopeId']['$signDocId'][$fieldId];
        else
            return NULL;
    }

    /**
     * @author Clifton Chiang <clifton@x2engine.com>
     */
    public function actionSignDocs($key) {
        $signLink = X2SignLinks::model()->findByAttributes(array(
            'key' => Fields::getPurifier()->purify($key),
        ));

        $this->layout = '//layouts/empty'; //don't use column3

        // Make sure the link is still valid before allowing
        // recipient to sign it (link must be less than 5 days old)
        if(isset($signLink) && time() < ($signLink->createDate + 432000) && $signLink->signedDate === NULL) {
            // Find relevant information for the current recipient if they need to sign the document
            $model = X2Model::model($signLink->modelType)->findByPk($signLink->modelId);
            $envelope = X2SignEnvelopes::model()->findByPk($signLink->envelopeId);

            if ($envelope->status == X2SignEnvelopes::CANCELLED)
                $this->redirect('signingCancelled');
            if ($envelope->status == X2SignEnvelopes::COMPLETED)
                throw new CHttpException (400, "Invalid link. Do not repeat this action.");

            $signDoc = X2SignDocs::model()->findByPk(json_decode($envelope->signDocIds)[0]);
            $fields = json_decode($signDoc->fieldInfo);
            if(!is_array($fields))$fields = array();
            // Find information of other recipients to show others previously input data
            $signFields = X2SignFields::model()->findAllByAttributes(array(
                'envelopeId' => $envelope->id,
                'signDocId' => $signDoc->id,
            ));

            $signeeFields = array();
            if(isset($model)) {
                foreach($fields as $field) {
                    if($field->recip == $signLink->position) { // Field is intended for recipient
                       $prevalue = null;
                       if(isset($field->value)) $prevalue = $field->value;
                       foreach($signFields as $signField) {
                           if(($field->id == $signField->fieldId) && !empty($signField->value) && $prevalue != $signField->value) {
                                if(strpos($field->id, "Signature") !== false || strpos($field->id, "Initials") !== false) {
                                    $recipSignature = X2Signature::model()->findByPk($signField->value);
                                    $field->value = $recipSignature->getSignatureImage();
                                } else {
                                    $field->value = $signField->value;
                                }

                           }
                        //need to do specail work here for checkboxes since 0, on, checked confuse JS AND PHP
                           if(($field->id == $signField->fieldId) && strpos($field->id, "Checkbox") !== false && $signField->value == 0){
                                $field->value = 0;
                            }

                        }
                        if(isset($field->value))
                            $field->value = Docs::replaceVariables($field->value, $model);
                    } else { // This field is another recipient's, but we want to show it to other recipients so they can see previous inputs
                        $field->{'read-only'} = 1;
                        foreach($signFields as $signField) {
                            if(($field->id == $signField->fieldId) && isset($signField->value) && !empty($signField->value)) {
                                if(strpos($field->id, "Signature") !== false || strpos($field->id, "Initials") !== false) {
                                    $recipSignature = X2Signature::model()->findByPk($signField->value);
                                    $field->value = $recipSignature->getSignatureImage();
                                } else {
                                    $field->value = $signField->value;
                                }
                            }
                        }
                    }
                    $signeeFields[] = $field;
                }
            }



            if(!isset($signLink->viewedDate)){
                $now = time();
                $viewAction = new Actions;
                $viewAction->setAttributes(array(
                    'createDate' => $now,
                    'completeDate' => $now,
                    'associationType' => 'X2SignEnvelopes',
                    'associationId' => $envelope->id,
                    'associationName' => $envelope->name,
                    'visibility' => 1,
                    'assignedTo' => $envelope->assignedTo,
                    'actionDescription' => "Signdoc Viewed",
                    'complete' => "Yes",
                ));

                /*$viewAction->createDate = $now;
                $viewAction->completeDate = $now;
                $viewAction->associationType = 'X2SignEnvelopes';
                $viewAction->associationId = $envelope->id;
                $viewAction->associationName = $envelope->name;
                $viewAction->visibility = 1;
                $viewAction->assignedTo = $envelope->assignedTo;
                $viewAction->actionDescription = "Signdoc Viewed";*/
                $viewAction->save();

                $signLink->viewedDate = $now;
                $signLink->save();
            }

            //check if more then one document
            $docCount = count(json_decode($envelope->signDocIds));
            if($docCount > 1) $lastDoc = false;
            else $lastDoc = true;



            //record view for each time
            $event = new X2SignEvents;
            $event->setAttributes(array(
                'createDate' => time(),
                'assignedTo' => $envelope->assignedTo,
                'documentId' => $signDoc->id,
                'envelopeId' => $envelope->id,
                'signLinkId' => $signLink->id,
                'IP' => Yii::app()->request->getUserHostAddress(),
                'type' => X2SignEvents::VIEWED,
            ), false);
            $event->save();
            $this->render('signDoc', array(
                'envelopeId' => $envelope->id,
                'modelType' => $signLink->modelType,
                'model' => $model,
                'signDocId' => $signDoc->id,
                'mediaId' => $signDoc->mediaId,
                'fields' => json_encode($signeeFields),
                'signReq' => $signLink->signRequired,
                'emailAddress' => $signLink->emailAddress,
                'position' => $signLink->position,
                'key' => Fields::getPurifier()->purify($key),
                'lastDoc' => $lastDoc,
            ));
        } else if (isset($signLink->signedDate)) {
            $envelope = X2SignEnvelopes::model()->findByPk($signLink->envelopeId);
            $this->redirect(array('signingComplete', 'key' => $signLink->key));
            Yii::app()->end();
            //echo "You have already signed this envelope!";
        } else if (isset($signLink)){
            // Check to see if document was cancelled
            $envelope = X2SignEnvelopes::model()->findByPk($signLink->envelopeId);

            if($envelope->status == X2SignEnvelopes::CANCELLED)
                $this->redirect('signingCancelled');

            try {
                echo "This link has expired.\n";
                $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
                $fail = $behavior->resendLink($signLink);
                if (!empty($fail)) throw new Exception('Could not resend email.');
                echo 'A new link has been sent to your email.';
            } catch (Exception $e) {
                $msg = $e->getMessage();
                echo "Could not generate new link. Reason:$e->msg\n",
                    "Please contact support for further assistance.";
            }
        } else {
            echo "Invalid link. If you belive that this is a valid link, please check your email for a newer link that may of been sent to you.";
        }

    }

    /**
     * @author Clifton Chiang <clifton@x2engine.com>
     * @edited By: Justin Toyomitsu <justin@x2engine.com>, Justin Law <jlaw@x2engine.com>
     *
     */

   /**
     * @author Clifton Chiang <clifton@x2engine.com>
     */
    public function actionSignDocs2($key) {
        $signLink = X2SignLinks::model()->findByAttributes(array(
            'key' => Fields::getPurifier()->purify($key),
        ));

        $this->layout = '//layouts/empty'; //don't use column3

        // Make sure the link is still valid before allowing
        // recipient to sign it (link must be less than 5 days old)
        if(isset($signLink) && time() < ($signLink->createDate + 1209600) && $signLink->signedDate === NULL) {
            // Find relevant information for the current recipient if they need to sign the document
            $model = X2Model::model($signLink->modelType)->findByPk($signLink->modelId);
            $envelope = X2SignEnvelopes::model()->findByPk($signLink->envelopeId);

            if($envelope->status == X2SignEnvelopes::CANCELLED)
                $this->redirect('signingCancelled');

            $signDoc = X2SignDocs::model()->findByPk(json_decode($envelope->signDocIds)[0]);
            $fields = json_decode($signDoc->fieldInfo);

            // Find information of other recipients to show others previously input data
            $signFields = X2SignFields::model()->findAllByAttributes(array(
                'envelopeId' => $envelope->id,
                'signDocId' => $signDoc->id,
            ));

            $signeeFields = array();
            if(isset($model)) { 
                foreach($fields as $field) {
                    if($field->recip == $signLink->position) { // Field is intended for recipient
                        if(isset($field->value)) {
                            //this will be fore employe records
                            if($field->value == "{e_name}"){
                               $employee = Employees::model()->findByAttributes(array('c_user__c' => $signDoc->assignedTo));
                                if(isset($employee)){
                                    $field->value = $employee->name ;
                                }
                            }
                            $field->value = Docs::replaceVariables($field->value, $model);
                            // Specifically for Transworld, strip $ from monetary values since it'll throw
                            // signing error when updating the record with their inputs
                            if(strpos($field->value, '$') !== false)
                                $field->value = str_replace('$', '', $field->value);

                        }
                    } else { // This field is another recipient's, but we want to show it to other recipients so they can see previous inputs
                        $field->{'read-only'} = 1;
                        foreach($signFields as $signField) {
                            if(($field->id == $signField->fieldId) && isset($signField->value)) {
                                if(strpos($field->id, "Signature") !== false) {
                                    $recipSignature = X2Signature::model()->findByPk($signField->value);
                                    $field->value = $recipSignature->getSignatureImage();
                                } else {
                                    $field->value = $signField->value;
                                }
                            }
                        }
                    }
                    $signeeFields[] = $field;
                }
            }

            $this->render('signDoc2', array(
                'envelopeId' => $envelope->id,
                'modelType' => $signLink->modelType,
                'model' => $model,
                'signDocId' => $signDoc->id,
                'mediaId' => $signDoc->mediaId,
                'fields' => json_encode($signeeFields),
                'signReq' => $signLink->signRequired,
                'emailAddress' => $signLink->emailAddress,
                'position' => $signLink->position,
            ));
        } else if (isset($signLink->signedDate)) {
            echo "You have already signed this envelope!";
        } else if (isset($signLink)){
            try {
                echo "This link has expired.\n";
                $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
                $fail = $behavior->resendLink($signLink);
                if (!empty($fail)) throw new Exception('Could not resend email.');
                echo 'A new link has been sent to your email.';
            } catch (Exception $e) {
                $msg = $e->getMessage();
                echo "Could not generate new link. Reason:$e->msg\n",
                    "Please contact support for further assistance.";
            }
        } else {
            echo "Invalid link. If you belive that this is a valid link, please check your email for a newer link that may of been sent to you.";
        }
    }




    public function actionGetFile($id, $render=1) {
        $id = (int) $id;
        $media = Media::model()->findByPk($id);
        if (!isset($media)) throw new Exception('Media not found.');

        if($media->isImage()) {
            echo $media->renderFile();
            return;
        } else if ($media->isDocx()) {
            try {
                $media = $media->docxToPdf();
            } catch (Exception $e) {
                $msg = $e->getMessage();
                throw new Exception("DOCX could not be converted to PDF: $msg");
            }
        }
        
        if ($media->isPdf()) 
            echo $render ? $media->renderFile() : $media->id;
        else 
            throw new CHttpException(500, 'Media not of type PDF or convertible to a PDF: ' . $media->name);
    }

    public function actionSignDocsIndex() {
        $model = new X2SignDocs('search');

        Yii::app()->user->setState('vcr-list', 'index');
        $this->render('signDocsIndex', array('model' => $model));
    }

    /**
    *Generate a preview page for the curent docs
    */

    public function actionPreviewDocs($modelType, $signees, $documents, $DocNum = 0){
            // Find relevant information for the current recipient if they need to sign the document
            //$model = X2Model::model($modelType)->findByPk(json_decode($signees)[0]);
            //$envelope = X2SignEnvelopes::model()->findByPk($signLink->envelopeId);
            //if the doc number is biger then number of docs reset it
            if(count(json_decode($documents)) - 1 < $DocNum){
                $DocNum = 0;
            }
            $signDoc = X2SignDocs::model()->findByPk(json_decode($documents)[$DocNum]);
            $fields = json_decode($signDoc->fieldInfo);

            // Find information of other recipients to show others previously input data
            $signFields = X2SignFields::model()->findAllByAttributes(array(
                'signDocId' => $signDoc->id,
            ));
            $signPeople = json_decode($signees);
            $signeeFields = array();
            $names = array();
            //go through each sign person and and fill the values for fields              
             foreach (json_decode($signees, false) as $key => $blob) {
                if($blob->id == "SELF"){
                    $model = User::getMe();
                    $names[] = $model->fullName;
                }else{
                    $model = $blob->modelType::model()->findByPk($blob->id);
                    $names[] = $model->name;
                }
                if(isset($model)) {
                    foreach($fields as $field) {
                        if($field->recip == ($key + 1)) { // Field is intended for recipient
                            if(isset($field->value))
                                $field->value = Docs::replaceVariables($field->value, $model);
                                $signeeFields[] = $field;
                        }
                    }
                }
            }

            //this will make the url for the next document
            $nextDocUrl = Yii::app()->absoluteBaseUrl.Yii::app()->request->requestUri."&DocNum=".($DocNum+1);
            $this->render('viewPrviewSign', array(
                //'model' => $model,
                'signDoc' => $signDoc,
                'mediaId' => $signDoc->mediaId,
                'docId' => $signDoc->docId,
                'fieldsInfo' => json_encode($signeeFields),
                'names' => $names,
                'nextDocUrl' => $nextDocUrl,       
            ));


//            $this->render('previewDoc', array(
                //'envelopeId' => $envelope->id,
//                'modelType' => $modelType,
//                'model' => $model,
//                'signDocId' => $signDoc->id,
//                'mediaId' => $signDoc->mediaId,
//                'fields' => json_encode($signeeFields),
//                'signReq' => 1,
//                'position' => 1,
//            ));        
    }


    /**
     * Save the event of signing for a recipient, if the envelope is complete,
     * it will be completed as well and a certificate of completion will be generated
     */
    public function actionSign() {
        // Throw exception if malicious data is trying to be passed through ids
        foreach(array($_POST['signDocId'], $_POST['envelopeId'], $_POST['modelId'], $_POST['cancel']) as $id)
            if(!is_numeric($id))
                throw new CHttpException(400, "Invalid request. Please do not repeat this request again.");

        //check to make sure the key goes to the envelope
        $signLink = X2SignLinks::model()->findByAttributes(array(
            'key' => Fields::getPurifier()->purify($_POST['key']),
        ));
        if($signLink->envelopeId != $_POST['envelopeId']){
            throw new CHttpException(400, "Invalid");
        }

        $signDocId = $_POST['signDocId'];
        $envelopeId = $_POST['envelopeId'];
        $modelId = $_POST['modelId'];
        $modelType = Fields::getPurifier()->purify($_POST['modelType']);
        $envelope = X2SignEnvelopes::model()->findByPk($envelopeId);
        $signDocIds = json_decode($envelope->signDocIds);

        // Cancel the envelope
        if($_POST['cancel']) {
            $envelope->setAttributes(array(
                'status' => X2SignEnvelopes::CANCELLED,
            ), false);

            $envelope->save();
            echo "canceled";
            return;
        }

        // Find the sign link that was just used to sign or view the document
        $link = X2SignLinks::model()->findByAttributes(array(
            'modelType' => $modelType,
            'modelId' => $modelId,
            'envelopeId' => $envelope->id,
        ));

        if($_POST['signReq'] == 1) {
            $fields = $_POST['fields'] ?? array();

            // Make sure the person who is signing has a signature
            $signature = X2Signature::model()->findByAttributes(array(
                'modelId' => $modelId,
                'modelType' => $modelType
            ));
            if(is_null($signature))
                throw new Exception('No signature found.');

            $signDoc = X2SignDocs::model()->findByPk($signDocId);
            $fieldInfo = json_decode($signDoc->fieldInfo);
            if (!isset($fieldInfo) || empty($fieldInfo)) $fieldInfo = array(); // If no fields were added to document, default to empty array

            // Save the field inputs into sign fields
            $i = 0;
            $model = $modelType::model()->findByPk($modelId);
            $attributes = array();
            foreach($fields as $field) {
                // Sanitize POST variable data
                $field['type'] = preg_replace("/[^A-Za-z]/", "", $field['type']);
                $field['fieldId'] = Fields::getPurifier()->purify($field['fieldId']);
                $field['value'] = Fields::getPurifier()->purify($field['value']);

                // If we find an insertable attribute pattern, attempt to update the
                // attribute on the model that signed the document
                //if(!isset($_POST['saveTime']) && isset($fieldInfo[$i]->value) && preg_match('/^{(.*)}$/', $fieldInfo[$i]->value, $matches)) {
                //    $attr = $matches[1];
                //    if($model->hasAttribute($attr))
                //        $attributes[$attr] = $field['value'];
               // }

                // If we find an insertable attribute pattern, attempt to update the
                // attribute on the model that signed the document
                //findSpot info to make sure fields line up
                $spotNumber = NULL;
                foreach($fieldInfo as $key => $checkField){
                    if($field['fieldId'] == $checkField->id){
                        $spotNumber = $key;
                    }
                }

                if(!isset($_POST['saveTime']) && isset($spotNumber) && isset($fieldInfo[$spotNumber]->value) && preg_match('/^{(.*)}$/', $fieldInfo[$spotNumber]->value, $matches)) {
                    $attr = $matches[1];
                    if($model->hasAttribute($attr))
                        $attributes[$attr] = $field['value'];
                }    
                $fieldOld = X2SignFields::model()->findByAttributes([
                    'envelopeId'=>$envelopeId,
                    'signDocId'=>$signDocId,
                    'fieldId'=> $field['fieldId']]);
                if(isset($fieldOld)) $X2SignField  = $fieldOld ;
                else $X2SignField = new X2SignFields;

                $X2SignField->setAttributes(array(
                    'type' => $field['type'],
                    'signDocId' => $signDocId,
                    'envelopeId' => $envelopeId,
                    'fieldId' => $field['fieldId'],
                    'value' => $field['value']
                ), false);

                //here we will check for the value of negative one for initials as some users are submiting that 
                if(($field['type'] == 'signature' || $field['type'] == 'initials' ) && ($X2SignField->value == -1 || $X2SignField->value == '-1'))
                    throw new CHttpException(500, "Signing cannot complete, issue with browser detected. Please disable any browser extensions or switch to incognito mode and try again."); 

                if(!$X2SignField->save())
                    throw new Exception('Error saving field.');

                $i++;
            }
            //check if we are just saving the fields
            if(isset($_POST['saveTime']))return;
            $model->setAttributes($attributes, false);
            if(!$model->save()){
                 $Errors = $model->getErrors();
                $onlyCur = True;
                $names = "";
                foreach($Errors as $key => $error){
                      $field = $model->getField($key);
                     if(!isset($field)) throw new CHttpException(500, "Please email your agent about '" . $error[0]);
                     if($field->type != 'currency') throw new CHttpException(500, "Please email your agent about field '" . $field->attributeLabel . "' it has problem: " . $error[0]);
                     $names .= $field->attributeLabel . ", ";
                }
                throw new CHttpException(500,  "Fields for: " . $names . " contain non-numerical characters. Anything in these fields aside from numbers (ex: $, 250k, or commas) will prevent you from completing the document. Please remove anything non-numeric and resubmit. If you wish to omit a numeric field, please enter 0. For reference, the error is: " . $error[0] );
            }
            //if(!$model->save())
            //    echo CJSON::encode($model->getErrors());

            // Create event when recipient signs
            $event = new X2SignEvents;
            $event->setAttributes(array(
                'createDate' => time(),
                'assignedTo' => $envelope->assignedTo,
                'documentId' => $signDocId,
                'envelopeId' => $envelope->id,
                'signLinkId' => $link->id,
                'IP' => Yii::app()->request->getUserHostAddress(),
                'type' => X2SignEvents::SIGNED,
            ), false);
            $event->save();
        }

            //check to make sure all fields submited
            foreach($fieldInfo as $key => $checkField){
                if($checkField->recip == $link->position){
                    $passedOn = false;
                    foreach($fields as $field){
                        if($field['fieldId'] == $checkField->id){
                           $passedOn = true;
                            //now that we have the field check the value and if their is a min max check throw an error if it does not pass
                            if(!empty($checkField->numberlimited) && $checkField->numberlimited == 1){
                                if(!is_numeric($field['value']))throw new CHttpException(500, $checkField->id . " on page " . $checkField->page . " needs to be a numeric value please correct this value.");
                                //check the value we got and if it does not pass throw an error
                                if(!empty($checkField->minvalue)|| $checkField->minvalue == 0){
                                    if(floatval($field['value']) < floatval($checkField->minvalue))
                                        throw new CHttpException(500, $checkField->id . " on page " . $checkField->page . " needs a Min value of " . $checkField->minvalue . " please correct this value.");
                                }
                                if(!empty($checkField->maxvalue)|| $checkField->maxvalue == 0){
                                    if(floatval($field['value']) > floatval($checkField->maxvalue))
                                        throw new CHttpException(500, $checkField->id . " on page " . $checkField->page . " has to be less than value of " . $checkField->maxvalue . " please correct this value.");
                                }


                            }
                       }
                    }

                    //if it has not passed on, if it is a file upload just skip it
                    if(!$passedOn){
                        $checkType = preg_replace("/[^A-Za-z]/", "", $checkField->id);
                        if($checkType == "FileUpload")$passedOn = true;
                    }

                    if(!$passedOn){
                        throw new CHttpException(500, 'Not all fields were submitted please disable any extensions you have and resubmit or reload the page');
                    }
                }
            }

            //try to update old saved fields
            //foreach($OldFields as $old){
            //    if(!$old->save()){
            //        foreach( $X2SignField as $xField){
            //            $xField->delete();

             //       }
             //       throw new CHttpException(500, 'Error saving field.\n' . CJSON::encode($old));
             //   }
            //}


        // If recipient signed last document in the envelope,
        // check if the entire envelope was signed by everyone
        // necessary; and complete it if so
        if($signDocId == end($signDocIds)) {
            if(is_null($link->signedDate)) {
                $link->signedDate = time();
                $link->save();

                $event = new X2SignEvents;
                $event->setAttributes(array(
                    'createDate' => time(),
                    'assignedTo' => $envelope->assignedTo,
                    'documentId' => $signDocId,
                    'envelopeId' => $envelope->id,
                    'signLinkId' => $link->id,
                    'IP' => Yii::app()->request->getUserHostAddress(),
                    'type' => X2SignEvents::SIGNED,
                ), false);
                $event->save();
            }

            $signLinks = X2SignLinks::model()->findAllByAttributes(array(
                'envelopeId' => $envelope->id,
                'signRequired' => 1,
            ), 'signedDate IS NULL');


            //check if it is an auto sign link and if so check if the related employe record allowas auto sign
            if(!empty($signLinks)){
                if($signLinks[0]->autoSign){
                    //assume the assigned to is the same
                    $employee = Employees::model()->findByAttributes(array('c_user__c' => $envelope->assignedTo));
                    if($employee->c_AutoSignNDA)$envelope->autoSignForUser($signLinks[0]->position);
                    $signLinks = X2SignLinks::model()->findAllByAttributes(array(
                        'envelopeId' => $envelope->id,
                        'signRequired' => 1,
                        ), 'signedDate IS NULL');


                }


            }



            // If there are no sign links with null signed dates, then
            // we have completed the envelope
            if(empty($signLinks)) {
                $envelope->status = X2SignEnvelopes::COMPLETED;
                $envelope->completeDate = time();
                if($envelope->save()) {
                    $event = new X2SignEvents;
                    $event->setAttributes(array(
                        'createDate' => time(),
                        'assignedTo' => $envelope->assignedTo,
                        'documentId' => $signDocId,
                        'envelopeId' => $envelope->id,
                        'signLinkId' => $link->id,
                        'IP' => Yii::app()->request->getUserHostAddress(),
                        'type' => X2SignEvents::COMPLETED,
                    ), false);
                    $event->save();
                    //record the completed Media Id <justin@x2engine.com>
                    $mediaId = $envelope->generateCompletePdf();
                    $envelope->completedDoc = $mediaId;
                    $envelope->save();

                    // Send out the completed PDF to recipients of the envelope
                    $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
                    $behavior->sendCompletePdf($envelope);
                    echo "complete";
                    //$this->redirect(array('signingComplete', 'completePdfId' => $mediaId));
                    Yii::app()->end();
                }
            } else{
                if ($envelope->sequential) {
                    $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
                    $behavior->resendEnvelope($envelope);
                }
                if ($modelType == 'User') {
                    $envelope->status = $envelope::WAITING_FOR_OTHERS;
                    $envelope->saveAttributes(array('status'));
                }

            }
            echo "complete";
            Yii::app()->end();
        } else {
            // Signee is required to sign some more documents
            $current = array_search($signDocId, $signDocIds);

            $signDoc = X2SignDocs::model()->findByPk($signDocIds[++$current]);
            $fields = ($_POST['signReq'] == 1) ? json_decode($signDoc->fieldInfo) : array();
            $model = X2Model::model($modelType)->findByPk($modelId);

            // Find information of other recipients to show others previously input data
            $signedFields = X2SignFields::model()->findAllByAttributes(array(
                'envelopeId' => $envelope->id,
                'signDocId' => $signDoc->id,
            ));
            $signeeFields = array();
            if(isset($model))
                if (!isset($fields) || empty($fields)) $fields = array(); // Assuming no fields were added to document
                foreach($fields as $field) {
                    if($field->recip == $link->position) { // Field is intended for recipient
                        if(isset($field->value))
                            $field->value = Docs::replaceVariables($field->value, $model);
                    } else { // This field is another recipient's, but we want to show it to other recipients so they can see previous inputs
                        $field->{'read-only'} = 1;
                        foreach($signedFields as $signedField) {
                            if(($field->id == $signedField->fieldId) && !empty($signedField->value) && isset($signedField->value)) {
                                if(strpos($field->id, "Signature") !== false) {
                                    $recipSignature = X2Signature::model()->findByPk($signedField->value);
                                    $field->value = $recipSignature->getSignatureImage();
                                } else {
                                    $field->value = $signedField->value;
                                }
                            }
                        }
                    }
                    $signeeFields[] = $field;
                }
            //this is a check if last docuemnt and if so on front end we will change Next Document button text
            if($signDocIds[$current] == end($signDocIds))$lastDoc = true;
            else $lastDoc = false;
            echo json_encode(array(
                'signDocId' => $signDoc->id,
                'model' => $model,
                'modelType' => $modelType,
                'mediaId' => $signDoc->mediaId,
                'fields' => json_encode($signeeFields),
                'lastDoc' => $lastDoc,
            ));
        }

        return;

    }

    public function actionRegenSig() {
         $signature = X2Signature::model()->findByAttributes(array(
            'modelId' => 100723,
            'modelType' => 'Sellers2',
            'initial' => 1,
        ));
        $signature->regenSigImage();
    }
    
    public function actionCreateSignature() {

       $model = (!is_null($_POST['modelType']) && !is_null($_POST['modelId']))
            ? X2Model::model($_POST['modelType'])->findByPk($_POST['modelId']) : NULL;

        $signature = X2Signature::model()->findByAttributes(array(
            'modelType' => $_POST['modelType'],
            'modelId' => $_POST['modelId'],
            'initial' => 0,
        ));

        if(!isset($signature)) {
            // Signature image creation
            $signature = new X2Signature;
            $signature->createImage($_POST['signature'], $_POST['modelType'], $_POST['modelId'], 0, $_POST['font']); //create image with input name
            $signature->setAttributes(array(
                'modelId' => !is_null($model) ? $model->id : NULL,
                'modelType' => $_POST['modelType'] ?? NULL,
                'signature' => $_POST['signature'],
                'emailAddress' => !is_null($model) ? $model->email : NULL,
                'IP' => Yii::app()->request->getUserHostAddress(),
            ), false);

            if(!$signature->save())
                throw new Exception('Could not save signature.');
        }

        $initials = X2Signature::model()->findByAttributes(array(
            'modelType' => $_POST['modelType'],
            'modelId' => $_POST['modelId'],
            'initial' => 1,
        ));

        if(!isset($initials)) {
            //Initials image creation
            $initials = new X2Signature;
            $fullName = explode(" ", $_POST['signature']);
            $initial = '';
            foreach($fullName as $partName){
                if(!empty($partName[0]))
                    $initial .= ucfirst($partName[0]);
            }
            //for cases of strange names
            if(empty($initial) || !ctype_alpha($initial))$initial =  $_POST['signature']; 
            $initials->createImage($initial, $_POST['modelType'], $_POST['modelId'], 1, $_POST['font']); //create image with input name
            $initials->setAttributes(array(
                'modelId' => !is_null($model) ? $model->id : NULL,
                'modelType' => $_POST['modelType'] ?? NULL,
                'signature' => $initial,
                'emailAddress' => !is_null($model) ? $model->email : NULL,
                'IP' => Yii::app()->request->getUserHostAddress(),
                'initial' => 1,
            ), false);

            if(!$initials->save())
                throw new Exception('Could not save signature.');
        }

        /*if(!$signature->save() || !$initials->save())
            throw new Exception('Could not save signature.');
        else*/
            echo json_encode(array($signature->id, $signature->getSignatureImage(), $initials->id, $initials->getSignatureImage()));

    }

    public function actionSigningComplete() {
        $this->layout = '//layouts/empty'; //don't use column3
        $this->render('signingComplete');
    }

    public function actionSigningCancelled() {
        $this->layout = '//layouts/empty'; //don't use column3
        $this->render('signingCancelled');
    }

    public function actionCertificate($id) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        $html = $envelope->generateCertificateHtml();
        $behavior = MPDFBehavior::createInstance();
        $pdf = $behavior->newPdf();
        $pdf->WriteHTML($html);
        $pdf->Output();
    }

    public function actionQuickResend($id = NULL) {
        if (isset($_POST['id'])) $id = $_POST['id'];
        $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        $fails = $behavior->resendEnvelope($envelope);
        if (empty($fails)) {
            echo 'Success!';
        } else {
            echo "Could not send to: " . json_encode($fails);
        }
    }

    public function actionResend() {
        if (!Yii::app()->request->isPostRequest) return;
       
        $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
        $envelope = X2SignEnvelopes::model()->findByPk((int) $_POST['modelId']);

        // Check the email if the user changed the subject or body and alter the
        // envelope and rhen resend it
        $email = json_decode($envelope->email);
        if (!isset($email)) {
            $envelope->emailSubject = $_POST['emailSubject'];
            $envelope->emailBody = $_POST['emailBody'];
            $envelope->save();
             if($envelope->id == 568651) printR($envelope,1);
        } else if ($_POST['emailSubject'] !== $email->subject || $_POST['emailBody'] !== $email->body) {
            $email->subject = $_POST['emailSubject'];
            $email->body = $_POST['emailBody'];
            $envelope->email = json_encode($email);
            $envelope->emailSubject = $_POST['emailSubject'];
            $envelope->emailBody = $_POST['emailBody'];
            $envelope->save();
        }
 
        $fails = $behavior->resendEnvelope($envelope);
        if (empty($fails)) {
            echo 'Success!';
        } else {
            echo "Could not send to: " . json_encode($fails);
        }
    }

     /**
     * Cancel the envelope and load the docs to a new envelope
     * @param integer $id the ID of the model to be displayed
     */
    public function actionEditAndSend($id) {
        $envelope = X2SignEnvelopes::model()->findByPk((int)$id);
        $envelope->setAttributes(array(
                'status' => X2SignEnvelopes::CANCELLED,
            ), false);

        $envelope->save();
        $user = User::getMe();
        $DocIds = json_decode($envelope->signDocIds);
        $newSignDoc = array();
        foreach($DocIds as $Did){
            $Sdoc = X2SignDocs::model()->findByPk($Did);
            $Ddoc =  Docs::model()->findByPk($Sdoc->docId);

            $nDoc = new Docs;
            $nSdoc = new X2SignDocs;

            $nDoc->name = $Ddoc->name;
            $nDoc->type = $Ddoc->type;
            $nDoc->associationType = $Ddoc->associationType;
            $nDoc->createdBy = $Ddoc->createdBy;
            $nDoc->updatedBy = $Ddoc->updatedBy;
            $nDoc->visibility = $Ddoc->visibility;
            $nDoc->text = $Ddoc->text;
            $nDoc->save();
            $nSdoc->name = $Sdoc->name;
            $nSdoc->docId = $nDoc->id;
            $nSdoc->mediaId = $Sdoc->mediaId;
            $nSdoc->assignedTo = $Sdoc->assignedTo;
            $nSdoc->recipients = $Sdoc->recipients;
            $nSdoc->fieldInfo = $Sdoc->fieldInfo;
            $nSdoc->signeesNeeded = $Sdoc->signeesNeeded;
            $nSdoc->visibility = $Sdoc->visibility;
            $nSdoc->save();
            $newSignDoc[] = $nSdoc->id;

        }
        $envelope2 = new X2SignEnvelopes;
        $envelope2->assignedTo = $user->username;
        $envelope2->signDocIds = json_encode($newSignDoc);
        $envelope2->sender = $envelope->sender;



        $envelope2->name = date('M d, Y: ') . ' Quick Send Doc';
        $envelope2->save();

        $recipients = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $envelope->id));
        //copy the links
        foreach($recipients as $recip){
                    $signLink = new X2SignLinks;
                    $signLink->setAttributes(array(
                        'modelType' => $recip->modelType ,
                        'envelopeId' => $envelope2->id,
                        'position' => $recip->position,
                        'modelId' => $recip->modelId,
                        'createDate' => time(),
                        'key' => $signLink->getKey(),
                        'emailAddress' => $recip->emailAddress,
                        'signRequired' => $recip->signRequired
                    ), false);
                    $signLink->save();


        }

        $this->redirect(Yii::app()->getBaseUrl() . '/index.php/x2sign/quickSetupRecipients/id/'. $envelope2->id);


    }

    public function actionCloneAndSend($id) {

        $envelope = X2SignEnvelopes::model()->findByPk((int)$id);
        $user = User::getMe();
        $DocIds = json_decode($envelope->signDocIds);
        $newSignDoc = array();
        foreach($DocIds as $Did){
            $Sdoc = X2SignDocs::model()->findByPk($Did);
            $Ddoc =  Docs::model()->findByPk($Sdoc->docId);

            $nDoc = new Docs;
            $nSdoc = new X2SignDocs;

            $nDoc->name = $Ddoc->name;
            $nDoc->type = $Ddoc->type;
            $nDoc->associationType = $Ddoc->associationType;
            $nDoc->createdBy = $Ddoc->createdBy;
            $nDoc->updatedBy = $Ddoc->updatedBy;
            $nDoc->visibility = $Ddoc->visibility;
            $nDoc->text = $Ddoc->text;
            $nDoc->save();
            $nSdoc->name = $Sdoc->name;
            $nSdoc->docId = $nDoc->id;
            $nSdoc->mediaId = $Sdoc->mediaId;
            $nSdoc->assignedTo = $Sdoc->assignedTo;
            $nSdoc->recipients = $Sdoc->recipients;
            $nSdoc->fieldInfo = $Sdoc->fieldInfo;
            $nSdoc->signeesNeeded = $Sdoc->signeesNeeded;
            $nSdoc->visibility = $Sdoc->visibility;
            $nSdoc->save();
            $newSignDoc[] = $nSdoc->id;

        }
        $envelope2 = new X2SignEnvelopes;
        $envelope2->assignedTo = $user->username;
        $envelope2->signDocIds = json_encode($newSignDoc);
        $envelope2->sender = $envelope->sender;


        $envelope2->name = date('M d, Y: ') . ' Quick Send Doc';
        $envelope2->save();

        $recipients = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $envelope->id));

        //copy the links
        foreach($recipients as $recip){
            $signLink = new X2SignLinks;
            $signLink->setAttributes(array(
                'modelType' => $recip->modelType ,
                'envelopeId' => $envelope2->id,
                'position' => $recip->position,
                'modelId' => $recip->modelId,
                'createDate' => time(),
                'key' => $signLink->getKey(),
                'emailAddress' => $recip->emailAddress,
                'signRequired' => $recip->signRequired
            ), false);
            $signLink->save();
        }

        $this->redirect(Yii::app()->getBaseUrl() . '/index.php/x2sign/quickSetupRecipients/id/'. $envelope2->id);
    }

    public function actionEditDocs($id) {
        //$this->layout = '//layouts/properties';
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        $docIds = json_decode($envelope->signDocIds);
        $signDocs = array();
        foreach($docIds as $docId) {
            $signDoc = X2SignDocs::model()->findByPk($docId);
            $signDocs[$signDoc->name] = array('id' => $signDoc->id, 'mediaId' => $signDoc->mediaId);
        }

        $this->render('editDocs', array(
            'envelope' => $envelope,
            'user' => User::getMe(),
            'signDocs' => json_encode($signDocs),
        ));
    }

    public function actionUpdateDocOrder($id) {
        $envelope = X2SignEnvelopes::model()->findByPk((int)$id);
        $envelope->signDocIds = json_encode($_POST['docs']);
        $envelope->save();

        echo "all good";
    }

    public function actionCreateEnvelopeWithTemp($id){
        $user = User::getMe();
        $DocIds = array($id);
        $newSignDoc = array();
        foreach($DocIds as $Did){
            $Sdoc = X2SignDocs::model()->findByAttributes(array("docId" => $Did));
            $Ddoc =  Docs::model()->findByPk($Sdoc->docId);

            $nDoc = new Docs;
            $nSdoc = new X2SignDocs;

            $nDoc->name = $Ddoc->name;
            $nDoc->type = $Ddoc->type;
            $nDoc->associationType = $Ddoc->associationType;
            $nDoc->createdBy = $Ddoc->createdBy;
            $nDoc->updatedBy = $Ddoc->updatedBy;
            $nDoc->visibility = $Ddoc->visibility;
            $nDoc->text = $Ddoc->text;
            $nDoc->save();
            $nSdoc->name = $Sdoc->name;
            $nSdoc->docId = $nDoc->id;
            $nSdoc->mediaId = $Sdoc->mediaId;
            $nSdoc->assignedTo = $Sdoc->assignedTo;
            $nSdoc->recipients = $Sdoc->recipients;
            $nSdoc->fieldInfo = $Sdoc->fieldInfo;
            $nSdoc->signeesNeeded = $Sdoc->signeesNeeded;
            $nSdoc->visibility = $Sdoc->visibility;
            $nSdoc->save();
            $newSignDoc[] = $nSdoc->id;

        }


        $envelope2 = new X2SignEnvelopes;
        $envelope2->assignedTo = $user->username;
        $envelope2->signDocIds = json_encode($newSignDoc);

        $envelope2->name = date('M d, Y: ') . ' Quick Send Doc';
        $envelope2->save();
        $this->redirect(Yii::app()->getBaseUrl() . '/index.php/x2sign/quickSetupRecipients/id/'. $envelope2->id);



    }

    public function actionFinishLater($id, $recipients = null) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        $envelope->status = X2SignEnvelopes::FINISH_LATER;
        $envelope->save();

        if(isset($_POST['recipients'])) {
            $recips = $_POST['recipients'];
            //we will unset the ones that have no email
            foreach($recips as $key => $recp){
                if(empty($recp['email']))unset($recips[$key]);
            }
            $this->saveRecipients($recips, $envelope->id);
        }

        return;
    }

    /**
     * @param array $recipients The recipients of the envelope
     */
    private function saveRecipients($recipients, $envelopeId) {
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
                        if($recipient['hiddenModel'] == 'Self')
                            $contact = User::getMe();
                        else
                            $contact = $recipient['hiddenModel']::model()->findByPk($recipient['hiddenId']);
                    } else {
                        switch($recipient['displayModel']) {
                            case 'Contacts':
                                $contact = X2Leads::model()->findByAttributes(array('c_email' => $recipient['email']));
                                if(isset($contact) && $contact->assignedTo != User::getMe()->username)
                                    unset($contact);
                                break;
                            case 'Sellers':
                                $contact = Sellers2::model()->findByAttributes(array('c_email' => $recipient['email']));
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
                                    'c_email' => $recipient['email'],
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

                                $contact->setAttributes(array(
                                    'leadSource' => 'Quick Send',
                                    'assignedTo' => User::getMe()->username,
                                    'firstName' => $recipient['firstName'],
                                    'lastName' => $recipient['lastName'],
                                    'email' => $recipient['email'],
                                    'visibility' => 0,
                                ), false);

                            }

                            $contact->save();
                        }
                    }

                    $signLink = new X2SignLinks;
                    $signLink->setAttributes(array(
                        'signRequired' => $recipient['viewer'] == 'true' ? 0 : 1,
                        'modelType' => $recipient['hiddenModel'] == 'Self' ? 'User' : get_class($contact),
                        'envelopeId' => $envelopeId,
                        'position' => $_POST['userOrder'] ? $recipient['order'] : $position,
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

    public function actionRegen($id) {
        //if (!Yii::app()->request->isPostRequest) return;
        $envelope = X2SignEnvelopes::model()->findByPk((int)$id);
        $Docid = $envelope->generateCompletePdf();

        if(!isset($envelope->completedDoc)){
            $envelope->completedDoc = $Docid;
            $envelope->save();
        }

        printR($Docid, 1);
    }

    // Quick Send actions
    public function actionQuickSend($id =null) {
        //$this->layout = '//layouts/properties';
        if(!isset($id)){
            $user = User::getMe();
            $envelope = new X2SignEnvelopes;
            $envelope->prepared = 0;
            $envelope->sequential = 1;
            $envelope->assignedTo = $user->username;
            $envelope->signDocIds = json_encode(array());

            $email = Credentials::model()->findDefault($user->id, 'email', TRUE);
            if(empty($email))
                throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
            else
                $email = $email->auth->email;
            $envelope->sender = $email;
            $envelope->name = date('M d, Y: ') . ' Quick Send Doc';
            $profile = Profile::model()->findByAttributes(array('username' => User::getMe()->username));
            $envelope->emailBody = $profile->signature;
            $envelope->emailSubject = '';
            $envelope->save();
        }
        else{
            $envelope = X2SignEnvelopes::model()->findByPk($id);
        }

        // Getting Data for Recipients
        $recepientsData=$this->HelperQuickSetupRecipients($envelope->id);
        // the email templates
        $emailData=$this->HelperQuickEmailView($envelope->id);
        // Users for assigning envelope
        $users=Yii::app()->db->createCommand()
            ->select('username')
            ->from('x2_users')
            ->queryAll();
        $users[] = ['username'=>'-----------'];
        $groups=Yii::app()->db->createCommand()
            ->select('id, name AS username')
            ->from('x2_groups')
            ->queryAll();
        $users = array_merge($users, $groups);

        $viewData=array();
        $viewData['envelope']=$envelope;
        $viewData['contactRoles'] = Dropdowns::model()->findByPk(1027);
        $viewData['users'] = $users;

        foreach($recepientsData as $key=>$value){
            $viewData[$key]=$value;
        }
        foreach($emailData as $key=>$value){
            $viewData[$key]=$value;
        }

        $this->render('quickSend', $viewData);
    }

    public function actionAddDocs($id) {
        //$this->layout = '//layouts/properties';
        $envelope = X2SignEnvelopes::model()->findByPk($id);

        // Load default user sign doc templates, if any
        $criteria = new CDbCriteria;
        $criteria->addCondition("assignedTo = '" . User::getMe()->username . "'");
        $criteria->addCondition('template = 1');
        $X2SignDocs = X2SignDocs::model()->search(null, $criteria)->getData();
        $templates = array();
        foreach($X2SignDocs as $X2SignDoc) {
            $templates[] = array(
                'modelId' => $X2SignDoc->id,
                'viewName' => $X2SignDoc->name . " - " . date('r', $X2SignDoc->createDate) ,
                'viewText' => $X2SignDoc->name,
            );
        }

        $this->render('quickSend', array(
            'templates' => $templates,
            'envelope' => $envelope,
        ));
    }

    public function actionQuickSetupRecipients($id) {
        //$this->layout = '//layouts/properties';
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        if ($envelope->status == 5) $envelope->status = null;
        //check to see if we have any jpg 

        $signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $id));
        $recipients = array();
        foreach($signLinks as $signLink) {
            $contact = X2Model::model($signLink->modelType)->findByPk($signLink->modelId);
            if($signLink->modelType == "Contacts"){
                $recipients[] = array(
                    'displayModel' => $signLink->modelType,
                    'hiddenModel' => $signLink->modelType,
                    'hiddenId' => $signLink->modelId,
                    //making these blank so not hard code the recipents
                    //'hiddenModel' => '',
                    //'hiddenId' => '',
                    'firstName' => $contact->firstName,
                    'lastName' => $contact->lastName,

                    'email' => $signLink->emailAddress,
                     'spot' => $signLink->position,

                );
            }else{
                $name = explode(" ", $contact->name);
                $recipients[] = array(
                    'displayModel' => $signLink->modelType,
                    'hiddenModel' => $signLink->modelType,
                    'hiddenId' => $signLink->modelId,
                    //making these blank so not hard code the recipents
                    //'hiddenModel' => '',
                    //'hiddenId' => '',
                    'firstName' => $name[0],
                    'lastName' => $name[count($name) - 1],
                    'email' => $signLink->emailAddress,
                    'spot' => $signLink->position,

                );


            }
        }
        if ($envelope->prepared == 1)
            throw new CHttpException(400, "Bad Request");

        $this->render('quickSetupRecipients', array(
            'envelope' => $envelope,
            'user' => User::getMe(),
            'recipients' => json_encode($recipients),
        ));
    }

    public function actionQuickSetupTemplate($id, $signDocNum = 0) {
        //$this->layout = '//layouts/properties';
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        if (isset($_POST['userOrder'])) {
            if ($_POST['userOrder'] == 'false') {
                $envelope->sequential = 0;
            } else {
                $envelope->sequential = 1;
            }
        }

        if(isset($_POST['docs'])){
            $envelope->signDocIds = json_encode($_POST['docs']);
        }

        //adding code to merge in old templates 
        if(!empty($_POST["oldTemps"])){

            $oldids = json_decode($envelope->signDocIds);
            //if not an array then make it an array
            if(!is_array($oldids)) $oldids = array();

            $newSignDoc = array();
            foreach($_POST["oldTemps"] as $Did){
                $Sdoc = X2SignDocs::model()->findByPk($Did);
                $Ddoc =  Docs::model()->findByPk($Sdoc->docId);
                if(!isset($Sdoc) || !isset($Ddoc)) continue;
                $nDoc = new Docs;
                $nSdoc = new X2SignDocs;

                $nDoc->name = $Ddoc->name;
                $nDoc->type = $Ddoc->type;
                $nDoc->associationType = $Ddoc->associationType;
                $nDoc->createdBy = time();
                $nDoc->updatedBy = $Ddoc->updatedBy;
                $nDoc->visibility = $Ddoc->visibility;
                $nDoc->text = $Ddoc->text;
                $nDoc->save();
                $nSdoc->name = $Sdoc->name;
                $nSdoc->docId = $nDoc->id;
                $nSdoc->mediaId = $Sdoc->mediaId;
                $nSdoc->assignedTo = $Sdoc->assignedTo;
                $nSdoc->recipients = $Sdoc->recipients;
                $nSdoc->fieldInfo = $Sdoc->fieldInfo;
                $nSdoc->signeesNeeded = $Sdoc->signeesNeeded;
                $nSdoc->visibility = $Sdoc->visibility;
                $nSdoc->save();
                $newSignDoc[] = $nSdoc->id;

            }

            $bothIDS = array_merge($oldids, $newSignDoc);
            $envelope->signDocIds = json_encode($bothIDS);        

        }


        if (isset($_POST['private'])) {
            if ($_POST['private'] == 'false') {
                $envelope->c_private = 0;
            } else {
                $envelope->c_private = 1;
            }
        }


        if (isset($_POST['subject']) && isset($_POST['message'])) {
            if($_POST['subject'] != "" && $_POST['subject'] != null){
                //$envelope->name = $_POST['subject'];
            }
            $envelope->emailSubject = $_POST['subject'];
            $envelope->emailBody = $_POST['message'];
            $delay = 0;
            if(isset( $_POST['delay']) && !empty($_POST['delay'])){
                    $delay = $_POST['delay'];
                    $envelope->sendDate = strtotime($delay);
                    $envelope->sent = 0;
                    $envelope->save();

            }else{
                //$envelope->sendDate = time();
                $envelope->save();
            }
            //set expire if set
            if(!empty($_POST['expireDate'])){
                $envelope->expireDate = strtotime($_POST['expireDate']);
            }
        }

        $envelope->save();

        if (isset($_POST['recipients'])) {
           $this->saveRecipients($_POST['recipients'], $envelope->id);
           //here we will check to see if the same order spot is used twice
           //only check if we are using order
           if($_POST['userOrder']){
                $places =array();
                foreach($_POST['recipients'] as $rcp){
                    if(isset($places[$rcp['order']]) && $places[$rcp['order']] == 'set') throw new CHttpException(400, $rcp['order'] . " used twice for order");
                    $places[$rcp['order']] = 'set';
                }
            }
        } else {
            if (!is_null($envelope)) {
                if (count(json_decode($envelope->signDocIds)) > 1 || (isset($envelope->prevSignDocIds) && count(json_decode($envelope->prevSignDocIds)) > 1)) {
                    // Combine PDFs into single PDF
                    $envelope->generateCombinedPdf();
                    //$envelope->refresh();
                }
                $contacts = array();
                //$signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $envelope->id));
                $signLinks = $envelope->getX2SignLinks(array("signRequired" => 1));
                $lastDoc = false;
                $i = 0;
                //this array will be all the spots that are viewers so we will not allow then to have fields
                $viewerSpots = array();
                foreach($signLinks as $signLink) {
                    $contact = $signLink->modelType::model()->findByPk($signLink->modelId);
                    $contacts[$i] =  array($signLink->emailAddress, $contact->name, $signLink->modelType);
                    if($signLink->signRequired == 0) $viewerSpots[] = $i + 1;
                    $i++;
                }

                if ($signDocNum + 1 >= count(json_decode($envelope->signDocIds)))
                    $lastDoc = true;

                $signDoc = X2SignDocs::model()->findByPk(json_decode($envelope->signDocIds)[$signDocNum]);
                $doc = Docs::model()->findByPk($signDoc->docId);
                $pdf = Media::model()->findByAttributes(array('id' => $signDoc->mediaId));
                $modelsArr = array("Contacts","Sellers2","X2Leads","User");
                $AttrsArr = array();
                foreach($modelsArr as $modelName){
                       $AttrsArr[$modelName] = X2Model::model($modelName)
                        ->getFieldsForDropdown(true, false);
                }

                $this->render('quickSetupTemplate', array(
                    'envelope' => $envelope,
                    'model' => $doc,
                    'signDoc' => $signDoc,
                    'pdf' => $pdf,
                    'pdfName' => $pdf->name,
                    'contacts' => $contacts,
                    'signDocNum' => $signDocNum + 1,
                    'lastDoc' => $lastDoc,
                    'AttysArr' => $AttrsArr,
                    'viewerSpots' => json_encode($viewerSpots),
                ));
            } else {
                throw new CHttpException(400, "Bad Request");
            }
        }
    }

    public function actionGetEnvelopeSignDoc($id) {
        $mediaIds=array();
        $envelope = X2SignEnvelopes::model()->findByPk((int)$id);

        foreach(json_decode($envelope->signDocIds) as $signDocId){
            $x2signDoc=X2SignDocs::model()->findByAttributes(['id'=>$signDocId]);
            $media=Media::model()->findByAttributes(['id'=>$x2signDoc->mediaId]);
            $mediaIds[]=array('id'=>$media->id,'size'=>$media->filesize,'name'=>$media->name,'fileName'=>$media->fileName, 'signDocId'=>$x2signDoc->id, 'docId'=>$x2signDoc->docId);
        }
        echo json_encode($mediaIds);

    }

    function actionReorderDelete($id){
        $success=0;
        $x2signDoc=X2SignDocs::model()->findByAttributes(['id'=>$id]);
        $media = Media::model()->findByPk($x2signDoc->mediaId);
        $envelope = X2SignEnvelopes::model()->findByPk($media->associationId);
        $docSignId=json_decode($envelope->signDocIds);
        $updatedDocId=array();
        foreach($docSignId as $docId){
            if($docId != $x2signDoc->id){
                $updatedDocId[]=$docId;
            }
        }
        $envelope->signDocIds=json_encode($updatedDocId);
        $envelope->save();
        if(X2SignDocs::model()->deleteAllByAttributes(['id'=>$id])){
           $success= Media::model()->deleteByPk ($x2signDoc->mediaId);
        }
        echo $success;
    }

    public function actionUpdateDocSignOrder($id) {
        $docIds=array();
        foreach($_POST['docs'] as $mediaId){
            $x2signDoc=X2SignDocs::model()->findByAttributes(['mediaId'=>$mediaId]);
            $docIds[]=$x2signDoc->id;
        }
        $envelope = X2SignEnvelopes::model()->findByPk((int)$id);
        $envelope->signDocIds = json_encode($docIds);
        if($envelope->save()){
            echo(json_encode(array('success' => 'Document Order Updated Successfully')));
        }else{
            echo(json_encode(array('error' => 'An Error Occured While Document Order')));
        }

    }




    /* public function actionQuickSetupEmail($id, $signDocNum = 0) {
        //$this->layout = '//layouts/properties';
        $envelope = X2SignEnvelopes::model()->findByPk($id);
                    $recipients = array();
                    if(isset($_POST['fieldInfo'])){
                        foreach($_POST['fieldInfo'] as $field) {
                            if(!in_array($field['recip'], $recipients))
                                $recipients[] = $field['recip'];
                        }
                        sort($recipients);
                        $temp = 0;
                        if($_POST['template'] == "true"){
                            $temp = 1;
                        }

                        $signDoc = X2SignDocs::model()->findByPk($_POST['signDocId']);
                        $signDoc->setAttributes(array(
                            'recipients' => json_encode($recipients),
                            'fieldInfo' => json_encode($_POST['fieldInfo']),
                            'template' => $temp,
                            'assignedTo' => User::getMe()->username,
                            'name' => $_POST['name'],
                        ), false);

                        $signDoc->save();
                    }
                    $signIds = json_decode($envelope->signDocIds);
                    if((count($signIds) > $signDocNum)){
                        if($signDocNum < 0) $signDocNum = 0;
                        echo Yii::app()->absoluteBaseUrl."/index.php/x2sign/quickSetupTemplate/?id=" . $id . "&signDocNum=".($signDocNum);
                    }else{
                        echo Yii::app()->absoluteBaseUrl. "/index.php/x2sign/quickEmailView/id/" . $id;
                    }

    } */

    public function actionQuickSetupEmail($id, $signDocNum = 0) {
        //$this->layout = '//layouts/properties';
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        $recipients = array();
        if(isset($_POST['fieldInfo'])) {
            foreach($_POST['fieldInfo'] as $field) {
                if(!in_array($field['recip'], $recipients))
                    $recipients[] = $field['recip'];
            }
            sort($recipients);
            $signDoc = X2SignDocs::model()->findByPk($_POST['signDocId']);
            $temp = 0;
            if (isset($_POST['template']) && $_POST['template'] == "true"){
                $temp = 1;
            }
            $signDoc->setAttributes(array(
                'recipients' => json_encode($recipients),
                'fieldInfo' => json_encode($_POST['fieldInfo']),
                'template' => $temp,
                'visibility' => $temp,
            ), false);

            $signDoc->save();
        }
        $signIds = json_decode($envelope->signDocIds);
        if(count($signIds) > $signDocNum) {
            if($signDocNum < 0){
                echo Yii::app()->absoluteBaseUrl. "/index.php/x2sign/quicksend/id/" . $id;
                return;
            }
            echo Yii::app()->absoluteBaseUrl."/index.php/x2sign/quickSetupTemplate/?id=" . $id . "&signDocNum=".($signDocNum);
        } else {
            echo "true";
        }
    }

    public function actionQuickEmailView($id) {
        //$this->layout = '//layouts/properties';
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
        if (isset($envelope))
            $this->render('quickSetupEmail', array(
                'envelope' => $envelope,
                'template' => $templateList,
                'recipients' => $emailAddresses,
                'signDocNames' => $signDocNames,
            ));
        else
            throw new CHttpException(400, 'Bad Request');
    }

    /* public function actionQuickSendFinish($id) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        if (isset($envelope)) {
            if (isset($_POST['subject']) && isset($_POST['message'])) {
                $subject = $_POST['subject'];
                $message = $_POST['message'];
                $delay = 0;
                //we do this to set the name
                $envelopeName = date('M d, Y') . ", " . $_POST['subject'];
                $signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $envelope->id));
                $i = 0;
                foreach($signLinks as $signLink) {
                    $contact = $signLink->modelType::model()->findByPk($signLink->modelId);
                    if($i == 0) $envelopeName .= "@ " . $contact->name;
                    else $envelopeName .= ", " . $contact->name;
                    $i++;
                }
                $envelope->name = $envelopeName;
                $envelope->save();



                if(isset( $_POST['delay']))$delay = $_POST['delay'];
                //set expire if set
                if(!empty($_POST['expireDate'])){
                    $envelope->expireDate = strtotime($_POST['expireDate']);
                    $envelope->save();
                }
                $fails = $envelope->quickSendEmail($subject, $message, $delay);
                if (isset($fails) && count($fails) > 0) throw new CHttpException(500, json_encode($fails));
            }
            else throw new CHttpException(400, 'Bad Request');
        } else {
            throw new CHttpException(400, 'Bad Request');
        }
    } */

    public function actionQuickSendFinish($id) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        if (isset($envelope)) {
            if (isset($envelope->hostedEnvelope) && $envelope->hostedEnvelope == 1) {
                echo 'saved';
                return;
            }
            $fails = $envelope->quickSendEmail($envelope->emailSubject, $envelope->emailBody, 0);
            if (isset($fails) && count($fails) > 0) throw new CHttpException(500, json_encode($fails));

        } else {
            throw new CHttpException(400, 'Bad Request');
        }
    }


    public function actionAjaxQuickGetNextSignDoc($id) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        if (isset($envelope) && isset($_POST['signDocId'])) {
            $currentDocId = $_POST['signDocId'];
            $signDocs = json_decode($envelope->signDocIds);
            $nextDocKey = array_search($currentDocId, $signDocs) + 1;
            $nextDocId = isset($signDocs[$nextDocKey]) ? $signDocs[$nextDocKey] : null;
            $nextDoc = X2SignDocs::model()->findByPk($nextDocId);
            if (isset($nextDoc)) {
                $doc = Docs::model()->findByPk($nextDoc->id);
                $response = array(
                    'modelId'=>$nextDoc->docId,
                    'signDocId'=>$nextDoc->id,
                    'mediaId'=>$nextDoc->mediaId,
                    'pdfName'=>$doc->name,
                    'fieldInfo'=>$nextDoc->fieldInfo,
                );
                echo json_encode($response);
            } else throw new CHttpException(400, 'Bad Request');
        } else throw new CHttpException(400, 'Bad Request');
    }

    public function actionAjaxGenSignDocs($envelopeId) {
        if (isset($envelopeId) && isset($_POST['mediaId'])) {
            $envelope = X2SignEnvelopes::model()->findByPk($envelopeId);
            $media = Media::model()->findByPk($_POST['mediaId']);

            // create assoc doc needed for signdoc
            $doc = new Docs;
            $doc->name = $media->name;
            $doc->type = 'x2signdoc';
            $doc->text = '<html><body></body></html>';
            $doc->createdBy = User::getMe()->username;
            $doc->visibility = 0;
            $doc->save();

            $signDoc = new X2SignDocs;
            $signDoc->name = $doc->name;
            $signDoc->docId = $doc->id;
            $signDoc->mediaId = $media->id;
            $signDoc->save();
        } else {
            throw new CHttpException(400, 'Bad Request');
        }
    }

    public function actionCancelQuickSetupEnvelope($id) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        if (isset($envelope)) $envelope->delete();
        else throw new CHttpException(400, 'Could not find envelope with requested id');
        $this->redirect('/index.php/x2sign/index');
    }
    //right now this is just used by JS to set the signature for BLIs
    public function actionUpdateFieldInfo() {
        $signDoc = X2SignDocs::model()->findByPk($_POST['id']);
        if(!isset($signDoc))
            throw new CHttpException(400, 'Could not find sign doc with requested id');
        $fieldInfo = json_decode($signDoc->fieldInfo)[0];
        $fieldInfo->top = $_POST['top'];
        $fieldInfo->page = $_POST['page'];
        if(count(json_decode($signDoc->fieldInfo)) == 1){
            $signDoc->setAttributes(array(
                'fieldInfo' => "[" . json_encode($fieldInfo) . "]",
            ), false);

            $signDoc->save();
        }
    }

    public function actionSaveFolder() {
        $time = time();
        $username = User::getMe()->username;
        $name = $_POST["name"];
        try {
            $folders = Yii::app()->db->createCommand()->insert('x2_sign_folders', array(
                'name' => $name,
                'createdBy' => $username,
                'createDate' => $time,
                'lastUpdated' => $time,
                'updatedBy' => $username,
                'visibility' => 0,
            ));
        } catch (CDbException $e) {
            $code = $e->getCode();
            if ($code == 23000)
               throw new CHttpException(500, "Cannot create folder. Duplicate folder name exists!");
        }
    }

    public function actionMoveFolder() {
        $envelopeId = $_POST['envelopeId'];
        $folderId = $_POST['folderId'];
        try {
            $err = Yii::app()->db->createCommand()->update('x2_sign_envelopes', array(
                'signFolderId' => $folderId,
            ), 'id = :id', array(':id' => $envelopeId));
        } catch (CDbException $e) {
            throw new CHttpException(500, "Cannot Move Folder: " . $e->getMessage());
        }
    }

    public function actionGetFolders() {
        $folders = Yii::app()->db->createCommand()
            ->select('*')
            ->from('x2_sign_folders')
            ->where('createdBy = "' . User::getMe()->username . '"')
            ->queryAll();
        echo json_encode($folders);
    }

    /**
     * create copies of the old templates and add it the curent envelope
     */

    //function to deal with editing the docs on the nda

    public function actionUpdateDocs(){
        $ID = $_POST['modelId'];
        $docsArray = $_POST['docsArray'];
        $model = Ndaconfigure::model()->findByPk($ID);
        $model->c_default_nda = json_encode($docsArray);
        $model->save();
        echo "nda update";
        Yii::app()->end();


    }

    public function actionUpdateListtingTemplate(){
        $IDvalue = $_POST['modelId'];
        $emailTempId =  $_POST['emailTemplate'];
        $list = Listings2::model()->findByPk($IDvalue);
        $list->autSendEmailTemp = $emailTempId;
        $list->save();
        echo "Email Tempate Updated";
        Yii::app()->end();

    }

    public function actionUpdateCIMListtingTemplate(){
        $IDvalue = $_POST['modelId'];
        $emailTempId =  $_POST['emailTemplate'];
        $list = Listings2::model()->findByPk($IDvalue);
        $list->cimEmailTemp = $emailTempId;
        $list->save();
        echo "Email Tempate Updated";
        Yii::app()->end();

    }


    public function actionUpdateListtingBliType(){
        $IDvalue = $_POST['modelId'];
        $bliType =  $_POST['bilPick'];
        $list = Listings2::model()->findByPk($IDvalue);
        $list->autoBliType = $bliType;
        $list->save();
        echo "Bli Updated";
        Yii::app()->end();

    }

    public function actionUpdateDocket(){
        $IDvalue = $_POST['modelId'];
        $docOrNda = $IDvalue[0];
        $IDvalue = substr($IDvalue, 2);
        $ListId = $_POST['ListId'];
        //if nda then do below if not just do it for one item
        if($docOrNda == "D"){
            $docsArray = array($IDvalue);

        }else{
            // I'll have to make a new media file entry 
            $model = Ndaconfigure::model()->findByPk($IDvalue);
            //get docs
            $docsArray = json_decode($model->c_default_nda);
        }
        foreach($docsArray as $doc){
            //have to make a clone of each document since the listings pull up related media files
            $X2signDoc = X2SignDocs::model()->findByPk($doc);
            $oldMedia = Media::model()->findByPk($X2signDoc->mediaId);
            $newMedia = new Media;
            $newMedia->associationType = "Listings2";
            $newMedia->associationId = $ListId;
            $newMedia->uploadedBy = $oldMedia->uploadedBy;
            $newMedia->fileName = $oldMedia->fileName;
            $newMedia->name = $oldMedia->name;
            $newMedia->mimetype = $oldMedia->mimetype;
            $newMedia->private = $oldMedia->private;
            $newMedia->filesize = $oldMedia->filesize;
            $newMedia->signdoc = 1;
            $newMedia->c_document_type = "NDA";
            $newMedia->save();

            // create assoc doc needed for signdoc
            $doc = new Docs;
            $oldDoc = Docs::model()->findByPk($X2signDoc->docId);
            $doc->name = $oldDoc->name;
            $doc->type = 'x2signdoc';
            $doc->text = '<html><body></body></html>';
            $doc->createdBy = $oldDoc->createdBy;
            $doc->updatedBy = $oldDoc->updatedBy;
            $doc->edition = $oldDoc->edition;
            $doc->assignedTo = $oldDoc->assignedTo;
            $doc->mediaId = $newMedia->id;
            $doc->visibility = 0;
            $doc->save();

            $signDoc = new X2SignDocs;
            $signDoc->name = $X2signDoc->name;
            $signDoc->docId = $doc->id;
            $signDoc->mediaId = $newMedia->id;
            $signDoc->recipients = $X2signDoc->recipients;
            $signDoc->fieldInfo = $X2signDoc->fieldInfo;
            $signDoc->signeesNeeded = $X2signDoc->signeesNeeded;
            $signDoc->assignedTo = $X2signDoc->assignedTo;
            $signDoc->visibility = $X2signDoc->visibility;
            $signDoc->format = $X2signDoc->format;
            $signDoc->template = $X2signDoc->template;
            $signDoc->save();




        }

        echo "nda update";
        Yii::app()->end();

    }



    public function actionSendNDA(){

        $ID = $_POST['modelId'];
        $buyer = Contacts::model()->findByAttributes(array('id' => $ID));
        //get the last user to update the buyer then their employee so I can get their NDA 
        $employee = Employees::model()->findByAttributes(array ('c_user__c' =>$buyer->assignedTo ));
        $ndaConfigure = Ndaconfigure::model ()->findByAttributes (array ('c_employee' =>$employee->nameId));
        //now if not set throw an error 
        if(empty($ndaConfigure)) throw new CHttpException(400, "No Nda has been set for the user");
        $envelope = new X2SignEnvelopes;
        $envelope->prepared = 0;
        $envelope->sequential = 1;
        $envelope->assignedTo =$user->username;
        $envelope->signDocIds = $ndaConfigure->c_default_nda;
        $envelope->c_automation_nda=0;
        // get the user then the credentials
        $user = User::getMe();
        $email = Credentials::model()->findDefault($user->id, 'email', TRUE);
        if(empty($email))
            throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
        else
            $email = $email->auth->email;

        $envelope->sender = $email;
        $envelope->name = date('M d, Y: ') . ' NDA Quick Send';
        $envelope->save();

        //make links for both the user and the buyer
        $signLink = new X2SignLinks;
        $signLink->setAttributes(array(
                'signRequired' => 1,
                'modelType' => get_class($buyer),
                'envelopeId' =>  $envelope->id,
                'position' => 1,
                'modelId' => $contact->id,
                'createDate' => time(),
                'key' => $signLink->getKey(),
                'emailAddress' => $contact->email
                    ), false);
        $signLink->save();


        $signLink = new X2SignLinks;
        $signLink->setAttributes(array(
                'signRequired' => 1,
                'modelType' => 'User',
                'envelopeId' => $envelope->id,
                'position' => 2,
                'modelId' => $user->id,
                'createDate' => time(),
                'key' => $signLink->getKey(),
                'emailAddress' => $user->email
                    ), false);
        $signLink->save();

        $subject = $ndaConfigure->c_EmailSubject;
        $body = $ndaConfigure->c_EmailBody;
        $email = json_encode([
                'subject' => $subject,
                'body' => $body,
                //'reminders' => json_decode($_POST['reminders'] ?? ''),
                ]);
        $envelope->email = $email;
        $envelope->emailBody = $body;
        $envelope->emailSubject = $subject;
        $envelope->save();
        //$fails = $envelope->quickSendEmail($subject, $body, 0);
        //if (isset($fails) && count($fails) > 0) throw new CHttpException(500, json_encode($fails));
        echo "NDA SENT";
        Yii::app()->end();


    }


    public function actionSendNDAQuick(){
        $ID = $_POST['modelId'];
        if(isset($_POST['contact'])){
            $buyer = Contacts::model()->findByPk($ID);
            $listId = Listings2::model()->findByAttributes(array ('nameId' => $buyer->c_listinglookup__c));
        }else{
            $inqury = Inquiries::model()->findByPk($ID);
            $buyer = Contacts::model()->findByAttributes(array('nameId' => $inqury->c_contact__c)); 
            $listId = Listings2::model()->findByAttributes(array ('nameId' => $inqury->c_listing__c));
        }
        //get the last user to update the buyer then their employee so I can get their NDA 
        $employee = Employees::model()->findByAttributes(array ('c_user__c' =>$buyer->assignedTo ));
        $ndaConfigure = Ndaconfigure::model ()->findByAttributes (array ('c_employee' =>$employee->nameId));
        //now if not set throw an error 
        //if(empty($ndaConfigure)) throw new CHttpException(400, "No Nda has been set for the user");
        $media = Media::model ()->findAllByAttributes (array ('associationType' => 'Listings2','associationId'=>$listId->id,//'uploadedBy'=>$buyerData['assignTo'],
                'c_document_type'=>'NDA'));
        if(empty($media))
            throw new Exception ('No NDA set up for this Listing');
        $docsArray = array();
        //needed to add code incase the media is an array
        if(is_array($media)){
            $docIds = array();
            foreach($media as $med){
                $signDoc=X2SignDocs::model()->findByAttributes(array('mediaId'=>$med->id));
                $docIds[] = $signDoc->id;
                $docsArray[] = $signDoc;
            }
        }

        $user = User::model()->findByAttributes(array("username" => $listId->assignedTo ));
        //only continue if the assigned to for the current user and the listing is the same
        $userLogedIn = User::getMe();
        if($userLogedIn->username != $listId->assignedTo) throw new Exception ('You are trying to send an NDA on a listing that is not yours, please use the Send Document button to send this NDA');
        $envelope = new X2SignEnvelopes;
        $envelope->prepared = 0;
        $envelope->sequential = 1; 
        $envelope->assignedTo =$user->username;
        $envelope->signDocIds = json_encode($docIds);
        $envelope->c_listing = $listId->nameId;

        $envelope->c_automation_nda=0;

        if($listId->c_BLIauto || $listId->c_cim)
            $envelope->BLIsendInfo = json_encode(array("listId" => $listId->id, 'bli' => $listId->c_BLIauto , 'cim' => $listId->c_cim));


        // get the user then the credentials
        $email = Credentials::model()->findDefault($user->id, 'email', TRUE);
        if(empty($email))
            throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
        else
            $email = $email->auth->email;

        $envelope->sender = $email;
        $envelope->name = date('M d, Y: ') . ' NDA Quick Send';
        $envelope->save();

        //make links for both the user and the buyer
        $signLink = new X2SignLinks;
        $signLink->setAttributes(array(
                'signRequired' => 1,
                'modelType' => get_class($buyer),
                'envelopeId' =>  $envelope->id,
                'position' => 1,
                'modelId' => $buyer->id,
                'createDate' => time(),
                'key' => $signLink->getKey(),
                'emailAddress' => $buyer->email
                    ), false);
        $signLink->save();

        $signLink = new X2SignLinks;
        $signLink->setAttributes(array(
                'signRequired' => 1,
                'modelType' => 'User',
                'envelopeId' => $envelope->id,
                'position' => 2,
                'modelId' => $user->id,
                'createDate' => time(),
                'key' => $signLink->getKey(),
                'emailAddress' => $user->email,
                'autoSign' => 1
                    ), false);
        $signLink->save();
        

        //convert all the fields so that they don't have $ and the like
        //set the fields
        $fieldArray = array();
        $models = array($buyer, $user);
        foreach($docsArray as $doc){
            $fields = json_decode($doc->fieldInfo, 1);
            foreach($fields as $field){
                $X2SignField = new X2SignFields;
                $hold = '';
                if(isset($field['value'])) {
                    $hold = Docs::replaceVariables($field['value'], $models[$field['recip'] - 1]);
                    $x2Field = Fields::model()->findByAttributes(array('modelName'=>get_class($models[$field['recip'] - 1]), 'fieldName'=>$field['value']));
                    if (isset($x2Field)) $x2FieldType = $x2Field->type;
                    if(strpos($hold, '$') !== false){
                        $hold= str_replace('$', '', $hold);
                        $hold= str_replace(',', '', $hold);
                    }
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

        }
        foreach($fieldArray as $xField){
            $xField->save();
        }

        $subject = 'NDA';
        $body = '';
        $emailTemplate=Docs::model()->findByAttributes(array('id'=>$listId->autSendEmailTemp));
            if(isset($emailTemplate)){
                    $subject = $emailTemplate->subject;
                    if(empty($subject))  $subject = "Please Sign NDA";
                    $body = $emailTemplate->text;
                
            }
 

        $email = json_encode([
                'subject' => $subject,
                'body' => $body,
                //'reminders' => json_decode($_POST['reminders'] ?? ''),
                ]);
        $envelope->email = $email;
        $envelope->emailBody = $body;
        $envelope->emailSubject = $subject;
        $envelope->save();
        $fails = $envelope->quickSendEmail($subject, $body, 0);
        if (isset($fails) && count($fails) > 0) throw new CHttpException(500, json_encode($fails));
        echo "NDA SENT";
        Yii::app()->end();



    }

    public function actionValidateNdaSetup(){
        $ListId = $_POST['ListId'];
        $list = Listings2::model()->findByPk($ListId);
        //check that there is an associated NDA 
        $media = Media::model()->findByAttributes(array('c_document_type' => 'NDA', 'associationType' => 'Listings2', 'associationId' => $ListId), "mimetype LIKE 'application/pdf%'");
        if(empty($media)){
            echo "NO NDAs set up";
            Yii::app()->end();
        }
        //now check that if there is bli checked then check the bli drop down is set
        if($list->c_BLIauto){
            if(empty($list->autoBliType)){
                $list->c_NDAauto = "No";
                $list->save();
                echo "BLI checked but not selected";
                Yii::app()->end();

            }
        }  

        // Check CIM options
        if ($list->c_cim) {
            // If a user want to send the CIM, they must supply the CIM to send
            // associationType: listingCIM
            // associationId: Id of this listing
            $cim = Media::model()->findAllByAttributes(array('associationType'=>'listingCIM', 'associationId'=>$list->id));
            if (!isset($cim) || empty($cim)) {
                $list->c_NDAauto = "No";
                $list->save();
                echo "CIM checked but no CIM document uploaded.";
                Yii::app()->end();
            }

            if ($list->c_BLIauto && !isset($list->cimEmailTemp)) {
                $list->c_NDAauto = "No";
                $list->save();
                echo "CIM checked but no CIM email template configured.";
                Yii::app()->end();
            }
        }

        //make sure to set praticitpate
        $list->c_NDAauto = "Yes";
        if(!$list->save()) {
            echo CJSON::encode($list->getErrors());
            Yii::app()->end();
        }
 
        echo "NDA SET UP DONE";
        Yii::app()->end();


    }

        //function to generate a fake bli for preview
    public function actionGetPreviewBLI(){
        $ListId = $_POST['ListId'];
        $docName = 'BLI_Template.docx';
        $listing = Listings2::model()->findByPk($ListId);
        //making a mock contact record just for passing listing
        $contact = new Contacts;
        $contact->c_listinglookup__c = $listing->nameId;
        $contact->email = "example@example.com";
        $contact->name = "Jon Doe";
        $docusign = DocusignBehavior::createDocusignInstance();
        $doc = Media::model()->findByAttributes(array('name' => $docName));
        $subject = "Confidential Business Profile - " . $listing->c_listing_number__c;
        echo $docusign->requestPreview(array($doc), array($contact), $subject, null, null, null, true);



    }




    public function actionAddOldTemps($id){
        $envelope = X2SignEnvelopes::model()->findByPk((int)$id);
        $oldids = json_decode($envelope->signDocIds);
        //if we have no old IDs and no new ones the envlope will have an issue so throw an error
        if(count($oldids) == 0 && empty($_GET["ids"])){
             echo "NO DOCS";
             return;
        }
        if(!isset($_GET["ids"]))return;
        //$DocIds = json_decode($_GET["ids"]);
        $newSignDoc = array();
        foreach($_GET["ids"] as $Did){
            $Sdoc = X2SignDocs::model()->findByPk($Did);
            $Ddoc =  Docs::model()->findByPk($Sdoc->docId);
            if(!isset($Sdoc) || !isset($Ddoc)) continue;
            $nDoc = new Docs;
            $nSdoc = new X2SignDocs;

            $nDoc->name = $Ddoc->name;
            $nDoc->type = $Ddoc->type;
            $nDoc->associationType = $Ddoc->associationType;
            $nDoc->createdBy = time();
            $nDoc->updatedBy = $Ddoc->updatedBy;
            $nDoc->visibility = $Ddoc->visibility;
            $nDoc->text = $Ddoc->text;
            $nDoc->save();
            $nSdoc->name = $Sdoc->name;
            $nSdoc->docId = $nDoc->id;
            $nSdoc->mediaId = $Sdoc->mediaId;
            $nSdoc->assignedTo = $Sdoc->assignedTo;
            $nSdoc->recipients = $Sdoc->recipients;
            $nSdoc->fieldInfo = $Sdoc->fieldInfo;
            $nSdoc->signeesNeeded = $Sdoc->signeesNeeded;
            $nSdoc->visibility = $Sdoc->visibility;
            $nSdoc->save();
            $newSignDoc[] = $nSdoc->id;

        }

        $bothIDS = array_merge($oldids, $newSignDoc);
        $envelope->signDocIds = json_encode($bothIDS);
        $envelope->save();

    }

    /**
     * Fetch recipients/documents
     */
    public function actionGetOldTemps($type, $model='Contacts', $query='') {
        switch ($type) {
            case 'documents':
                //manual addition of conditions since X2SignDocs are not the main X2Model of the x2sign module
                $criteria = new CDbCriteria;
                $criteria->compare('name', $query, true);
                $criteria->addCondition('template = 1');
                $criteria->addCondition("assignedTo = '" . User::getMe()->username . "'");
                $criteria->order= 'name ASC';
                $X2SignDocs = X2SignDocs::model()->search(null, $criteria)->getData();
                $templates = array();
                foreach($X2SignDocs as $X2SignDoc)
                    $templates[] = array(
                        'modelId' => $X2SignDoc->id,
                        'viewName' => $X2SignDoc->name . " - " . date('r', $X2SignDoc->createDate) ,
                        'viewText' => $X2SignDoc->name,
                    );

                echo CJSON::encode(array($templates));
                break;
            case 'documentsNonTemp':
                //manual addition of conditions since X2SignDocs are not the main X2Model of the x2sign module
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
                    echo CJSON::encode(array());
                    break;
                }
                $ids = array_map(function($e){return $e->id;}, $data);
                $criteria = new CDbCriteria();
                foreach ($ids as $id) {
                    $criteria->compare('docId', $id, false, 'OR');
                }
                 $criteria->addCondition('fieldInfo IS NOT NULL AND fieldInfo != ""');
                $provider = new CActiveDataProvider('X2SignDocs');
                $criteria->compare('name', $query, true, 'AND');
                $criteria->order = 'name ASC';
                $provider->setCriteria($criteria);
                $provider->pagination = false;
                $X2SignDocs = $provider->getData();
                $templates = array();
                foreach($X2SignDocs as $X2SignDoc)
                    $templates[] = array(
                        'modelId' => $X2SignDoc->id,
                        'viewName' => "$X2SignDoc->name",
                        'viewText' => $X2SignDoc->name,
                        'signerCount' => count(json_decode($X2SignDoc->recipients)),
                    );

                echo CJSON::encode(array($templates));
                break;
                case 'signees':
            case 'viewers':

                $models = array();
                if($model == 'All Models') $models = array("Sellers2","Contacts","X2Leads");
                else $models[] = $model;

                $records = array();

                foreach($models as $mod){

                    $contactCriteria = new CDbCriteria;
                    $contactCriteria->compare('name', $query, true);
                    if($mod == "Contacts"){
                        $contactCriteria->addCondition('email IS NOT NULL');
                    }else{
                        $contactCriteria->addCondition('c_email IS NOT NULL');
                    }
                    $contactCriteria->addCondition('TRIM(email) <> ""');

                    $contactList = $mod::model()->search(100, $contactCriteria)->getData();

                    foreach ($contactList as $contact) {
                        $name = $contact->name ? $contact->name : "$contact->firstName $contact->lastName";
                        $email = $contact->email;
                        if($mod == "Sellers2" OR $mod == "X2Leads")$email = $contact->c_email;
                        $records[] = array(
                            'modelId' => $contact->id,
                            'modelType' => $mod,
                            'viewName' => $name . " - "  . "$email",
                            'viewText' => "<b>$name</b>, $contact->email",
                            'assignedTo' => $contact->assignedTo,
                            'email' => $contact->email
                        );
                    }
                }
                echo CJSON::encode(array($records));
                break;
        }
    }

    public function actionExportX2SignAudit() {
        // Export Audit Trail using X2Sign criteria
        if (isset($_GET['contactId'])) {
            $contact = Contacts::model()->findByPk($_GET['contactId']);
            $criteria = new CDBCriteria();
            /*$criteria = array(
                "join" => 'JOIN x2_sign_links as t0 ON t.id = t0.envelopeId',
                "condition" => 't0.modelId='.$model->id.' AND t0.modelType="'.get_class($model).'"',
                "order" => 't.createDate DESC'
            );*/
            $criteria->alias = 't';
            $criteria->select = 't.*';
            $criteria->join = 'JOIN x2_sign_links AS l ON t.id = l.envelopeId';
            $criteria->addCondition(array('l.modelType = :modelType', 'l.modelId = :contactId', 'l.emailAddress = :email', 't.status = 4'));
            $criteria->params = array(':modelType' => 'Contacts', ':contactId' => $contact->id, ':email' => $contact->email);
            //$criteria->group = 'e.id';

            $_SESSION['exportModelCriteria'] = $criteria;
            $_SESSION['modelExportFile'] = User::getMe()->username . "_S2Sign_Audit_Trail_Export.csv";
            $_SESSION['modelExportMeta'] = array_keys(X2Model::model('X2SignEnvelopes')->attributes);
            $_SESSION['includeTags'] = 1;
            $_SESSION['exportFormat']['exportDestination'] = 'download';
            $_SESSION['exportFormat']['compressOutput'] = 0;
            $_SESSION['writeCSVHeader'] = 2;
            echo CJSON::encode($_SESSION['exportModelCriteria']);
            return;
        }
    }

    /**
     * This function gets the dropdowns from the database
     * @return type json encoded array of dropdowns
     */
    public function actionGetDropdowns(){
        $allDropdowns = Yii::app()->db->createCommand()
        ->select('id,name')
            ->from('x2_dropdowns')
            ->queryAll();
        echo json_encode($allDropdowns);
    }

    /**
     * This function gets the dropdown options for a given dropdown id
     * @param type $id
     * @return type json encoded array of options
     */
    public function actionGetSelectedDropdown($id){
        $selectedDropdown = Yii::app()->db->createCommand()
        ->select('options')
            ->from('x2_dropdowns')
            ->where('id=:id', array(':id'=>$id))
            ->queryRow();
        echo json_encode($selectedDropdown);
    }


    public function actionSignInPerson($id =null) {
        //$this->layout = '//layouts/properties';

        if(!isset($id)){
            $user = User::getMe();
            $envelope = new X2SignEnvelopes;
            $envelope->prepared = 0;
            $envelope->sequential = 1;
            $envelope->hostedEnvelope=1;
            $envelope->assignedTo = $user->username;
            $envelope->signDocIds = json_encode(array());

            $email = Credentials::model()->findDefault($user->id, 'email', TRUE);
            if(empty($email))
                throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
            else
                $email = $email->auth->email;
            $envelope->sender = $email;
            $envelope->name = date('M d, Y: ') . ' Quick Send Doc';
            $envelope->save();
        }
        else{
            $envelope = X2SignEnvelopes::model()->findByPk($id);
        }

        // Getting Data for Recipients
        $recepientsData=$this->HelperQuickSetupRecipients($envelope->id);
        // the email templates
        $emailData=$this->HelperQuickEmailView($envelope->id);
        $viewData=array();
        $viewData['envelope']=$envelope;
        // Users for assigning envelope
        $users=Yii::app()->db->createCommand()
            ->select('username')
            ->from('x2_users')
            ->queryAll();
        $users[] = ['username'=>'-----------'];
        $groups=Yii::app()->db->createCommand()
            ->select('id, name AS username')
            ->from('x2_groups')
            ->queryAll();
        $users = array_merge($users, $groups);
        $viewData['users'] = $users;


        foreach($recepientsData as $key=>$value){
            $viewData[$key]=$value;
        }
        foreach($emailData as $key=>$value){
            $viewData[$key]=$value;
        }

        $this->render('signInPerson', $viewData);

    }

    public function helperQuickSetupRecipients($id) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);

        $signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $id));
        $recipients = array();
        foreach($signLinks as $signLink) {
            $contact = X2Model::model($signLink->modelType)->findByPk($signLink->modelId);
            $recipients[] = array(
                'displayModel' => $signLink->modelType,
                'hiddenModel' => $signLink->modelType,
                'hiddenId' => $signLink->modelId,
                'spot' => $signLink->position,
                'firstName' => $contact->firstName,
                'lastName' => $contact->lastName,
                'email' => $signLink->emailAddress,
                'contactRole' =>  ''//$signLink->contactRole,
            );
        }
        if ($envelope->prepared == 1)
            throw new CHttpException(400, "Bad Request");

        return array(
            'envelope' => $envelope,
            'user' => User::getMe(),
            'recipients' => json_encode($recipients),
        );
    }

    public function helperQuickEmailView($id) {
        $envelope = X2SignEnvelopes::model()->findByPk($id);

        $templateList = array('' => '------------') + Docs::getEmailTemplates('email', 'Contacts');

        if (isset($envelope))
            return array(
                'template' => $templateList,
            );
        else
            throw new CHttpException(400, 'Bad Request');
    }

    public function actionSaveHostInfo($id){
        $envelope = X2SignEnvelopes::model()->findByPk($id);
        $envelope->hostName=$_POST['name'];
        $envelope->hostEmail=$_POST['email'];

        if($envelope->save()){
            echo(json_encode(array('success' => 'Host Data Saved Successfully')));
        }else{
            echo(json_encode(array('error' => 'An Error Occured While Saving Host Data')));
        }
    }

    public function actionUsers() {
        $users=Yii::app()->db->createCommand()
        ->select('username')
        ->from('x2_users')
        ->queryAll();
        $users[] = ['username'=>'-----------'];
        $groups=Yii::app()->db->createCommand()
        ->select('id, name AS username')
        ->from('x2_groups')
        ->queryAll();
        $users = array_merge($users, $groups);
        echo(json_encode($users));
    }

    public function actionSendBuyProf(){
        $contact = "";
        $me = User::getMe();
        $subject = "Buyer Profile- Action required";
        $signer = array(
                'roleName' => 'Signer',
                'name' => $contact->firstName . ' ' . $contact->lastName,
                'email' => $contact->email,
                'recipientId' => (string) $contact->id,
                'type' => get_class($contact),
            );

        $email = Credentials::model()->findDefault($user->id, 'email');
        // Create X2Sign Envelopes
        $envelope = new X2SignEnvelopes;
        $enName = "Buyer Profile Document";
        $emailSubject = "Buyer Profile- Action required";
        $envelope->setAttributes(array(
            'name' => date('M d, Y - ') . $enName,
            'assignedTo' => $user->username,
            'signDocIds' => json_encode(5),
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
        $envelope->c_listing = $SavedModel->c_listinglookup__c;
        $envelope->emailSubject = $emailSubject;
        $envelope->save();        
        $fails = $envelope->quickSendEmail($envelope->subject, $envelope->body, 0);
        if (isset($fails) && count($fails) > 0) throw new CHttpException(500, json_encode($fails));
        echo "Buyer Profile Sent";
        Yii::app()->end();
        




    }


    public function actionX2SignDocUpload($envelopeId,$visibility=0,$assignedTo=null) {
        $mediaIds = array();
        $errorFiles = array();
        if ($assignedTo == null) {
            $assignedTo = User::getMe()->username;
        }
        if (isset($_FILES['pdfFiles']) && is_array($_FILES['pdfFiles']['tmp_name'])) {
            $uploadsDirectory = 'uploads/protected/media/' . $assignedTo . DIRECTORY_SEPARATOR;

            for ($i = 0; $i < count($_FILES['pdfFiles']['tmp_name']); $i++) {
                $fileExtension = strtolower(pathinfo($_FILES['pdfFiles']['name'][$i], PATHINFO_EXTENSION));

                if ($fileExtension == 'pdf' || $fileExtension == 'docx') {  // Fix file extension condition
                    $upload = new CUploadedFile($_FILES['pdfFiles']['name'][$i], $_FILES['pdfFiles']['tmp_name'][$i], $_FILES['pdfFiles']['type'][$i], $_FILES['pdfFiles']['size'][$i], $_FILES['pdfFiles']['error'][$i]);

                    $media = new Media;
                    $media->fileName = $upload->getName();
                    $media->createDate = time();
                    $media->uploadedBy = $assignedTo;
                    $media->private = $visibility;
                    $media->associationType = 'x2signdocs';

                    if (!(file_exists('uploads/protected/media/' . $assignedTo) && is_dir('uploads/protected/media/' . $assignedTo))) {
                        if (!@mkdir('uploads/protected/media/' . $assignedTo, 0777, true)) { // make dir with edit permission
                            throw new Exception('Error creating user folder.');
                        }
                    }

                    if (!$media->save()) {
                        throw new Exception('Error saving Media entry');
                    }

                    $uploadPath = $uploadsDirectory . $media->fileName;
                    if (!move_uploaded_file($upload->getTempName(), $uploadPath)) {
                        throw new Exception('Error saving file');
                    }

                    $mediaIds[] = $media->id;
                } else {
                    $errorFiles[] = $_FILES['pdfFiles']['name'][$i];
                }
            }
        } else {
            throw new Exception('No files uploaded.');
        }

        foreach ($mediaIds as $mediaId) {
            $this->publisherActionForDocuments($envelopeId, $mediaId);
        }

        $response = array(
            'mediaIds' => implode(',', $mediaIds),
            'errorFiles' => implode(',', $errorFiles)
        );

        echo json_encode($response);
    }


    /**
     *Helper function to save the publisher action for documents
     * @param type $envelopeId and $mediaId
     * @return type void saves the publisher action and shows attachment in the envelope
     */
    public function publisherActionForDocuments($envelopeId, $mediaId) {
        $note = new Actions;
        $note->createDate = time();
        $note->visibility = '1';
        $note->type = 'attachment';
        $note->associationId = $envelopeId;
        $note->associationType = 'X2Sign';
        $note->actionDescription = Media::model()->findByPk($mediaId)->name. ':' . $mediaId;
        $note->save();
    }




}
