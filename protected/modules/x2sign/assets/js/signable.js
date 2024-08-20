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
let fileId;
const dropzone = $('#document')[0];//$('#document')[0];
const recipientColors = {
    1: 'rgba(3, 207, 252, 0.5)',
    2: 'rgba(252, 207, 3, 0.5)',
    3: 'rgba(98, 209, 0, 0.5)',
    4: 'rgba(255, 17, 0, 0.5)',
    5: 'rgba(0, 218, 196, 0.5)',
    6: 'rgba(255, 0, 221, 0.5)',
    7: 'rgba(0, 140, 255, 0.5)',
    8: 'rgba(132, 0, 255, 0.5)',
    9: 'rgba(255, 140, 0, 0.5)',
    10: 'rgba(173, 173, 173, 0.5)',
};
let draggableCounter = 0;
let documentId = null;
let mode = ''; //set to create/view/update by the respective init function
let numPages = 0;
let textDraggables = ["Formula", "Text", "Name", "Title"];

// Create the draggable onto the dropzone
function createDraggable(id, sync=null) {
    //here we will check to see if the curently selected recipent is a viewer
    var curRec = $('#choose-recipient option[value=\'' + $('#choose-recipient option:selected').val() + '\']').data('recip');
    var viewers = $("#viewerspotsIds").data('value');
    if(viewers.includes(curRec)){
        alert('No fields can be assigned to this recipent, they are a viewer.');
        return;
    }
    var scrollTop = $(window).scrollTop();
    //var pageNum = $('#page_num').text();
    var pageNum = 1;
    var original = document.getElementById(id + '-draggable');
    var clone = original.cloneNode(true);
    $(clone).attr('id', id + '-' + ++draggableCounter);
    if(pageNum === '')
        $(clone).attr('page', 1);
    else
        $(clone).attr('page', pageNum);
    $(clone).removeAttr('style');
    $(clone).css({
        'left': '10px', 
        'top': scrollTop + 10 + 'px', 
        'position': 'absolute',
        'height': '30px',
        'cursor': 'pointer',
        'z-index': '4',
        'outline': '0.2rem ridge #ffff00',
    });

    let cloneId = $(clone).attr('id');
    cloneId = cloneId.substring(0, cloneId.indexOf('-'));
    // Change the fields' width depending if it's not a checkbox, date, or initials
    switch(cloneId){
        case 'Text':
            $(clone).css('width', '70px');
            $(clone).resizable({
                handles: 'ne, se, sw, nw',
                cancel: 'a, i',
                containment: 'parent',
                resize: function(event, ui) {
                    $(clone).find("input").width($(clone).width() - 20 + "px"); //($(clone).width() - 5) - 30
                    $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
                }
            });

            break;          
        case 'YesNo':
            $(clone).css('width', '80px');
            $(clone).attr({ "conditional": 0, "dependent-fields": "", "trigger-condition": "", "show-hide" : "" });
            break; 
        case 'Dropdown':
            $(clone).attr({ "selectedOptionId": "" });
            $('#custom-select-field').css('display', 'block');
            $(clone).css('width', '160px');
            $.ajax({
                type: "GET",
                url: yii.baseUrl + "/index.php/x2sign/GetDropdowns",
                dataType: "json",
                success: function (data) {
                    $("#select-option").empty();
                    $("#select-option").append(
                        $("<option>", {
                            value: "",
                            text: "Select",
                        })
                    );
                    $.each(data, function (index, option) {
                        $("#select-option").append(
                            $("<option>", {
                                value: option.id,
                                text: option.name,
                            })
                        );
                    });
                },
                error: function (xhr, textStatus, errorThrown) {
                    console.error("Error fetching dropdown data: " + errorThrown);
                },
            });
            break;              
        case 'FileUpload':
            var autocompleteValue = $('#autocomplete-field option:first').val();
            $('#visibility-field').css('display', 'block');
            $(clone).attr({ "visibility": "Private", "autocomplete": autocompleteValue });
            $(clone).css({
                'width': '120px', 
                'height': '37px',
            });
            break;
        case 'Signature':    
        case 'Initials':
            $(clone).css('width', '90px');
            $(clone).resizable({
                handles: 'ne, se, sw, nw',
                cancel: 'a, i',
                containment: 'parent',
                resize: function(event, ui) {
                    $(clone).find("input").width($(clone).width() - 20 + "px"); //($(clone).width() - 5) - 30
                    $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
                }
            });
            break;           
        case 'Checkbox':
        case 'Radio':
           $(clone).css('width', '30px');
           $(clone).attr({ "conditional": 0, "dependent-fields": "", "trigger-condition": "", "show-hide" : "" });
            break;   
        case 'Dropdown':
            $(clone).attr({ "conditional": 0, "dependent-fields": "", "trigger-condition": "", "show-hide" : "" });
            break;           
        default:
            $(clone).css('width', '115px');
            $(clone).resizable({
                handles: 'ne, se, sw, nw',
                cancel: 'a, i',
                containment: 'parent',
                resize: function(event, ui) {
                    $(clone).find("input").width($(clone).width() - 20 + "px"); //($(clone).width() - 5) - 30
                    $(clone).find("input").css({"font-size": ($(clone).height() * .8) - 10});
                }
            });

    } 
    // Give the newest draggable the active class and remove it
    // from the current active draggable
    $('#document').find('.active').removeClass('active').css({
        'z-index': '4',
        'outline': '',
    });
    
    // Make the newest element added to document active
    // and make required and read-only false by default
    $(clone).addClass("active").attr({ "req": 0, "read-only": 0 });

    // Make the required and read-only checkboxes unchecked
    // when adding a field
    $('#is-required, #read-only').prop('checked', false);
    if($(clone).attr('id').includes('Signature') || $(clone).attr('id').includes('Initials')) {
        $('#is-required').prop('checked', true);
        $(clone).attr('req', 1);
    }




    /* $(clone).css('background-color', $('#choose-recipient option:selected').val());
    $(clone).draggable({
        cursorAt: {
            top: 5,
            left: 5
        },
        addClasses: false,
        scroll: true,
        cancel: '.delete-field, input, textarea',
        containment: 'parent',
        cursor: 'pointer',
        //appendTo: 'div'
    }); */

    dropzone.appendChild(clone);

    // Set the position of the draggable on the canvas it's hovering over
    let yPos = $(clone).position()['top'];
    for(var i = 1; i <= numPages; i++) {
        let pdfPos = parseInt($("#page-" + i).position()['top']);
        let pdfHeight = parseInt($("#page-" + i).outerHeight());
        let endOfPage = pdfPos + pdfHeight;
        if(yPos > pdfPos && yPos < endOfPage) {
            $(clone).attr('page', i);
            $("#page-" + i)[0].appendChild(clone);
            $(clone).css('top', yPos - pdfPos);

            break;
        }    
    }
    //initX2SignFieldPosition(clone);

    // Determine what field options should be changeable
    $('.field-options').show();
    $('.text-options').show();
    $('.input-field-options').show();

    $('.data-labels').show();
    $("#data-label").removeClass('active');
    $('.data-label-dropdown, .data-label-form').hide();

    $('#attach-data-label').hide();
    $('#remove-data-label').hide();

    if (!textDraggables.includes(cloneId)) {
        $('.data-labels').hide();
    }

    if(['Date', 'Checkbox', 'Signature', 'Initials','YesNo', 'Dropdown','Radio'].includes(cloneId)) {
        $('.text-options').hide();
        if(cloneId == 'Date')
            $('.input-field-options').hide();
    }

    if(cloneId == 'Radio')
        $('#radio-field').show();
    else
        $('#radio-field').hide();

    $(clone).css('background-color', $('#choose-recipient option:selected').val());
    $(clone).draggable({
        addClasses: false,
        scroll: true,
        cancel: '.delete-field, input, textarea, select',
        containment: 'parent',
        cursor: 'pointer',
        tolerance: 'fit',
        revert: 'invalid',
        //appendTo: 'div'
    });

    if(!['FileUpload'].includes(cloneId)){
        $(clone).find('input').css({
            'font-size': ($(clone).height() * .8) - 10,
            'width': $(clone).width() - 10,
        });
    }else{
        $(clone).find('input').css({
            'width': $(clone).width() - 10,
        });

    }

    if(['Signature', 'Initials'].includes(cloneId))
            $(clone).resizable({
                handles: 'ne, se, sw, nw',
                cancel: 'a, i',
                containment: 'parent',
                minHeight: 30,
                maxHeight: 80,
                minWidth: 90,
                maxWidth: 150,
            });
    
    if(cloneId == 'Date')
        $(clone).css({
            'height': '20px',
            'width': '80px',
        });
    if(id == "Name"){
        $(clone).find(".x2-sign-input").val($("#choose-recipient option:selected").text());

    }

    if(id == "Email"){
        $(clone).find(".x2-sign-input").val($("#choose-recipient option:selected").attr("email"));

    }

    if(cloneId == 'Radio' && !sync){
        //if radio we need 2 checkboxes and to link them togheter 
        createDraggable('Radio', draggableCounter);
        $(clone).attr('partner', JSON.stringify([draggableCounter]));
        $(clone).removeClass('active').css({
            'z-index': '4',
            'outline': '',
        });
        
    }else if (cloneId == 'Radio' ){
        $(clone).attr('partner', JSON.stringify([sync]));
    }
    if (cloneId == 'Radio' )
        linkRadio($(clone).attr('id'));
    // Adapted from: https://stackoverflow.com/questions/8141749/how-attach-html-element-to-mouse-using-jquery
    //adding code so if it is a ratio we will space the 2 objects
    $(dropzone).mousemove(function(e) { 
        var space = 0;
        if ($(clone).attr('id').includes('Radio')){
                if($(clone).attr('id').split('-')[1] % 2)space = 20;
                else space = -20;
        }

        $(clone).offset({ 
            top: e.pageY, 
            left: e.pageX + space 
        }); 
    }).click(function () { 
        $(this).unbind("mousemove");
        var hoverArray = document.querySelectorAll( ":hover" );
        //console.log(hoverArray );
        var pageID = '';
        //get the page ID string.includes(substring)
        for (let i = 0; i < hoverArray.length; i++) {
            if(hoverArray[i].id.includes('page')){
                pageID = hoverArray[i].id;
            }
        }
        $(clone).css({
            'z-index': '5',
            'outline': '',
        });
        $(clone).css('top', parseInt($(clone).offset().top) - parseInt($("#" + pageID).offset().top));
        $(clone).attr('page', $("#" + pageID).children('canvas').attr('data-page-num'));
        $("#" + pageID).append(clone);

        var prefix = "page-";
        var id = pageID.substring(prefix.length);

        $(`.thumbnail-canvas[data-index="${id}"]`).append(`
            <div class="css-clip">
                <div class="css-clip-edit" data-qa="indicator-tag"></div>
            </div>`
        );

        //console.log(pageID);
        $(this).unbind("click");

    });


}


//this function will be to ensure on ratio boxes only one is clicked 
function linkRadio(id){
    //get the other radio Id
    //I assume that the other radio ID is my id + 1

    $('#' + id).find('input').change(function() {
        var ids = JSON.parse($('#' + id).attr('partner'));

        if($(this).prop("checked")){
            ids.forEach((element) => {
                $('#Radio-' + element).find('input').prop("checked", false);
            });
        }
    });

    $('#' + id).on("remove", function () {
        var ids = JSON.parse($('#' + id).attr('partner'));

        ids.forEach((element) => {
            $('#Radio-' + element).find('input').parent().remove();
        });
    });
}


// Remove draggable
function deleteDraggable(parent) {
    if($(parent).hasClass('active') && $(dropzone).children().length > 1) {
        $('.field-options').hide();
    }

    const id = parent.attr('id');
    const page = parent.attr('page');

    if (id && id.includes("Radio")) {
        // Remove the first two elements
        $(`.thumbnail-canvas[data-index="${page}"]`).find(".css-clip").slice(0, 2).remove();
    }
    else {
        $(`.thumbnail-canvas[data-index="${page}"]`).find(".css-clip")[0]?.remove();
    }

    $(parent).remove();

    if($(dropzone).children().length == 0) {
        $('.field-options').hide();
    }
}


//if delete is click remove the curent active field
document.addEventListener("keydown", KeyCheck);  //or however you are calling your method
function KeyCheck(event)
{
   var KeyID = event.keyCode;
   switch(KeyID)
   {
      case 8: 
      case 46:
      //test to see if user is in text area or on box
      if($("#document").find(".active").children('input').is(":focus") || $(".data-label-form").children('input').is(":focus")){
        //input and text area has focus
      }else{
        deleteDraggable($("#document").find(".active"));  
      }
      break;
      default:
      break;
   }
}



function copyToAllPages(parent) {
     for(let i = 1; i <= numPages; i++) {
        if(i == $(parent).attr('page'))
            continue;

        let clone = $(parent).clone(); //parent.cloneNode(true);
        let id = $(parent).attr('id').substr(0, $(parent).attr('id').indexOf('-'));

        $(clone).attr({
            'id': id + '-' + ++draggableCounter,
            'page': i,
            'req': $(parent).prop('req'),
            'read-only': $(parent).prop('read-only'),
            'conditional': $(parent).prop('conditional'),
        }).css('outline', '').draggable({
            addClasses: false,
            scroll: true,
            cancel: '.delete-field, input, textarea',
            containment: 'parent',
            cursor: 'pointer',
            //appendTo: 'div'
        }).removeClass('active').appendTo($("#page-" + i));

        if(['Signature', 'Initials'].includes(id))
            $(clone).resizable({
                handles: 'ne, se, sw, nw',
                cancel: 'a, i',
                containment: 'parent',
                minHeight: 30,
                maxHeight: 80,
                minWidth: 90,
                maxWidth: 150,
            });
    }
    
    return; 
}

// Show fields for view or update page
function showFields(fields) {
    $.each(fields, function(index, value) {
        var fieldType = value['id'].substring(0, value['id'].indexOf('-'));
        if(mode == 'update' && value['id'].substring(value['id'].indexOf('-') + 1) > draggableCounter)
            draggableCounter = value['id'].substring(value['id'].indexOf('-') + 1);

        // Create clone element
        var clone = $('#' + fieldType + '-draggable').clone().attr({
            'id': value['id'],
            'page': value['page'],
            'req': value['req'],
            'read-only': value['read-only'],
            'conditional': value['conditional'],
        }).css({
            'top': parseInt(value['top'].substring(0, value['top'].indexOf('p'))) + 'px',
            'left': parseInt(value['left'].substring(0, value['left'].indexOf('p'))) + 'px',
            'height': value['height'],
            'width': value['width'],
            'position': 'absolute',
            'background-color': recipientColors[value['recip']],
            'cursor': 'pointer',
        }).show().appendTo(dropzone);
       
        $(clone).detach().appendTo($("#page-" + value['page']));
        
        //check to see if there is a data lable
        if('DataKey' in value) $(clone).attr('dataKey',value['DataKey']);
       
        //check to see if the field has the dropdown id its a drop down so reload it
        if('selectedOptionId' in value){
             $(clone).attr('selectedOptionId',value['selectedOptionId']);
             var activeSelectElement = $(clone).find('select#Dropdown');
            var ajaxUrl = yii.baseUrl + "/index.php/x2sign/GetSelectedDropdown/" + value['selectedOptionId'];

            $.ajax({
                type: "GET",
                url: ajaxUrl,
                dataType: "json",
                success: function(data) {
                    var optionsData = JSON.parse(data.options);
                    $('#trigger-condition').empty();
                    activeSelectElement.empty();
                    var firstOptionAdded = false;

                    $.each(optionsData, function(key, value) {
                        var option = $('<option>', {
                            value: key,
                            text: value
                        });
                        activeSelectElement.append(option);
                        var option_clone = option.clone();
                         $('#trigger-condition').append(option_clone);
                        if (!firstOptionAdded) {
                            option.prop('selected', true);
                            firstOptionAdded = true;
                        }
                    });

                },
                error: function(xhr, textStatus, errorThrown) {
                    console.error("Error fetching dropdown data: " + errorThrown);
                },
            });
        } else {
            console.log("No option selected in #select-option dropdown.");
        }


        //check to see if it has conditonal values if so add them in
        if('conditional' in value)$(clone).attr('conditional',value['conditional']);
        if('dependent-fields' in value)$(clone).attr('dependent-fields',value['dependent-fields']);
        if('trigger-condition' in value)$(clone).attr('trigger-condition',value['trigger-condition']);
        if('show-hide' in value)$(clone).attr('show-hide',value['show-hide']);        
        if('numberlimited' in value){
            $(clone).attr('numberlimited',1)
            if('minvalue' in value)$(clone).attr('minvalue',value['minvalue']);
            if('maxvalue' in value)$(clone).attr('maxvalue',value['maxvalue']);

        }


        //check to see if their are fileupload fields
        if('visibility' in value){
            $(clone).attr('visibility',value['visibility']);
            $('#visibility-field').css('display', 'block');
        }
        if('autocompleteValue' in value)$(clone).attr('autocomplete',value['autocompleteValue']);
 
        // Size the input field proportionally to the wrapper div
        if($(clone).children('input').attr('id') !== 'checkbox')
            $(clone).children('input').css('width', value['width'] - 20).val(value['value']);

        // Size the wrapper div to the draggable div
        $(clone).children('div').css('width', value['width']);

        // Size the input field proportionally to the wrapper div
        if($(clone).children('input').attr('id') !== 'checkbox') {
            $(clone).find('input, textarea').css({
                'width': value['width'] - 20,
                'height': value['height'] - 15,
            }).val(value['value']);
        }

        // Hide or show the draggables depending on the page
        /*$('#document').children().each(function() {
            if($(this).attr('page') != page.pageIndex + 1)
                $(this).hide();
            else
                $(this).show();
        });*/

        //check if a partner stat exsits
        if(typeof value['partner'] !== 'undefined'){
            $(clone).attr('partner', value['partner']);
            linkRadio(value['id']);
        }


        if (mode == 'update') {
            $(clone).draggable({
                addClasses: false,
                scroll: true,
                cancel: '.delete-field, input, textarea',
                containment: 'parent',
                cursor: 'pointer',
                //appendTo: 'div'
            });

            if(['Signature', 'Initials'].includes(fieldType))
                    $(clone).resizable({
                        handles: 'ne, se, sw, nw',
                        cancel: 'a, i',
                        containment: 'parent',
                        minHeight: 30,
                        maxHeight: 80,
                        minWidth: 90,
                        maxWidth: 150,
                    });
        } else if (mode == 'view') {
            $('.recipients').find('span').each(function () {
                $(this).css('background-color', recipientColors[$(this).attr('id')]);
                $(this).parent().css('margin-left', '7px');
            });
        }
    });
}

function editInit (modelId, signDocId, envelopeId, signDocNum) {
    $(dropzone).on('drag click mousedown', function(e) {
        if(e.target === this) {
            $('#document').find('.active').removeClass('active').css({
                'z-index': '5',
                'outline': '',
            });
            $('.field-options').hide();
        }
    });

    $(dropzone).on('drag click mousedown', '.draggable', function(e) {
        // Don't allow event to trigger if user is clicking on the delete button
        if($(e.target).is('a, a *'))
            return;
        
        // Make sure that there is draggable with 'active' class
        // before trying to manipulate it
        if($('#document').find('.active').length > 0) {
            if(['Signature', 'Initials'].includes($('#document').find('.active').attr('id')))
                $('#document').find('.active').resizable('disable');
            
            $('#document').find('.active').removeClass('active').css({
                'z-index': '4',
                'outline': '',
            });
        }

        let activeElemId = $(this).attr('id');
        activeElemId = activeElemId.substring(0, activeElemId.indexOf('-'));
            
        // Determine what field options should be changeable
        $('.field-options').show();
        $('.text-options').show();
        $('.input-field-options').show();
        $('.conditional-options').hide();
        
        $('.data-labels').show();
        $("#data-label").removeClass('active');
        $('.data-label-dropdown, .data-label-form').hide();
        $('#attach-data-label').hide();
        $('#remove-data-label').hide(); 
        if (!textDraggables.includes(activeElemId)) {
            $('.data-labels').hide();
        }

        if(['Date', 'Signature', 'Initials', 'Checkbox'].includes(activeElemId)) {
            $('.text-options').hide();
            if(activeElemId == 'Date')
                $('.input-field-options').hide();
        }
    
        $(this).addClass('active').css({
            'z-index': '4',
            'outline': '0.2rem ridge #ffff00',
        });

        activeElemId = $('#document').find('.active').attr('id');
        activeElemId = activeElemId.substring(0, activeElemId.indexOf('-'));

        if(activeElemId == 'Radio'){
            $('#radio-unselect').show();
            $('#radio-field').show();
        }else{
            $('#radio-unselect').hide();
            $('#radio-field').hide(); 
        }

        if(activeElemId == 'FileUpload')
            $('#visibility').val($(dropzone).find('.draggable.active').attr('visibility'));
            $('#autocomplete-field').val($(dropzone).find('.draggable.active').attr('autocomplete'));

        if(['Signature', 'Initials'].includes(activeElemId))
            $(this).resizable('enable');
    
        if($(this).attr('req') == 1)
            $('#is-required').prop('checked', true);
        else
            $('#is-required').prop('checked', false);
    
        if($(this).attr('read-only') == 1)
            $('#read-only').prop('checked', true);
        else
            $('#read-only').prop('checked', false);
    
        if($(this).attr('conditional') == 1)
            $('#conditional').prop('checked', true);
        else
            $('#conditional').prop('checked', false);
    
        var fieldName = $(this).attr('id').substr(0, $(this).attr('id').indexOf('-'));
        $('.assign-field-name').text(fieldName);
    
        $('span.color-preview').css('background-color', $(this).css('background-color'));
    
        $('#choose-recipient').val($(this).css('background-color'));

        if(['Checkbox', 'Radio', 'Dropdown', 'YesNo'].includes(activeElemId)) {
            $('.conditional-options').show();
            displayDependentFields($('#conditional').is(":checked"));
            displayTriggerConditions($('#conditional').is(":checked"));
            displayShowHide($('#conditional').is(":checked"));
        }
        if(['Text', 'Formula'].includes(activeElemId)) {
            displayNumberFields();
        }else{
            hideNumberFields();
        }

        
    });

    $(document).on('input', '.x2-sign-input', function() {
       // Field is not text field      
        if(!['checkbox','radio', 'YesNo', 'Dropdown, FileUpload'].includes($(this).attr('id'))){ 
            var textLe =  getTextWidth($(this).val() + 'test', $(this).css('font-size') + ' ' +  $(this).css('font-family') );
            var singleText =  getTextWidth('t', $(this).css('font-size') + ' '  +  $(this).css('font-family') );
            var whiteLength = (singleText+2) * $(this).val().length / 2;
            if(textLe + (singleText * 2) + 4 + whiteLength + 35 > $(this).parent().width()) {
                var ratio = (($(this).parent().height() * .8) / 12);
                $(this).width(textLe - singleText - 5 +  'px');
                $(this).parent().width(textLe - singleText + 20 + 'px');
                //$(this).width(((this.value.length + 1) * 7.3) * ratio + 'px'); 
                //$(this).parent().width(((this.value.length + 1) * 7.3) * ratio + 35 + 'px');
            } else if(textLe + 36 < $(this).parent().width() - (singleText * 2)) {
                $(this).width(textLe - singleText  +  'px');
                $(this).parent().width(textLe - singleText  + 35 + 'px');
            }
        }
    });

    $('.create-button-container').click(function(e) {
        e.preventDefault();
        // Remove error messages and error class
        // from anything to prevent error message from being duplicated
        $('.error').removeClass('error');
        $('.errorMessage').remove();

        var errors = false;
        if($.trim($('#doc-name').val()).length == 0) {
            $('#doc-name').addClass('error');
            $('[for=\'X2SignDocs_name\']').addClass('error');
            $('#doc-name').after('<div class=\'errorMessage\'>Name cannot be blank</div>');
            errors = true;
        }
        if(!documentId) {
            $('#Media_name').addClass('error');
            $('[for=\'X2SignDocs_document\']').addClass('error');
            $('#Media_name').after('<div class=\'errorMessage\'>Please select document</div>');
            errors = true;
        }
        $('[id^="Dropdown-"]').each(function() {    
            if(!isNaN($(this).attr('id').substr($(this).attr('id').indexOf('-')+1))) {
                console.log($(this).find('#Dropdown').val())
                if($(this).find('#Dropdown').val() == '') {
                    errors = true;
                    alert('Please add dropdown options for ' + $(this).attr('id'))
                }
            }
        })
        //if($('#document').children().length == 0) {
        //    $('#create-button').before('<div class=\'errorMessage\'>No fields placed on the document</div>');
        //    errors = true;
       // }
        if(!errors) {
            var draggables = [];
            var recipient = '';
            //this is a check for if fields are overlaping
            var overLapCheck = [];
            $(dropzone).find('.draggable').each(function() {
                var draggable = $(this);
                recipient = $('#choose-recipient option[value=\'' + draggable.css('background-color') + '\']').data('recip');

                cloneId = draggable.attr('id').substring(0, draggable.attr('id').indexOf('-'));
                if(['YesNo'].includes(cloneId)){
                    draggables.push({
                        'id': draggable.attr('id'),
                        'page': draggable.attr('page'),
                        'top': draggable.css('top'),
                        'left': draggable.css('left'),
                        'value': draggable.find('select').val(),
                        'width': draggable.width(),
                        'height': draggable.height(),
                        'req': draggable.attr('req'),
                        'read-only': draggable.attr('read-only'),
                        'recip': recipient,
                        'conditional': draggable.attr('conditional'),
                        'dependent-fields': draggable.attr('dependent-fields'),
                        'trigger-condition': draggable.attr('trigger-condition'),
                        'show-hide': draggable.attr('show-hide'),
                    });
                }else if(['Checkbox', 'Radio'].includes(cloneId)) { 
                    draggables.push({
                        'id': draggable.attr('id'),
                        'page': draggable.attr('page'),
                        'top': draggable.css('top'),
                        'left': draggable.css('left'),
                        'value': draggable.find('select').val(),
                        'width': draggable.width(),
                        'height': draggable.height(),
                        'req': draggable.attr('req'),
                        'read-only': draggable.attr('read-only'),
                        'recip': recipient,
                        'partner': draggable.attr('partner'),
                        'conditional': draggable.attr('conditional'),
                        'dependent-fields': draggable.attr('dependent-fields'),
                        'trigger-condition': draggable.attr('trigger-condition'),
                        'show-hide': draggable.attr('show-hide'),
                    });
                }else if(['Dropdown'].includes(cloneId)){
                    const selectElement = draggable.find('select');
                    const selectedValue = selectElement.val();
                    const options = selectElement.find('option').map(function() {
                        return {
                            value: $(this).val(),
                            text: $(this).text()
                        };
                    }).get();
                    draggables.push({
                        'id': draggable.attr('id'),
                        'page': draggable.attr('page'),
                        'top': draggable.css('top'),
                        'left': draggable.css('left'),
                        'value': selectedValue,
                        'options': options,
                        'selectedOptionId': draggable.attr('selectedoptionid'),
                        'width': draggable.width(),
                        'height': draggable.height(),
                        'req': draggable.attr('req'),
                        'read-only': draggable.attr('read-only'),
                        'recip': recipient,
                        'conditional': draggable.attr('conditional'),
                        'dependent-fields': draggable.attr('dependent-fields'),
                        'trigger-condition': draggable.attr('trigger-condition'),
                        'show-hide': draggable.attr('show-hide'),
                    });
                }else if (['FileUpload'].includes(cloneId)) {              
                    draggables.push({
                        'id': draggable.attr('id'),
                        'page': draggable.attr('page'),
                        'top': draggable.css('top'),
                        'left': draggable.css('left'),
                        'visibility': draggable.attr('visibility'),
                        'autocompleteValue': draggable.attr('autocomplete'), 
                        'width': draggable.width(),
                        'height': draggable.height(),
                        'req': draggable.attr('req'),
                        'read-only': draggable.attr('read-only'),
                        'recip': recipient,
                        'partner': draggable.attr('partner'),
                    });
                }else{
                    draggables.push({
                        'id': draggable.attr('id'),
                        'page': draggable.attr('page'),
                        'top': draggable.css('top'),
                        'left': draggable.css('left'),
                        'value': draggable.find('input').val(),
                        'width': draggable.width(),
                        'height': draggable.height(),
                        'req': draggable.attr('req'),
                        'read-only': draggable.attr('read-only'),
                        'numberlimited': draggable.attr('numberlimited'),
                        'minvalue': draggable.attr('minvalue'),
                        'maxvalue': draggable.attr('maxvalue'),
                        'recip': recipient,
                        'partner': draggable.attr('partner'),
                        'DataKey': draggable.attr('datakey'),
                    });
                }
                //now check to see if any of the fields are overlaping
                var pageNum = $(this).attr('page');
                var startX = parseInt($(this).css('left'),10);
                var startY = parseInt($(this).css('top'),10);
                var height = $(this).height();
                var width = $(this).width();
                if(!(pageNum in overLapCheck)) overLapCheck[pageNum] = [];
                //now check the x values 
                if(!(startX in overLapCheck[pageNum])) overLapCheck[pageNum][startX] = [];
                //now for the length of the width fill in each spot on the page X array
                for (let i = 0; i < width; i++) {
                    var checkX = startX + i;
                    if(!(checkX in overLapCheck[pageNum])) overLapCheck[pageNum][checkX] = [];
                } 
                //now here is were the check will start, we will look at the y values
                //if an value is already at the 3d array then there is an overlap
                for (let i = 0; i < width; i++) {
                    var checkX = startX + i;
                    for (let y = 0; y < height ; y++) {
                        var checkY = startY + y;
                        if(!(checkY in overLapCheck[pageNum][checkX])) overLapCheck[pageNum][checkX][checkY] = 1;
                        else{
                            //scroll to it 
                            $(this).get(0).scrollIntoView();
                            window.scrollBy(0,-100)
                            //if we already have a value at the point then there is overlap
                            alert("Fields Overlap on page " + pageNum);
                            throw new Error("Fields Overlap on page " + pageNum);
                            return;
                        }
                    }
                }
                 
                if(parseFloat(draggable.css('left')) > 1001){
                    if (confirm("One of your fields is place off page, this is probably from an extension you have installed. Clicking ok will remove the field from the document this field is on page: " + draggable.attr('page')) == true) {
                      draggable.remove();
                    } else {
                      //text = "You canceled!";
                    }
                    throw new Error("One of your fields is place off page plese rectifiy this");
                    return;

                }



            });
            JSON.stringify(draggables);

            var actionUrl = yii.scriptUrl + '/docs/createSignable';
            var params = {
                name: $('#doc-name').val(),
                template: $("#template-checkbox").prop("checked"),
                fieldInfo: draggables,
                    file: documentId
                };
            if (modelId && signDocId) {
                actionUrl = yii.scriptUrl + '/x2sign/QuickSetupEmail/?id=' + envelopeId + '&signDocNum='+signDocNum;
                params.signDocId = signDocId;
            }
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: params,
                async: false,
                success: function(data) {
                    if(data == 'true') {
                        if ($('.create-button-container').is('#template-btn-top')) {
                            alert("Template saved successfully")
                            let redirectUrl = yii.scriptUrl + '/docs/index';
                            window.location.replace(redirectUrl);
            
                        } else{
                            var throbber$ = auxlib.pageLoading ();
                            // Force CKEditor to update the textarea it replaces with the value
                            //CKEDITOR.instances.emailMessage.updateElement();
                            $.ajax({
                                url: yii.scriptUrl + '/x2sign/quickSendFinish?id=' + envelopeId, 
                                type: 'POST',
                                data: {},
                                success: function (resp) {
                                    alert('Success!');
                                    let redirectUrl = yii.scriptUrl + '/x2sign/index';
                                    window.location.replace(redirectUrl);
                                },
                                error: function(resp) {
                                    throbber$.remove();
                                    console.log('error');
                                    alert('Could not send email: ' + JSON.stringify(resp));
                                }
                            });
                        }
                    }else {
                        window.location.replace(data);
                    }
                }
            });            
        }
    });

    $('.back-button-container').click(function(e) {
        e.preventDefault();

        // Remove error messages and error class
        // from anything to prevent error message from being duplicated
        $('.error').removeClass('error');
        $('.errorMessage').remove();

        var errors = false;
        if($.trim($('#doc-name').val()).length == 0) {
            $('#doc-name').addClass('error');
            $('[for=\'X2SignDocs_name\']').addClass('error');
            $('#doc-name').after('<div class=\'errorMessage\'>Name cannot be blank</div>');
            //errors = true;
        }

        if(!documentId) {
            $('#Media_name').addClass('error');
            $('[for=\'X2SignDocs_document\']').addClass('error');
            $('#Media_name').after('<div class=\'errorMessage\'>Please select document</div>');
            //errors = true;
        }
        if($('#document').children().length == 0) {
            $('#create-button').before('<div class=\'errorMessage\'>No fields placed on the document</div>');
            //errors = true;
        }

        if(!errors) {
            var draggables = [];
            var recipient = '';
            //$('#document').children().each(function() {
            $(dropzone).find('.draggable').each(function() {
                var draggable = $(this);
                recipient = $('#choose-recipient option[value=\'' + draggable.css('background-color') + '\']').data('recip');

                draggables.push({
                    'id': draggable.attr('id'),
                    'page': draggable.attr('page'),
                    'top': draggable.css('top'),
                    'left': draggable.css('left'),
                    'value': draggable.find('input').val(),
                    'width': draggable.width(),
                    'height': draggable.height(),
                    'req': draggable.attr('req'),
                    'read-only': draggable.attr('read-only'),
                    'numberlimited': draggable.attr('numberlimited'),
                    'minvalue': draggable.attr('minvalue'),
                    'maxvalue': draggable.attr('maxvalue'),
                    'recip': recipient,
                    'DataKey': draggable.attr('datakey'),
                });
                if(parseFloat(draggable.css('left')) > 1001){
                    if (confirm("One of your fields is place off page, this is probably from an extension you have installed. Clicking ok will remove the field from the document this field is on page: " + draggable.attr('page')) == true) {
                      draggable.remove();
                    } else {
                      //text = "You canceled!";
                    }
                    throw new Error("One of your fields is place off page plese rectifiy this");
                    return;

                }
            });
            JSON.stringify(draggables);

            var actionUrl = yii.scriptUrl + '/docs/createSignable';
            var params = {
                name: $('#doc-name').val(),
                fieldInfo: draggables,
                file: documentId,
                template: $("#template-checkbox").prop("checked")
            };

            if (modelId && signDocId) {
                signDocNum--;
                signDocNum--;
                actionUrl = yii.scriptUrl + '/x2sign/QuickSetupEmail/?id=' + envelopeId + '&signDocNum='+signDocNum;
                params.signDocId = signDocId;
            }

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: params,
                success: function(data) {
                    window.location.replace(data);
                }
            });
        }
    });

    $('#create-button').click(function(e) {
        e.preventDefault();
    
        // Remove error messages and error class
        // from anything to prevent error message from being duplicated
        $('.error').removeClass('error');
        $('.errorMessage').remove();
    
        var errors = false;
        if($.trim($('#doc-name').val()).length == 0) {
            $('#doc-name').addClass('error');
            $('[for=\'X2SignDocs_name\']').addClass('error');
            $('#doc-name').after('<div class=\'errorMessage\'>Name cannot be blank</div>');
            errors = true;
        }
        if(!documentId) {
            $('#Media_name').addClass('error');
            $('[for=\'X2SignDocs_document\']').addClass('error');
            $('#Media_name').after('<div class=\'errorMessage\'>Please select document</div>');
            errors = true;
        }
        if($('#document').children().length == 0) {
            $('#create-button').before('<div class=\'errorMessage\'>No fields placed on the document</div>');
            errors = true;
        }
    
        if(!errors) {
            var draggables = [];
            var recipient = '';
            var documentChildren = $('#document').children();
            documentChildren.each(function() {
                var currentElement = $(this);
                recipient = $('#choose-recipient option[value=\'' + currentElement.css('background-color') + '\']').text();
                var value;
                if(currentElement.attr('id').includes('Checkbox'))
                    value = currentElement.find('input').is(':checked').toString();
                else
                    value = currentElement.find('input, textarea').val();
                
                draggables.push({
                    'id': currentElement.attr('id'),
                    'page': currentElement.attr('page'),
                    'top': currentElement.css('top'),
                    'left': currentElement.css('left'),
                    'value': value,
                    'width': currentElement.width(),
                    'height': currentElement.height(),
                    'req': currentElement.attr('req'),
                    'read-only': currentElement.attr('read-only'),
                    'numberlimited': currentElement.attr('numberlimited'),
                    'minvalue': currentElement.attr('minvalue'),
                    'maxvalue': currentElement.attr('maxvalue'),
                    'recip': recipient,
                    'DataKey': currentElement.attr('datakey'),
                });
                if(parseFloat(currentElement.css('left')) > 1001){
                    if (confirm("One of your fields is place off page, this is probably from an extension you have installed. Clicking ok will remove the field from the document this field is on page: " + currentElement.attr('page')) == true) {
                      currentElement.remove();
                    } else {
                      //text = "You canceled!";
                    }
                    throw new Error("One of your fields is place off page plese rectifiy this");
                    return;

                }
            });
            JSON.stringify(draggables);
    
            var actionUrl = yii.scriptUrl + '/docs/createSignable';
            var params = {
                name: $('#doc-name').val(),
                fieldInfo: draggables,
                file: documentId
            };
            if (modelId && signDocId) {
                actionUrl = yii.scriptUrl + '/docs/updateSignable/' + modelId;
                params.signDocId = signDocId;
            }
    
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: params,
                success: function(data) {
                    data = JSON.parse(data);
                    let redirectUrl = yii.scriptUrl + '/docs/viewSignable?' + $.param({id: data['id']});
                    window.location.replace(redirectUrl);
                }
            });
        }
    });

    $('#Media_name').change(function() {
        if($('#MediaModelId').val() === '')
            return; 
        
        // Stop user from being able to select document to make template
        // while the document is loaded
        $(this).prop('disabled', true);
    
        // Clear PDF if user chooses a different PDF to use for a template
        $('#document').empty();
    
        // If user attempts selecting prompt for document,
        // clear the canvas
        if($(this).val().length == 0) {
            $('#page_num').empty();
            $('#page_count').empty();
            canvas = document.getElementById('pdf');
            ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            documentId = null;
            $('.add-fields').hide();
            $('.field-options').hide();
            $(this).prop('disabled', false);
            return;
        }
    
        let baseUrl = yii.scriptUrl + '/x2sign/getFile';
        $.ajax({
            url: baseUrl,
            type: 'GET',
            data: {
                id: $('#MediaModelId').val(), 
                render: 0
            },
            success: function(data) {
                $('#MediaModelId').val(data);
                pdfDoc = null,
                    url = baseUrl + '?id=' + data,
                    pageNum = 1,
                    pageRendering = false,
                    pageNumPending = null,
                    scale = 2,
                    canvas = document.getElementById('pdf'),
                    ctx = canvas.getContext('2d');
    
                documentId = $('#MediaModelId').val();
                renderPDF(url);
    
                $('.add-fields').show();
            },
            error: function(xhr) {
                $('#Media_name').prop('disabled', false);
                //console.log(xhr);
                alert(xhr.responseText);
            }
        });
    });

    $('#choose-recipient').change(function() {
        $('span.color-preview').css('background-color', $(this).val());
        var color = $(this).val();
    
        $('#document').find('.active').css('background-color', color);
        var placeHolder = $('#document').find('.active').find(".x2-sign-input").attr('placeholder');
        if(placeHolder == "Name"){
            $('#document').find('.active').find(".x2-sign-input").val($("#choose-recipient option:selected").text());

        }
        if(placeHolder == "Email"){
            $('#document').find('.active').find(".x2-sign-input").val($("#choose-recipient option:selected").attr("Email"));
        }
        //check if there is a parther, if so radio and recolor all
        var part = $('#document').find('.active').attr('partner');
        if (typeof part !== 'undefined' && part !== false) {
            var ids = JSON.parse($('#document').find('.active').attr('partner'));
            ids.forEach((element) => {
                $('#Radio-' + element).css('background-color', color);
            });

        }

    });
    
    $('#is-required').change(function () {
        var checked = this.checked;
        checked ? $('#document').find('.active').attr('req', 1) : $('#document').find('.active').attr('req', 0);
    });
    
    $('#read-only').change(function () {
        var checked = this.checked;
        checked ? $('#document').find('.active').attr('read-only', 1) : $('#document').find('.active').attr('read-only', 0);
    });

    //check for the number fields
    $('#DisallowNumber').change(function () {
        var checked = this.checked;
        $('#document').find('.active').attr('numberLimited', checked);
        if(checked){
            displayMaxMin();
            $('#document').find('.active').attr('numberLimited', 1);

        }else{ 
            $('#document').find('.active').attr('numberLimited', 0);
            hideMaxMin();

        }
    });

    $('#MinValue').change(function () {
        
        $('#document').find('.active').attr('MinValue', $('#MinValue').val());
    });

    $('#MaxValue').change(function () {
        $('#document').find('.active').attr('MaxValue', $('#MaxValue').val());
    });





    $('#conditional').change(function () {
        var checked = this.checked;
        checked ? $('#document').find('.active').attr('conditional', 1) : $('#document').find('.active').attr('conditional', 0);
        displayDependentFields(checked);
        displayTriggerConditions(checked);
        displayShowHide(checked);
    });

    $('#dependent-fields').change(function () {
        $('#document').find('.active').attr('dependent-fields', $('#dependent-fields').find(":selected").val());
    });
    
    $('#trigger-condition').change(function () {
        $('#document').find('.active').attr('trigger-condition', $('#trigger-condition').find(":selected").val());
    });
    
    $('#show-hide').change(function () {
        $('#document').find('.active').attr('show-hide', $('#show-hide').find(":selected").val());
    });
    
    $( '#AddAtts' ).click(function() {
        $('#document').find('.active').find('input, textarea').val( $('#document').find('.active').find('input, textarea').val() + '{' + $('#ModelFields').val() + '}' );
    });  

    //$('#select-option').change(function () {
    //    $('#document').find('.active').attr('selectedOptionId', $('#select-option').val());
    //});

    $('#visibility').change(function () {
        var selectedValue = $(this).val();
        $('#document').find('.active[visibility]').attr('visibility', selectedValue);
    });

    $('#autocomplete-field').change(function () {
        var selectedValue = $(this).val();
        $('#document').find('.active[autocomplete]').attr('autocomplete', selectedValue);
    });
    
      $('#AddAttsOptions').click(function() {
        if($('#conditional').prop('checked')) {
            displayTriggerConditions(1);
        }
        var selectedOptionValue = $('#select-option').val();
    
        if (selectedOptionValue) {
            $('#document').find('.active').attr('selectedOptionId', $('#select-option').val());
            var activeSelectElement = $('#document').find('.active').find('select#Dropdown');
            var ajaxUrl = yii.baseUrl + "/index.php/x2sign/GetSelectedDropdown/" + selectedOptionValue;
    
            $.ajax({
                type: "GET",
                url: ajaxUrl,
                dataType: "json",
                success: function(data) {
                    var optionsData = JSON.parse(data.options);
                    $('#trigger-condition').empty();
                    activeSelectElement.empty();
                    var firstOptionAdded = false;

                    $.each(optionsData, function(key, value) {
                        var option = $('<option>', {
                            value: key,
                            text: value
                        });
                        activeSelectElement.append(option);
                        var option_clone = option.clone();
                         $('#trigger-condition').append(option_clone);
                        if (!firstOptionAdded) {
                            option.prop('selected', true);
                            firstOptionAdded = true;
                        }
                    });

                },
                error: function(xhr, textStatus, errorThrown) {
                    console.error("Error fetching dropdown data: " + errorThrown);
                },
            });
        } else {
            console.log("No option selected in #select-option dropdown.");
        }
    });

    function updateAutocomplete() {
        $.ajax({
            type: "GET",
            url: yii.baseUrl + "/index.php/x2sign/Users",
            dataType: "json",
            success: function (data) {
                $('#autocomplete-field').empty();
                $.each(data, function (index, option) {
                    var username = option.username;
                    var id = option.id;
                    if (username !== null) {
                        $("#autocomplete-field").append(
                            $("<option>", {
                                value: id != 'undefined' ? id : username,
                                text: username,
                            })
                        );
                    }
                });
            },
        });
    }
    updateAutocomplete();
    

    function displayDependentFields(checked) {
        $('#dependent-fields').children().remove().end();
        $(dropzone).find('.draggable').each(function() {
            if(($('#dependent-fields') && $('#dependent-fields').parent())) {
                if(checked) {
                    populateDependentFields($(this));                    
                    $('#dependent-fields').parent().show()
                } else {
                    $('#dependent-fields').parent().hide()
                }
            }
        });
        var selected_field = $(dropzone).find('.draggable.active');
        if(selected_field.attr('dependent-fields')) {
            $('#dependent-fields').val(selected_field.attr('dependent-fields'))
        }
        checked ? $('#document').find('.active').attr('dependent-fields', $('#dependent-fields').find(":selected").val()) : $('#document').find('.active').attr('dependent-fields', '');
    }


    function populateDependentFields(field) {
        if(!field.hasClass("active")) {
            let option = document.createElement("option");
            let optionExists = ($('#dependent-fields option[value=' + field.attr('id') + ']').length > 0);
            if(!optionExists) {
                option.text = field.attr('id');
                option.value = field.attr('id');
                $('#dependent-fields').append(option);
            }
        }
    }


    function displayTriggerConditions(checked) {
        $('#trigger-condition').children().remove().end();
        let selected_field = $(dropzone).find('.draggable.active');
        var selected_field_type = selected_field.attr('id').substring(0, selected_field.attr('id').indexOf('-'));
        if(($('#trigger-condition') && $('#trigger-condition').parent())) {
            if(checked) {
                populateTriggerConditions(selected_field, selected_field_type);
                $('#trigger-condition').parent().show()
            } else {
                $('#trigger-condition').parent().hide()
            }

            if(selected_field.attr('trigger-condition')) {
                $('#trigger-condition').val(selected_field.attr('trigger-condition'))
            }
        }
        checked ? $('#document').find('.active').attr('trigger-condition', $('#trigger-condition').find(":selected").val()) : $('#document').find('.active').attr('trigger-condition', '');
    }


    function populateTriggerConditions(field, field_type) {
        var options = [];

        if(field_type == 'Checkbox' || field_type == 'Radio') {
            options = ['checked', 'unchecked'];
        }
        if(field_type == 'YesNo' ) {
            options = ['Yes', 'No'];
        }

        if(field_type == 'Dropdown') {
            field.find('#Dropdown option').each(function() {
                if($(this).val()) {
                    options.push($(this).val());
                }
            });
        }

        options.forEach((option) => {
            let option_el = document.createElement("option");
            let optionExists = ($('#trigger-condition option[value="' + option + '"]').length > 0);
            if(!optionExists) {
                option_el.text = option;
                option_el.value = option;
                $('#trigger-condition').append(option_el);
            }
        });

    }


    //these function will be for showing number max and mids and setting them to a field
    function displayNumberFields(){
        $('#numberCheck').show();
        //check if the active record is a number limited
        if($(dropzone).find('.draggable.active').attr("numberLimited") == 1){
            $('#numberOptions').show();
            $('#DisallowNumber').prop('checked', true);
            //now try and get the values from the field for max and min 
        }else{
            $('#numberOptions').hide();
            $('#DisallowNumber').prop('checked', false);
        }
        //lastly set the min max from the active record
        $('#MinValue').val($('#document').find('.active').attr('MinValue'));
         $('#MaxValue').val($('#document').find('.active').attr('MaxValue'));

    }

   function hideNumberFields(){
        $('#numberCheck').hide();
        //check if the active record is a number limited
        $('#numberOptions').hide();
        $('#MinValue').val('');
        $('#MaxValue').val('');



    }


    function displayMaxMin(){
        $('#numberOptions').show();

    }

    function hideMaxMin(){
        $('#numberOptions').hide();
    }


    function displayShowHide(checked) {
        let selected_field = $(dropzone).find('.draggable.active');
        if(($('#show-hide') && $('#show-hide').parent())) {
            checked ? $('#show-hide').parent().show() : $('#show-hide').parent().hide();
            if(selected_field.attr('show-hide')) {
                $('#show-hide').val(selected_field.attr('show-hide'));
            }
        }
        checked ? $('#document').find('.active').attr('show-hide', $('#show-hide').find(":selected").val()) : $('#document').find('.active').attr('show-hide', '');
    }

   /**
     * Uses canvas.measureText to compute and return the width of the given text of given font in pixels.
     * 
     * @param {String} text The text to be rendered.
     * @param {String} font The css font descriptor that text is to be rendered with (e.g. 'bold 14px verdana').
     * 
     * @see https://stackoverflow.com/questions/118241/calculate-text-width-with-javascript/21015393#21015393
     */
    function getTextWidth(text, font) {
        // re-use canvas object for better performance
        var canvas = getTextWidth.canvas || (getTextWidth.canvas = document.createElement('canvas'));
        var context = canvas.getContext('2d');
        context.font = font;
        var metrics = context.measureText(text);
        return metrics.width;
    }


    $(document).ready(function() {
        // check where the shoppingcart-div is  

        $(window).scroll(function () {
            totalHight = 0;
            $('.x2-sign-doc').each(function(i, obj) {
                totalHight = totalHight + $(this).height();
            });

            var scrollTop = $(window).scrollTop(); // check the visible top of the browser  
            // add in a space to start 
            var extraSpace = 0;
            scrollTop = scrollTop - extraSpace;
            if(totalHight > scrollTop + $("#add-fields div:first-child").height() && scrollTop > 0)  $('#add-fields').css('margin-top', scrollTop);
            $('#add-fields').css('margin-top', scrollTop); // temp fix for when PDFs with 200 pages are uploaded and the field bank stops scrolling with user scroll

        });
    });
}

//fuction to add more radio buttons 
function addRadio (parent) {
    var ids = JSON.parse($(parent).attr('partner'));
    var myId = $(parent).attr('id');
    myId = Number(myId.substring(myId.indexOf('-') + 1));
    myId = [myId];
    createDraggable('Radio', myId.concat(ids));
    ids = ids.concat([draggableCounter]);
    ids.forEach((element) => {
        //console.log(element);
        var idsAdd = [];
        //get the ids for partners
        ids.forEach((id) => {
            if(id != element) idsAdd.push(id);
        });
        $("#Radio-" + element).attr('partner', JSON.stringify(myId.concat(idsAdd)));
        
    });
    $(parent).attr('partner', JSON.stringify(ids));
}

//function to unselect a radio button
function radioUnselect(parent) {
    var ids = JSON.parse($(parent).attr('partner'));
    var myId = $(parent).attr('id');
    $(parent).find('input').prop('checked', false);
    myId = Number(myId.substring(myId.indexOf('-') + 1));
    myId = [myId];
    ids.forEach((element) => {
        //console.log(element);
        $("#Radio-" + element).find('input').prop('checked', false);

    });
    $(parent).attr('partner', JSON.stringify(ids));
}


/**
 * Add functionality to the 'Next' and 'Prev' buttons for the PDF
 */
function navInit () {
    document.getElementById('prev').addEventListener('click', onPrevPage);
    document.getElementById('next').addEventListener('click', onNextPage);
}

/**
 * Render multiple pages simultaneously
 */
function renderPages(pdfDoc) {
    numPages = pdfDoc.numPages;
    $('#thumbnail-container').prepend(`<h2 class="document-name"> ${$("#doc-name").val()} </h2>`);
    
    var promises = [];

    for (var num = 1; num <= numPages; num++) {
        pdfDoc.getPage(num).then(function (page) {
            promises.push(renderThumbnail(page, 0.5));
            promises.push(renderPage(page));
        });
    }
    
    Promise.all(promises).then(function () {
        requestAnimationFrame(() => {
            // Code to be executed after rendering
            // This will run in the next animation frame
    
            $("#thumbnail-container").on("click", ".delete-btn", handleThumbnailDeleteClick);
    
            $(`#thumbnail-container`).on("click", ".thumbnail-canvas", handleThumbnailClick);
    
            let fixmeTop = $('#thumbnail-container').offset().top;
    
            window.addEventListener('scroll', () => makeSidebarFixed(fixmeTop));
    
            // window.addEventListener('scroll', debounce(showActiveThumbnailOnScroll, 150), { passive: true });
        });
    });
}

function renderPage(page) {
    var viewport = page.getViewport({scale: scale});
    var wrapper = document.createElement("div");
    wrapper.classList.add('canvas-wrapper');
    wrapper.id = 'page-' + (page.pageIndex + 1);
    var newCanvas = document.createElement('canvas');
    $(newCanvas).attr('data-page-num', page.pageIndex + 1);
    //newCanvas.id = 'page-' + (page.pageIndex + 1);
    newCanvas.classList.add('x2-sign-doc');
    
    var ctx = newCanvas.getContext('2d');
    var renderContext = {
        canvasContext: ctx,
        viewport: viewport
    };
    newCanvas.height = viewport.height;
    newCanvas.width = viewport.width;
    wrapper.appendChild(newCanvas);
    $('#document')[0].appendChild(wrapper);

    $(wrapper).droppable({
        drop: function(event, ui) {
            $(ui.draggable).css('top', parseInt($(ui.draggable).offset().top) - parseInt($(this).offset().top));
            $(this).append(ui.draggable);
        },
        over: function(event, ui) {
            $(ui.draggable).attr('page', $(this).children('canvas').attr('data-page-num'));
            //console.log($(ui.draggable).css('top'));
        },
        tolerance: 'fit'
    });
    page.render(renderContext);
}

function renderThumbnail(page, scalesize) {
    const viewport = page.getViewport({ scale: 1 });
    const canvas = document.createElement("canvas");
    const canvasContainer = document.createElement("div");

    $(canvasContainer).addClass("thumbnail-canvas").append(canvas);
    $(canvasContainer).attr("data-index", page.pageIndex + 1);

    (page.pageIndex + 1 == 1) ? $(canvasContainer).addClass("active") : null;

    $(canvasContainer).append(
        `<div class="thumbnail-footer">
            <span class="page-index">${page.pageIndex + 1}</span>
            <span class="delete-btn"><i class="fa fa-trash"></i></span>
        </div>`
    );

    $("#thumbnail-container").append(canvasContainer);

    canvas.width = viewport.width * scalesize;
    canvas.height = viewport.height * scalesize;

    const scale = Math.min(
        canvas.width / viewport.width,
        canvas.height / viewport.height
    );

    return page
        .render({
            canvasContext: canvas.getContext("2d"),
            viewport: page.getViewport({ scale: scale }),
        }).promise;
}

function debounce(func, delay) {
    let timeoutId;

    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

let shouldHandleScrollEvent = true;

function handleThumbnailClick() {
    shouldHandleScrollEvent = false;

    $(`.thumbnail-canvas`).removeClass("active");
    $(this).addClass("active");

    const index = $(this).attr("data-index");

    let canvas = document.querySelector(`.canvas-wrapper canvas[data-page-num="${index}"]`);

    // Calculate the target scroll position
    let targetScrollPosition = window.pageYOffset + canvas.getBoundingClientRect().top;

    window.scrollTo({
        top: targetScrollPosition,
        behavior: "smooth"
    });

    const tolerance = 1;

    // listen to the scroll event on the container to detect when the scrolling is done
    window.addEventListener("scroll", function checkScrollPosition() {
        // check if the scrolling has reached its target position
        if (Math.abs(window.pageYOffset - targetScrollPosition) <= tolerance) {
            // set the flag to true to allow the scroll event to fire again
            shouldHandleScrollEvent = true;

            // remove the event listener
            window.removeEventListener("scroll", checkScrollPosition);
        }
    });
}

function handleThumbnailDeleteClick(e) {
    let index = parseInt($(this).parents(".thumbnail-canvas").attr("data-index")) - 1;
    deletePage(index);
}

function showActiveThumbnailOnScroll() {
    // get all the sections within the container
    const sections = document.querySelectorAll(".canvas-wrapper canvas");

    // get the element of thumbnail container
    const thumbnailContainer = document.querySelector("#thumbnail-container");

    // check if the scroll event should be handled
    if (!shouldHandleScrollEvent) {
        return;
    }

    // get the current scroll position of the container
    const scrollPosition = window.pageYOffset;

    // loop through each section to check if it's in view
    sections.forEach((section) => {
        // get the position of the section relative to the top of the container
        const sectionPosition = window.pageYOffset + section.getBoundingClientRect().top;
        const index = $(section).data("page-num");

        /* 
            * This constant is because we want to add a buffer of 100px 
            * between the top of the container and the top of the active section. 
            * This means that the active thumbnail will be highlighted 
            * when it's about 100px away from the top of the container, 
            * rather than exactly at the top.
        */
        const buffer = 100;

        // Check if the section is in view
        if (
            // check if the top of the section is above the bottom of the container, meaning it's visible in the container.
            sectionPosition <= scrollPosition + buffer &&
            // checks if the bottom of the section is below the top of the container, meaning it's not scrolled out of view yet.
            sectionPosition + section.offsetHeight + 20 > scrollPosition + buffer
        ) {
            // if the section is in view, add the active class to its corresponding thumbnail
            const thumbnail = document.querySelector(`.thumbnail-canvas[data-index="${index}"]`);
            thumbnail.classList.add("active");

            scrollToThumbnail(thumbnailContainer, thumbnail);
        }
        else {
            // if the section is not in view, remove the active class from its corresponding navigation
            const thumbnail = document.querySelector(`.thumbnail-canvas[data-index="${index}"]`);
            thumbnail.classList.remove("active");
        }

        if (index == 1 && sectionPosition > scrollPosition) {
            const thumbnail = document.querySelector(`.thumbnail-canvas[data-index="${index}"]`);
            thumbnail.classList.add("active");

            scrollToThumbnail(thumbnailContainer, thumbnail);
        }
    });
}

function makeSidebarFixed(fixmeTop) {
    var windowHeight = $(window).height();
    var currentScroll = $(window).scrollTop();
    var documentOffsetBottom = $('#document').offset().top + $('#document').outerHeight();
    
    if (currentScroll >= fixmeTop) {
        $('#thumbnail-container').css({
            position: 'fixed',
            bottom: 'unset',
            top: '50px'
        });
    }
    
    if (currentScroll + windowHeight >= documentOffsetBottom) {
        $('#thumbnail-container').css({
            position: 'absolute',
            bottom: '0',
            top: 'unset'
        });
    }
    
    if (currentScroll < fixmeTop && currentScroll + windowHeight < documentOffsetBottom) {
        $('#thumbnail-container').css({
            position: 'static',
            top: '50px',
            bottom: 'unset'
        });
    }
}

function scrollToThumbnail(thumbnailContainer, thumbnail) {
    // Calculate the target scroll position
    const thumbnailRect = thumbnail.getBoundingClientRect();
    const containerRect = thumbnailContainer.getBoundingClientRect();
    const targetScrollPosition = thumbnailContainer.scrollTop + (thumbnailRect.top - containerRect.top);

    thumbnailContainer.scrollTo({
        top: targetScrollPosition,
        behavior: "smooth"
    });
}

/**
 * Get page info from document, resize canvas accordingly, and render page.
 * @param num Page number.
 */
/* function renderPage(num) {
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
    document.getElementById('page_num').textContent = num;
} */

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
    $('#document').children().each(function() {
        if($(this).attr('page') != pageNum)
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
    $('#document').children().each(function() {
        if($(this).attr('page') != pageNum)
            $(this).hide();
        else
            $(this).show();
    });
}

  
/**
 * Deletes a page from the PDF document on the server.
 * @param {number} pageIndex The index of the page to delete.
 * @returns {Promise} A promise that is resolved when the page is deleted.
*/
function deletePage(pageIndex) {
    let url=yii.scriptUrl + '/x2sign/x2sign/getFile/id/' + fileId;
    function fetchURLContent(url) {
        return fetch(url)
          .then(response => response.arrayBuffer());
      }    
    fetchURLContent(url)
    .then(arrayBuffer => {
        const { PDFDocument } = PDFLib;
        // Load the PDF document
        PDFDocument.load(arrayBuffer)
        .then(pdfDoc => {
            let pageIndexToDelete = pageIndex;
            const pageCount = pdfDoc.getPageCount();    
            if (pageIndexToDelete >= 0 && pageIndexToDelete < pageCount) {
            // Remove the page
            pdfDoc.removePage(pageIndexToDelete);
            // Save the modified PDF as a Blob
            pdfDoc.save()
                .then(newPdfBytes => {
                const modifiedPdfBlob = new Blob([newPdfBytes], { type: 'application/pdf' });
                // Create a FormData object and append the PDF Blob
                const formData = new FormData();
                formData.append('pdfFile', modifiedPdfBlob, 'modified_file'+Date.now()+'.pdf');
                //Get the id in current browser url
                let url = window.location.href;
                var parsedUrl = new URL(url);

                // Extract the id parameter using URLSearchParams
                var searchParams = new URLSearchParams(parsedUrl.search);
                var id = searchParams.get('id');
                var signDocNum = searchParams.get('signDocNum');

                if (signDocNum === null || signDocNum === undefined) {
                    signDocNum = 0; 
                }

                if(id===null || id=== undefined){
                    id = url.substring(url.lastIndexOf('/') + 1);
                }
                
                formData.append('YII_CSRF_TOKEN', x2.csrfToken);
                $.ajax({
                    url: yii.scriptUrl+'/x2sign/x2signupdate?envelopeId='+id +'&signDocNum='+signDocNum,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                       fileId=response;

                        $(`.canvas-wrapper canvas[data-page-num="${pageIndex + 1}"]`).parent().remove();
                        $(`.thumbnail-canvas[data-index="${pageIndex + 1}"]`).remove();

                        $(".canvas-wrapper").each(function (index, element) {
                            $(element).attr("id", `page-${index + 1}`);
                            $(element).find("canvas").attr("data-page-num", index + 1);
                            $($(".thumbnail-canvas")[index]).attr("data-index", index + 1);
                            $($(".thumbnail-canvas")[index]).find('.page-index').html(index + 1);
                        });

                        $(`.thumbnail-canvas[data-index="${pageIndex + 1}"]`).addClass("active");
                       
                        // $(window).off("scroll", showActiveThumbnailOnScroll);
                        // renderPDF(yii.scriptUrl + '/x2sign/x2sign/getFile/id/' + response);

                        // $(window).scrollTop(0);
                        // $('#thumbnail-container').css({
                        //     position: 'static',
                        //     top: '50px',
                        //     bottom: 'unset'
                        // });
                    },
                        error: function(jqXHR, textStatus, errorThrown) {
                        console.error('PDF upload failed:', errorThrown);
                    }
                });
                })
                .catch(error => {
                console.error('Error saving modified PDF:', error);
                });
            } else {
            console.error('Invalid page index.');
            }
        })
        .catch(error => {
            console.error('Error loading PDF:', error);
        });
    })
    .catch(error => {
        console.error('Error fetching URL content:', error);
    });
}
/**
 * Asynchronously downloads PDF.
 */
function renderPDF(url) {  
    pdfjsLib.disableWorker = true;
    pdfjsLib.getDocument(url).promise.then(renderPages);
    // Re-allow user to select a different document once current is loaded
    // (avoids javascript Promise error)
    if (mode == 'create' || mode == 'update')
        $('#Media_name').prop('disabled', false);
    /*pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        //document.getElementById('page_count').textContent = pdfDoc.numPages;

        renderPages(pdfDoc);

        // Initial/first page rendering
        //renderPage(pageNum);

        // Re-allow user to select a different document once current is loaded
        // (avoids javascript Promise error)
        if (mode == 'create' || mode == 'update')
            $('#Media_name').prop('disabled', false);
    });*/
}

function loadPdf (mediaId, fieldInfo) {
    pdfDoc = null,
        scale = 2,
        url = yii.scriptUrl + '/x2sign/x2sign/getFile/id/' + mediaId;
        fileId=mediaId;
        /*pageNum = 1,
        pageRendering = false,
        pageNumPending = null,
        scale = 2,
        canvas = document.getElementById('pdf'),
        ctx = canvas.getContext('2d');*/
    renderPDF(url);
    
    // Solution for "waiting" until the canvas elements are completely rendered in DOM
    // then subsequently adding the field to the correct page
    // Adapted from: https://stackoverflow.com/questions/16149431/make-function-wait-until-element-exists/47776379 
           var highPage = 1;
        if(fieldInfo.length > 0) {
            for(i = 0; i < fieldInfo.length; i++) {
                var field = fieldInfo[i];
                if(parseInt(highPage) < parseInt(field["page"])) highPage = parseInt(field["page"]);
            }
        }

    var checkExist = setInterval(function() {
        if ($("#page-" + highPage).length) {
            documentId = mediaId;
            $('.add-fields').show();

            if(fieldInfo !== undefined && fieldInfo !== null && fieldInfo.length !== 0)
                showFields(fieldInfo);
            clearInterval(checkExist);
        }
    }, 3);
 
    /*documentId = mediaId;
    $('.add-fields').show();

    if(fieldInfo !== undefined && fieldInfo !== null && fieldInfo.length !== 0)
        showFields(fieldInfo);*/
}

function createSignable () {
    mode = 'create';
    editInit();
    //navInit();
}

function updateSignable (modelId, signDocId, mediaId, pdfName, fieldInfo, envelopeId, signDocNum) {
    mode = 'update';
    editInit(modelId, signDocId, envelopeId, signDocNum);
    //navInit();
    $('#Media_name').val(pdfName);
    loadPdf(mediaId, fieldInfo);
}

function viewSignable (mediaId, fieldInfo) {
    mode = 'view';
    //navInit();
    loadPdf(mediaId, fieldInfo);
}

// Quick Send Functions
function createQuickSignDoc(data, envelopeId) {
    $.ajax({
        url: yii.absoluteBaseUrl + '/index.php/x2sign/ajaxGenSignDocs/envelopeId/' + envelopeId,
        type: 'POST',
        data: {
            mediaId: data
        },
        success: function (data) {
            //console.log('successful creation of signdoc');
        },
        error: function(data) {
            alert('Couldn\'t create sign doc!');
            //console.log(data);
        }
    });
}

//Toggle PDF sidebar
$('#toggle').on("click", function(event) {
    event.preventDefault(); // Prevent default form submission behavior

    $('#full-width-pdf').toggleClass('col-8 col-10');
    $('#pdf-right-sidebar').toggleClass('col-2');
    $('#pdf-right-sidebar').toggle();
  });

function isValidJson(jsonStr) {
    try {
        JSON.parse(jsonStr);
    }
    catch (e) {
        return false;
    }
    return true;
}

function getDataLabels(envelopeId, callback) {
    $.ajax({
        url: yii.scriptUrl + '/x2sign/getDataLabel?id=' + envelopeId,
        type: 'GET',
        success: function (response) {
            if (isValidJson(response)) {
                callback(JSON.parse(response));
            }
        },
        error: function (data) {
            showAlert('error', 'Some error occured while fetching the data labels');
        }
    });
}

function showDataLabels(dataLabel, envelopeId) {
    if (!$(".data-label-form").length) {
        let elements = $(`
            <form class="data-label-form">
                <input id="datalabel-key" name="key" type="text" placeholder="Datalabel Key" required />
                <input id="datalabel-value" name="value" type="text" placeholder="Datalabel Value" required />

                <div class="btn-group">
                    <button type="button" class="save-btn" onclick="createDataLabel(event, ${envelopeId})">Save</button>
                    <button type="button" class="cancel-btn" onclick="handleDataLabelCancel(this)">Cancel</button>
                </div>
            </form>

            <select class="data-label-dropdown" ></select>
            <input id="attach-data-label" onclick="addDataLabelToField();" name="yt7" type="button" value="Add Data Label" class="active">
            <input id="remove-data-label" onclick="removeDataLabelToField();" name="yt8" type="button" value="Remove Data Label" class="active">
        `);

        $(dataLabel).parent().append(elements);
        renderDataLabelDropdown(envelopeId);
    }
    else {
        $(".data-label-form, .data-label-dropdown").show();
        $('#attach-data-label').show();
        $('#remove-data-label').show();
    }

    $(dataLabel).addClass('active');
}

function createDataLabel(event, envelopeId) {
    const form = $(event.currentTarget).parents(".data-label-form")[0];

    const formData = {};
    const dataArray = $(form).serializeArray();

    $.each(dataArray, function (index, item) {
        formData[item.name] = item.value;
    });

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const data = {
        dataLabel: formData.key,
        dataValue: formData.value,
    };

    $.ajax({
        url: yii.scriptUrl + '/x2sign/saveDataLabel?id=' + envelopeId,
        type: 'POST',
        data,
        success: function (response) {
            if (isValidJson(response)) {
                const res = JSON.parse(response);

                if (res.duplicate) {
                    showAlert('error', "This key is already exist. please enter unqiue one!");
                    return;
                }

                showAlert('success', "Your DataLabel has been Saved Successfully!");

                $(form).trigger('reset');

                renderDataLabelDropdown(envelopeId);
            }
        },
        error: function (data) {
            showAlert('error', 'Some error occured while creating the data labels');
        }
    });
}

function renderDataLabelDropdown(envelopeId) {
    getDataLabels(envelopeId, (datalabels) => {
        let optionsHTML = `<option value="">Select Data Label</option>`;

        datalabels.forEach(elem => {
            optionsHTML += `<option value="${elem.dataValue}">${elem.dataLabel}</option>`;
        });

        $('.data-label-dropdown').html(optionsHTML);
    });
}

function addDataLabelToField(){
    const input = $('#document').find('.active').find('input')[0];
    $(input).val(event.target.value);
    //here I will set a new attr call dataKey to save the related data value
    $('#document').find('.active').attr('dataKey', $( ".data-label-dropdown option:selected" ).text());
    $('#document').find('.active').find('input').val( $( ".data-label-dropdown option:selected" ).val());
    //triggerInputEvent(input);
}

function removeDataLabelToField(){
    const input = $('#document').find('.active').find('input')[0];

    //here I will set a new attr call dataKey to save the related data value
    $('#document').find('.active').removeAttr('dataKey');
    $('#document').find('.active').find('input').val('');
    //triggerInputEvent(input);
}

function handleDataLabelChange(event, activeDraggable) {
    const input = activeDraggable.find('input')[0];

    $(input).val(event.target.value);
    //here I will set a new attr call dataKey to save the related data value
    $('#document').find('.active').attr('dataKey', $( ".data-label-dropdown option:selected" ).text());
    triggerInputEvent(input);
}

function handleDataLabelCancel(elem) {
    $(elem).parents(".data-label-form").trigger('reset');
}

function triggerInputEvent(element) {
    if (typeof element.dispatchEvent === 'function') {
        var event = new Event('input', { bubbles: true, cancelable: true });
        element.dispatchEvent(event);
    } 
    else if (typeof element.fireEvent === 'function') {
        var event = document.createEventObject();
        element.fireEvent('oninput', event);
    }
}

function showAlert(type, message, dismissable) {
    let alertBox = $('<div>').addClass('alert');

    let closeButton = dismissable ? $('<button>').attr('type', 'button').addClass('close').html('&times;') : '';

    if (type === 'success') {
        alertBox.addClass('alert-success');
        var iconClass = 'fa fa-check mr-2';
    } 
    else {
        alertBox.addClass('alert-error');
        var iconClass = 'fa fa-times mr-2';
    }

    alertBox.append(`<i class="${iconClass}" aria-hidden="true"></i>${message}${closeButton}`);

    alertBox.hide().appendTo('body').fadeIn();

    if (dismissable) {
        alertBox.find('.close').on('click', function () {
            alertBox.fadeOut(() => {
                alertBox.remove();
            });
        });
    } 
    else {
        setTimeout(function () {
            alertBox.fadeOut(() => {
                alertBox.remove();
            });
        }, 2000);
    }
}
