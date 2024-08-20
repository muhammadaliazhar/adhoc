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
 * X2FlowAction that completes a workflow stage
 * 
 * @package application.components.x2flow.actions
 */
class X2FlowWorkflowTerminateProcess extends BaseX2FlowWorkflowStageAction {
     /**
     * Fields
     */
    public $title = 'Terminate Process';
    public $info = 'Terminates the most recent process. *(This only works for cronological pipelines)';
     /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        $workflows = Workflow::getList(false); // no "none" options
        
        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' =>  array(
            array(
                    'name' => 'workflowId',
                    'label' => 'Process',
                    'type' => 'dropdown',
                    'options' => $workflows
                )
            )
        ));
    }
     /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        $workflowId = $this->parseOption('workflowId', $params);
        $stageNumber = $this->parseOption('stageNumber', $params);
         $model = $params['model'];
        $type = lcfirst(X2Model::getModuleName(get_class($model)));
        $modelId = $model->id;
        $workflowStatus = Workflow::getWorkflowStatus($workflowId, $modelId, $type);
        $message = '';
        if($stageNumber == null){
            $stageCount = count($workflowStatus['stages']);
            $stageTime = 0;
            $stageNumber = 0;
            //get stage number
            for($i=1; $i<=$stageCount; $i++) {
                if($stageTime <= $workflowStatus['stages'][$i]['createDate'] && $stageNumber < $i){
                    $stageNumber = $i;
                    $stageTime = $workflowStatus['stages'][$i]['createDate'];
                }
            }
        }    
                
        if (Workflow::validateAction('terminate', $workflowStatus, $stageNumber, '', $message)) {
             list ($started, $workflowStatus) = Workflow::terminateProcess(
                            $workflowId, $stageNumber, $model, $workflowStatus);
            //assert($started);
            return array(true, Yii::t('studio', 'Process terminated at stage "{stageName}" for {recordName}', array(
                    '{stageName}' => $workflowStatus['stages'][$stageNumber]['name'],
                    '{recordName}' => $model->getLink(),
                        )
            ));
        } else {
            return array(false, $message);
        }
    }
 }
