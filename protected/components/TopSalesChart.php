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
class TopSalesChart extends X2Widget {

    public $visibility;
    
    public function init() {
	parent::init();
    }

    public function run() {
//get the values for the grids and make sure to set each to show 25 users
        date_default_timezone_set('UTC');
        $StartMonth = strtotime(date('Y-m-01 00:00:00'));
        $NextMonthNumber = date('m', strtotime('-1 month'));
        $EndMonth = strtotime(date('Y-'.$NextMonthNumber.'-01 00:00:00'));


	$prof = Yii::app()->params->profile;
	$prof->setGoals();
	
	$time=time();
	$TopBuyers = Yii::app()->db
			->createCommand("SELECT id , assignedTo, count(assignedTo) FROM x2_contacts  WHERE createDate > " . $StartMonth . " AND assignedTo != 'Anyone' Group By assignedTo ORDER BY COUNT(assignedTo) DESC")
			->queryAll();		

	//shrink the array size 
	$TopBuyers = array_slice($TopBuyers,0,25);
        $topList = Yii::app()->db
                        ->createCommand("SELECT id , assignedTo, count(assignedTo) from x2_listings2  WHERE c_client_agreement_date__c > " . $StartMonth . " AND assignedTo != 'Anyone' Group By assignedTo ORDER BY COUNT(assignedTo) DESC")
                        ->queryAll();

	$topList = array_slice($topList,0,25);
 
	$topSoldList = Yii::app()->db
                        ->createCommand("SELECT id, name, c_user__c, c_2024_Actual_Sold_Deals from x2_employees WHERE c_2024_Actual_Sold_Deals > 0 AND c_user__c IN (SELECT username FROM x2_users where status = 1) ORDER BY  cast(c_2024_Actual_Sold_Deals as unsigned) DESC")
                        ->queryAll();
	
	$topSoldList = array_slice($topSoldList,0,25);
	
	$this->render('TopSalesChart',
		array(
		      
		      'TopBuyers' => $TopBuyers,
		      'topList' => $topList,
		      'topSoldList' => $topSoldList,
		)
	);
    }
}


