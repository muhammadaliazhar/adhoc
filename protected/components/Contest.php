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
class Contest extends X2Widget {

    public $visibility;

    public function init() {
        parent::init();
    }

    public function run() {
        //Custom Code: Contest for Transworld
        //by Justin T (June 20th)
        //**CUSTOM CODE START**
        $months = 3;
        $months_dataProvider = array();
        for($i = 0; $i < $months; $i++){
            $startMonth = strtotime(date('Y-'.($i + 7).'-01 00:00:00'));
            if ($i == 2) 
                $endMonth = strtotime(date('Y-12-01 00:00:00'));
            else
                $endMonth = strtotime(date('Y-'.($i + 8).'-01 00:00:00'));
            $query = "SELECT id, assignedTo, count(assignedTo) FROM x2_listings2 " .
                        "WHERE c_client_agreement_date__c  >= $startMonth AND c_client_agreement_date__c < $endMonth" .
                        " AND assignedTo != 'Anyone' AND c_client_status__c = 'Active' GROUP BY assignedTo ORDER BY COUNT(assignedTo) DESC LIMIT 30";
            $months_dataProvider[] = Yii::app()->db
                        ->createCommand($query)
                        ->queryAll();

            $spot = 0;
            foreach($months_dataProvider[$i] as $key => $value){
                $spot++;
                $months_dataProvider[$i][$key]["spot"] = $spot;

            }

        }
        //**CUSTOM CODE ENDS**

        $this->render('contest',
                array(
                      'months_dataProvider' => $months_dataProvider,
                )
        );
    }
}

