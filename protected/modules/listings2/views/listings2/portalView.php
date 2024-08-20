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
 * @author Justin Toyomitsu <justin@x2engine.com>, Peter Czupil <peter@x2engine.com>
 */

// Import Bootstrap

Yii::app()->clientScript->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');
Yii::app()->clientScript->registerScriptFile('https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js');


Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/View.css');
Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/X2VerticalTabs.css');
Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/MenuTables.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/View.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/X2VerticalTabs.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/pdfReader/build/pdf.js');
Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl.'/js/signable.js');



Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/x2sign.js');

Yii::app()->clientScript->registerScript('viewerActivityJS',"

    if (x2.x2sign === undefined) x2.x2sign = {};

    // check nda selection element for any errors, returns true if an error exists
    x2.x2sign.checkNda = function () {
        console.log($('#NDASelect').val());
        if ($('#NDASelect').val() == 'Select an NDA for Automation') return true;
        return false;
    }

    x2.x2sign.updateDocs = function(){
        let ListId = '" . $model->id . "';
        let NdaId =  $('#NDASelect').val();
        $.ajax({
            url: yii.baseUrl+'/index.php/x2sign/UpdateDocket',
            type: 'POST',
            data: {
                modelId: NdaId,
                ListId: ListId,
                " . Yii::app()->request->csrfTokenName . ": '" . Yii::app()->request->csrfToken . "', 
            },
            success: function (obj) {
                x2.topFlashes.displayFlash('Added To Listing', 'success');
                //location.reload();
            },
            error: function (data) {
                x2.topFlashes.displayFlash(data.responseText, 'error');
            },
            complete: function () {

            }
        });

    }

    $('#NDASelect').on('change', function() {
        $('#download-nda-btn').show();
    });

    $('#add-cim-btn').on('click', function () {
        $('#cimUploadModal').modal('show');
    })

    $('#afterSendOption').on('change', function () {
    let option = $('#afterSendOption').val();
        $.ajax({
            url: yii.baseUrl+'/index.php/listings2/updateBli/id/" . $model->id . "',
            type: 'POST',
            data: {
                'ajax': 1,
                'option' : option,
            },
            success: function (data) {
                x2.topFlashes.displayFlash('BLI Auto Saved', 'success');
                //update to validate 
                //location.reload();
                if (option == 'bli') {
                    $('#bliDocDropdown').css('display', 'block');
                    $('#previewBLIbutton').css('display', 'block');

                    $('#cim-select-div').css('display', 'none');
                    $('#cimEmailSelect').css('display', 'none');
                    $('#add-cim-btn').css('display', 'none');
                    $('#CIMSelect').css('display', 'none');
                    $('#cim-email-btn-div').css('display', 'none');
                    $('#add-cim-email-template-btn').css('display', 'none');
                } else if (option == 'cim') {
                    $('#bliDocDropdown').css('display', 'none');
                    $('#previewBLIbutton').css('display', 'none');

                    $('#cim-select-div').css('display', 'block');
                    $('#cimEmailSelect').css('display', 'block');
                    $('#add-cim-btn').css('display', 'block');
                    $('#CIMSelect').css('display', 'block');
                    $('#cim-email-btn-div').css('display', 'block');
                    $('#add-cim-email-template-btn').css('display', 'block');
                } else if (option == 'both') {
                    $('#bliDocDropdown').css('display', 'block');
                    $('#previewBLIbutton').css('display', 'block');

                    $('#cim-select-div').css('display', 'block');
                    $('#cimEmailSelect').css('display', 'block');
                    $('#add-cim-btn').css('display', 'block');
                    $('#CIMSelect').css('display', 'block');
                    $('#cim-email-btn-div').css('display', 'block');
                    $('#add-cim-email-template-btn').css('display', 'block');
                } else {
                    $('#bliDocDropdown').css('display', 'none');
                    $('#previewBLIbutton').css('display', 'none');

                    $('#cim-select-div').css('display', 'none');
                    $('#cimEmailSelect').css('display', 'none');
                    $('#add-cim-btn').css('display', 'none');
                    $('#CIMSelect').css('display', 'none');
                    $('#cim-email-btn-div').css('display', 'none');
                    $('#add-cim-email-template-btn').css('display', 'none');
                }
            },
            error: function (data) {
                x2.topFlashes.displayFlash(data.responseText, 'error');
            },
            complete: function () {

            }
        });
    });

    // check email selection element for any errors, returns true if an error exists
    x2.x2sign.checkEmail = function () {
        console.log($('#emailSelect').val());
        if ($('#emailSelect').val() == 'Select an email template') return true;
        return false;
    }

    // check email selection element for any errors, returns true if an error exists
    x2.x2sign.CIMcheckEmail = function () {
        console.log($('#cimEmailSelect').val());
        if ($('#cimEmailSelect').val() == 'Select an email template') return true;
        return false;
    }


    $('#docViewClose').on('click', function() {
        $('#pdf-nav').hide();
        $('#docViewModal').modal('hide');
    });

    $('#docViewModal').on('hide.bs.modal', function () {
        $('#pdf-nav').hide();
    }); 

    $('#add-nda-btn').on('click', function () {
        var err = x2.x2sign.checkNda();
        if (!err) {
            x2.x2sign.updateDocs();
            $('#email-select-div').show();
            $('#email-btn-div').show();
        } else {
            x2.topFlashes.displayFlash('Please select an NDA from the NDA CONFIGURATION dropdown', 'error');
        }
    });

    $('#bliCheckbox').change( function () {
        let checked = $(this).is(':checked');
        $.ajax({
            url: yii.baseUrl+'/index.php/listings2/updateBli/id/" . $model->id . "',
            type: 'POST',
            data: {
            },
            success: function (data) {
                x2.topFlashes.displayFlash('BLI Auto Saved', 'success');
                //update to validate 
                //location.reload();
                if (checked) {
                    $('#bliDocDropdown').css('display', 'block');
                    $('#previewBLIbutton').css('display', 'block');
                } else {
                    $('#bliDocDropdown').css('display', 'none');
                    $('#previewBLIbutton').css('display', 'none');
                }
            },
            error: function (data) {
                x2.topFlashes.displayFlash(data.responseText, 'error');
            },
            complete: function () {

            }
        });
    });


    //setting up a fuction to validate the NDA set up


    $('#check-NDA-setup-btn').on('click', function () {
        let ListId = '" . $model->id . "';
        $.ajax({
            url: yii.baseUrl+'/index.php/x2sign/validateNdaSetup',
            type: 'POST',
            data: {
                ListId: ListId,
                " . Yii::app()->request->csrfTokenName . ": '" . Yii::app()->request->csrfToken . "', 
            },
            success: function (data) {
                x2.topFlashes.displayFlash(data, 'success');
                //update to validate 
                if(data == 'NDA SET UP DONE')$( '#check-NDA-setup-btn' ).attr('validated', 1);
                //location.reload();
            },
            error: function (data) {
                x2.topFlashes.displayFlash(data.responseText, 'error');
            },
            complete: function () {

            }
        });
    });




    $('#add-email-template-btn').on('click', function () {
        var err = x2.x2sign.checkEmail();
        if (!err) {
                    let ListId = " .   $model->id .";
                    let emailTemplate =  $('#emailSelect').val();
                $.ajax({
                    url: yii.baseUrl+'/index.php/x2sign/updateListtingTemplate',
                    type: 'POST',
                    data: {
                        modelId: ListId,
                        emailTemplate: emailTemplate,
                        " . Yii::app()->request->csrfTokenName . ": '" . Yii::app()->request->csrfToken . "', 

                    },
                    success: function (obj) {
                        x2.topFlashes.displayFlash('Template Changed', 'success');
                    },
                    error: function (data) {
                        x2.topFlashes.displayFlash(data.responseText, 'error');
                    },
                    complete: function () {

                    }
                });

            $('#bli-check-div').show();
            $('#verify-congig-div').show();
        } else {
            x2.topFlashes.displayFlash('Please select an Email Template from the dropdown', 'error');
        }
    });


    $('#add-cim-email-template-btn').on('click', function () {
        var err = x2.x2sign.CIMcheckEmail();
        if (!err) {
                    let ListId = " .   $model->id .";
                    let emailTemplate =  $('#cimEmailSelect').val();
                $.ajax({
                    url: yii.baseUrl+'/index.php/x2sign/updateCIMListtingTemplate',
                    type: 'POST',
                    data: {
                        modelId: ListId,
                        emailTemplate: emailTemplate,
                        " . Yii::app()->request->csrfTokenName . ": '" . Yii::app()->request->csrfToken . "', 

                    },
                    success: function (obj) {
                        x2.topFlashes.displayFlash('Template Changed', 'success');
                    },
                    error: function (data) {
                        x2.topFlashes.displayFlash(data.responseText, 'error');
                    },
                    complete: function () {

                    }
                });

            $('#bli-check-div').show();
            $('#verify-congig-div').show();
        } else {
            x2.topFlashes.displayFlash('Please select an Email Template from the dropdown', 'error');
        }
    });




    //function to get the preview
    $('#previewBLIbutton').on('click', function () {
        let listId = '" . $model->id . "';
        let win = window.open('https://sydney.tworld.com/index.php/site/bliForm?listingId=' + listId);
        if (win) {
            win.focus();
        } else {
            x2.topFlashes.displayFlash('Could not display preview in new tab', 'error');
        }
    });



");

Yii::app()->clientScript->registerCss('listingPortalCss', "
    .fa-tab-lg {
        font-size: 6em;
    }

    .single-column-layout-content {
        max-width: 100%;
    }

    #content {
        margin: 0px;
    }

    #header {
        box-shadow: none;
    }

    .form-check-input:checked {
        background-color: #768a39;
        border-color: #768a39;
    }

    .grid-view table.items {
        background-color: white !important;
    }

    tbody tr.odd {
        background-color: white !important;
    }

    tbody tr.even {
        background-color: white !important;
    }

    #add-cim-btn {
        color: white;
        background-color: #104220;
    }

    #add-cim-email-template-btn {
        color: white;
        background-color: #104220;
    }
");

$download_nda_url = Yii::app()->createUrl('/listings2/downloadNda/id/' . $model->id);

X2Html::getFlashes ();


$jsVersion = '?'.Yii::app()->params->buildDate;
$themeUrl = Yii::app()->theme->getBaseUrl();
$baseUrl = Yii::app()->request->getBaseUrl();
$dateFormat = Formatter::formatDatePicker('medium');
$timeFormat = Formatter::formatTimePicker();
$amPm = Formatter::formatAMPM() ? 'true' : 'false';
$language = (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage();
$jsVersion = '?'.Yii::app()->params->buildDate;
?>

        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/main.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/details.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/form.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/ui-elements.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/x2forms.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/responsiveLayout.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/responsiveUIElements.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/responsiveX2Forms.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/css/base/jquery-ui.css'; ?>" />
        <!-- <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jquery.js'; ?>"></script> -->
        <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/js/jquery-ui.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/js/jquery-ui-i18n.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/jquery-ui-timepicker-addon.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/qtip/jquery.qtip.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/ComboBox.js'; ?>"></script>
<script>
var addedjs = false;
var addToListing  = function(){
    //this code will be to add more ndas to the curent listing
    $("#NdaDocName").parent().toggle();
    if(!addedjs){
        jQuery('#NdaDocName').autocomplete({'minLength':'1','select':function( event, ui ) {
                                        $('#NdaDocId').
                                            val(ui.item.id);
                                        $(this).val(ui.item.value);
                                        return false;
                                },'create':'','source':'\\index.php\\ndaconfigure\\getItems?modelType=Ndaconfigure'});

        addedjs = true;
    }
}

/*
//this will be a function to save the email template

var updateEmailTemplate = function(){
                    let ListId = <?php echo $model->id?>;
                    let emailTemplate =  $('#emailTemplate').val();
                $.ajax({
                    url: yii.baseUrl+'/index.php/x2sign/updateListtingTemplate',
                    type: 'POST',
                    data: {
                        modelId: ListId,
                        emailTemplate: emailTemplate,
                        <?php echo Yii::app()->request->csrfTokenName; ?>: '<?php echo Yii::app()->request->csrfToken; ?>', 
                    },
                    success: function (obj) {
                        alert("Template Changed");
                    },
                    error: function (data) {
                        alert(data.responseText);
                    },
                    complete: function () {

                    }
                });

}
*/
var updateBLI = function(){
                    let ListId = <?php echo $model->id?>;
                    let bilPick =  $('#bliDocDropdown').val();
                $.ajax({
                    url: yii.baseUrl+'/index.php/x2sign/UpdateListtingBliType',
                    type: 'POST',
                    data: {
                        modelId: ListId,
                        bilPick: bilPick,
                        <?php echo Yii::app()->request->csrfTokenName; ?>: '<?php echo Yii::app()->request->csrfToken; ?>', 
                    },
                    success: function (obj) {
                        x2.topFlashes.displayFlash('BLI Changed', 'success');
                    },
                    error: function (data) {
                        x2.topFlashes.displayFlash(data.responseText, 'error');
                    },
                    complete: function () {

                    }
                });

}


/*
window.addEventListener("load", (event) => {
    var translations = {'title': 'Discard unsaved changes?', 'message':'You are about to navigate off this page. You must verify the NDA setup before and NDA can be sent for this listing automatically', 'cancel':'Cancel', 'confirm':'Yes'};
    $(document).on ('click', 'a', function (evt) {
        var link$ = $(this);
        //check to see if we are verifyed
        var varFy = $( "#check-NDA-setup-btn" ).attr( "validated" ); 
        if ($.type (link$.attr ('href')) === 'string' && link$.attr ('href') !== '#' && 
            !link$.attr ('href').match (/^javascript:/) && !varFy) {

            auxlib.confirm (function () {
                window.location = link$.attr ('href');
            }, translations);
            return false;
        } 
    });

});*/


window.addEventListener("beforeunload", function (e) {
  var confirmationMessage = "You are about to navigate off this page. You must verify the NDA setup before and NDA can be sent for this listing automatically";
  var varFy = parseInt($( "#check-NDA-setup-btn" ).attr( "validated" ));
  console.log(e);
  if(typeof varFy === 'undefined' || varFy === null || !varFy){
    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
    return confirmationMessage;                            //Webkit, Safari, Chrome
  }
});



</script>

<style>
.grid-view td {height: 100px;}
</style>
<div class="preview-header">
    <div class="d-flex w-100">
        <div class="col-6 d-flex w-50">
            <?php if (isset($model->c_website_listing_image__c) && !empty($model->c_website_listing_image__c)): ?>
            <div class="col-auto"><img height="250" width="300" src="<?php echo $model->c_website_listing_image__c ?>" alt="<?php echo $model->c_website_listing_image__c ?>" style="border: 3px solid white;" /></div>
            <?php endif; ?>
            <div class="col-auto d-flex flex-column justify-content-center ms-2">
                <h2 style="margin-bottom: 10px; color: white !important;" class="fw-normal">VIEW LISTING</h2>
                <h1 style="font-size:32px; margin: 0 !important; color: white !important; font-weight: bold;"><?php echo $model->name;?></h1>
                <div class="d-flex">
                    <h4 class="fw-normal" style="color: white;"><?php echo $model->c_listing_number__c;?></h4> <div class="mx-2" style="color: white;">|</div> <h4 class="fw-normal" style="color: white;">$<?php echo $model->c_listing_price__c;?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 d-flex w-50i justify-content-start">
            <img height="250" src="https://sydney.tworld.com/index.php/media/media/getFile/id/201347122/key/51f9edfe577a68d3fe49fa35fcf171caf6f5b90d931fbe10725642ead231eb2e"></img>
        </div>
    </div>
</div>
<div class="container" style="width:100%; max-width: 1500px;">
<div class="property-menu">
    <ul class="nav nav-tabs nav-fill justify-content-center">
        <li class="nav-item">
           <a class="nav-link" href="https://sydney.tworld.com/index.php/listings2/<?php echo $model->id;?>" ><div><img style="height: 150px;" src="/images/details.png"></img></div></a>
        </li>
        <li class="nav-item">
           <a class="nav-link active" aria-current="page" onclick="x2Tabs(event, 'Docs')" href="#"><div><img style="height: 150px;" src="/images/documents.png"></img></div></a>
        </li>
        <li style="display:none;" class="nav-item">
            <a class="nav-link" href="#"><div><img style="height: 150px;" src="/images/buyers.png"></img></div></a>
        </li>
        <li style="display:none;" class="nav-item">
            <a class="nav-link" href="#"><div><img style="height: 150px;" src="/images/deals.png"></img></div></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" tabindex="-1" onclick="x2Tabs(event, 'Activity')"><div><img style="height: 150px;" src="/images/activity.png"></img></div></a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" href="#" tabindex="-1"><div><i class="fa fa-file-alt fa-tab-lg" aria-hidden="true"></i></div>CIM</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" tabindex="-1"><div><i class="fa fa-file fa-tab-lg" aria-hidden="true"></i></div>BLI</a>
        </li> -->
    </ul>
    <div class="x2-vertical-tabs" style="display: none">
        <button class="tablinks" onclick="x2Tabs(event, 'Docs')" id="defaultOpen">
        <i class="property-icons fas fa-file-signature"></i>
            <div class="x2-tabs-title">DOCS</div>
        </button>
         <button class="tablinks" onclick="x2Tabs(event, 'Activity')">
            <i class="property-icons far fa-chart-bar"></i>
            <div class="x2-tabs-title">ACTIVITY</div>
        </button>

    </div>
    <!-- <div class="row d-flex my-3">
        <label>NDA Configuration</label>
        <div class="d-flex justify-content-evenly" style="border: thin solid #d3c8c8;">
            <div class="d-flex flex-column col-auto my-3">
                <select id="NDASelect" class="form-select" aria-label="Default select example" onchange='updateDocs()'>
                    <option selected>Select an NDA for Automation</option>
                    <?php 
                        foreach($ndaRecs as $nda){
                            echo "<option value='N:" . $nda->id . "'>Franchise Office NDA: " . $nda->name . "</option>";
                        }
                        //have to do this for the sign docs too
                        foreach($X2SignDocs as $x2){
                            echo "<option value='D:" . $x2->id . "'>S2Doc: " . $x2->name . "</option>";
                        }

    
                    ?>
                </select>
                <select id="emailTemplate"  class="form-select mt-3" aria-label="Default select example" onchange='updateEmailTemplate()'>
                    <option selected>Select an email template</option>
                    <?php 
                        foreach($EmailTemplates as $id => $temp){
                            if($model->autSendEmailTemp == $id){
                                echo "<option  selected value=" . $id . ">" . $temp . "</option>";
                            }else{
                                echo "<option value=" . $id . ">" . $temp . "</option>";
                            }
                        }

                    ?>

                </select>
            </div>
            <div class="d-flex flex-column col-auto my-3">
                <div class="form-check">
                    <label class="form-check-label" for="bliCheckbox">
                        After NDA is Signed:
                    </label>
                    <!-- <input class="form-check-input" type="checkbox" value="" onclick="location.href='<?php echo Yii::app()->createUrl('/listings2/updateBli/id/' . $model->id);  ?>';" 
                             id="bliCheckbox" <?php if($model->c_BLIauto) echo "checked"; ?> > -->
                    
                </div>
                <div class="form-group form-inline">
                    <!-- <label class="form-label" for="bliDocDropdown">
                        BLI Document Used
                    </label>
                    <select id="bliDocDropdown" class="form-select mt-3" aria-label="Select A BLI Document" onchange='updateBLI()'>
                        <option selected>Select a BLI ↓</option>
                        <option value="BLI">BLI</option>
                        <option value="Teaser">Teaser</option>
                    </select>
                </div>
                <div class="form-group form-inline">
                    <!-- <label class="form-label" for="bliDocDropdown">
                        BLI Document Used
                    </label>
                     <button class="btn btn-outline-secondary mt-3" type="button">BLI Preview</button> 
                </div>

            </div>
        </div>
    </div> -->
    <hr class="my-2">
    <div id="Docs" class="tabcontent">
        <?php /**
         * ========== CONTENTS OF DOCS ===========
         */ ?>
        <div class="preview-document-hearder">
        </div>
        <div class="container-fluid">
            <div class="d-flex row justify-content-between">
                <div class="col-6 bg-light" style="padding-left: 0px;padding-right: 0px;height:600px;">
                    <div class="p-2" style="width: 100%; text-align: center;background-color: #768a39"><h2 class="fw-bold" style="color: white !important;">NDA AUTOMATION CONFIGURATION</h2></div>
                    <div class="row mt-4">
                        <div class="col-6 d-flex flex-row ms-5"> <!-- NDA Dropdown -->
                            <label for="NDASelect" class="align-self-center me-2" style="width: 20%;">Step 1: </label>
                            <select id="NDASelect" name="NDASelect" class="w-100 form-select" style="border-radius: 0; background-color: white !important;" aria-label="Default select example" >
                                <option selected>Select an NDA ↓</option>
                                <?php
                                    foreach($ndaRecs as $nda){
                                        echo "<option value='N:" . $nda->id . "'>Franchise Office NDA: " . $nda->name . "</option>";
                                    }
                                    //have to do this for the sign docs too
                                    foreach($X2SignDocs as $x2){
                                        echo "<option value='D:" . $x2->id . "'>S2Doc: " . $x2->name . "</option>";
                                    }


                                ?>
                            </select>
                        </div>
                        <div class="col-5">
                            <div class="d-flex justify-content-between flex-row">
                                <button id="add-nda-btn" class="btn" style="color: white; background-color: #104220;"><span>Add NDA</span></button>
                                <button id="download-nda-btn" class="btn" style="display: none; color: white; background-color: #104220;" onclick="nda_download('<?php echo $download_nda_url; ?>')"><span>Download NDA</span></button>
                            </div>
                        </div>
                        <div id="email-select-div" class="col-6 ms-5 d-flex flex-row mt-3" <?php if ($doc_dataProvider->getTotalItemCount() == 0) echo 'style="display: none !important;"' ?>> <!-- Email Template Dropdown -->
                            <label for="emailSelect" class="align-self-center me-2" style="width: 20%;">Step 2: </label>
                            <select id="emailSelect" name="emailSelect" class="form-select" style="border-radius: 0; background-color: white !important;" aria-label="Default select example">
                                <option style="background: white !important;" selected>Select an email template ↓</option>
                                <?php
                                    foreach($EmailTemplates as $id => $temp){
                                        if($model->autSendEmailTemp == $id) {
                                            echo "<option  selected value=" . $id . ">" . $temp . "</option>";
                                        } else {
                                            echo "<option value=" . $id . ">" . $temp . "</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div id="email-btn-div" class="col-5 mt-3" <?php if ($doc_dataProvider->getTotalItemCount() == 0) echo 'style="display: none;"' ?>>
                            <div class="d-flex justify-content-between flex-row">
                                <button id="add-email-template-btn" class="btn" style="color: white; background-color: #104220;"><span>Add Template</span></button>
                            </div>
                        </div>
                        <div <?php if ($doc_dataProvider->getTotalItemCount() == 0) echo 'style="display: none;margin-left: 1.7rem!important"' ?> id="bli-check-div" class="col-12 mt-5" style="margin-left: 1.7rem!important;">
                            <div class="col-10 d-flex form-check">
                                <label class="form-check-label" for="bliCheckbox" style="width: 35%; align-self: center;">
                                    Step 3: After NDA Is Signed
                                </label>
                                <div>
                                <!-- <input class="form-check-input" type="checkbox" value="" id="bliCheckbox" <?php if($model->c_BLIauto) echo "checked"; ?> > -->
                                    <select class="form-select" id="afterSendOption" style="background-color: white !important;">
                                        <option>Select An Option ↓</option>
                                        <option <?php if($model->c_BLIauto == 1 && $model->c_cim == 0) echo "selected";  ?> value="bli">Send BLI</option>
                                        <option <?php if($model->c_BLIauto == 0 && $model->c_cim == 1) echo "selected";  ?> value="cim">Send CIM</option>
                                        <option <?php if($model->c_BLIauto == 1 && $model->c_cim == 1) echo "selected";  ?> value="both">Send BLI And CIM</option>
                                    </select>
                                </div>
                             </div>
                            <div class="row mb-3">
                                <div class="col-6 d-flex justify-content-end ms-3">
                                    <select id="bliDocDropdown" class="form-select mt-3" style="width:80%;border-radius: 0; background-color: white !important; <?php if (!$model->c_BLIauto) echo "display: none"; ?>" aria-label="Select A BLI Document" onchange='updateBLI()'>
                                        <option>Select a BLI ↓</option>
                                        <option <?php if($model->autoBliType == "BLI") echo "selected";  ?> value="BLI">BLI</option>
                                        <option <?php if($model->autoBliType == "Teaser") echo "selected";  ?>  value="Teaser">Teaser</option>
                                    </select>
                                </div>
                                <div class="col-5 mt-3">
                                    <div class="d-flex justify-content-between flex-row">
                                        <button id="previewBLIbutton"  class="btn" style="color: white; background-color: #104220; <?php if (!$model->c_BLIauto) echo "display: none"; ?>"><span>Preview BLI</span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex justify-content-center mt-4">
                            <div class="col-auto">
                                <button id="add-cim-btn" class="btn" <?php if (!$model->c_cim) echo 'style="display: none; color: white; background-color: #104220;"' ?>><span>Upload CIM Doc</span></button>
                            </div>
                        </div>
                        <div class="row">
                                <div id="cim-select-div" class="col-6 ms-5 d-flex flex-row justify-content-end mt-3" <?php if (!$model->c_cim) echo 'style="display: none !important;"' ?>> <!-- Email Template Dropdown -->
                                    <select id="cimEmailSelect" name="cimEmailSelect" class="form-select" style="width: 80%; border-radius: 0; background-color: white !important;" aria-label="Default select example">
                                        <option style="background: white !important;">Select a CIM email template ↓</option>
                                        <?php
                                            foreach($EmailTemplates as $id => $temp){
                                                if($model->cimEmailTemp == $id) {
                                                    echo "<option  selected value=" . $id . ">" . $temp . "</option>";
                                                } else {
                                                    echo "<option value=" . $id . ">" . $temp . "</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div id="cim-email-btn-div" class="col-5 mt-3" <?php if (!$model->c_cim) echo 'style="display: none;"' ?>>
                                    <div class="d-flex justify-content-between flex-row">
                                        <button id="add-cim-email-template-btn" class="btn" <?php if (!$model->c_cim) echo 'style="display: none; color: white; background-color: #104220;"' ?>><span>Add CIM Email Template</span></button>
                                    </div>
                                </div>
                        </div>
                        <div <?php if ($doc_dataProvider->getTotalItemCount() == 0) echo 'style="display: none;"' ?> id="verify-congig-div" class="col-12 ms-5 mt-5">
                            <div class="row">
                                <div class="col-10 d-flex justify-content-center">
                                    <button id="check-NDA-setup-btn" class="btn" style="color: white; background-color: #104220;" validated='0'<?php //echo $validated ?>><span>Verify NDA Setup</span></button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mt-3 col-10 d-flex justify-content-center">
                                    <span>Once you have completed set up, click Verify NDA Setup to save</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column col-4" style="padding-left: 0px;padding-right: 0px;height:600px; overflow-y: none;">
                    <div style="background-color: white; overflow-y: auto; height: 200px;">
                    <div class="p-2" style="width: 100%; text-align: center;background-color: #768a39"><h2 class="fw-bold" style="color: white !important;">Selected NDAs</h2></div>
                        <?php
                             $this->widget('zii.widgets.grid.CGridView', array(
                                 'id'=>'documents-grid',
                                 'ajaxUpdate' => true,
                                 'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name. '/css/gridview',
                                 'template'=> '{items}{pager}',
                                 'rowHtmlOptionsExpression' => 'array("onclick"=>"")',
                                 'dataProvider'=>$doc_dataProvider,
                                 'columns'=>array(
                                     array(
                                     'name'=>'Documents',
                                     'value' =>'Listings2::getDocuments($data)',
                                     'type'=>'raw',
                                     'headerHtmlOptions'=>array('style'=>'width:100%; display:none;'),
                                     ),
                                 ),
                             ));
                        ?>
                    </div>
                    <div style="margin-top: 3.5rem !important; background-color: white;i overflow-y: auto; height:200px;">
                    <div class="p-2" style="width: 100%; text-align: center;background-color: #768a39"><h2 class="fw-bold" style="color: white !important;">Selected CIM</h2></div>
                        <?php
                             $this->widget('zii.widgets.grid.CGridView', array(
                                 'id'=>'cim-documents-grid',
                                 'ajaxUpdate' => true,
                                 'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name. '/css/gridview',
                                 'template'=> '{items}{pager}',
                                 'rowHtmlOptionsExpression' => 'array("onclick"=>"")',
                                 'dataProvider'=>$cim_dataProvider,
                                 'columns'=>array(
                                     array(
                                     'name'=>'Documents',
                                     'value' =>'Listings2::getDocuments($data)',
                                     'type'=>'raw',
                                     'headerHtmlOptions'=>array('style'=>'width:100%; display:none;'),
                                     ),
                                 ),
                             ));
                        ?>
                    </div>
                    <div class="p-3 fst-italic" style="background: white; font-size: 11px;">To replace selected CIM, upload a new document using the Upload CIM Doc button</div>
                </div>
            </div>
        </div>
        <!-- <div class="preview-buttons">
            <button type="button" class="btn btn-outline-primary btn-sm shadow" data-toggle="modal" data-target="#ndaDocUploadModal">ADD NDA</button>
            <?php if ($doc_dataProvider->totalItemCount > 0 ){ ?>
                 <button class="btn btn-outline-primary btn-sm shadow" onclick="nda_download('<?php echo $download_nda_url; ?>')">DOWNLOAD NDA</button>
            <?php } ?>
            <input type="button" class="btn btn-outline-primary btn-sm shadow" onclick="location.href='<?php echo Yii::app()->createUrl('/listings2/' . $model->id);  ?>';" value="Return To Listing" />



        </div>
        <div style="display:none;">
            <br>
            <input id="NdaDocId" type="hidden" >
            <input title="Listing Lookup" tabindex="" id="NdaDocName" type="text" autocomplete="off" class="ui-autocomplete-input">
            <input type="button" class="btn btn-outline-primary btn-sm shadow" onclick="updateDocs()" value="Submit" />
        </div>
        <div class="modal fade" id="ndaDocUploadModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
            <?php 
            /* $this->widget('FileUploader', ['id'=>'ndaDoc-upload', 
                                                 'displayToggle' => false,
                                                 'displayForm' => false,
                                                 'mediaParams' => ['associationType' => 'Listings2', 'associationId' => $model->id,'c_document_type'=>'NDA','YII_CSRF_TOKEN' => Yii::app()->request->csrfToken], 
                                                 'acceptedFiles' => 'application/pdf', 
                                                 'viewParams' => ['closeButton'=>false, 'showButton' => false],
                                                 'events' => ['success' => '$.fn.yiiGridView.update("documents-grid",
                                                 );$("#ndaDocUploadModal").modal("hide");']]
            ); */ ?>
                </div>
            </div>
          </div>
        </div>
        <div class="preview-documents">
             <?php
                 $this->widget('zii.widgets.grid.CGridView', array(
                     'id'=>'documents-grid',
                     'ajaxUpdate' => true,
                     'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name. '/css/gridview',
                     'template'=> '{items}{pager}',
                     'rowHtmlOptionsExpression' => 'array("onclick"=>"")',
                     'dataProvider'=>$doc_dataProvider,
                     'columns'=>array(
                         array(
                         'name'=>'Documents',
                         'value' =>'Listings2::getDocuments($data)',
                         'type'=>'raw',
                         'headerHtmlOptions'=>array('style'=>'width:100%; display:none;'),
                         ),
                     ),
                 ));
            ?>
        </div>
        <?php /**
         * =========== END OF DOCS ==============
         */ ?>
    
    </div> -->
    </div>
    <div id="Activity" class="tabcontent">
        <?php
                $this->renderPartial('_activity', array(
                    'listingsId'=>$model->id,
                    'numOfBliSent' => $numOfBliSent,
                    'numOfNdaSent' => $numOfNdaSent,
                    'numOfNdaSigned' => $numOfNdaSigned,
                ));
        ?>
    </div>
    
</div>

<div id="pdf-nav" class="pdf-nav bg-dark py-4" style="display: none;">
    <div class="row col-4 ml-2">
        <button id="prev" type="button" class="btn btn-outline-light btn-sm"  aria-label="Prev">Prev</button>
        <button id="next" type="button" class="btn btn-outline-light btn-sm ml-2" aria-label="Next">Next</button>
    </div>
    <div class="row col-4 text-light" style="justify-content: center;">
        Page: 
        <div id="page_num" class="text-light px-2"></div>
        /
        <div id="page_count" class="text-light pl-2"></div>
    </div>
    <div class="row col-4" style="justify-content: flex-end;">
        <button id="docViewClose" type="button" class="btn btn-outline-light" aria-label="Close">
            Close
        </button>
    </div>
</div>

<div id="docViewModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 1224px;" role="document">
    <div class="modal-content">
      <div class="modal-body" style="width:auto; overflow-y: auto; padding: 3rem 0 0 0;">
          <div id="docView">
              <canvas id="pdf"></canvas>
          </div>
      </div>
    </div>
  </div>
</div>

<div id="cimUploadModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 1224px;" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Upload CIM Document</h2>
        </div>
        <div class="modal-body" style="width:auto; overflow-y: auto;">
            <div id="cimUploadDiv">
                <?php $this->widget('FileUploader', ['id'=>'cim-upload',
                                                 'url' => '/site/upload',
                                                 'displayToggle' => false,
                                                 'displayForm' => false,
                                                 'mediaParams' => ['associationType' => 'listingCIM', 'associationId' => $model->id],
                                                 'acceptedFiles' => 'application/pdf',
                                                 'viewParams' => ['closeButton'=>false, 'showButton' => false],
                                                 'events' => ['success' => "x2.topFlashes.displayFlash('CIM Uploded','success'); $('#cimUploadModal').modal('hide');$.fn.yiiGridView.update('cim-documents-grid')"]]
            ); ?>
            </div>
        </div>
    </div>
  </div>
</div>

<div id="renameDocModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
          <input id="renameInput" class="form-control"></input>
      </div>
      <div class="modal-footer">
        <button id="renameBtn" type="button" class="btn btn-primary">Rename Document</button>
      </div>
    </div>
  </div>
</div>
</div>
