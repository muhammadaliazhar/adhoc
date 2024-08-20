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
 * @author Justin Toyomitsu <justin@x2engine.com>
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

$column = array(
    "thisWeek"   => "This Week",
    "lastWeek"   => "Last Week",
    "thisMonth"  => "This Month",
    "lastMonth"  => "Last Month",
    "last6Month" => "Last 6 Months",
    "thisYear"   => "This Year"
);

$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module', 'X2Sign Home'), 'url' =>array('x2sign/index')),
    array('label'=>Yii::t('module', 'X2Sign Report'), 'url'=>array('x2sign/report'))
));

?>

<div id='content-container-inner'>
    <div class='form form2'>

        <div class='page-title' style='padding-bottom:5px padding-top:10px;'>
            <i style="color:grey; float:left;" class="fa fa-chart-line fa-2x"></i>
            <h2>X2Sign Envelope Velocity Report</h2>
        </div><hr>
        <div class="generating-velocity-report">
            <table class="x2sign-table">
                <thead>
                   <th style="width:30%; text-align:left;">Time Period</th>
                   <th style="width:40%; text-align:center;">Dates</th>
                   <th style="width:30%; text-align:right;">Count</th>
               </thead>
               <tbody>
               <?php
                   foreach( $column as $key => $value) {
                       echo '<tr id="'. (($velocity_count[$key] > 0) ? $key : '') . '">';
                       echo '<td style="font-size:13px; text-align:left;">';
                       echo '<span>' . $column[$key] . '</span>';
                       echo '</td>';
                       echo '<td style="font-size:13px; text-align:center;">';
                       echo '<span>' . $velocity_dates[$key] . '</span>';
                       echo '</td>';
                       echo '<td style="text-align:right;">';
                       echo '<span>' . (($velocity_count[$key] > 0) ? $velocity_count[$key] : '--') . '</span>';
                       echo '</td>';
                       echo '</tr>';
                   }
               ?>
               </tbody> 
            </table>
        </div>

    </div>
</div>
