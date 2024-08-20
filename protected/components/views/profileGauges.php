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

echo '<center style = "background-color: #e7e7e7; box-shadow: 3px 3px 5px #b3b8bd;">';

echo '<div class= "submenu-title-bar widget-title-bar contacts ui-sortable-handle">';
echo '<div class="widget-title">Goals</div>';
echo '</div>';
echo '<div style="  justify-content: center;  align-content: center; display: flex; align-items: center;">';

$prof = Yii::app()->params->profile;
$prof->setGoals(); 

echo '<div class="cell span-6">';
$this->Widget('ext.justgage.JustGage', array(
    'options' => array(
        'value' => (int)$prof->getNewListings(),
        'min' => 0,
        'max' => ($prof->newListings ? $prof->newListings : 0),
        'title' => "New Listing",
    ),

));
//echo '</b><b>';
 
echo '</div><div class="cell span-6">';


$this->Widget('ext.justgage.JustGage', array(
    'options' => array(
        'value' => (int)$prof->getSoldDeals(),
        'min' => 0,
        'max' => ($prof->soldDeals ? $prof->soldDeals : 0),
        'title' => "Sold Deals",
    ),
	

));
//echo '</b><b>';

echo '</div><div class="cell span-6">';

$this->Widget('ext.justgage.JustGage', array(
    'options' => array(
        'value' => (int)$prof->getGrossCommission(),
        'min' => 0,
        'max' => ($prof->grossCommission ? $prof->grossCommission : 0),
        'title' => "Gross Commission",
    ),
	
));
echo '</div>';
echo '</div>';

echo '</center>';
echo '<br>';

?>


