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

/**
 * @author Justin Toyomitsu <justin@x2engine.com>
 */



Yii::import('application.models.X2List');
Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_template".
 * @package application.modules.template.models
 */
class X2SignEnvelopes extends X2Model {

    public $completeTime;

    const ACTIONS_REQUIRED = 1;
    const WAITING_FOR_OTHERS = 2;
    const CANCELLED = 3;
    const COMPLETED = 4;
    const FINISH_LATER = 5;
    /**
     * Returns the static model of the specified AR class.
     * @return Template the static model class
     */
    public static function model($className=__CLASS__) { return parent::model($className); }

    /**
     * @return string the associated database table name
     */
    public function tableName() { return 'x2_sign_envelopes'; }

    public function behaviors() {
        return array_merge(parent::behaviors(),array(
            'LinkableBehavior'=>array(
                'class'=>'LinkableBehavior',
                'module'=>'x2sign'
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
        if (isset($_GET['status'])) $status = $_GET['status'];
        if (isset($_GET['folderId'])) $folderId = $_GET['folderId'];
        $cond = 'status != 5';
        if (isset($status))
            $cond = "status = $status";
        if (isset($status) && $status == 0)
            $cond = 'status != 5';
        if (isset($folderId))
            $cond = "signFolderId = '$folderId'";
        if(!empty($_GET['X2SignEnvelopes_sort'])) $criteria = new CDbCriteria(array('condition' => $cond));
        else  $criteria = new CDbCriteria(array('condition' => $cond, 'order' => 'createDate DESC'));

        $genCriteria = new CDbCriteria;
        if(!Yii::app()->params->isAdmin){
            $usersGroupIds = Groups::model()->getUserGroups(Yii::app()->getSuId());
            $generationalGroupmates = array();
            if (!empty($usersGroupIds) ){
            //if (!empty($usersGroupIds) && ($generationalGroupmates = Yii::app()->cache->get(User::getMe()->username.'_generational_groupmates')) === false) {
                $generationalGroupmates = Groups::model()->getGenerationChildGroupmatesIDs($usersGroupIds, array(), true);
                $isFranchise = false;
                foreach($usersGroupIds as $groupId) {
                    if (Groups::hasRole($groupId, 'Franchise'))
                        $isFranchise = true;
                }
                if ($isFranchise) {
                    $groupmates = Groups::model()->getGroupmates(Yii::app()->getSuId());
                    $generationalGroupmates = array_merge($generationalGroupmates, $groupmates);
                }
            }
            foreach ($generationalGroupmates as $groupmate) {
                        $genCriteria->addCondition('assignedTo = "' . $groupmate . '"', 'or');
            }      

            $genCriteria->addCondition('assignedTo = "' . User::getMe()->username . '"',"or");
        }
        //this will be for index 2 page
        if(isset($_GET['data-id'])){
           $criteria->addCondition('status = ' . $_GET['data-id'] , "and"); 
        }
        $criteria->mergeWith($genCriteria);
        return $this->searchBase($criteria, $pageSize);
    }

        public function searchAll($pageSize = null, $filter = null) {
            return $this->search($pageSize, $filter);
        }

        public function searchRelatedRecords($pageSize = null) {
            $criteria=new CDbCriteria;
            if(!Yii::app()->params->isAdmin){
                $usersGroupIds = Groups::model()->getUserGroups(Yii::app()->getSuId());
                $generationalGroupmates = array();
                if (!empty($usersGroupIds) && ($generationalGroupmates = Yii::app()->cache->get(User::getMe()->username.'_generational_groupmates')) === false) {
                    $generationalGroupmates = Groups::model()->getGenerationChildGroupmatesIDs($usersGroupIds, array(), true);
                }
                foreach ($generationalGroupmates as $groupmate) {
                            $criteria->addCondition('assignedTo = "' . $groupmate . '"', 'or');
                }

                $criteria->addCondition('assignedTo = "' . User::getMe()->username . '"',"or");
            }
            //this will be for index 2 page
            if(isset($_GET['data-id'])){
               $criteria->addCondition('status = ' . $_GET['data-id'] , "and");

            }
            if (isset($_GET['X2SignEnvelopes']['relatedRecords']) && !empty($_GET['X2SignEnvelopes']['relatedRecords'])) {
                $ids = Yii::app()->db->createCommand()
                    ->select('envelopeId')
                    ->from('x2_sign_links')
                    ->where('modelName LIKE "%'.$_GET['X2SignEnvelopes']['relatedRecords'].'%"')
                    ->queryAll();
                $ids = array_column($ids, 'envelopeId');
                if(is_array($ids) && count($ids) > 0){
                    $ids = implode(',', $ids);
                    $criteria->addCondition('id IN ('.$ids.')');
                }else
                    $criteria->addCondition('id = 0');
            }
            
            return $this->searchBase($criteria, $pageSize);
        }

        /**
         * Sets default status to (1) before save
         *
         * @return boolean whether or not to save
         */
        public function beforeSave() {
            if ($this->status === null) {
                $this->status = self::WAITING_FOR_OTHERS;
            }

            //changeing this code to check that the listing it is closed to 
            if(false && !empty($this->BLIsendInfo) && $this->owner->status == self::COMPLETED && $this->attributeChanged ('status')){
                //check if the related listing has bliChecked
                $bliArray = json_decode($this->BLIsendInfo, true);
                $listing = Listings2::model()->findByPk($bliArray["listId"]);
                if(!empty($listing) && $listing->c_BLIauto==1){
                    Yii::log("In Send Bli after NDA Signed",'trace','application.api');
                    yii::setPathOfAlias('Custom',dirname(__FILE__, 4));
                    yii::import('Custom.components.behaviors.SendBLIBehaviour');
                    $this->attachBehavior('SendBLIBehaviour', new SendBLIBehaviour);
                    $signLink=X2SignLinks::model()->findByAttributes(array ('envelopeId' => $this->owner->id));
                    $buyer=Contacts::model()->findByAttributes(array ('id' => $signLink->modelId));
                    $this->SendBLIDoc($buyer->id);
                }
            }

            //adding code to set the relatedRecords each time it gets save
            if(!empty($this->id)){
                $signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $this->id));
                $HtmlReturn = '';
                foreach($signLinks as $link){
                    if($link->modelType != "user"){
                        $linkModel = ($link->modelType)::model()->findByPk($link->modelId);
                        if(isset($linkModel)){
                            //$HtmlReturn .=  $linkModel->name . ", ";
                             $HtmlReturn .=  $linkModel->getLink() . ", ";
                        }
                    }
    
                }
                $this->relatedRecords = $HtmlReturn;
            }


            return parent::beforeSave();
        }

        public function renderAttribute($fieldName, $makeLinks = true, $textOnly = true, $encode = true){
            switch ($fieldName) {
                case 'completeDate':
                    return $this->completeDate ? Formatter::formatDate($this->completeDate) : '--'; 
                case 'status':
                    if ($this->status == self::ACTIONS_REQUIRED)
                        return 'Actions Required';
                    if ($this->status == self::WAITING_FOR_OTHERS)
                        return 'Waiting For Others'; 
                    if ($this->status == self::CANCELLED)
                        return 'Cancelled';
                    if ($this->status == self::COMPLETED)
                        return 'Completed';
                    if ($this->status == self::FINISH_LATER)
                        return 'Finish Later';

                case 'completedDoc':
                    if (isset($this->completedDoc) && $this->completedDoc != null) {
                        $media = X2Model::model('Media')->findByPk($this->completedDoc); 
                        if(isset($media))
                            return $media->getMediaLink() . '  |  ' . $media->getDownloadLink();
                    }
                    return '--';
                case 'signDocIds':
                    $listView = '';
                    $documents = json_decode($this->signDocIds);
                    for($x=0; $x < count($documents); $x++) {
                        $doc = X2SignDocs::model()->findByPk($documents[$x]);
                        if(isset($doc->mediaId))
                            $media = X2Model::model('Media')->findByPk($doc->mediaId);
                        if($x>0)
                            $listView .= '<br>';
                        if(isset($media))
                            $listView .= $x+1 . '. ' . $media->getMediaLink() . ' ';
                        else
                            $listView .= $x+1 . '. No Media Found ';
                    }
                    return $listView;
                case 'c_waiting_on':
                    if ($this->status != self::WAITING_FOR_OTHERS) return '--';
                    $waitLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $this->id, 'signedDate' => NULL));
                    $waitStr = "<br>";
                    foreach ($waitLinks as $link)
                        $waitStr .= '<a href="'.Yii::app()->getBaseUrl(true).'/index.php/'.strtolower($link->modelType).'/'.$link->modelId.'">' . $link->emailAddress . '</a><br>';
                    return $waitStr;
                default:
                    return parent::renderAttribute($fieldName, $makeLinks, $textOnly, $encode);
            }
        }

        public function renderStatus() {
            switch($this->status) {
                case 1:
                    return "Need to Sign";
                case 2:
                    return "Waiting for Others";
                case 3:
                    return "Cancelled";
                Case 4:
                    return "Completed";
                case 5:
                    return "Draft";
                default:
                    return "Not Sent";
            }
        }

        public function renderSentDate() {
            return date ('m/d/Y h:i A', $this->lastUpdated);
        }

        public function renderLink() {
           //$title = isset($this->emailSubject) ? $this->emailSubject : $this->name;
           $title = $this->name;
           $links = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $this->id));
           $recipientText = 'To: ';
           foreach ($links as $link) {
               $recipientText .= $link->emailAddress . ' ';
           }
           return '<a href="' . Yii::app()->controller->createUrl('/x2sign/view/', array('id' => $this->id)). '">' . $title . '</a><br>' . $recipientText;
        }

        public function renderToolDropdown() {
            if ($this->status == 1)
                return "<a href=\"https://sydney.tworld.com/index.php/x2sign/signFromEnvelope/id/$this->id\" target=\"_blank\"><button type=\"button\" class=\"w-100 btn btn-outline-dark x2sign-tool-btn btn-sm\">SIGN</button></a>";
            if ($this->status == 2)
                return "<button type=\"button\" data-envelope-id=\"$this->id\" class=\"w-100 btn btn-outline-dark x2sign-tool-btn btn-sm resend-btn\" data-bs-toggle=\"modal\" data-bs-target=\"#resend-modal\" onclick=\"x2.x2sign.setupResendClass($this->id)\">RESEND</button>";
                //return "<button type=\"button\" data-envelope-id=\"$this->id\" class=\"w-100 btn btn-outline-dark x2sign-tool-btn btn-sm resend-btn\">RESEND</button>";
            if ($this->status == 5)
                return '<button data-envelope-id="'.$this->id.'" type="button" class=" w-100 btn btn-outline-dark x2sign-tool-btn btn-sm cont-btn">CONTINUE</button>';
            return '<button data-envelope-id="'.$this->id.'" type="button" class="w-100 btn btn-outline-dark x2sign-tool-btn btn-sm move-btn" data-bs-toggle="modal" data-bs-target="#folder-move-modal">MOVE</button>';
        }

        public static function getCompleteTime($data = null) {

            $completeTime = '--';
 
            if(is_null($data)) {
                return $completeTime;
            }else {
                if(isset($data->completeDate) && isset($data->createDate)) {
                    $completeTime = $data->completeDate - $data->createDate;
                    if($completeTime > 59){
                        $bit = array(
                            ' day'         => floor($completeTime / 86400),
                            ' hour'        => $completeTime / 3600 % 24,
                            ' minute'      => $completeTime / 60 % 60,
                        );
       
                        foreach($bit as $k => $v){
                            if($v > 1)$ret[] = $v . $k . 's';
                            if($v == 1)$ret[] = $v . $k;
                        }
                        array_splice($ret, count($ret)-1, 0, 'and');
                        $ret[] = '';
                        $completeTime = join(' ', $ret);
                    }elseif($completeTime < 60 && $completeTime >= 0) {
                        $completeTime = '1 minute';
                    }else {
                        $completeTime = 'ERROR(negative seconds)';
                    }
                }
            }
            return $completeTime;
        }


        public static function renderName ($data) {
            $options = array (
                "class" => "file-system-object-name",
            );

            $envelope = new X2SignEnvelopes();

            if ($data->title && preg_match ('/^[a-zA-Z0-9 \-]+$/', $data->title))
                $options['title'] = $data->title;
            return $data->getIcon ().CHtml::tag ("span", $options, $envelope->getLink ($data));
        }

        public static function getNameLink($data = null) {
            if(is_null($data)) {
                return 'No Data';
            }else {
                return X2Html::link(CHtml::encode($data->name), 
                Yii::app()->controller->createUrl('/x2sign/view', array('id' => $data->id)),
                array());
            }
        }

        public static function getListLink($data = null) {
            if(is_null($data)) {
                return '';
            }else {
                $list = Listings2::model()->findByAttributes(array('nameId' => $data->c_listing));
                if(isset($list)){
                    return $list->getLink();
                }
                return '';
            }
        }


        public function getLink($data = null) {

            $name = 'envelope name';
            $url = '#';
            $class = array();

            //NAME
            if(isset($data) && isset($data->name))
               $name = CHtml::encode($data->name);
            elseif(isset($this->name))
               $name = CHtml::encode($this->name);

            //URL
            if(isset($data) && isset($data->objId) && ($data->type) && ($data->type !== 'folder'))
               $url = Yii::app()->controller->createUrl('/x2sign/view', array('id' => $data->objId));
            elseif(isset($this->id))
               $url = Yii::app()->controller->createUrl('/x2sign/view', array('id' => $this->id));

            //CLASS
            if(isset($data->type) && $data->type === 'folder')
               //$class = array('class'=>'folder-link pseudo-link', 'data-id'=>$data->objId); 
                 $url = Yii::app()->controller->createUrl('/x2sign/index2?data-id=' . $data->objId);
            //needs name, url, and class
            return X2Html::link($name, $url, $class);
        }

        public function getFolderNum($assignedTo = null) {

            $folderNum = array();
             $statuses = array(self::ACTIONS_REQUIRED, self::WAITING_FOR_OTHERS, self::CANCELLED, self::COMPLETED, self::FINISH_LATER);

            if(!is_null($assignedTo)) {
                foreach($statuses as $status){
                    $files = X2SignEnvelopes::model()->findAllByAttributes(array(
                        'assignedTo' => $assignedTo,
                        'status' => $status
                    ));
                    if(count($files) == 0) {
                        $folderNum[$status] = '--';
                    }else{
                        $folderNum[$status] = count($files);
                    }
                }
            }
            return $folderNum;
        }

        public function getFiles($assignedTo = null, $status = null) {
            $files = X2SignEnvelopes::model()->findAllByAttributes(array(
                'assignedTo' => $assignedTo, 
                'status' => $status
            ));
            
            return $files;
        }

        /**
         * Returns a view folder for 3 files 
         * 1. Awaiting Action
         * 2. Waiting for Others
         * 3. Cancelled 
         * 4. Completed
         * @return FileSystemObjectDataProvider 
         */
        public function folderX2SignView($assignedTo, $id = null, $files = array()) {
            $folderView = array();
            if(is_null($id)) {
                $folderNums = $this->getFolderNum($assignedTo);
                $folderView[] = $this->createFileSystemObject('parent', 'Actions Required', null, $folderNums[1],  1);
                $folderView[] = $this->createFileSystemObject('parent', 'Waiting for Others', null, $folderNums[2], 2);
                $folderView[] = $this->createFileSystemObject('parent', 'Cancelled', null, $folderNums[3], 3);
                $folderView[] = $this->createFileSystemObject('parent', 'Completed', null, $folderNums[4], 4);
                $folderView[] = $this->createFileSystemObject('parent', 'Finish Later', null, $folderNums[5], 5);
            }else {
                $folderView[] = $this->createFileSystemObject('parent', '..', null, '--', null);
                if(!is_null($files) && count($files) > 0) {
                    foreach($files as $envelope) {
                        $folderView[] = $this->createFileSystemObject('envelope', $envelope->name, $envelope, '--', $envelope->id);
                    }
                }
            }
            return $folderView;
        }
      

        /**
         * Returns a file system (parameter: name)
         * @return FileSystemObject
         */
        public function createFileSystemObject($type, $name, $model, $itemNum, $id) {
            $options = array(
                'id' => $id,
                'parentId' => $this->id,
                'type' => null,
                'objId' => null,
                'name' => null,
                'createdBy' => null,
                'lastUpdated' => null,
                'updatedBy' => null,
                'visibility' => null,
            );
            if ($type === 'parent') {
                $options['objId'] = $id ? $id : null;
                $options['type'] = 'folder';
                $options['name'] = $name;
                $options['itemNum'] = $itemNum;
                $options['title'] = $model ? $model->name : Yii::t('docs', 'Docs');
                $options['isParent'] = true;
            } else {
                $options['type'] = $type;
                $options['objId'] = $model->id;
                $options['name'] = $name;
                //$options['createdBy'] = $model->createdBy;
                //$options['lastUpdated'] = $model->lastUpdated;
                //$options['updatedBy'] = $model->updatedBy;
                //$options['visibility'] = $model->visibility;
            }
            return new FileSystemObject($options);
        }

        /* public function generateCompletePdf() {
            if (!$this->status === self::COMPLETED) return;

            $behavior = MPDFBehavior::createInstance();
            $pdf = $behavior->newPdf();

            $completeDate = (new DateTime('', new DateTimeZone('UTC')))
                ->setTimestamp($this->completeDate)
                ->format('M j, Y @ H:i:s T');
            $pdf->SetWatermarkText("Completed on: $completeDate");
            $pdf->showWatermarkText = true;

            $signDocIds = json_decode($this->signDocIds);
            foreach ($signDocIds as $signDocId) {
                $signDoc = X2SignDocs::model()->findByPk($signDocId);
                $fieldInfo = json_decode($signDoc->fieldInfo);
                $media = Media::model()->findByPk($signDoc->mediaId);
                $pdf->setSourceFile($media->path);
                while ($page = $pdf->nextPage()) {
                    $pageItems = array_filter($fieldInfo, function($e)use($page){return $e->page==$page;});
                    foreach ($pageItems as $item) {
                        $fieldId = $item->id;
                        $signField = X2SignFields::model()->findByAttributes([
                            'signDocId'=>$signDocId,
                            'envelopeId'=>$this->id,
                            'fieldId'=>$fieldId]);
                        if (!$signField) continue;
                        $type = explode('-', $fieldId)[0];
                        switch ($type) {
                            case 'Signature':
                                $sig = X2Signature::model()->findByPk($signField->value);
                                $lnk = X2SignLinks::model()->findByAttributes([
                                    'envelopeId' => $this->id,
                                    'modelType' => $sig->modelType,
                                    'modelId' => $sig->modelId]);
                                $evt = X2SignEvents::model()->findByAttributes([
                                    'envelopeId' => $this->id,
                                    'documentId' => $signDocId,
                                    'signLinkId' => $lnk->id]);
                                    $pdf->WriteText($item->left, $item->top, $evt->IP, 'times', '', 4, .1);
                                $pdf->ImageByArray($sig->path, $item->left, $item->top, $item->width, $item->height);
                                break;
                            case 'Checkbox':
                                $value = $signField->value;
                                $pdf->WriteText($item->left, $item->top, $value);
                                break;
                            case 'Date':
                                $value = $signField->value;
                                $pdf->WriteText($item->left, $item->top, $value);
                                break;

                            default:
                                $value = $signField->value;
                                $fontSize = $item->height * .8 - 10;    
                                $pdf->WriteText($item->left, $item->top, $value, '', '' , $fontSize);
                        }
                    }
                }
            }
            $fileName = "Envelope_{$this->id}_Complete.pdf";
            $filePath = "uploads/protected/x2sign/$fileName";
            $pdf->Output($filePath,'F');

            $media = new Media;
            $media->fileName = $fileName;
            $media->uploadedBy = $this->assignedTo;
            if (Yii::app()->settings->amazonS3CredentialsId) {
                //post to s3
                $media->s3 = 1;
                $key = $media->getAccessKey();
                $body = $pdf->Output('','S');
                AmazonS3Behavior::createInstance()->put($key, $body);
                unlink($model->path);
            }
            $media->save();

            return $media->id;
        } */

        public function encryptedComplete($pdf, $signDocId) {
            $signDoc = X2SignDocs::model()->findByPk($signDocId);
            $format = CJSON::decode($signDoc->format);
            $width = $format['width'];
            $height = $format['height'];
            $fieldInfo = json_decode($signDoc->fieldInfo); // Changed to CJSON::decode 10/09 since one field would break rest of code (treated as object instead of array) 
            // if not array make blanke array
            if(!(is_array($fieldInfo) ))$fieldInfo = array();
            $media = Media::model()->findByPk($signDoc->mediaId);
            $pdf->setSetaSourceFile($media->path);
            while($page = $pdf->nextPage()) { 
                //$page = $pdf->removeAnnotations($page);
                $format = SetaPDF_Core_PageFormats::getFormat($page->getWidthAndHeight(), SetaPDF_Core_PageFormats::ORIENTATION_AUTO);
                $pageItems = array_filter($fieldInfo, function($e)use($page, $pdf){ return $e->page == $pdf->getPageNum(); });
                foreach ($pageItems as $item) {
                    $fieldId = $item->id;
                    $signField = X2SignFields::model()->findByAttributes(array(
                        'signDocId' => $signDocId,
                        'envelopeId' => $this->id,
                        'fieldId' => $fieldId
                    ));
                    if (!$signField) continue;
                    $type = explode('-', $fieldId)[0];
                    //$top = $item->top;
                    //$left = $item->left;
                    //$top = (int) preg_replace('/\D/', '', $item->top);
                    //$left = (int) preg_replace('/\D/', '', $item->left);
                    $top = (float) str_replace('px', '', $item->top);
                    $left = (float) str_replace('px', '', $item->left);

                    // Convert px at 96 dpi back to 72
                    //$convertTopDpi = $top / 96 * 72;
                    //$convertLeftDpi = $left / 96 * 72;                    

                    /*if(!SetaPDF_Core_PageFormats::is(SetaPDF_Core_PageFormats::LETTER, $format)) {
                        $convertTopPpi = round((792 * $item->top) / 1056);
                        $top = round(($convertTopPpi * $format['height']) / 792);
                        $convertLeftPpi = round((612 * $item->left) / 816); 
                        $left = round(($convertLeftPpi * $format['width']) / 612);
                    }*/

                    switch ($type) {
                        case 'Radio':
                            $value = $signField->value;

                            if($value)
                                $img = 'images/radiobuttonon.png';
                            else
                                $img = 'images/radiobuttonoff.png';
                            $page = $pdf->WriteSetaPngImage($page, $img, $left + 10, $top + 10, 10, 10);
                            break;
                        case 'Date':
                            $value = $signField->value;
                            $fontSize = (float) $item->height * 0.75 - 6;
                            $page = $pdf->WriteSetaText($page, $left, $top, $value, $fontSize, $format);
                            break;
                        case 'Checkbox':
                            $value = $signField->value;
                            if($value)
                                $img = 'images/checkbox_icon.png';
                            else
                                $img = 'images/unchecked_box.jpg';
                            $page = $pdf->WriteSetaPngImage($page, $img, $left + 10, $top + 10, 10, 10);
                            //$page = $pdf->WriteSetaCheckbox($page, $item->left, $item->top, $item->width, $value); 
                            break;
                        case 'Text':
                            $value = html_entity_decode($signField->value);
                            //$fontSize = (float) 12 * 0.75;//$item->height * .8 - 10;
                            $fontSize = $item->height * .8 - 12;
                            $page = $pdf->WriteSetaText($page, $left, $top, $value, $fontSize, $format);
                            break;
                        case 'Initials':
                        case 'Signature':
                            if (empty($signField->value)) break;
                            if($signField->value != -1) {
                                $sig = X2Signature::model()->findByPk($signField->value);
                                /*$lnk = X2SignLinks::model()->findByAttributes([
                                    'envelopeId' => $this->id,
                                    'modelType' => $sig->modelType,
                                    'modelId' => $sig->modelId]);
                                $evt = X2SignEvents::model()->findByAttributes([
                                    'envelopeId' => $this->id,
                                    'documentId' => $signDocId,
                                    'signLinkId' => $lnk->id,
                                    'type' => X2SignEvents::SIGNED]);*/
                                $sig = X2Signature::model()->findByPk($signField->value);
                                 $imagePath =  Yii::app()->getBasePath() . '/' . $sig->path; 
                                        //had to add code since upload needs to be difrent for path
                                $pathCheck = strpos(addslashes($sig->path),addslashes("uploads"));
                                if( $pathCheck != FALSE) $imagePath = $sig->path;
                                /*$lnk = X2SignLinks::model()->findByAttributes([
                                    'envelopeId' => $this->id,
                                    'modelType' => $sig->modelType,
                                    'modelId' => $sig->modelId]);
                                $evt = X2SignEvents::model()->findByAttributes([
                                    'envelopeId' => $this->id,
                                    'documentId' => $signDocId,
                                    'signLinkId' => $lnk->id,
                                    'type' => X2SignEvents::SIGNED]);*/
                                $page = $pdf->writeSetaPngImage($page, $imagePath , $left, $top - 10, $item->width, $item->height);
                            }
                            break;
                        case 'Stamp':
                            break;
                        default:
                            $value = html_entity_decode($signField->value);
                            // Determine font size in pt
                            $fontSize = (float) (($item->height - 9) * .8 - 2.8) * 0.75;//$item->height * .8 - 10;
                            $page = $pdf->WriteSetaText($page, $left, $top, $value, $fontSize, $format);
                            break;
                    }

                }

                // Stamp each page with the envelope id
                $page = $pdf->WriteSetaText($page, 0, 0, "X2Sign Envelope ID: $this->id", 7, $format); 

                $pdf->addSetaPage($page);
            }
        }

        public function nonEncryptedComplete($pdf, $signDocId) {
            $signDoc = X2SignDocs::model()->findByPk($signDocId);
            $fieldInfo = json_decode($signDoc->fieldInfo);
            $media = Media::model()->findByPk($signDoc->mediaId);
            $pdf->setSourceFile($media->path);
            while ($page = $pdf->nextPage()) {
                $pageItems = array_filter($fieldInfo, function($e)use($page){return $e->page==$page;});
                foreach ($pageItems as $item) {
                    $fieldId = $item->id;
                    $signField = X2SignFields::model()->findByAttributes([
                        'signDocId'=>$signDocId,
                        'envelopeId'=>$this->id,
                        'fieldId'=>$fieldId]);
                    if (!$signField) continue;
                    $type = explode('-', $fieldId)[0];
                    switch ($type) {
                        case 'Signature':
                            if (empty($signField->value)) break;
                            $sig = X2Signature::model()->findByPk($signField->value);
                            $lnk = X2SignLinks::model()->findByAttributes([
                                'envelopeId' => $this->id,
                                'modelType' => $sig->modelType,
                                'modelId' => $sig->modelId]);
                            $evt = X2SignEvents::model()->findByAttributes([
                                'envelopeId' => $this->id,
                                'documentId' => $signDocId,
                                'signLinkId' => $lnk->id,
                                'type' => X2SignEvents::SIGNED]);
                            $pdf->WriteText($item->left, $item->top, $evt->IP, 'times', '', 4, .1);
                            $pdf->ImageByArray($sig->path, $item->left, $item->top, $item->width, $item->height);
                            break;
                        case 'Stamp':
                            if (empty($signField->value)) break;
                            $sig = X2Signature::model()->findByPk($signField->value);
                            $lnk = X2SignLinks::model()->findByAttributes([
                                'envelopeId' => $this->id,
                                'modelType' => $sig->modelType,
                                'modelId' => $sig->modelId]);
                            $evt = X2SignEvents::model()->findByAttributes([
                                'envelopeId' => $this->id,
                                'documentId' => $signDocId,
                                'signLinkId' => $lnk->id,
                                'type' => X2SignEvents::SIGNED]);
                            $pdf->WriteText($item->left, $item->top, $evt->IP, 'times', '', 4, .1);
                            $pdf->ImageByArray($sig->path, $item->left, $item->top, $item->width, $item->height);
                            $signDate = (new DateTime('', new DateTimeZone('UTC')))
                                ->setTimestamp($evt->createDate)
                                ->format('m/d/Y');
                            $pdf->WriteText(intval($item->left)+intval($item->width), $item->top, $signDate);
                            break;
                        default:
                            $value = $signField->value;
                            $fontSize = $item->height * .8 - 10;
                            $pdf->WriteText($item->left, $item->top, $value, '', '' , $fontSize);
                    }
                }
            }
        }

        public function generateCompletePdf() {
            if (!$this->status === self::COMPLETED) return;

            $behavior = MPDFBehavior::createInstance();
            $pdf = $behavior->newPdf();
            //$pdf->checkForEncryption();
            $completeDate = (new DateTime('', new DateTimeZone('UTC')))
                ->setTimestamp($this->completeDate)
                ->format('M j, Y @ H:i:s T');
            //$pdf->SetWatermarkText("Completed on: $completeDate");
            //$pdf->showWatermarkText = true;

            $fileName = "Envelope_{$this->id}_Complete.pdf";
            $filePath = "uploads/protected/x2sign/$fileName";
            $pdf->setSetaDestFile($filePath);

            $signDocIds = json_decode($this->signDocIds);
            foreach ($signDocIds as $signDocId) {
                $signDoc = X2SignDocs::model()->findByPk($signDocId);
                $fieldInfo = json_decode($signDoc->fieldInfo);
                $media = Media::model()->findByPk($signDoc->mediaId);

                //check to see if it is a docx file
                if ($media->isDocx()) {
                    try {
                        $media = $media->docxToPdf();
                    } catch (Exception $e) {
                        $msg = $e->getMessage();
                        throw new Exception("DOCX could not be converted to PDF: $msg");
                    }
                }                 
 
                //flat this here
                $flatName = "Flat_{$signDocId}_.pdf";
                $flatPath = "/var/www/transworld_crm/uploads/protected/x2sign/$flatName";
                                    //shell_exec('convert -density ' . $media->path . ' ' . $media->path);

                // Check if the PDF is an AcroForm, only then will we flatten it
                // Adapted from: https://github.com/howtomakeaturn/pdfinfo/blob/master/src/Howtomakeaturn/PDFInfo/PDFInfo.php
                exec('pdfinfo ' . escapeshellcmd($media->path), $output, $return);
                //for useing flat path
                $useFlat = false;
                foreach($output as $prop){
                    //just flaten all files
                        //$outputOfShell = shell_exec('pdf2ps ' . escapeshellcmd($media->path) . ' - | ps2pdf - ' . escapeshellcmd($media->path) . '.tmp 2>&1; mv ' . escapeshellcmd($media->path) . '.tmp ' . $flatPath);
                        //if we have a none empty return the the flat threw an error so don't use the file
                        //if(empty($outputOfShell))
                        if($media->path != "/var/www/transworld_crm/uploads/protected/media/jdibartolo/Deck_Plate_firearms_ML#2_.pdf"){
                            $useFlat = true;
                            $pdf->makeFlatFile($media->path, $flatPath);
                        }else{


                        }

                }
                if(!$useFlat) $flatPath = $media->path;
                //shell_exec('pdf2ps ' . escapeshellcmd($media->path) . ' - | ps2pdf - ' . escapeshellcmd($media->path) . '.tmp ; mv ' . escapeshellcmd($media->path) . '.tmp ' . $flatPath);
                $pdf->checkForEncryption($flatPath);
                //if(strpos($this->name, "Buyer Profile Document") === false)
                  //  shell_exec('pdf2ps ' . escapeshellcmd($media->path) . ' - | ps2pdf - ' . escapeshellcmd($media->path) . '.tmp ; mv ' . escapeshellcmd($media->path) . '.tmp ' . $flatPath);

                //if ($pdf->encrypted) {
                    $this->encryptedComplete($pdf, $signDocId); // need to use SetaPDF-Core if PDF is encrypted
                // } else {
                //     $this->nonEncryptedComplete($pdf, $signDocId);
                // }
            }

            //if ($pdf->encrypted) {
                $pdf->setaOutput();
            //} else {
            //    $pdf->Output($filePath,'F');
            //}

            $media = new Media;
            $media->fileName = $fileName;
            $media->uploadedBy = $this->assignedTo;
            if (Yii::app()->settings->amazonS3CredentialsId) {
                //post to s3
                $media->s3 = 1;
                $key = $media->getAccessKey();
                $body = $pdf->Output('','S');
                AmazonS3Behavior::createInstance()->put($key, $body);
                unlink($model->path);
            }
            $media->save();
            return $media->id;
        }

        public function generateCertificateHtml() {
            if (!$this->status) return;
            
            $html = Yii::app()->controller->renderPartial('_certificate', [
                'envelope' => $this,
            ],true);
            return $html;
        }

        public function getX2SignLinks(array $attributes=[]) {
            $attributes = array_merge($attributes, ['envelopeId'=>$this->id]);
            $links = X2SignLinks::model()->findAllByAttributes($attributes, ['order'=>'position']);
            return $links ? $links : [];
        }

        public function quickSendEmail($subject, $message, $delay = 0) {
            $this->setAttributes(array(
                'emailSubject' => $subject,
                'emailBody' => $message,
                'reminders' => null,
            ), false);
            $this->save();
            $links = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $this->id), ['order'=>'position']);
            if (is_null($links))
                throw new CHttpException(400, 'Bad Request');

            $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
            //we now only send right away if no delay
            if($delay === 0 || empty($delay)){
                $fails = $this->sendDocsEmailHelper($links, $this);
            }else{
                $this->sendDate = time() + ($delay * 60 * 60);
                $this->sent = 0;
                 $this->save();
                return;

            }
            if (!empty($fails)) {
                $this->failed = 1;
                $this->save();
            }
            return $fails;
        }

 
    /**
     * Save new IDs to this envelope's Waiting On field.
     * 
     * @param mixed $ids int or int array of ids
     * @param boolean $save toggle model save
     * @return true if successful, false otherwise
     */
    public function pushToWaitingOn($ids, $save = true) {
        if (!isset($ids)) return true;
        $ids = is_array($ids) ? $ids : [$ids];
        $waitingOn = json_decode($this->c_waiting_on) ? json_decode($this->c_waiting_on) : [];
        //$mergedValues = array_values(array_unique(array_merge($ids, $sharedTo)));
        $mergedValues = $waitingOn;
    
        foreach($ids as $id) {
            if (!is_numeric($id) || Groups::userHasRole($id, 'Application Admin') || $id == 4143 || $id == 1) continue;
            if (!in_array($id, $mergedValues))$mergedValues[] = (string) $id;
        }
        $this->c_waiting_on = json_encode($mergedValues);
        if (!$save) return true;
        if (!$this->save(true, ['c_waiting_on'])) {
            Yii::log("Envelope $this->id: Could not add IDs to c_waiting_on.", 'error', 'application');
            return false;
        }
        $this->refresh();
        if (json_decode($this->c_waiting_on) === null) {
            Yii::log("Envelope $this->id: JSON format error for c_waiting_on.", 'error', 'application');
            $this->c_waiting_on = json_encode($c_waiting_on);
            $this->save(true, ['c_waiting_on']);
            return false;
        }
        return true;
    }

     public function renderRelatedRecords() {
            //here wrting code to check and save the related records field
            if(empty($this->relatedRecords)) $this->setRelatedRecords();
            return $this->relatedRecords;
            /*$signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $this->id));
            $HtmlReturn = '';
            foreach($signLinks as $link){
                if($link->modelType != "user"){
                    $linkModel = ($link->modelType)::model()->findByPk($link->modelId);
                    if(isset($linkModel)){
                        $HtmlReturn .=  $linkModel->getLink() . ", ";

                    }
                }

            }
            return $HtmlReturn;*/
        }


        public function setRelatedRecords() {
            $signLinks = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $this->id));
            $HtmlReturn = '';
            foreach($signLinks as $link){
                if($link->modelType != "user"){
                    $linkModel = ($link->modelType)::model()->findByPk($link->modelId);
                    if(isset($linkModel)){
                        //$HtmlReturn .=  $linkModel->name . ", ";
                         $HtmlReturn .=  $linkModel->getLink() . ", ";
                    }
                }

            }
            $this->relatedRecords = $HtmlReturn;
            $this->save();
            //return $HtmlReturn;
        }


     public function autoSignForUser($posNumber){
        //first get the x2sign link
        $link = X2SignLinks::model()->findByAttributes(array(
                'envelopeId' => $this->id,
                'position' => $posNumber,
            ));
        //with the link find the signature
        $signature = X2Signature::model()->findByAttributes(array(
            'modelType' => $link->modelType,
            'modelId' => $link->modelId,
            'initial' => 0,
        ));

        $initials = X2Signature::model()->findByAttributes(array(
            'modelType' => $link->modelType,
            'modelId' => $link->modelId,
            'initial' => 1,
        ));

        //now go and look at all the fields that are assigned to the user and sign them
        $docIds = json_decode($this->signDocIds);
        foreach($docIds as $docId){
            $signDoc = X2SignDocs::model()->findByPk($docId);
            $fields = json_decode($signDoc->fieldInfo);
            if (!isset($fields)) $fields = array();
            foreach($fields as  $field){
                // check for date and auto-fill
                if($field->recip == $posNumber && (strpos($field->id, "Date") !== false)) {
                    $dateField = X2SignFields::model()->findByAttributes([
                    'envelopeId'=>$this->id,
                    'signDocId'=>$docId,
                    'fieldId'=> $field->id]);
                    if(empty($dateField))$dateField = new X2SignFields;
                    $dateField->setAttributes(array(
                        'type' => $type,
                        'signDocId' => $docId,
                        'envelopeId' => $this->id,
                        'fieldId' => $field->id,
                        'value' => date("m/d/Y", time()),
                    ), false);
                    $dateField->save();
                }
                //check to see if the field is assigned to the user and if it is a signature
                if($field->recip == $posNumber && (strpos($field->id, "Signature") !== false || strpos($field->id, "Initials") !== false)){
                    //see if there is a field for already and if not create one
                    $SignField = X2SignFields::model()->findByAttributes([
                    'envelopeId'=>$this->id,
                    'signDocId'=>$docId,
                    'fieldId'=> $field->id]);
                    if(empty($SignField))$SignField = new X2SignFields;
                    $signId =  $signature->id;
                    //check if it should be initials 
                    if(strpos($field->id, "Initials") !== false) $signId =  $initials->id;
                    $type = explode('-', $field->id)[0];

                    $SignField->setAttributes(array(
                        'type' => $type,
                        'signDocId' => $docId,
                        'envelopeId' => $this->id,
                        'fieldId' => $field->id,
                        'value' => $signId
                    ), false);
                    $SignField->save();


                }
            }

        }

        $link->signedDate = time();
        $link->save();

     }



     public function renderRelatedListing() {

            $HtmlReturn = '';
            if(empty($this->c_listing)) {
                return '';
            }else {
                //we will just generate the link by using the name and id without a look up to speed it up
                list($name, $id) = Fields::nameAndId($this->c_listing);
                $link = '<a href="https://dedupe.sydney.x2developer.com/index.php/listings2/id/' .  $id . '"><span>' . $name . '</span></a>';
                //$list = Listings2::model()->findByAttributes(array('nameId' => $this->c_listing));
                //if(isset($list)){
                //    return $list->getLink();
                //}
                //return '';
                return $link;
            }

            return $HtmlReturn;
        }


        public function laterSend(){
            $this->sent = 1;
            $this->save();
            $links = X2SignLinks::model()->findAllByAttributes(array('envelopeId' => $this->id));
            if (is_null($links))
                throw new CHttpException(400, 'Bad Request');

            $behavior = $this->attachBehavior('DocusignBehavior', new DocusignBehavior);
            $fails = $this->sendDocsEmailHelper($links, $this);
            return $fails;

        }


}

