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

echo '<center id = "TopID"  style = "background-color: #e7e7e7; box-shadow: 3px 3px 5px #b3b8bd;">';

echo '<div class= "submenu-title-bar widget-title-bar contacts ui-sortable-handle">';
echo '<div class="widget-title" style="font-size: 15pt;">2024 Monthly Contests</div>';
echo '</div>';
echo '<div style="  justify-content: center;  align-content: center; display: flex; align-items: center; flex-direction: column;">';

echo '<div style="width:100%;"><br><div class= "submenu-title-bar widget-title-bar contacts ui-sortable-handle"><div id= "Sales Title 1" class="widget-title" style="font-size: 15pt;">New listings in January</div></div><div  style="overflow:scroll; height:450px;">';


//echo '<a href="https://transworldbusiness.sharepoint.com/:f:/s/TransworldBusinessAdvisors/Eus9vkrZN8pKtrqx1Vt3pzQBzgnMYZgzaaMq9APDd9XvFg?e=dwfdpp" target="_blank"><img style="width:100%;" src="https://sydney.tworld.com/index.php/media/media/getFile/id/201059348/key/c700c629994b1ded068baa4df0e1492ddeeb06391b200acc401113437bb190e0" alt="Comp"></a>';


$dataProvider=new CArrayDataProvider($topList,array(

'sort'=>array('attributes'=>array('assignedTo','count(assignedTo)'),),

'pagination'=>array('pageSize'=>25)


));


echo "
<style>
#new-grid  td {
  border: thin solid grey !important;
 
}


#post-grid2  td {
  border: none !important;

}
#post-grid3  td {
  border: none !important;

}

#post-grid  {
  padding: 0px !important;
 
}


#post-grid2  {
  padding: 0px !important;

}
#post-grid3  {
  padding: 0px !important;

}



</style>";

//echo '<img style=" height: 90%;" src="https://sydney.tworld.com/index.php/media/media/getFile/id/200795471/key/296030d670c1c067410fb3da783d21f6740bb61650931fbbf1c9b0386bddf257" alt="officeComp">';




$this->widget('zii.widgets.grid.CGridView', array(

    'id'=>'new-grid',

    'dataProvider'=>$dataProvider,

    
    'cssFile' => '',    
        

    'summaryText' => '', 

    'columns'=>array(

               array(
             'header'=>'Rank',
             'value'=>'$this->grid->dataProvider->pagination->currentPage * $this->grid->dataProvider->pagination->pageSize + ($row+1)',
                'htmlOptions'=>array('width'=>'50' , 'style' => 'background-color:#FFFFFF; font-size: 12pt;' ),
               'headerHtmlOptions'=>array('width'=>'50' , 'style' => 'background-color:#FFFFFF; font-size: 12pt;' ),

          ),

        array(

        'type'=>'raw',

        'name'=>'Agent',

        'value'=>'"<div style=\'overflow: hidden;\'><div style=\'float:left;\'>" 
        . Profile::renderLowQualityAvatarUsername($data["assignedTo"],45) 
        . "</div><div style=\'margin: auto;overflow: hidden; padding:5px 10px 0px 10px;\'>" 
        . CHtml::link(CHtml::encode(Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"])) 
        ? Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"]))->name 
        :  $data["assignedTo"]),array("/profile/profile/viewName","name"=>$data["assignedTo"])) 
        . "<br>" 
        . (Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"])) 
        ? " Franchise: " . Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"]))->linkFranch() 
        :  "")
        . "</div></div>"',

                'htmlOptions'=>array('width'=>'180', 'style' => 'background-color:#FFFFFF;font-size: 12pt;'  ),
                'headerHtmlOptions'=>array('width'=>'180', 'style' => 'background-color:#FFFFFF;font-size: 12pt;' ),


        ),

        array(

        'type'=>'raw',

        'name'=>'Count',

        'value'=>'\'<div style="text-align:center;">\' . $data["count(assignedTo)"]  . \'</div>\'',

                'htmlOptions'=>array('width'=>'130' , 'style' => 'background-color:#FFFFFF;font-size: 12pt;' ),
                'headerHtmlOptions'=>array('width'=>'130' , 'style' => 'background-color:#FFFFFF;font-size: 12pt;' ),


        ),


    ),

));



echo '</div></div>';

echo '</div>';
echo '</center>';
echo '<br>';

?>


