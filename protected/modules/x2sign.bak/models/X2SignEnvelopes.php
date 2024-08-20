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
		$criteria=new CDbCriteria;
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
                default:
                    return parent::renderAttribute($fieldName, $makeLinks, $textOnly, $encode);
            }
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
               $class = array('class'=>'folder-link pseudo-link', 'data-id'=>$data->objId); 

            //needs name, url, and class
            return X2Html::link($name, $url, $class);
        }

        public function getFolderNum($assignedTo = null) {

            $folderNum = array();
            $statuses = array(self::ACTIONS_REQUIRED, self::WAITING_FOR_OTHERS, self::CANCELLED, self::COMPLETED);

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

        public function generateCompletePdf() {
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
}
