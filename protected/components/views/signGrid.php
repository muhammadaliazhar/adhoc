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





Yii::app()->clientScript->registerCss('actionMenu',"

#action-menu-right-widget a {
    text-decoration: none;
    color: black;
}

");

$X2SignEnvelope = Modules::displayName(false, 'X2SignEnvelopes');
$X2SignEnvelopes = Modules::displayName(true, 'X2SignEnvelopes');

Yii::app()->clientScript->registerScript('setShowActions', '
    if (typeof x2 == "undefined")
        x2 = {};
    x2.setShowActions = function(type) {
        var saveShowActionsUrl = '.json_encode(Yii::app()->controller->createUrl('/actions/actions/saveShowActions')).';
        var viewUrl = "'.Yii::app()->controller->createUrl('/actions/actions/viewAll').'";
        $.post(
            saveShowActionsUrl,
            { ShowActions: type }
        );
    };
');

?>
<ul id='action-menu-right-widget'>
    <li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('x2sign/index'); ?>'
            >
            <?php echo $total; ?></a>
        </strong><?php 
        echo Yii::t('app','Total {Action}|Total {Actions}', array(
            $total,
            '{Action}' => $X2SignEnvelope,
            '{Actions}' => $X2SignEnvelopes,
        ));
    ?></li>
    <li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('/x2sign/index?status=Need to Sign'); ?>'
                >
            <?php echo $actionReq; ?></a>
        </strong><?php 
        echo Yii::t('app','Actions Required {Action}|Actions Required {Actions}', array(
            $actionReq,
            '{Action}' => $X2SignEnvelope,
            '{Actions}' => $X2SignEnvelopes,
        ));
    ?></li>
    <li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('/x2sign/index?status=Waiting for Others'); ?>'
                >
            <?php echo $waiting; ?></a>
        </strong><?php 
        echo Yii::t('app','Waiting for Others {Action}|Waiting for Others {Actions}', array(
            $waiting,
            '{Action}' => $X2SignEnvelope,
            '{Actions}' => $X2SignEnvelopes,
        ));
    ?></li>
        <li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('/x2sign/index?status=Cancelled'); ?>'
               >
            <?php echo $cancelled; ?></a>
        </strong><?php 
        echo Yii::t('app','Cancelled {Action}|Cancelled {Actions}', array(
            $cancelled,
            '{Action}' => $X2SignEnvelope,
            '{Actions}' => $X2SignEnvelopes,
        ));
    ?></li>
    <li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('/x2sign/index?status=Completed'); ?>'
                >
            <?php echo $completed; ?></a>
        </strong><?php 
        echo Yii::t('app','Completed {Action}|Completed {Actions}', array(
            $completed,
            '{Action}' => $X2SignEnvelope,
            '{Actions}' => $X2SignEnvelopes,
        ));
    ?></li>
</ul>

