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

include("protected/modules/listings2/listings2Config.php");

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('create')),
	array('label'=>Yii::t('module','View {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Edit {X}',array('{X}'=>Modules::itemDisplayName()))),
	array('label'=>Yii::t('module','Delete {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
	array('label'=>Yii::t('module','Share {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('share', 'id'=>$model->id)),
), array ('X2Model' => $model));
?>


<div class="page-title listings2"><h2><?php echo Yii::t('module','Update {X}',array('{X}'=>Modules::itemDisplayName())); ?> <?php echo $model->name; ?></h2></div>

<?php 
$this->widget ('FormView', array(
	'model' => $model
));
//echo $this->renderPartial('application.components.views.@FORMVIEW', array('model'=>$model,'users'=>$users, 'modelName'=>'listings2'));

Yii::app()->clientScript->registerScript('hidePlusSpan', "
           $('span.quick-create-button.create-Employees').hide();
           $('span.quick-create-button.create-Franchisees').hide();
", CClientScript::POS_END);


Yii::app()->clientScript->registerScript('updateListing2Subcategory',"
    var categoryDropdownValues = '';
    var lastSelection = null;
    $('#Listings2_c_subcategory_1__c').hide();
    $('#Listings2_c_subcategory_2__c').hide();
    $('#Listings2_c_subcategory_3__c').hide();
    $('#Listings2_c_subcategory_4__c').hide();

    $('select#Listings2_c_category__c').change(function() {
        categoryDropdownValues = $('select#Listings2_c_category__c').val();
        if ($(this).val() && $(this).val().length > 4) {
          $(this).val(lastSelection);
        } else {
          lastSelection = $(this).val();
        }
        $.ajax({
                type:  'GET',
                url: '/index.php/listings2/getItemsSubCategories',
                data: {
                    categoryTerm: JSON.stringify(categoryDropdownValues)
                },
                success: function (data) {
                    $('#dynamic_subcategory_dropdown_4').remove();
                    $('#dynamic_subcategory_dropdown_1').remove();
                    $('#dynamic_subcategory_dropdown_2').remove();
                    $('#dynamic_subcategory_dropdown_3').remove();
                    var dynamic_id = 'dynamic_subcategory_dropdown_';
                    var dropdownDatum = JSON.parse(data);
                    if (dropdownDatum[1] === 'subcategories') {
                        var dropdownDataArray = dropdownDatum[0];
                        var i=1;
                        var subcategory_id='';
                        for(var j=0;j < dropdownDataArray.length, i<5;j++) {
                            dynamic_id = dynamic_id + i.toString();
                            var id = '#'+dynamic_id;
                            dropdownData=dropdownDataArray[j];
                            var s = $('<select />');
                            subcategory_id = '#Listings2_c_subcategory_' + i.toString() + '__c';
                            s.prop('id', dynamic_id);
                            s.change(function() {
				if ($('#dynamic_subcategory_dropdown_1 option:selected').text())
                                    $('#Listings2_c_subcategory_1__c').val($('#dynamic_subcategory_dropdown_1 option:selected').text());
                                if ($('#dynamic_subcategory_dropdown_1 option:selected').text())
				    $('#Listings2_c_subcategory_2__c').val($('#dynamic_subcategory_dropdown_2 option:selected').text());
                                if ($('#dynamic_subcategory_dropdown_1 option:selected').text())
				    $('#Listings2_c_subcategory_3__c').val($('#dynamic_subcategory_dropdown_3 option:selected').text());
                                if ($('#dynamic_subcategory_dropdown_1 option:selected').text())
				    $('#Listings2_c_subcategory_4__c').val($('#dynamic_subcategory_dropdown_4 option:selected').text());
                            });
                            for(var val in dropdownData) {
                                $('<option />', {value: dropdownData[val], text: dropdownData[val].name}).appendTo(s);
                            }
                            if ($(id).length > 0){
    
                                $(id).val(dropdownData);
                            } else if (typeof dropdownData !== 'undefined' && dropdownData.length > 0){
                                if ($('#Listings2_c_category__c_field-input').length > 0){
                                    $('#Listings2_c_category__c_field-input').append(s); // or wherever it should be
                                } else {
				    $(subcategory_id+'_field').append(s);	
                                }
                           }
                            dynamic_id = 'dynamic_subcategory_dropdown_';
                            i=i+1;
                        }
                    }
                },
                error: function (error) {
                }
         })
    });
",CClientScript::POS_END);



Yii::app()->clientScript->registerCss('geographiesCreateCss',"
/* Always set the map height explicitly to define the size of the div
 * element that contains the map. */
#map {
  height: 300px !important;
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

#map #infowindow-content {
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

function initMap() {
  var map = new google.maps.Map(document.getElementById('map'), {
    center: {lat: 0, lng: 0},
    zoom: 2
  });
  var card = document.getElementById('pac-card');
  var input = document.getElementById('pac-input');
  var types = document.getElementById('type-selector');
  var strictBounds = document.getElementById('strict-bounds-selector');

  map.controls[google.maps.ControlPosition.TOP_RIGHT].push(card);

  var autocomplete = new google.maps.places.Autocomplete(input);

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
    // Clear/Reset input fields
    $('#Listings2_c_address').val('');
    $('#Listings2_c_city__c').val('');
    $('#Listings2_c_country__c').val('');
    $('#Listings2_c_state__c').val('');
    $('#Listings2_c_county__c').val('');
    $('#Listings2_c_postalcode').val('');

    // Fill geographic fields
    var streetAddr = '';
    place.address_components.forEach(function(addrComp){
        switch(addrComp.types[0]) {
            case 'street_number':
                 streetAddr += addrComp.long_name;
                 break;
            case 'route':
                 streetAddr += ' ' + addrComp.long_name;
                 $('#Listings2_c_address').val(streetAddr);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Listings2_c_address').val('Withdrawn');
                 }
                 break;
            case 'administrative_area_level_2':
                 $('#Listings2_c_county__c').val(addrComp.long_name);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Listings2_c_county__c').val('Withdrawn');
                 }
                 break;
            case 'country':
                 $('#Listings2_c_country__c').val(addrComp.long_name);
                 break;
            case 'postal_code':
                 $('#Listings2_c_postalcode').val(addrComp.long_name);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Listings2_c_postalcode').val('Withdrawn');
                 }
                 break;
            case 'administrative_area_level_1':
                 $('#Listings2_c_state__c').val(addrComp.long_name);
                 break;
            case 'locality':
                 $('#Listings2_c_city__c').val(addrComp.long_name);
                 if($('#location-withdrawn-checkbox').is(':checked')){
                     $('#Listings2_c_city__c').val('Withdrawn');
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

Yii::app()->clientScript->registerScript('setFieldsReadOnly', "
  document.getElementById('Listings2_c_city__c').readOnly = true;
  document.getElementById('Listings2_c_state__c').readOnly = true;
  document.getElementById('Listings2_c_county__c').readOnly = true;
  document.getElementById('Listings2_c_postalcode').readOnly = true;
  document.getElementById('Listings2_c_country__c').readOnly = true;
", CClientScript::POS_END);

Yii::app()->clientScript->registerScript('placeMap', "
  var map = document.getElementById('map');
  $('.formSection:nth-child(9)').append($('#map'));
  $('#Listings2_c_address_field').append($('#location-withdrawn_field'));
", CClientScript::POS_END);


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

<div id="map"></div>
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
