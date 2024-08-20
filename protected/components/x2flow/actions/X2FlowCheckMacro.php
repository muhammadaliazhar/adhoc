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
 * X2FlowAction that creates a notification
 *
 * @package application.components.x2flow.actions
 */
class X2FlowCheckMacro extends X2FlowAction {
    /**
     * Fields
     */
    public $title = 'Check and Put in Macro';
    public $info = 'Gets all the records that past conditions and puts them in a Macro';
    /**
     * Parameter rules
     * 
     * @return type
     */
    public function paramRules() {
        $macros = array();
        $descriptions = array();
        $flows = Yii::app()->db->createCommand()
                ->select('id, name, description')
                ->from('x2_flows')
                ->where('triggerType = :type',array(':type'=>'MacroTrigger'))
                ->queryAll();
        foreach($flows as $row){
            $macros[$row['id']] = $row['name'];
            $descriptions[$row['id']] = $row['description'];
        }
        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            
                'options' => array(
                    array(
                    'name' => 'Macro',
                    'label' => 'Macro',
                    'type' => 'dropdown',
                    'options' => $macros
                    ),
                    array(
                    'name' => 'RecordType',
                    'label' => 'RecordType',
                    'type' => 'dropdown',
                    'options' => X2Model::getModelNames(),
                    'id' => "MacroType"
                    ),
                )
            )
        );
    }
    
    
    
    
        
       /**
     * @param int the month 
     * @return int the quarter for that month
     * 
     */
    
    public static function getCurQuarter($month){
        if($month < 4){
            return 1;
        }elseif($month < 7){
            return 2;
        }elseif($month < 10){
            return 3;
        }else{
            return 4;
        }
    }
    
     /**
     * @param array where the dates will be stored 
     * @param the amount of quarters distance from current quarter
     * 
     */
    
    
    public static function getFiscalQuarter($dates, $timeScale){
        $curMonth = date("n");
        $curYear = date("Y");
        $numOfYears = $timeScale%4;
        $numberOfQart = $timeScale - (4 * $numOfYears);
        $curQuarter = getCurQuarter($curMonth);
        $wantQuarter = $curQuarter + $numberOfQart;
        if($wantQuarter > 4){
            $curYear++;
            $wantQuarter = $wantQuarter - 4;
        }
        if($wantQuarter < 1){
            $curYear--;
            $wantQuarter = $wantQuarter + 4;
        }
        $wantYear = $curYear + $numberOfQart;
        $startMonth = ($wantQuarter * 3) - 2;
        $endMonth = ($wantQuarter * 3);
        $numOfDays = cal_days_in_month(CAL_GREGORIAN, $endMonth, $wantYear);
        $dates['start'] = strtotime($wantYear . "/" . $startMonth . "/1" );
        $dates['end'] = strtotime($wantYear . "/" . $endMonth . "/" . $numOfDays );
        return;
    }
    
    public static function getCurFiscalYear(){
        if ( date('m') > 6 ) {
           return (date('Y') + 1);
        }
        elseif ( date('m') < 6 ){
            return date('Y');
        }else{
            return NULL;
        }
        
        
    }
    
    public static function getMonthDates($month, $year = null){
        if(!isset($year)){
            $year = date('Y');
        }
        $dates = array( 'start' => NULL , 'end' => NULL);
        $dates['start'] =  strtotime($month . "/1/" . $year);
        
        $numOfDays = ($month == 2) ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31); 
        $dates['end'] =  strtotime($month . '/' .$numOfDays . '/'. $year);
        return $dates;
        
    }
    
        public static function getweekDates($scale){
            $dates = array( 'start' => NULL , 'end' => NULL);
            $dates['start'] = strtotime("last Sunday");
            //86400 is 7 days
            $dates['start'] = $dates['start'] + ($scale * 86400);
            $dates['end'] = $dates['start'] + $scale;
            return $dates;
    }
    
    
    public static function getEpocTime($timeName){
        $dates = array( 'start' => NULL , 'end' => NULL);
        $curYear = date("Y");
        $curMonth = date("n");
        $fiscalYear = self::getCurFiscalYear();
        if($timeName == 'Current FY'){
            $dates['start'] =  strtotime("October 1 " . ($fiscalYear - 1));
            $dates['end'] =  strtotime("September 30 " . $fiscalYear);
        }elseif($timeName == 'Previous FY'){
            $dates['start'] =  strtotime("October 1 " . ($fiscalYear - 2));
            $dates['end'] =  strtotime("September 30 " . ($fiscalYear - 1));
        }elseif($timeName == '2 FY Ago'){
            $dates['start'] =  strtotime("October 1 " . ($fiscalYear - 3));
            $dates['end'] =  strtotime("September 30 " . ($fiscalYear - 2));
        }elseif($timeName == 'Next FY'){
            $dates['start'] =  strtotime("October 1 " . ($fiscalYear));
            $dates['end'] =  strtotime("September 30 " . ($fiscalYear + 1));
        }elseif($timeName == 'Current FQ'){
            self::getFiscalQuarter($dates , 0);
        }elseif($timeName == 'Next FQ'){
            self::getFiscalQuarter($dates , 1);
        }elseif($timeName == 'Previous FQ'){
            self::getFiscalQuarter($dates , -1);
        }elseif($timeName == 'Current CY'){
            $dates['end'] =  strtotime("december 1 " . ($curYear));
            $dates['start'] =  strtotime("january 1 " . ($curYear));
        }elseif($timeName == 'Previous CY'){
            $dates['end'] =  strtotime("december 1 " . ($curYear - 1));
            $dates['start'] =  strtotime("january 1 " . ($curYear - 1));
        }elseif($timeName == '2 CY Ago'){
           $dates['end'] =  strtotime("december 1 " . ($curYear - 2));
            $dates['start'] =  strtotime("january 1 " . ($curYear - 2));            
        }elseif($timeName == 'Next CY'){
           $dates['end'] =  strtotime("december 1 " . ($curYear + 1));
            $dates['start'] =  strtotime("january 1 " . ($curYear + 1));            
        }elseif($timeName == 'Current CQ'){
            self::getFiscalQuarter($dates , 0);
        }elseif($timeName == 'Next CQ'){
             self::getFiscalQuarter($dates , 1);
        }elseif($timeName == 'Previous CQ'){
             self::getFiscalQuarter($dates , -1);
        }elseif($timeName == 'Last Month'){
            $targetMonth =  $curMonth - 1;
            if($targetMonth == 0){
                $targetMonth = 12;
                $curYear --;
            }
           $dates = self::getMonthDates($targetMonth , $curYear);
        }elseif($timeName == 'This Month'){
           $dates = self::getMonthDates($curMonth , $curYear);
        }elseif($timeName == 'Next Month'){
            
            $targetMonth =  $curMonth + 1;
            if($targetMonth == 13){
                $targetMonth = 1;
                $curYear ++;
            }
           $dates = self::getMonthDates($targetMonth , $curYear);
            
        }elseif($timeName == 'Last Week'){
            $dates = self::getweekDates(-1);
        }elseif($timeName == 'This Week'){
            $dates = self::getweekDates(0);
        }elseif($timeName == 'Next Week'){
            $dates = self::getweekDates(1);
        }elseif($timeName == 'Yesterday'){
            $dates['start'] = strtotime("Yesterday");
            $dates['end'] = strtotime("Today");
        }elseif($timeName == 'Today'){
            $dates['start'] = strtotime("Today");
            $dates['end'] = $dates['start'] + 86400 ;
            
        }elseif($timeName == 'Tomorrow'){
            $dates['start'] = strtotime("Tomorrow");
            $dates['end'] = $dates['start'] + 86400 ;
            
        }elseif($timeName == 'Last 7 Days'){
            
            $dates['end'] = strtotime("Now");
            $dates['start'] = strtotime("-7 days") ;             
        }elseif($timeName == 'Last 30 Days'){
            
            $dates['end'] = strtotime("Now");
            $dates['start'] =  strtotime("-30 days") ;            
        }elseif($timeName == 'Last 60 Days'){
            
            $dates['end'] = strtotime("Now");
            $dates['start'] = strtotime("-60 days") ;            
        }elseif($timeName == 'Last 90 Days'){
            
            $dates['end'] = strtotime("Now");
            $dates['start'] =  strtotime("-90 days") ;          
        }elseif($timeName == 'Last 120 Days'){
            
            $dates['end'] = strtotime("Now");
            $dates['start'] = strtotime("-120 days") ;            
        }elseif($timeName == 'Next 7 Days'){
            
            $dates['start'] = strtotime("Now");
            $dates['end'] = strtotime("+7 days") ;            
        }elseif($timeName == 'Next 30 Days'){
            
            $dates['start'] = strtotime("Now");
            $dates['end'] = strtotime("+30 days") ;            
        }elseif($timeName == 'Next 60 Days'){
            
            $dates['start'] = strtotime("Now");
            $dates['end'] = strtotime("+60 days") ;            
        }elseif($timeName == 'Next 90 Days'){
            
            $dates['start'] = strtotime("Now");
            $dates['end'] = strtotime("+90 days") ;            
        }elseif($timeName == 'Next 120 Days'){
            $dates['start'] = strtotime("now");
            $dates['end'] = strtotime("+120 days");           
        }
        return $dates;
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
        'RelativeTime' => 'Relative Time',
    );
    public static function getGenericConditions() {
        return array_map(function($term) {
            return Yii::t('studio', $term);
        }, self::$genericConditions);
    }
    public static function getGenericCondition($type) {
        switch ($type) {
            case 'current_user':
                return array(
                    'name' => 'user',
                    'label' => Yii::t('studio', 'Current User'),
                    'type' => 'dropdown',
                    'multiple' => 1,
                    'options' => X2Model::getAssignmentOptions(false, false),
                    'operators' => array('=', '<>', 'list', 'notList')
                );
            case 'month':
                return array(
                    'label' => Yii::t('studio', 'Current Month'),
                    'type' => 'dropdown',
                    'multiple' => 1,
                    'options' => Yii::app()->locale->monthNames,
                    'operators' => array('=', '<>', 'list', 'notList')
                );
            case 'day_of_week':
                return array(
                    'label' => Yii::t('studio', 'Day of Week'),
                    'type' => 'dropdown',
                    'multiple' => 1,
                    'options' => Yii::app()->locale->weekDayNames,
                    'operators' => array('=', '<>', 'list', 'notList')
                );
            case 'day_of_month':
                $days = array_keys(array_fill(1, 31, 1));
                return array(
                    'label' => Yii::t('studio', 'Day of Month'),
                    'type' => 'dropdown',
                    'multiple' => 1,
                    'options' => array_combine($days, $days),
                    'operators' => array('=', '<>', 'list', 'notList')
                );
            case 'time_of_day':
                return array(
                    'label' => Yii::t('studio', 'Time of Day'),
                    'type' => 'time',
                    'operators' => array('before', 'after')
                );
            case 'current_time':
                return array(
                    'label' => Yii::t('studio', 'Current Time'),
                    'type' => 'dateTime',
                    'operators' => array('before', 'after')
                );
            case 'user_active':
                return array(
                    'label' => Yii::t('studio', 'User Logged In'),
                    'type' => 'dropdown',
                    'options' => X2Model::getAssignmentOptions(false, false)
                );
            case 'on_list':
                return array(
                    'label' => Yii::t('studio', 'On List'),
                    'type' => 'link',
                    'linkType' => 'X2List',
                    'linkSource' => Yii::app()->controller->createUrl(
                            CActiveRecord::model('X2List')->autoCompleteSource)
                );
            case 'has_tags':
                return array(
                    'label' => Yii::t('studio', 'Has Tags'),
                    'type' => 'tags',
                );
            case 'email_open':
                return array(
                    'label' => Yii::t('studio', 'Email Opened'),
                    'type' => 'dropdown',
                    'options' => array(),
                );
            default:
                return false;
        }
    }
    /**
     * Can be overridden in child class to give flow a default return value
     */
    public function getDefaultReturnVal($flowId) {
        return null;
    }
    /**
     * Can be overridden in child class to extend behavior of validate method
     */
    public function afterValidate(&$params, $defaultErrMsg = '', $flowId) {
        return array(false, Yii::t('studio', $defaultErrMsg));
    }
    /**
     * Checks if all all the params are ship-shape
     */
    public function validate(&$params = array(), $flowId = null) {
        $paramRules = $this->paramRules();
        if (!isset($paramRules['options'], $this->config['conditions'])) {
            return $this->afterValidate(
                            $params, YII_DEBUG ?
                            'invalid rules/params: trigger passed options when it specifies none' :
                            'invalid rules/params', $flowId);
        }
        $config = &$this->config['conditions'];
        if (isset($paramRules['modelClass'])) {
            $modelClass = $paramRules['modelClass'];
            if ($modelClass === 'modelClass') {
                if (isset($config['modelClass'], $config['modelClass']['value'])) {
                    $modelClass = $config['modelClass']['value'];
                } else {
                    return $this->afterValidate(
                                    $params, YII_DEBUG ?
                                    'invalid rules/params: ' .
                                    'trigger requires model class option but given none' :
                                    'invalid rules/params', $flowId);
                }
            }
            if (!isset($params['model'])) {
                return $this->afterValidate(
                                $params, YII_DEBUG ?
                                'invalid rules/params: trigger requires a model but passed none' :
                                'invalid rules/params', $flowId);
            }
            if ($modelClass !== get_class($params['model'])) {
                return $this->afterValidate(
                                $params, YII_DEBUG ?
                                'invalid rules/params: required model class does not match model passed ' .
                                'to trigger' :
                                'invalid rules/params', $flowId);
            }
        }
        return $this->validateOptions($paramRules, $params);
    }
    /**
     * Default condition processor for main config panel. Checks each option against the key in 
     * $params of the same name, using an operator if provided (defaults to "=")
     * 
     * @return array (error status, message)
     */
    public function check(&$params) {
        foreach ($this->config['conditions'] as $name => &$option) {
            // modelClass is a special case, ignore it
            if ($name === 'modelClass') {
                continue;
            }
            // if it's optional and blank, forget about it
            if ($option['optional'] && ($option['value'] === null ||
                    $option['value'] === '')) {
                continue;
            }
            $value = $option['value'];
            if (isset($option['type'])) {
                $value = X2Flow::parseValue($value, $option['type'], $params);
            }
            if (isset($option['comparison']) && !$option['comparison']) {
                continue;
            }
            if (!static::evalComparison($params[$name], $option['operator'], $value)) {
                if (is_string($value) && is_string($params[$name]) &&
                        is_string($option['operator'])) {
                    return array(
                        false,
                        Yii::t('studio', 'The following condition did not pass: ' .
                                '{name} {operator} {value}', array(
                            '{name}' => $params[$name],
                            '{operator}' => $option['operator'],
                            '{value}' => (string) $value,
                        ))
                    );
                } else {
                    return array(
                        false,
                        Yii::t('studio', 'Condition failed')
                    );
                }
            }
        }
        return $this->checkConditions($params);
    }
    /**
     * Tests this trigger's conditions against the provided params.
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
    /**
     * Used to check workflow status condition
     * @param Array $condition
     * @param Array $params
     * @return bool true for success, false otherwise
     */
    public static function checkWorkflowStatusCondition($condition, &$params) {
        if (!isset($params['model']) ||
                !isset($condition['workflowId']) ||
                !isset($condition['stageNumber']) ||
                !isset($condition['stageState'])) {
            return false;
        }
        $model = $params['model'];
        $workflowId = $condition['workflowId'];
        $stageNumber = $condition['stageNumber'];
        $stageState = $condition['stageState'];
        $modelId = $model->id;
        $type = lcfirst(X2Model::getModuleName(get_class($model)));
        $workflowStatus = Workflow::getWorkflowStatus($workflowId, $modelId, $type);
        if (!isset($workflowStatus['stages'][$stageNumber])) {
            return false;
        }
        $passed = false;
        switch ($stageState) {
            case 'completed':
                $passed = Workflow::isCompleted($workflowStatus, $stageNumber);
                break;
            case 'started':
                $passed = Workflow::isStarted($workflowStatus, $stageNumber);
                break;
            case 'notCompleted':
                $passed = !Workflow::isCompleted($workflowStatus, $stageNumber);
                break;
            case 'notStarted':
                $passed = !Workflow::isStarted($workflowStatus, $stageNumber);
                break;
            default:
                return false;
        }
        return $passed;
    }
    /**
     * @param Array $condition
     * @param Array $params
     * @return bool true for success, false otherwise
     */
    public static function checkCondition($condition, &$params) {
        if ($condition['type'] === 'workflow_status') {
            return self::checkWorkflowStatusCondition($condition, $params);
        }
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
            case 'workflow_status':
                if (!isset($model, $condition['workflowId'], $condition['stageNumber']))
                    return false;
                switch ($operator) {
                    case 'started_workflow':
                        return CActiveRecord::model('Actions')->exists(
                                        'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow', array(
                                    ':type' => get_class($model),
                                    ':modelId' => $model->id,
                                    ':workflow' => $condition['workflowId'],
                        ));
                    case 'started_stage':
                        return CActiveRecord::model('Actions')->exists(
                                        'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow AND stageNumber=:stage AND (completeDate IS NULL OR completeDate=0)', array(
                                    ':type' => get_class($model),
                                    ':modelId' => $model->id,
                                    ':workflow' => $condition['workflowId'],
                                    ':stageNumber' => $condition['stageNumber'],
                        ));
                    case 'completed_stage':
                        return CActiveRecord::model('Actions')->exists(
                                        'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow AND stageNumber=:stage AND completeDate > 0', array(
                                    ':type' => get_class($model),
                                    ':modelId' => $model->id,
                                    ':workflow' => $condition['workflowId'],
                                    ':stageNumber' => $condition['stageNumber'],
                        ));
                    case 'completed_workflow':
                        $stageCount = CActiveRecord::model('WorkflowStage')->count('workflowId=:id', array(':id' => $condition['workflowId']));
                        $actionCount = CActiveRecord::model('Actions')->count(
                                'associationType=:type AND associationId=:modelId AND type="workflow" AND workflowId=:workflow', array(
                            ':type' => get_class($model),
                            ':modelId' => $model->id,
                            ':workflow' => $condition['workflowId'],
                        ));
                        return $actionCount >= $stageCount;
                }
                return false;
            case 'email_open':
                if (isset($params['sentEmails'], $params['sentEmails'][$value])) {
                    $trackEmail = TrackEmail::model()->findByAttributes(array('uniqueId' => $params['sentEmails'][$value]));
                    return $trackEmail && !is_null($trackEmail->opened);
                }
                
                
            case 'RelativeTime':
                $attr = &$condition['name'];
                $EpocTime = self::getEpocTime($value);
                $attrVal = $model->getAttribute($attr);
                $BeforeDate = self::evalComparison($attrVal, '<=' , $EpocTime['end']);
                $AfterDate = self::evalComparison($attrVal, '>=' , $EpocTime['start']);
                if($BeforeDate && $AfterDate){
                    return True;
                }else {
                    
                    return false;
                }
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
     * Execute action
     * 
     * @param type $params
     * @param type $triggerLogId
     * @return type
     */
    public function execute(&$params, $triggerLogId = null) {
        // Sets options from config
         $macroId = $this->config['options']['Macro']['value'];
        //set a log exicution time
        //set_time_limit(1500);
        $flow = X2Flow::model()->findByPk($macroId);
       $params['modelClass'] = $this->config['modelClass'];
        $Smart = new SmartActiveDataProvider($this->config['modelClass'], array(
                'pagination' => array(
                    'pageSize' => 20,
                ),
            ));
        //$records = X2Model::model($this->config['modelClass'])->findAll();
        foreach( $Smart->getData() as $records){
                foreach($records as $record){
                $params['model'] = X2Model::model($this->config['modelClass'])->findbyPK($record);
                        $PassOn = $this->checkConditions($params);
                        if($PassOn[0]){
                                X2Flow::executeFlow($flow, $params, null);
                        }
                }
        }
        return array(
                true,
                Yii::t('studio', 'MACRO DONE ') );
    }
}
