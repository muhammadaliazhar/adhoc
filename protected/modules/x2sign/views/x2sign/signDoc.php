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

$allura = Yii::app()->controller->module->assetsUrl.'/main/fonts/Allura/Allura-Regular.otf';
$otto = Yii::app()->controller->module->assetsUrl.'/main/fonts/Otto/Otto.ttf';
$may = Yii::app()->controller->module->assetsUrl.'/main/fonts/may-queen/mayqueen.ttf';

$signature = X2Signature::model()->findByAttributes(array(
    'modelId' => $model->id,
    'modelType' => $modelType,
    'initial' => 0,
));

$initial = X2Signature::model()->findByAttributes(array(
    'modelId' => $model->id,
    'modelType' => $modelType,
    'initial' => 1,
));

//if only a signature and no initial create one

if(isset($signature) && !isset($initial)){
            //Initials image creation
            $initial = new X2Signature;
            $fullName = explode(" ", $signature->signature);
            $iName = '';
            foreach($fullName as $partName){
                if(!empty($partName[0]))
                    $iName .= ucfirst($partName[0]);
            }
            //if($signature->id == "92231") printR("-" . $iName ."-" ,1);
            //for cases of strange names
            if(empty($iName) || !ctype_alpha($iName))$iName = $signature->signature;
            $initial->createImage($iName, $signature->modelType, $signature->modelId, 1); //create image with input name
            $initial->setAttributes(array(
                'modelId' => $signature->modelId,
                'modelType' => $signature->modelType,
                'signature' => $iName,
                'emailAddress' => $signature->emailAddress,
                'IP' => $signature->IP,
                'initial' => 1,
            ), false);
            $initial->save();

}

if(isset($signature) && isset($initial)){
    $signatureExists = 1;
    $sigImage = $signature->getSignatureImage();
    $initialImage = $initial->getSignatureImage();
} else {
    $signatureExists = 0;
    $sigImage = '';
    $initialImage = '';
}

Yii::app()->clientScript->registerScript('retrieveDocs', '
    const dropzone = $("#document")[0];
    var currentDocId =  ' . $signDocId . ';
    var sigImage = "' . $sigImage . '";
    var initialImage = "' . $initialImage . '";
    var signatureId = "' . (isset($signature->id) ? $signature->id : "") . '";
    var initialId = "' . (isset($initial->id) ? $initial->id : "") . '";
    var fields = ' . $fields . ';
    var signReq = ' . $signReq . ';
    var focusFields = [];

    if (x2.x2sign === undefined) x2.x2sign = {};
    x2.x2sign.fields = fields;

    function replaceSignature(element) {
        var clone = $(element).attr("id").includes("Signature") ? $(sigImage).clone() : $(initialImage).clone();
        $(element).replaceWith(clone);

        clone.attr({
            "id": $(element).attr("id"),
            "class": "sign-field",
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



    // Render the fields as needed for the recipient
    function renderFields() {
        const template = $("template").prop("content");
        console.log(fields);
        if(fields.length > 0) {
            for(i = 0; i < fields.length; i++) {
                var field = fields[i];
                if(!field["id"].includes("Date")) {
                    var original = $(template).children("#" + field["id"].substring(0, field["id"].indexOf("-")));
                    var clone = $(original).clone().attr({
                        "id": field["id"],
                        "page": field["page"],
                        "req": field["req"],
                        "read-only": field["read-only"],
                        "recip": field["recip"],
                    }).css({
                        "top": field["top"],//parseInt(field["top"]) - parseInt($("#page-" + field["page"]).offset().top),
                        "left": field["left"],
                        "height": field["height"],
                        "width": field["width"],
                        "position": "absolute",
                        "background-color": field["recip"] == ' . $position . ' && field["req"] == 1 ? "rgba(255, 235, 0, 0.75)" : "rgba(255, 255, 255, 0)",
                    }).appendTo($("#page-" + field["page"]));
                    if(field["recip"] <= ' . $position . ') $(clone).show();                  
                    if(field["req"] == 1 && field["recip"] == ' . $position . ')
                        focusFields.push(field["id"]);         
 
                    // If the cloned element is a signature field, and it\'s not the current recipients\',
                    // then replace with their signature image to show other recipients that it\'s been signed
                    if((field["id"].includes("Signature") || field["id"].includes("Initials")) && field["recip"] != ' . $position . ') {
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
                            "z-index": 3,
                        });
                    }
                    //check if already signed
                    if(field["id"].includes("Signature") && field["recip"] == ' . $position . '  && (field["value"] !== null && field["value"] !== "") && ("value" in field)) {
                        let sigImg = $(field["value"]).clone();
                        $(clone).replaceWith(sigImg);
                         
                        sigImg.attr({
                            "id": $(clone).attr("id"),
                            "page": $(clone).attr("page"),
                            "req": 1,
                            "read-only": 0,
                            "recip": $(clone).attr("recip"),
                        }).css({
                            "top": $(clone).css("top"),
                            "left": $(clone).css("left"),
                            "width": $(clone).css("width"),
                            "height": $(clone).css("height"),
                            "position": "absolute",
                        }).addClass("sign-field");

                    }


                     //if dropdown set value
                     if(field["id"].includes("YesNo")){
                        $(clone).find("select").val(field["value"]);
                        //also disable
                        if(field["read-only"] == 1) $(clone).find("select").attr("disabled", true); 
                     }

                     //if dropdown set value
                     if(field["id"].includes("Dropdown")){
                        var selectedOptionId = field["selectedOptionId"];
                        var ajaxUrl = yii.baseUrl + "/index.php/x2sign/GetSelectedDropdown?id=" + selectedOptionId;
                        var preSelectValue = field["value"];
                        var readOnly = field["read-only"];
                        $.ajax({
                            type: "GET",
                            url: ajaxUrl,
                            dataType: "json",
                            async: false,
                            context: { selected_dropdown : field.id },
                            success: function (data) {
                                var optionsData = JSON.parse(data.options);
                                var dropdownElement = $("#" + this.selected_dropdown).find("#dropdown");
                                dropdownElement.empty();
                                $.each(optionsData, function (key, value) {
                                    var option = $("<option>", {
                                        value: key,
                                        text: value
                                    });
                                    console.log(value);
                                    if(preSelectValue == value)
                                        option = $("<option>", {
                                            value: key,
                                            text: value,
                                            selected: true, 
                                        });

                                    dropdownElement.append(option);
                                    //if (selectedValue !== key && dropdownElement.find("option:selected").length === 0) {
                                    //    option.prop("selected", true);
                                    //}
                                });
                                if(readOnly == 1) dropdownElement.attr("disabled", true);
                                //set the value to the drop down to the value
                                //$("#select option[value=" + field["value"] +"]").attr("selected", "selected");
                                 
                            },
                        });
                     }

 
                    //set font size
                    if(!field["id"].includes("FileUpload")) $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
                    //$(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
                    $(clone).find("select").css({"font-size": ($(clone).height() * .8) - 10}); 

                    if($(clone).attr("read-only") == 1)
                        $(clone).children("input").prop("disabled", true);

                    // Find any fields that had insertable attributes and
                    // change their size
                    var input = $(clone).find("input, textarea");
                    if(input.length > 0 && $(input).attr("id") !== "checkbox") {
                        // Size the input field proportionally to the wrapper div
                        if($(input).attr("id") !== "checkbox") {
                            $(clone).find("input, textarea").css({
                                "width": field["width"] - 20,
                            }).val(field["value"]);

                            $(clone).find("textarea").css("height", parseInt(field["height"]) - 15 + "px");
                        }
                        var textLe =  getTextWidth($(clone).find("input").val() + "12", $(clone).find("input").css("font-size") + " " +  $(clone).find("input").css("font-family"));
                        var singleText =  getTextWidth("t", $(clone).find("input").css("font-size") + " "  +  $(clone).find("input").css("font-family") );
                        var whiteLength = (singleText + 2) * $(clone).find("input").val().length / 2;
                        var FontSize = $(clone).find("input").css("font-size"); 
                        if(textLe + (singleText * 2) + 4 + whiteLength > $(clone).width() && $(clone).find("input").val().length > 0){ 
                            $(clone).find("input").width(textLe - singleText  +  "px"); 
                            $(clone).width(textLe + singleText  + 3 + "px");
                        } else if(textLe < $(clone).width() - (singleText * 2) && $(clone).find("input").val().length > 0) {
                            $(clone).find("input").width(textLe - singleText  +  "px"); 
                            $(clone).width(textLe - singleText  +  "px");
                        }
                    }

                    if(field["value"] == 1 && !($(input).attr("id") !== "checkbox"))$(clone).find("input").prop("checked", true);
                    //this will check for radtio
                    if(field["value"] == 1 && !($(input).attr("id") !== "radio"))$(clone).find("input").prop("checked", true);

                    //function to ensure only one radio option active at a time
                    if(field["partner"]){
                        $(clone).attr("partner", field["partner"]);
                        $(clone).find("input").change(function() {
                            
                             var pids = JSON.parse($(this).parent().attr("partner"));
                             
                             if($(this).prop("checked")){
                                pids.forEach((element) => {
                                    $("#Radio-" + element).find("input").prop("checked", false);
                                });
                             }
                         });
                    }


                } else if(field["id"].includes("Signature") && field["recip"] != ' . $position . ') {
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
                    }).appendTo(dropzone);


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
                } else {
                    let date;
                    let dateElem = $("<p></p>").attr({
                        "id": field["id"],
                        "class": "sign-field",
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
                    $("#page-" + field["page"]).append(dateElem);
                }
            }
        } else {
        }

        // Hide or show the fields depending on the page
        /*$(dropzone).children().each(function() {
            if($(this).attr("page") != pageNum)
                $(this).hide();
            else
                $(this).show();
        });*/
    }

    $(".create-signature-button").click(function () {
        if($("input#signature-first-name").val().length == 0)
            $("input#signature-first-name").addClass("error");
        else
            $("input#signature-first-name").removeClass("error");
        
        if($("input#signature-last-name").val().length == 0)
            $("input#signature-last-name").addClass("error");
        else
            $("input#signature-last-name").removeClass("error");
        
        if($("input#signature-first-name").hasClass("error") || $("input#signature-last-name").hasClass("error"))
            return;
        let signature = $("#signature-first-name").val().trim() + " " 
            + ($("#signature-middle-initial").val().trim() === "" ? "" : $("#signature-middle-initial").val().trim() + " ")
            + $("#signature-last-name").val().trim(); 
        $.ajax({
            url: "' . $this->createUrl('/x2sign/x2sign/createSignature') . '",
            type: "POST",
            data: {
                signature: signature,
                font: $("#signature-font div input:checked").val(),
                modelId: ' . $model->id . ',
                modelType: "' . $modelType . '",
                key: "' .$key. '",
            },
            success: function(data) {
                location.reload();
                $("#create-signature-modal").hide();
                data = JSON.parse(data);
                signatureId = data[0];
                sigImage = $(data[1]);
                initialId = data[2];
                initialImage = $(data[3]);
            }
        });
    });

    $(document).on("input", ".x2-sign-input", function() {
        // Adjust input width depending on amount of text    
        if(!["checkbox","radio","yesNo", "dropdown"].includes($(this).attr("id"))){
            var textLe =  getTextWidth($(this).val() + "test", $(this).css("font-size") + " " +  $(this).css("font-family") );
            var singleText =  getTextWidth("t", $(this).css("font-size") + " "  +  $(this).css("font-family") );
            console.log($(this).css("font-size"));
            var whiteLength = (singleText+2) * $(this).val().length / 2;
            var FontSize = $(this).css("font-size"); 
            console.log($(this).parent().width());   
            $(this).width(textLe - singleText - 5 + "px"); 
            $(this).parent().width(textLe - singleText + 17 + "px");
        }

        /*if(($(this).parent().attr("id").indexOf("Text") == -1 && $(this).val().length > 13) || 
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
        }*/
    });

    // Sign Document
    $("#sign-button").click(function () {
        var signFields = [];
        var uploadFail = 0;
        var failChangeCheck = 0;
        var uploadFields = [];
        var fail = 0;
        $(dropzone).find(".sign-field").each(function () {
            // Ensure this field is the current recipients
            if($(this).attr("recip") == ' . $position . ') {
                if(!$(this).attr("id").includes("Signature") && !$(this).attr("id").includes("Initials")) {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).find("input, textarea, select").val().length > 0)) {
                        let fieldVal = "";
                        if($(this).attr("id").includes("Date"))
                            fieldVal = $(this).val();
                        else if($(this).attr("id").includes("Checkbox"))
                            if($(this).find("input").is(":checked"))
                                fieldVal = 1;
                            else
                                fieldVal = 0;
                        else if($(this).attr("id").includes("Radio")){
                            if($(this).find("input").is(":checked"))
                                fieldVal = 1;
                            else
                                fieldVal = 0;
                            //now check that the partner is filled or not filled
                            var oneCheck = false;
                            var pids = JSON.parse($(this).attr("partner"));
                            pids.forEach((element) => {
                                if($(this).find("input").is(":checked") != $("#Radio-" + element).find("input").is(":checked")){
                                    $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                                    oneCheck = true;
                                }
                            });
                            if(!oneCheck){
                                $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                                fail = 1;
                            }
                        }else if($(this).attr("id").includes("YesNo")) {
                            if($(this).find("select").val() == ""){
                                $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                                fail = 1;
                            }
                        }else if($(this).attr("id").includes("Dropdown")) {
                            if($(this).find("select").val() == ""){
                                $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                                fail = 1;
                            } 
 

                        }else if($(this).attr("id").includes("Email")){
                                //used this https://www.w3resource.com/javascript/form/email-validation.php
                            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test($(this).find("input, textarea").val())){
                                //real email
                                fieldVal = $(this).find("input, textarea").val()
                            }else{
                                //noe valid email
                                $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                                fail = 1;
                                alert("Non Valid Email Used");
                            }

                        }else
                            fieldVal = $(this).find("input, textarea").val();


                        //if file upload set value
                        if($(this).attr("id").includes("FileUpload")){
                           var formData = new FormData();
                           var files = $(this).find("#fileupload")[0].files;
                           var pdfFiles = [];
                            
                           for (var i = 0; i < files.length; i++) {
                                formData.append("pdfFiles[]", files[i]);

                           }
                            var csrfTokenRegex = /(?:^|.*;)\s*YII_CSRF_TOKEN\s*=\s*([^;]*)(?:.*$|$)/;
                            var csrfToken = document.cookie.replace (csrfTokenRegex, "$1");
                            var visibility = "public";
                            var assignedTo = "";
                            formData.append("YII_CSRF_TOKEN", csrfToken );
                            
                            for(i = 0; i < fields.length; i++) {
                                var field = fields[i];
                                if(field["id"] == this.id) {
                                    if(field.visibility == "Private") {
                                        visibility = "1";
                                    } else if(field.visibility == "Public") {
                                        visibility = "0";
                                    }
                                    assignedTo = field.autocompleteValue;
                                }
                            }
                            uploadFields.push({
                                "envelopeId": ' . $envelopeId . ',
                                "visibility": visibility,
                                "assignedTo": assignedTo,
                                "data": formData
                            });
    
                        } else {

                            //if dropdown set value
                            if($(this).attr("id").includes("YesNo")){
                               fieldVal = $(this).find("select").val();
                            }
                                //if dropdown set value
                            if($(this).attr("id").includes("Dropdown")){
                                fieldVal = $(this).find("select").val();
                                }        
                        
                            signFields.push({
                                "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                                "fieldId": $(this).attr("id"),
                                "value": fieldVal,
                            });
                        }
                    } else {
                        $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                        fail = 1;
                    }
                } else {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).is("img"))) {
                        signFields.push({
                            "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                            "fieldId": $(this).attr("id"),
                            "value": $(this).is("img") ? ($(this).attr("id").includes("Signature") ? signatureId : initialId) : -1,
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
        
        $("#sign-button-container").hide();
        if(uploadFields) {
            uploadFields.forEach((uploadField) => { 
                 $.ajax({
                    url: "' . $this->createUrl('/x2sign/X2SignDocUpload?envelopeId=" + uploadField.envelopeId +"&visibility=" + uploadField.visibility + "&assignedTo=" + uploadField.assignedTo') . ',
                    type: "POST",
                    data: uploadField.data,
                    contentType: false,
                    processData: false,
                    async: false,
                    success: function(response) {
                        var responseData = JSON.parse(response);
                        if (responseData.errorFiles) {
                            alert("Error while uploading the files");
                        }
                    },
                    error: function() {
                        alert("Error while uploading the files");
                    }
                });
            })
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
                envelopeId: ' . $envelopeId . ',
                cancel: 0,
                key: "' .$key. '",
            },
            success: function(data) {
                if(data == "complete" || data.replace(/(\r\n|\n|\r)/gm, "") == "complete") {
                    window.location.replace("' . $this->createUrl('/x2sign/x2sign/signingComplete') . '?key=' .$key. '");
                } else {
                    data = JSON.parse(data);
                    // Clear PDF if user chooses a different PDF to use for a template
                    $("#pdf-dropzone").empty();

                    $("#page_num").empty();
                    $("#page_count").empty();
                    $(".canvas-wrapper").each(function(i, obj) {
                        obj.remove();
                    });


                    pdfDoc = null,
                        url = "' . $this->createUrl('/x2sign/x2sign/getFile/id') . '/" + data["mediaId"],
                        pageNum = 1,
                        pageRendering = false,
                        pageNumPending = null,
                        scale = 2;
                    url = "' . $this->createUrl('/x2sign/x2sign/getFile/id') . '/" + data["mediaId"];
                    fields = JSON.parse(data["fields"]);
                    currentDocId = data["signDocId"];
                    renderPDF(url);
                    //if this is the last doc change the Sign Doc button to Complete document
                    if(data["lastDoc"]) $("#sign-button").val("Complete Signing");
                    $("#sign-button-container").show();
                }
            },
            error: function(data) {
                $("#sign-button-container").show();
                alert(data.responseText); 
            }  
        });
    });

    // save Document
    $("#finish-later-button").click(function () {
        var signFields = [];
        var fail = 0;
        $(dropzone).find(".sign-field").each(function () {
            // Ensure this field is the current recipients
            if($(this).attr("recip") == ' . $position . ') {
                if(!$(this).attr("id").includes("Signature") && !$(this).attr("id").includes("Initials")) {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).find("input, textarea").val().length > 0)) {
                        let fieldVal = "";
                        if($(this).attr("id").includes("Date"))
                            fieldVal = $(this).val();
                        else if($(this).attr("id").includes("Checkbox"))
                            if($(this).find("input").is(":checked"))
                                fieldVal = 1;
                            else
                                fieldVal = 0;
                        else
                            fieldVal = $(this).find("input, textarea").val();
                        
                        signFields.push({
                            "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                            "fieldId": $(this).attr("id"),
                            "value": fieldVal,
                        });
                    } else {
                        fail = 1;
                    }
                } else {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).is("img"))) {
                        signFields.push({
                            "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                            "fieldId": $(this).attr("id"),
                            "value": $(this).is("img") ? ($(this).attr("id").includes("Signature") ? signatureId : initialId) : -1,
                        });
                    } else {
                        fail = 1;
                    }
                }
            }
        });
        
        $.ajax({
            url: "' . $this->createUrl('/x2sign/x2sign/sign') . '",
            type: "POST",
            data: {
                signReq: signReq,
                fields: signFields,
                signDocId: currentDocId,
                modelId: ' . $model->id . ',
                modelType: "' . $modelType . '",
                envelopeId: ' . $envelopeId . ',
                cancel: 0,
                saveTime: 1,
                key: "' .$key. '",
            },
            success: function(data) {
                alert("Changes saved");
            }
        });
    });


    // Cancel signing 
    $("#cancel-signing-button").click(function () {
        if(confirm("Are you sure you want to cancel?"))
            $.ajax({
                url: "' . $this->createUrl('/x2sign/x2sign/sign') . '",
                type: "POST",
                data: {
                    signDocId: currentDocId,
                    modelId: ' . $model->id . ',
                    modelType: "' . $modelType . '",
                    envelopeId:  ' . $envelopeId . ',
                    cancel: 1,
                    key: "' .$key. '",
                },
                success: function(data) {
                    if(data == "canceled")
                        window.location.replace("' . $this->createUrl('/x2sign/x2sign/signingCancelled') . '");
                }
            });
    });

    /**
     * Render multiple pages simultaneously
     */
    function renderPages(pdfDoc) {
        numPages = pdfDoc.numPages;
        for(var num = 1; num <= numPages; num++)
            pdfDoc.getPage(num).then(renderPage);
    }

    function renderPage(page) {
        var viewport = page.getViewport({scale: scale});
        var wrapper = document.createElement("div");
        wrapper.classList.add("canvas-wrapper");
        wrapper.id = "page-" + (page.pageIndex + 1);
        var newCanvas = document.createElement("canvas");
        $(newCanvas).attr("data-page-num", page.pageIndex + 1);
        //newCanvas.id = "page-" + (page.pageIndex + 1);
        newCanvas.classList.add("x2-sign-doc");

        var ctx = newCanvas.getContext("2d");
        var renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        newCanvas.height = viewport.height;
        newCanvas.width = viewport.width;
        wrapper.appendChild(newCanvas);
        //$("#document")[0].appendChild(wrapper);
        $(wrapper).insertBefore("#sign-button-container");
        page.render(renderContext);
        page.getTextContent().then(function(textContent) {
                        console.log("trying to find sig");
                        if(textContent.items != null) {
                            for(let j = 0; j < textContent.items.length; j++) {
                                var block = textContent.items[j];
                                var viewPort = page.getViewport({scale: scale});
                                var objTransformed = pdfjsLib.Util.transform(viewPort.transform, block.transform);
                                if(block["str"] == "Docusign_Recipient_signature") {
                                    let transform = block.transform;
                                    const y = transform[5];
                                    //const height = block.height;
                                    let top = 1056 - ((y * 96) / 72) - 27.06;
                                    //check to see if the signature is on the right page if not we will have to reload page
                                    var RightPage = ((page.pageIndex + 1) == $("#Signature-1").attr("page"));
                                    //check for signature 2 just in case
                                    if(!RightPage)RightPage = ((page.pageIndex + 1) == $("#Signature-2").attr("page"));
                                    $("#Signature-1").css({
                                        "top": top,
                                    //    "height": height
                                    });
                                    //if($(".x2-sign-doc").data("pageNum") != $("#Signature-1").attr("page"))
                                    //    $("#Signature-1").hide();
                                    //else
                                    //    $("#Signature-1").show();
                                    $.ajax({
                                        url: "' . $this->createUrl('/x2sign/x2sign/updateFieldInfo') . '",
                                        type: "POST",
                                        data: {
                                            id: ' . $signDocId . ',
                                            top: top,
                                            page: (page.pageIndex + 1),
                                        },
                                        success: function(data) {
                                            //data = JSON.parse(data);
                                            console.log(data);
                                            //if it was on the wrong page we now reload the page 
                                            if(!RightPage)location.reload(true);
                                        }
                                    });
                                }
                            }
                        }
                    });
    }

    /**
     * Get page info from document, resize canvas accordingly, and render page.
     * @param num Page number.
     */
    /*function renderPage(num) {
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
        $("#page_num").text(num);
        //document.getElementById("page_num").textContent = num;
    }*/

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
    $("#prev").on("click", onPrevPage);
    // document.getElementById("prev").addEventListener("click", onPrevPage);

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
    $("#next").on("click", onNextPage);
    // document.getElementById("next").addEventListener("click", onNextPage);


    function onNextField() {
        var signFields = [];
        var fail = 0;
        var pages = [];

        var nextId = "-1";
        $(dropzone).children().each(function () {
            if($(this).attr("recip") == ' . $position . ') {
                if(!$(this).attr("id").includes("Signature") && !$(this).attr("id").includes("Checkbox")) {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).find("input, textarea").val().length > 0)) {
                        $(this).css("background-color", "rgba(0, 0, 0, 0)");
                        signFields.push({
                            "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                            "fieldId": $(this).attr("id"),
                            "value": $(this).attr("id").indexOf("Date") == -1 ? $(this).find("input, textarea").val() : $(this).val(),
                        });
                    } else {
                        $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                        if(!pages.includes($(this).attr("page")))pages.push($(this).attr("page"));
                        if(nextId == "-1")nextId = $(this).attr("id");
                        fail = 1;
                    }
                } else if($(this).attr("id").includes("Checkbox")) {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).find("input").val().length > 0)) {
                        signFields.push({
                            "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                            "fieldId": $(this).attr("id"),
                            "value": $(this).find("input").is(":checked").toString(),
                        });
                    } 
                } else {
                    if($(this).attr("req") == 0 || ($(this).attr("req") == 1 && $(this).is("img"))) {
                        $(this).css("background-color", "rgba(255, 255, 255, 0.5)");
                        signFields.push({
                            "type": $(this).attr("id").substring(0, $(this).attr("id").indexOf("-")).toLowerCase(),
                            "fieldId": $(this).attr("id"),
                            "value": $(this).is("img") ? signatureId : -1,
                        });
                    } else {
                        $(this).css("background-color", "rgba(255, 0, 0, 0.5)");
                        if(!pages.includes($(this).attr("page")))pages.push($(this).attr("page"));
                        if(nextId == "-1")nextId = $(this).attr("id");
                        fail = 1;
                    }
                }
            }
    
        });
        
        if(fail == 1) {
            signFields = [];
            var FieldNumber = $("#" + nextId).attr("page");
            if(pageNum < FieldNumber){
                 var runs = FieldNumber - pageNum;
                for (i = 0; i < runs; i++){
                    onNextPage();
                }                           
            }

            if(pageNum > FieldNumber){
                var runs = pageNum - FieldNumber;
                for (i = 0; i < runs; i++){
                    onPrevPage();
                }
            }
            
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#" + nextId).offset().top - 30
                }, 20);
            
            return;
        }

    }


    $("#next-field").click(function() {
        console.log( focusFields,1);
        if(focusFields.length == 0)
            $("#sign-button").focus();
        else {
            let id = focusFields[0];
            //check to see if field already filled, if so skip
            if(checkFill(id)){
                focusFields.shift();
                $("#next-field").click();
            }else{
                $("#" + id + " > input").focus();
                $("#" + id)[0].scrollIntoView({
                    block: "center",
                    inline: "center"
                });
                focusFields.shift();
            }
        }
    });
    
    
    //this function will check to see if a field is filled allready
    function checkFill(id){
        if(id.includes("Signature") ||  id.includes("Initials")){
            var varlegnt = $("#" + id).find("p").length;
            if(varlegnt == 0)return true;
            else false;
        }else{
            if($("#" + id).find("input, textarea, select").val().length > 0)return true;
            else return false;
        }

    }


    /**
     * Asynchronously downloads PDF.
     */
    function renderPDF(url) {
        pdfjsLib.disableWorker = true;
        pdfjsLib.getDocument(url).promise.then(renderPages);

        // Solution for "waiting" until the canvas elements are completely rendered in DOM
        // then subsequently adding the field to the correct page
        // Adapted from: https://stackoverflow.com/questions/16149431/make-function-wait-until-element-exists/47776379 

        //check for the highest page number and wait till that page is loaded
        var highPage = 1;
        if(fields.length > 0) {
            for(i = 0; i < fields.length; i++) {
                var field = fields[i];
                if(parseInt(highPage) < parseInt(field["page"])) highPage = parseInt(field["page"]);
            }
        }
        var checkExist = setInterval(function() {
            console.log(highPage);
            if ($("#page-" + highPage).length) {
                if(signReq == 1){
                    renderFields();
                }else{
                    $("#sign-button").val("Next");        
                }

                $("input:radio,input:checkbox,select#dropdown,select#yesNo").on( "change", function(e) {
                    var field = getFieldByID(this.parentElement.id)
                    if(field["conditional"] == "1") {
                        showHideDependentFields(field);
                        if(field["dependent-fields"]) {
                            let dependent_field = getFieldByID(field["dependent-fields"])
                            showHideDependentFields(dependent_field);
                        }
                    }
                    if($("#" + field["id"]).attr("partner")) {
                        let partner_id = $("#" + field["id"]).attr("partner").match(/\[(.*?)\]/)[1];
                        let partner_field = getFieldByID("Radio-" + partner_id)
                        
                        if(partner_field["conditional"] == "1") {
                            showHideForCheckboxRadio(partner_field);
                        }
                    }
                });
                handleConditionalFields();

                clearInterval(checkExist);
            }
        }, 3);


        function getFieldByID(field_id) {
            for(i = 0; i < fields.length; i++) {
                var field = fields[i];
                if(field["id"] == field_id) {
                    return field;
                }
            }
            return null;
        }
        
        function handleConditionalFields() {
            for(i = 0; i < fields.length; i++) {
                var field = fields[i];
                console.log(field);
                if(field["conditional"] == "1") {
                    showHideDependentFields(field);
                }
            }
        }
        
        function showHideDependentFields(field) {
            if($("#" + field["id"]).is(":hidden")) {
                $("#" + field["dependent-fields"]).hide();
                return;
            }
            
            var field_type = field["id"].substring(0, field["id"].indexOf("-"));
            if(field_type == "Radio" || field_type == "Checkbox") {
                showHideForCheckboxRadio(field);
            } else if(field_type == "Dropdown" || field_type == "YesNo") {
                showHideForDropdown(field);
            }
        }
        function showHideForCheckboxRadio(field) {
            var checked = $("#" + field["id"]).find("input").is(":checked") ? "checked" : "unchecked";
            if(field["trigger-condition"] == checked) {
                if(field["show-hide"] == "show") {
                    $("#" + field["dependent-fields"]).show();
                } else if(field["show-hide"] == "hide") {
                    $("#" + field["dependent-fields"]).hide();
                }
            } else {
                if(field["show-hide"] == "show") {
                    $("#" + field["dependent-fields"]).hide();
                } else if(field["show-hide"] == "hide") {
                    $("#" + field["dependent-fields"]).show();
                }
            }
        }
                    
        function showHideForDropdown(field) {
            var selected_val = $("#" + field["id"]).find("select :selected").text();
            if(field["trigger-condition"] == selected_val) {
                if(field["show-hide"] == "show") {
                    $("#" + field["dependent-fields"]).show();
                } else if(field["show-hide"] == "hide") {
                    $("#" + field["dependent-fields"]).hide();
                }
            } else {
                if(field["show-hide"] == "show") {
                    $("#" + field["dependent-fields"]).hide();
                } else if(field["show-hide"] == "hide") {
                    $("#" + field["dependent-fields"]).show();
                }
            }
        }


        
        /*pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            $("#page_count").text(pdfDoc.numPages);

            // Initial/first page rendering
            renderPage(pageNum);
            
            if(signReq == 1)
                renderFields();
            else
                $("#sign-button").val("Next");
        });*/
    }

    // Render whatever initial page for signing is needed
    pdfDoc = null,
        scale = 2,
        url = "' . $this->createUrl('/x2sign/x2sign/getFile/id') . '/" + ' . $mediaId . ',
        /*pageNum = 1,
        pageRendering = false,
        pageNumPending = null,
        scale = 2,
        canvas = document.getElementById("pdf"),
        ctx = canvas.getContext("2d");*/

    renderPDF(url);



    //code to detect mobile taken from here https://stackoverflow.com/questions/3514784/what-is-the-best-way-to-detect-a-mobile-device
// device detection
    if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) { 
        // we will remove the next field buttons and change the css to have 50% on document
        $("#nextDiv").hide();
        $("#document").attr("style","left:50%;");
    }


    if(' . $signatureExists . ' == 0 && signReq == 1)
        $("#create-signature-modal").show();

    // Hide the next and cancel buttons if recipient is a viewer
    if(signReq == 0)
        $("#cancel-button-container, #sign-button-container, #finish-later-button-container").hide();

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
            location.reload(true);
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
        width: 816px;
        border-radius: 3px;
        position: relative;
        left: 30%;
        transform: translate(-50%);
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
        position: relative;
        left: 50%;
        transform: translate(-50%);
        /*margin-left: -408px;*/
    }

    #pdf-dropzone {
        z-index: 2;
        width: 816px;
        height: 1056px;
        position: absolute;
        left: 50%;
        margin-left: -408px;
    }

    .canvas-wrapper {
        width: 816px;
        height: 1056px;
        display: inline-block;
        margin: auto;
        margin-bottom: 20px;
        z-index: 1;
        position: sticky;
    }

    .x2-sign-template {
        position: relative;
        height: 1070px;
    }

    .sign-field {
        z-index: 3;
        top: 6px;
        image-rendering: -webkit-optimize-contrast;
    }



    input.x2-sign-input {
        //min-width: 95px !important;
        //width: 95px;
        position: relative;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: left;
        font-family: monospace !important;
        font-size: 12px;
        letter-spacing: 0.14px;
        margin: unset !important;
    } 

    select#YesNo {
        position: absolute;
        top: 5px;
        left: 5px;
        margin: unset;
        z-index: 3;
    }

    select#Dropdown {
        position: absolute;
        top: 5px;
        left: 5px;
        margin: unset;
        z-index: 3;
        width:150px;
    }


    input#text {
        position: relative;
        //min-width: 27px !important;
        width: 27px;
      //  top: 5px;
      //  left: 5px;
      //  margin: unset;
    }

    input#checkbox {
        position: absolute;
        top: 7px;
        left: 8px;
        margin: unset;
    }

    // input#initials {
    //     min-width: 44px !important;
    //     width: 44px;
    //     position: relative;
    //     top: 5px;
    //     left: 7px;
    //     height: 20px;
    //     text-align: left;
    //     font-family: monospace;
    //     font-size: 12px;
    //     letter-spacing: 0.14px;
    //     margin: unset;
    // }

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

    #finish-later-button-container {
        margin: auto;
    }


    #next-field {
        text-align: center;
        margin-top: 20px;
    }

    #sign-button {
        width: 30%;
        top: 38%;
        position: relative;
        height: 42%;
        font-size: 135%;
        margin: auto;
        background-color: #fff700;
    }

    #finish-later-button {
        position: relative;
        margin: auto;
        margin-top: 2%;
        margin-bottom: 1%;
    }



    #cancel-signing-button {
        position: relative;
        margin: auto;
        margin-top: 2%;
        margin-bottom: 1%;
    }

    a[id^="Signature"] > p, a[id^="Initials"] > p {
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
        overflow: auto;
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 3; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        background-color: rgb(0, 0, 0); /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
        overflow-y: auto;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 0% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 20%; /* Could be more or less, depending on screen size */
    }

    .create-signature {
        font-size: 20px;
        width: 70%;
        display: block;
        border: solid 1px black;
        border-radius: 5px;
    }

    .create-signature-button {
        margin: auto !important;
        display: inline-block !important;
        border-color: black;
    }

    .required {
        color: red;
    }

    @font-face {
        font-family: Allura-X2;
        src: url('. $allura  .');
    }

    @font-face {
        font-family: Otto-X2;
        src: url('. $otto .');
    }

    @font-face {
        font-family: May-X2;
        src: url('. $may  .');
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
        font-size: 40px;
    }
');


//this will get the value for first and last name 
$fistName = "";
$lastName = "";
if(isset($model->firstName)){
    $fistName = $model->firstName;
    $lastName = $model->lastName;

}


?>

<!-- Adapted from example: https://stackoverflow.com/questions/43165984/how-to-create-modal-box-using-html-css-and-jquery -->
<div id="create-signature-modal">
    <div class="modal-content">
        <h6 style="text-align:center;">In order for you to sign this document, you need to create a digital signature. To do so, please fill out your
        full legal name below.</h6>
        <br>
        <h5>First Name <span class="required">*</span></h5>
        <input class="create-signature" id="signature-first-name" value=<?php echo '"'.  $fistName  .  '"' ?>></input>
        <br>
        <h5>MI</h5>
        <input class="create-signature" id="signature-middle-initial" style="width: 35px;" maxlength="1" size="1"></input>
        <br>
        <h5>Last Name <span class="required">*</span></h5>
        <input class="create-signature" id="signature-last-name" value=<?php echo '"'.  $lastName  .  '"' ?>></input>
        </br>
        <p style="text-align: center; color: blue;">Choose a signature font:</p>
        <div id="signature-font">
            <div class="font-choice">
                <input type="radio" name="font" value="0" checked>&nbsp;&nbsp;&nbsp;<label class="font-Allura font-label">Allura</label>
            </div>
            <div class="font-choice">
                <input type="radio" name="font" value="1">&nbsp;&nbsp;&nbsp;<label class="font-Otto font-label">Otto</label>
            </div>
            <div class="font-choice">
                <input type="radio" name="font" value="2">&nbsp;&nbsp;&nbsp;<label class="font-May font-label">May-Queen</label>
            </div>
        </div>
        <input class="create-signature-button btn btn-primary border-primary" style="" type="submit" value="Create signature"></input>
        <br>
        <?php if(!Yii::app()->user->isGuest) { ?>
        <hr>
        <p style="text-align: center; padding-top: 1em; color: blue;">Upload your own signature image</p>
        <div class="signature-upload">
            <?php $this->widget('FileUploader', array(
                    'id' => 'sign-upload',
                    'mediaParams' => array('signer_email' => $emailAddress, 'signer_upload' => 1, 'associationType' => get_class($model), 'associationId' => $model->id),
                    'viewParams' => array('showButton' => false, 'closeButton' => false),
                    'acceptedFiles' => 'image/*',
                ));
            ?>
            <button class="btn btn-primary" style="position: relative; left: 50%; transform: translate(-50%);" onclick="upload_signature();">Upload</button>
        </div>
        <?php } ?>
    </div>
</div>

<template>
    <a id="Signature" class="x2-button sign-field" style="display:none;" onclick="replaceSignature(this);">
        <p>Sign here</p>
    </a>
    <a id="Initials" class="x2-button sign-field" style="display:none;" onclick="replaceSignature(this);">
        <!-- <input id="initials" class="x2-sign-input" type="text" minlength="1" maxlength="5" size="5"  placeholder="Initials"></input> -->
        <p>Initials here</p>
    </a>
    <div id="Formula" class="sign-field" style="display:none;">
        <input id="formula" class="x2-sign-input" type="text" minlength="1" size="15"  placeholder="Formula"></input>
    </div>
    <div id="Checkbox" class="sign-field" style="display:none;">
        <input id="checkbox" class="x2-sign-input"  type="checkbox"></input>
    </div>
    <div id="Text" class="sign-field" style="display:none;">
        <input id="text" class="x2-sign-input" type="text" size="15"  placeholder="Text"></input>
    </div>
    <div id="Name" class="sign-field" style="display:none;">
        <input id="name" class="x2-sign-input" type="text" minlength="1" size="15"  placeholder="Name"></input>
    </div>
    <div id="Email" class="sign-field" style="display:none;" page="">
        <input id="email" class="x2-sign-input" type="email"  placeholder="Email"></input>
    </div>
    <div id="Title" class="sign-field" style="display:none;" page="">
        <input id="title" class="x2-sign-input" type="text" minlength="1"  placeholder="Title"></input>
    </div>
    <div id="YesNo" class="sign-field" style="display:none;" page="">
        <select id="yesNo" class="x2-sign-input">
            <option value="">Select</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>
    </div>
    <div id="Dropdown" class="sign-field" style="display:none;" page="">
        <select id="dropdown" class="x2-sign-input">
            <option value="">Select</option>
            <option value="option1">option 1</option>
            <option value="option2">option 2</option>
        </select>
    </div>
    <div id="FileUpload" class="sign-field" style="display:none;" page="">
        <input id="fileupload" class="x2-sign-input" type="file" style="width: 100px;" multiple></input>
    </div>
    <div id="Radio" class="sign-field" style="display:none;">
        <input id="radio" class="x2-sign-input" type="radio"></input>
    </div>
</template>
<div>
    <div id="nextDiv" class="x2-button-group" style="text-align: center; float: left; display: inline-block; width: 20%;">
        <button type="button" id="next-field" class="btn btn-light btn-sm" style="text-align: center; float: right; ">Next Required Field</button>
        <!-- <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span> -->
    </div>


    <div id="document" class="column">


    <!-- <div class="row x2-sign-template">
        <canvas id="pdf" class="x2-sign-doc"></canvas>
        <div id="pdf-dropzone"></div>
    </div> -->
        </br>
        <div class="cell" id="sign-button-container">
            <input id="sign-button" type="submit" class="x2-button" value="<?php if ($lastDoc) echo Yii::t('app', 'Complete Signing'); else echo Yii::t('app', 'Next Document');?>" />
        </div>
        <div class="cell" id="finish-later-button-container">
            <input id="finish-later-button" type="submit" class="x2-button" value="<?php echo Yii::t('app', 'Finish Later');?>" />
        </div>

        <div class="cell" id="cancel-button-container">
            <input id="cancel-signing-button" type="submit" class="x2-button" value="<?php echo Yii::t('app', 'VOID');?>" />
        </div>
    </div>

</div>
<script>
    $(document).ready(function() {
        // check where the shoppingcart-div is  
        var offset = $("#nextDiv").offset();

        $(window).scroll(function () {
            var scrollTop = $(window).scrollTop(); // check the visible top of the browser  
            $("#nextDiv").css("margin-top", scrollTop);
        });
    });
</script>






