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
 * @author Justin Toyomitsu <justin@x2engine.com> 
 */


include("protected/modules/x2sign/x2signConfig.php");

$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module', 'X2Sign Home')),
    array('label'=>Yii::t('module', 'X2Sign Send'), 'url' => 'x2sign/quickSend'),
    array('label'=>Yii::t('module', 'X2Sign Report'), 'url' => array('x2sign/report')),
    array('label'=>Yii::t('module', 'Create X2Sign Template'), 'url' => array('docs/createSignable')),
    array('label'=>Yii::t('module', 'Quick Send'), 'url' => array('x2sign/quickSend'))
));

// editor javascript files
Yii::app()->clientScript->registerPackage ('emailEditor');

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/index.css');

// bootstrap
Yii::app()->clientScript->registerCssFile("https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css");
Yii::app()->clientScript->registerScriptFile("https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js");

Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/FolderManager.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/x2sign.js');


Yii::app()->clientScript->registerScript('docsIndexJS',"
x2.folderManager = new x2.FolderManager (".CJSON::encode (array (
    'translations' => array (
        'createFolder' => Yii::t('docs', 'Create Folder'),
        'deleteFolderConf' =>
            Yii::t('docs', 'Are you sure you want to delete this folder and all of its contents?'),
        'deleteDocConf' => Yii::t('docs','Are you sure you want to delete this Doc?'),
        'folderDeleted' => Yii::t('docs', 'Folder deleted.'),
        'docDeleted' => Yii::t('docs', 'Doc deleted.'),
        'permissionsMissing' =>
            Yii::t('docs', 'You do not have permission to delete that Doc or folder.'),
    ),
    'urls' => array (
        'moveFolder' => Yii::app()->controller->createUrl('/x2sign/moveFolder'),
        'index' => Yii::app()->controller->createUrl('/x2sign/home'),
    ),
)).");
", CClientScript::POS_END);

Yii::app()->clientScript->registerScript('X2SignIndexJS', "
    if (x2.x2sign == undefined) x2.x2sign = {};

    x2.x2sign.reloadFolders = function () {
        var throbber$ = auxlib.pageLoading();
        $.ajax({
            type: 'GET',
            url: '" . Yii::app()->controller->createUrl('/x2sign/getFolders') . "',
            success: function (resp) {
                var folders = JSON.parse(resp);
                console.log(resp);
                x2.x2sign.clearFolders();
                x2.x2sign.loadFolders(folders);
                throbber$.remove();
            },
            error: function (resp) {
                console.log(resp);
                throbber$.remove();
            }
        });
    }

    x2.x2sign.clearFolders = function () {
        console.log('clear folders called');
        // Clear folder divs on left panel and in modal dropdown
        $('.folder-div').remove();
        $('#folder-names option').remove();
    }

    x2.x2sign.loadFolders = function (folders) {
       console.log('loadFolders called.');
       // add folder <a> and <option> elements
       $('#folder-names').append(`<option value=\"empty\" selected=\"selected\">select an option</option>`);
       folders.forEach(function (folder) {
           // load modal dropdown
           $('#folder-names').append(`<option value=\"` + folder['id'] + `\">` + folder['name'] + `</option>`)
           console.log(folder['id']);

           //load left panel links
           $('.folder-links-div').append(
               `
                <div class=\"m-3 folder-item\">
                    <h4 id=\"` + folder['id'] + `\" data-folder-id=\"` + folder['id'] + `\" class=\"folder-item link-item p-2\">&nbsp; ` + folder['name'] + `</h4>
                </div>
               `
           );
       })
       x2.x2sign.setupFolderOnClick()
    }

    x2.x2sign.setupFolderOnClick = function () {
        $('.folder-item').on('click', function () {
            let folderId = $(this).data('folder-id');
            $.fn.yiiGridView.update('x2sign-index-grid-view', {
                url: window.location.href,
                data: 'folderId='+folderId, // Word is what you are sending now
                complete: function () {
                    $('.move-btn').on('click', function () {
                        let envelopeId = $(this).data('envelope-id');
                        $('#move-folder-btn').data('envelope-id', envelopeId);
                        $('#move-folder-btn').attr('data-envelope-id', envelopeId);
                    });
                }
            })
        })
    }

    $('.move-btn').on('click', function () {
        let envelopeId = $(this).data('envelope-id');
        console.log('move-btn even triggered');
        console.log('envelopeId');
        console.log(envelopeId);
        $('#move-folder-btn').data('envelope-id', envelopeId);
        $('#move-folder-btn').attr('data-envelope-id', envelopeId);
        console.log('data-envelope-id');
        console.log($('#move-folder-btn').data('envelope-id'));
    });

    $('#move-folder-btn').on('click', function () {
        let envelopeId = $(this).data('envelope-id');
        let folderId = $('#folder-names').val();
        $.ajax({
            type: 'POST',
            url: '".Yii::app()->controller->createUrl('/x2sign/moveFolder')."',
            data: {
                'envelopeId': envelopeId,
                'folderId': folderId
            },
            success: function (resp) {
                console.log(resp);
                $('#folder-move-modal').modal('hide')
            },
            error: function (resp) {
                alert(resp.responseText);
            }
        });
    });

    $('.folder-item').on('click', function () {
        let folderId = $(this).data('folder-id');
        $.fn.yiiGridView.update('x2sign-index-grid-view', {
            url: window.location.href,
            data: 'folderId='+folderId, // Word is what you are sending now
             complete: function () {
                 $('.move-btn').on('click', function () {
                     let envelopeId = $(this).data('envelope-id');
                     $('#move-folder-btn').data('envelope-id', envelopeId);
                     $('#move-folder-btn').attr('data-envelope-id', envelopeId);
                 });
             }
        })
    })

     x2.x2sign.setupResendClass = function (id) {
         console.log(id);
         $('#resend-btn').data('envelope-id', id);
         console.log($('#resend-btn').data('envelope-id'));
     }

     x2.x2sign.signFromEnvelope = function (id) {
         $.ajax({
            type: 'POST',
            url: '".Yii::app()->getBaseUrl(true) . '/index.php/x2sign/signFromEnvelope/id/'."'+id,
            data: {
                'ajax': 1,
                'id': id,
            },
            success: function (resp) {
                console.log(resp);
                alert('Envelope Signed!');
            },
            error: function (resp) {
                alert(resp.responseText);
            }
        });
     } 

    $('#resend-btn').on('click', function () {
        let modelId = $(this).data('envelope-id');
        let subject = $('#email-subject').val();
        let body = $('#message-text').val();
        $.ajax({
            type: 'POST',
            url: '".Yii::app()->controller->createUrl('/x2sign/resend')."',
            data: {
                'modelId': modelId,
                'emailSubject': subject,
                'emailBody': body
            },
            success: function (resp) {
                console.log(resp);
                alert('Email Sent!');
                $('#resend-modal').modal('hide')
            },
            error: function (resp) {
                alert(resp.responseText);
            }
        });
    });

    $('.cont-btn').on('click', function () {
        let envelopeId = $(this).data('envelope-id');
        window.location.href = 'https://sydney.tworld.com/index.php/x2sign/quickSetupRecipients/' + envelopeId; 
    });

    $('.link-item').on('hover', function() {
        $('.link-item-active').removeClass('link-item-active');
        $(this).addClass('link-item-active');
    });

    // On click events for updating gridview
    $('#sent').on('click', function() {
        $.fn.yiiGridView.update('x2sign-index-grid-view', {
            url: window.location.href,
            data: 'status=0', // Word is what you are sending now
        });
    });

    $('#draft').on('click', function() {
        $.fn.yiiGridView.update('x2sign-index-grid-view', {
            url: window.location.href,
            data: 'status=5', // Word is what you are sending now
            complete: function () {
                $('.cont-btn').on('click', function () {
                    let envelopeId = $(this).data('envelope-id');
                    window.location.href = 'https://sydney.tworld.com/index.php/x2sign/quickSetupRecipients/' + envelopeId; 
                });
            }
        });
    });

    $('#action-required').on('click', function() {
        $.fn.yiiGridView.update('x2sign-index-grid-view', {
            url: window.location.href,
            data: 'status=1', // Word is what you are sending now
        });
    });

    $('#waiting-for-others').on('click', function() {
        $.fn.yiiGridView.update('x2sign-index-grid-view', {
            url: window.location.href,
            data: 'status=2', // Word is what you are sending now
        });
    });

    $('#cancelled').on('click', function() {
        $.fn.yiiGridView.update('x2sign-index-grid-view', {
            url: window.location.href,
            data: 'status=3', // Word is what you are sending now
        });
    });

    $('#completed').on('click', function() {
        $.fn.yiiGridView.update('x2sign-index-grid-view', {
            url: window.location.href,
            data: 'status=4', // Word is what you are sending now
            complete: function () {
                 $('.move-btn').on('click', function () {
                     let envelopeId = $(this).data('envelope-id');
                     $('#move-folder-btn').data('envelope-id', envelopeId);
                     $('#move-folder-btn').attr('data-envelope-id', envelopeId);
                 });
             }
        });
    });

    // Add folder events
    $('#save-folder-btn').on('click', function () {
        console.log($('#folder-name-input').val());
        $.ajax({
            type: 'POST',
            url: '". Yii::app()->controller->createUrl('/x2sign/saveFolder') ."',
            data: {
                name: $('#folder-name-input').val()
            },
            success: function (resp){
                console.log(resp);
                $('#folder-name-modal').modal('hide')

                // reload folder list
                x2.x2sign.reloadFolders();
            },
            error: function (resp) {
                alert(resp.responseText);
            }
        });
    });
    //this will check to see if the url has a stats passed and if so pre click the buttons
    $(document).ready(function() {
        // check where the shoppingcart-div is  
        var url_string = window.location.href;
        var url = new URL(url_string);
        var status = url.searchParams.get('status');
        switch(status) {
            case 'Need to Sign':
                $('#action-required').click();
                break;
            case 'Waiting for Others':
                $('#waiting-for-others').click();
                break;
            case 'Cancelled':
                $('#cancelled').click();
                break;
            case 'Completed':
                $('#completed').click();
                break;
        }

    });



");

Yii::app()->clientScript->registerCss('X2SignCreateModal',"

#x2sign-send {
    display: display:inline-block;
    background: #007FCF;
    color: white;
}

");

Yii::app()->clientScript->registerCss("X2SignMain", "
    #content {
        background: none;
        border: none;
    }

    #x2sign-menu-col {
        background-color: white;
        border: 1px solid #e0e0e0;
    }

    #x2sign-content-main {
        background-color: white;
        border: 1px solid #e0e0e0;
    }

    .x2sign-header {
        background-color: white;
        color: black;
    }

    #x2sign-index-grid-view {
        margin: 10px;
    }

    .start-btn {
        width: 100%;
    }

    .x2sign-tool-btn {
       width: 25%;
    }

    #x2sign-menu-inner {
        background-color: #b5b2b242;
        height: 100%;
    }

    .link-item:hover {
        cursor: pointer;
        background-color: grey;
        color: white;
    }

    .link-item-active {
        background-color: grey;
        color: white;
    }

    #add-folder-btn {
        float: right;
    }

    #add-folder-btn:hover {
         cursor: pointer;
    }

    tr {
        border-bottom: 1px solid;
        padding: 5px;
    }
");

Yii::app()->clientScript->registerScript("X2SignMainJS", "
    $('#start-btn button').on('click', function() {
        window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/quickSend')."';
    });

    $('#insign-btn button').on('click', function() {
        window.location.href = '" . Yii::app()->controller->createUrl('/x2sign/signInPerson')."';
    });
", CClientScript::POS_END);

$columns = array (
    array (
        'name' => 'name',
        'header' => Yii::t('docs', 'Name'),
        'type' => 'raw',
        'value' => '$data->renderLink();',
        'htmlOptions' => array (
            'id' => 'php:$data->id . "-x2sign-envelope-object"',
            'data-type' => 'php:$data->type',
            'data-id' => 'php:$data->objId',
            'class' => 'php:"view x2sign-envelope-object".',
            'width' => '400px',
        ),
        'headerHtmlOptions' => array (
             'class' => 'x2sign-header',
             'width' => '400px',
        ),

    ),
    array (
        'name' => 'status',
        'header' => Yii::t('docs', 'Status'),
        'type' => 'raw',
        'value' => '$data->renderStatus();',
        'htmlOptions' => array (
            'id' => 'php:$data->id . "-x2sign-envelope-object"',
            'data-type' => 'php:$data->type',
            'data-id' => 'php:$data->objId',
            'class' => 'php:"view x2sign-envelope-object".',
            'width' => '120px',
        ),
        'headerHtmlOptions' => array (
             'class' => 'x2sign-header',
             'width' => '120px',
        ),
    ),
    array (
        'name' => 'c_listing',
        'header' => Yii::t('docs', 'Listing'),
        'type' => 'raw',
        'value' => '$data->renderRelatedListing();',
        'htmlOptions' => array (
            'id' => 'php:$data->id . "-x2sign-envelope-object"',
            'data-type' => 'php:$data->type',
            'data-id' => 'php:$data->objId',
            'class' => 'php:"view x2sign-envelope-object".',
            'width' => '120px',
        ),
        'headerHtmlOptions' => array (
             'class' => 'x2sign-header',
             'width' => '120px',
        ),
    ),
    array (
        'name' => 'createDate',
        'header' => Yii::t('docs', 'Create Date'),
        'type' => 'raw',
        'value' => '$data->renderSentDate();',
        'htmlOptions' => array (
            'id' => 'php:$data->id . "-x2sign-envelope-object"',
            'data-type' => 'php:$data->type',
            'data-id' => 'php:$data->objId',
            'class' => 'php:"view x2sign-envelope-object".',
            'width' => '130px',
        ),
        'headerHtmlOptions' => array (
             'class' => 'x2sign-header',
             'width' => '130px',
        ),
    ),
    'assignedTo'=>array(
        'name'=>'assignedTo',
        'value'=>'$data->assignedTo',
        'type'=>'raw',
    ),
    'relatedRecords'=>array(
        'name'=>'relatedRecords',
        'value'=>'$data->renderRelatedRecords()',
        'type'=>'raw',
    ),
    array (
        'type' => 'raw',
        'value' => '$data->renderToolDropdown();',
        'htmlOptions' => array (
            'id' => 'php:$data->id . "-x2sign-envelope-object"',
            'data-type' => 'php:$data->type',
            'data-id' => 'php:$data->objId',
            'class' => 'php:"view x2sign-envelope-object".',
        )

    ),
); 
?>
<div class="container-fluid">
    <div class="row gx-2">
        <div id="x2sign-menu-col" class="col-3 p-5">
           <div id="x2sign-menu-inner" class="inner-col p-3">
               <div id="start-btn">
                   <button type="button" class="start-btn btn btn-warning flex-item">NEW</button>
               </div>
               <div id="insign-btn" class="mt-3">
                   <button type="button" class="start-btn btn btn-warning flex-item">Sign In-Person</button>
               </div>
               <div class="envelope-links-div pt-3">
                   <div class="quick-links-title m-1">
                       <h3>ENVELOPES</h3>
                   </div>
                   <div class="m-3">
                       <h4 id="sent" class="link-item link-item-active p-2"><i class="fa fa-paper-plane fa-fw" aria-hidden="true"></i>&nbsp; Sent</h4>
                   </div>
                   <div id="draft" class="m-3">
                       <h4 class="link-item p-2"><i class="fa fa-file fa-fw" aria-hidden="true"></i>&nbsp; Drafts</h4>
                   </div>
               </div>
               <div class="quick-links-div pt-3">
                   <div class="quick-links-title m-1">
                       <h3>QUICK SORT</h3>
                   </div>
                   <div class="m-3">
                       <h4 id="action-required" class="link-item p-2"><i class="fa fa-exclamation-circle fa-fw" aria-hidden="true"></i>&nbsp; Action Required</h4>
                   </div>
                   <div class="m-3">
                       <h4 id="waiting-for-others" class="link-item p-2"><i class="fa fa-clock-o fa-fw" aria-hidden="true"></i>&nbsp; Waiting for Others</h4>
                   </div>
                   <div class="m-3">
                       <h4 id="cancelled" class="link-item p-2"><i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i>&nbsp; Cancelled</h4>
                   </div>
                   <div class="m-3">
                       <h4 id="completed" class="link-item p-2"><i class="fa fa-check fa-fw" aria-hidden="true"></i>&nbsp; Completed</h4>
                   </div>
               </div>
               <div class="folder-links-div pt-3">
                   <div class="quick-links-title m-1">
                       <h3>FOLDERS <i id="add-folder-btn" class="fa fa-plus fa-fw" data-bs-toggle="modal" data-bs-target="#folder-name-modal">&nbsp;</i></h3>
                   </div>
                   <?php foreach($folders as $folder): ?>
                   <div class="m-3 folder-div">
                       <h4 id="<?php echo $folder['name']; ?>" data-folder-id="<?php echo $folder['id']; ?>" class="folder-item link-item p-2">&nbsp; <?php echo $folder['name']; ?></h4>
                   </div>
                   <?php endforeach; ?>
               </div>
           </div>
        </div>
        <div id="x2sign-content-main" class="col-9 px-0">
<?php $widget = $this->widget ('X2GridViewGeneric', array (
    'dataProvider' => $dataProvider,
    'id' => 'x2sign-index-grid-view',
    'buttons'=>array('clearFilters'),
    'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/listview',
//    'dataColumnClass' => 'X2DataColumnGeneric',
    'filter'=>$model,
    'columns' => $columns,
    'template' => '<div class="page-title rounded-top icon docs"><h2 id="x2sign-gridview-title">' . Yii::t('apps', 'X2DocSign Envelopes') . '</h2>{buttons}' .
                  '</div>{items}{pager}',
    'enableColDragging' => false,
    'enableGridResizing' => true,
    'rememberColumnSort' => false,
    
)); 

$folderNames = array('empty' => 'select an option');
foreach ($folders as $folder) {
    if (!isset($folder['id'])) continue;
    $folderNames[$folder['id']] = $folder['name'];
}

?>
        </div>
    </div>
</div>
<div id="folder-name-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">ADD A FOLDER</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input id="folder-name-input" placeholder="ENTER NAME HERE"></input>
      </div>
      <div class="modal-footer">
        <button type="button" id="save-folder-btn" class="btn btn-primary">ADD</button>
      </div>
    </div>
  </div>
</div>
<div id="folder-move-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">MOVE DOCUMENT</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <?php echo CHtml::dropDownList('folder-names', "empty", $folderNames); ?>
      </div>
      <div class="modal-footer">
        <button type="button" id="move-folder-btn" class="btn btn-primary">MOVE</button>
      </div>
    </div>
  </div>
</div>
<div id="resend-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">RESEND ENVELOPE</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form>
              <div class="mb-3">
                  <label for="email-subject" class="col-form-label">Email Subject:</label>
                  <input type="text" class="form-control" id="email-subject">
              </div>
              <div class="mb-3">
                  <label for="message-text" class="col-form-label">Message:</label>
                  <textarea class="form-control" id="message-text"></textarea>
              </div>
          </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="resend-btn" class="btn btn-primary">RESEND</button>
        <button type="button" id="resend-cancel-btn" class="btn btn-outline-primary" data-bs-dismiss="modal">CANCEL</button>
      </div>
    </div>
  </div>
</div>
