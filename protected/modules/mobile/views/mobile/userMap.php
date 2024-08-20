<?php
/* * *********************************************************************************
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
 * ******************************************************************************** */

/* @edition:ent */
?>

<div class='detail-view'>
    <?php
// Gets google api key
    $key = '';
    $settings = Yii::app()->settings;
    $creds = Credentials::model()->findByPk($settings->googleCredentialsId);
    if ($creds && $creds->auth) {
        $key = $creds->auth->apiKey;
    }

// Sets map using key
    $assetUrl = 'https://maps.googleapis.com/maps/api/js?libraries=visualization&callback=initializeMap';
    if (!empty($key)) {
        $assetUrl .= '&key=' . $key;
    }
    $contactLinks = array();
    foreach ($locations as $location) {
        $contact = Contacts::model()->findByPk($location['recordId']);
        if ($contact) {
            $contactLinks[$location['recordId']] = $contact->getLink(array('style' => 'text-decoration:none;'));
        }
    }

    Yii::app()->clientScript->registerScript('maps-initialize', "
markers = [];
defaultMiles = 25;
defaultTime = 24;
user = {'lat':" . $model->lat . ", 'lng':" . $model->lon . "}
locations = " . json_encode($locations) . ";
contactLinks = " . json_encode($contactLinks) . ";

function initializeMap() {
    var center = $center;
    var zoom = " . (isset($zoom) ? $zoom : "14") . ";
    var latLng = new google.maps.LatLng(center['lat'],center['lng']);

    // Create map
    map = new google.maps.Map(document.getElementById('map_canvas'), {zoom: 0, 
        mapTypeId: google.maps.MapTypeId.roadmap,
        gestureHandling: 'cooperative',center: latLng});
    
    // Set zoom in miles
    var z = getZoomFromMiles(defaultMiles);
    var mapOptions = {zoom: z};
    map.setOptions(mapOptions);
    
    $('#distance').val(defaultMiles);
    $('#time').val(defaultTime);
        
    // Add markers
    addMarkers();
};

function addMarkers() {
    clearMarkers();
    
    $.each(locations, function(i, loc) {
        var distance = calculateDistance(user.lat, user.lng, loc.lat, loc.lon);
        
        var distanceVal = $('#distance').val();
        var timeVal = $('#time').val();
        if(isNaN(distanceVal) || isNaN(timeVal)) {
            return;
        }
        
        var d = new Date();
        var diffsec = ((d.getTime() / 1000) - loc.createDate);

        console.log(diffsec / 3600);

        var time = parseInt(timeVal) * 3600;
        if (diffsec > time) return;

        var selection = getZoomFromMiles(parseFloat(distanceVal));
        if (distance > distanceVal) return;

        var details = contactLinks[loc['recordId']];
        if(loc.type){
            details += '<br>'+loc.type;
        }
        if(loc.info){
            details += '<br>'+loc.info;
        }
        if(loc.time){
            details += '<br>'+loc.time;
        }
        
        var marker = addLargeMapMarker(loc, details);
        markers.push(marker);
    });
};
    
function addLargeMapMarker(pos, contents, open = true) {
    var latLng = new google.maps.LatLng(pos.lat, pos.lon);
    var marker = new google.maps.Marker({
        position: latLng,
        map: map
    });
    
    if(typeof infowindow === 'undefined'){
        var infowindow = new google.maps.InfoWindow({
            content: contents
        });
        infowindow.open(map, marker);
    }
    
    google.maps.event.addListener(infowindow,'domready',function(){
        $('#corporate-directions').click(function(e){
            e.preventDefault();
            getDirections('corporate');
        });
        $('#personal-directions').click(function(e){
            e.preventDefault();
            getDirections('personal');
        });
    });

    google.maps.event.addListener(marker,'click',function(){
        infowindow.open(map,marker);
    });

    return marker;
};

function degtorad(degrees) {
    return degrees * Math.PI / 180;
};

function calculateDistance(flat, flon, tlat, tlon) {
    var radius = 3961;

    var latFrom = degtorad(flat);
    var lonFrom = degtorad(flon);
    var latTo = degtorad(tlat);
    var lonTo = degtorad(tlon);
    var latDelta = latTo - latFrom;
    var lonDelta = lonTo - lonFrom;
    var angle = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin(latDelta / 2), 2) +
                Math.cos(latFrom) * Math.cos(latTo) * Math.pow(Math.sin(lonDelta / 2), 2)));
    var distance = Math.round(angle * radius, 2);
    return distance;
};
    
function setMapOnAll(set) {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(set);
    }
};

function clearMarkers() {
    setMapOnAll(null);
    markers = [];
};
    
function getZoomFromMiles(miles) {
    var meters = miles * 1609.34;
    var mapConv = meters / 6;

    var w = screen.width;
    var d = mapConv * 4;
    var zooms = [,21282,16355,10064,5540,2909,1485,752,378,190,95,48,24,12,6,3,1.48,0.74,0.37,0.19];
    var z = 20, m;
    while( zooms[--z] ){
        m = zooms[z] * w;
        if( d < m ){
            break;
        }
    }
    return z;
};
  
function setNewView() {
    var distanceVal = $('#distance').val();
    if(isNaN(distanceVal)) {
        return;
    }
    var distance = getZoomFromMiles(parseFloat(distanceVal));
    var mapOptions = {zoom: distance};
    map.setOptions(mapOptions);
    
    addMarkers();
};

", CClientScript::POS_HEAD);

    Yii::app()->clientScript->registerScript('change', "
    $('#go').on('click', function() {
        setNewView();
    });
    
    $('#distance').on('keydown', function(e) {
        if(e.keyCode === 13){
            setNewView();
        }
    });
    
    $('#time').on('keydown', function(e) {
        if(e.keyCode === 13){
            setNewView();
        }
    });
    ", CClientScript::POS_READY);
    Yii::app()->clientScript->registerScriptFile($assetUrl, CClientScript::POS_END);
    ?>
    <div id="map_canvas" style="height: 400px; width:100%;float:right;"></div>

    <div id="controls" class="form">
        <div class="row">
            <h2 style='margin: 5px'><?php echo Yii::t('contacts', 'Filters'); ?></h2>
            <div class="cell" style='margin: 5px'>
                <label><?php echo Yii::t('users', 'Distance in Miles'); ?></label>
                <input id="distance"></input>
                <label><?php echo Yii::t('users', 'Time in hours'); ?></label>
                <input id="time"></input>
            </div>

            <div id="go" class="x2-button" style="cursor: pointer;width:100px;text-align: center;margin: 5px;background:#eee;border: 3px solid #ccc;">Go</div>
        </div>
    </div>

</div>
