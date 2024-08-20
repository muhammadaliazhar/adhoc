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
 * @author Clifton Chiang <clifton@x2engine.com>
 */
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/pdfReader/build/pdf.js');

$Allura = Yii::app()->controller->module->assetsUrl.'/main/fonts/Allura/Allura-Regular.otf';
$Otto = Yii::app()->controller->module->assetsUrl.'/main/fonts/Otto/Otto.ttf';
$May = Yii::app()->controller->module->assetsUrl.'/main/fonts/may-queen/mayqueen.ttf';

$signature = X2Signature::model()->findByAttributes(array(
    'modelId' => $model->id,
    'modelType' => $modelType,
));

if(isset($signature)){
    $signatureExists = 1;
    $image = $signature->getSignatureImage();
} else {
    $signatureExists = 0;
    $image = '';
}

Yii::app()->clientScript->registerScript('retrieveDocs', '
    const dropzone = $("#pdf-dropzone")[0];
    var currentDocId =  "' . $signDocId . '";
    var image = "' . $image . '";
    var signatureId = "' . (isset($signature->id) ? $signature->id : "") . '";
    var fields = ' . $fields . ';
    var signReq = ' . $signReq . ';

    const recipientColors = {
        1: "rgba(3, 207, 252, 0.5)",
        2: "rgba(252, 207, 3, 0.5)",
        3: "rgba(98, 209, 0, 0.5)",
        4: "rgba(255, 17, 0, 0.5)",
        5: "rgba(0, 218, 196, 0.5)",
        6: "rgba(255, 0, 221, 0.5)",
        7: "rgba(0, 140, 255, 0.5)",
        8: "rgba(132, 0, 255, 0.5)",
        9: "rgba(255, 140, 0, 0.5)",
        10: "rgba(173, 173, 173, 0.5)",
    };



    function replaceSignature(element) {
        var clone = $(image).clone();
        $(element).replaceWith(clone);

        clone.attr({
            "id": $(element).attr("id"),
            "page": $(element).attr("page"),
            "req": $(element).attr("req"),
            "read-only": $(element).attr("read-only"),
            "recip": $(element).attr("recip"),
        }).css({
            "top": $(element).css("top"),
            "left": $(element).css("left"),
            "width": $(element).css("width"),
            "height": $(element).css("height"),
            "position": "absolute",
        });
    }

    // Render the fields as needed for the recipient
    function renderFields() {
        const template = $("template").prop("content");
        if(fields.length > 0) {
            for(i = 0; i < fields.length; i++) {
                var field = fields[i];
                
                if(!field["id"].includes("Date") && (' . $position . ' == 2 || (' . $position . ' == 1 && field["recip"] == 1))) {
                    var original = $(template).children("#" + field["id"].substring(0, field["id"].indexOf("-")));
                    var clone = $(original).clone().attr({
                        "id": field["id"],
                        "page": field["page"],
                        "req": field["req"],
                        "read-only": field["read-only"],
                        "recip": field["recip"],
                    }).css({
                        "top": field["top"],
                        "left": field["left"],
                        "height": field["height"],
                        "width": field["width"],
                        "position": "absolute",
                        "background-color": recipientColors[field["recip"]]
                    }).appendTo(dropzone);
                    
                    // If the cloned element is a signature field, and it\'s not the current recipients\',
                    // then replace with their signature image to show other recipients that it\'s been signed
                    if(field["id"].includes("Signature") && field["recip"] != ' . $position . ') {
                        let sigImg = $(field["value"]).clone();
                        $(clone).replaceWith(sigImg);
                
                        sigImg.attr({
                            "id": $(clone).attr("id"),
                            "page": $(clone).attr("page"),
                            "req": $(clone).attr("req"),
                            "read-only": $(clone).attr("read-only"),
                            "recip": $(clone).attr("recip"),
                        }).css({
                            "top": $(clone).css("top"),
                            "left": $(clone).css("left"),
                            "width": $(clone).css("width"),
                            "height": $(clone).css("height"),
                            "position": "absolute",
                        });
                    }

                    if($(clone).attr("read-only") == 1)
                        $(clone).children("input").prop("disabled", true);

                    // Find any fields that had insertable attributes and
                    // change their size
                    var input = $(clone).find("input");
                    if(input.length > 0) {
                        // Size the input field proportionally to the wrapper div
                        $(clone).children("input").css("width", field["width"] - 15).val(field["value"]);

                        // Adjust input width depending on amount of text  
                        if(($(input).parent().attr("id").indexOf("Text") == -1 && $(input).val().length > 13) || 
                            ($(input).parent().attr("id").indexOf("Text") == 0 && $(input).val().length > 3)) {
                            var temp = $(input).val().length * 8;
                            $(input).width((($(input).val().length + 1) * 7.3) + "px"); 
                            $(input).parent().width((($(input).val().length + 1) * 7.3) + 20 + "px");
                        } else if($(input).parent().attr("id").indexOf("Text") == -1 && $(input).val().length < 13 && $(input).width() > 95) {
                            if($(input).attr("id") !== "initials") {
                                $(input).width("95px");
                                $(input).parent().width("115px");
                            }
                        } else if($(input).parent().attr("id").indexOf("Text") == 0 && $(input).val().length < 4) {
                            $(input).width("27px");
                            $(input).parent().width("44px");
                        }
                    }
                } else {
                    let date;
                    let dateElem = $("<p></p>").attr({
                        "id": field["id"],
                        "page": field["page"],
                        "req": field["req"],
                        "read-only": field["read-only"],
                        "recip": field["recip"],
                    }).css({
                        "top": field["top"],
                        "left": field["left"],
                        "position": "absolute",
                    });

                    if(field["value"] === undefined && field["recip"] == '. $position .') {
                        date = new Date();
                        $(dateElem).text(date.toLocaleDateString()).val(date.toLocaleDateString());
                    } else {
                        $(dateElem).text(field["value"]).val(field["value"]);
                    }
                    $("#pdf-dropzone").append(dateElem);
                }
            }
            $("#sign-button").val("Sign");
        } else {
            $("#sign-button").val("Next");
        }

        // Hide or show the fields depending on the page
        $(dropzone).children().each(function() {
            if($(this).attr("page") != pageNum)
                $(this).hide();
            else
                $(this).show();
        });
    }

    $(".create-signature-button").click(function () {
        if($("input#create-signature").val().length == 0) {
            alert("Please fill in the signature field to begin signing.");
            return;
        }
        $.ajax({
            url: "' . $this->createUrl('/x2sign/x2sign/createSignature') . '",
            type: "POST",
            data: {
                signature: $("#create-signature").val().trim(),
                font: $("#signature-font div input:checked").val(),
                modelId: ' . $model->id . ',
                modelType: "' . $modelType . '",
            },
            success: function(data) {
                $("#create-signature-modal").hide();
                data = JSON.parse(data);
                image = $(data[0]);
                signatureId = data[1];
            }
        });
    });

    $(document).on("input", ".x2-sign-input", function() {
        // Adjust input width depending on amount of text    
        if(($(this).parent().attr("id").indexOf("Text") == -1 && $(this).val().length > 13) || 
            ($(this).parent().attr("id").indexOf("Text") == 0 && $(this).val().length > 3)) {
            var temp = this.value.length * 8;
            $(this).width(((this.value.length + 1) * 7.3) + "px"); 
            $(this).parent().width(((this.value.length + 1) * 7.3) + 20 + "px");
        } else if($(this).parent().attr("id").indexOf("Text") == -1 && $(this).val().length < 13 && $(this).width() > 95) {
            if($(this).attr("id") !== "initials") {
                $(this).width("95px");
                $(this).parent().width("115px");
            }
        } else if($(this).parent().attr("id").indexOf("Text") == 0 && $(this).val().length < 4) {
            $(this).width("27px");
            $(this).parent().width("44px");
        }
    });

    // Sign Document
    $("#sign-button").click(function () {
        var signFields = [];
        var fail = 0;
        $(dropzone).children().each(function () {
            if($(this).attr("recip") == ' . $position . ') {
                if(!$(this).attr("id").includes("Signature")) {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).find("input").val().length > 0)) {
                        signFields.push({
                            "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                            "fieldId": $(this).attr("id"),
                            "value": $(this).attr("id").indexOf("Date") == -1 ? $(this).find("input").val() : $(this).val(),
                        });
                    } else {
                        $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                        fail = 1;
                    }
                } else {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).is("img"))) {
                        signFields.push({
                            "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                            "fieldId": $(this).attr("id"),
                            "value": $(this).is("img") ? signatureId : "",
                        });
                    } else {
                        $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                        fail = 1;
                    }
                }
            }
        });
        
        if(fail == 1) {
            signFields = [];
            alert("Please complete remaining fields highlighted in red.");
            return;
        }

        $.ajax({
            url: "' . $this->createUrl('/x2sign/x2sign/sign') . '",
            type: "POST",
            data: {
                signReq: signReq,
                fields: signFields,
                signDocId: currentDocId,
                modelId: ' . $model->id . ',
                modelType: "' . $modelType . '",
                
            },
            success: function(data) {
                if(data == "complete") {
                    window.location.replace("' . $this->createUrl('/x2sign/x2sign/signingComplete') . '");
                } else {
                    data = JSON.parse(data);
                    // Clear PDF if user chooses a different PDF to use for a template
                    $("#pdf-dropzone").empty();

                    $("#page_num").empty();
                    $("#page_count").empty();
                    canvas = document.getElementById("pdf");
                    ctx = canvas.getContext("2d");
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    pdfDoc = null,
                        url = "' . $this->createUrl('/x2sign/x2sign/getFile/id') . '/" + data["mediaId"],
                        pageNum = 1,
                        pageRendering = false,
                        pageNumPending = null,
                        scale = 2,
                        canvas = document.getElementById("pdf"),
                        ctx = canvas.getContext("2d");
                    
                    fields = JSON.parse(data["fields"]);
                    currentDocId = data["signDocId"];
                    renderPDF(url);
                }
            }
        });
    });

    /**
     * Get page info from document, resize canvas accordingly, and render page.
     * @param num Page number.
     */
    function renderPage(num) {
        pageRendering = true;
        // Using promise to fetch the page
        pdfDoc.getPage(num).then(function(page) {
            var viewport = page.getViewport({scale: scale});
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Render PDF page into canvas context
            var renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            var renderTask = page.render(renderContext);

            // Wait for rendering to finish
            renderTask.promise.then(function() {
                pageRendering = false;
                if (pageNumPending !== null) {
                    // New page rendering is pending
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
            });
        });

        // Update page counters
        document.getElementById("page_num").textContent = num;
    }

    /**
     * If another page rendering in progress, waits until the rendering is
     * finised. Otherwise, executes rendering immediately.
     */
    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }

    /**
     * Displays previous page.
     */
    function onPrevPage() {
        if (pageNum <= 1) {
            return;
        }
        pageNum--;
        queueRenderPage(pageNum);
        // Hide or show the draggables depending on the page
        $(dropzone).children().each(function() {
            if($(this).attr("page") != pageNum)
                $(this).hide();
            else
                $(this).show();
        });
    }
    document.getElementById("prev").addEventListener("click", onPrevPage);

    /**
     * Displays next page.
     */
    function onNextPage() {
        if (pageNum >= pdfDoc.numPages) {
            return;
        }
        pageNum++;
        queueRenderPage(pageNum);
        // Hide or show the draggables depending on the page
        $(dropzone).children().each(function() {
            if($(this).attr("page") != pageNum)
                $(this).hide();
            else
                $(this).show();
        });
    }
    document.getElementById("next").addEventListener("click", onNextPage);

    /**
     * Asynchronously downloads PDF.
     */
    function renderPDF(url) {
        pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById("page_count").textContent = pdfDoc.numPages;

            // Initial/first page rendering
            renderPage(pageNum);
            
            if(signReq == 1)
                renderFields();
            else
                $("#sign-button").val("Next");
        });
    }

    // Render whatever initial page for signing is needed
    pdfDoc = null,
        url = "' . $this->createUrl('/x2sign/x2sign/getFile/id') . '/" + ' . $mediaId . ',
        pageNum = 1,
        pageRendering = false,
        pageNumPending = null,
        scale = 2,
        canvas = document.getElementById("pdf"),
        ctx = canvas.getContext("2d");

    renderPDF(url);

    if(' . $signatureExists . ' == 0) {
        $("#create-signature-modal").show();
    }

    function upload_signature() {
        var fileUploader = x2.FileUploader.list["sign-upload"];
        var csrfTokenRegex = /(?:^|.*;)\s*YII_CSRF_TOKEN\s*=\s*([^;]*)(?:.*$|$)/;
        var csrfToken = document.cookie.replace (csrfTokenRegex, "$1");
        x2.csrfToken = csrfToken;
        fileUploader.toggle();
    }

    if($("#create-signature-modal").is(":visible")) 
        x2.FileUploader.on ("sign-upload", "success", function(){
            x2.FileUploader.list["sign-upload"].toggle();
            $("#create-signature-modal").hide();
        });
', CClientScript::POS_END);

Yii::app()->clientScript->registerCss('signDoc', '
    .column {
        float: left;
        margin: unset;
    }

    #document {
        background-color: #cccccc;
        width: 855px;
        border-radius: 3px;
        position: absolute;
        left: 50%;
        margin-left: -422.5px;
    }

    hr {
        background-color: #555555;
        margin: 1px;
    }

    .x2-sign-doc {
        width: 816px; 
        height: 1056px;
        display: inline-block; 
        margin: auto;
        z-index: 1;
        position: absolute;
        left: 50%;
        margin-left: -408px;
    }

    #pdf-dropzone {
        z-index: 2;
        width: 816px;
        height: 1056px;
        position: absolute;
        left: 50%;
        margin-left: -408px;
    }

    .x2-sign-template {
        position: relative;
        height: 1070px;
    }

    .sign-field {
        z-index: 3;
        top: 6px;
        background-color: rgba(3, 207, 252, 0.5);
    }

    input.x2-sign-input:not([id=initials]) {
        min-width: 95px !important;
        width: 95px;
        position: relative;
        top: 5px;
        left: 7px;
        height: 20px;
        text-align: left;
        font-family: monospace !important;
        font-size: 12px !important;
        letter-spacing: 0.14px;
        margin: unset !important;
    }

    input#text {
        position: relative;
        min-width: 27px !important;
        width: 27px;
        top: 5px;
        left: 5px;
        margin: unset;
    }

    input#checkbox {
        position: absolute;
        top: 7px;
        left: 8px;
        margin: unset;
    }

    input#initials {
        min-width: 44px !important;
        width: 44px;
        position: relative;
        top: 5px;
        left: 7px;
        height: 20px;
        text-align: left;
        font-family: monospace;
        font-size: 12px;
        letter-spacing: 0.14px;
        margin: unset;
    }

    #main-column {
        width: 90%;
        margin: auto;
    }

    .checkbox {
        display: block;
        text-align: center;
        margin-top: 9px;
        margin-bottom: 3px;
    }

    #sign-button-container {
        margin: auto;
        height: 100px;
    }

    #sign-button {
        width: 20%;
        top: 38%;
        position: relative;
        height: 42%;
        font-size: 135%;
        margin: auto;
        background-color: #fff700;
    }

    a[id^="Signature"] > i {
        display: block;
        margin: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-right: -50%;
        transform: translate(-50%, -50%);
    }

    a[id^="Date"] > i {
        display: block;
        margin: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-right: -50%;
        transform: translate(-50%, -50%);
    }
    
    #create-signature-modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 3; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0, 0, 0); /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 20% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 20%; /* Could be more or less, depending on screen size */
    }

    #create-signature {
        text-align: center;
        width: 70%;
        margin: auto;
        display: block;
        border: solid 1px black;
        border-radius: 5px;
    }

    .create-signature-button {
        margin: auto !important;
        display: inline-block !important;
    }

     @font-face {
        font-family: Allura-X2;
        src: url('. $Allura  .');
    }

    @font-face {
        font-family: Otto-X2;
        src: url('. $Otto .');
    }

    @font-face {
        font-family: May-X2;
        src: url('. $May  .');
    }

    #signature-font {
        margin-bottom: 1em;
        margin-left: 1em;
    }

    .font-Allura {
        font-family: Allura-X2;
    }

    .font-Otto {
        font-family: Otto-X2;
    }

    .font-May {
        font-family: May-X2;
    }

    .font-label {
        margin-left: 1em;
        font-size: 30pt;
    }

    .signature-upload {
        margin-bottom: 1em;
    }
');

?>

<!-- Adapted from example: https://stackoverflow.com/questions/43165984/how-to-create-modal-box-using-html-css-and-jquery -->

<template>
    <a id="Signature" class="x2-button sign-field" style="display:none;" onclick="replaceSignature(this);">
        <i class="fas fa-signature fa-2x"></i>
    </a>
    <div id="Initials" class="sign-field" style="display:none;">
        <input id="initials" class="x2-sign-input" type="text" minlength="1" maxlength="5" size="5" placeholder="Initials"></input>
    </div>
    <div id="Formula" class="sign-field" style="display:none;">
        <input id="formula" class="x2-sign-input" type="text" minlength="1" size="15" placeholder="Formula"></input>
    </div>
    <div id="Checkbox" class="sign-field" style="display:none;">
        <input id="checkbox" class="x2-sign-input" type="checkbox"></input>
    </div>
    <div id="Text" class="sign-field" style="display:none;">
        <input id="text" class="x2-sign-input" type="text" size="15" placeholder="Text"></input>
    </div>
    <div id="Name" class="sign-field" style="display:none;">
        <input id="name" class="x2-sign-input" type="text" minlength="1" size="15" placeholder="Name"></input>
    </div>
    <div id="Email" class="sign-field" style="display:none;" page="">
        <input id="email" class="x2-sign-input" type="email" placeholder="Email"></input>
    </div>
    <div id="Title" class="sign-field" style="display:none;" page="">
        <input id="title" class="x2-sign-input" type="text" minlength="1" placeholder="Title"></input>
    </div>
</template>
<div id="document" class="column">
    <div class="x2-button-group" style="text-align: center;">
        <span id="prev" class="x2-button page-prev">Previous</span>
        <span id="next" class="x2-button page-next">Next</span>
        &nbsp; &nbsp;
        <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
    </div>
    <div class="row x2-sign-template">
        <canvas id="pdf" class="x2-sign-doc"></canvas>
        <div id="pdf-dropzone"></div>
        <canvas id="test"></canvas>
    </div>
    </br>
    <div class="cell" id="sign-button-container">
        <input id="sign-button" type="submit" class="x2-button" value="<?php echo Yii::t('app', 'Sign');?>" />
    </div>
</div>
