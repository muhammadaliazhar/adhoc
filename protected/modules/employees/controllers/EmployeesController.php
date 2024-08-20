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

class EmployeesController extends x2base {

    public $modelClass = 'Employees';

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
        //LinkableBehavior::getItems ($term);
        //allow anyone to see all employee records
        $modelClass = Yii::app()->controller->modelClass;
        $model = X2Model::model($modelClass);
        $valueAttr='name';
        $nameAttr='id';
        if (isset($model)) {
            $modelClass::checkThrowAttrError (array ($valueAttr, $nameAttr));
            $tableName = $model->tableName();
            $qterm = $term . '%';
            $params = array (
                ':qterm' => $qterm,
            );
            $sql = "
                SELECT $nameAttr as id, $valueAttr as value 
                FROM " . $tableName . " as t
                WHERE $valueAttr LIKE :qterm ";
            //if ($model->asa ('permissions')) {
            //    list ($accessCond, $permissionsParams) = $model->getAccessSQLCondition ();
            //    $sql .= ' AND '.$accessCond;
            //    $params = array_merge ($params, $permissionsParams);
            //}

            $sql .= "ORDER BY $valueAttr ASC";
            $command = Yii::app()->db->createCommand($sql);
            $result = $command->queryAll(true, $params);
            echo CJSON::encode($result);
        }
        Yii::app()->end();

    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $type='employees';
        $model=$this->loadModel($id);
        //check for one user 
        //if($id == 242) $model->setSalesValues();
	//set up so employee get's the last name from the user 	
	if(isset($model->c_user__c)){
		$user=X2Model::model('User')->findByAttributes(array("username" => $model->c_user__c));
		if(isset($user)){
			//$model->c_lastname = $user->lastName;
			//$model->save();

	
		}
	}
        parent::view($model, $type);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new Employees;
        $users=User::getNames();

        if(isset($_POST['Employees'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['Employees']);

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

        if(isset($_POST['Employees'])) {
            $temp = $model->attributes;
            $model->setX2Fields($_POST['Employees']);
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
        $model=new Employees('search');
        $this->render('index', array('model'=>$model));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model=new Employees('search');
        $this->render('admin', array('model'=>$model));
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='employees-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
