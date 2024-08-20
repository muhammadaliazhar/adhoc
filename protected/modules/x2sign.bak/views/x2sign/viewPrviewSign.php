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

$this->setPageTitle(CHtml::encode("Preview"));
$title = Yii::t('docs', 'X2SignDoc:');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/pdfReader/build/pdf.js');

//$authParams = array('X2Model' => $model);
$menuOptions = array(
  'index', 'create', 'createEmail', 'createQuote', 'createSignable',
);

//if (!$model->isNewRecord) {
//    $existingRecordMenuOptions = array(
//        'viewSignable', 'editSignable', 'permissions', 'delete',
//    );
//  $menuOptions = array_merge($menuOptions, $existingRecordMenuOptions);
//}
//$this->insertMenu($menuOptions, $model, $authParams);
//$this->renderPartial('_docPageHeader', compact('title', 'model'));
// $layoutManager = $this->widget('RecordViewLayoutManager', array('staticLayout' => false));

if(!$mediaId)
    $mediaId = $_GET["mediaId"];

Yii::app()->clientScript->registerScript('viewX2Sign', '
    const parentOffset = $("#pdf-dropzone").offset();
    const dropzone = $("#pdf-dropzone")[0];
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

    pdfDoc = null,
        url = "' . $this->createUrl('/media/media/getFile/id') . '/" + "' . $mediaId . '",
        pageNum = 1,
        pageRendering = false,
        pageNumPending = null,
        scale = 2,
        canvas = document.getElementById("pdf"),
        ctx = canvas.getContext("2d");

    renderPDF(url);


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
     * Asynchronously downloads PDF.
     */
    function renderPDF(url) {
        pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById("page_count").textContent = pdfDoc.numPages;

            // Initial/first page rendering
            renderPage(pageNum);
        });
    }

    function showFields(fields) {
        $.each(fields, function(index, value) {
            var fieldType = value["id"].substring(0, value["id"].indexOf("-"));
            var original = document.getElementById(fieldType + "-draggable");

            // Create clone element
            var clone = $("#" + fieldType + "-draggable").clone().attr({
                "id": value["id"],
                "page": value["page"],
            }).css({
                "top": parseInt(value["top"].substring(0, value["top"].indexOf("p"))) + "px",
                "left": parseInt(value["left"].substring(0, value["left"].indexOf("p"))) + "px",
                "height": value["height"],
                "width": value["width"],
                "position": "absolute",
                "background-color": recipientColors[value["recip"]]
            }).show().appendTo("#pdf-dropzone");
            
            // Size the input field proportionally to the wrapper div
            if($(clone).children("input").attr("id") !== "checkbox")
                $(clone).children("input").css("width", value["width"] - 20).val(value["value"]);

            // Size the wrapper div to the draggable div
            $(clone).children("div").css("width", value["width"]);

            // Hide or show the draggables depending on the page
            $("#pdf-dropzone").children().each(function() {
                if($(this).attr("page") != pageNum)
                    $(this).hide();
                else
                    $(this).show();
            });
        });

        $(".recipients").find("span").each(function () {
            $(this).css("background-color", recipientColors[$(this).attr("id")]);
            $(this).parent().css("margin-left", "7px");
        });
    }

    showFields(' . $fieldsInfo . ');
', CClientScript::POS_END);

Yii::app()->clientScript->registerCss('viewX2Sign', '
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
        left: 16px;
    }

    #pdf-dropzone {
        z-index: 2;
        width: 816px;
        height: 1056px;
        position: absolute;
        left: 16px;
    }

    .x2-sign-template {
        position: relative;
        height: 1070px;
        overflow: scroll;
    }

    .draggable {
        top: 6px;
    }

    .input-wrapper:not([id=initial-wrapper]) {
        background-color: #03cffc;
        height: 30px;
        position: relative;
        width: 115px;
        z-index: -1 !important;
        top: -20px;
        left: 0px;
        opacity: 50%;
    }

    input.x2-sign-input:not([id=initials]) {
        min-width: 95px !important;
        width: 95px;
        position: relative;
        top: 5px;
        left: 7px;
        text-align: left;
        font-family: monospace !important;
        font-size: 12px !important;
        letter-spacing: 0.14px;
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
        top: 9px;
        left: 6px;
    }

    input#initials {
        min-width: 44px !important;
        width: 44px;
        position: relative;
        top: 6px;
        left: 5px;
        text-align: left;
        font-family: monospace;
        font-size: 12px;
        letter-spacing: 0.14px;
        margin: unset;
    }

    #assign-fields {
        width: 150px;
        border-radius: 5px;
        float: right;
        border-radius: 5px;
        border: 1px solid black;
    }

    div[id^="Signature"] > i {
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

    .color-preview {
        height: 25px;
        width: 25px;
        background-color: rgba(3, 207, 252, 0.5);
        border-radius: 50%;
        display: inline-block;
        position: absolute;
        margin-left: 7px;
    }
');

/**
 * Header
 */
$title = Yii::t('docs', 'Document:');
?>

<div id="assign-fields" class="column" style="font-size: 1.2rem;">
    <h3 style="margin: 0px 0px 0px !important; text-align: center;">Recipients</h3>
    <hr>
    <div class="recipients">
        <?php
            for($i = 1; $i <= 10; $i++) {
                if(isset($names[$i - 1])){
                    echo "<p> " . $names[$i - 1];   
                }else{
                    echo "<p> Recipient " . $i;
                }
                echo "<span id=\"" . $i . "\" class=\"color-preview\"></span></p>";
            }
        ?>
    </div>
</div>

<div class="row" style="display: flex; flex-direction: row; justify-content: center;">
    <div id="field-bank" class="column" style="display: none;">
        <div id="Signature-draggable" class="draggable resizable">
            <i class="fas fa-signature fa-2x"></i>
        </div>
        <div id="Initials-draggable" class="draggable">
            <input id="initials" class="x2-sign-input" type="text" minlength="1" maxlength="8" size="5" placeholder="Initials" readonly></input>
        </div>
        <div id="Formula-draggable" class="draggable">
            <input id="formula" class="x2-sign-input" type="text" minlength="1" size="15" placeholder="Formula" readonly></input>
        </div>
        <div id="Checkbox-draggable" class="draggable">
            <input id="checkbox" class="x2-sign-input" type="checkbox" disabled></input>
        </div>
        <div id="Text-draggable" class="draggable">
            <input id="text" class="x2-sign-input" type="text" size="15" placeholder="Text" readonly></input>
        </div>
        <div id="Name-draggable" class="draggable">
            <input id="name" class="x2-sign-input" type="text" minlength="1" size="15" placeholder="Name" readonly></input>
        </div>
        <div id="Email-draggable" class="draggable">
            <input id="email" class="x2-sign-input" type="email" placeholder="Email" readonly></input>
        </div>
        <div id="Title-draggable" class="draggable">
            <input id="title" class="x2-sign-input" type="text" minlength="1" placeholder="Title" readonly></input>
        </div>
        <div id="Date-draggable" class="draggable resizable">
            <i class="far fa-calendar-alt fa-lg"></i>
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

<center>

<?php echo '<h3><a class="x2-button" href="' . $nextDocUrl . '">Next Document</a></h3>'  ?>
</center>



