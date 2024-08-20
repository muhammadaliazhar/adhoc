<?php

class MyInquiriesController extends InquiriesController {
    
    public function processBuyerData($data){
        $buyerData=array();
        $name=explode(" ",$data["c_contact__c"]);
        $buyerData["fname"]=$name[0];
        $buyerData["lname"]=$name[1];
        $buyerData["email"]=$data["c_email__c"];
        $buyerData["assignTo"]=$data["assignedTo"];
        $buyerData["listing"]=$data["c_listing__c"];
        return $buyerData;

    }
    //attach behavior to controller and get buyer and assigned to information
    public function actionSendNDA($id){
        $this->attachBehaviors(array(
            'X2SignBehaviour' => array(
                'class' => 'application.components.behaviors.X2SignBehaviour',
            )
        ));
        $groupMembers=Groups::model()->getGroupmates(yii::app()->user->getId());
        $buyerDataCmp=Inquiries::model()->findByPk($id);
        $isActive=Employees::model()->isActive($buyerDataCmp["assignedTo"]); 
        if(Yii::app()->user->name=='admin' || (Yii::app()->user->name==$buyerDataCmp['assignedTo'] || in_array($buyerDataCmp['assignedTo'] , $groupMembers) && $isActive)){
            $this->createX2SignAutomatically($this->processBuyerData($buyerDataCmp));
            $buyerDataCmp->c_document_status="NDA Sent";
            $buyerDataCmp->save();
        }
        else{
            throw new CHttpException(403, "UnAuthorized User . Cannot Send NDA Document");
        }
        $this->redirect(array('index'));
    }
}

