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


// Setup constants to be used throughout create/update/view signable
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
var draggableCounter = 0;
var documentId = null;
var mode = ''; //set to create/view/update by the respective init function

// Create the draggable onto the dropzone
function createDraggable(id) {
    var scrollTop = $(window).scrollTop();
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
        "left": "10px", 
        "top": scrollTop + 10 + "px", 
        "position": "absolute",
        "height": "30px",
        "cursor": "pointer",
        "z-index": "4",
        "outline": "0.2rem ridge #ffff00",
    });

    // Change the fields\' width depending if it\'s not a checkbox, date, or initials
    if(!clone.id.includes("Checkbox") && !clone.id.includes("Signature") && !clone.id.includes("Date"))
       $(clone).css({
            "width": "123px",
            "height": "26px",
            "min-width": "24px",
            "min-height": "24px",
            "max-height": "60px",
        }); 
    else if(clone.id.includes("Signature"))
        $(clone).css({
            "width": "130px",
            "min-height": "24px",
            "min-width": "85px",
            "max-height": "60px",
        });
    else if(clone.id.includes("Checkbox"))
        $(clone).css("width", "30px");   
    else if(clone.id.includes("Date"))
        $(clone).css({
            "height": "20px",
            "width": "80px",
        });
         
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

    $(clone).css("background-color", $("#choose-recipient option:selected").val());
    
    // Add draggable to canvas
    $(clone).appendTo(dropzone);
    //dropzone.appendChild(clone); //this was the original, but I used jQuery to make it look nicer (see above line)

    // Hide field options if the field is a date field
    if($(".field-options").is(":hidden") && !clone.id.includes("Date"))
        $(".field-options").show();
    else if($(".field-options").is(":visible") && clone.id.includes("Date"))
        $(".field-options").hide();

    $(clone).draggable({
        addClasses: false,
        scroll: true,
        cancel: ".delete-field, input, textarea",
        containment: "parent",
        cursor: "pointer",
        //appendTo: "div"
    });

    if($(clone).attr("id").includes("Signature")) {
        $(clone).resizable({
            handles: "ne, se, sw, nw",
            cancel: "a, i",
            containment: "parent",
        });
    } else if(!$(clone).attr("id").includes("Date") && !$(clone).attr("id").includes("Checkbox")) {
        $(clone).resizable({
            handles: "ne, se, sw, nw",
            cancel: "a, i",
            containment: "parent",
            resize: function(event, ui) {
                $(clone).find("input").width($(clone).width() - 20 + "px"); //($(clone).width() - 5) - 30
                $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
            }
        });

        $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
    }


    /*if($(clone).attr("id").includes("Initials")){
        $(clone).resizable({
            handles: "ne, se, sw, nw",
            cancel: "a, i",
            containment: "parent",
            resize: function(event, ui) {
                $(clone).find("input").width((($(clone).width() - 5)) - 35 + "px");
                $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
            }
        });
         
        $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
     }*/ 
}

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

// Show fields for view or update page
function showFields(fields) {
    $.each(fields, function(index, value) {
        var fieldType = value["id"].substring(0, value["id"].indexOf("-"));
        if(mode == "update" && (Number(value["id"].substring(value["id"].indexOf("-") + 1)) > Number(draggableCounter)))
            draggableCounter = value["id"].substring(value["id"].indexOf("-") + 1);
        var original = document.getElementById(fieldType + "-draggable");

        // Create clone element
        var clone = $("#" + fieldType + "-draggable").clone().attr({
            "id": value["id"],
            "page": value["page"],
            "req": value["req"],
            "read-only": value["read-only"],
        }).css({
            "top": parseInt(value["top"].substring(0, value["top"].indexOf("p"))) + "px",
            "left": parseInt(value["left"].substring(0, value["left"].indexOf("p"))) + "px",
            "height": value["height"],
            "width": value["width"],
            "position": "absolute",
            "background-color": recipientColors[value["recip"]],
            "cursor": "pointer",
        });//.show().appendTo("#pdf-dropzone");
        
        $(clone).find("input, textarea").css("width", $(clone).width() - 20).val(value["value"]);
        $(clone).find("input, textarea").height($(clone).height() - 18 + "px");
        
        // Change the fields\' width depending if it\'s not a checkbox, date, or initials
        /*if($(clone).attr("id").indexOf("Checkbox") == -1 && $(clone).attr("id").indexOf("Signature") == -1 && $(clone).attr("id").indexOf("Date") == -1)
           $(clone).css({                                                                                 
                "width": "123px",                                                                         
                "height": "26px",                                                                         
                "min-width": "24px",                                                                      
                "min-height": "24px",                                                                     
                "max-height": "60px",                                                                     
            });                                                                                           
        else if($(clone).attr("id").indexOf("Signature") !== -1)//clone.id.includes("Signature"))                                                           
            $(clone).css({                                                                                
                "width": "130px",                                                                         
                "min-height": "24px",                                                                     
                "min-width": "85px",                                                                      
                "max-height": "60px",                                                                     
            });                                                                                           
        else if($(clone).attr("id").indexOf("Checkbox") !== -1)//clone.id.includes("Checkbox"))                                                            
            $(clone).css("width", "30px");                                                                
        else if($(clone).attr("id").indexOf("Date") !== -1)                                                                
            $(clone).css({                                                                                
                "height": "20px",                                                                         
                "width": "80px",                                                                          
            });*/ 
       
        // Append the field to the canvas 
        $(clone).appendTo(dropzone);
        
        // Check if we have the text limit value
        if(jQuery.inArray("maxlength", value))
            clone.find("input").attr("maxlength", value["maxlength"]);
        
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

        if (mode == "update") {
            $(clone).draggable({
                addClasses: false,
                scroll: true,
                cancel: ".delete-field, input, textarea",
                containment: "parent",
                cursor: "pointer",
                //appendTo: "div"
            });

           if($(clone).attr("id").includes("Signature")) {
                $(clone).resizable({
                    handles: "ne, se, sw, nw",
                    cancel: "a, i",
                    containment: "parent",
                });
            } else if(!$(clone).attr("id").includes("Date") && !$(clone).attr("id").includes("Checkbox")) {
                $(clone).resizable({
                    handles: "ne, se, sw, nw",
                    cancel: "a, i",
                    containment: "parent",
                    resize: function(event, ui) {
                        $(clone).find("input").width($(clone).width() - 20 + "px"); //($(clone).width() - 5) - 30
                        $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
                    }
                });

                $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
            } 
        } else if (mode == "view") {
            $(".recipients").find("span").each(function () {
                $(this).css("background-color", recipientColors[$(this).attr("id")]);
                $(this).parent().css("margin-left", "7px");
            });
        }
    });
}

function editInit (modelId, signDocId) {
    // Remove active class if user clicks on dropzone, not draggable
    $(dropzone).on("drag click mousedown", function(e) {
        if(e.target === this) {
            $("#pdf-dropzone > .active").removeClass("active").css({
                "z-index": "3",
                "outline": "",
            });
            $(".field-options").hide();
        }
    });

    $("#add-field-button").click(function () {
        createDraggable($('#choose-field option:selected').text());
    });

    // Change what draggable currently has the active
    // class for determining what recipient the field is
    // assigned to
    $(dropzone).on("drag click mousedown", ".draggable", function(e) {
        // Don't allow event to trigger if user is clicking on the delete button
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
        
        if($(this).find("input").attr("maxlength") > 0)
            $("#text-Length").val($(this).find("input").attr("maxlength"));
        else
            $("#text-Length").val("");
        
        $("span.color-preview").css("background-color", $(this).css("background-color"));

        $("#choose-recipient").val($(this).css("background-color"));
    });

    $(document).on("input", ".x2-sign-input", function() {
       // Field is not text field       
        var textLe =  getTextWidth($(this).val() + "test", $(this).css("font-size") + " " +  $(this).css("font-family") );
        var singleText =  getTextWidth("t", $(this).css("font-size") + " "  +  $(this).css("font-family") );
        var whiteLength = (singleText+2) * $(this).val().length / 2; 
        if(textLe + (singleText * 2) + 4 + whiteLength + 35 > $(this).parent().width()) { 
            var ratio = (($(this).parent().height() * .8) / 12);
            $(this).width(textLe - singleText - 5 +  "px"); 
            $(this).parent().width(textLe - singleText + 20 + "px");
            //$(this).width(((this.value.length + 1) * 7.3) * ratio + "px"); 
            //$(this).parent().width(((this.value.length + 1) * 7.3) * ratio + 35 + "px");
        } else if(textLe + 36 < $(this).parent().width() - (singleText * 2)) {
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
            if(document.getElementById("officeLibrary") != null)
                checkValue = document.getElementById("officeLibrary").checked;
 
            var actionUrl = yii.scriptUrl + '/docs/createSignable';
            var params = { 
                name: $("#doc-name").val(),
                fieldInfo: draggables,
                file: documentId,
                officeLibrary: checkValue,
                selectLibrary: $("#select-Library").val(),
                copyDoc: $("#copy-doc").val(),
            };

            if (modelId && signDocId) {
                actionUrl = yii.scriptUrl + '/docs/updateSignable/' + modelId;
                params.signDocId = signDocId;
            }
            
            $.ajax({
                url: actionUrl,
                type: "POST",
                data: params,
                success: function(data) {
                    data = JSON.parse(data);
                    let redirectUrl = yii.scriptUrl + "/docs/viewSignable?" + $.param({id: data["id"]});
                    window.location.replace(redirectUrl);
                }
            });
        }
    });

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
    
        let baseUrl = yii.scriptUrl + "/x2sign/getFile";
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
        if(!$(this).val())
            $("#pdf-dropzone > .active").attr("maxlength", "");
        else
            $("#pdf-dropzone > .active").find("input").attr("maxlength", $(this).val());
    });
    
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
            $("#add-fields").css("margin-top", scrollTop);
        });  
    });    
}

/**
 * Add functionality to the "Next" and "Prev" buttons for the PDF
 */
function navInit () {
    document.getElementById("prev").addEventListener("click", onPrevPage);
    document.getElementById("next").addEventListener("click", onNextPage);
}

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
        if (mode == "create" || mode == "update")
            $("#Media_name").prop("disabled", false);
    });
}

function loadPdf (mediaId, fieldInfo) {
    pdfDoc = null,
        url = yii.scriptUrl + '/x2sign/x2sign/getFile/id/' + mediaId,
        pageNum = 1,
        pageRendering = false,
        pageNumPending = null,
        scale = 2,
        canvas = document.getElementById("pdf"),
        ctx = canvas.getContext("2d");
    renderPDF(url);
    documentId = mediaId;
    $(".add-fields").show();
    
    if(fieldInfo !== undefined && fieldInfo !== null && fieldInfo.length !== 0)
        showFields(fieldInfo);
}

function createSignable () {
    mode = 'create';
    editInit();
    navInit();
}

function updateSignable (modelId, signDocId, mediaId, pdfName, fieldInfo) {
    mode = 'update';
    editInit(modelId, signDocId);
    navInit();
    $("#Media_name").val(pdfName);
    loadPdf(mediaId, fieldInfo);
}

function viewSignable (mediaId, fieldInfo) {
    mode = 'view';
    navInit();
    loadPdf(mediaId, fieldInfo);
}
