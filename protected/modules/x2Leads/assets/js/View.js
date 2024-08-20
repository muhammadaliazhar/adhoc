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
 * @author Justin Toyomitsu <justin@x2engine.com>, Peter Czupil <peter@x2engine.com>
 */

var csrfTokenRegex = /(?:^|.*;)\s*YII_CSRF_TOKEN\s*=\s*([^;]*)(?:.*$|$)/;
var csrfToken = document.cookie.replace (csrfTokenRegex, '$1');


var setUpDialog = function (id, title) {
    var dialog = $('<div />');
    dialog.prop('id', id);
    dialog.prop('title', title);
    return dialog;
}

var viewDoc = function (mediaId, fieldInfo) {
        $('#pdf-nav').show();
        viewSignable(mediaId, fieldInfo);
        $('#docViewModal').modal('show');
}

var setUpShareBody = function (url) {
    var body = '<div class="form-group col-auto">' + 
               '<center><label class="share-label">Share Link:</label></center>' +
               '<div class="input-group">' +
               '<center><input id="sharePropInput" style="margin: 0 !important; width: 20rem;" class="form-control" value="' + url + '"></input></center>' +
               '<div class="input-group-append"><i id="shareLinkCopy" onclick="copyToClipboard()" class="fa fa-clipboard input-group-text"></i></div>' +
               '</div><div style="display: none;" id="copySuccess" >Link Copied!</div></div>';

    return body;
}

function copyToClipboard() {
        var copyText = $("#sharePropInput");
        copyText.select();
        copyText[0].setSelectionRange(0, 99999);
        console.log(copyText);
        document.execCommand("copy");
        $('#copySuccess').slideDown(function() {
            setTimeout(function() {
                $('#copySuccess').slideUp();
            }, 5000);
        });
}

function shareProperty(id = null) {
    var dialog = setUpDialog('share_property', 'Share Link');
    var url = yii.absoluteBaseUrl + '/index.php/properties/publicView/' + id;
    dialog.dialog({
        modal: true,
        resizable: true,
        height: 150,
        width: 500,
    });
    var body = setUpShareBody(url);
    dialog.html(body);
    dialog.css('background', 'white');
}

function editProp(id = null) {
    var url = yii.scriptUrl + '/properties/update/id/' + id;
    window.location.href = url;    
}

/* ================ DOCS ================== */

function show_rename_modal(mediaId, propId) {
    $('#renameDocModal').modal('show');
    $('#renameBtn').data('mediaid', mediaId);
    $('#renameBtn').on('click', function() {
        $.ajax({
            url: yii.absoluteBaseUrl + '/index.php/media/update/'+mediaId+'?ajax=1',
            type: 'POST',
            data: {
                'Media': {
                    'name': $('#renameInput').val(),
                    'associationType': 'properties',
                    'associationId': propId,
                    'private': 0
                }
            },
            success: function(data) {
                $('#renameDocModal').modal('hide');
                $.fn.yiiGridView.update("documents-grid");
            },
            error: function(data) {
                $('#renameDocModal').modal('hide');
                console.log(data);
            }
        });
    });
}

function rename_document(id) {
    
}

function delete_document(id, elem) {
    var deleteConfirm = confirm('Are you sure you want to delete this document? This cannot be undone.');
    if (deleteConfirm == true) {
        var throbber = auxlib.pageLoading();
        $.ajax({
           url: yii.scriptUrl + "/properties/deleteDocument/id/" + id,
           type: 'POST',
           data: {
               docId: $(elem).data('docid')
           },
           success: function(data) {
               $.fn.yiiGridView.update('documents-grid');
               auxlib.pageLoadingStop();
           }
        });
    }
}

function replace_document(id, elem) {
    var deleteConfirm = confirm('Are you sure you want to replace this document? This cannot be undone.');
    if (deleteConfirm == true) {
        var throbber = auxlib.pageLoading();
        $.ajax({
           url: yii.scriptUrl + "/properties/replaceDocument/id/" + id,
           type: 'POST',
           data: {
               docId: $(elem).data('docid')
           },
           success: function(data) {
               $.fn.yiiGridView.update('documents-grid');
               auxlib.pageLoadingStop();
           }
        });
    }
}

function notify_viewers(propertyId) {
    $.ajax({
        url: yii.scriptUrl + "/properties/notifyViewers/id/" + propertyId,
        success: function(data) {
            console.log(data);
        },
        error: function(data) {
            console.log(data);
        }
    });
}

function property_download(url) {
    var a = document.createElement('a');
    a.href = url;
    a.click();
    a.remove();
}

function property_upload() {
    var fileUploader = x2.FileUploader.list['doc-upload'];
}

x2.FileUploader.on ('doc-upload', 'success', function(){
    x2.FileUploader.list['doc-upload'].toggle();
});

/* ================ DOCS ================== */
/* =============== OFFERS ================== */

function response_switch(response_type, street, name) {
    var subject_replace = '';
    var message_repalce = '';
    var subject = $('#response_subject');
    var message = $('#response_message');
    if(response_type == 'accept') {
        subject_replace = 'Your Offer on ' + street + ' has been Accepted';
        message_replace = "Hello " + name
                   + ",\n\n I'm happy to inform you that your offer on \n " + street
                   + "has been accepted. Congratulations,\n and I'll be in touch soon with next steps.";
    }
    if(response_type == 'message') {
        subject_replace = 'RE: Your Offer on ' + street;
        message_replace = '';
    }
    subject.val(subject_replace);
    message.val(message_replace);
}

function send_response() {
    var offer_form = $('#offer_respond_form');
    var datastring = offer_form.serialize();
    var url = yii.scriptUrl + '/properties/sendResponse';
    $.ajax({
        url: url,
        type: 'POST',
        data: datastring,
        success: function (data) {
            var dialog = $('#response_offer_dialog');
            dialog.dialog('close');
            dialog.remove();
        }
    });
}

function response_to_offer(propertyId, offerId) {
    var dialog = setUpDialog('response_offer_dialog', 'VIEWING OFFER');
    var url = yii.scriptUrl + '/properties/getResponseView/id/' + propertyId + '?offerId=' + offerId;
    $.ajax({
        url: url,
        type: 'GET',
        success: function (data) {
            dialog.dialog({
                modal:true,
                position: { my: "center", at: "center top", of: window },
                resizable:false,
                height: 550,
                width: 350,
            });
            dialog.html(data);
        }
    });
}

function get_detail_offer(offerId) {
    var dialog = setUpDialog('detail_offer_dialog', 'VIEWING OFFER');
    var url = yii.scriptUrl + '/properties/getDetailView/id/' + offerId;
    $.ajax({ 
        url: url,
        type: 'GET',
        success: function (data) {
            dialog.dialog({ 
                modal:true, 
                position: { my: "center", at: "center top", of: window },
                resizable:false,
                height: 800,
                width: 500,
            });
            dialog.html(data);
        }
    });
}

function remove_empty_offer(propertyId) {
    var dialog = $('#add_offer_dialog');
    dialog.dialog('close');
    dialog.remove();
    var empty_info = $('#empty-offer-card');
    var regular_view  = $('#offer_view');
    var url = yii.scriptUrl + '/properties/getView/id/' + propertyId;
    $.ajax({
        url: url,
        type: 'GET',
        success: function (data) {
            var not_empty_card = $(data);
            if(not_empty_card != null) {
                if(empty_info.length) {
                    empty_info.fadeOut(300, function(){ $(this).remove();});
                } else {
                    regular_view.fadeOut(300, function(){ $(this).remove();});
                }
                not_empty_card.fadeIn(300, function(){ $(this).appendTo($('#Offers'));});
            }
        }
    });
}

function send_offer(propertyId) {
    var offer_form = $('#offer_add_form');
    var datastring = offer_form.serialize();
    var url = yii.scriptUrl + '/properties/offers';
    $.ajax({
        url: url,
        type: 'POST',
        data: datastring,
        success: function (data) {
            remove_empty_offer(propertyId);
        }
    });
}

function openOfferAddDialog(body) {
    var dialog = setUpDialog('add_offer_dialog', 'ADD OFFER');
    dialog.dialog({
        modal:true,
        position: { my: "center", at: "center top", of: window },
        resizable:false,
        height: 800,
        width: 500,
    });
    dialog.html(body);
}

function add_offer_empty(propertyId) { //display modal
    var url = yii.scriptUrl + '/properties/getOffersForm/id/' + propertyId;

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            'propertyId': propertyId,
            YII_CSRF_TOKEN: csrfToken
        },
        success: function (data) {
            openOfferAddDialog(data);
        }
    });
}

/* =============== OFFERS ================== */
/* ================= SETTINGS ============= */

function property_mute(id) {
    var url = yii.scriptUrl + '/properties/mute/id/' + id;
    var mute = $('#settings-notification-button').is(':checked'); //if checked 1 | else 0
    $('#settings-notification-button').prop('disabled', true); //disable to so they can't spam it.
    
    $.ajax({
        url: url,
            type:'POST',
            data: {
                'mute' : mute,
                YII_CSRF_TOKEN: csrfToken
            },
            success: function (data) {
                if($('#settings-notification-button').is(':disabled')) {
                    $('#settings-notification-button').removeAttr("disabled"); //remove disabled on success
                }
                var response = JSON.parse (data);
                if(response.muted == 'false') {
                    alert('Un-Muted');
                } else {
                    alert('Muted');
                } 
            }
    });    
}

function property_delete(id) {
    var url = yii.scriptUrl + '/properties/delete/id/' + id;
    var returnUrl = yii.scriptUrl + '/properties/index';
    $.ajax({
        url: url,
            type:'POST',
            data: {
                'returnUrl' : returnUrl,
                YII_CSRF_TOKEN: csrfToken
            },
            success: function (data) {
                window.location.href = returnUrl;
            }
    });
}

function property_archive_or_unarchive(id, archive_status, unarchive_status, current) {
    var url = yii.scriptUrl + '/properties/status/id/' + id;

    if(current == archive_status) {
        var new_status = unarchive_status;
        var button_id = "settings-unarchive-button";
        var button = document.getElementById(button_id);
        var next_button = $('<button id="settings-archive-button" onclick="property_archive_or_unarchive(' 
                         + id + ',' + archive_status + ',' + unarchive_status + ',' + unarchive_status
                         + ')" class="white prop-button" style="width:12em;">ARCHIVE PROPERTY</button>');

    }else {
        var new_status = archive_status;
        var button_id = "settings-archive-button";
        var button = document.getElementById(button_id);
        var next_button = $('<button id="settings-unarchive-button" onclick="property_archive_or_unarchive('
                         + id + ',' + archive_status + ',' + unarchive_status + ',' + archive_status
                         +')" class="prop-button settings-unarchive-button" style="width:12em;">UNARCHIVE PROPERTY</button>');
    }

    var parentDiv = button.parentNode;

    $.ajax({
        url: url,
        type:'POST',
        data: {
            'new_status': new_status,
            YII_CSRF_TOKEN: csrfToken
        },
        success: function (data) {
            $('#' + button_id).fadeOut(300, function(){ $(this).remove();});
            next_button.fadeIn(300, function(){ $(this).appendTo(parentDiv);});
        }
    });
}

function viewer_edit(viewerId, propertyId) {
    var url = yii.scriptUrl + '/properties/viewerAction/id/' + propertyId;
    console.log('edit clicked.');
    console.log($('#viewerModal').modal({
        keyboard: false
    }));
    $.ajax({
        url: url,
        type:'POST',
        data: {
            'action': 'edit',
            'viewer': viewerId,
            YII_CSRF_TOKEN: csrfToken
        },
        success: function (data) {

        }
    });
}

function viewer_remind(viewerId, propertyId) {
    var url = yii.scriptUrl + '/properties/viewerAction/id/' + propertyId;
    $.ajax({
        url: url,
        type:'POST',
        data: {
            'action': 'remind',
            'viewer': viewerId
        },
        success: function (data) {

        }
    });
}

function viewer_remove(viewerId, propertyId) {
    var url = yii.scriptUrl + '/properties/viewerAction/id/' + propertyId;
    $.ajax({
        url: url,
        type:'POST',
        data: {
            'action': 'remove',
            'viewer': viewerId
        },
        success: function (data) {
            $.fn.yiiGridView.update('viewers-grid');
        }
    });
}
/* ================= SETTINGS ============= */
