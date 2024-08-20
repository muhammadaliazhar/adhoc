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
 * @edited By: Justin Toyomitsu <justin@x2engine.com>
 */
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/pdfReader/build/pdf.js');

$title = Yii::t('docs', 'Create X2SignDoc');
$authParams = array('X2Model' => $model);
$menuOptions = array(
  'index', 'create', 'createEmail', 'createQuote', 'createSignable', 'helpGuide',
);

if (!$model->isNewRecord) {
    $existingRecordMenuOptions = array(
        'view', 'permissions', 'exportToHtml', 'edit', 'delete'
    );
    $menuOptions = array_merge($menuOptions, $existingRecordMenuOptions);
}
$this->insertMenu($menuOptions, $model, $authParams);
$this->renderPartial('_docPageHeader', compact('title', 'model'));
$layoutManager = $this->widget('RecordViewLayoutManager', array('staticLayout' => false));

$form = $this->beginWidget('CActiveForm', array(
	'id' => 'x2sign-form',
	'enableAjaxValidation' => false,
)); 

Yii::app()->clientScript->registerScript("retrievePdf", '
    const dropzone = $("#pdf-dropzone")[0];

    var draggableCounter = 0;
    var documentId = null;

    // Create the draggable onto the dropzone
    function createDraggable(id) {
        var pageNum = $("#page_num").text();
        var original = document.getElementById(id + "-draggable");
        var clone = original.cloneNode(true);
        $(clone).attr("id", id + "-" + ++draggableCounter);
        if(pageNum === "")
            $(clone).attr("page", 1);
        else
            $(clone).attr("page", pageNum);
        $(clone).removeAttr("style");
        $(clone).css({
            "left": "0px", 
            "top": "10px", 
            "position": "absolute",
            "height": "30px",
            "cursor": "pointer",
            "z-index": "4",
            "outline": "0.2rem ridge #ffff00",
        });

        // Change the fields\' width depending if it\'s not a checkbox, date, or initials
        if(!clone.id.includes("Checkbox") && !clone.id.includes("Initials") && 
            !clone.id.includes("Date") && !clone.id.includes("Text")) {
            $(clone).css("width", "130px");
        } else if(clone.id.includes("Initials")) {
            $(clone).css("width", "85px");
        } else if(clone.id.includes("Text")){
            $(clone).css("width", "130px");
        } else {
            $(clone).css("width", "30px");
        }


        if(!clone.id.includes("Checkbox") && 
            !clone.id.includes("Date") && !clone.id.includes("Signature")) {
            $(clone).css("height", "26Px");            
        }

        
        // Give the newest draggable the active class and remove it
        // from the current active draggable
        $("#pdf-dropzone > .active").removeClass("active").css({
            "z-index": "3",
            "outline": "",
        });
        
        // Make the newest element added to document active
        // and make required and read-only false by default
        $(clone).addClass("active");
        $(clone).attr("req", 0);
        $(clone).attr("read-only", 0);

        // Make the required and read-only checkboxes unchecked
        // when adding a field
        $("#is-required").prop("checked", false);
        if($(clone).attr("id").includes("Signature") || $(clone).attr("id").includes("Initials")) {
            $("#is-required").prop("checked", true);
            $(clone).attr("req", 1);
        }
        $("#read-only").prop("checked", false);

        dropzone.appendChild(clone);

        if($(".field-options").is(":hidden") && $(clone).attr("id").indexOf("Date") == -1) {
            $(".field-options").show();
        } else if($(".field-options").is(":visible") && $(clone).attr("id").indexOf("Date") == 0) {
            $(".field-options").hide();
        }

        $(clone).css("background-color", $("#choose-recipient option:selected").val());
        $(clone).draggable({
            addClasses: false,
            scroll: true,
            cancel: ".delete-field, input, textarea",
            containment: "parent",
            cursor: "pointer",
            //appendTo: "div"
        });

        if($(clone).attr("id").includes("Signature"))
            $(clone).resizable({
                handles: "ne, se, sw, nw",
                cancel: "a, i",
                containment: "parent",
            });

             if($(clone).attr("id").includes("Text") || $(clone).attr("id").includes("Title") || $(clone).attr("id").includes("Email")  || $(clone).attr("id").includes("Name")  || $(clone).attr("id").includes("Formula")){
                 $(clone).resizable({
                     handles: "ne, se, sw, nw",
                     cancel: "a, i",
                     containment: "parent",
                     resize: function(event, ui ) {
                          $(clone).find("input").width((($(clone).width() - 5)) - 30 + "px");
                           $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
                     }
                 });
                $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
             }
 
 
             if($(clone).attr("id").includes("Initials")){
                     $(clone).resizable({
                     handles: "ne, se, sw, nw",
                     cancel: "a, i",
                     containment: "parent",
                     resize: function(event, ui ) {
                          $(clone).find("input").width((($(clone).width() - 5)) - 35 + "px");
                           $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
 
                     }
                 });
                 $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
             }

        
        if($(clone).attr("id").includes("Date"))
            $(clone).css({
                "height": "20px",
                "width": "80px",
            });
    }

    $("#pdf-dropzone").on("drag click mousedown", function(e) {
        if(e.target === this) {
            $("#pdf-dropzone > .active").removeClass("active").css({
                "z-index": "3",
                "outline": "",
            });
            $(".field-options").hide();
        }
    });

    // Change what draggable currently has the active
    // class for determining what recipient the field is
    // assigned to
    $("#pdf-dropzone").on("drag click mousedown", ".draggable", function(e) {
        // Don\'t allow event to trigger if user is clicking on the delete button
        if($(e.target).is("a, a *"))
            return;
        
        // Make sure that there is draggable with "active" class
        // before trying to manipulate it
        if($("#pdf-dropzone > .active").length > 0) {
            if($("#pdf-dropzone > .active").attr("id").includes("Signature")) {
                $("#pdf-dropzone > .active").resizable("disable");
            }
            
            $("#pdf-dropzone > .active").removeClass("active").css({
                "z-index": "3",
                "outline": "",
            });
        }

        if($(".field-options").is(":hidden") && $(this).attr("id").indexOf("Date") == -1) {
            $(".field-options").show();
        } else if($(".field-options").is(":visible") && $(this).attr("id").indexOf("Date") == 0) {
            $(".field-options").hide();
        }

        $(this).addClass("active").css({
            "z-index": "4",
            "outline": "0.2rem ridge #ffff00",
        });
        
        if($(this).attr("id").includes("Signature"))
            $(this).resizable("enable");

        if($(this).attr("req") == 1)
            $("#is-required").prop("checked", true);
        else
            $("#is-required").prop("checked", false);

        if($(this).attr("read-only") == 1)
            $("#read-only").prop("checked", true);
        else
            $("#read-only").prop("checked", false);

        if($(this).find("input").attr("maxlength") > 0){
            $("#text-Length").val($(this).find("input").attr("maxlength"));
        }else{
            $("#text-Length").val("");
        }



        var fieldName = $(this).attr("id").substr(0, $(this).attr("id").indexOf("-"));

        $("span.color-preview").css("background-color", $(this).css("background-color"));

        $("#choose-recipient").val($(this).css("background-color"));
    });

    // Remove draggable
    function deleteDraggable(parent) {
        if($(parent).hasClass("active") && $("#pdf-dropzone").children().length > 1) {
            $(".field-options").hide();
        }
        
        $(parent).remove();

        if($("#pdf-dropzone").children().length == 0) {
            $(".field-options").hide();
        }
    }

    $(document).on("input", ".x2-sign-input", function() {
        // Field is not text field       
         var textLe =  getTextWidth($(this).val() + "test", $(this).css("font-size") + " " +  $(this).css("font-family") );
         var singleText =  getTextWidth("t", $(this).css("font-size") + " "  +  $(this).css("font-family") );
         var whiteLength = (singleText+2) * $(this).val().length / 2; 
         if( textLe + (singleText * 2) + 4 + whiteLength + 35> $(this).parent().width()){ 
             var ratio = (($(this).parent().height() * .8) / 12);
             $(this).width(textLe - singleText  +  "px"); 
             $(this).parent().width(textLe - singleText + 36  +  "px");
             //$(this).width(((this.value.length + 1) * 7.3) * ratio + "px"); 
             //$(this).parent().width(((this.value.length + 1) * 7.3) * ratio + 35 + "px");
         }else if(textLe + 36 < $(this).parent().width() - (singleText * 2)){
             $(this).width(textLe - singleText  +  "px"); 
             $(this).parent().width(textLe - singleText  + 35 + "px");
        }

    });

    $("#create-button").click(function(e) {
        e.preventDefault();

        // Remove error messages and error class
        // from anything to prevent error message from being duplicated
        $(".error").removeClass("error");
        $(".errorMessage").remove();

        var errors = false;
        if($.trim($("#doc-name").val()).length == 0) {
            $("#doc-name").addClass("error");
            $("[for=\'X2SignDocs_name\']").addClass("error");
            $("#doc-name").after("<div class=\'errorMessage\'>Name cannot be blank</div>");
            errors = true;
        }
        if(!documentId) {
            $("#Media_name").addClass("error");
            $("[for=\'X2SignDocs_document\']").addClass("error");
            $("#Media_name").after("<div class=\'errorMessage\'>Please select document</div>");
            errors = true;
        }
        if($("#pdf-dropzone").children().length == 0) {
            $("#create-button").before("<div class=\'errorMessage\'>No fields placed on the document</div>");
            errors = true;
        }

        if(!errors) {
            var draggables = [];
            var recipient = "";
            $("#pdf-dropzone").children().each(function() {
                recipient = $("#choose-recipient option[value=\'" + $(this).css("background-color") + "\']").text();

                draggables.push({
                    "id": $(this).attr("id"),
                    "page": $(this).attr("page"),
                    "top": $(this).css("top"),
                    "left": $(this).css("left"),
                    "value": $(this).find("input").val(),
                    "width": $(this).width(),
                    "height": $(this).height(),
                    "req": $(this).attr("req"),
                    "read-only": $(this).attr("read-only"),
                    "recip": recipient,
                    "maxlength" : $(this).find("input").attr("maxlength"),
                });
            });
            JSON.stringify(draggables);

            var checkValue = 0;
            if(document.getElementById("officeLibrary") != null){
                 checkValue  = document.getElementById("officeLibrary").checked;

            }


            $.ajax({
                url: "' . $this->createUrl('/docs/docs/createSignable') . '",
                type: "POST",
                data: {
                    name: $("#doc-name").val(),
                    X2SignDocs: draggables,
                    file: documentId,
                    officeLibrary: checkValue,
                    selectLibrary: $("#select-Library").val(),

                },
                success: function(data) {
                    data = JSON.parse(data);
                    window.location.replace("' . $this->createUrl('/docs/docs/viewSignable/id/') . '/" + data["id"] + "?mediaId=" + data["mediaId"]);
                }
            });
        }
    })

    $("#Media_name").change(function() {
        if($("#MediaModelId").val() === "")
            return; 
        
        // Stop user from being able to select document to make template
        // while the document is loaded
        $(this).prop("disabled", true);

        // Clear PDF if user chooses a different PDF to use for a template
        $("#pdf-dropzone").empty();

        // If user attempts selecting prompt for document,
        // clear the canvas
        if($(this).val().length == 0) {
            $("#page_num").empty();
            $("#page_count").empty();
            canvas = document.getElementById("pdf");
            ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            documentId = null;
            $(".add-fields").hide();
            $(".field-options").hide();
            $(this).prop("disabled", false);
            return;
        }

        let baseUrl = "'.$this->createUrl('/x2sign/x2sign/getFile').'";
        $.ajax({
            url: baseUrl,
            type: "GET",
            data: {
                id: $("#MediaModelId").val(), 
                render: 0
            },
            success: function(data) {
                $("#MediaModelId").val(data);
                pdfDoc = null,
                    url = baseUrl + "?id=" + data,
                    pageNum = 1,
                    pageRendering = false,
                    pageNumPending = null,
                    scale = 2,
                    canvas = document.getElementById("pdf"),
                    ctx = canvas.getContext("2d");

                documentId = $("#MediaModelId").val();
                renderPDF(url);

                $(".add-fields").show();
            },
            error: function(xhr) {
                $("#Media_name").prop("disabled", false);
                console.log(xhr);
                alert(xhr.responseText);
            }
        });
    });

    $("#choose-recipient").change(function() {
        $("span.color-preview").css("background-color", $(this).val());
        var color = $(this).val();

        $("#pdf-dropzone > .active").css("background-color", color);
    });
    
    $("#is-required").change(function () {
        var checked = this.checked;
        checked ? $("#pdf-dropzone > .active").attr("req", 1) : $("#pdf-dropzone > .active").attr("req", 0);
    });

    $("#read-only").change(function () {
        var checked = this.checked;
        checked ? $("#pdf-dropzone > .active").attr("read-only", 1) : $("#pdf-dropzone > .active").attr("read-only", 0);
    });



    $("#text-Length").change(function() {
         
        if(!$(this).val()){
            $("#pdf-dropzone > .active").attr("maxlength","");

        }else{
            $("#pdf-dropzone > .active").find("input").attr("maxlength",$(this).val());
        }
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
        $("#pdf-dropzone").children().each(function() {
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
        $("#pdf-dropzone").children().each(function() {
            if($(this).attr("page") != pageNum)
                $(this).hide();
            else
                $(this).show();
        });
    }
    document.getElementById("next").addEventListener("click", onNextPage);


 /**
  * Uses canvas.measureText to compute and return the width of the given text of given font in pixels.
  * 
  * @param {String} text The text to be rendered.
  * @param {String} font The css font descriptor that text is to be rendered with (e.g. "bold 14px verdana").
  * 
  * @see https://stackoverflow.com/questions/118241/calculate-text-width-with-javascript/21015393#21015393
  */
 function getTextWidth(text, font) {
     // re-use canvas object for better performance
     var canvas = getTextWidth.canvas || (getTextWidth.canvas = document.createElement("canvas"));
     var context = canvas.getContext("2d");
    context.font = font;
     var metrics = context.measureText(text);
     return metrics.width;
 }


$(document).ready(function() {  
 // check where the shoppingcart-div is  
 var offset = $("#add-fields").offset();  

 $(window).scroll(function () {  
   var scrollTop = $(window).scrollTop(); // check the visible top of the browser  

        $("#add-fields").css("margin-top",scrollTop);
        
  });  
});  


    /**
     * Asynchronously downloads PDF.
     */
    function renderPDF(url) {
        pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById("page_count").textContent = pdfDoc.numPages;

            // Initial/first page rendering
            renderPage(pageNum);

            // Re-allow user to select a different document once current is loaded
            // (avoids javascript Promise error)
            $("#Media_name").prop("disabled", false);
        });
    }
', CClientScript::POS_END);

Yii::app()->clientScript->registerCss('createX2Sign', '
    .column {
        float: left;
        margin: unset;
    }

    #document {
        background-color: #e4e4e4;
        border: 1px solid black;
        border-radius: 5px;
        width: 850px;
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
        left: 15px;
    }

    #pdf-dropzone {
        z-index: 2;
        width: 816px;
        height: 1056px;
        position: absolute;
        left: 15px;
    }

    .x2-sign-template {
        position: relative;
        height: 1070px;
    }

    .delete-field {
        float: right;
        position: relative;
        left: 19px;
        top: -4px;
    }

    div[id^="Signature"] > .delete-field {
        left: 23px;
        top: -9px;
    }

    div[id^="Date"] > .delete-field {
        left: 23px;
        top: -9px;
    }
    
    div[id^="Signature"] > p {
        display: block;
        margin: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-right: -50%;
        transform: translate(-50%, -50%);
    }

    div[id^="Date"] > i {
        display: block;
        margin: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-right: -50%;
        transform: translate(-50%, -50%);
    }

    .delete-field:hover {
        transform: scale(1.2);
    }

    .draggable > a.delete-field > i {
        color: red;
    }

    .draggable {
        z-index: 3;
        top: 6px;
    }

     input.x2-sign-input:not([id=initials]):not([id=checkbox]) {
        min-width: 9px !important;
        width: 95px;
        position: relative;
        vertical-align: top;
        text-align: left;
        font-family: monospace !important;
        letter-spacing: 0.14px;
        margin: unset !important;
        margin-top: 4px !important;
        margin-left: 4px !important;
   }

    input#text {
        position: relative;
        min-width: 27px !important;
        margin: unset;
    }

    input#checkbox {
        position: absolute;
        top: 9px;
        left: 9px;
        margin: unset;
    }

    input#initials {
        min-width: 1px !important;
        width: 44px;
        position: relative;        
        text-align: left;
        vertical-align: top;
        font-family: monospace;
        letter-spacing: 0.14px;
        margin: unset !important;
        margin-top: 4px !important;
        margin-left: 4px !important;
    }

    #main-column {
        width: 90%;
        margin: auto;
    }

    #recipient-selection-field {
        display: block;
        text-align: center;
        margin-top: 9px;
        margin-bottom: 3px;
    }

    .checkbox {
        display: block;
        text-align: center;
        margin-top: 9px;
        margin-bottom: 3px;
    }

    .color-preview {
        height: 15px;
        width: 15px;
        background-color: rgba(3, 207, 252, 0.5);
        border-radius: 50%;
        display: inline-block;
        border: 0.5px solid black;
        position: relative;
        top: 3px;
    }

    div[id*="Signature"] > .ui-resizable-handle {
        height: 15px;
        width: 15px;
        background-color: #bbbbbb;
        border-radius: 50%;
        display: inline-block;
    }

    div[id*="Signature"] > .ui-resizable-se {
        background-image: url("");
        right: -5px;
        bottom: -5px;
    }

    div[id*="Signature"] > .ui-resizable-se, .ui-resizable-nw {
        cursor: nwse-resize;
    }


    div[id*="Signature"] > .ui-resizable-ne, .ui-resizable-sw {
        cursor: nesw-resize;
    }


    div[id*="Text"] > .ui-resizable-handle {
        height: 15px;
        width: 15px;
        background-color: #bbbbbb;
        border-radius: 50%;
        display: inline-block;
    }


    div[id*="Text"] > .ui-resizable-se {
        background-image: url("");
        right: -5px;
        bottom: -5px;
    }

    div[id*="Text"] > .ui-resizable-se, .ui-resizable-nw {
        cursor: nwse-resize;
    }

    div[id*="Text"] > .ui-resizable-ne, .ui-resizable-sw {
        cursor: nesw-resize;
    }

    div[id*="Email"] > .ui-resizable-handle {
        height: 15px;
        width: 15px;
        background-color: #bbbbbb;
        border-radius: 50%;
        display: inline-block;
    }


    div[id*="Email"] > .ui-resizable-se {
        background-image: url("");
        right: -5px;
        bottom: -5px;
    }

    div[id*="Email"] > .ui-resizable-se, .ui-resizable-nw {
        cursor: nwse-resize;
    }

    div[id*="Email"] > .ui-resizable-ne, .ui-resizable-sw {
        cursor: nesw-resize;
    }

    div[id*="Name"] > .ui-resizable-handle {
        height: 15px;
        width: 15px;
        background-color: #bbbbbb;
        border-radius: 50%;
        display: inline-block;
    }


    div[id*="Name"] > .ui-resizable-se {
        background-image: url("");
        right: -5px;
        bottom: -5px;
    }

    div[id*="Name"] > .ui-resizable-se, .ui-resizable-nw {
        cursor: nwse-resize;
    }

    div[id*="Name"] > .ui-resizable-ne, .ui-resizable-sw {
        cursor: nesw-resize;
    }

    div[id*="Formula"] > .ui-resizable-handle {
        height: 15px;
        width: 15px;
        background-color: #bbbbbb;
        border-radius: 50%;
        display: inline-block;
    }


    div[id*="Formula"] > .ui-resizable-se {
        background-image: url("");
        right: -5px;
        bottom: -5px;
    }

    div[id*="Formula"] > .ui-resizable-se, .ui-resizable-nw {
        cursor: nwse-resize;
    }

    div[id*="Formula"] > .ui-resizable-ne, .ui-resizable-sw {
        cursor: nesw-resize;
    }

    div[id*="Initials"] > .ui-resizable-handle {
        height: 15px;
        width: 15px;
        background-color: #bbbbbb;
        border-radius: 50%;
        display: inline-block;
    }


    div[id*="Initials"] > .ui-resizable-se {
        background-image: url("");
        right: -5px;
        bottom: -5px;
    }

    div[id*="Initials"] > .ui-resizable-se, .ui-resizable-nw {
        cursor: nwse-resize;
    }

    div[id*="Initials"] > .ui-resizable-ne, .ui-resizable-sw {
        cursor: nesw-resize;
    }


    div[id*="Title"] > .ui-resizable-handle {
        height: 15px;
        width: 15px;
        background-color: #bbbbbb;
        border-radius: 50%;
        display: inline-block;
    }


    div[id*="Title"] > .ui-resizable-se {
        background-image: url("");
        right: -5px;
        bottom: -5px;
    }

    div[id*="Title"] > .ui-resizable-se, .ui-resizable-nw {
        cursor: nwse-resize;
    }

    div[id*="Title"] > .ui-resizable-ne, .ui-resizable-sw {
        cursor: nesw-resize;
    }


    #add-field-button {
        color: #01b701;
        cursor: pointer;
    }

    #is-required, #read-only {
        position: relative;
        top: 3px;
        left: 10px;
        height: 10px;
        width: 10px;
    }
');
?>

<div class="form no-border">
	<div class="row">
		<div class="cell">
            <?php
                echo $form->errorSummary($model); 
                echo $form->label($model,'name'); 
                echo $form->textField(
                    $model,'name',
                    array('maxlength'=>100,'id'=>'doc-name')); 
                echo $form->error($model,'Template Name'); 
            ?>
		</div>
        <div class="cell">
            <?php
                echo $form->label($model, 'document');
                echo "<div id='media-autocomplete-container'>"; 
                echo X2Model::renderModelAutocomplete (
                    'Media', false, array (
                        'name' => 'Media[name]'
                    ),
                    null,
                    'https://dedupe.sydney.x2developer.com/index.php/media/getSignDocs'
                );
                echo CHtml::hiddenField ('MediaModelId');
                echo "</div>";
            ?>	
        </div>
		<div class="cell right" id='create-button-container'>
            <?php
 
                echo CHtml::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), 
                    array(
                        'id' => 'create-button',
                        'class' => 'x2-button float'
                    )
                ); 
            ?>
        
        </div>

        <div class="row">

            <div class="cell right">
                <?php
                                      //for if we add it to Libraries
                    $user = User::getMe();
                    if(Groups::userHasRole($user->id, 'Franchise') || Yii::app()->params->isAdmin){
                        echo CHtml::label('Put in office library', '#officeLibrary') .  CHtml::checkbox('officeLibrary', false);
                    }
                    if(Yii::app()->params->isAdmin){
                        $sqlLibraryList = 'SELECT id, name FROM x2_libraries ORDER BY name ASC';
                        $command = Yii::app()->db->createCommand($sqlLibraryList);
                        $libraryList = $command->queryAll();
                        $listopp = array(''=>'');
                        foreach( $libraryList as $key => $value){
                            $listopp[$value['id']] = $value['name'];
                        }
                        echo  CHtml::label('Or select a Library', '#selectLibrary');
                        echo CHtml::dropdownList('select-Library', '',   $listopp , array('id' => 'select-Library'));

                    }
                ?>
            </div>

	</div>
    <div class="row" style="display: flex; flex-direction: row; justify-content: center;">
        <div id="field-bank" class="column" style="display: none;">
            <!-- Draggable Elements -->
            <div id="Signature-draggable" class="draggable resizable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg"></i></a>
                <p>Sign here</p>
            </div>
            <div id="Initials-draggable" class="draggable resizable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg"></i></a>
                <input id="initials" class="x2-sign-input" type="text" minlength="1" maxlength="5" size="5" placeholder="Initials"></input>
            </div>
            <div id="Formula-draggable" class="draggable resizable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg" style="color:red"></i></a>
                <input id="formula" class="x2-sign-input" type="text" minlength="1" size="15" placeholder="Formula"></input>
            </div>
            <div id="Checkbox-draggable" class="draggable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg"></i></a>
                <input id="checkbox" class="x2-sign-input" type="checkbox"></input>
            </div>
            <div id="Text-draggable" class="draggable resizable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg"></i></a>
                <!-- <textarea id="text" class="x2-sign-input" rows="1" style="resize:none;"></textarea> -->
                <input id="text" class="x2-sign-input" type="text" size="15" placeholder="Text"></input>
            </div>
            <div id="Name-draggable" class="draggable resizable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg"></i></a>
                <input id="name" class="x2-sign-input" type="text" minlength="1" size="15" placeholder="Name"></input>
            </div>
            <div id="Email-draggable" class="draggable resizable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg"></i></a>
                <input id="email" class="x2-sign-input" type="email" placeholder="Email"></input>
                <div id="email-wrapper" class="input-wrapper">
                </div>
            </div>
            <div id="Title-draggable" class="draggable resizable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg"></i></a>
                <input id="title" class="x2-sign-input" type="text" minlength="1" placeholder="Title"></input>
                <div id="title-wrapper" class="input-wrapper">
                </div>
            </div>
            <div id="Date-draggable" class="draggable">
                <a class="delete-field" onclick="deleteDraggable(this.parentNode);" href="#"><i class="fas fa-times-circle fa-lg"></i></a>
                <i class="far fa-calendar-alt fa-lg"></i>
            </div>
        </div>
       
        <div id="add-fields" class="add-fields" style="display: none; margin-top:0;">
            <div style="margin-right: 20px;">
            <a id="Signature" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 10px 10px 0px 0px;" onclick="createDraggable(this.getAttribute('id'));"><i class="fas fa-signature"></i> Signature </a>
            <a id="Initials" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 0px;" onclick="createDraggable(this.getAttribute('id'));"><i class="far fa-closed-captioning"></i> Initial</a>
            <a id="Formula" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 0px;" onclick="createDraggable(this.getAttribute('id'));"><i class="fas fa-superscript"></i> Formula</a>
            <a id="Checkbox" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 0px;" onclick="createDraggable(this.getAttribute('id'));"><i class="fas fa-check-circle"></i> Checkbox</a>
            <a id="Text" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 0px;" onclick="createDraggable(this.getAttribute('id'));"><i class="far fa-comment"></i> Text </a>
            <a id="Name" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 0px;" onclick="createDraggable(this.getAttribute('id'));"><i class="far fa-user"></i> Name</a>
            <a id="Email" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 0px;" onclick="createDraggable(this.getAttribute('id'));"><i class="fas fa-at"></i> Email</a>
            <a id="Title" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 0px;" onclick="createDraggable(this.getAttribute('id'));"><i class="fas fa-briefcase"></i> Title</a>
            <a id="Date" class="x2-button x2-sign-field" style="display:block; font-size: Large !important; border-radius: 0px; border-radius: 0px 0px 10px 10px;" onclick="createDraggable(this.getAttribute('id'));"><i class="fas fa-calendar-alt"></i> Date</a>
                <?php
                    echo CHtml::label('Recipient', '#choose-recipient') . CHtml::dropDownList('choose-recipient', 0, array(
                        'rgba(3, 207, 252, 0.5)' => '1',
                        'rgba(252, 207, 3, 0.5)' => '2',
                        'rgba(98, 209, 0, 0.5)' => '3',
                        'rgba(255, 17, 0, 0.5)' => '4',
                        'rgba(0, 218, 196, 0.5)' => '5',
                        'rgba(255, 0, 221, 0.5)' => '6',
                        'rgba(0, 140, 255, 0.5)' => '7',
                        'rgba(132, 0, 255, 0.5)' => '8',
                        'rgba(255, 140, 0, 0.5)' => '9',
                        'rgba(173, 173, 173, 0.5)' => '10',
                    ));
                ?>
                <span class="color-preview"></span>
            </div>
            <div class="field-options" style="display: none;">
                <div class= style="margin-right: 5px;">
                    <?php
                        echo CHtml::label('Required', '#is-required') . CHtml::checkbox('is-required', false);
                    ?>
                </div>
                <div >
                    <?php
                        echo CHtml::label('Read-only', '#read-only') .  CHtml::checkbox('read-only', false);
                    ?>
                </div>
                <div>
                    <?php
                         echo CHtml::label('Max text Length', '#text-Length') . CHtml::numberField("text-Length", "" , array("style" => "width:50Px;"));
                    ?>
                </div>
            </div>

        </div>

 
        <div id="document" class="column">
            <div class="row">
                <div class="x2-button-group" style="text-align: center;">
                    <span id="prev" class="x2-button page-prev">Previous</span>
                    <span id="next" class="x2-button page-next">Next</span>
                    &nbsp; &nbsp;
                    <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
                </div>
            </div>
            <div class="row x2-sign-template">
                <canvas id="pdf" class="x2-sign-doc"></canvas>
                <div id="pdf-dropzone"></div>
            </div>
        </div>
    </div>
</div>

<?php
    $this->endWidget();
?>
