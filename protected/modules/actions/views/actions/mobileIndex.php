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

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/Property.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/Property.js');

// Import Bootstrap
Yii::app()->clientScript->registerCssFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
Yii::app()->clientScript->registerScriptFile('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js');
Yii::app()->clientScript->registerScriptFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js');

Yii::app()->clientScript->registerScript('sellerEventsJS', "
    $('#seller-search-btn').on('click', function() {
        console.log($('#seller-search-input').val());
        var searchTerm = $('#seller-search-input').val()
        if (!searchTerm) {
            alert('Please Enter A Buyers\'s Name To Search');
            return;
        }
        $.fn.yiiListView.update('properties-grid', {
            url: window.location.href,
            data: 'name='+searchTerm,
        });
    });

    $('#sort-btn').on('click', function () {
        var order = $(this).data('order');
        if (order == 'DESC') {
            $(this).data('order', 'ASC');
        } else {
            $(this).data('order', 'DESC');
        }

        $.fn.yiiListView.update('properties-grid', {
            url: window.location.href,
            data: 'sortOrder='+order,
        });
    });
", CClientScript::POS_END);

if(!isset($filter)) //if rendered without filter attribute
    $filter = array(
        'all' => 'All',
        'thisMonth' => 'This Month',
    );
if(!isset($sort))
    $sort =array(
        'recent' => 'Most Recent',
        'viewed' => 'Most Viewed',
        'alphabet' => 'Alphabetical',
    );
?>
<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBZmjPYHzHtYymcpUvMi1c-qKTh2RRVNeg&libraries=places">
</script>
<div id="pictures-wrapper">
    <?php $this->widget('FileUploader', array(
        'id' => 'pictures',
        'mediaParams' => ['associationType' => 'Properties'],
        'viewParams' => ['showButton' => false, 'closeButton' => false],
        'acceptedFiles' => 'image/*',
        ));
    ?>
</div>

<div id="agents" class="container grid-mb columns mt-4" style="display: none;">
    <div class="column">
        <div class="form-group bordered p-4" style="max-width: 600px; margin: 0px auto;">
            <label for="agent1DropdownMenu">Agent 1</label>
            <div class="dropdown show col-12 mb-4" style="width: 100%;">
                <div class="dropdown form-input btn-group" href="#" role="button" id="agent1DropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="agent-tile">
                        <div class="tile-icon"><?php echo Profile::renderAvatarImage($user->id, 45, 45, array('style' => 'border-radius: 50%')); ?></div>
                        <div class="tile-content" style="padding-left: 0.4rem;">
                            <div class="text-lg text-bold"><?php echo $user->firstName . " " . $user->lastName; ?></div>
                            <div class="text-ellipsis"><?php echo $user->emailAddress; ?></div>
                        </div>
                    </div>
                </div>

                <div class="dropdown-menu" aria-labelledby="agent1DropdownMenu">
                    <a class="dropdown-item" href="#">Invite Agent</a>
                </div>

                <div class="form-group">
                    <label for="formLicense">License</label>
                    <input name="license" type="text" class="form-control" id="formLicense" value="<?php  ?>" placeholder="Please enter your license #">
                </div>
                <div class="form-group">
                    <label for="formTitle">Title</label>
                    <input name="title" type="text" class="form-control" id="formTitle" value="<?php ?>" placeholder="Please enter your title">
                </div>
                <div class="form-group">
                    <label for="formPhone">Phone Number</label>
                    <input name="phone" type="text" class="form-control" id="formPhone" value="<?php echo $profile->officePhone; ?>" placeholder="Please enter your phone #">
                </div>
                <div class="form-group">
                    <label for="formWebsite">Website</label>
                    <input name="website" type="text" class="form-control" id="formWebsite" value="<?php ?>" placeholder="Please enter website URL">
                </div>
                <div class="form-group">
                    <label for="formBrokerName">Brokerage Name</label>
                    <input name="brokerName" type="text" class="form-control" id="formBrokerName" value="<?php ?>" placeholder="Please enter your brokerage name">
                </div>
                <div class="form-group">
                    <label for="formBrokerLicense">Brokerage License</label>
                    <input name="brokerLicense" type="text" class="form-control" id="formBrokerLicense" value="<?php  ?>" placeholder="Please enter your brokerage license #">
                </div>
                <div class="form-group">
                    <label for="formBrokerPhone">Brokerage Phone Number</label>
                    <input name="brokerPhone" type="text" class="form-control" id="formBrokerPhone" value="<?php  ?>" placeholder="Please enter your brokerage phone number">
                </div>
                <div class="form-group">
                    <label for="formBrokerWebsite">Brokerage Website</label>
                    <input name="brokerWebsite" type="text" class="form-control" id="formBrokerWebsite" value="<?php  ?>" placeholder="Please enter your brokerage website URL">
                </div>
            </div>
        </div>
    </div>
    <div class="column">
        <div class="form-group bordered p-4" style="max-width: 600px; margin: 0px auto;">
            <label for="agent2DropdownMenu">Agent 2</label>
            <div class="dropdown show col-12 mb-4" style="width: 100%;">
                <div class="dropdown form-input btn-group" href="#" role="button" id="agent2DropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="agent-tile">
                        <div class="tile-content text-lg text-bold">
                            INVITE USER
                        </div>
                    </div>
                </div>

                <div class="dropdown-menu" aria-labelledby="agent2DropdownMenu">
                    <a class="dropdown-item" href="#">Invite Agent</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container property" style="border-left: .05rem solid #e7e9ed; border-right: .05rem solid #e7e9ed;">
    <div class="container pt-2">
        <div class="w-100 row mb-2">
        </div>
    </div>

    <div class="pt-3 container rounded" style="background-color: #f4f4f4;">
    <?php
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/actionHistory.css');
Yii::app()->clientScript->registerResponsiveCss('responsiveActionsCss',"

@media (max-width: 759px) {

    #action-frame {
        height: 366px !important;
    }

    #action-view-pane {
        width: 100% !important;
    }

    #action-list > .items {
        margin-right: 0 !important;
        border: none !important;
    }

}

");



?>
<div class="responsive-page-title page-title icon actions" id="page-header">
    <h2>
    <?php echo Yii::t('actions','{module}', array(
        '{module}' => Modules::displayName(),
    ));?>
    </h2>
    <?php 
    echo ResponsiveHtml::gripButton ();
    ?>
        <br>
        <div class='responsive-menu-items' style="display: block !important; float: left !important;">
        <?php
        /*
        disabled until fixed header is added
        echo CHtml::link(Yii::t('actions','Back to Top'),'#',array('class'=>'x2-button right','id'=>'scroll-top-button')); */
        echo CHtml::link(Yii::t('actions','Filters'),'#',array('class'=>'controls-button x2-button right','id'=>'advanced-controls-toggle')); 
        //echo CHtml::link(
        //    Yii::t('actions','New {module}', array(
        //        '{module}' => Modules::displayName(false),
        //    )),
        //    array('/actions/actions/create'),
        //    array('class'=>'controls-button x2-button right','id'=>'create-button')
        //); 
        //echo CHtml::link(Yii::t('actions','Switch to Grid'),array('index','toggleView'=>1),array('class'=>'x2-button right')); ?>
        </div>
</div>
<br>
<br>
<br>
<div>
<?php 
echo $this->renderPartial('_advancedControls',$params,true);
$this->widget('zii.widgets.CListView', array(
    'id'=>'action-list',
    'dataProvider'=>$dataProvider,
    'itemView'=>'application.modules.actions.views.actions._viewIndex',
    'htmlOptions'=>array('class'=>'action x2-list-view list-view','style'=>'width:100%'),
    'viewData'=>$params,
    'template'=>'{items}{pager}',
    'afterAjaxUpdate'=>'js:function(){
        clickedFlag=false;
        lastClass="";
        $(\'#advanced-controls\').after(\'<div class="form x2-layout-island" id="action-view-pane" style="float:right;width:0px;display:none;padding:0px;"></div>\');
    }',
    'pager' => array(
        'class' => 'ext.infiniteScroll.IasPager',
        'rowSelector'=>'.view',
        'listViewId' => 'action-list',
        'header' => '',
        'options' => array(
            'history' => true,
            'triggerPageTreshold' => 2,
            'trigger'=>Yii::t('app','Load More'),
            'scrollContainer'=>'.items',
            'container'=>'.items',
        ),
      ),
));
?>
</div>
<script>
    var clickedFlag=false;
    var lastClass="";
    /* disabled until fixed header is added
    $(document).on('click','#scroll-top-button',function(e){
        e.preventDefault();
        $(".items").animate({ scrollTop: 0 }, "slow");
    });*/
    $(document).on('click','#advanced-controls-toggle',function(e){
        e.preventDefault();
        if($('#advanced-controls').is(':hidden')){
            $("#advanced-controls").slideDown();
        }else{
            $("#advanced-controls").slideUp();
        }
    });
    $(document).on('ready',function(){
        $('#advanced-controls').after('<div class="form" id="action-view-pane" style="float:right;width:0px;display:none;padding:0px;"></div>');
    });
    <?php 
    if (AuxLib::isIPad ()) { 
        echo "$(document).on('vclick', '.view', function (e) {" ;
    } else {
        echo "$(document).on('click','.view',function(e){";
    }
    ?>
        if(!$(e.target).is('a')){
            e.preventDefault();
            if(clickedFlag){
                if($('#action-view-pane').hasClass($(this).attr('id'))){
                    $('#action-view-pane').removeClass($(this).attr('id'));
                    $('.items').animate({'margin-right': '20px'},400,function(){
                        $('.items').css('margin-right','0px')
                    });
                    $('#action-view-pane').html('<div style="height:800px;"></div>');
                    $('#action-view-pane').animate({width: '0px'},400,function(){
                        $('#action-view-pane').hide();
                    });
                    $(this).removeClass('important');
                    clickedFlag=!clickedFlag;
                }else{
                    $('#'+lastClass).removeClass('important');
                    $(this).addClass('important');
                    $('#action-view-pane').removeClass(lastClass);
                    $('#action-view-pane').addClass($(this).attr('id'));
                    lastClass=$(this).attr('id');
                    x2.actionFrames.setLastClass (lastClass);
                    var pieces=lastClass.split('-');
                    x2.actionFrames.setLastClass (lastClass);
                    var id=pieces[1];
                    $('#action-view-pane').html(
                        '<iframe id="action-frame" src="<?php 
                            echo Yii::app()->controller->createAbsoluteUrl(
                            '/actions/actions/viewAction'); ?>?id=' + id +
                            '" onload="x2.actionFrames.createControls(' + id + ', false);">' +
                        '</iframe>');
                }
            }else{
                $(this).addClass('important');
                if (x2.isAndroid)
                    $('.items').css('margin-right','20px').animate({'margin-right': '5%'});
                else
                    $('.items').css('margin-right','20px').animate({'margin-right': '0%'});
                $('#action-view-pane').addClass($(this).attr('id'));
                lastClass=$(this).attr('id');
                var pieces=lastClass.split('-');
                x2.actionFrames.setLastClass (lastClass);
                var id=pieces[1];
                $('#action-view-pane').show();
                $('#action-view-pane').attr('style', 'width: 100% !important;');
                //$('#action-view-pane').animate({width: '59%'});
                clickedFlag = !clickedFlag;
                $('#action-view-pane').html(
                    '<iframe id="action-frame" src="<?php 
                        echo Yii::app()->controller->createAbsoluteUrl(
                        '/actions/actions/viewAction'); ?>?id=' + id +
                        '" onload="x2.actionFrames.createControls(' + id + ', false);">' +
                    '</iframe>');
            }
        }
    });

</script>
<style>
    #action-frame {
        width:99%;
        height:800px;
    }
    @media (max-width: 759px) {
        #action-view-pane {
            width: 100%;
        }
    }
    .complete{
        color:green;
    }
</style>

    </div>
</div>



