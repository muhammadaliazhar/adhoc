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

class SearchlistingsController extends x2base {

    public $modelClass = 'Searchlistings';

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
        $type='searchlistings';
        $model=$this->loadModel($id);
        parent::view($model, $type);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new Searchlistings;
        $users=User::getNames();

        if(isset($_POST['Searchlistings'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['Searchlistings']);

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

        if(isset($_POST['Searchlistings'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['Searchlistings']);
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
     * Lists all models.
     */
    public function actionIndex() {
        $model=new Listings2('search');

        $criteria = new CDbCriteria;
        $this->compareAttributes($criteria, $model);
        $sort = new SmartSort(
                'listings2', 'listings2');
        $sort->multiSort = false;
        //$sort->attributes = $this->getSort();
        $sort->defaultOrder = 't.lastUpdated DESC, t.id DESC';

        $listingDP  = new SmartActiveDataProvider('Listings2', array(
            'sort'=>$sort,
            'pagination'=>array(
                'pageSize'=>Profile::getResultsPerPage(),
            ),
            'criteria' => $criteria,
        ));
        $this->render('index', array(
            'model'=>$model,
            'listingDP'=>$listingDP
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model=new Searchlistings('search');
        $this->render('admin', array('model'=>$model));
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='searchlistings-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    protected function compareBoolean($data) {
        if (is_null($data) || $data == '')
            return null;

        // default to true unless recognized as false
        return in_array(
                        mb_strtolower(
                                trim($data)), array(0, 'f', 'false', Yii::t('actions', 'No')), true) ? 0 : 1;
    }

    protected function compareAssignment($data) {
        if (is_null($data) || $data == '')
            return null;
        $userNames = Yii::app()->db->createCommand()
                ->select('username')
                ->from('x2_users')
                ->where(array('like', 'CONCAT(firstName," ",lastName)', "%$data%"))
                ->queryColumn();
        $groupIds = Yii::app()->db->createCommand()
                ->select('id')
                ->from('x2_groups')
                ->where(array('like', 'name', "%$data%"))
                ->queryColumn();

        return (count($groupIds) + count($userNames) == 0) ? -1 : $userNames + $groupIds;
    }

   protected function compareDropdown($ddId, $value) {
        if (is_null($value) || $value == '') {
            return null;
        }
        $dropdown = X2Model::model('Dropdowns')->findByPk($ddId);
        $multi = $dropdown->multi;
        if (isset($dropdown)) {
            $index = $dropdown->getDropdownIndex($ddId, $value, $multi);
            if (!is_null($index)) {
                return $index;
            } else {
                return -1;
            }
        }
        return -1;
    } 

    protected function compareAttribute(&$criteria, $field, $model) {
        $fieldName = $field['fieldName'];
        switch ($field['type']) {
            case 'boolean':
                $criteria->compare(
                        't.' . $fieldName, $this->compareBoolean($model->$fieldName), true);
                break;
            case 'assignment':
                $assignmentCriteria = new CDbCriteria;
                $assignmentVal = $this->compareAssignment($model->$fieldName);

                if ($field->linkType === 'multiple' && $this->$fieldName) {
                    if (!is_array($assignmentVal))
                        $assignmentVal = array();
                    $assignmentVal = array_map(function ($val) {
                        return preg_quote($val);
                    }, $assignmentVal);
                    if (strlen($this->$fieldName) && strncmp(
                                    "Anyone", ucfirst($this->$fieldName), strlen($this->$fieldName)) === 0) {

                        $assignmentVal[] = 'Anyone';
                    }
                    $assignmentRegex = '(^|, )(' . implode('|', $assignmentVal) . ')' .
                            (in_array('Anyone', $assignmentVal) ? '?' : '') . '(, |$)';

                    $assignmentParamName = CDbCriteria::PARAM_PREFIX . CDbCriteria::$paramCount;
                    $criteria->params[$assignmentParamName] = $assignmentRegex;
                    CDbCriteria::$paramCount++;
                    $criteria->addCondition(
                            't.' . $fieldName . ' REGEXP BINARY ' . $assignmentParamName);
                } else {
                    $assignmentCriteria->compare(
                            't.' . $fieldName, $assignmentVal, true);
                    if (strlen($model->$fieldName) && strncmp(
                                    "Anyone", ucfirst($model->$fieldName), strlen($model->$fieldName)) === 0) {
                                    
                                            $assignmentCriteria->compare('t.' . $fieldName, 'Anyone', false, 'OR');
                        $assignmentCriteria->addCondition('t.' . $fieldName . ' = ""', 'OR');
                    }
                }
                $criteria->mergeWith($assignmentCriteria);
                break;
            case 'dropdown':
                $dropdownVal = $this->compareDropdown($field->linkType, $model->$fieldName);
                if (is_array($dropdownVal)) {
                    foreach ($dropdownVal as $val) {
                        //code to deal with special french words
                        $val = json_encode($val);
                        $val = substr($val, 1 , -1);

                        $dropdownRegex = '(^|((\\[|,)"))' . preg_quote($val) . '(("(,|\\]))|$)';
                        $dropdownParamName = CDbCriteria::PARAM_PREFIX . CDbCriteria::$paramCount;
                        $criteria->params[$dropdownParamName] = $dropdownRegex;
                        CDbCriteria::$paramCount++;
                        $criteria->addCondition(
                                't.' . $fieldName . ' REGEXP BINARY ' . $dropdownParamName);
                    }
                } else {
                    $criteria->compare('t.' . $fieldName, $dropdownVal, false);
                }
                break;
            case 'date':
            case 'dateTime':
                if (!empty($model->$fieldName)) {
                    // get operator and convert date string to timestamp
                    $retArr = $this->unshiftOperator($this->$fieldName);

                    $operator = $retArr[0];
                    $timestamp = Formatter::parseDate($retArr[1]);
                    if (!$timestamp) {
                        // if date string couldn't be parsed, it's better to display no results
                        // than non-empty incorrect results (which could result in bad mass updates
                        // or deletes)
                        $criteria->addCondition('FALSE');
                    } else if ($operator === '=' || $operator === '') {
                      $criteria->addBetweenCondition(
                                't.' . $fieldName, $timestamp, $timestamp + 60 * 60 * 24);
                    } else {
                        $value = $operator . $timestamp;
                        $criteria->compare('t.' . $fieldName, $value);
                    }
                }
                break;
            case 'phone':
            // $criteria->join .= ' RIGHT JOIN x2_phone_numbers ON (x2_phone_numbers.itemId=t.id AND x2_tags.type="Contacts" AND ('.$tagConditions.'))';
            default:
                $criteria->compare('t.' . $fieldName, $model->$fieldName, true);
        }
    }

    protected function compareAttributes(&$criteria, $model) {
        foreach ($model->getFields(true) as &$field) {
            $this->compareAttribute($criteria, $field, $model);
        }
    }
}
