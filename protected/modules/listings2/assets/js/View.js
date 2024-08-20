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


 var viewDoc = function (mediaId, fields) {
         $('#pdf-nav').show();
         viewSignable(mediaId, fields);
         $('#docViewModal').modal('show');
 }



 function show_rename_modal(mediaId) {
     console.log(mediaId);
     $('#renameDocModal').modal('show');
     $('#renameBtn').data('mediaid', mediaId);
     $('#renameBtn').on('click', function() {
         $.ajax({
            url: yii.absoluteBaseUrl + '/index.php/media/updateMedia/id/'+mediaId+'?name='+$('#renameInput').val(),
            type: 'UPDATE',
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

 function delete_document(id) {
     console.log(id)
     var deleteConfirm = confirm('Are you sure you want to delete this document? This cannot be undone.');
     if (deleteConfirm == true) {
         var throbber = auxlib.pageLoading();
         $.ajax({
            url: yii.scriptUrl + "/listings2/deleteDocument/id/" + id,
            type: 'GET',
            success: function(data) {
                location.reload();
                $.fn.yiiGridView.update('documents-grid');
                auxlib.pageLoadingStop();
            },
            error: function(data) {
                console.log(data);
            }
         });
     }
 }

 function nda_download(url) {
     var a = document.createElement('a');
     a.href = url;
     a.click();
     a.remove();
 }


/* ================= SETTINGS ============= */
