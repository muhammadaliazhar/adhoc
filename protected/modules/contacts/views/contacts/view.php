<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/

$modelName = str_replace('\'', '', $model->name);
$modelListingLookup = addslashes($model->c_listinglookup__c);
$modelListingNumberLookup = addslashes($model->c_listing_number_lookup);
$jsVars = "modelId = '$model->id'; modelName = '$modelName'; modelEmail = '$model->email'; modelListingLookup = '$modelListingLookup'; modelListingNumberLookup = '$modelListingNumberLookup'; modelType = 'Contacts';";
Yii::app()->clientScript->registerScript('jsVars', $jsVars);

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/Relationships.js');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/docusign.js?v=55556');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/x2sign.js?23443332');

Yii::app()->clientScript->registerCss('contactRecordViewCss',"

#content {
    background: none !important;
    border: none !important;
}
.show-left-bar .page-title > .x2-button {
    display: none !important;
}

");

Yii::app()->clientScript->registerCss('geographiesCreateCss',"
/* Always set the map height explicitly to define the size of the div
 * element that contains the map. */
#googlemap {
  height: 350px !important;
}

#description {
  font-family: Roboto;
  font-size: 15px;
  font-weight: 300;
}

#infowindow-content .title {
  font-weight: bold;
}

#infowindow-content {
  display: none;
}

#googlemap #infowindow-content {
  display: inline;
}

.pac-card {
  margin: 10px 10px 0 0;
  border-radius: 2px 0 0 2px;
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  outline: none;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
  background-color: #fff;
  font-family: Roboto;
}

#pac-container {
  padding-bottom: 12px;
  margin-right: 12px;
}

.pac-controls {
  display: inline-block;
  padding: 5px 11px;
}

.pac-controls label {
  font-family: Roboto;
  font-size: 13px;
  font-weight: 300;
}

#pac-input {
  background-color: #fff;
  font-family: Roboto;
  font-size: 15px;
  font-weight: 300;
  margin-left: 12px;
  padding: 0 11px 0 13px;
  text-overflow: ellipsis;
  width: 400px;
}

#pac-input:focus {
  border-color: #4d90fe;
}

#title {
  color: #fff;
  background-color: #4d90fe;
  font-size: 25px;
  font-weight: 500;
  padding: 6px 12px;
}
");

Yii::app()->clientScript->registerCss('verifyButtonCss',"
.verifyButton{
    border-radius: 8px;
}
.verifyImage{
    float: left;
    width: 25px;
    margin-top: 6px;
}
.verifyText{
    float:left;
    margin-left: 12px;
    margin-top: 2px;
}
.show{
    display:block !important;
}
.hide{
    display:none !important;
}
#verifyByAdmin ,#verifyBuyer {
    background-color: rgb(0 0 0 / 50%) !important;
}
");

Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');

$this->setPageTitle(empty($model->name) ? $model->firstName." ".$model->lastName : $model->name);

$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerScript('hints', '
    $(".hint").qtip();
    x2.FileUploader.toggle("attachment");
');

Yii::app()->clientScript->registerScript('exportInquiryListings', "
    var btn = $('#inquiries-export-button');
    btn.on('click', function () {
        // GET to current record
        $.ajax({
            url: location.origin + '/index.php/listings2/exportInquiryListings',
            data: {
                contactId: window.location.pathname.split('/').pop()
            },
            success: function (resp) {
                createDownload();
            },
            error: function (resp) {
                console.log(resp);
            }
        });
        function createDownload(page = 0) {
            $.ajax({
                url: location.origin + '/index.php/admin/exportModelRecords',
                data: {
                    page: page,
                    model: 'Listings2'
                },
                success: function (resp) {
                    var message = JSON.parse(resp.message);
                    var url = location.origin + '/index.php/admin/downloadData?' + 'file=' + message.dlUrl + '&deleteAfter=1';
                    var a = $('<a>',{
                        href: url,
                    });
                    a.on('click', function () {
                        window.location.href = url;
                    });
                    a.appendTo('body');
                    console.log(a);
                    a.click();
                },
            });
        }
    });
");

Yii::app()->clientScript->registerScript('geographiesCreate', "
  // This example requires the Places library. Include the libraries=places
  // parameter when you first load the API. For example:
  // <script src=\"https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places\">

function initMap() {
  map = new google.maps.Map(document.getElementById('googlemap'), {
    center: {lat: 0, lng: 0},
    zoom: 2
  });
  var card = document.getElementById('pac-card');
  var input = document.getElementById('pac-input');
  var types = document.getElementById('type-selector');
  var strictBounds = document.getElementById('strict-bounds-selector');

  map.controls[google.maps.ControlPosition.TOP_RIGHT].push(card);

  autocomplete = new google.maps.places.Autocomplete(input);

  // Bind the map's bounds (viewport) property to the autocomplete object, 
  // so that the autocomplete requests use the current map bounds for the
  // bounds option in the request.
  autocomplete.bindTo('bounds', map);

  var infowindow = new google.maps.InfoWindow();
  var infowindowContent = document.getElementById('infowindow-content');
  infowindow.setContent(infowindowContent);
  var marker = new google.maps.Marker({
    map: map,
    anchorPoint: new google.maps.Point(0, -29)
  });

  autocomplete.addListener('place_changed', function() {
    infowindow.close();
    marker.setVisible(false);
    var place = autocomplete.getPlace();
    var locationInfo = document.getElementById('pac-input').value.split(\", \");

    console.log(place.address_components);
    // Clear/Reset input fields
    $('#Contacts_address').val('');
    $('#Contacts_city').val('');
    $('#Contacts_country').val('');
    $('#Contacts_state').val('');
    $('#Contacts_c_zipcode').val('');

    // Fill geographic fields
    var streetAddr = '';
    place.address_components.forEach(function(addrComp){
        switch(addrComp.types[0]) {
            case 'street_number':
                 streetAddr += addrComp.long_name;
                 break;
            case 'route':
                 streetAddr += ' ' + addrComp.long_name;
                 $('#Contacts_address').val(streetAddr);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Contacts_address').val('Withdrawn');
                 }
                 break;
            case 'administrative_area_level_2':
                 $('#Contacts_c_county').val(addrComp.long_name);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Contacts_c_county').val('Withdrawn');
                 }
                 break;
            case 'country':
                 $('#Contacts_country').val(addrComp.long_name);
                 break;
            case 'postal_code':
                 $('#Contacts_c_zipcode').val(addrComp.long_name);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                   $('#Contacts_c_zipcode').val('Withdrawn');
                 }
                 break;
            case 'administrative_area_level_1':
                 $('#Contacts_state').val(addrComp.long_name);
                 break;
            case 'locality':
                 $('#Contacts_city').val(addrComp.long_name);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Contacts_city').val('Withdrawn');
                 }
                 break;
            default:
        }
    });

    if (!place.geometry) {
      // User entered the name of a Place that was not suggested and
      // pressed the Enter key, or the Place Details request failed.
      window.alert(\"No details available for input: '\" + place.name + \"'\");
      return;
    }

    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
      map.fitBounds(place.geometry.viewport);
    } else {
      map.setCenter(place.geometry.location);
      map.setZoom(17);  // Why 17? Because it looks good.
    }
    marker.setPosition(place.geometry.location);
    marker.setVisible(true);
    
    var address = '';
    if (place.address_components) {
      address = [
        (place.address_components[0] && place.address_components[0].short_name || ''),
        (place.address_components[1] && place.address_components[1].short_name || ''),
        (place.address_components[2] && place.address_components[2].short_name || '')
      ].join(' ');
    }

    infowindowContent.children['place-icon'].src = place.icon;
    infowindowContent.children['place-name'].textContent = place.name;
    infowindowContent.children['place-address'].textContent = address;
    infowindow.open(map, marker);
  });

  // Sets a listener on a radio button to change the filter type on Places
  // Autocomplete.
  function setupClickListener(id, types) {
    var radioButton = document.getElementById(id);
    radioButton.addEventListener('click', function() {
      autocomplete.setTypes(types);
    });
  }

  setupClickListener('changetype-all', []);
  setupClickListener('changetype-address', ['address']);
  setupClickListener('changetype-establishment', ['establishment']);
  setupClickListener('changetype-geocode', ['geocode']);

  document.getElementById('use-strict-bounds')
      .addEventListener('click', function() {
        console.log('Checkbox clicked! New state=' + this.checked);
        autocomplete.setOptions({strictBounds: this.checked});
      });
}
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript('editGeoLocation', "
    // Set input fields to read-only when edit buttons are clicked
    $('#Contacts_address_field a.edit-icon').click(function () {
        $('#Contacts_address').prop('readonly', true);
        $('#Contacts_city_field a.edit-icon').trigger('click');
        $('#Contacts_city').prop('readonly', true);
        $('#Contacts_state_field a.edit-icon').trigger('click');
        $('#Contacts_state').prop('readonly', true);
        $('#Contacts_country_field a.edit-icon').trigger('click');
        $('#Contacts_country').prop('readonly', true);
        $('#Contacts_c_zipcode_field a.edit-icon').trigger('click');
        $('#Contacts_c_zipcode').prop('readonly', true);
        $('div#googlemap').show();
        $('div#pac-card').show();
        $('#location-withdrawn_field').show();
        google.maps.event.trigger(map, 'resize');
    });

    $('#Contacts_state_field a.cancel-icon').click(function () {
        $('div#googlemap').hide();
        $('div#pac-card').hide();
        $('#location-withdrawn_field')
    });

    $('#Contacts_state_field a.confirm-icon').click(function () {
        $('div#googlemap').hide();
        $('div#pac-card').hide();
        $('#location-withdrawn_field')
    });
", CClientScript::POS_END);

Yii::app()->clientScript->registerScript('placeMap', "
  var pacCard = $('div#pac-card');
  $('.formSection:nth-child(3)').append($('#googlemap'));
  $('#Contacts_address_field').append($('#location-withdrawn_field'));
  $('#googlemap').hide();
  pacCard.hide();
  $('#location-withdrawn_field').hide();
  $('#Contacts_city_field a.edit-icon').hide();
  $('#Contacts_state_field a.edit-icon').hide();
  $('#Contacts_country_field a.edit-icon').hide();
  $('#Contacts_c_zipcode_field a.edit-icon').hide();
", CClientScript::POS_END);

/*if($this->beginCache(User::getMe()->username.get_class($model) . "_view".(string)$model->id, array('dependency'=>array(
        'class'=>'system.caching.dependencies.CDbCacheDependency',
        'sql'=>'SELECT MAX(timestamp) FROM x2_changelog WHERE type="Contacts" AND itemId='.(string)$model->id)))) {*/

// find out if we are subscribed to this contact
$result = Yii::app()->db->createCommand()
        ->select()
        ->from('x2_subscribe_contacts')
        ->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), 
            array(':contact_id' => $model->id, 'user_id' => Yii::app()->user->id))
        ->queryAll();
$subscribed = !empty($result); // if we got any results then user is subscribed

$modTitles = array(
    'contact' => Modules::displayName(false),
    'account' => Modules::displayName(false, "Accounts"),
    'opportunity' => Modules::displayName(false, "Opportunities"),
);

$authParams['X2Model'] = $model;

$opportunityModule = Modules::model()->findByAttributes(array('name' => 'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name' => 'accounts'));
$serviceModule = Modules::model()->findByAttributes(array('name' => 'services'));

$menuOptions = array(
    'all', 'lists', 'create', 'view', 'edit', 'share',  
    'email', /*'attach', 'quotes',*/ 'print', 'viewOnMap', 'editLayout', 'addRecordAlias', 'convert',
);

if(Yii::app()->params->isAdmin){
        $menuOptions[] = 'delete';
}

$menuOptions[] = ($subscribed ? 'unsubscribe' : 'subscribe');
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions, $model, $authParams);

$modelType = json_encode("Contacts");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('subscribe', "
$(function() {
    $('body').data('subscribed', ".json_encode($subscribed).");
    $('body').data('subscribeText', ".json_encode(Yii::t('contacts', 'Subscribe')).");
    $('body').data('unsubscribeText', ".json_encode(Yii::t('contacts', 'Unsubscribe')).");
    $('body').data('modelType', $modelType);
    $('body').data('modelId', $modelId);


    $('.x2-subscribe-button').qtip();
});

// subscribe or unsubscribe from this contact
function subscribe(link) {
    $('body').data('subscribed', !$('body').data('subscribed')); // subscribe or unsubscribe
    $.post('".$this->createUrl('/contacts/contacts/subscribe')."', {ContactId: '{$model->id}', Checked: $('body').data('subscribed')}); // tell server to subscribe / unsubscribe
    if( $('body').data('subscribed') )
        link.html($('body').data('unsubscribeText'));
    else
        link.html($('body').data('subscribeText'));
    return false; // stop event propagation
}

", CClientScript::POS_HEAD);

/* x2customstart */ /* JLaw Jul1 2019 */
// render flash if an error occurs
if ($message = Yii::app()->user->getFlash('error')) {
    Yii::app()->clientScript->registerScript('contactViewFlash', "
        x2.topFlashes.displayFlash ('$message', 'error', 'clickOutside');
        ", CClientScript::POS_LOAD);
}
/* x2customend */

// widget layout
$layout = Yii::app()->params->profile->getLayout();
$themeUrl = Yii::app()->theme->getBaseUrl();

$this->widget('X2ModelConversionWidget', array(
    'buttonSelector' => '#convert-contact-button',
    'targetClass' => 'Sellers2',
    'namespace' => 'Sellers2',
    'model' => $model,
));


?>
<script>
function sendNdaQuick(modelId) {
       $.ajax({
            url: yii.baseUrl+'/index.php/x2sign/SendNDAQuick',
            type: 'POST',
            data: {
                modelId: modelId,
                contact: true,
            },
            success: function (data) {
                alert("NDA SENT");
            },
            error: function (data) {
                alert(data.responseText);
            },
            complete: function () {
                //loading.remove();
            }
        });


}

</script>


<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
<div class="page-title icon contacts">
	 <h2 style="float:left"><?php echo CHtml::encode($model->name); ?></h2>

    <?php 
    //$this->renderPartial('_vcrControls', array('model' => $model)); 
    $this->widget('RecordAliasesWidget', array(
        'model' => $model
    ));
    if (Yii::app()->settings->googleIntegration) {
        echo CHtml::link(
            '', $this->createUrl('googleMaps', array('contactId' => $model->id, 'noHeatMap' => 1)),
            array(
                'class' => 'x2-button icon map right',
                'title' => Yii::t('app', 'View {module} on Map', array(
                    '{module}' => $modTitles['contact'],
                )),
            )
        );
    }
    if(Yii::app()->user->checkAccess('ContactsUpdate', $authParams)){
        if(!empty($model->company) && is_numeric($model->company)) {
            echo CHtml::link(
                '<span></span>', '#',
                array(
                    'class' => 'x2-button icon sync right hint',
                    'id' => $model->id.'-account-sync',
                    'title' => Yii::t('contacts', 'Clicking this button will pull any relevant '.
                        'fields from the associated {account} record and overwrite the {contact} '.
                        'data for those fields.  This operation cannot be reversed.',array(
                            '{account}' => $modTitles['account'],
                            '{contact}' => $modTitles['contact'],
                        )),
                    'submit' => array(
                        'syncAccount',
                        'id' => $model->id
                    ),
                    'confirm' => 'Are you sure you want to overwrite this record\'s fields with '.
                        'relevant Account data?'
                )
            );
        }
    }
        echo CHtml::link(
            '', $this->createUrl('update', array('id' => $model->id)),
            array(
                'class' => 'x2-button icon edit right',
                'title' => Yii::t('app', 'Edit {module}', array(
                    '{module}' => $modTitles['contact'],
                )),
            )
        );
    echo X2Html::emailFormButton();
    echo X2Html::inlineEditButtons();
    echo X2Html::X2SignSendButton($model->id, addslashes($model->name), get_class($model), $model->email);

    echo X2Html::addSendNDAButton($model->id);

    if(User::getMe()->canUseNDAAuto())echo X2Html::addSendNDAButton($model->id);
    /* x2entstart */
    // echo '<div id="docusign-nda" class="x2-button right">Send NDA</div>';
    //echo '<div id="docusign-send" class="x2-button right">Send Document</div>';
    echo '<div id="docusign-exp" class="x2-button right">Send Buyer Profile</div>';
    echo '<div id="docusign-bli" class="x2-button right">Send BLI</div>';
    /* x2entend */
	
    //JUSTIN CUSTOM EXCLUSIVITY
    if(isset($model) && (($model->c_status__c != "PEG") && (!$model->private))){
        echo '<div id="docusign-exc" class="x2-button right">Send Exclusivity</div>';
    }

    $imageUrl=Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/verify_buyer.png';
    //if(User::getMe()->canUseNDAAuto()){
    if(Yii::app()->params->isAdmin){
        $imageUrl=Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/verify_buyer.png';
        if($model->c_verify_buyer==1){
            echo '<div id="verifyByAdmin" class="x2-button left verifyButton hide">Click to Verify</div>';
            echo '<div id="unVerifyBuyer" class="x2-button right show">Un Verify Buyer</div>';
            echo ' <div id="verifiedShow" class="show">
                    <img id="avatarImage" class="verifyImage" src='.$imageUrl.' >
                    <div id="verified" class="verifyText" >VERIFIED BUYER</div>
                 </div>';
        }else{
            echo '<div id="verifyByAdmin" class="x2-button left verifyButton show">Click to Verify</div>';
            echo '<div id="unVerifyBuyer" class="x2-button right hide">Un Verify Buyer</div>';
            echo ' <div id="verifiedShow" class="hide">
                    <img id="avatarImage" class="verifyImage" src='.$imageUrl.' >
                    <div id="verified" class="verifyText"  >VERIFIED BUYER</div>
                  </div>';
        }
    }
    else{
        if($model->c_verify_buyer==1){
            echo ' <div id="verifiedShow" class="show">
            <img id="avatarImage" class="verifyImage" src='.$imageUrl.'>
            <div id="verified" class="verifyText"  >VERIFIED BUYER</div>
         </div>';
        }else{
            echo '<div id="verifyBuyer" class="x2-button left verifyButton show" >Click to Verify</div>';
            echo '<div id="verifiedShow" class="hide">
                <img id="avatarImage" class="verifyImage" src='.$imageUrl.'>
                <div id="verified" class="verifyText"  >VERIFIED BUYER</div>
              </div>';
        }
    }
    //}



    ?>
</div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
    <div id='contacts-detail-view'> 
    <?php 
    // $this->renderPartial(
        // 'application.components.views.@DETAILVIEW', 
    $this->widget('DetailView', array(
        'model' => $model, 
        'modelName' => 'contacts'
    ));
    ?>
    </div>
    <?php

    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'to' => '"'.$model->name.'" <'.$model->email.'>, ',
            'modelName' => 'Contacts',
            'modelId' => $model->id,
            'targetModel' => $model,
        ),
        'startHidden' => true,
    ));

    /*     * * Begin Create Related models ** */

    $linkModel = X2Model::model('Accounts')->findByAttributes(array(
        'nameId' => $model->company
    ));
    if(isset($linkModel))
        $accountName = $linkModel->name;
    else
        $accountName = '';
    $createContactUrl = $this->createUrl('/contacts/contacts/create');
    $createAccountUrl = $this->createUrl('/accounts/accounts/create');
    $createOpportunityUrl = $this->createUrl('/opportunities/opportunities/create');
    $createCaseUrl = $this->createUrl('/services/services/create');
    $assignedTo = $model->assignedTo;
    $tooltip = (
        Yii::t('contacts', 'Create a new {opportunity} associated with this {contact}.', array(
            '{contact}' => $modTitles['contact'],
            '{opportunity}' => $modTitles['opportunity'],
        ))
    );
    $contactTooltip = (
        Yii::t('contacts', 'Create a new {contact} associated with this {contact}.', array(
            '{contact}' => $modTitles['contact'],
        ))
    );
    $accountsTooltip = (
        Yii::t('contacts', 'Create a new {account} associated with this {contact}.', array(
            '{contact}' => $modTitles['contact'],
            '{account}' => $modTitles['account'],
        ))
    );
    $caseTooltip = (
        Yii::t('contacts', 'Create a new {service} Case associated with this {contact}.', array(
            '{contact}' => $modTitles['contact'],
            '{service}' => Modules::displayName(false, "Services"),
        ))
    );
    $contactName = $model->firstName.' '.$model->lastName;
    $phone = $model->phone;
    $website = $model->website;
    $leadSource = $model->leadSource;
    $leadtype = $model->leadtype;
    $leadStatus = $model->leadstatus;
//*** End Create Related models ***/

    $this->widget ('ModelFileUploader', array(
            'associationType' => 'contacts',
            'associationId' => $model->id
        )); ?>

    <div id="quote-form-wrapper">
        <?php
        $this->widget('InlineQuotes', array(
            'startHidden' => true,
            'recordId' => $model->id,
            'contactId' => $model->id,
            'account' => $model->getLinkedAttribute('company', 'name'),
            'modelName' => X2Model::getModuleModelName ()
        ));
        ?>
    </div>
</div>

<?php
$this->widget('X2WidgetList', array(
    'model' => $model,
    'layoutManager' => $layoutManager,
    'widgetParamsByWidgetName' => array (
        'InlineRelationshipsWidget' => array (
            'defaultsByRelatedModelType' => array (
                'Accounts' => array (
                    'name' => $accountName,
                    'assignedTo' => $assignedTo,
                    'phone' => $phone,
                    'website' => $website
                ),
                'Contacts' => array (
                    'company' => $accountName,
                    'assignedTo' => $assignedTo,
                    'leadSource' => $leadSource,
                    'leadtype' => $leadtype,
                    'leadstatus' => $leadStatus
                ),
                'Opportunity' => array (
                    'accountName' => $accountName,
                    'assignedTo' => $assignedTo,
                ),
                'Services' => array (
                    'contactName' => $contactName,
                    'assignedTo' => $assignedTo,
                )
            )
        )
    )
));

/*$this->endCache();
}*/

?>

<table>
  <tr id="location-withdrawn_field" class="formItem leftLabel  inline-edit">
    <td class="label">
      <label>Withdraw Location?</label>
    </td>
    <td>
      <input type="checkbox" id="location-withdrawn-checkbox">
    </td>
  </tr>
</table>

<div class="pac-card" id="pac-card">
  <div>
    <div id="title">
      Autocomplete search
    </div>
    <div id="type-selector" class="pac-controls">
      <input type="radio" name="type" id="changetype-all" checked="checked">
      <label for="changetype-all">All</label>

      <input type="radio" name="type" id="changetype-establishment">
      <label for="changetype-establishment">Establishments</label>

      <input type="radio" name="type" id="changetype-address">
      <label for="changetype-address">Addresses</label>

      <input type="radio" name="type" id="changetype-geocode">
      <label for="changetype-geocode">Geocodes</label>
    </div>
    <div id="strict-bounds-selector" class="pac-controls">
      <input type="checkbox" id="use-strict-bounds" value="">
      <label for="use-strict-bounds">Strict Bounds</label>
    </div>
  </div>
  <div id="pac-container">
    <input id="pac-input" type="text"
        placeholder="Enter a location">
  </div>
</div>

<div id="googlemap"></div>
<div id="infowindow-content">
  <img src="" width="16" height="16" id="place-icon">
  <span id="place-name"  class="title"></span><br>
  <span id="place-address"></span>
</div>
<!-- Replace the value of the key parameter with your own API key.
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAHOUH0B8NME7_S6lQpkhZ2b2uMgM9hEiM&libraries=places&callback=initMap"
        async defer></script> -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyANDvDG3JnMJqJPtKFm4eoM6MAZu46LH1M&libraries=places&callback=initMap"
        async defer></script>
