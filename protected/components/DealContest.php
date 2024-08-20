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
 * Class for displaying the "Quick Contact" widget
 *
 * @package application.components
 */
class DealContest extends X2Widget {

    public $visibility;

    public function init() {
        parent::init();
    }

    public function run() {
        //Custom Code: Contest for Transworld
        //by Jack L (Juse 17 2021)
        //**CUSTOM CODE START**
        $months = 3;
        $dealCount = array();
        $dealCommissions = array();
        $i = 7;
        $startMonth = strtotime(date('Y-'.($i + 0).'-01 00:00:00') . ' UTC');

        $endMonth = strtotime(date('Y-'.($i + 3).'-01 00:00:00') . ' UTC');
        $query = "select id, assignedTo, count(assignedTo) from x2_opportunities where c_actual_close_date_c > " . $startMonth . " AND salesStage = 'Sold' group By assignedTo order by count(*) DESC LIMIT 30";
        $dealCount = Yii::app()->db
                        ->createCommand($query)
                        ->queryAll();
        $i = 0;
        foreach($dealCount as $key => $value){
            $i++;
            $dealCount[$key]["spot"] = $i;

        }

        $query = "select id, c_user__c, c_contestTotal from x2_employees where c_contestTotal != 0 order by c_contestTotal DESC LIMIT 30";
        $dealComish = Yii::app()->db
                        ->createCommand($query)
                        ->queryAll();

        $i = 0;
        foreach($dealComish as $key => $value){
            $i++;
            $dealComish[$key]["spot"] = $i;

        }


        //**CUSTOM CODE ENDS**

        $this->render('dealContest',
                array(
                      'dealCount' => $dealCount,
                      'dealComish' => $dealComish,
                )
        );
    }
}

