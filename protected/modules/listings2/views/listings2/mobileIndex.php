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
            alert('Please Enter A Listing\'s Name To Search');
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
            <div class="col-8">
                <h5>Recent Listings</h5>
            </div>
            <div class="d-flex col-4 align-items-center justify-content-end">
                <!-- <button class="btn mr-2" style="color: #203046;border-color: #203046;background-color: white;width: 56px;height: 30px;font-size: 12px;">Filter</button> -->
                <!-- <button id="sort-btn" class="btn" style="color: white;background-color: #203046;border-color: #203046;width: 56px;height: 30px;font-size: 12px;" data-order="ASC">Sort</button> -->
            </div>
        </div>
    </div>

    <div class="pt-3 container rounded" style="background-color: #f4f4f4;">
        <div class="w-100 mb-2">
            <div class="input-group mb-3">
                <input id="seller-search-input" type="text" class="form-control" placeholder="Enter Name Here" aria-label="Address Search" aria-describedby="basic-addon2">
                <span class="input-group-text" id="seller-search-btn" style="margin-left: -1px;border-top-left-radius: 0;border-bottom-left-radius: 0;">Search</span>
            </div>
        </div>
        <div class="w-100 mb-2">
            <div class="w-100 mb-3">
            </div>
        </div>
    <?php
        $this->widget('X2ListView', array(
            'id'=>'properties-grid',
            'ajaxUpdate' => true,
            'ajaxUrl' => Yii::app()->request->baseUrl.'/index.php/listings2/searchSellers',
            //'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name. '/css/gridview',
            'template' => '{items}{pager}',
            'itemView' => '_propListItem',
            'dataProvider' => $model->search(),
            'itemsCssClass' => 'propItems', 
        ));
    ?>
    </div>
</div>
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
            alert('Please Enter A Listing\'s Name To Search');
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
            <div class="col-8">
                <h5>Recent Listing</h5>
            </div>
            <div class="d-flex col-4 align-items-center justify-content-end">
                <!-- <button class="btn mr-2" style="color: #203046;border-color: #203046;background-color: white;width: 56px;height: 30px;font-size: 12px;">Filter</button> -->
                <button id="sort-btn" class="btn" style="color: white;background-color: #203046;border-color: #203046;width: 56px;height: 30px;font-size: 12px;" data-order="ASC">Sort</button>
            </div>
        </div>
    </div>

    <div class="pt-3 container rounded" style="background-color: #f4f4f4;">
        <div class="w-100 mb-2">
            <div class="input-group mb-3">
                <input id="seller-search-input" type="text" class="form-control" placeholder="Enter Name Here" aria-label="Address Search" aria-describedby="basic-addon2">
                <span class="input-group-text" id="seller-search-btn" style="margin-left: -1px;border-top-left-radius: 0;border-bottom-left-radius: 0;">Search</span>
            </div>
        </div>
        <div class="w-100 mb-2">
            <div class="w-100 mb-3">
            </div>
        </div>
    <?php
        $this->widget('X2ListView', array(
            'id'=>'properties-grid',
            'ajaxUpdate' => true,
            'ajaxUrl' => Yii::app()->request->baseUrl.'/index.php/listings2/searchSellers',
            //'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name. '/css/gridview',
            'template' => '{items}{pager}',
            'itemView' => '_propListItem',
            'dataProvider' => $model->search(),
            'itemsCssClass' => 'propItems', 
        ));
    ?>
    </div>
</div>


