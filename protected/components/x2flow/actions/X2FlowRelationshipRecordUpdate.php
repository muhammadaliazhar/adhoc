<?php

/* * *********************************************************************************
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
 * ******************************************************************************** */

/**
 * X2FlowAction that updates a record's Relationship
 *
 * @package application.components.x2flow.actions
 */
class X2FlowRelationshipRecordUpdate extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Update Related Records';
    public $info = 'Updates a Record\'s Related Records';

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        return array_merge(parent::paramRules(), array(
            'title' => $this->title,
            'info' => Yii::t('studio', $this->info),
            'modelClass' => 'modelClass',
            'options' => array(
                array('name' => 'attributes'),
                array(
                    'name' => 'headers',
                    'type' => 'attributes',
                    'optional' => 1
                ),
                array(
                    'name' => 'modelClass', 'label' => Yii::t('studio', 'Record Type'),
                    'type' => 'dropdown',
                    'options' => X2Flow::getModelTypes(true)
                ),
            /* array(
              'name' => 'createRelationship',
              'label' =>
              Yii::t('studio', 'Create Relationship') .
              '&nbsp;' .
              X2Html::hint2(
              Yii::t('app', 'Check this box if you want a new relationship to be ' .
              'established between the record created by this action and the ' .
              'record that triggered the flow.')),
              'type' => 'boolean',
              'defaultVal' => false,
              ), */
            ),
        ));
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        $classPicked = $this->config['modelClass'];
        $modelPicked = new $classPicked;
        $conditions = $this->getModelAttributes($modelPicked, $this->config['conditions'], $params);
        $modelPassed = $params['model'];

        // make sure this is a valid model type
        if (!is_subclass_of($this->config['modelClass'], 'X2Model')) {
            return array(false, "");
        }
        if (!isset($this->config['attributes']) || empty($this->config['attributes'])) {
            return array(
                false,
                Yii::t('studio', "Flow item configuration error: No attributes added"));
        }
        
        // Kludge for getting all related records of the chosen model class
        $firstSetOfRelationshipModels = array();
        $secondSetOfRelationshipModels = array();
        $firstSetOfRelationshipsFromPassedModel = Relationships::model()->findAll('firstType=:modelClassString AND secondType=:modelPassed AND secondId=:modelPassedID', array(':modelClassString' => $classPicked, 'modelPassed' => get_class($modelPassed), 'modelPassedID' => $modelPassed->id));
        if (!empty($firstSetOfRelationshipsFromPassedModel))
            foreach ($firstSetOfRelationshipsFromPassedModel as $relationshipWithModel) {
                $modelsToBeChanged = $classPicked::model()->findByAttributes(array_merge(array('id'=>$relationshipWithModel->firstId),$conditions));
                if (!empty($modelsToBeChanged))
                    $firstSetOfRelationshipModels[] = $modelsToBeChanged;
            }
        $secondSetOfRelationshipsFromPassedModel = Relationships::model()->findAll('secondType=:modelClassString AND firstType=:modelPassed AND firstId=:modelPassedID', array(':modelClassString' => $classPicked, 'modelPassed' => get_class($modelPassed), 'modelPassedID' => $modelPassed->id));
        if (!empty($secondSetOfRelationshipsFromPassedModel))
            foreach ($secondSetOfRelationshipsFromPassedModel as $relationshipWithModel) {
                $modelsToBeChanged = $classPicked::model()->findByAttributes(array_merge(array('id'=>$relationshipWithModel->secondId),$conditions));
                if (!empty($modelsToBeChanged))
                    $secondSetOfRelationshipModels[] = $modelsToBeChanged;
            }
        $relationshipsOfPassedModel = array_merge($firstSetOfRelationshipModels,$secondSetOfRelationshipModels);
        
        $updatedRecordLinks = array();
        if (!empty($relationshipsOfPassedModel)) {
            foreach ($relationshipsOfPassedModel as $model) {
                if ($this->setModelAttributes($model, $this->config['attributes'], $params) && 
                        is_subclass_of($model, 'X2Model') &&
                        $model->save()) 
		{
			if (get_class($modelPassed) == "Contacts" && $classPicked == "Inquiries" && $model->assignedTo == "Anyone") {
				$model->assignedTo = $modelPassed->assignedTo;	
				$model->save();
			}
			if (get_class($modelPassed) == "Listings2" && $classPicked == "Inquiries" && $model->c_listing_owner__c == "Anyone") {
				$model->c_listing_owner__c = $modelPassed->assignedTo;	
				$model->save();
			}
	
                    $updatedRecordLinks[] = Yii::t('studio', 'View updated record: ') . $model->getLink();
                } else {
                    $errors = $model->getErrors();
                    return array(false, array_shift($errors));
                }
            }
            return array(
                true,
                implode(", ",$updatedRecordLinks));
        } else {
            return array(false, "");
        }
    }

}
