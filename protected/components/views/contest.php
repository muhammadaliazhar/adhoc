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

Yii::app()->clientScript->registerScript('Highlight Contest', '

$(".contest").each(function(el){
    var value = parseInt($(this).attr("data-color"), 10);
       if(value >= 4){
           $(this).closest("tr").css("cssText", "background-color: #C6E0B3 !important;");
       }else{
           $(this).closest("tr").css("cssText", "background-color: #FFFFFF !important;");
       }
});


', CClientScript::POS_READY);

Yii::app()->clientScript->registerCss("AvatarCss", "

#contest-table0 .empty{
    background-color: white;
}

#contest-table1 .empty{
    background-color: white;
}

#contest-table2 .empty{
    background-color: white;
}

");

echo '<center style = "background-color: #e7e7e7;">';

echo '<div class="submenu-title-bar widget-title-bar contacts ui-sortable-handle">';
echo '<div class="widget-title" style="float:none !important;">Listing Contest</div>';
echo '</div>';
echo '<div style="justify-content: center; align-content:center; display:flex; align-items:center;">';
?>

<table>
    <tr style="background-color:#00431B; height:30px;">
        <th style="text-align:center; font-weight:bold; font-size:13px; color:white !important;"> July </th>
        <th style="text-align:center; font-weight:bold; font-size:13px; color:white !important;"> August </th>
        <th style="text-align:center; font-weight:bold; font-size:13px; color:white !important;"> September </th>
    </tr>
    <tr>

<?php
$months = 3;
for($i = 0; $i < 3; $i++){
    echo '<td style="vertical-align: top;">';
    $dataProvider=new CArrayDataProvider($months_dataProvider[$i],array(
    'sort'=>array('attributes'=>array('assignedTo','count(assignedTo), spot'),),
    'pagination'=>array('pageSize'=>30)));

    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'contest-table' . $i,
        'dataProvider'=>$dataProvider,
        'htmlOptions'=>array('style'=>'padding: 0px !important; overflow-y:auto; height:175px;'),
        'emptyText' => 'Stay Tuned for Results',
        'summaryText'=>'',
        'columns'=>array(
                array(
                'type'=>'raw',
                'name'=>'Rank',
                'value'=>'\'<div class="" style="text-align:center" >\' . $data["spot"]  . \'</div>\'',
                'htmlOptions'=>array('width'=>'15%'),
                'headerHtmlOptions'=>array('width'=>'15%'),
                ),
                array(
                'type'=>'raw',
                'name'=>'Agent',
                'value'=>'"<div style=\'overflow: hidden;\'><div style=\'float:left;\'>" 
        . Profile::renderLowQualityAvatarUsername($data["assignedTo"],65) 
        . "</div><div style=\'margin: auto;overflow: hidden; padding:5px 10px 0px 10px;\'>" 
        . CHtml::link(CHtml::encode(Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"])) 
        ? Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"]))->name 
        :  $data["assignedTo"]),array("/profile/profile/viewName","name"=>$data["assignedTo"])) 
        . "<br>" 
        . (Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"])) 
        ? " Franchise: " . Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"]))->linkFranch() 
        :  "")
        . "</div></div>"',

                'htmlOptions'=>array('width'=>'75%'),
                'headerHtmlOptions'=>array('width'=>'75%'),
                ),
                array(
                'type'=>'raw',
                'name'=>'Listings',
                'value'=>'\'<div class="contest" style="text-align:center" data-color=\'. $data["count(assignedTo)"] . \'>\' . $data["count(assignedTo)"]  . \'</div>\'',
                'htmlOptions'=>array('width'=>'15%'),
                'headerHtmlOptions'=>array('width'=>'15%'),
                ),
        ),
    ));
    echo '</td>';
}

echo '</tr>';
echo '</table>';

echo '</div>';
echo '</center>';
echo '<br>';
?>

