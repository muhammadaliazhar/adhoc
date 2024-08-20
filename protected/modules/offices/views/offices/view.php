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

$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerCss('contactRecordViewCss', "

#content {
    background: none !important;
    border: none !important;
}
");

Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');

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
    $('#Offices_c_street_address__c').val('');
    $('#Offices_c_city__c').val('');
    $('#Offices_c_state__c').val('');
    $('#Offices_c_country__c').val('');
    $('#Offices_c_zipcode__c').val('');

    // Fill geographic fields
    var streetAddr = '';
    place.address_components.forEach(function(addrComp){
        switch(addrComp.types[0]) {
            case 'street_number':
                 streetAddr += addrComp.long_name;
                 break;
            case 'route':
                 streetAddr += ' ' + addrComp.long_name;
                 $('#Offices_c_street_address__c').val(streetAddr);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Offices_c_street_address__c').val('Withdrawn');
                 }
                 break;
            case 'country':
                 $('#Offices_c_country__c').val(addrComp.long_name);
                 break;
            case 'postal_code':
                 $('#Offices_c_zipcode__c').val(addrComp.long_name);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Offices_c_zipcode__c').val('Withdrawn');
                 }
                 break;
            case 'administrative_area_level_1':
                 $('#Offices_c_state__c').val(addrComp.long_name);
                 break;
            case 'locality':
                 $('#Offices_c_city__c').val(addrComp.long_name);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Offices_c_city__c').val('Withdrawn');
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
    $('#Offices_c_street_address__c_field a.edit-icon').click(function () {
        $('#Offices_c_street_address__c').prop('readonly', true);
        $('#Offices_c_city__c_field a.edit-icon').trigger('click');
        $('#Offices_c_city__c').prop('readonly', true);
        $('#Offices_c_state__c_field a.edit-icon').trigger('click');
        $('#Offices_c_state__c').prop('readonly', true);
        $('#Offices_c_zipcode__c_field a.edit-icon').trigger('click');
        $('#Offices_c_zipcode__c').prop('readonly', true);
        $('#Offices_c_country__c_field a.edit-icon').trigger('click');
        $('#Offices_c_country__c').prop('readonly', true);
        $('div#googlemap').show();
        $('div#pac-card').show();
        $('#location-withdrawn_field').show();
        google.maps.event.trigger(map, 'resize');
    });

    $('#Offices_c_zipcode__c_field a.cancel-icon').click(function () {
        $('div#googlemap').hide();
        $('div#pac-card').hide();
        $('#location-withdrawn_field').hide();
    });

    $('#Offices_c_zipcode__c_field a.confirm-icon').click(function () {
        $('div#googlemap').hide();
        $('div#pac-card').hide();
        $('#location-withdrawn_field').hide();
    });
", CClientScript::POS_END);

Yii::app()->clientScript->registerScript('placeMap', "
  var pacCard = $('#pac-card');
  $('.formSection:nth-child(1)').append($('#googlemap'));
  $('#Offices_c_street_address__c_field').append($('#location-withdrawn_field'));
  $('#googlemap').hide();
  pacCard.hide();
  $('#location-withdrawn_field').hide();
  $('#Offices_c_city__c_field a.edit-icon').hide();
  $('#Offices_c_state__c_field a.edit-icon').hide();
  $('#Offices_c_zipcode__c_field a.edit-icon').hide();
  $('#Offices_c_country__c_field a.edit-icon').hide();
", CClientScript::POS_END);

include("protected/modules/offices/officesConfig.php");

$actionMenuViewItem = RecordViewLayoutManager::getViewActionMenuListItem ($model->id);
if (isset ($actionMenuViewItem['url'])) unset ($actionMenuViewItem['url']);

$this->actionMenu = $this->formatMenu(array(
    array('label' => Yii::t('module', '{X} List', array('{X}' => Modules::itemDisplayName())), 'url' => array('index')),
    array('label' => Yii::t('module', 'Create {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('create')),
    $actionMenuViewItem,
    array('label' => Yii::t('module', 'Edit {X}', array('{X}' => Modules::itemDisplayName())), 'url' => array('update', 'id' => $model->id)),
    array('label' => Yii::t('module', 'Delete {X}', array('{X}' => Modules::itemDisplayName())), 'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'))),
    array(
        'label' => Yii::t('app', 'Send Email'), 'url' => '#',
        'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')),
    ModelFileUploader::menuLink(),
    //array('label' => Yii::t('quotes', 'Quotes/Invoices'), 'url' => 'javascript:void(0)', 'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')),
    array(
        'label' => Yii::t('app', 'Print Record'),
        'url' => '#',
        'linkOptions' => array(
            'onClick' => "window.open('" .
            Yii::app()->createUrl('/site/printRecord', array(
                'modelClass' => "Offices",
                'id' => $model->id,
                'pageTitle' =>
                Yii::t('app', '{X}', array('{X}' => Modules::itemDisplayName())) . ': ' . $model->name
            )) . "');"
        ),
    ),
    RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
), array ('X2Model' => $model));

$modelType = json_encode("Offices");
$modelId = json_encode($model->id);

Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
	x2.FileUploader.toggle('attachment');
});");
?>
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
        <div class="page-title offices">
            <h2>
                <?php echo Yii::t('module', 'View {X}', array('{X}' => Modules::itemDisplayName())); ?>: <?php
                echo $model->renderAttribute ('name');
                ?>
            </h2>
            <?php
            echo X2Html::editRecordButton($model);
            echo X2Html::emailFormButton();
            echo X2Html::inlineEditButtons();
            ?>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
            <?php 
            $this->widget ('DetailView', array(
                'model' => $model
            ));
            //$this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $model, 'modelName' => 'offices')); ?>

<?php
$this->widget('InlineEmailForm', array(
    'attributes' => array(
        'to' => implode(', ', $model->getRelatedContactsEmails()),
        'modelName' => get_class($model),
        'modelId' => $model->id,
    ),
    'insertableAttributes' =>
    array(
        Yii::t('module', '{modelName} Attributes', array('{modelName}' => get_class($model))) =>
        $model->getEmailInsertableAttrs($model)
    ),
    'startHidden' => true,
        )
);


$this->widget ('ModelFileUploader', array(
    'associationType' => 'offices',
    'associationId' => $model->id,
));

?>
    <div id="quote-form-wrapper">
    <?php
    $this->widget('InlineQuotes', array(
        'startHidden' => true,
        'contactId' => $model->id,
        'modelName' => X2Model::getModuleModelName()
    ));
    ?>
    </div>
</div>
<?php
$this->widget('X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'model' => $model,
    ));
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
<!-- Replace the value of the key parameter with your own API key. -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAHOUH0B8NME7_S6lQpkhZ2b2uMgM9hEiM&libraries=places&callback=initMap"
        async defer></script>
