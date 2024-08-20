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

/**
 * This is the model class for table "x2_form_versions".
 *
 * @package application.models
 * @property integer $id
 * @property string $model
 * @property string $version
 * @property string $layout
 * @property boolean $defaultView
 * @property boolean $defaultForm
 * @property integer $createDate
 * @property integer $lastUpdated
 */
class FormLayout extends CActiveRecord {

	public static $scenarios = array('Default','Inline',  'Follow-Up');

	/**
	 * Returns the static model of the specified AR class.
	 * @return FormVersions the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_form_layouts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('version, model', 'length', 'max'=>250),
			array('createDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('defaultView, defaultForm', 'boolean'),
			array('layout', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, version, model, layout, defaultView, defaultForm, createDate, lastUpdated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'version' => 'Version',
			'model' => 'Model Name',
			'layout' => 'Layout',
			'defualtView' => 'Default View',
			'defualtForm' => 'Default Form',
			'createDate' => 'Create Date',
			'lastUpdated' => 'Last Updated',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('version',$this->version,true);
		$criteria->compare('defaultView',$this->defaultView,true);
		$criteria->compare('defaultForm',$this->defaultForm,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('lastUpdated',$this->lastUpdated,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

    /**
     * Returns fieldName, fieldLabel pairs for all fields for which the user has edit rights and
     * which are present in the layout.
     */
    public function getEditableFieldsInLayout ($modelName) {
        $editableFieldsFieldInfo = X2Model::model ($modelName)->getEditableFieldNames (false);

        // Construct criteria for finding the right form layout.
        $attributes = array('model'=>ucfirst($modelName),'defaultForm'=>1);

        $layout = self::model()->findByAttributes($attributes);

        $layoutData = json_decode((isset($layout)? $layout->layout : X2Model::getDefaultFormLayout($modelName)),true);

        $editableFieldsInLayout = array ();
	    if(isset($layoutData['sections']) && count($layoutData['sections']) > 0) {
		    foreach($layoutData['sections'] as &$section) {
				foreach($section['rows'] as &$row) {
					if(isset($row['cols'])) {
						foreach($row['cols'] as &$col) {
							if(isset($col['items'])) {
								foreach($col['items'] as &$item) {

                                    if(isset($item['name'],$item['labelType'],$item['readOnly'])) {
                                        $fieldName = preg_replace('/^formItem_/u','',$item['name']);

                                        if(in_array (
                                            $fieldName, array_keys ($editableFieldsFieldInfo))) {

                                            $editableFieldsInLayout[$fieldName] =
                                                $editableFieldsFieldInfo[$fieldName];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $editableFieldsInLayout;
    }

    /**
     * Helper method to unset all defaultView or defaultForm flags
     * @param string $type Form type, either 'view' or 'form', or both if argument is omitted
     * @param string Model type to unset flags for
     */
    public static function clearDefaultFormLayouts($type = null, $model = null, $scenario = null) {
        // Construct attributes to select form layouts
        $attr = array('model' => $model);
        if ($scenario)
            $attr['scenario'] = $scenario;
        if ($type === 'view')
            $attr['defaultView'] = 1;
        else if ($type === 'form')
            $attr['defaultForm'] = 1;
        $layouts = FormLayout::model()->findAllByAttributes ($attr);

        foreach ($layouts as &$layout) {
            if ($type === 'view')
                $layout->defaultView = false;
            else if ($type === 'form')
                $layout->defaultForm = false;
            else
                $layout->defaultView = $layout->defaultForm = false;
            $layout->save();
        }
    }


    /**
     * @return array all standard comparison operators
     */
    public static function getFieldComparisonOptions() {
        return array(
            '=' => Yii::t('app', 'equals'),
            '>' => Yii::t('app', 'greater than'),
            '<' => Yii::t('app', 'less than'),
            '>=' => Yii::t('app', 'greater than or equal to'),
            '<=' => Yii::t('app', 'less than or equal to'),
            '<>' => Yii::t('app', 'not equal to'),
            'list' => Yii::t('app', 'in list'),
            'notList' => Yii::t('app', 'not in list'),
            'empty' => Yii::t('app', 'empty'),
            'notEmpty' => Yii::t('app', 'not empty'),
            'contains' => Yii::t('app', 'contains'),
            'noContains' => Yii::t('app', 'does not contain'),
            'changed' => Yii::t('app', 'changed'),
            'before' => Yii::t('app', 'before'),
            'after' => Yii::t('app', 'after'),
        );
    }    
    
        public static $genericConditions = array(
        'attribute' => 'Compare Attribute',
        'workflow_status' => 'Process Status',
        'current_user' => 'Current User',
        'month' => 'Current Month',
        'day_of_week' => 'Day of Week',
        'day_of_month' => 'Day of Month',
        'time_of_day' => 'Time of Day',
        'current_time' => 'Current Time',
        'user_active' => 'User Logged In',
        'on_list' => 'On List',
        'has_tags' => 'Has Tags',
        'email_open' => 'Email Opened',
    );
    public static function getGenericConditions() {
        return array_map(function($term) {
            return Yii::t('studio', $term);
        }, self::$genericConditions);
    }
    
    
    
        /**
     * @param Array $condition
     * @param Array $params
     * @return bool true for success, false otherwise
     */
    public static function checkCondition($condition, &$params) {
        $model = isset($params['model']) ? $params['model'] : null;
        $operator = isset($condition['operator']) ? $condition['operator'] : '=';
        // $type = isset($condition['type'])? $condition['type'] : null;
        $value = isset($condition['value']) ? $condition['value'] : null;
        // default to a doing basic value comparison
        if (isset($condition['name']) && $condition['type'] === '') {
            if (!isset($params[$condition['name']])) {
                return false;
            }
            return self::evalComparison($params[$condition['name']], $operator, $value);
        }
        switch ($condition['type']) {
            case 'attribute':
                if (!isset($condition['name'], $model)) {
                    return false;
                }
                $attr = &$condition['name'];
                if (null === $field = $model->getField($attr)) {
                    return false;
                }
                if ($operator === 'changed') {
                    return $model->attributeChanged($attr);
                }
                if ($field->type === 'link') {
                    list ($attrVal, $id) = Fields::nameAndId($model->getAttribute($attr));
                } else {
                    $attrVal = $model->getAttribute($attr);
                }
                return self::evalComparison(
                                $attrVal, $operator, X2Flow::parseValue($value, $field->type, $params), $field);
            case 'current_user':
                return self::evalComparison(Yii::app()->user->getName(), $operator, X2Flow::parseValue($value, 'assignment', $params));
            case 'month':
                return self::evalComparison((int) date('n'), $operator, $value);    // jan = 1, dec = 12
            case 'day_of_month':
                return self::evalComparison((int) date('j'), $operator, $value); // 1 through 31
            case 'day_of_week':
                return self::evalComparison((int) date('N'), $operator, $value); // monday = 1, sunday = 7
            case 'time_of_day':    // - mktime(0,0,0)
                return self::evalComparison(time(), $operator, X2Flow::parseValue($value, 'time', $params)); // seconds since midnight
            case 'current_time':
                return self::evalComparison(time(), $operator, X2Flow::parseValue($value, 'dateTime', $params));
            case 'user_active':
                return CActiveRecord::model('Session')->exists(
                                'user=:user AND status=1', array(
                            ':user' => X2Flow::parseValue($value, 'assignment', $params)));
            case 'on_list':
                if (!isset($model, $value)) {
                    return false;
                }
                $value = X2Flow::parseValue($value, 'link');
                // look up specified list
                if (is_numeric($value)) {
                    $list = CActiveRecord::model('X2List')->findByPk($value);
                } else {
                    $list = CActiveRecord::model('X2List')->findByAttributes(
                            array('name' => $value));
                }
                return ($list !== null && $list->hasRecord($model));
            case 'has_tags':
                if (!isset($model, $value))
                    return false;
                $tags = X2Flow::parseValue($value, 'tags');
                return $model->hasTags($tags, 'AND');
                return false;
            case 'email_open':
                if (isset($params['sentEmails'], $params['sentEmails'][$value])) {
                    $trackEmail = TrackEmail::model()->findByAttributes(array('uniqueId' => $params['sentEmails'][$value]));
                    return $trackEmail && !is_null($trackEmail->opened);
                }
                return false;
        }
        return false;
        // foreach($condition as $key = >$value) {
        // Record attribute (=, <, >, <>, in list, not in list, empty, not empty, contains)
        // Linked record attribute (eg. a contact's account has > 30 employees)
        // Current user
        // Current time (day of week, hours, etc)
        // Current time in record's timezone
        // Is user X logged in
        // Workflow status (in workflow X, started stage Y, completed Y, completed all)
        // }
    }
    protected static function parseArray($operator, $value) {
        $expectsArray = array('list', 'notList', 'between');
        // $value needs to be a comma separated list
        if (in_array($operator, $expectsArray, true) && !is_array($value)) {
            $value = explode(',', $value);
            $len = count($value);
            for ($i = 0; $i < $len; $i++) {
                // loop through the values, trim and remove empty strings
                if (($value[$i] = trim($value[$i])) === '')
                    unset($value[$i]);
            }
        }
        return $value;
    }
    /**
     * @param mixed $subject if applicable, the value to compare $subject with (value of model 
     *  attribute)
     * @param string $operator the type of comparison to be used
     * @param mixed $value the value being analyzed (specified in config menu)
     * @return boolean
     */
    public static function evalComparison($subject, $operator, $value = null, Fields $field = null) {
        $value = self::parseArray($operator, $value);
        switch ($operator) {
            case '=':
                // check for multiselect dropdown
                if ($field && $field->type === 'dropdown') {
                    $dropdown = $field->getDropdown();
                    if ($dropdown && $dropdown->multi) {
                        $subject = StringUtil::jsonDecode($subject, false);
                        AuxLib::coerceToArray($subject);
                        AuxLib::coerceToArray($value);
                        return $subject === $value;
                    }
                    // check for muti-assignment field
                } else if ($field && $field->type === 'assignment' &&
                        $field->linkType === 'multiple') {
                    $subject = explode(Fields::MULTI_ASSIGNMENT_DELIM, $subject);
                    AuxLib::coerceToArray($subject);
                    AuxLib::coerceToArray($value);
                    return $subject === $value;
                }
                // this case occurs when dropdown or assignment fields are changed from multiple
                // to single selection, and flow conditions are left over from before the change 
                // was made
                if (is_array($value)) {
                    AuxLib::coerceToArray($subject);
                }
                return $subject == $value;
            case '>':
                return $subject > $value;
            case '<':
                return $subject < $value;
            case '>=':
                return $subject >= $value;
            case '<=':
                return $subject <= $value;
            case 'between':
                if (count($value) !== 2)
                    return false;
                return $subject >= min($value) && $subject <= max($value);
            case '<>':
            case '!=':
                return $subject != $value;
            case 'notEmpty':
                return $subject !== null && $subject !== '';
            case 'empty':
                return $subject === null || trim($subject) === '';
            case 'list':
                if (count($value) === 0)    // if the list is empty,
                    return false;                                // A isn't in it
                foreach ($value as &$val)
                    if ($subject == $val)
                        return true;
                return false;
            case 'notList':
                if (count($value) === 0)    // if the list is empty,
                    return true;                                // A isn't *not* in it
                foreach ($value as &$val)
                    if ($subject == $val)
                        return false;
                return true;
            case 'noContains':
                return stripos($subject, $value) === false;
            case 'contains':
            default:
                return stripos($subject, $value) !== false;
        }
    }
    protected static $_tokenChars = array(
        ',' => 'COMMA',
        '{' => 'OPEN_BRACKET',
        '}' => 'CLOSE_BRACKET',
        '+' => 'ADD',
        '-' => 'SUBTRACT',
        '*' => 'MULTIPLY',
        '/' => 'DIVIDE',
        '%' => 'MOD',
            // '(' => 'OPEN_PAREN',
            // ')' => 'CLOSE_PAREN',
    );
    
    protected static $_tokenRegex = array(
        '\d+\.\d+\b|^\.?\d+\b' => 'NUMBER',
        '[a-zA-Z]\w*\.[a-zA-Z]\w*' => 'VAR_COMPLEX',
        '[a-zA-Z]\w*' => 'VAR',
        '\s+' => 'SPACE',
        '.' => 'UNKNOWN',
    );
    /**
     * Breaks a string expression into an array of 2-element arrays (type, value)
     * using {@link $_tokenChars} and {@link $_tokenRegex} to identify tokens
     * @param string $str the input expression
     * @return array a flat array of tokens
     */
    protected static function tokenize($str) {
        $tokens = array();
        $offset = 0;
        while ($offset < mb_strlen($str)) {
            $token = array();
            $substr = mb_substr($str, $offset);    // remaining string starting at $offset
            foreach (self::$_tokenChars as $char => &$name) {    // scan single-character patterns first
                if (mb_substr($substr, 0, 1) === $char) {
                    $tokens[] = array($name);    // add it to $tokens
                    $offset++;
                    continue 2;
                }
            }
            foreach (self::$_tokenRegex as $regex => &$name) {    // now loop through regex patterns
                $matches = array();
                if (preg_match('/^' . $regex . '/u', $substr, $matches) === 1) {
                    $tokens[] = array($name, $matches[0]);    // add it to $tokens
                    $offset += mb_strlen($matches[0]);
                    continue 2;
                }
            }
            $offset++;    // no infinite looping, yo
        }
        return $tokens;
    }
    
    
    
     /**
     * Tests this Form's conditions against the provided params.
     * reusing Code from X2FlowTrigger.php
     * @return array (error status, message)
     */
    public function checkConditions(&$params) {
        if (isset($this->config['conditions'])) {
            foreach ($this->config['conditions'] as &$condition) {
                if (!isset($condition['type'])) {
                    $condition['type'] = '';
                }
                $required = isset($condition['required']) && $condition['required'];
                // required param missing
                if (isset($condition['name']) && $required && !isset($params[$condition['name']])) {
                    if (YII_DEBUG) {
                        return array(false, Yii::t('studio', 'a required parameter is missing'));
                    } else {
                        return array(false, Yii::t('studio', 'conditions not passed'));
                    }
                }
                if (array_key_exists($condition['type'], self::$genericConditions)) {
                    if (!self::checkCondition($condition, $params))
                        return array(
                            false,
                            Yii::t('studio', 'conditions not passed')
                        );
                }
            }
        }
        return array(true, '');
    }
    
    

}
