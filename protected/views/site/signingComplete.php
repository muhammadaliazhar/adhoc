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
 * @author Clifton Chiang <clifton@x2engine.com>
 */

Yii::app()->clientScript->registerCss('signingComplete', '
    #complete {
        position: absolute;
        top: 35%;
        left: 50%;
        margin-right: -50%;
        transform: translate(-50%, -25%);
    }
   
    p {
        text-align: center;
        font-size: 24px;  
    } 

    img {
        position: absolute;
        left: 50%;
        margin-right: -50%;
        transform: translate(-50%, -20%);   
    }
');
 ?>

<div id="complete" >
        <h2 style='font-size: 55px;'>You finished signing the envelope!</h2>
        <p style="
    text-align: center;
    font-size: 45px;
">An executed copy will be sent to you</p>
        <img style="200px;" src=<?php 
            echo Yii::app()->createExternalUrl('/x2sign/x2sign/getFile', array(
                'id' => 201364115,
            ));
        ?>>
    </div>
