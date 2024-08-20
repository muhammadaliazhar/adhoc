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

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/quicksend.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/reOrderDocument.js');

Yii::app()->clientScript->registerScript("editDocsJs", "

    $('#sortable').sortable({
        items: 'li.sign-doc-item',
        cursor: 'move',
    }).disableSelection();

    $('#reorder-accordian').on('click', function (e) {
        if ($(this).find('i').hasClass('fa-angle-down')) return;

        $('#reorder-loader').show();

        $.ajax({
            url: '" .Yii::app()->controller->createUrl('/x2sign/editDocs', array('id' => $envelope->id))."',
            type: 'GET',

            success: function (files) {
                let html = '';
                let promises = [];

                $('#sortable').html('');
                pdfjsLib.disableWorker = true;

                files = JSON.parse(files);

                $.each(files, function(fileName, attrs) {
                    let url =  yii.scriptUrl+'/x2sign/x2sign/getFile/id/' + attrs['mediaId'];

                    let promise = pdfjsLib.getDocument(url).then(function(pdf) {
                        
                        return pdf.getPage(1).then(function(page) {
                            let viewport = page.getViewport({scale: 1.5});
                            let canvas = document.createElement('canvas');
                            let ctx = canvas.getContext('2d');
                            
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            
                            let renderContext = {
                                canvasContext: ctx,
                                viewport: viewport
                            };

                            return page.render(renderContext).promise.then(function() {
                                // set to draw behind current content
                                ctx.globalCompositeOperation = 'destination-over';
                                
                                // set background color
                                ctx.fillStyle = '#fff';
                                
                                // draw on entire canvas
                                ctx.fillRect(0, 0, canvas.width, canvas.height);
                                
                                // create an img from the canvas which contains the page contents
                                let imgSrc = canvas.toDataURL();
                                let fileCard = 
                                `<li class='sign-doc-item' id='\${attrs['id']}'>
                                    <div class='d-flex justify-content-between align-items-center text-left'>
                                        <b class='doc-title'> \${fileName} </b>
                        
                                        <div class='d-flex align-items-center'>
                                            <a id='\${attrs['id']}' class='delete mr-2' onclick='deleteDocument(this.id)'>
                                                <i class='far fa-times-circle delete-doc-icon'></i>
                                            </a>
                        
                                            <a class='download-icon-order' href='\${url}' download='\${fileName}'>
                                                <i class='fa fa-download'></i>
                                            </a>
                                        </div>
                                    </div>

                                    <img src='\${imgSrc}' class='pdf-preview'>
                                </li>`;

                                html += fileCard;
                                canvas.remove();
                            });
                        });
                    });

                    // Push the promise to the array
                    promises.push(promise);
                });

                // Wait for all promises to be fulfilled
                Promise.all(promises).then(function() {
                  let sortedElement = [];

                  $.each(files, function(key, file) {
                    let element = $(html).filter(`#\${file.id}`)[0];
                    sortedElement.push(element);
                  });

                  $('#reorder-loader').hide();

                  $('#sortable').html(sortedElement);
                });
            },

            error: function (data) {
                alert(data.responseText);
            }
        });
    });

    

    //set up the docs
    $('#save-order').on('click', function() {
        var signDocs = [];
        
        $('#sortable').children().each(function () {
            // Add sign doc id to new order of the documents
            signDocs.push(parseInt($(this).attr('id')));
        });        

        $.ajax({
            url: '".Yii::app()->getRequest()->getScriptUrl()."' + '/x2sign/updateDocOrder?id=' + '" .$envelope->id."', 
            type: 'POST',
            data: {
                'docs': signDocs,
                'YII_CSRF_TOKEN': x2.csrfToken
            },
            success: function(data) {
                showAlert('success', 'Documents Order Saved Successfully!');
            },
            error: function(data) {
                showAlert('error', 'Some Error Occurred while saving the documents order! Please try again');
            }
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
", CClientScript::POS_END);


Yii::app()->clientScript->registerCss('sortableItems', '
    .delete-doc-icon {
        font-size: 16px;
    }

    .delete-doc-icon:hover {
        color: red;
    }
    
    .pdf-preview {
        height: 180px;
        width: 180px;
        display: block;
        margin: 12px auto 0 auto;
        border: 1px solid black;
    }

    .sign-doc-item {
        cursor: pointer;
        max-width: 300px; 
        height: auto;
        padding: 5px; 
        display: inline-block;
        margin-right: 12px;
        margin-bottom: 12px;
        border-radius: 5px; 
        border: 1px solid rgb(190, 190, 190); 
        background-color: rgb(255, 255, 255); 
        word-break: break-word; 
    }

    .doc-title {
        width: 100%;
        max-width: 15ch;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ALert bar Styling Start */

    .alert {
        display: none;
        margin-bottom: 10px;
        border: 1px solid #a4a4a4;
        border-radius: 4px;
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        padding: 15px 25px 15px 15px;
        font-size: 14px;
    }

    .alert .close {
        position: absolute;
        right: 0px;
        top: 0px;
        background: transparent;
        border: none;
        font-size: 16px;
    }

    .alert-success {
        color: #000000de;
        background-color: #dff0d8;
        border: 1px solid green;
    }

    .alert-error {
        color: #000000de;
        background-color: #f2dede;
        border-color: #ebccd1;
        border: 1px solid red;
    }

    /* ALert bar Styling End */


    /* Loader Styling Start */

    .lds-ring {
        display: none;
        position: relative;
        width: 80px;
        height: 80px;
        margin: 50px auto;
    }
    
    .lds-ring div {
        box-sizing: border-box;
        display: block;
        position: absolute;
        width: 64px;
        height: 64px;
        margin: 8px;
        border: 8px solid #8a8a8a;
        border-radius: 50%;
        animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        border-color: #8a8a8a transparent transparent transparent;
    }
    
    .lds-ring div:nth-child(1) {
        animation-delay: -0.45s;
    }
    
    .lds-ring div:nth-child(2) {
        animation-delay: -0.3s;
    }
    
    .lds-ring div:nth-child(3) {
        animation-delay: -0.15s;
    }
    
    @keyframes lds-ring {
        0% {
            transform: rotate(0deg);
        }
    
        100% {
            transform: rotate(360deg);
        }
    }

    /* Loader Styling End */
');
?>

<div class="reorder-docs-container section-container container">
    
    <div class="alert"></div>

    <div class="section-header">
        <h3 id="add-documents">Re-Order Documents</h3>
        <a id="reorder-accordian" class="accordianBtn"><i class="fa fa-angle-down"></i></a>
    </div>

    <div class="section-body text-center hide">
        <div id="reorder-loader" class="lds-ring"><div></div><div></div><div></div><div></div></div>

        <ul 
            id="sortable"
            style="width: 100%; list-style-type: none; max-height: 600px; overflow-y: auto;"
        >
        </ul>
        
        <div class="cell right pb-4 pr-3" style="float: right;">
            <button id="save-order" class="btn btn-warning">Save Order</button>
        </div>
    </div>
</div>

