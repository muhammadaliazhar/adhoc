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
            $this->redirect('index');
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

    /**
     * @author Justin Toyomitsu <justin@x2engine.com>
     * Lists all models.
     */
    public function actionIndex($id = null) {

        $envelope = new X2SignEnvelopes;

        $files = $envelope->getFiles(Yii::app()->user->name, $id);

        $view = $envelope->folderX2SignView(Yii::app()->user->name, $id, $files);

        $dataProvider = new FileSystemObjectDataProvider($view, array(
            'id' => 'root-folder-contents',
        ));

        $model=new X2SignEnvelopes('search');
        $this->render('index', array(
            'dataProvider'=> $dataProvider
        ));
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
        foreach($each_condition as $key => $value) {
            $velocity_count[$key] = X2SignEvents::model()->count("createDate>=" . $value . ' AND ' . "type=1");
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

    /**
     * Fetch recipients/documents
     */
    public function actionGetList($type, $model='Contacts', $query='') {
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

                $records = array();

                //since listtings do not have email pull contacts
                if($model == "Listings2" || $model == "Opportunity") $model = "Sellers2";

                $contactCriteria = new CDbCriteria;
                
                if($model == "Contacts"){
                    $contactCriteria->compare('name', $query, true);
                }else{
                    $contactCriteria->addCondition('(firstName LIKE "%' . addslashes($query) . '%" OR lastName LIKE "%' . addslashes($query) . '%")');
                }
                $contactCriteria->addCondition('email IS NOT NULL');
                $contactCriteria->addCondition('TRIM(email) <> ""');
                if($model == "Contacts"){
                    $contactList = $model::model()->search(100, $contactCriteria)->getData();
                }elseif ($model == "X2Leads"){
                    $contactList = $model::model()->search(100, null ,$contactCriteria,true)->getData();
                }else{
                    $contactList = $model::model()->search(100, null ,$contactCriteria)->getData();
                }
                foreach ($contactList as $contact) {
                    $email = $contact->email;
                    if($model == "X2Leads") $email = $contact->c_email;
                    $name = $contact->name ? $contact->name : "$contact->firstName $contact->lastName";
                    $records[] = array(
                        'modelId' => $contact->id,
                        'modelType' => $model,
                        'viewName' => $name . " - "  . "$email",
                        'viewText' => "<b>$name</b>, $email",
                        'assignedTo' => $contact->assignedTo
                    );
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
        $email = Credentials::model()->findDefault($user->id, 'email');
        if(empty($email))
            throw new Exception ('No default email credentials set. To resolve, create and set email credentials as default.');
        else
            $email = $email->auth->email;

        $signees = json_decode($_POST['signees']);
        $viewers = json_decode($_POST['viewers']);
        $modelType = $_POST['modelType'];
        if($modelType == "Listings2") $modelType = "Contacts"; 
        $names = [];
        foreach (json_decode($_POST['signees']) as $id) {
            if ($id == 'SELF') continue;
            else $names[] = $modelType::model()->findByPk($id)->name;
        }
        $names = implode(', ', $names);

        $envelope = new X2SignEnvelopes;
        $envelope->setAttributes(array(
            'name' => date('M d, Y: ') . "$_POST[emailSubject] @: $names",
            'assignedTo' => $user->username,
            'signDocIds' => $_POST['documents'],
            'sender' => $email,
            'email' => json_encode([
                'subject' => $_POST['emailSubject'],
                'body' => $_POST['emailBody'],
                'reminders' => json_decode($_POST['reminders'] ?? ''),
                ]),
            'sequential' => json_decode($_POST['sequential']) ?? $envelope->sequential,
        ), false);
        
        //save the listing if the x2sign doc is sent to a contact
        if( $modelType == "Contacts"){
            $contactid;
            if(json_decode($_POST['signees'])[0] == 'SELF'){
                $contactid = json_decode($_POST['signees'])[1];    
            }else{
                $contactid = json_decode($_POST['signees'])[0];
            }
            $envelope->c_listing = $modelType::model()->findByPk($contactid)->c_listinglookup__c;

        }

        if (!$envelope->save())
            throw new Exception ('Could not create envelope');

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
            echo 'Issue creating links: Please contact support.';
            return;
        }

        if (empty($fails)) {
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

        if (time() >= $link->createDate + 432000) {
            $link->renewKey();
            $link->sent = 1;
            $link->save();
        }

        $this->redirect($this->createUrl('signDocs', ['key'=>$link->key]));
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
            echo "Invalid link. Do not repeat this action.";
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
        if(isset($signLink) && time() < ($signLink->createDate + 432000) && $signLink->signedDate === NULL) {
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
            echo "Invalid link. Do not repeat this action.";
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
            foreach($signPeople as $key => $personId){
                if($personId == "SELF"){
                    $model = Yii::app()->params->profile;
                    $names[] = $model->fullName;      
                }else{
                    $model = X2Model::model($modelType)->findByPk($personId);
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

            // Save the field inputs into sign fields
            $i = 0;
            $model = $modelType::model()->findByPk($modelId);
            $attributes = array();           
            foreach($fields as $field) {
                // Sanitize POST variable data
                $field['type'] = preg_replace("/[^A-Za-z]/", "", $field['type']);
                $field['fieldId'] = Fields::getPurifier()->purify($field['fieldId']);
                $field['value'] = Fields::getPurifier()->purify($field['value']);

                //have to write code since the fields don't come from post in order
                //$spotNumber = substr($field['fieldId'], strrpos($field['fieldId'], '-') + 1);
                //$spotNumber--;
                // If we find an insertable attribute pattern, attempt to update the
                // attribute on the model that signed the document
                //findSpot info to make sure fields line up
                $spotNumber = NULL;
                foreach($fieldInfo as $key => $checkField){
                    if($field['fieldId'] == $checkField->id){
                        $spotNumber = $key;
                    }
                }

                if(isset($spotNumber) && isset($fieldInfo[$spotNumber]->value) && preg_match('/^{(.*)}$/', $fieldInfo[$spotNumber]->value, $matches)) {
                    $attr = $matches[1];
                    if($model->hasAttribute($attr))
                        $attributes[$attr] = $field['value'];
                }    

                $X2SignField = new X2SignFields;
                
                $X2SignField->setAttributes(array(
                    'type' => $field['type'],
                    'signDocId' => $signDocId,
                    'envelopeId' => $envelopeId,
                    'fieldId' => $field['fieldId'],
                    'value' => ($field['type'] == 'signature') ? $signature->id : $field['value']
                ), false);
                
                if(!$X2SignField->save())
                    throw new CHttpException(500, 'Error saving field.\n' . CJSON::encode($X2SignField));
                
                $i++;
            }
            $model->setAttributes($attributes, false);
            if(!$model->save())
                 throw new CHttpException(500, CJSON::encode($model->getErrors()));

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
                        'envelopeId' => $envelope->id,
                        'type' => X2SignEvents::COMPLETED,
                    ), false);
                    $event->save();

                    //record the completed Media Id <justin@x2engine.com>
                    $mediaId = $envelope->generateCompletePdf();
                    $envelope->completedDoc = $mediaId;
                    $envelope->save();
                   
                    $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
                    $behavior->sendCompletePdf($envelope); 
                    //if this is an exclusive waver make record private
                    $testId = json_decode($envelope->signDocIds); 
                    if(count($testId) == 1 && $testId[0] == 227){
                        $signLinksCheck = X2SignLinks::model()->findByAttributes(array(
                            'envelopeId' => $envelope->id
                        ));
                                                
                        $record = Contacts::model()->findByPk($signLinksCheck->modelId);
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
                        $record->assignedTo = $envelope->assignedTo; //set new assignedTo
                        $record->save();

                        
                    }
                }
            } else {
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
        } else {
            // Signee is required to sign some more documents
            $current = array_search($signDocId, $signDocIds);
            $signDoc = X2SignDocs::model()->findByPk($signDocIds[++$current]);
            $fields = ($_POST['signReq'] == 1) ? json_decode($signDoc->fieldInfo) : '';
            $model = X2Model::model($modelType)->findByPk($modelId);
            $signeeFields = array();
            if(isset($model))
                foreach($fields as $field) {
                    if(isset($field->value))
                        $field->value = Docs::replaceVariables($field->value, $model);
                    
                    if($field->recip == $link->position)
                        $signeeFields[] = $field;
                }
            echo json_encode(array(
                'signDocId' => $signDoc->id,
                'model' => $model,
                'modelType' => $modelType,
                'mediaId' => $signDoc->mediaId, 
                'fields' => json_encode($signeeFields), 
            ));
        }
        
        return;
    }
    
    public function actionCreateSignature() {
        $model = (!is_null($_POST['modelType']) && !is_null($_POST['modelId']))
            ? X2Model::model($_POST['modelType'])->findByPk($_POST['modelId']) : NULL;
        
        $signature = new X2Signature;
        $signature->createImage($_POST['signature'], $_POST['modelType'], $_POST['modelId'], $_POST['font']); //create image with input name
        $signature->setAttributes(array(
            'modelId' => !is_null($model) ? $model->id : NULL,
            'modelType' => $_POST['modelType'] ?? NULL,
            'signature' => $_POST['signature'],
            'emailAddress' => !is_null($model) ? $model->email : NULL,
            'IP' => Yii::app()->request->getUserHostAddress(),
        ), false);

        if(!$signature->save())
            throw new Exception('Could not save signature.');
        else
            echo json_encode(array($signature->getSignatureImage(), $signature->id));
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

    public function actionResend($id) {
        if (!Yii::app()->request->isPostRequest) return;
        
        $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
        $envelope = X2SignEnvelope::model()->findByPk((int)$id);
        $fails = $behavior->resendEnvelope($envelope);
        if (empty($fails)) {
            echo 'Success!';
        } else {
            echo "Could not send to: " . json_encode($fails);
        }
    }

}
