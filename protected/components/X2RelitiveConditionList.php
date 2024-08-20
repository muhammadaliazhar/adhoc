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
 * Generic condition list form which enables user specification of conditions on model properties.
 * User specified conditions can be retrieved through the front-end X2ConditionList API (see 
 * X2ConditionList.js).
 */
class X2RelitiveConditionList extends X2Widget {
    /**
     * @var string $id id of container element
     */
    public $id;
    /**
     * @var string $name condition list input name
     */
    public $name; 
    /**
     * @var array $value conditions already added
     */
    public $value; 
    /**
     * @var X2Model $model model whose attributes should be used to populate attribute list
     */
    public $model;
    /**
     * @var bool $useLinkedModels if true, add field options for related models
     */
    public $useLinkedModels = false;
    /**
     * @var array (optional) Used to instantiate JS X2ConditionList class. If not specified, this
     *  value will default to return value of {@link X2Model::getFieldsForDropdown}
     */
    public $attributes;
    
     /**
     * @var array this array will be used to hold all the different time options
     */
   
    /**
     * @var array $_packages
     */
    protected $_packages;
    public static function listOption ($attributes, $name) {
        if ($attributes instanceof Fields) {
            $attributes = $attributes->getAttributes ();
        }
        $data = array(
            'name' => $name,
            'label' => $attributes['attributeLabel'],
        );
        if(isset ($attributes['type']) && $attributes['type'])
            $data['type'] = $attributes['type'];
        if(isset ($attributes['required']) && $attributes['required'])
            $data['required'] = 1;
        if(isset ($attributes['readOnly']) && $attributes['readOnly'])
            $data['readOnly'] = 1;
        if(isset ($attributes['type'])) {
           if (($attributes['type'] === 'assignment' || 
                $attributes['type'] === 'optionalAssignment')) {
               $data['options'] = AuxLib::dropdownForJson(
                   X2Model::getAssignmentOptions(true, true));
            } elseif ($attributes['type'] === 'dropdown' && isset ($attributes['linkType'])) {
                $data['linkType'] = $attributes['linkType'];
                $data['options'] = AuxLib::dropdownForJson(
                    Dropdowns::getItems($attributes['linkType']));
            } elseif ($attributes['type'] === 'link' && isset ($attributes['linkType'])) {
                $staticLinkModel = X2Model::model($attributes['linkType']);
                if(array_key_exists('LinkableBehavior', $staticLinkModel->behaviors())) {
                    $data['linkType'] = $attributes['linkType']; 
                    $data['linkSource'] = Yii::app()->controller->createUrl(
                        $staticLinkModel->autoCompleteSource);
                }
            }
        }
        return $data;
    }
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'X2Fields' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2Fields.js',
                        'js/X2FieldsGeneric.js',
                        'js/jquery-ui-timepicker-addon.js',
                    ),
                    'depends' => array ('jquery.ui')
                ),
                'X2RelitiveConditionListJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2RelitiveConditionList.js',
                    ),
                    'depends' => array ('auxlib', 'X2Fields')
                ),
            );
        }
        return $this->_packages;
    }
    public function init () {
        if (!$this->attributes) {
            
            $TimeFunc = function ($field){
                if($field->type == "date" || $field->type == "dateTime"){
                        return TRUE;
                } else {
                        return FALSE;
                }
            };
            
            $this->attributes = $this->model->getFieldsForRelativeDropdown ($this->useLinkedModels, TRUE, $TimeFunc);
        }
    }
    public function run () {
 
        foreach($this->attributes as $keyone => $TheArray){
            foreach($TheArray as $keytwo => $att){
                $this->attributes[$keyone][$keytwo]["type"] = 'dropdown';
                $this->attributes[$keyone][$keytwo]["options"] = array(
                    array(
                        'Current FY',
                        'Current FY'
                    ),
                    array(
                        'Previous FY',
                        'Previous FY'
                    ),
                    array(
                        '2 FY Ago',
                        '2 FY Ago'
                    ),
                    array(
                        'Next FY',
                        'Next FY'
                    ),
                    array(
                        'Current FQ',
                        'Current FQ'
                    ),
                    array(
                        'Next FQ',
                        'Next FQ'
                    ),
                    array(
                        'Previous FQ',
                        'Previous FQ'
                    ),
                    array(
                        'Current CY',
                        'Current CY'
                    ),
                    array(
                        'Previous CY',
                        'Previous CY'
                    ),
                    array(
                        '2 CY Ago',
                        '2 CY Ago'
                    ),
                    array(
                        'Next CY',
                        'Next CY'
                    ),
                    array(
                        'Current CQ',
                        'Current CQ'
                    ),
                    array(
                        'Next CQ',
                        'Next CQ'
                    ),
                    array(
                        'Previous CQ',
                        'Previous CQ'
                    ),
                    array(
                        'Last Month',
                        'Last Month'
                    ),
                    array(
                        'This Month',
                        'This Month'
                    ),
                    array(
                        'Next Month',
                        'Next Month'
                    ),
                    array(
                        'Last Week',
                        'Last Week'
                    ),
                    array(
                        'This Week',
                        'This Week'
                    ),
                    array(
                        'Next Week',
                        'Next Week'
                    ),
                    array(
                        'Yesterday',
                        'Yesterday'
                    ),
                    array(
                        'Today',
                        'Today'
                    ),
                    array(
                        'Tomorrow',
                        'Tomorrow'
                    ),
                    array(
                        'Last 7 Days',
                        'Last 7 Days'
                    ),
                    array(
                        'Last 30 Days',
                        'Last 30 Days'
                    ),
                    array(
                        'Last 60 Days',
                        'Last 60 Days'
                    ),
                    array(
                        'Last 90 Days',
                        'Last 90 Days'
                    ),
                    array(
                        'Last 120 Days',
                        'Last 120 Days'
                    ),
                    array(
                        'Next 7 Days',
                        'Next 7 Days'
                    ),
                    array(
                        'Next 30 Days',
                        'Next 30 Days'
                    ),
                    array(
                        'Next 60 Days',
                        'Next 60 Days'
                    ),
                    array(
                        'Next 90 Days',
                        'Next 90 Days'
                    ),
                    array(
                        'Next 120 Days',
                        'Next 120 Days'
                    ),
                   
                );
            }
        }
       
        $this->registerPackages ();
        $this->render ('x2RelitiveConditionList');
    }
}
