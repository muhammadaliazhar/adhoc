<?php
/***********************************************************************************
* Copyright (C) 2011-2018 X2 Engine Inc. All Rights Reserved.
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
 * This is the model class for table "x2_fields".
 *
 * @package application.models
 * @property integer $id
 * @property string $modelName
 * @property string $fieldName
 * @property string $attributeLabel
 * @property integer $show
 * @property integer $custom
 * @property string $myTableName The name of the table to which the field corresponds
 * @author Jake Houser <jake@x2engine.com>, Demitri Morgan <demitri@x2engine.com>
 */
class ConvertRecords extends CActiveRecord {
    /**
     * PHP types corresponding to field types in X2Engine.
     *
     * This is to supplement Yii's active record functionality, which does not
     * typecast column values according to their canonical type.
     * @var type
     */
    public static $phpTypes = array(
        'assignment' => 'string',
        'boolean' => 'boolean',
        'credentials' => 'integer',
        'currency' => 'double',
        'date' => 'integer',
        'dateTime' => 'integer',
        'dropdown' => 'string',
        'email' => 'string',
        'int' => 'integer',
        'link' => 'string',
        'optionalAssignment' => 'string',
        'percentage' => 'double',
        'rating' => 'integer',
        'varchar' => 'string',
    );
    /**
     * Constructor override.
     */
    public function __construct($scenario = 'insert') {
        parent::__construct($scenario);
        if($scenario == 'search') {
            $this->setAttributes(
                array_fill_keys(
                    $this->attributeNames(),
                    null
                ),
                false);
        }   
        
    }
    /**
     * Returns the static model of the specified AR class.
     * @return Fields the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }
    
    public function behaviors () {
        return array_merge (parent::behaviors (), array (
            'CommonFieldsBehavior' => array (
                'class' => 'application.components.behaviors.CommonFieldsBehavior',
            )
        ));
    }
    /**
     * Rules for saving a field.
     *
     * See the following MySQL documentation pages for more info on length
     * restrictions and naming requirements:
     * http://dev.mysql.com/doc/refman/5.0/en/identifiers.html
     *
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('modelFrom, modelTo', 'length', 'max' => 250),
            array('fieldFrom, fieldTo', 'length', 'max' => 64), // Max length for column identifiers in MySQL
            array('fieldFrom, fieldTo', 'match', 'pattern' => '/^[a-zA-Z]\w+$/', 'message'=>Yii::t('admin','Field name may only contain alphanumeric characters and underscores.')),
            array('fieldFrom, fieldTo', 'nonReserved'),
            array('modelFrom, modelTo, fieldFrom, fieldTo', 'required'),
            array('modelFrom, modelTo', 'in', 'range' => array_keys(X2Model::getModelNames()),'allowEmpty'=>false),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, modelFrom, modelTo, fieldFrom, fieldTo', 'safe', 'on' => 'search'),
        );
    }
    public static function searchRelevance() {
        return array('Low' => Yii::t('app', 'Low'), "Medium" => Yii::t('app', "Medium"), "High" => Yii::t('app', "High"));
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('admin', 'ID'),
            'modelName' => Yii::t('admin', 'Model Name'),
            'fieldName' => Yii::t('admin', 'Field Name'),
            'attributeLabel' => Yii::t('admin', 'Attribute Label'),
            'custom' => Yii::t('admin', 'Custom'),
            'modified' => Yii::t('admin', 'Modified'),
            'readOnly' => Yii::t('admin', 'Read Only'),
            'required' => Yii::t('admin', "Required"),
            'searchable' => Yii::t('admin', "Searchable"),
            'relevance' => Yii::t('admin', 'Search Relevance'),
            'uniqueConstraint' => Yii::t('admin', 'Unique'),
            'defaultValue' => Yii::t('admin', 'Default Value'),
            'keyType' => Yii::t('admin','Key Type'),
            'data' => Yii::t('admin','Template'),
        );
    }
    /**
     * Validator for ensuring an identifier does not include MySQL reserved words
     * or X2Engine reserved words
     */
    public function nonReserved($attribute,$params = array()) {
        if($this->isNewRecord){
            $dataFiles = array();
            $reservedWords = array();
            $dataFiles[] = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', 'mysqlReservedWords.php'));
            $dataFiles[] = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'data', 'modelReservedWords.php'));
            foreach($dataFiles as $path){
                if(file_exists($path)){
                    $reservedWords = array_merge($reservedWords, require($path));
                }
            }
            if(in_array($this->$attribute, $reservedWords)){
                $this->addError($attribute, Yii::t('admin', 'This field is a MySQL or X2Engine reserved word.  Choose a different field name.'));
            }
        }
    }
    /**
     * @return array relational rules.
     */
    public function relations(){
        return array();
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search(){
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $criteria = new CDbCriteria;
        
        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                ));
    }
    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_convert_records';
    }
    /**
     * Validator that prevents adding a unique constraint to a field without
     * also making it required.
     */
    public function requiredUnique($attribute, $params = array()) {
        if($this->$attribute == 'UNI' && !$this->uniqueConstraint) {
            $this->addError($attribute,Yii::t('admin','You cannot add a unique constraint unless you also make the field unique and required.'));
        }
    }
    /**
     * Check that the combination of model and field name will not conflict
     * with any existing one.
     *
     * @param type $attribute
     * @param type $params
     */
    public function uniqueFieldName($attribute, $params = array()) {
        $fields = self::model()->findAllByAttributes(array($attribute=>$this->$attribute,'modelName'=>$this->modelName));
        if(count($fields) > 0) {
            // There can and should only be one.
            $existingField = reset($fields);
            if($this->id != $existingField->id) {
                // This is not the field! Saving will produce a database
                // cardinality violation error due to the unique constraint on
                // model name and field name.
                $this->addError($attribute,Yii::t('admin','A field in the specified data model with that name already exists.'));
            }
        }
    }
    /**
     * Check that the default value is appropriate given the type of the field.
     * 
     * @param string $attribute
     * @param array $params
     */
    public function validDefault($attribute,$params = array()) {
        if($this->fieldName == '')
            return; // Nothing is possible without the field name. Validation will fail for it accordingly.
        // Use the amorphous model for "proxy" validation, and use a "dummy"
        // field model (because we'll need to set the name differently to make
        // things easier on ourselves, given how user input for field name might
        // not be appropriate for a property name)
        $dummyModel = new AmorphousModel();
        $dummyField = new Fields;
        foreach($this->attributes as $name=>$value) {
            $dummyField->$name = $value;
        }
        $dummyField->fieldName = 'customized_field';
        $dummyModel->scenario = 'insert';
        $dummyModel->addField($dummyField,'customized_field');
        $dummyModel->setAttribute('customized_field',$this->$attribute);
        $dummyModel->validate();
        if($dummyModel->hasErrors('customized_field')) {
            foreach($dummyModel->errors['customized_field'] as $error) {
                $this->addError($attribute, str_replace($dummyField->attributeLabel, $dummyField->getAttributeLabel($attribute), $error));
            }
        }
    }
    /**
     * Alter/purify the input for the custom data field.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validCustom($attribute,$params = array()) {
        if($this->type == 'custom') {
            if($this->linkType == 'formula') {
                $this->$attribute = trim($this->$attribute);
                if(strpos($this->$attribute,'=')!==0) {
                    $this->$attribute = '='.$this->$attribute;
                }
            } else if($this->linkType == 'display') {
               $this->$attribute = self::getPurifier()->purify($this->$attribute);
            }
        }
    }
}
