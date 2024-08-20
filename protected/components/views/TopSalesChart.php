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
echo '<div class="widget-title">Top Performers</div>';
echo '</div>';
echo '<div style="  justify-content: center;  align-content: center; display: flex; align-items: center; flex-direction: column;">';

echo '<div style="width:65%;"><br><div class= "submenu-title-bar widget-title-bar contacts ui-sortable-handle"><div id= "Sales Title 1" class="widget-title">New Buyers this Month</div></div><div  style="overflow:scroll; height:200px;">';

$dataProvider=new CArrayDataProvider($TopBuyers,array(

'sort'=>array('attributes'=>array('assignedTo','count(assignedTo)'),),

'pagination'=>array('pageSize'=>25)


));


echo "
<style>
#post-grid  td {
  border: none !important;
 
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


$this->widget('zii.widgets.grid.CGridView', array(

	'id'=>'post-grid',

	'dataProvider'=>$dataProvider,

	
	'cssFile' => '',	
		

 	'summaryText' => '', 

	'columns'=>array(

     	       array(
       		 'header'=>'Rank',
       		 'value'=>'$this->grid->dataProvider->pagination->currentPage * $this->grid->dataProvider->pagination->pageSize + ($row+1)',
                'htmlOptions'=>array('width'=>'30' , 'style' => 'background-color:#FFFFFF' ),
               'headerHtmlOptions'=>array('width'=>'30' , 'style' => 'background-color:#FFFFFF' ),

	      ),

		array(

		'type'=>'raw',

		'name'=>'Agent',

		'value'=>'CHtml::link(CHtml::encode(Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"])) ? Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"]))->name :  $data["assignedTo"]),array("/profile/profile/viewName","name"=>$data["assignedTo"]))',

                'htmlOptions'=>array('width'=>'135', 'style' => 'background-color:#FFFFFF'  ),
                'headerHtmlOptions'=>array('width'=>'135', 'style' => 'background-color:#FFFFFF' ),


		),

		array(

		'type'=>'raw',

		'name'=>'Count',

		'value'=>'\'<div style="text-align:center;">\' . $data["count(assignedTo)"]  . \'</div>\'',

                'htmlOptions'=>array('width'=>'110' , 'style' => 'background-color:#FFFFFF' ),
                'headerHtmlOptions'=>array('width'=>'110' , 'style' => 'background-color:#FFFFFF' ),


		),


	),

));



echo '</div></div><br><div style="width:65%;"><br><div class= "submenu-title-bar widget-title-bar contacts ui-sortable-handle"><div id= "Sales Title 2" class="widget-title">New Listings this Month</div></div><div  style="overflow:scroll; height:200px;">';


$TopListdataProvider=new CArrayDataProvider($topList,array(

'sort'=>array('attributes'=>array('name','c_2024_Actual_Listings'),),

'pagination'=>array('pageSize'=>25)


));



$this->widget('zii.widgets.grid.CGridView', array(

        'id'=>'post-grid2',

 	'summaryText' => '', 

        'dataProvider'=>$TopListdataProvider,

	'cssFile' => '',

        'columns'=>array(

               array(
                 'header'=>'Rank',
                 'value'=>'$this->grid->dataProvider->pagination->currentPage * $this->grid->dataProvider->pagination->pageSize + ($row+1)',
                'htmlOptions'=>array('width'=>'30' , 'style' => 'background-color:#FFFFFF' ),
                'headerHtmlOptions'=>array('width'=>'30' , 'style' => 'background-color:#FFFFFF' ),

              ),



                array(

		'type'=>'raw',

                'name'=>'Agent',

                'value'=>'CHtml::link(CHtml::encode(Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"])) ? Employees::model()->findByAttributes(array("c_user__c" => $data["assignedTo"]))->name :  $data["assignedTo"]),array("/profile/profile/viewName","name"=>$data["assignedTo"]))',




                'htmlOptions'=>array('width'=>'135', 'style' => 'background-color:#FFFFFF'  ),
                'headerHtmlOptions'=>array('width'=>'135', 'style' => 'background-color:#FFFFFF' ),

                ),

                array(

		'type'=>'raw',	

                'name'=>'Count',

                'value'=>'\'<div style="text-align:center;">\' . $data["count(assignedTo)"] . \'</div>\'',

                'htmlOptions'=>array('width'=>'110', 'style' => 'background-color:#FFFFFF'  ),
                'headerHtmlOptions'=>array('width'=>'110', 'style' => 'background-color:#FFFFFF' ),

                ),


        ),

));


echo '</div></div><br><div style="width:65%;"><br><div class= "submenu-title-bar widget-title-bar contacts ui-sortable-handle"><div id= "Sales Title 3" class="widget-title">Sold Deals this Year</div></div><div  style="overflow:scroll; height:200px;">';


$TopSoldListdataProvider=new CArrayDataProvider($topSoldList,array(

'sort'=>array('attributes'=>array('name','c_2024_Actual_Sold_Deals'),),

'pagination'=>array('pageSize'=>25)


));



$this->widget('zii.widgets.grid.CGridView', array(

        'id'=>'post-grid3',

	'summaryText' => '', 

        'dataProvider'=>$TopSoldListdataProvider,

	'cssFile' => '',

        'columns'=>array(

               array(
                 'header'=>'Rank',
                 'value'=>'$this->grid->dataProvider->pagination->currentPage * $this->grid->dataProvider->pagination->pageSize + ($row+1)',
                'htmlOptions'=>array('width'=>'30', 'style' => 'background-color:#FFFFFF' ),
                'headerHtmlOptions'=>array('width'=>'30', 'style' => 'background-color:#FFFFFF' ),

               ),


                array(

		'type'=>'raw',

                'name'=>'Agent',

                'value'=>'CHtml::link(CHtml::encode($data["name"]),array("/profile/profile/viewName","name"=>$data["c_user__c"]))',

                'htmlOptions'=>array('width'=>'135', 'style' => 'background-color:#FFFFFF'  ),
                'headerHtmlOptions'=>array('width'=>'135', 'style' => 'background-color:#FFFFFF' ),

                ),

                array(

		'type'=>'raw',

                'name'=>'Count',

                'value'=>'\'<div style="text-align:center;">\' . $data["c_2024_Actual_Sold_Deals"] . \'</div>\'',

                'htmlOptions'=>array('width'=>'110', 'style' => 'background-color:#FFFFFF'  ),
                'headerHtmlOptions'=>array('width'=>'110', 'style' => 'background-color:#FFFFFF' ),

                ),


        ),

));

echo '</div>';
echo '</div>';

echo '</div>';
echo '</center>';
echo '<br>';

?>


