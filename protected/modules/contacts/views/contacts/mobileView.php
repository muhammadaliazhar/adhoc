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
 * @author Justin Toyomitsu <justin@x2engine.com>, Peter Czupil <peter@x2engine.com>
 */
Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/Property.css');

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/View.css');
Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/OfferDialog.css');
Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/X2VerticalTabs.css');
Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->assetsUrl.'/css/MenuTables.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/View.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->controller->module->assetsUrl.'/js/X2VerticalTabs.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/pdfReader/build/pdf.js');
//Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl.'/js/signable.js', CClientScript::POS_BEGIN);

Yii::app()->clientScript->registerPackage ('emailEditor');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/x2signMobile.js');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/x2sign.js');
//Yii::app()->clientScript->registerScript('openDialog',"
//    function openDialog() {
//        openX2SignDialog('Contacts', '', '', '', '$signDocs');
//    }
//", CClientScript::POS_END);

/*Yii::app()->clientScript->registerScript('viewerActivityJS',"
    /* $('#getShareLinkBtn').on('click', function () {
        $.ajax({
            url: '" . substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/properties/getShareLink/$model->id',
            success: function(data) {
                console.log(data);
                resp = JSON.parse(data);
                $('#sharePropInput').val('" . substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/properties/viewerView/' + resp.link);
                $('#getShareLinkBtn').hide();
                $('#sharePropDiv').show();
            },
            error: function () {
                alert('Could not get share link');
            }
        });
    }); */

  /*  $('#viewerEditSaveBtn').on('click', function () {
        let viewerId = $('#viewerEditBtn').data('viewer');
        $.ajax({
            url: '" . substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/contacts/viewerAjaxUpdate',
            type: 'POST',
            data: {
                id: viewerId,
                fields: $('#requestForm').serializeArray()
            },
            success: function (data) {
                console.log(data);
            }
        });
        $('#viewerModal').modal('hide');
    });

    $('#propDeleteBtn').on('click', function() {
        var deleteConfirm = confirm('Are you sure you want to delete this property? This cannot be undone.');
        if (deleteConfirm == true) {
            var throbber = auxlib.pageLoading();
            $.ajax({
               url: '" . substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/properties/delete/id/$model->id',
               type: 'POST',
               success: function(data) {
                   window.location.href = '" . substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/properties'
               }
            });
        }
    });

    $('#saveEditBtn').on('click', function() {
        $.ajax({
            url: '" . substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/properties/ajaxUpdate',
            type: 'POST',
            data: {
                id: " . $model->id . ",
                fields: $('#editForm').serializeArray()
            },
            success: function (data) {
                console.log(data);
            }
        });

        $('.property-street').text($('input[name=\'Properties_street\']').val());
        $('.property-zip').text($('input[name=\'Properties_city\']').val() + ', ' + $('input[name=\'Properties_state\']').val() + ', US');
        $('.property-inner').text($('input[name=\'Properties_price\']').val() + ' | ' + $('input[name=\'Properties_bedrooms\']').val() + ' Bed, ' + $('input[name=\'Properties_bathrooms\']').val() + ' Bath');
        $('#editPropModal').modal('hide');
    });

    $('#sendShareEmailBtn').on('click', function () {
        var throbber = auxlib.pageLoading();
        $('#sharePropertyModal').modal('hide');
        $.ajax({
            url: '" . substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . "index.php/properties/sendShareEmail/" . $model->id . "',
            type: 'POST',
            data: {
                to: $('#shareEmailTo').val(),
                subject: $('#shareEmailSubject').val(),
                message: $('#shareEmailMessage').text()
            },
            success: function(data) {
                throbber.remove();
                console.log(data);
            },
            error: function(data) {
                throbber.remove();
                console.log(data);
            }
        });
    });

    $('#docViewClose').on('click', function() {
        $('#pdf-nav').hide();
        $('#docViewModal').modal('hide');
    });

    $('#docViewModal').on('hide.bs.modal', function () {
        $('#pdf-nav').hide();
    });

     //google maps autocomplete
     autoInput = new google.maps.places.Autocomplete($('input[name=\'Properties_street\']')[0]);
     // init autocomplete
     autoInput.setFields(['address_component']);
     autoInput.addListener('place_changed', fillInAddress);

     function fillInAddress() {
         var place = autoInput.getPlace();
         for (var i = 0; i < place.address_components.length; i++) {
             var addressType = place.address_components[i].types[0];
             if (addressType == 'street_number') $('input[name=\'Properties_street\']').val(place.address_components[i].long_name);
             if (addressType == 'route') $('input[name=\'Properties_street\']').val($('input[name=\'Properties_street\']').val() + ' ' + place.address_components[i].long_name);
             if (addressType == 'locality') $('input[name=\'Properties_city\']').val(place.address_components[i].long_name);
             if (addressType == 'administrative_area_level_1') $('input[name=\'Properties_state\']').val(place.address_components[i].short_name);
             if (addressType == 'postal_code') $('input[name=\'Properties_zipcode\']').val(place.address_components[i].short_name);
         }
     }

     // Send Disclosure Functions

     function removeRecipient(minusElem) {
         $(minusElem).parent('div').remove();
     }

      $('#addRecipBtn').on('click', function() {
          const recipInputGroup = `
             <div class=\"row input-group pt-4 \">
                <input class=\"form-control\" type=\"name\" name=\"firstName\" placeholder=\"First Name\" />
                <input class=\"form-control\" type=\"name\" name=\"lastName\" placeholder=\"Last Name\" />
                <input class=\"form-control\" type=\"email\" name=\"email\" placeholder=\"Email Address\" />
                <a class=\"recipBtn tooltip-test\" title=\"Remove recipient\" onclick=\"removeRecipient(this)\"><i class=\"pl-2 pt-3 fa fas fa-minus\" style=\"position: absolute;\"></i></a>
            </div>
         `;
          $('#recipientForm').append(recipInputGroup);
      });

      // Email message functions
      // Get email templates
      $.ajax({
          url: yii.absoluteBaseUrl + '/index.php/docs/getEmailTemplates',
          type: 'GET',
          data: {model: 'Contacts'},
          success: (result) => {
              $.each(JSON.parse(result), (k,v) =>
                  {\$('#email-templates').append($('<option />').attr('value',k).text(v))});
          },
      });

      // add template to CKEditor when template is selected
      $('#email-templates').change(()=>{
            if ($('#email-templates').val() == 0 ) return;
            $.ajax({
                url: yii.absoluteBaseUrl + '/index.php/docs/loadEmailTemplate',
                data: {docsId: $('#email-templates').val()},
                type: 'GET',
                success: (result) => {
                   result = JSON.parse(result);
                   $('#email-subject').val(result.subject);
                   CKEDITOR.instances['email-body'].setData(result.body);
                },
            });
        }); 

      // initialize CKEDITOR
      //CKEDITOR.replace($('#email-body')[0], {height: '8em', placeholder: 'Message (Optional)', insertableAttributes:x2.insertableAttributes});

      // Send disclosure button event
      $('#sendDiscBtn').on('click', function () {
          var throbber = auxlib.pageLoading();
          $('#sendDisclosureModal').modal('hide');
          let docs = [];
          $('#docsForm :input:checked').each(function () {
              docs.push($(this).data('id'));
          });

          let signees = [];
          $('#recipientForm').children('div.input-group').each(function () {
              let signee = {};
              $(this).children(':input').each(function () {
                  if ($(this).attr('name') == 'firstName') signee.firstName = $(this).val();
                  if ($(this).attr('name') == 'lastName') signee.lastName = $(this).val();
                  if ($(this).attr('name') == 'email') signee.email = $(this).val();
              });
              signees.push(signee);
          });

          // send signing request
          $.ajax({
              type: 'POST',
              url: yii.scriptUrl + '/x2sign/sendDocs',
              data: {
                  modelType: 'contacts',
                  emailSubject: $('#email-subject').val(),
                  emailBody: CKEDITOR.instances['email-body'].getData(),
                  signees: JSON.stringify(signees),
                  documents: JSON.stringify(docs),
                  reminders: undefined,
                  sequential: false
              },
              success: function (url) {
                  throbber.remove();

                  // var expression = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
                  // var regex = new RegExp(expression);

                  // if (url.match(expression)) {
                  //     // Redirect to docusign
                  //     window.location = url;
                  // } else {
                      console.log(url.responseText);
                      alert(url);
                  // }
              },
              error: function (data) {
                  throbber.remove();
                  console.log(data.responseText);
                  alert(data.responseText);
              }
          });
      });

", CClientScript::POS_END);
*/
/**
 * Archive Button Default
 */
 $archive_button = ''; 
// if($model->status == Properties::ARCHIVE) { 
//     $archive_button = '<button id="settings-unarchive-button" onclick="property_archive_or_unarchive('
//                      .$model->id . ',' . Properties::ARCHIVE . ',' . Properties::PUBLIC . ',' . $model->status
//                      .')" class="btn btn-outline-secondary btn-sm">UNARCHIVE</button>';
// } else { 
//     $archive_button = '<button id="settings-archive-button" onclick="property_archive_or_unarchive('
//                      .$model->id . ',' . Properties::ARCHIVE . ',' . Properties::PUBLIC . ',' . $model->status
//                      .')" class="btn btn-outline-secondary btn-sm" style="width:12em;">ARCHIVE PROPERTY</button>';
// }

Yii::app()->clientScript->registerScript("recordViewJS", "
    $('button.tablinks').on('click', function () {
        console.log($('body').height());
    });
");

$download_url = '';
$download_url = Yii::app()->createUrl('/properties/download/id/' . $model->id);

//$signDocsArr = json_decode($signDocs, TRUE);

X2Html::getFlashes ();

?>

<style>
#contentDiv {
    background: white;
}
.container {padding-left: 0px; padding-right: 0px;}
.dot {
  height: 25px;
  width: 25px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
}
.property-zip a {
    color: white;
}
.property-zip a:hover {
    color: white;
}
.property-inner a {
    color: white;
}
.property-inner a:hover {
    color: white;
}
.property-inner.detail-input a {
    color: #007bff;
}

body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

#mobileFooter {
  background-color: #efefef;
  flex: 0 0 50px;/*or just height:50px;*/
  margin-top: auto;
}

.tabcontent {
    margin-bottom: 200px;
}

</style>

<div class="w-100" style='padding-left: 0px; padding-right: 0px; position: fixed; bottom: 0; z-index: 500;'>
    <div class="w-100" style="margin-right: auto; margin-left: auto; padding-left: 6%; padding-right: 9%; background-color: white;">
        <button style="" id="updateRecord" type="button" class="btn btn-primary w-100 mb-2 mx-1" onclick='goToUpdate()'>Edit</button>
        <button id="send-button" class="btn btn-warning w-100 mb-2 mx-1" style="display: none;" onclick="SendDocMobile()">Send</button>
    </div>
    <div class="x2-vertical-tabs" style="width: 100%;">
        <div style="float: left; width: 33%; background-color: #f0f3f8;">
        <button style="float: left;" class="tablinks" onclick="x2Tabs(event, 'Offers')">
            <i class="property-icons fas fa-money-check-alt"></i>
            <div class="x2-tabs-title">SEND DOC</div>
        </button>
        </div>
        <div style="float: left; width: 33%; background-color: #f0f3f8;">
         <button style="float: left;"  class="tablinks" onclick="x2Tabs(event, 'Activity')">
            <i class="property-icons far fa-chart-bar"></i>
            <div class="x2-tabs-title">ACTIVITY</div>
        </button>
        </div>
        <div style="float: left; width: 34%; background-color: #f0f3f8;">
         <button style="float: left;"  class="tablinks" onclick="x2Tabs(event, 'Details')" id="defaultOpen">
            <i class="property-icons far fa-edit"></i>
            <div class="x2-tabs-title">DETAILS</div>
        </button>
        </div>
    </div>
</div>




<div <?php //if(isset($background)) echo $background; ?> style='background: #203046;' class="preview-header">
    <div class="columns" style="width: 100%;">
        <div class="d-flex flex-column py-2">
            <div class="text-light p-2" style="font-size: 2rem;"><i class="fas fa-user text-light " style="padding-right: 1rem; font-size: 1.5rem;"></i><?php echo $model->name; ?></div>
            <div class="text-light pt-1 pb-2 px-2"><i class="fas fa-envelope text-light" style="padding-right: 1rem; font-size: 1rem;"></i><a href="mailto:<?php echo $model->email; ?>"><?php echo $model->email; ?></a></div>
            <div class="text-light p-2"><i class="fas fa-phone text-light" style="padding-right: 1rem; font-size: 1rem;"></i><a href="tel:<?php echo $model->phone; ?>"><?php echo $model->phone;  ?></a></div>
        </div>
        <div class="column col-6 d-flex flex-row-reverse" style="align-items: flex-end;">
            <div class="property-edit-list">
                <div class="d-flex property-edit btn-group flex-space-around" style="height: 2.4rem;">
                    <div class="px-2">
                    </div>
                    <div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="background: #203046;">
<div class="property-menu" style="padding-top: 10px; height:calc(100% + 400px); border-radius: 30px 30px 0px 0px;">
    <div id="Docs" class="tabcontent">
        <?php /**
         * ========== CONTENTS OF DOCS ===========
         */ ?>
        <div class="preview-document-hearder">
        </div>
        <hr>
        <div class="preview-buttons">
            <button type="button" class="btn btn-primary btn-sm shadow" data-toggle="modal" data-target="#docUploadModal">ADD DOCUMENT</button>
            <button class="btn btn-outline-primary btn-sm shadow" onclick="property_download('<?php echo $download_url; ?>')">DOWNLOAD</button>
            <button class="btn btn-outline-primary btn-sm shadow" data-toggle="modal" data-target="#sendDisclosureModal">SEND DISCLOSURES FOR SIGNING</button>
            <button class="btn btn-outline-primary btn-sm shadow">STAMP</button>
        </div>
        <div class="preview-notify">
            <button class="notify-button" onclick="notify_viewers( <?php echo $model->id; ?> )">
                NOTIFY VIEWERS OF UPDATE<div class="notify-circle"><?php //echo $viewerCount; ?></div>
            </button>
        </div>
        <div class="modal fade" id="docUploadModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
            <?php $this->widget('FileUploader', ['id'=>'doc-upload', 
                                                 'displayToggle' => false,
                                                 'displayForm' => false,
                                                 'mediaParams' => ['associationType' => 'Properties', 'associationId' => $model->id], 
                                                 'acceptedFiles' => 'application/pdf', 
                                                 'viewParams' => ['closeButton'=>false, 'showButton' => false],
                                                 'events' => ['success' => '$.fn.yiiGridView.update(
                                                     "documents-grid",
                                                 );$("#docUploadModal").modal("hide");']]
            ); ?>
                </div>
            </div>
          </div>
        </div>
        <div class="preview-documents">
             <?php /*
                 $this->widget('zii.widgets.grid.CGridView', array(
                     'id'=>'documents-grid',
                     'ajaxUpdate' => true,
                     'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name. '/css/gridview',
                     'template'=> '{items}{pager}',
                     'rowHtmlOptionsExpression' => 'array("onclick"=>"")',
                     'dataProvider'=>$doc_dataProvider,
                     'columns'=>array(
                         array(
                         'name'=>'Documents',
                         'value' =>'Properties::getDocuments($data)',
                         'type'=>'raw',
                         'headerHtmlOptions'=>array('style'=>'width:100%; display:none;'),
                         ),
                     ),
                 ));
                */
            ?>
        </div>
        <?php /**
         * =========== END OF DOCS ==============
         */ ?>
    </div>
    <div id="Viewers" class="tabcontent">
        <?php
        /**
         * =============== CONTENTS OF VIEWERS =============
         */
        ?>
        <div class="viewer-header">
            <div class="view-buttons">
                <button class="btn btn-primary btn-sm" onclick="shareProperty(<?php echo $model->id;?>)">SHARE PROPERTY</button>
                <button class="btn btn-outline-primary btn-sm">DOWNLOAD VIEWER ACTIVITY</button>
            </div>
            <hr>
        </div>
        <div class="viewer-list">
            <?php
                /*
                $this->widget('zii.widgets.grid.CGridView', array(
                     'id'=>'viewers-grid',
                     'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name. '/css/gridview',
                     'template'=> '{items}{pager}',
                     'rowHtmlOptionsExpression' => 'array("onclick"=>"")',
                     'dataProvider'=>$view_dataProvider,
                     'columns'=>array(
                         array(
                         'name'=>'Documents',
                         'value' =>'Properties::getViewers($data'. ',' . $model->id. ')',
                         'type'=>'raw',
                         'headerHtmlOptions'=>array('style'=>'width:100%; display:none;'),
                         ),
                     ),
                 ));
                */
            ?>
            <div class="modal fade bd-example-modal-lg" id="viewerModal" tabindex="-1" role="dialog" aria-labelledby="viewerModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Viewer</h5>
                            <button id="moreInfoClose" type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="max-height: calc(100vh - 15rem);overflow-y: auto;height: 25rem;">
                            <form id="requestForm">
                                <div class="form-group" style="padding-top: 10px;">
                                    <label for="formRole">Role</label>
                                    <select name="role" class="form-control" id="formRole">
                                        <option value="teamMember">Team Member</option>
                                        <option value="agent">Agent</option>
                                        <option value="buyer">Buyer</option>
                                        <option value="seller">Seller</option>
                                    </select>
                                </div>
                                <div class='form-group' id='formPhoneDiv'>
                                    <label for='formPhone'>Phone</label>
                                    <input name="phone" type='phone' class='form-control' id='formPhone'>
                                </div>
                                <div class="form-group" id="formNotesDiv">
                                    <label for="formNotes">Notes</label>
                                    <textarea name="backgroundInfo" id="formNotes" class="form-control"></textarea>
                                </div>
                            </form>
                        <div class="modal-footer">
                            <button id="viewerEditSaveBtn" type="submit" class="btn btn-primary">Save</button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        <?php
        /**
         * ================ END OF VIEWERS ============
         */
        ?>
    </div>
    <div id="Details" style="margin-bottom: 200px;" class="tabcontent">
        <div class="column grid-view" id="properties-grid">
            <table>
                <tbody>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner" style="padding: 10px 0px 10px 10px; color: black;">Name: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->name;?></span></div> </td></tr>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner" style="padding: 10px 0px 10px 10px; color: black;">Email: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->email;?></span></div> </td></tr>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner detail-input" style="padding: 10px 0px 10px 10px; color: black !important;">Phone: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->renderAttribute('phone');?></span></div> </td></tr>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner detail-input" style="padding: 10px 0px 10px 10px; color: black !important;">AssignedTo: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->renderAttribute('assignedTo', TRUE, TRUE);?></span></div> </td></tr>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner detail-input" style="padding: 10px 0px 10px 10px; color: black !important;">Mobile Phone: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->renderAttribute('c_mobilephone');?></span></div> </td></tr>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner detail-input" style="padding: 10px 0px 10px 10px; color: black !important;">Lead Source: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->renderAttribute('c_leadsource');?></span></div> </td></tr>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner detail-input" style="padding: 10px 0px 10px 10px; color: black !important;">Description:</div> <div class="pl-2 d-flex align-items-center"> <span class="field-data"><?php echo $model->renderAttribute('description');?></span></div> </td></tr>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner detail-input" style="padding: 10px 0px 10px 10px; color: black !important;">Listing Lookup: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->renderAttribute('c_listinglookup__c');?></span></div> </td></tr>
                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner detail-input" style="padding: 10px 0px 10px 10px; color: black !important;">Listing Number Lookup: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->renderAttribute('c_listing_number_lookup');?></span></div> </td></tr>

                    <tr style="background: #f0f3f8;"><td class="d-flex"><div class="col-5 property-inner detail-input" style="padding: 10px 0px 10px 10px; color: black !important;">Referred by: </div> <div class="pl-2 d-flex align-items-center"><span class="field-data"><?php echo $model->renderAttribute('c_referredBy');?></span></div> </td></tr>
                </tbody>
            </table>
        </div>
        <script>
        function goToUpdate(){
            document.location.href='<?php echo Yii::app()->createUrl('/contacts/update',array('id'=>$model->id));?>';


        }
        </script>
        <?php
        //$this->widget('DetailView', array(
        //    'model' => $model,
        //    'modelName' => 'sellers2',
        //    'scenario' => 'Mobile',
        //));
        //had to add this since for some reason the edit js does not load
        //Yii::app()->clientScript->registerScript('backupUpdateJS','
            //$(function () {if(typeof x2.detailView == "undefined") x2.detailView = new x2.DetailView ({"element":"#yw2","translations":{"unsavedChanges":"There are unsaved changes on this page"},"namespace":"","modelId":"'.$model->id.'","modelName":"sellers2","inlineEdit":true});});
        //', CClientScript::POS_END);

        /**$jsParams = CJSON::encode (array (
                'modelId' => $model->id,
                'translations' => array (
                    'unsavedChanges' => Yii::t('app', 'There are unsaved changes on this page.')
                ),
                'csrfToken' => Yii::app()->request->getCsrfToken(),
            ));

            Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/recordView/inlineEditor.js');
            Yii::app()->clientScript->registerScript('inlineEditJS', "
                new x2.InlineEditor($jsParams); 
            ", CClientScript::POS_READY);
        /**
         * ================ CONTENTS OF OFFERS ===============
         */

        //$offer = new PropertyOffers;
        //echo $offer->getView($model->id);

        /**
         * ================ END OF OFFERS ===============
         */
        ?>
    </div>
    <div id="Offers" class="tabcontent">
        <br>
        <h2>Subject</h2>
        <input id="email-subject" placeholder="Subject" style="width: 100%; margin-bottom: 5px;">
        <h2>Message</h2>
        <textarea id="emailMessage" class="form-control" style="overflow:auto !important; height: 250px !important; resize: none;" name="emailMessage"></textarea>
 
        <h2>Signees</h2>
        <div id="signee-list" style="width: 95%; list-style-type: decimal; height: 150px; overflow-y: auto; border: 1px solid lightgrey; border-radius: 3px 3px 0px 0px; padding: 10px 0px;" class="ui-sortable">
            <li modelId="<?php echo $model->id;?>" modelType="Contacts" style="border: 1px solid rgb(229, 229, 229); border-radius: 5px; cursor: pointer; background-color: rgb(255, 255, 255); margin: 0px 0px 5px 20px; max-width: 200px; padding: 5px; word-break: break-word;">
                <b><?php echo $model->name;?> - <?php echo $model->email;?></b> <a class="right delete" onclick="$(this).parent().remove();"><i class="far fa-times-circle"></i></a></li>
            <li modelId="myself" modelType="myself"  style="border: 1px solid rgb(229, 229, 229); border-radius: 5px; cursor: pointer; background-color: rgb(255, 255, 255); margin: 0px 0px 5px 20px; max-width: 200px; padding: 5px; word-break: break-word;">
                <b>Myself</b><a class="right delete" onclick="$(this).parent().remove();"><i class="far fa-times-circle"></i></a></li>
        </div>
        <input id="queryInput"/>    
        <select id = 'sendModel'><option>All Models</option><option value="X2Leads">Contacts</option><option value="Contacts">Buyers</option><option value="Sellers2">Sellers</option></select>
        <button class=" btn btn-light flex-item" type="button" onclick="lookUpSigner()"><i class="fas fa-search"></i></button>
        <br>
        <select id = 'signAddOpt' style="display:none;width:100%;"></select>
        <button class="x2-button" style="display:none;" id="addSignBut" type="button" onclick="addSigner()">Add</button>
        <h2>Documents</h2>
        <div id="documents-list" style="width: 95%; list-style-type: decimal; height: 150px; overflow-y: auto; border: 1px solid lightgrey; border-radius: 3px 3px 0px 0px; padding: 10px 0px;" class="ui-sortable">
        </div>
        <input id="docInput"/>
        <button class=" btn btn-light flex-item" type="button" onclick="lookUpDocuments()"><i class="fas fa-search"></i></button>
        <br>
        <select id = 'docAddOpt' style="display:none;width:100%;"></select>
        <button class="x2-button" style="display:none;" id="addDocBut" type="button" onclick="AddDocuments()">Add</button>


    </div>
    <div id="Activity" class="tabcontent">
        <style>
          .row {
                margin-right: 0px;
                margin-left: 0px;
                flex-wrap: wrap;
                }
        </style>
        <br>
        <?php
        //had to add new style for rows as the margins would mess up side bar hrml 
        $this->widget('Publisher', array(
            'associationId' => $model->id,
            'associationType' => 'contacts',
        ));


        $this->widget('History', array(
            'associationId' => $model->id,
            'associationType' => 'contacts',
        ));

        /*$params = array();
        $actionsModel = new Actions('search');
        $criteria = $model->getAccessCriteria();
         $criteria = $criteria->addCondition("type != 'emailOpened' AND associationType = 'Sellers2' AND associationId = '" . $model->id. "'");
        $dataProvider = $actionsModel->search($criteria, Actions::ACTION_INDEX_PAGE_SIZE);
        $this->widget('zii.widgets.CListView', array(
            'id'=>'action-list',
            'dataProvider'=>$dataProvider,
            'itemView'=>'application.modules.actions.views.actions._viewIndex',
            'htmlOptions'=>array('class'=>'action x2-list-view list-view','style'=>'width:100%'),
            'viewData'=>$params,
            'template'=>'{items}{pager}',
            'afterAjaxUpdate'=>'js:function(){
                clickedFlag=false;
                lastClass="";
                $(\'#advanced-controls\').after(\'<div class="form x2-layout-island" id="action-view-pane" style="float:right;width:0px;display:none;padding:0px;"></div>\');
            }',
            'pager' => array(
                'class' => 'ext.infiniteScroll.IasPager',
                'rowSelector'=>'.view',
                'listViewId' => 'action-list',
                'header' => '',
                'options' => array(
                    'history' => true,
                    'triggerPageTreshold' => 2,
                    'trigger'=>Yii::t('app','Load More'),
                    'scrollContainer'=>'.items',
                    'container'=>'.items',
                ),
              ),
        ));
        */
        ?>
    </div>
    <div id="Settings" class="tabcontent">
        <?php
         /**
          * =============== CONTENTS OF SETTINGS ===============
          * Different Status:
          * 1. Public
          * 2. Archived
          * 3. Comming Soon
          */
        ?> 

        <div class="settings-notification">
            <div class="settings-header">Notification Preferences</div>
            <div class="settings-discription">
                Manage notifications for this property.
            </div>
            <div class="settings-action">
                <input type="checkbox" id="settings-notification-button" onclick="property_mute(<?php echo $model->id; ?>)" <?php //echo $checked; ?>>
                <label for="settings-notification-button">Mute view and download notifications</label>
            </div>
        </div>
        <div class="settings-archive">
            <div class="settings-header">Archive Property</div>
            <div class="settings-discription">
                Archived properties are only visible to you.
            </div>
            <div class="settings-action">
                <?php echo $archive_button; ?>
            </div>
        </div>
        <div class="settings-delete">
            <div class="settings-header">Warning</div>
            <div class="settings-discription">
                Delete this property. This action cannot be undone.
            </div>
            <div class="settings-action">
                <a id="propDeleteBtn" href="#" class="btn btn-danger btn-sm">DELETE PROPERTY</a>
            </div>
        </div>

        <?php
        /**
         * ============== END OF SETTINGS =============
         */
        ?>
    </div>
</div>
</div>
<div id="editPropModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Property</h5>
        <button id="moreInfoClose" type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <nav>
              <div class="nav nav-tabs nav-justified d-flex" id="nav-tab" role="tablist">
                <a class="nav-item nav-link active" id="nav-address-tab" data-toggle="tab" href="#nav-address" role="tab" aria-controls="nav-address" aria-selected="true">Address</a>
                <a class="nav-item nav-link" id="nav-info-tab" data-toggle="tab" href="#nav-info" role="tab" aria-controls="nav-info" aria-selected="false">Info</a>
              <!--  <a class="nav-item nav-link" id="nav-photos-tab" data-toggle="tab" href="#nav-photos" role="tab" aria-controls="nav-photos" aria-selected="false">Photos</a> -->
              </div>
          </nav>
          <form id="editForm" novalidate>
          <div class="tab-content d-flex" id="nav-tabContent">
              <div class="tab-pane fade show active" id="nav-address" role="tabpanel" aria-labelledby="nav-address-tab">
                  <div class="columns mt-4">
                      <div class="col-9">
                          <label for="Properties_street" class="form-label">Street Address</label>
                          <input class="form-control" value="<?php //echo $model->street ?>" name="Properties_street" required></input>
                      </div>
                      <div class="col-3">
                          <label for="Properties_unit" class="form-label">Unit #</label>
                          <input class="form-control" value="<?php //echo $model->unit ?>" name="Properties_unit"></input>
                      </div>
                      <div class="col-6 mt-4">
                          <label for="Properties_city" class="form-label">City</label>
                          <input class="form-control" value="<?php //echo $model->city ?>" name="Properties_city" readonly></input>
                      </div>
                      <div class="col-3 mt-4">
                          <label for="Properties_state" class="form-label">State</label>
                          <input class="form-control" value="<?php //echo $model->state ?>" name="Properties_state" readonly></input>
                      </div>
                      <div class="col-3 mt-4">
                          <label for="Properties_zip" class="form-label">Zipcode</label>
                          <input class="form-control" value="<?php //echo $model->zipcode ?>" name="Properties_zipcode" readonly></input>
                      </div>
                  </div>
              </div>
              <div class="tab-pane fade" id="nav-info" role="tabpanel" aria-labelledby="nav-info-tab">
                  <div class="columns mt-4">
                      <div class="col-6">
                          <label for="Properties_type" class="form-label">Property Type</label>
                          <select class="custom-select form-control" name="Properties_type" >
                              <option value="Single Family">Single Family</option>
                              <option value="Apartment/Condo/TIC">Condo/Coop/TIC/Loft</option>
                              <option value="Multi-Family">Multi-Family</option>
                              <option value="Commercial">Commercial</option>
                              <option value="Land">Land</option>
                          </select>
                      </div>
                      <div class="col-6">
                          <label for="Properties_price" class="form-label">Asking Price</label>
                          <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">$</span>
                              </div>
                              <input type="number" class="form-control" name="Properties_price" placeholder="Listing Price" value="<?php //echo $model->price ?>" aria-label="Price" aria-describedby="basic-addon1"></input>
                          </div>
                      </div>
                      <div class="col-4 mt-4">
                          <label for="Properties_bedrooms" class="form-label">Bedrooms</label>
                          <input type="number" class="form-control" value="<?php //echo $model->bedrooms ?>" name="Properties_bedrooms"></input>
                      </div>
                      <div class="col-4 mt-4">
                          <label for="Properties_bathrooms" class="form-label">Bathrooms</label>
                          <input type="number" class="form-control" value="<?php //echo $model->bathrooms ?>" name="Properties_bathrooms"></input>
                      </div>
                      <div class="col-4 mt-4">
                          <label for="Properties_yearBuilt" class="form-label">Year Built</label>
                          <input type="number" class="form-control" value="<?php //echo $model->built ?>" name="Properties_built"></input>
                      </div>
                      <div class="col-6 mt-4">
                          <label for="Properties_sqft" class="form-label">~Sq. Footage</label>
                          <input type="number" class="form-control" value="<?php //echo $model->sqft ?>" name="Properties_sqft"></input>
                      </div>
                      <div class="col-6 mt-4">
                          <label for="Properties_lotSize" class="form-label">Lot Size</label>
                          <div class="input-group mb-3">
                              <input type="number" class="form-control" value="<?php //echo $model->lot_size ?>" name="Properties_lot_size"></input>
                              <select class="custom-select">
                                  <option>SqFt</option>
                                  <option>Acres</option>
                              </select>
                          </div>
                      </div>
                      <div class="column col-12 mt-4">
                          <label for="Properties_description" class="form-label">Description</label>
                          <textarea rows="5" class="form-control" name="Properties_description"><?php echo $model->description ?></textarea>
                      </div>
                  </div>
              </div>
             <!--  <div class="tab-pane fade" id="nav-photos" role="tabpanel" aria-labelledby="nav-photos-tab">...</div> -->
          </div>
         </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="saveEditBtn" class="btn btn-primary">Save changes</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div id="moreInfoModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Property Information</h5>
        <button id="moreInfoClose" type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="max-height: calc(100vh - 15rem);overflow-y: auto;height: 25rem;">
          <div class="bordered mb-4" <?php //echo Properties::getBackgroundImage(null, $model->street, $model->city, $model->state, "height: 200px;border: 0.05rem solid #e7e9ed;border-radius: 0.2rem") ?>></div>
          <div class="row">
              <div class="col-sm d-flex flex-column">
                  <div>Beds</div>
                  <div><?php //echo $model->bedrooms; ?></div>
              </div>
              <div class="col-sm d-flex flex-column">
                  <div>Baths</div>
                  <div><?php //echo $model->bathrooms; ?></div>
              </div>
              <div class="col-sm d-flex flex-column">
                  <div>Year Built</div>
                  <div><?php //echo $model->built; ?></div>
              </div>
          </div>
          <div class="row">
              <div class="col-sm d-flex flex-column">
                  <div>Lot Size</div>
                  <div><?php //echo $model->lot_size; ?></div>
              </div>
              <div class="col-sm d-flex flex-column">
                  <div>SqFt</div>
                  <div><?php //echo $model->sqft ? $model->sqft : 0; ?></div>
              </div>
              <div class="col-sm d-flex flex-column">
                  <div>$/SqFt</div>
                  <div><?php //echo Yii::app()->locale->numberFormatter->formatCurrency($model->price / ($model->sqft > 0 ? $model->sqft : 1), 'USD'); ?></div>
              </div>

          </div>
          <div class="row">
              <div class="col-sm d-flex flex-column">
                  <div>Price</div>
                  <div><?php //echo Yii::app()->locale->numberFormatter->formatCurrency($model->price ? $model->price : 0, 'USD'); ?></div>
              </div>
          </div>
          <div class="row">
              <div class="col-sm d-flex flex-column">
                  <div>Description</div>
                  <div class="text-truncate"><?php //echo $model->description; ?></div>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

<div id="sharePropertyModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">SHARE PROPERTY</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <nav>
              <div class="nav nav-pills nav-justified d-flex" id="nav-tab" role="tablist">
                  <a class="nav-item nav-link active" id="nav-address-tab" data-toggle="tab" href="#nav-email" role="tab" aria-controls="nav-email" aria-selected="true">Email</a>
                  <a class="nav-item nav-link" id="nav-address-tab" data-toggle="tab" href="#nav-link" role="tab" aria-controls="nav-link" aria-selected="true">Link</a>
              </div>
          </nav>
          <div class="tab-content d-flex" id="nav-tabContent">
              <div class="tab-pane fade show active" id="nav-email" role="tabpanel" aria-labelledby="nav-email-tab" style="width: 100%;">
                  <div class="pt-3">
                      <label for="shareEmailTo">To:</label>
                      <input type="email" name="shareEmailTo" class="form-control form-control-sm" style="width: 20rem;" id="shareEmailTo" required></input>
                      <label for="shareEmailSubject" class="pt-2">Email Subject:</label>
                      <input name="shareEmailSubject" class="form-control form-control-sm" style="width: 20rem;" id="shareEmailSubject" required></input>
                      <label for="shareEmailMessage" class="pt-2">Email Message:</label>
                      <textarea name="shareEmailMessage" class="form-control form-control" rows=10 id="shareEmailMessage" required>
Hi there,

    Here is the Property Info Packet for <?php //echo $model->street; ?>. You can share it with buyers or your team by clicking the blue "Share" button. Please do not forward this email, the link is specific to you.

Quick note: your client's information is only visible to you and your team. X2RE is great about security. Let me know if you have any questions,

<?php echo User::getMe()->firstName . " " . User::getMe()->lastName; ?></textarea>
                  </div>
              </div>
              <div class="tab-pane fade show" id="nav-link" style="width: 100%;" role="tabpanel" aria-labelledby="nav-link-tab">
                  <div class="form-group col-auto">
                      <div id="sharePropDiv" class="input-group pt-4">
                          <input id="sharePropInput" style="margin: 0 !important;" class="form-control" readonly value="<?php echo substr(Yii::app()->createAbsoluteUrl(''), 0, -9) . 'index.php/properties/publicView/' . $model->id; ?>"></input>
                          <div class="input-group-append"><i id="shareLinkCopy" onclick="copyToClipboard()" class="fas fa-clipboard input-group-text"></i></div>
                      </div>
                      <div style="display: none;" id="copySuccess" >Link Copied!</div>
                  </div>
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button id="sendShareEmailBtn" type="button" class="btn btn-primary">Send Email</button>
      </div>
    </div>
  </div>
</div>

<div id="pdf-nav" class="pdf-nav bg-dark py-4" style="display: none;">
    <div class="row col-4 ml-2">
        <button id="prev" type="button" class="btn btn-outline-light btn-sm"  aria-label="Prev">Prev</button>
        <button id="next" type="button" class="btn btn-outline-light btn-sm ml-2" aria-label="Next">Next</button>
    </div>
    <div class="row col-4 text-light" style="justify-content: center;">
        Page: 
        <div id="page_num" class="text-light px-2"></div>
        /
        <div id="page_count" class="text-light pl-2"></div>
    </div>
    <div class="row col-4" style="justify-content: flex-end;">
        <button id="docViewClose" type="button" class="btn btn-outline-light" aria-label="Close">
            Close
        </button>
    </div>
</div>

<div id="docViewModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 1224px;" role="document">
    <div class="modal-content">
      <div class="modal-body" style="width:auto; overflow-y: auto; padding: 3rem 0 0 0;">
          <div id="docView">
              <canvas id="pdf"></canvas>
          </div>
      </div>
    </div>
  </div>
</div>

<div id="renameDocModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
          <input id="renameInput" class="form-control"></input>
      </div>
      <div class="modal-footer">
        <button id="renameBtn" type="button" class="btn btn-primary">Rename Document</button>
      </div>
    </div>
  </div>
</div>

<div id="sendDisclosureModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">SEND DISCLOSURES FOR SIGNING</h5>
        <button id="moreInfoClose" type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="overflow-y: auto;">
          <div id="accordion">
              <div class="card">
                <div class="card-header" id="recipientsHeading">
                  <h5 class="mb-0">
                    <div class="link-div" data-toggle="collapse" data-target="#recipientsCollapse" aria-expanded="true" aria-controls="recipientsCollapse">
                      RECIPIENTS
                    </div>
                  </h5>
                </div>

                <div id="recipientsCollapse" class="collapse show" aria-labelledby="recipientsHeading" data-parent="#accordion">
                  <div class="card-body">
                      <form id="recipientForm">
                          <div class="row input-group">
                              <input class="form-control" type="name" name="firstName" placeholder="First Name" />
                              <input class="form-control" type="name" name="lastName" placeholder="Last Name" />
                              <input class="form-control" type="email" name="email" placeholder="Email Address" />
                              <a id="addRecipBtn" title="Add recipient" class="recipBtn tooltip-test"><i class="pl-2 pt-2 fa fas fa-plus" style="position: absolute;top: 5;"></i></a>
                          </div>
                      </form>
                  </div>
                </div>
              </div>
              <div class="card">
                <div class="card-header" id="docsHeading">
                  <h5 class="mb-0">
                    <div class="link-div" data-toggle="collapse" data-target="#docsCollapse" aria-expanded="false" aria-controls="docsCollapse">
                      DISCLOSURE DOCUMENTS
                    </div>
                  </h5>
                </div>
                <div id="docsCollapse" class="collapse" aria-labelledby="docsHeading" data-parent="#accordion">
                  <div class="card-body">
                      <form id="docsForm">
                          <div class="col-2">
                              <?php /*foreach($doc_dataProvider->getData() as $docData): ?>
                                  <input data-id="<?php echo $signDocsArr[$docData->id]; ?>" class="form-check-input" type="checkbox" checked="checked">
                                  <label class="form-check-label active"><?php echo $docData->name; ?></label>
                                  <?php endforeach; */?>
                          </div>
                      </form>
                  </div>
                </div>
              </div>
              <div class="card">
                <div class="card-header" id="emailHeading">
                  <h5 class="mb-0">
                    <div class="link-div" data-toggle="collapse" data-target="#emailCollapse" aria-expanded="false" aria-controls="emailCollapse">
                      EMAIL MESSAGE
                    </div>
                  </h5>
                </div>
                <div id="emailCollapse" class="collapse" aria-labelledby="emailHeading" data-parent="#accordion">
                  <div class="card-body">
                      <label for="email-subject">Email Subject</label>
                      <input class="form-control form-control-sm" id="email-subject" name="email-subject"></input>
                      <label class="pt-2" for="email-templates">Email Template:</label>
                      <select class="form-control form-control-sm" id="email-templates" name="email-templates">
                          <option>Custom Message</option>
                      </select>
                      <div id="email-body" rows="4"></div>
                  </div>
                </div>
              </div>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button id="sendDiscBtn" type="button" class="btn btn-primary">Send</button>
      </div>
    </div>
  </div>
</div>


