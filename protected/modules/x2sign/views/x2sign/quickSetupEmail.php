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
//Tours::loadTips('x2sign.quickEmailView');

// Import Bootstrap
Yii::app()->clientScript->registerCssFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
Yii::app()->clientScript->registerScriptFile('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js');
Yii::app()->clientScript->registerScriptFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js');
Yii::app()->clientScript->registerPackage('ckeditor');
Yii::app()->clientScript->registerScript('setupEmailForm', '
    CKEDITOR.replace("emailMessage", {
        bodyId: "emailMessage", 
        height: 250,
        //width: 500, 
        removePlugins: "toolbar, resize, elementspath",
        fontSize_defaultLabel: "1rem",
    });
');

$cs = Yii::app()->clientScript;
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/ui-elements.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/font-awesome.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/all.min.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/v4-shims.min.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/form.css?' . Yii::app()->params->buildDate, 'screen, projection');

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/quicksend.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/signable.js');
Yii::app()->clientScript->registerScript('sendEnvelopeJS', "
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
                        window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/cancel2', array('id' => $envelope->id)) . "';
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
    $('#next-page-button').on('click', function() {

        if($('#uploadDone').attr('data-value') == '0') {
              $( function() {
                $( '#dialogNoUpload' ).dialog();
              } );
            return;

        }

        var signDocs = [];
        $('#sortable').children().each(function () {
            // Add sign doc id to new order of the documents
            signDocs.push(parseInt($(this).attr('id')));
        });

        var viewers = [];
        var signers = [];
        var recipients = [];
        $('.recipient').each(function() {
            recipients.push({
                'firstName': $(this).find('.firstNameInput').val(),
                'lastName': $(this).find('.lastNameInput').val(),
                'email': $(this).find('.emailInput').val(),
                'contactRole': $(this).find('.roleDropdown').val(),
                'displayModel': $(this).find('.sendModel').val(),
                'hiddenModel': $(this).find('.hiddenModel').val(),
                'hiddenId': $(this).find('.hiddenId').val(),   
                'order': $(this).find('.orderNum').val(),
                'viewer': $(this).find('.is-viewer').is(':checked'),
                'inPerson': $(this).find('.recipientRoleSelect').val(),
            });    
            //adding code so that if a viewer is ahead of a signer we throw an error
            if($(this).find('.is-viewer').is(':checked')){
                viewers.push(parseInt($(this).find('.orderNum').val()));
            }else{
                signers.push(parseInt($(this).find('.orderNum').val()));

            }


        });

        // Check if there's any duplicate recipients
        let values = recipients.map(function(item) { 
            return item.email
        });

        let checkValues = values.every((item) => item == '');

        if((new Set(values).size !== recipients.length) && !checkValues) {
            alert('Remove any duplicate contacts?');
            return;        
        }
        console.log(viewers);
        console.log(signers);
        //check here if any of the viewer sports are before the signers

        for (let index = 0; index < viewers.length; ++index) {
            const element = viewers[index];
            console.log(element);
            console.log(signers.every(el => el < element)); 
            if(!signers.every(el => el < element)){
                alert('Please insure that all your viewers spots are set after your signers.');
                return;  

            }
        }   


        //here I will do a error message if the subject line is empty
        if(!$('#emailSubject').val()){
            x2.topFlashes.displayFlash('Please fill in the email body and subject line.', 'error');
            return;

        }

        //save the reminders first
        if($('#reminder').is(':checked')){
        $.ajax({
                url: yii.scriptUrl + '/x2sign/setReminders?envelopeId=' + " .  $envelope->id .", 
                type: 'POST',
                data: {
                    'count': $('#count').val(),
                    'interval': $('#interval').val(),
                    'YII_CSRF_TOKEN': x2.csrfToken
                },
                success: function (resp) {
                    alert('Reminders Saved!');
                },
                error: function(resp) {
                    console.log(resp);
                }
            });
        }

        //adding code to seend over the oldIds for templates that were added
        var oldTemps = [];
        $('#docsToAdd > li').each(function(i){
            var id = $( this ).attr('mediaId');
            oldTemps.push(id);
        });

        $.ajax({
            url: '" . Yii::app()->controller->createUrl('/x2sign/quickSetupTemplate', array('id' => $envelope->id)) . "',
            type: 'POST',
            data: {
                //'docs': signDocs, // enable if needed 
                'recipients': recipients,
                'userOrder': $('#orderCheck').is(':checked'),
                '" . Yii::app()->request->csrfTokenName . "': '" . Yii::app()->request->csrfToken . "',
                subject: $('#emailSubject').val(),
                message: $('#emailMessage').val(),
                delay: $('#sendDate').val(),
                expireDate : $('#expireDate').val(),
                oldTemps :  oldTemps,
            },
            success: function(data) {
                console.log(data);
                window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/quickSetupTemplate', array('id' => $envelope->id)) . "';
            },
            error: function(data) {
                console.log(data);
                console.log($('#orderCheck').is(':checked'));
                $('.recipient').each(function() {
                    if($(this).find('.firstNameInput').val() == '')
                        $(this).find('.firstNameInput').addClass('error');
                    else
                        $(this).find('.firstNameInput').removeClass('error');
                    
                    if($(this).find('.lastNameInput').val() == '')
                        $(this).find('.lastNameInput').addClass('error');
                    else
                        $(this).find('.lastNameInput').removeClass('error');

                    const checkrecipientRole = () => $(this).find('.recipientRoleSelect').length ? $(this).find('.recipientRoleSelect').val() == 'false' : true;                

                    if($(this).find('.emailInput').val() == '' && checkrecipientRole())
                        $(this).find('.emailInput').addClass('error');
                    else
                        $(this).find('.emailInput').removeClass('error');
                });
                //alert(data.responseText);
            }
        });
    });

    $('#send-button').on('click', function() {
        var throbber$ = auxlib.pageLoading ();
        // Force CKEditor to update the textarea it replaces with the value
        CKEDITOR.instances.emailMessage.updateElement();
        $.ajax({
           url: '" . Yii::app()->controller->createUrl('/x2sign/quickSendFinish', array('id' => $envelope->id)) . "', 
           type: 'POST',
           data: {
               subject: $('#emailSubject').val(),
               message: $('#emailMessage').val(),
               delay: $('#sendDate').val(),
               expireDate : $('#expireDate').val()
           },
           success: function (resp) {
               alert('Success!');
               let redirectUrl = '" . Yii::app()->controller->createUrl('/x2sign/quickSend') . "';
               window.location.replace(redirectUrl);
           },
           error: function(resp) {
                throbber$.remove();
                console.log('error');
                alert('Could not send email: ' + JSON.stringify(resp));
           }
        });
    });

    /* $('#template').change(function() {
        $.ajax({
        url: '".substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/docs/fullView/' + $('#template').val() + '?json=1&replace=1',
        type: 'POST',
        data: {
            json: 1,
            replace: 1
        },
        success: function (data) {
            $('#emailMessage').val($(data.body).text());
                $('#emailSubject').val(data.subject);

        },
        error: function() {
            console.log('error');
        }
        });
    }); */

    $('#template').change(function() {
        $.ajax({
        url: '".substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/docs/fullView/' + $('#template').val() + '?json=1&replace=1',
        type: 'POST',
        data: {
            json: 1,
            replace: 1
        },
        success: function (data) {
            $('#emailMessage').html(data.body);
            $('.cke_wysiwyg_frame').contents().find('#emailMessage').html(data.body);
                $('#emailSubject').val(data.subject);

        },
        error: function() {
            console.log('error');
        }
        });
    });

    $('#cancel-button').on('click', function() {
        confirmDialog('Are you sure you want to cancel this envelope? All progress will be lost.');
    });

    $('#back-button').on('click', function() {
        window.location.replace('" . Yii::app()->controller->createUrl('/x2sign/quickSetupTemplate', array('id' => $envelope->id)) . "');
    });

    $('#preview-docs').on('click', function() {
        window.open('" . Yii::app()->createUrl('x2sign/x2sign/previewDocs', array('envelopeId' => $envelope->id)) . "', '_blank');
    });

    $('#edit-document-order').on('click', function() {
        window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/editDocs', array('id' => $envelope->id)) . "';
    });

    $('#add-document').on('click', function() {
        window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/quickSend', array('id' => $envelope->id)) . "';
    });

     // This uses the same confirm dialog as the 'void envelope' button,
    // however that is hard-coded for the void message and didn't worry
    // about changing it to be general. TO-DO in the future to simplify
    // this code. (Clifton clifton@x2engine.com)
    $('#finish-later').on('click', function() {
        $('<div></div>').appendTo('body')
            .html('<div><h6>Finish this envelope later? Progress will be saved.</h6></div>')
            .dialog({
                modal: true,
                title: 'Save',
                zIndex: 10000,
                autoOpen: true,
                width: 'auto',
                resizable: false,
                buttons: {
                    Yes: function() {
                        $('body').append('<h1>Confirm Dialog Result: <i>Yes</i></h1>');
                        $(this).dialog('close');
                        CKEDITOR.instances.emailMessage.updateElement();
                        $.ajax({
                            url: '" . Yii::app()->controller->createUrl('/x2sign/finishLater', array('id' => $envelope->id)) . "',
                            type: 'POST',
                            data: {
                               subject: $('#emailSubject').val(),
                               message: $('#emailMessage').val(),
                            },

                            success: function() {
                                window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/index') . "';
                            }
                        });
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
    });
        
    //load up values to subject line
    $(function() {
        $('#emailSubject').val(". json_encode($envelope->emailSubject)  . ");
        $('#emailMessage').val(". json_encode($envelope->emailBody)  . ");
    });

    $('#dropdown-menu-users').on('change', function () {
        console.log($(this).val());
        var username = $(this).find(':selected').text();
        $.ajax({
           url: '" . Yii::app()->controller->createUrl('/x2sign/ajaxAssignUser', array('id' => $envelope->id)) . "',
           type: 'POST',
           data: {
               userId: $(this).val(),
               envelopeId: '" . $envelope->id . "',
           },
           success: function (data) {
               x2.topFlashes.displayFlash('Envelope assigned to ' + username, 'success');
           },
           error: function (data) {
               x2.topFlashes.displayFlash('Could not assign envelope', 'error');
           },
        });
    });

    $('#checkbox-envelope-private').on('click', function () {
        console.log($(this).is(':checked'));
        $.ajax({
           url: '" . Yii::app()->controller->createUrl('/x2sign/ajaxAssignPrivate', array('id' => $envelope->id)) . "',
           type: 'POST',
           data: {
               privStatus: $(this).is(':checked'),
               envelopeId: '" . $envelope->id . "',
           },
           success: function (data) {
               x2.topFlashes.displayFlash('Envelope modified', 'success');
           },
           error: function (data) {
               x2.topFlashes.displayFlash('Could not modify envelope', 'error');
           },
        });
    });

");

Yii::app()->clientScript->registerCss('emailStyling', '
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    //input[type=number] {
    //    -moz-appearance: textfield;
    //}

    #expiration-days {
        width: 52px;
        height: 30px;
    }

    #top-flashes-container-outer {
        margin-top: 10px;
    }
');
?>

<div class="review-send-container section-container container">
    <div class="section-header">
        <h1 id="add-documents">Review and Send</h1>
        <a class="accordianBtn"><i class="fa fa-angle-down"></i></a>
    </div>

    <div class="section-body">
        <!-- <div class="dropdown cell right">
            <div>
                <label>Private</label>
                
            </div>
           <div>
                <label>Make Envelope Private?</label>
                <input type="checkbox" id="checkbox-envelope-private"></input>
            </div>
            <select class="btn btn-primary dropdown-toggle" type="button" id="dropdown-menu-users">
                <option class="dropdown-item">Assign Envelope To User</option>
                <?php foreach ($users as $user): ?>
                <option id="<?php echo $user['username']; ?>" class="dropdown-item" value="<?php echo isset($user['id']) ? $user['id'] : $user['username'] ?>"><?php echo $user['username'] ?></option>
                <?php endforeach; ?>
            </select>
        </div> -->

        <!-- <div>
            <b id="document-label">Document(s): </b>
            <span>
                <?php 
                    for($i = 0; $i < isset($signDocNames) && count($signDocNames); $i++) {
                        echo $signDocNames[$i];
                        if($i < count($signDocNames) - 1) 
                            echo ", "; 
                    }
                ?>
            </span>
        </div> -->

        <!-- <div>
            <b id="recipient-label">Recipient(s): </b>
            <span>
                <?php
                    for($i = 0; $i < isset($signDocNames) && count($recipients); $i++) {
                        echo $recipients[$i];
                        if($i < count($recipients) - 1) 
                            echo ", "; 
                    }
                ?>
            </span>
        </div> -->


        <div class="row ml-0 mt-3">
            <form id="emailForm" class="w-100">
                <div class="row mt-4" style="margin-left: 0px;">
                    <div class=" w-100 form-group">
                        <?php echo CHtml::label(Yii::t('marketing','Email Template'), ''); ?>
                        <?php echo CHtml::dropDownList('template', '', $template, array('class' => 'w-50 form-control', 'style'=>'background: white !important;')); ?>
                    </div>
                </div>

                <div class="row mt-4" style="margin-left: 0px;">
                    <div class="col-6" style="padding-left: 0px; padding-right: 0px;">
                        <div class="form-group">
                            <?php echo CHtml::label(Yii::t('marketing','Delay Send: (Optional)'), ''); ?>
                            <?php echo X2Html::activeDatePicker (new X2SignEnvelopes, 'sendDate', array ('placeholder' => 'Delay until...', 'id' => 'sendDate', 'name' => 'sendDate', 'class' => 'form-control', 'style' => 'background: white !important;'), 'datetime'); ?>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-group">
                            <?php echo CHtml::label(Yii::t('marketing','Expiration Date (optional)'), ''); ?>
                            <?php echo X2Html::activeDatePicker (new X2SignEnvelopes, 'expireDate', array ('placeholder' => 'Expire on...', 'id' => 'expireDate', 'name' => 'expireDate', 'class' => 'form-control', 'style' => 'background: white !important;'), 'datetime'); ?>
                        </div>
                    </div>
                </div>

                <div class="row mt-4" style="margin-left: 0px;">
                    <div class="form-group w-50">
                        <label>Subject</label>
                        <input id="emailSubject" class="form-control" name="emailSubject"></input>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <label id="email-message-label">Email Message</label>
                    <textarea id="emailMessage" class="form-control" style="overflow:auto !important; height: 250px !important; resize: none;" name="emailMessage"></textarea>
                    <!-- <div contenteditable id="emailMessage" class="form-control" style="overflow:hidden !important; height:100% !important;" name="emailMessage" style="width: 500px; hight: 500px;"></div> -->
                </div>
                <div class="form-group d-flex align-items-center mt-1 mb-2">
                    <label for="reminder" class="mb-0 mr-2">Would you like to send reminders?</label>
                    <input type="checkbox" class="my-0" id="reminder" required>
                </div>

                <div id="interDiv" style="display:none;" class="form-group">
                    <label for="interval">How many days apart should the reminders be sent?</label>
                    <input value=2 type="number" min="0" class="w-25 form-control" id="interval" name="interval" placeholder="Interval" required disabled>
                </div>

                <div id="countDiv" style="display:none;" class="form-group mb-0">
                    <label for="count">How many times should reminders be sent?</label>
                    <input value=2 type="number" min="0" class="w-25 form-control" id="count" name="count" placeholder="Count" required disabled>
                </div>
            </form>
        </div>
    </div>
</div>

