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
 * @author: Justin Toyomitsu <justin@x2engine.com>
 */

include("protected/modules/x2sign/x2signConfig.php");

Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/X2SignReport.js');

Yii::app()->clientScript->registerCss('X2SignReportList',"

.list-status-box {
    margin-bottom: 10px;
}

.x2sign-table thead th {
    padding: 8px 12px 8px 5px;
    text-align: left;
    font-weight: 700;
}

.x2sign-table tbody td {
    padding: 16px 5px 14px;
    border-top: 1px solid #f5f5f5;
    vertical-align: top;
}

.x2sign-table tbody tr:hover {
    background: #EFF3F5; 
}

");

/**
 *====================================================================
 *                           BODY OF VIEW
 * ===================================================================
 */

$awaiting = X2Html::fa('fa-exclamation-circle', array('style' => 'color:blue;'));
$others = X2Html::fa('fa-clock', array('style' => 'color:grey;'));
$cancelled = X2Html::fa('fa-exclamation-triangle', array('style' => 'color:red;'));
$completed = X2Html::fa('fa-check', array('style' => 'color:green;'));

$rows = array( 
    'row1' => 'Envelope Default Report', 
    'row2' => 'Envelope Activity Report', 
    'row3' => 'Envelope Velocity Report', 
    'row4' => 'Envelope Completed Report'
);

$row_summary = array(
    'row1' => 'Simple Report of Envelope',
    'row2' => 'Detail of Envelope Activity',
    'row3' => 'Totals for individual status on specific time periods',
    'row4' => 'Detail Report on Completed Envelopes'
);

$row_status = array(
    'row1' => $awaiting . ' ' . $others . ' ' . $cancelled . ' ' . $completed,
    'row2' => $awaiting . ' ' . $others . ' ' . $cancelled . ' ' . $completed,
    'row3' => $awaiting . ' ' . $others . ' ' . $cancelled . ' ' . $completed,
    'row4' => $completed
);

$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module', 'X2Sign Home'), 'url'=>array('x2sign/index')),
    array('label'=>Yii::t('module', 'X2Sign Report'))
));

//THIS VIEW GIVES THE USER OPTIONS TO SELECT WHICH REPORT
?>
<div id='content-container-inner'>
    <div class='form form2'>
        <div class='page-title' style='padding-bottom:5px padding-top:10px;'>
            <i style="color:grey; float:left;" class="fa fa-chart-line fa-2x"></i>   
            <h2> X2Sign Envelope Report </h2>
        </div><hr>
        <div class="list-status-box">
            <center>
            <span class="list-status-title">List of Status</span>
            <?php
                    echo '<div class="list-status">';
                    echo $awaiting . ' Awaiting  ';
                    echo $others . ' Waiting Others  ';
                    echo $cancelled . ' Cancelled  ';
                    echo $completed . ' Completed';
                    echo '</div>';
            ?>
            </center>
        </div><hr>
        <div>
           <table class="x2sign-table"> 
               <thead>
                   <th style="width:60%;">Name</th>
                   <th style="width:40%; text-align:right;">Envelope Status</th>
               </thead>
               <tbody>
               <?php
                   foreach( $rows as $row => $row_title) {
                       echo '<tr id="'. $row . '">';    
                       echo '<td>';
                       echo '<span style="display:block;font-size:13px;font-weight:700;">' . $row_title . '</span>'; 
                       echo '<span>'. $row_summary[$row] .'</span>';
                       echo '</td>';
                       echo '<td>';
                       echo '<span style="float:right;">' . $row_status[$row]  . '</span>'; 
                       echo '</td>';
                       echo '</tr>';
                   }
               ?> 
               </tbody>
           </table>
        </div>
    </div>
</div>
