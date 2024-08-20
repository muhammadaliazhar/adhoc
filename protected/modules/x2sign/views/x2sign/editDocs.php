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
// Import Bootstrap
Yii::app()->clientScript->registerCssFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
Yii::app()->clientScript->registerScriptFile('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js');
Yii::app()->clientScript->registerScriptFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/pdfReader/build/pdf.js');

$cs = Yii::app()->clientScript;
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/ui-elements.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/font-awesome.css');
// $cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/all.css');
// $cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/v4-shims.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/form.css?' . Yii::app()->params->buildDate, 'screen, projection');

Yii::app()->clientScript->registerScript('hideSidebarJS', "
    $('#fullscreen-button').hide();
    $('#sidebar-right').hide();
");


Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/quicksend.css');
Yii::app()->clientScript->registerScript("quickRecipientJS", "
    let signDocs = " . $signDocs . ";

    $(function() {
        $('#sortable').sortable();
        $('#sortable').disableSelection();
    });
    
    //set up the docs
    $('#next-button').on('click', function() {
        var signDocs = [];
        $('#sortable').children().each(function () {
            // Add sign doc id to new order of the documents
            signDocs.push(parseInt($(this).attr('id')));
        });

        $.ajax({
            url: '" . Yii::app()->controller->createUrl('/x2sign/updateDocOrder', array('id' => $envelope->id)) . "',
            type: 'POST',
            data: {
                'docs': signDocs,
                '" . Yii::app()->request->csrfTokenName . "': '" . Yii::app()->request->csrfToken . "',
            },
            success: function(data) {
                console.log(data);
                window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/quickSetupRecipients', array('id' => $envelope->id)) . "';
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    //set up the docs
    $('#next-email-button').on('click', function() {
        var signDocs = [];
        $('#sortable').children().each(function () {
            // Add sign doc id to new order of the documents
            signDocs.push(parseInt($(this).attr('id')));
        });

        $.ajax({
            url: '" . Yii::app()->controller->createUrl('/x2sign/updateDocOrder', array('id' => $envelope->id)) . "',
            type: 'POST',
            data: {
                'docs': signDocs,
                '" . Yii::app()->request->csrfTokenName . "': '" . Yii::app()->request->csrfToken . "',
            },
            success: function(data) {
                console.log(data);
                window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/quickEmailView', array('id' => $envelope->id)) . "';
            },
            error: function(data) {
                console.log(data);
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

    $('#cancel-button').on('click', function() {
        confirmDialog('Are you sure you want to cancel this envelope? All progress will be lost.');
    });

    $('#add-document').on('click', function() {
        window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/addDocs', array('id' => $envelope->id)) . "';
    });

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
                        var recipients = [];
                        $('.recipient').each(function() {
                            recipients.push({
                                'firstName': $(this).find('.firstNameInput').val(),
                                'lastName': $(this).find('.lastNameInput').val(),
                                'email': $(this).find('.emailInput').val(),
                                'displayModel': 'Contacts',
                                'hiddenModel': $(this).find('.hiddenModel').val(),
                                'hiddenId': $(this).find('.hiddenId').val(),   
                                'order':  $(this).find('.orderNum').val(), 
                            });    
                        });

                        // Check if there's any duplicate recipients
                        let values = recipients.map(function(item) { return item.email; });
                        if(new Set(values).size !== recipients.length) {
                            alert('Remove any duplicate contacts');
                            return;        
                        }
                        window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/finishLater') .
                         "?id=" . $envelope->id . "&recipients=recipients';
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

    // Adapted from: https://bl.ocks.org/palerdot/bf0c52d84aa046a6963c
    $(document).ready(function() {
        pdfjsLib.disableWorker = true;
        // for(const fileName in signDocs) {
        $.each(signDocs, function(fileName, attrs) {
            let url = '" . $this->createUrl('/x2sign/x2sign/getFile/id') . "/' + attrs['mediaId'];
            pdfjsLib.getDocument(url).then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                    let viewport = page.getViewport({scale: 1.5});
                    let canvas = document.createElement('canvas');
                    let ctx = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    let renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };

                    page.render(renderContext).promise.then(function() {
                        //set to draw behind current content
                        ctx.globalCompositeOperation = 'destination-over';
                        //set background color
                        ctx.fillStyle = '#fff';
                        //draw on entire canvas
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        // create an img from the canvas which contains the page contents
                        let imgSrc = canvas.toDataURL();
                        let img = $('<img>').attr('src', imgSrc).attr('class', 'pdf-preview');
                        let clone = $('#list-item-template').clone();
                        $(clone).attr('id', attrs['id']);
                        $(clone).find('b').html(fileName);
                        $(clone).append(img)
                            .appendTo('#sortable')
                            .show();
                        canvas.remove();
                    });
                });
            });
        });
    });

    function showAlert(type, message, dismissable) {
        let alertBox = $('.alert');
        alertBox.removeClass('alert-success alert-error');
    
        if (type == 'success') {
            alertBox.addClass('alert-success');
            alertBox.html(`<i class='fa fa-check mr-2' aria-hidden='true'></i>` + message);
        }
        else {
            alertBox.addClass('alert-error');
            alertBox.html(`<i class='fa fa-times mr-2' aria-hidden='true'></i>` + message);
        }
    
        alertBox.html(alertBox.html() + (dismissable ? `<button type='button' class='close'>&times;</button>` : ''));
    
        alertBox.fadeIn();
    
        if (dismissable) {
            alertBox.find('.close').on('click', function () {
                alertBox.fadeOut();
            });
        }
        else {
            setTimeout(() => {
                alertBox.fadeOut(2000);
            }, 2000);
        }
    }

", CClientScript::POS_READY);

Yii::app()->clientScript->registerCss('sortableItems', '
    .delete-doc-icon:hover {
        color: red;
        transform: scale(1.2);
    }
    
    .pdf-preview {
        height: 300px;
        width: 220px;
        margin-top: 10px;
        margin-left: 50%;
        transform: translate(-50%);
        border: 1px solid black;
    }

    #list-item-template {
        display: none;
    }

    .sign-doc-item {
        border: 1px solid rgb(190, 190, 190); 
        border-radius: 5px; 
        cursor: pointer; 
        background-color: rgb(255, 255, 255); 
        margin: 0px 0px 5px 20px; 
        max-width: 400px; 
        padding: 5px; 
        word-break: break-word; 
        height: auto;
        transform: translate(50%);
    }
');
?>

<div class="container">
    <div class="dropdown cell right pb-4" style="float: right; margin-top: 10px; margin-right: 10px;">
        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Envelope Actions
        </button>
        <div class="dropdown-menu">
            <button id="finish-later" class="dropdown-item">Finish Later</button>
            <button id="add-document" class="dropdown-item">Add Document</button>
            <button id="cancel-button" class="dropdown-item">Void Envelope</button>
        </div>
    </div>
    <li class="sign-doc-item" id="list-item-template">
        <div>
            <b></b><a class="right delete" onclick="$(this).parent().parent().remove();"><i class="far fa-lg fa-times-circle delete-doc-icon"></i></a>      
        </div>
    </li>
    <ul id="sortable" style="width: 100%; list-style-type: decimal; height: 600px; overflow-y: auto; border-radius: 3px 3px 0px 0px; padding: 10px 0px;">
    </ul>
    <div class="cell right pb-4" style="float: right;">
        <button id="next-email-button" class="btn btn-secondary">Back To Email Setup</button>
        <button id="next-button" class="btn btn-warning">Back to Recipient Setup</button>
    </div>
</div>


