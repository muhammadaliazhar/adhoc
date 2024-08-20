<?php

/* * *********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2017 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 * ******************************************************************************** */

/**
 * @package application.modules.groups.controllers 
 */
class GroupsController extends x2base {

    public $modelClass = 'Groups';

//    public function behaviors() {
//        return array_merge(parent::behaviors(), array(
//            'MobileControllerBehavior' => array(
//                'class' => 
//                    'application.modules.mobile.components.behaviors.MobileControllerBehavior'
//            ),
//        ));
//    }

    /**
     * Filters to be used by the controller.
     * 
     * This method defines which filters the controller will use.  Filters can be
     * built in with Yii or defined in the controller (see {@link GroupsController::filterClearGroupsCache}).
     * See also Yii documentation for more information on filters.
     * 
     * @return array An array consisting of the filters to be used. 
     */
    public function filters() {
        return array(
            'clearGroupsCache - view, index', // clear the cache, unless we're doing a read-only operation here
            'setPortlets',
        );
    }

//    public function actionMobileView ($id) {
//        $model = $this->loadModel ($id);
//        $this->dataUrl = $model->getUrl ();
//        if ($this->checkPermissions($model, 'view')) {
//            $this->render (
//                $this->pathAliasBase.'views.mobile.recordView',
//                array (
//                    'model' => $model,
//                )
//            );
//        }
//    }

    public function actionIndex() {
        $groupArray = Groups::model()->findAllBySql('select * from x2_groups order by name asc');

        $groups = array();
        foreach ($groupArray as $group) {
            $childrenGroupIds = json_decode($group->childrenGroupIds);
            if ($childrenGroupIds === NULL)
                $childrenGroupIds = array();
            $groups[] = array(
                'id' => $group->id,
                'name' => $group->name,
                'childrenIds' => $childrenGroupIds,
                'children' => array(),
            );
        }

        $roots = array();

        foreach ($groups as $group) {
            $found = false;
            foreach ($groups as $check) {
                if (in_array($group['id'], $check['childrenIds'])) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $group['level'] = 0;
                $roots[] = $group;
            }
        }

        $counter = -1;
        $roots = $this->heirarchyTraverse($roots, $groups, $counter);

        $list = $this->flatten($roots, array());

        $this->render('index', array(
            'list' => $list,
        ));
    }

    public function flatten($level, $list) {
        $newList = array();
        foreach ($level as $item) {
            $group = $item;
            unset($group['children']);
            $newList[] = $group;
            $newList = array_merge($newList, $this->flatten($item['children'], $newList));
        }

        return $newList;
    }

    public function heirarchyTraverse($groupLevel, $groups, $counter) {

        $level = array();
        $counter += 1;
        foreach($groupLevel as $parent) {
            $children = array();
            $group = $parent;
            foreach($parent['childrenIds'] as $childId) {
                foreach($groups as $group) {
                    if ($group['id'] !== $childId)
                        continue;

                    $children[] = $group;
                }
            }
            $children = $this->heirarchyTraverse($children, $groups, $counter);
            $parent['level'] = $counter;
            $parent['children'] = $children;
            $level[] = $parent;
        }

        return $level;
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $userLinks = GroupToUser::model()->findAllByAttributes(array('groupId' => $id));
	$model = X2Model::model('Groups')->findByPk($id);
        $str = "";
        $childrenModelsIds = array();
        foreach ($userLinks as $userLink) {
            $user = X2Model::model('User')->findByPk($userLink->userId);
            if (isset($user)) {
                $str.=$user->username . ", ";
            }
        }

        $str = substr($str, 0, -2);
        $users = User::getUserLinks($str);

        // add group to user's recent item list
        User::addRecentItem('g', $id, Yii::app()->user->getId());

        // Generate children list
        $childrenModelsIds = isset($model->childrenGroupIds) ? json_decode($model->childrenGroupIds) : array();
        $childrenModelsArray = array();
        if (isset($childrenModelsIds) && !empty($childrenModelsIds)) {
            foreach ($childrenModelsIds as $childId) {
                $group = Groups::model()->findByPk($childId);
                if (isset($group) && !empty($group))
                    $childrenModelsArray[] = $group;
            }
        }

        //just take the first parent
        if (!empty($childrenModelsArray)) {
            $this->render('view', array(
                'model' => $this->loadModel($id),
                'childrenModels' => $childrenModelsArray,
                'users' => $users,
            ));
        } else {
            $this->render('view', array(
                'model' => $this->loadModel($id),
                'users' => $users,
            ));
        }
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new Groups;
        $users = User::getNames();
        $children = null;
        unset($users['admin']);
        unset($users['']);
        $childrenSelected = array();
        if (isset($_POST['Groups'])) {
            $model->attributes = $_POST['Groups'];
            if (isset($_POST['users']))
                $users = $_POST['users'];
            else
                $users = array();
            if (isset($_POST['children']))
                $children = $_POST['children'];
            else
                $children = null;
            if (!empty($children)) {
                $childrenArray = array();
                foreach ($children as &$child) {
                    $childRecord = Groups::model()->findByPk(array('id' => (int) $child));
                    if (isset($childRecord)) {
                        array_push($childrenArray, $childRecord->id);
                    }
                }
                $model->childrenGroupIds = json_encode($childrenArray);
            } else {
                $model->childrenGroupIds = null;
            }
            $childrenGroup = json_decode($model->childrenGroupIds);
            if (!empty($childrenGroup)) {
                foreach ($childrenGroup as &$childGroup) {
                    $childGroup = Groups::model()->findByPk((int) $childGroup);
                    $childrenSelected[] = $childGroup->id;
                }
            }
            if ($model->save()) {
                if (!empty($users)) {
                    foreach ($users as $user) {
                        $link = new GroupToUser;
                        $link->groupId = $model->id;
                        $userRecord = User::model()->findByAttributes(array('username' => $user));
                        if (isset($userRecord)) {
                            $link->userId = $userRecord->id;
                            $link->username = $userRecord->username;
                            if (!empty($children)) {
                                $childrenArray = array();
                                foreach ($children as &$child) {
                                    $childRecord = Groups::model()->findByPk(array('id' => (int) $child));
                                    if (isset($childRecord)) {
                                        array_push($childrenArray, $childRecord->id);
                                    }
                                }
                                $link->childrenGroupIds = json_encode($childrenArray);
                            }
                            $link->save();
                        }
                    }
                } else if (!empty($children)) {
                    $link = new GroupToUser;
                    $link->groupId = $model->id;
                    $childrenArray = array();
                    foreach ($children as &$child) {
                        $childRecord = Groups::model()->findByPk(array('id' => (int) $child));
                        if (isset($childRecord)) {
                            array_push($childrenArray, $childRecord->id);
                        }
                    }
                    $link->childrenGroupIds = json_encode($childrenArray);

                    $link->save();
                }
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        if (empty($childrenSelected)) {
            array_push($childrenSelected, '');
        }
        $this->render('create', array(
            'model' => $model,
            'users' => $users,
            'childrenSelected' => $childrenSelected,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users = User::getNames();
        $selected = array();
        $childrenSelected = array();
        $links = GroupToUser::model()->findAllByAttributes(array('groupId' => $id));
        $childrenGroup = json_decode($model->childrenGroupIds);
        if (!empty($childrenGroup)) {
            foreach ($childrenGroup as &$childGroup) {
                $childGroup = Groups::model()->findByPk((int) $childGroup);
                if ($childGroup)
                    $childrenSelected[] = $childGroup->id;
            }
        }
        foreach ($links as $link) {
            $user = User::model()->findByPk($link->userId);
            //$childrenGroup = json_decode($link->childrenGroupIds);
            if (isset($user)) {
                $selected[] = $user->username;
            }
            /* if (!empty($childrenGroup))
              foreach ($childrenGroup as &$childGroup) {
              $childGroup = Groups::model()->findByPk((int) $childGroup);
              $childrenSelected[] = $childGroup->name;
              } */
        }
        unset($users['admin']);
        unset($users['']);
        $children = null;
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Groups'])) {
            $userLinks = GroupToUser::model()->findAllByAttributes(array('groupId' => $model->id));
            foreach ($userLinks as $userLink) {
                $userLink->delete();
            }
            $model->attributes = $_POST['Groups'];
            if (isset($_POST['users']))
                $users = $_POST['users'];
            else
                $users = array();
            if (isset($_POST['children']))
                $children = $_POST['children'];
            else
                $children = array();
            if (!empty($children)) {
                $childrenArray = array();
                foreach ($children as &$child) {
                    $childRecord = Groups::model()->findByPk(array('id' => (int) $child));
                    if (isset($childRecord)) {
                        array_push($childrenArray, $childRecord->id);
                    }
                }
                $model->childrenGroupIds = json_encode($childrenArray);
            } else {
                $model->childrenGroupIds = json_encode($children);
            }
            if ($model->save()) {
                $changeMade = false;
                if (!empty($users)) {
                    foreach ($users as $user) {
                        $link = new GroupToUser;
                        $link->groupId = $model->id;
                        $userRecord = User::model()->findByAttributes(array('username' => $user));
                        if (isset($userRecord)) {
                            $link->userId = $userRecord->id;
                            $link->username = $userRecord->username;
                            if (!empty($children)) {
                                $childrenArray = array();
                                foreach ($children as &$child) {
                                    $childRecord = Groups::model()->findByPk(array('id' => (int) $child));
                                    if (isset($childRecord)) {
                                        array_push($childrenArray, $childRecord->id);
                                    }
                                }
                                $link->childrenGroupIds = json_encode($childrenArray);
                            }
                        }
                        $test = GroupToUser::model()->findByAttributes(array('groupId' => $model->id, 'userId' => $userRecord->id));
                        if (!isset($test)) {
                            $link->save();
                            $changeMade = true;
                        }
                    }
                } else if (!empty($children)) {
                    $link = new GroupToUser;
                    $link->groupId = $model->id;
                    $childrenArray = array();
                    foreach ($children as &$child) {
                        $childRecord = Groups::model()->findByPk(array('id' => (int) $child));
                        if (isset($childRecord)) {
                            array_push($childrenArray, $childRecord->id);
                        }
                    }
                    $link->childrenGroupIds = json_encode($childrenArray);

                    $link->save();
                }
                if ($changeMade)
                    Yii::app()->authCache->clear();
                $selected = array();
                $childrenSelected = array();
                $links = GroupToUser::model()->findAllByAttributes(array('groupId' => $id));
                $childrenGroup = json_decode($model->childrenGroupIds);
                if (!empty($childrenGroup)) {
                    foreach ($childrenGroup as &$childGroup) {
                        $childGroup = Groups::model()->findByPk((int) $childGroup);
                        $childrenSelected[] = $childGroup->id;
                    }
                }
                foreach ($links as $link) {
                    $user = User::model()->findByPk($link->userId);
                    //$childrenGroup = json_decode($link->childrenGroupIds);
                    if (isset($user)) {
                        $selected[] = $user->username;
                    }
                    /* if (!empty($childrenGroup))
                      foreach ($childrenGroup as &$childGroup) {
                      $childGroup = Groups::model()->findByPk((int) $childGroup);
                      $childrenSelected[] = $childGroup->name;
                      } */
                }
                $this->render('update', array(
                    'model' => $model,
                    'users' => $users,
                    'selected' => $selected,
                    'childrenSelected' => $childrenSelected,
                ));
            }
        }
        if (empty($childrenSelected)) {
            array_push($childrenSelected, '');
        }
        $this->render('update', array(
            'model' => $model,
            'users' => $users,
            'selected' => $selected,
            'childrenSelected' => $childrenSelected,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $links = GroupToUser::model()->findAllByAttributes(array('groupId' => $id));
            foreach ($links as $link) {
                $link->delete();
            }
            $contacts = X2Model::model('Contacts')->findAllByAttributes(array('assignedTo' => $id));
            foreach ($contacts as $contact) {
                $contact->assignedTo = 'Anyone';
                $contact->save();
            }
            $newChildrenGroupIds = array();
            $groupModel = $this->loadModel($id);
            $arrayOfChildrenGroupIds = (array) json_decode($groupModel->childrenGroupIds);
            foreach ($arrayOfChildrenGroupIds as $childrenGroupId) {
                array_push($newChildrenGroupIds, $childrenGroupId);
            }
            $this->loadModel($id)->delete();
            $match = addcslashes((string) $id, '%_'); // escape LIKE's special characters
            $q = new CDbCriteria(array(
                'condition' => "childrenGroupIds LIKE :match", // no quotes around :match
                'params' => array(':match' => "%\"$match\"%")  // Aha! Wildcards go here
            ));

            $parentGroups = Groups::model()->findAll($q);     // works!
            foreach ($parentGroups as $parentGroup) {
                $parentGroupModel = $this->loadModel($parentGroup->id);
                $childrenGroupIds = (array) json_decode($parentGroupModel->childrenGroupIds);
                foreach ($childrenGroupIds as $childrenGroupId) {
                    if ((string) $childrenGroupId !== (string) $id &&!in_array($childrenGroupId, $newChildrenGroupIds)) {
                        array_push($newChildrenGroupIds, $childrenGroupId);
                    }
                }
                $parentGroupModel->childrenGroupIds = json_encode($newChildrenGroupIds);
                $parentGroupModel->save();
            }
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        } else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Lists all models.
     */
/*
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('Groups');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }
*/

    public function actionGetGroups() {
        $checked = false;
        if (isset($_POST['checked'])) // coming from a group checkbox?
            $checked = json_decode($_POST['checked']);
        elseif (isset($_POST['group']))
            $checked = true;

        $id = null;
        if (isset($_POST['field']))
            $id = $_POST['field'];

        $options = array();
        if ($checked) { // group checkbox checked, return list of groups
            echo CHtml::listOptions($id, Groups::getNames(), $options);
        } else { // group checkbox unchecked, return list of user names
            $users = User::getNames();
            if (!in_array($id, array_keys($users)))
                $id = Yii::app()->user->getName();

            echo CHtml::listOptions($id, $users, $options);
        }
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'groups-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * A filter to clear the groups cache.
     * 
     * This method clears the cache whenever the groups controller is accessed.
     * Caching improves performance throughout the app, but will occasionally 
     * need to be cleared. Keeping this filter here allows for cleaning up the
     * cache when required.
     * 
     * @param type $filterChain The filter chain Yii is currently acting on.
     */
    public function filterClearGroupsCache($filterChain) {
        $filterChain->run();
        Yii::app()->cache->delete('user_groups');
        Yii::app()->cache->delete('user_roles');
    }

    public function actionGetItems($term) {
        LinkableBehavior::getItems($term);
    }

    /**
     * Create a menu for Groups
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Group = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'delete',
         * );
         */
        $menuItems = array(
            array(
                'name' => 'index',
                'label' => Yii::t('groups', '{group} List', array(
                    '{group}' => $Group,
                )),
                'url' => array('index')
            ),
            array(
                'name' => 'create',
                'label' => Yii::t('groups', 'Create {group}', array(
                    '{group}' => $Group,
                )),
                'url' => array('create')
            ),
            array(
                'name' => 'view',
                'label' => Yii::t('groups', 'View'),
                'url' => array('view', 'id' => $modelId)
            ),
            array(
                'name' => 'edit',
                'label' => Yii::t('groups', 'Edit {group}', array(
                    '{group}' => $Group,
                )),
                'url' => array('update', 'id' => $modelId)
            ),
            array(
                'name' => 'delete',
                'label' => Yii::t('groups', 'Delete {group}', array(
                    '{group}' => $Group,
                )),
                'url' => '#',
                'linkOptions' => array(
                    'submit' => array('delete', 'id' => $modelId),
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'))
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
