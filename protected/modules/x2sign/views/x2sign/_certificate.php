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





?>

<style>
    h3 {
        background-color: lightgrey;
    }
</style>



<html>
<body>
<h3>Certificate of Completion</h3>
<table  autosize="1">

    <?php
        $envelopeProctor = User::model()->findByAttributes(array('username' => $envelope->assignedTo));
        // printR($envelopeProctor, 1);
        $envelopeStatuses = array(1 => 'Actions Required', 2 => 'Waiting on others', 3 => 'Cancelled', 4 => 'Completed');
        $start = 0;
        $style = array("style"=>"font-size:10px;");
        foreach ($envelope->getAttributes() as $attr => $val) {
            
            if($start % 2 == 0)
                echo CHtml::openTag('tr');
            if($attr == 'id')
                echo CHtml::tag('td', $style, 'Envelope Id:');
            elseif($attr == 'emailSubject' || $attr == 'emailBody' || $attr == 'email' || $attr == 'c_listing')
                echo "";
            else
                echo CHtml::tag('td', $style, $envelope->getAttributeLabel($attr) . ':');
           
            switch($attr) {
                case 'assignedTo':
                    echo CHtml::tag('td', $style, $envelopeProctor->firstName . ' ' . $envelopeProctor->lastName);
                    break;
                case 'status':
                    if (isset($envelopeStatuses[$val]))echo CHtml::tag('td', $style, $envelopeStatuses[$val]);
                    break;
                case 'createDate':
                    echo CHtml::tag('td', $style, date('r', $val));
                    break;                    
                case 'lastUpdated':
                    echo CHtml::tag('td', $style, date('r', $val));
                    break;
                case 'completeDate':
                    echo CHtml::tag('td', $style, date('r', $val));
                    break;
                case'signDocIds':
                    $docIds = json_decode($envelope->signDocIds);
                    $newSignDoc = array(); 
                    foreach($docIds as $docId){
                        $signDoc = X2SignDocs::model()->findByPk($docId);
                        $newSignDoc[] = $signDoc->name;
                    }
                    echo CHtml::tag('td', $style, implode(", ", $newSignDoc));
                    break;
                case'relatedRecords':
                    $links = $envelope->getX2SignLinks();
                    $names = array();
                    foreach($links as $link){
                        if($link->modelType != "user"){
                            $linkModel = ($link->modelType)::model()->findByPk($link->modelId);
                            if(isset($linkModel)){
                                $names[] = $linkModel->name;

                            }
                        }   
                    }
                    echo CHtml::tag('td', $style, implode(", ",$names));
                    break;
                case 'email':
                case 'emailBody':
                case 'emailSubject':
                case 'c_listing':
                    break;
                default:
                    echo CHtml::tag('td', $style, $val);    
            }
            
            if($start % 2 == 0)    
                echo CHtml::closeTag('tr');
            $start++;
        }
    ?>
</table>
<h3>Signer Events</h3>
<table>
    <?php
    $dateArray = array("createDate", "signedDate", "openedDate", "viewedDate");
    foreach ($envelope->getX2SignLinks() as $link) {
        $start = 0;
        foreach ($link->getAttributes() as $attr => $val) {
            if($start % 2 == 0)
                echo CHtml::openTag('tr');
            echo CHtml::tag('td', $style, $link->getAttributeLabel($attr));
            if(in_array($attr, $dateArray))
                echo CHtml::tag('td', $style, date('r', $val));
            else
                echo CHtml::tag('td', $style, $val);
            if($start % 2 == 0)
                echo CHtml::closeTag('tr');
            $start++;
        }
        //print IP
        $signature = X2Signature::model()->findByAttributes(array(
            'emailAddress' => $link->emailAddress
        ));
        if(!isset($signature)){
           $signature = X2Signature::model()->findByAttributes(array(
                'modelType' => $link->modelType,
                'modelId' => $link->modelId
            ));
            

        }
       echo CHtml::openTag('tr');
       echo CHtml::tag('td', $style, "Signature IP");
       echo CHtml::tag('td', $style, $signature->IP);
       echo CHtml::closeTag('tr');

       echo CHtml::openTag('tr');
       echo CHtml::tag('td', $style, "Signature Image");
       echo CHtml::tag('td', [], '<img width="230px" height="60px" src="' . Yii::app()->getBasePath() . '/' . $signature->path . '" alt="icon" />'); 
       echo CHtml::closeTag('tr');



       $event_list = X2SignEvents::model()->findAllByAttributes(
            array(),
            "signLinkId =" . $link->id . " AND type = 6" 
       );
       echo CHtml::openTag('tr');
       echo CHtml::tag('td', $style, "VIEW EVENT LOG");
       echo CHtml::closeTag('tr');
       foreach($event_list as $event){
           echo CHtml::openTag('tr');
           echo CHtml::tag('td', $style, "IP: ");
           echo CHtml::tag('td', $style, $event->IP);
           echo CHtml::tag('td', $style, " Date: ");
           echo CHtml::tag('td', $style,  date('r', $event->createDate));
           echo CHtml::closeTag('tr');
        }
    }
    ?>
</table>
</body>
</html>


