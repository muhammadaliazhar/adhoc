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
 * @author Peter Czupil <peter@x2engine.com>
 */

// Setup Tips/Tour
//Tours::loadTips('x2sign.quickSend');

// Import Bootstrap
Yii::app()->clientScript->registerCssFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
Yii::app()->clientScript->registerScriptFile('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js');
Yii::app()->clientScript->registerScriptFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/pdfReader/build/pdf.js');


$cs = Yii::app()->clientScript;
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/ui-elements.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/font-awesome.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/all.min.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/v4-shims.min.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/form.css?' . Yii::app()->params->buildDate, 'screen, projection');



Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/quicksend.css?67891');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/signable.js?67891');

?>

<div class="add-document-container section-container container">
    <div class="row">
        <div class="col-6 d-flex justify-content-start align-items-center"><h1>S2Sign Quick Send</h1></div>
        <div style="display:none !important;" class="col-6 d-flex justify-content-end">
            <div class="d-flex align-items-center">
                <button class="btn btn-primary advance-options">Advanced Options</button>
            </div>
        </div>
    </div>

    <div class="section-header mt-4">
        <h3 id="add-documents">Upload documents</h3>
        <a class="accordianBtn"><i class="fa fa-angle-up"></i></a>
    </div>

    <div class="signDocUpload section-body">
        <?php
            $this->widget('FileUploader', array('id' => 'doc-upload',
                'displayToggle' => false,
                'displayForm' => false,
                //'downloadBtn' => true,
                'removeFileBtn' => true,
                'showExistingFiles' => true,
                'documentView' => true,
                'documentFilesPreviewImage' => true,
                'getExistingFilesUrl' => "/x2sign/getEnvelopeSignDoc?id=" . $envelope->id,
                'previewElementSortable' => true,
                'saveFilesOrderAPIUrl' => "/x2sign/updateDocSignOrder?id=" . $envelope->id,
                'mediaParams' => array('associationType' => 'x2signenvelopes', 'associationId' => $envelope->id),
                'acceptedFiles' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/pdf , image/jpeg, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'viewParams' => array('showButton' => false, 'closeButton' => false),
                'events' => array( 'success' => '$("#uploadDone").attr("data-value","1");')
            )); 
        ?>
        
        <div id="uploadDone" data-value=<?php if($envelope->signDocIds == "[]") echo"0"; else echo "1";?>></div>
        <div id="dialogNoUpload" title="No Upload" style='display:none;'>
          <p>No files detected, if you have uploaded a file make sure it has finished before continuing.</p>
        </div>
        <div class="oldTemps row mt-4">
            <div class="col-6">
                <label for="fileName">Template Search</label>
                <div class="template-name d-flex">
                    <div class="row w-100">
                        <div class="col-9">
                            <input class="form-control" placeholder="Name of template..." style="background: white !important;" type="text" id="fileName" name="fileName">
                        </div>
                        <div class="col-auto">
                            <button class="btn quicksend-btn" id="lookUpTemps">Search</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="lookup-results">
                    <label>Select A Template</label>
                    <select class="form-control" style="background: white !important;" name="listOfTemps" id="listOfTemps">
                        <option value="none">Pre-existing Templates</option>
                    </select>
                </div>
            </div>
            <div class="col-auto mt-3">
                <button class="btn quicksend-btn" style="background: #CECECE; border-color: #CECECE;" id="addTemp">Add Template</button>
                <ol id="docsToAdd"></ol>
            </div>
        </div>
    </div>
</div>

<?php
    /* Yii::app()->controller->renderPartial('editDocs',array(
        'envelope' => $envelope,
    )); */
?>

<?php
    Yii::app()->controller->renderPartial('quickSetupRecipients',array(
        'envelope' => $envelope,
        'user' => User::getMe(),
        'recipients' => $recipients,
        //'contactRoles' => $contactRoles,
    ));
?>

<?php
    Yii::app()->controller->renderPartial('quickSetupEmail', array(
        'envelope' => $envelope,
        'template' => $template,
        'users' => $users,
    ));
?>

<div class="prev-next-bar">
    <button id="cancel-button" class='btn btn-light flex-item' style='border: 1px solid #8e9296;'>Cancel</button>
    <button id='next-page-button' class='btn quicksend-btn flex-item'>Next</button>
</div>

<form class="advanced-options-form hide">
    <div class="form-group d-flex align-items-center mt-1 mb-2">
        <label for="reminder" class="mb-0 mr-2">Reminder:</label>
        <input type="checkbox" class="my-0" id="reminder" required>
    </div>

    <div class="form-group">
        <label for="interval">Interval:</label>
        <input type="number" min="0" class="form-control" id="interval" name="interval" placeholder="Interval" required disabled>
    </div>

    <div class="form-group mb-0">
        <label for="count">Count:</label>
        <input type="number" min="0" class="form-control" id="count" name="count" placeholder="Count" required disabled>
    </div>
</form>

<script>
    $('#lookUpTemps').on('click', function() {
        $.ajax({
            url: yii.baseUrl+'/index.php/x2sign/getOldTemps',
            type: 'GET',
            data: {
                type: 'documents',
                model: 'Contacts',
                query: $('#fileName').val()
            },
            success: function (obj) {
                $('#listOfTemps').children().remove();
                var modelList = $.parseJSON(obj)[0];
                for (var i = 0; i < modelList.length; i++) {
                    $("#listOfTemps").append(
                        $('<option></option>').val(modelList[i].modelId).html(modelList[i].viewName)
                    );
    
                }

                if (modelList.length > 0) {
                    $('#addTemp').css('background-color', '#937D54 !important');
                    $('#addTemp').css('border-color', '#937D54 !important');
                } else {
                    x2.topFlashes.displayFlash('No templates found', 'error');
                }
            },
            error: function (data) {
                alert(data.responseText);
            },
            complete: function () {
                //loading.remove();
            }
        });
  
    });

    $(".accordianBtn").on("click", function (e) {
        $(this).parents(".section-header").siblings(".section-body").slideToggle();
        $(this).find("i").toggleClass("fa-angle-up fa-angle-down");
    });

    $('#addTemp').on('click', function() {
        var e = document.getElementById("listOfTemps");
        var id = e.value;
        var name = e.options[e.selectedIndex].text;
       
         $("#docsToAdd").append(
            "<li mediaId = '" + id + "'>" + name + "</li>"
        );

    });

    $('#next-button').on('click', function() {
        var oldTemps = [];
        $('#docsToAdd > li').each(function(i){
            var id = $( this ).attr("mediaId");
            oldTemps.push(id);
        });
        $.ajax({
            url: '<?php echo Yii::app()->controller->createUrl('/x2sign/addOldTemps', array('id' => $envelope->id)); ?>',
            type: 'GET',
            data: {
                ids: oldTemps,
            },
            success: function (obj) {
                 window.location.href = <?php echo "'" . Yii::app()->controller->createUrl('/x2sign/quickSetupRecipients', array('id' => $envelope->id)) . "'"; ?>;
            },
            error: function (data) {
                alert(data.responseText);
            },
            complete: function () {
                //loading.remove();
            }
        });
 

    });

    // Inspiration taken from: https://stackoverflow.com/questions/3519861/yes-or-no-confirm-box-using-jquery
    function confirmDialog(message) {
      $('<div></div>').appendTo('body')
        .html('<div><h6>' + message + '</h6></div>')
        .dialog({
            modal: true,
            title: 'Delete message',
            zIndex: 10000,
            autoOpen: true,
            width: 'auto',
            resizable: false,
            buttons: {
                Yes: function() {
                    $('body').append('<h1>Confirm Dialog Result: <i>Yes</i></h1>');
                    $(this).dialog('close');
                    window.location.href = '<?php echo Yii::app()->controller->createUrl('/x2sign/cancelQuickSetupEnvelope', array('id' => $envelope->id)); ?>';
                },
                No: function() {
                    $('body').append('<h1>Confirm Dialog Result: <i>No</i></h1>');
                    $(this).dialog('close');
                }
            },
            close: function(event, ui) {
                $(this).remove();
            }
        });
    }; 


    $('.prev-next-bar #cancel-button').on('click', function() {
        confirmDialog('Are you sure you want to cancel this envelope?');
    });

    /**
    * Addded Dialog popup 
    */
    
    $(".advance-options").on("click", function(e) {
        $(".advanced-options-form").dialog({
            modal: true,
            width: 350,
            resizable: false,
            draggable: false,
            title: "Advance Options",
            dialogClass: "advanced-options-dialog",
            position: { my: "center", at: "center", of: window },
            buttons: [
                {
                    text: 'Submit',
                    click: function () {
                        saveAdvanceOptions($(this));
                    },
                    class: 'submit-btn'
                },
                {
                    text: 'Cancel',
                    click: function () {
                        $(this).dialog('close');
                    },
                    class: 'cancel-btn'
                },
            ]
        });
    });

    function saveAdvanceOptions(event) {
        const formData = {};
        const dataArray = $(".advanced-options-form").serializeArray();

        $.each(dataArray, function (index, item) {
            formData[item.name] = item.value;
        });

        if (!$(".advanced-options-form")[0].checkValidity()) {
            $(".advanced-options-form")[0].reportValidity();
            return;
        }

        $.ajax({
            url: yii.scriptUrl + '/x2sign/setReminders?envelopeId=' + '<?php echo $envelope->id; ?>', 
            type: 'POST',
            data: {
                'count': formData.count,
                'interval': formData.interval,
                'YII_CSRF_TOKEN': x2.csrfToken
            },
            success: function (resp) {
                event.dialog('close');
                alert('Reminders Saved!');
            },
            error: function(resp) {
                console.log(resp);
            }
        });
    }

    $('#reminder').click(function() {
        $( "#countDiv, #interDiv" ).slideToggle( "slow" );
        if ($(this).is(':checked')) {
            $('#interval, #count').prop('disabled', false);
        } 
        else {
            $('#interval, #count').prop('disabled', true);
        }
    });
</script>

