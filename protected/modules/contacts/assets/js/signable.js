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
    $(clone).find('.delete-field').click(function () {
        deleteDraggable(this.parentNode);
    });

    // Change the fields' height depending if it's not a checkbox or initials
    if(!clone.id.includes("Checkbox") && !clone.id.includes("Initials") && !clone.id.includes("Signature")) {
        $(clone).css("width", "115px");
    } else if(!clone.id.includes("Checkbox")) {
        $(clone).css("width", "80px");
    } else {
        $(clone).css("width", "30px");
    }
    
    // Give the newest draggable the active class and remove it
    // from the current active draggable
    $("#pdf-dropzone > .active").removeClass("active").css({
        "z-index": "3",
        "outline": "",
    });

    $(clone).addClass("active");
    $(clone).attr("req", 0);
    $(clone).attr("read-only", 0);

    $("#is-required").prop("checked", false);
    $("#read-only").prop("checked", false);

    dropzone.appendChild(clone);
    $(".assign-field-name").text($("a#" + id).text());

    if($("#is-required").is(":hidden")) {
        $(".field-options").show();
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

    if(clone.id.includes("Signature"))
        $(clone).resizable({
            handles: "ne, se, sw, nw",
            cancel: "a, i",
            containment: "parent",
            minHeight: 30,
            maxHeight: 80,
            minWidth: 90,
            maxWidth: 150,
        });
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

function showFields(fields) {
    $.each(fields, function(index, value) {
        var fieldType = value["id"].substring(0, value["id"].indexOf("-"));
        if(mode == "update" && value["id"].substring(value["id"].indexOf("-") + 1) > draggableCounter)
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

        if (mode == "update") {
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
                    minHeight: 30,
                    maxHeight: 80,
                    minWidth: 90,
                    maxWidth: 150,
                });
        } else if (mode == "view") {
            $(".recipients").find("span").each(function () {
                $(this).css("background-color", recipientColors[$(this).attr("id")]);
                $(this).parent().css("margin-left", "7px");
            });
        }
    });
}

function editInit (modelId, signDocId) {
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

    $(dropzone).on("drag click mousedown", ".draggable", function(e) {
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
    
        $(this)
            .addClass("active")
            .css({
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
    
        var fieldName = $(this).attr("id").substr(0, $(this).attr("id").indexOf("-"));
        $(".assign-field-name").text(fieldName);
    
        $("span.color-preview").css("background-color", $(this).css("background-color"));
    
        $("#choose-recipient").val($(this).css("background-color"));
    
        if($(".field-options").is(":hidden")) {
            $(".field-options").show();
        }
    });

    $(document).on("input", ".x2-sign-input", function() {
        // Grow the text field size if the user inputs more text than minimum input size         
        if($(this).val().length > 13) {
            var temp = this.value.length * 8;
            $(this).width(((this.value.length + 1) * 7.3) + "px"); 
            $(this).parent().width(((this.value.length + 1) * 7.3) + 20 + "px");
        } else if($(this).val().length < 13 && $(this).width() > 95) {
            if($(this).attr("id") !== "checkbox" && $(this).attr("id") !== "initials") {
                $(this).width("95px");
                $(this).parent().width("115px");
            }
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
                });
            });
            JSON.stringify(draggables);
    
            var actionUrl = yii.scriptUrl + '/docs/createSignable';
            var params = {
                name: $("#doc-name").val(),
                fieldInfo: draggables,
                file: documentId
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
}

function navInit () {
    document.getElementById("prev").addEventListener("click", onPrevPage);
    document.getElementById("next").addEventListener("click", onNextPage);
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