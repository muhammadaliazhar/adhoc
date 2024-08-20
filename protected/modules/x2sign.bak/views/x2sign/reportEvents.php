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

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/report.css');

$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module', 'X2Sign Home'), 'url' =>array('x2sign/index')),
    array('label'=>Yii::t('module', 'X2Sign Report'), 'url'=>array('x2sign/report'))
));

?>

<div id='content-container-inner'>
    <div class='form form2'>

        <div class='page-title' style='padding-bottom:5px padding-top:10px;'>
            <i style="color:grey; float:left;" class="fa fa-chart-line fa-2x"></i>
            <h2>X2Sign Envelope Report</h2>
        </div><hr>

        <div class="brief-description">
            <h3><?php echo $report_title; ?></h3>
            <div class="report-options">
                <div class="button1-list">
                    <button class="button main-button">RUN REPORT</button> 
                    <button class="button main-button">RESET</button>
                </div>
                <div class="button2-list" style = "display: none;">
                    <div style="float:right;">
                        <button class="button" id="info-button">
                            <i class="fas fa-info-circle" title="help"></i>
                        </button>
                        <button class="button" id="email-button">
                            <i class="fas fa-envelope-open" title="email"></i>
                        </button>
                        <button class="button" id="print-button">
                            <i class="fas fa-print" title="print"></i>
                        </button>
                        <button class="button" id="download-button">
                            <i class="fas fa-arrow-alt-circle-down" title="download"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="generating-report">
            <?php
                $this->widget('X2ActiveGridView', array(
                'id' => 'x2signReportEvent-grid',
                'modelName' => 'X2SignEvents',
                'dataProvider'=>$dataProvider,
                'baseScriptUrl'=> Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
                'template'=> '{items}{pager}',
                'buttons' => array ('autoResize'),
                'gvSettingsName'=>'x2signEventIndex',
                'defaultGvSettings' => array (
                    'type'        => 80,
                    'envelopeId'  => 200,
                    'documentId'  => 150,
                    'createDate'  => 120
                ),
                'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
                'specialColumns'=>$columns
                ));
            ?>
        </div>

    </div>
</div>
