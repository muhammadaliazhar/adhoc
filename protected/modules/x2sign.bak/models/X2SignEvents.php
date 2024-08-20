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
 * @author: Justin Toyomitsu <justin@x2engine.com>
 */



Yii::import('application.models.X2List');
Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_template".
 * @package application.modules.template.models
 */
class X2SignEvents extends X2Model {

        const SENT = 1;
        const SIGNED = 2;
        const CANCELLED = 3;
        const UPDATED = 4;
        const COMPLETED = 5;

	/**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_sign_events'; }

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

    public function renderAttribute($fieldName, $makeLinks = true, $textOnly = true, $encode = true){
        switch ($fieldName) {
            case 'completeDate':
                return $this->completeDate ? Formatter::formatDate($this->completeDate) : '--';
            case 'documentId':
                $document = Media::model()->findByPk($this->documentId);
                if(!is_null($document))
                    return X2Html::link(CHtml::encode($document->fileName),
                        Yii::app()->controller->createUrl('/media/view', array('id' => $this->documentId)),
                    array());
                else
                    return X2Html::link(CHtml::encode('empty envelope'),
                        Yii::app()->controller->createUrl('media/index'),
                    array()); 
            case 'envelopeId':
                $envelope = X2SignEnvelopes::model()->findByPk($this->envelopeId);
                if(!is_null($envelope))
                    return X2Html::link(CHtml::encode($envelope->name),
                        Yii::app()->controller->createUrl('/x2sign/view', array('id' => $this->envelopeId)),
                    array());
                else
                    return X2Html::link(CHtml::encode('empty envelope'),
                        Yii::app()->controller->createUrl('x2sign/index'),
                    array());
            case 'type':
                if ($this->type == self::SENT)
                    return 'Sent';
                if ($this->type == self::SIGNED)
                    return 'Signed';
                if ($this->type == self::CANCELLED)
                    return 'Cancelled';
                if ($this->type == self::UPDATED)
                    return 'Updated';
                if ($this->type == self::COMPLETED)
                    return 'Completed';
            default:
                return parent::renderAttribute($fieldName, $makeLinks, $textOnly, $encode);
        }
    }

    public function beforeSave() {
        return parent::beforeSave();
    }

        
    public function toBin($str) {
        $str = (string) $str;
        $l = strlen($str);
        $result = '';
        for($i = 0; $i < $l; $i++){
            $result .= str_pad(decbin(ord($str[$i])), 8, "0", STR_PAD_LEFT);
        }
        return $result;
    }
}
