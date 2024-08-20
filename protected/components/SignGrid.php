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
 * Widget class for rendering a user's actions widget.
 * 
 * Renders the actions widget with action statistics, i.e. how many actions total,
 * how many actions complete, how many incomplete, titled "My Actions"
 * @package application.components 
 */
class SignGrid extends X2Widget {

    public $visibility;
    public function init() {
        parent::init();
    }

    /**
     * Creates the widget. 
     */
    public function run() {
        list ($assignedToCondition, $params) = X2SignEnvelopes::model()->getAssignedToCondition (); 
        $total = Yii::app()->db->createCommand ("
            select count(*)
            from x2_sign_envelopes
            where $assignedToCondition
        ")->queryScalar ($params);
        $ACTIONS_REQUIRED = Yii::app()->db->createCommand ("
            select count(*)
            from x2_sign_envelopes
            where $assignedToCondition and status = 1
        ")->queryScalar ($params);
        $WAITING_FOR_OTHERS = Yii::app()->db->createCommand ("
            select count(*)
            from x2_sign_envelopes
            where $assignedToCondition and status = 2
        ")->queryScalar ($params);
        $CANCELLED = Yii::app()->db->createCommand ("
            select count(*)
            from x2_sign_envelopes
            where $assignedToCondition and status = 3
        ")->queryScalar ($params);
        $COMPLETED = Yii::app()->db->createCommand ("
            select count(*)
            from x2_sign_envelopes
            where $assignedToCondition and status = 4
        ")->queryScalar ($params);



        $this->render('signGrid', array(
            'total' => $total,
            'actionReq' => $ACTIONS_REQUIRED,
            'waiting' => $WAITING_FOR_OTHERS,
            'cancelled' => $CANCELLED,
                        'completed' => $COMPLETED,
        ));
    }

}

?>

