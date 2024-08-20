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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_template".
 * @package application.modules.template.models
 */
class Sellers2 extends X2Model {

	/**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_sellers2'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'sellers2'
			),
            'ERememberFiltersBehavior' => array(
				'class'=>'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
			'InlineEmailModelBehavior' => array(
				'class'=>'application.components.behaviors.InlineEmailModelBehavior',
			),
            'ModelConversionBehavior' => array(
                'class' => 'application.components.behaviors.ModelConversionBehavior',
            )
		));
	}

    public function beforeSave() {
        //update email so if we use it for campaings
        $this->email = $this->c_email;

        return parent::beforeSave();
    }


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
    public function search($pageSize = null, CDbCriteria $criteria = null) {
        if ($criteria === null) {
            $criteria = new CDbCriteria;
        }

        if (isset($_GET['name'])) {
            $name = $_GET['name'];
            $cond = isset($name) ? "name LIKE '%$name%'" : '';
            $criteria = new CDbCriteria(array('condition' => $cond, 'order' => 'lastUpdated DESC'));
        }

		return $this->searchBase($criteria, $pageSize);
	}

        private $quick = false;
        public function setQuick($set) {
            $this->quick = $set;
        }

        public function isQuick() {
            return $this->quick;
        }


        public function FirstNameSet(){

                $sellersSet = Sellers2::model()->findAllBySql('SELECT * FROM' .
                        ' x2_sellers2 WHERE c_firstname is NULL and name is not NULL');

                foreach($sellersSet as $sell){
                        echo $sell->name . "\n";
                        $nameParts = explode(" ", $sell->name);
                        if(isset($nameParts[0]) && !empty($sell->name) && isset($sell->name)){
                                $sell->c_firstname = $nameParts[0];
                                $sell->save();
                        }
                }
        }

/**
     * Gets a DataProvider for all the contacts in the specified list,
     * using this Contact model's attributes as a search filter
     */
    public function searchList($id, $pageSize = null) {
        $list = X2List::model()->findByPk($id);

        if (isset($list)) {
            $search = $list->queryCriteria();

            $this->compareAttributes($search);

            return new SmartActiveDataProvider('Sellers2', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC'    // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else {    //if list is not working, return all contacts
            return $this->searchBase();
        }
    }





}
