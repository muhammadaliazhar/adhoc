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

/**
 * @package application.components.sortableWidget
 */
class BuyerPrefsWidget extends SortableWidget {

     

    /**
     * @var CActiveRecord $model
     */
    public $model;

    public $criteriaIds; 

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{titleBarButtons}{closeButton}{minimizeButton}</div>{widgetContents}';

    public $viewFile = '_buyerPrefsWidget';

    protected $containerClass = 'sortable-widget-container x2-layout-island';

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array (
                'model' => new Contacts('search'),
            );
        }
        return $this->_viewFileParams;
    }
 
    private static $_JSONPropertiesStructure;
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Buyer Match Preference',
                    'hidden' => false,
                    'containerNumber' => 2,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function renderTitleBarButtons () {
    /*echo '<div class="x2-button-group">';
        echo "<a class='x2-button rel-title-bar-button' href='#' onclick='toggleInquiriesEmailForm(); return false;' id='inquiries-email-button'>"
                . X2Html::fa ('fa-envelope', array()) . "</a>";
        echo "<a class='x2-button icon rel-title-bar-button' href='#' id='inquiries-export-button'>"
                . X2Html::fa ('fa-cloud-download', array ()) . "</a>";
        echo '</div>';*/
    }

    public function getRelatedCriteria () {
        $relatedRecordIds = array();
        $firstRel = Relationships::model()->findAllByAttributes(array(
            "firstType" => get_class($this->model),
            "firstId" => Yii::app()->controller->model->id,
            "secondType" => "Privatedealsipos"
        ));

        foreach($firstRel as $rel){
            $relatedRecordIds[] = $rel->secondId;
        }

        $secRel = Relationships::model()->findAllByAttributes(array(
            "secondType" => get_class($this->model),
            "secondId" => Yii::app()->controller->model->id,
            "firstType" => "Privatedealsipos"
        ));


        foreach($secRel as $rel){
            $relatedRecordIds[] = $rel->firstId;
        }

 
        $criteria = new CDbCriteria;
        $idText = "";
        foreach($relatedRecordIds as $id){
            if(empty($idText)){
                $idText = $idText . $id;
            }else{
                $idText = $idText . ", " . $id;
            }
        }
        if(!empty($idText))
            $criteria->addCondition('(id in (' . $idText . '))');
        else
            $criteria->addCondition('FALSE');

        return $criteria;
    }

    public function renderWidgetLabel () {
        $label = $this->getWidgetLabel ();
        echo "<div class='widget-title'>Buyer Match Preference</div>";
    }

    public function regesterJS(){

        $query = "SELECT * FROM x2_buyer_prefs WHERE buyerId = " .  Yii::app()->controller->model->id . "";
        $command = Yii::app()->db->createCommand($query);
        $bPref = $command->queryAll();
        //if we have buyers prefs set save as js values if not set myself
        if(isset($bPref[0])){
            $down = json_encode($bPref[0]['down_payment']);
            $price = json_encode($bPref[0]['price']);
            $minSell = json_encode($bPref[0]['minimum_seller_discretionary_earnings']);
            $geo = $bPref[0]['geography']; 
            $industry = $bPref[0]['industry_subcartegory'];
            $country = $bPref[0]['country'];
        }else{
            $down = 0;
            $price = 0;
            $minSell = 0;
            $geo = "[]";
            $industry = "[]";
            $country = "United States";

        }
        $states = array(
        "all" => "all",
        "Alabama"   =>  "Alabama",
        "Alaska"    =>  "Alaska",
        "Arizona"   =>  "Arizona",
        "Arkansas"  =>  "Arkansas",
        "California"    =>  "California",
        "Colorado"  =>  "Colorado",
        "Connecticut"   =>  "Connecticut",
        "Delaware"  =>  "Delaware",
        "District Of Columbia"  =>  "District Of Columbia",
        "Florida"   =>  "Florida",
        "Georgia"   =>  "Georgia",
        "Hawaii"    =>  "Hawaii",
        "Idaho" =>  "Idaho",
        "Illinois"  =>  "Illinois",
        "Indiana"   =>  "Indiana",
        "Iowa"  =>  "Iowa",
        "Kansas"    =>  "Kansas",
        "Kentucky"  =>  "Kentucky",
        "Louisiana" =>  "Louisiana",
        "Maine" =>  "Maine",
        "Maryland"  =>  "Maryland",
        "Massachusetts" =>  "Massachusetts",
        "Michigan"  =>  "Michigan",
        "Minnesota" =>  "Minnesota",
        "Mississippi"   =>  "Mississippi",
        "Missouri"  =>  "Missouri",
        "Montana"   =>  "Montana",
        "Nebraska"  =>  "Nebraska",
        "Nevada"    =>  "Nevada",
        "New Hampshire" =>  "New Hampshire",
        "New Jersey"    =>  "New Jersey",
        "New Mexico"    =>  "New Mexico",
        "New York"  =>  "New York",
        "North Carolina"    =>  "North Carolina",
        "North Dakota"  =>  "North Dakota",
        "Ohio"  =>  "Ohio",
        "Oklahoma"  =>  "Oklahoma",
        "Oregon"    =>  "Oregon",
        "Pennsylvania"  =>  "Pennsylvania",
        "Rhode Island"  =>  "Rhode Island",
        "South Carolina"    =>  "South Carolina",
        "South Dakota"  =>  "South Dakota",
        "Tennessee" =>  "Tennessee",
        "Texas" =>  "Texas",
        "Utah"  =>  "Utah",
        "Vermont"   =>  "Vermont",
        "Virginia"  =>  "Virginia",
        "Washington"    =>  "Washington",
        "West Virginia" =>  "West Virginia",
        "Wisconsin" =>  "Wisconsin",
        "Wyoming"   =>  "Wyoming"    
        );
        $stateOps = "";
        $counties = array();
        $countries = array(
            "Canada"=>"Canada",
            "United States"=>"United States",
            "Both" => "Both",
            );
        $provinces = array(
             "all" => "all",
            "Alberta"=>"Alberta",
            "British Columbia"=>"British Columbia",
            "Manitoba"=>"Manitoba",
            "New Brunswick"=>"New Brunswick",
            "Newfoundland and Labrador"=>"Newfoundland and Labrador",
            "Nova Scotia"=>"Nova Scotia",
            "Ontario"=>"Ontario",
            "Prince Edward Island"=>"Prince Edward Island",
            "Quebec"=>"Quebec",
            "Saskatchewan"=>"Saskatchewan",
        );
        $provOps = "";
        foreach($states as $key => $value){
            //for the cointes drop down
            $query = "select name from x2_counties where c_state__c LIKE '" . $value  . "%'";
            $command = Yii::app()->db->createCommand($query);
            $countie = $command->queryAll();
            foreach($countie as $co){
                $counties[$value][] = $co['name'] . " County" ;
            }

            $stateOps .= "<option value='" . $value . "'>" . $value . "</option>";
        }

        foreach($provinces as $prov){
             $provOps .= "<option value='" . $prov . "'>" . $prov . "</option>";

        }

        //make the dropdown for the country select
        $countriesOps = ""; 
        foreach($countries as $key => $value){
            //if country is the same preslect the option
            if($country == $value) $countriesOps .= "<option value='" . $value . "' selected>" . $value . "</option>";
            else $countriesOps .= "<option value='" . $value . "'>" . $value . "</option>";
        }


        $jsVars = "var down = " . $down . "; " ;
        $jsVars .= "var price = " . $price . "; " ;
        $jsVars .= "var minSell = " . $minSell . "; " ;
        $jsVars .= "var geo = " . $geo . "; " ;
        
        //check to see if all array if so make object to make my life easy
        if($industry == '["all"]') $industry = '{"all":[\'all\']}';
        $jsVars .= "var industry = " . $industry . "; " ;
        $query = 'select options from x2_dropdowns where id = 1016'; 
        $command = Yii::app()->db->createCommand($query);
        $result = $command->queryAll();
        $cats = json_decode($result[0]['options'],true);
        $htmlOptions = "<option value='all'>all</option>";
        foreach($cats as $key => $value){

            $htmlOptions .= "<option value='" . $value . "'>" . $value . "</option>";
        }
        $jsVars .= "var insOptions = " . json_encode($htmlOptions) . "; " ; 
        $jsVars .= "var stateOps = " . json_encode($stateOps) . "; " ;
        $jsVars .= "var stateCountOps = " . json_encode($counties) . "; " ;
        $jsVars .= "var countriesOps = " . json_encode($countriesOps) . "; " ;
        $jsVars .= "var provOps = " . json_encode($provOps) . "; " ;
        Yii::app()->clientScript->registerScript('popupGridView',
        " " . $jsVars . "
            var i = 0;
            var indsNames = [];
            var geoCount = 0;
            var geoNames = [];
 
            
            $('#edit_buyer_prefs').click(function(evt) {
                openBuyerEdit();
                return false;
            });
            
            function getCountyOps(state){
                if(!(state in stateCountOps))return '';
                var coutArry = stateCountOps[state];
                var coutOps = \"<option  value=''></options>\";
                if(state == 'all')return coutOps;
                $.each(coutArry, function(q){
                    coutOps = coutOps + \"<option  value='\"+coutArry[q]+\"'>\"+coutArry[q]+\"</options>\";
                        
                });       
                return coutOps;

            };


            function setUpSubCall(id,subs){
               if (subs === undefined) {
                    subs= [];
                }

                $(id).change(function() {
                        $.ajax({
                        type:  'GET',
                        url: '" . Yii::app()->getBaseUrl() . '/index.php/contacts/getItemsSubCategories' . "',
                        data: {
                            categoryTerm: JSON.stringify([$(id).val()])
                        },
                        success: function (data) {
                            var dropdownDatum = JSON.parse(data)[0][0];
                            //idea for making drop down from here https://forum.jquery.com/topic/create-dropdown-list-in-jquery
                            var opts = '';
                             $.each(dropdownDatum, function(i){
                                var selctor = '';
                                if(subs.includes(dropdownDatum[i]['name'])) selctor = 'selected'; 
                                opts = opts + \"<option \" + selctor + \" value='\"+dropdownDatum[i]['name']+\"'>\"+dropdownDatum[i]['name']+\"</options>\";   
                            });
                            var select = \"<label>SubCategories: </label><select id='catSel' >\"+opts+\"</select>\";
                            //remove any pre drop down
                            $(id + 'Sub label').remove();
                            $(id + 'Sub select').remove();
                            $(id + 'Sub').append(select);
                            return false;
                        },
                        error: function (error) {
                        }
                     })
                    return false;
                });

            return false;
            };
            function addIndusDiv(){
                     var nameI = \"cat\" + i;
                     indsNames.push(nameI);
                     var indusBox = $('<div id=\"' + nameI + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>Industry: </label><select name=\"' + nameI  + '\" id=\"' + nameI + '\">' + insOptions + '</select><div id=\"' + nameI + 'Sub\" ></div><br></div>');
                     $( indusBox ).insertBefore( '#indusButCon' );
                     //get the subcats
                     setUpSubCall(\"#\" + nameI);
                     i = i + 1;
                     return false;
            };
            
            function openBuyerEdit(){
                    // Container view for whole dialog
                var buyPrefBox = $('<div />');
                buyPrefBox.prop('id', 'docusign');
                buyPrefBox.prop('title', 'Edit Buyer Preference');
                var downPayBox = $('<label style=\"font-weight: bold;\">Max Down Payment: </label><input class=\"currency-field\"   type=\"text\" id=\"downPay\" name=\"downPay\" value=' + down + '><br>');
                var priceBox = $('<label style=\"font-weight: bold;\">Max Price: </label><input class=\"currency-field\" type=\"text\" id=\"price\" name=\"price\" value=' + price + '><br>');
                var minSellBox = $('<label style=\"font-weight: bold;\">Minimum Seller Discretionary Earnings: </label><input class=\"currency-field\"  type=\"text\" id=\"minSell\" name=\"minSell\" value=' + minSell + '><br>');          
                buyPrefBox.append(priceBox, downPayBox, minSellBox);
                buyPrefBox.css('height', 'auto');
                buyPrefBox.css('width', '700px');
                buyPrefBox.css('padding', 20);
                buyPrefBox.css('overflow-y', 'auto');
                buyPrefBox.css('overflow-x', 'auto');
                buyPrefBox.dialog({ width: 450, height:'auto',autoResize:true, draggable: false});
                console.log(industry);
                Object.keys(industry).forEach(function(k){
                    $.each(industry[k], function(q){
                         var nameI = \"cat\" + i;
                         indsNames.push(nameI);
                         var indusBox = $('<div id=\"' + nameI + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>Industry: </label><select name=\"' + nameI  + '\" id=\"' + nameI + '\">' + insOptions + '</select><div id=\"' + nameI + 'Sub\" ></div><br></div>');
                         buyPrefBox.append(indusBox);
                         //get the subcats
                         console.log(k);
                         //select the subcatagories
                         var newNames = []; 
                        newNames.push(industry[k][q].replace(/_.*/, ''));
                        console.log(newNames);
                         setUpSubCall(\"#\" + nameI, newNames);
                         //set the industry then trigger sub cat call
                         buyPrefBox.children(\"#\" + nameI + \"Div\").children(\"#\" + nameI).val(k).change();
                         i = i + 1;
                    });
                }); 
                var indusBut = $('<div id=\"indusButCon\" href=\"#\"><a class=\"x2-button \" id=\"AddIndus\" href=\"#\"><i href=\"#\" class=\" fa fa-share fa-lg\"> </i>Add Industry</a><\div>'); 
                buyPrefBox.append(indusBut);
                $('#AddIndus').click(function(evt) {
                    addIndusDiv();
                    return false;
                });
                var countryBox = $('<div id=\"countryBuyerDiv\"><label style=\"font-weight: bold;\">Country: </label><select name=\"countryBuyer\" id=\"countryBuyer\">' + countriesOps + '</select></div>'); 
                buyPrefBox.append(countryBox);
                //now I set up the location 
                $.each(geo, function(q){
                    var nameG = \"geo\" + geoCount;
                    geoNames.push(nameG);
                    var parts = geo[q].split(':');
                    //difrent ver if we are set to Canada
                    if($('#countryBuyer').val() == 'Canada'){
                        var geoBox = $('<div id=\"' + nameG + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>State: </label><select name=\"' + nameG  + '\" id=\"' + nameG + '\">' + provOps + '</select><br><label>County: </label> <select id=\"' + nameG + 'Count\"></select><br></div>');
                    }
                    if($('#countryBuyer').val() == 'United States'){
                         var stateCountOps = getCountyOps(parts[0]);
                        var geoBox = $('<div id=\"' + nameG + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>State: </label><select name=\"' + nameG  + '\" id=\"' + nameG + '\">' + stateOps + '</select><br><label>County: </label> <select  id=\"' + nameG + 'Count\">' + stateCountOps  + '</select><br></div>');
                    }
                    if($('#countryBuyer').val() == 'Both'){
                         var stateCountOps = getCountyOps(parts[0]);
                        //if we got not states county options we know this is a providnce
                        if(stateCountOps){
                            var geoBox = $('<div id=\"' + nameG + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>State: </label><select name=\"' + nameG  + '\" id=\"' + nameG + '\">'  + provOps + stateOps + '</select><br><label>County: </label> <select  id=\"' + nameG + 'Count\">' + stateCountOps  + '</select><br></div>');
                        }else{
                            var geoBox = $('<div id=\"' + nameG + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>State: </label><select name=\"' + nameG  + '\" id=\"' + nameG + '\">'  + provOps + stateOps + '</select><br><label>County: </label> <select  id=\"' + nameG + 'Count\">' + '</select><br></div>');

                        }

                    }

                     buyPrefBox.append(geoBox);
                     $(\"#\" + nameG).val(parts[0]);
                     $(\"#\" + nameG + \"Count\").val(parts[1]);
                     geoCount = geoCount + 1;
                     //if state changes 
                     $(\"#\" + nameG).change(function() {                     
                        var stateCountOps = getCountyOps($(\"#\" + nameG).val());
                        $(\"#\" + nameG + \"Count\").empty().append(stateCountOps);
                        return false;
                     });
                });
                var geoBut = $('<div id=\"geoButCon\" href=\"#\"><a class=\"x2-button \" id=\"AddGeo\" href=\"#\"><i href=\"#\" class=\" fa fa-share fa-lg\"> </i>Add Location</a><\div>'); 
                buyPrefBox.append(geoBut);
                $('#AddGeo').click(function(evt) {
                    
                    var nameG = \"geo\" + geoCount;
                    geoNames.push(nameG);
                    var stateCountOps = getCountyOps('Alabama');
                    //difrent ver if we are set to Canada
                    //changing this to have both states and providences if 'Both' is selected
                    if($('#countryBuyer').val() == 'Canada'){
                        var geoBox = $('<div id=\"' + nameG + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>State: </label><select name=\"' + nameG  + '\" id=\"' + nameG + '\">' + provOps + '</select><br><label>County: </label> <select id=\"' + nameG + 'Count\"></select><br></div>');
                    }
                    if($('#countryBuyer').val() == 'United States'){               
                        var geoBox = $('<div id=\"' + nameG + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>State: </label><select name=\"' + nameG  + '\" id=\"' + nameG + '\">' + stateOps + '</select><br><label>County: </label> <select id=\"' + nameG + 'Count\">' + stateCountOps  + '</select><br></div>');
                    }
                    if($('#countryBuyer').val() == 'Both'){               
                        var geoBox = $('<div id=\"' + nameG + 'Div\"><a class=\"delete\" onclick=\"$(this).parent().remove();\"><i class=\"far fa-times-circle\"></i></a><label>State: </label><select name=\"' + nameG  + '\" id=\"' + nameG + '\">' + provOps + stateOps + '</select><br><label>County: </label> <select id=\"' + nameG + 'Count\">' + stateCountOps  + '</select><br></div>');
                    }
                    $( geoBox).insertBefore( '#geoButCon' );
                    $(\"#\" + nameG).change(function() {        
                        var stateCountOps = getCountyOps($(\"#\" + nameG).val());
                        $(\"#\" + nameG + \"Count\").empty().append(stateCountOps);
                        return false;
                     });
                    geoCount = geoCount + 1;
                    return false;

                });
                //now add the save button 
                var saveBut = $('<button id=\"buyPrefSave\">Save</button>');
                buyPrefBox.append(saveBut);
                

                //here this code will remove location settings if the country is changed
                $('#countryBuyer').change(function() {
                        $.each(geoNames, function(spot){
                            if($('#' + geoNames[spot]).length != 0){
                                $('#' + geoNames[spot]).parent().remove();
                            }

                        });
                    geoNames = [];
                });


                $('#buyPrefSave').click(function(evt) {
                        var cats = {};
                        var geos = [];
                        //go and get all the val
                        $.each(indsNames, function(spot){
                            if($('#' + indsNames[spot]).length != 0){
                                var mainCat = $('#' + indsNames[spot]).val();
                                if (typeof cats[mainCat] == 'undefined') {
                                    cats[mainCat] = [];
                                }
                                cats[mainCat].push($('#' + indsNames[spot] + 'Sub > #catSel').val());
                            }

                        });
                        //go get the geo locations
                        $.each(geoNames, function(spot){
                            if($('#' + geoNames[spot]).length != 0){
                                if($('#' + geoNames[spot] + 'Count').val())
                                    var geo = $('#' + geoNames[spot]).val() + ':' +  $('#' + geoNames[spot] + 'Count').val();
                                else
                                    var geo = $('#' + geoNames[spot]).val();
                                geos.push(geo);
                            }

                        });

                        

                        $.ajax({
                        type:  'POST',
                        url: '" . Yii::app()->getBaseUrl() . '/index.php/contacts/UpdateBuyPrefs' . "',
                        data: {
                            buyerId: " . Yii::app()->controller->model->id . ",
                            down: $('#downPay').val(),
                            price: $('#price').val(),
                            minSell: $('#minSell').val(),
                            cats: JSON.stringify(cats),
                            geos: JSON.stringify(geos),
                            country: $('#countryBuyer').val(),
                            //categoryTerm: JSON.stringify([$(id).val()])
                        },
                        success: function (data) {
                            location.reload();
                        },
                        error: function (error) {
                            alert(error);
                        }
                     })
                });


            $(\"#price\").maskMoney();
            $(\"#downPay\").maskMoney();
            $(\"#minSell\").maskMoney();

            return false;
            };

            ", CClientScript::POS_END);

        $elementIds = array("#price","#downPay","#minSell");
        foreach($elementIds as $elementId){
                    Yii::app()->controller->widget('application.extensions.moneymask.MMask', array(
                        'element' => $elementId,
                        'currency' => Yii::app()->params['currency'],
                        'config' => array(
                            //'showSymbol' => true,
                            'affixStay' => true,
                            'decimal' => Yii::app()->locale->getNumberSymbol('decimal'),
                            'thousands' => Yii::app()->locale->getNumberSymbol('group'),
                        )
                    ));

            
        }


    }


    public function run () {
        if ($this->widgetManager->layoutManager->staticLayout) return;
        if(Yii::app()->controller->module->getName() != "contacts") return;
        //$criteriaIds = $this->getRelatedCriteria ();
        //$this->criteriaIds = $criteriaIds;
        $this->regesterJS();
        $query = "SELECT * FROM x2_buyer_prefs WHERE buyerId = " .  Yii::app()->controller->model->id . ""; 
        $command = Yii::app()->db->createCommand($query);
        $bPref = $command->queryAll();



        // hide widget if journal view is disabled
        if (!Yii::app()->params->profile->miscLayoutSettings['enableJournalView']) {
            $this->registerSharedCss ();
            $this->render ('application.components.sortableWidget.views.'.$this->sharedViewFile,
                array (
                    'widgetClass' => get_called_class (),
                    'profile' => $this->profile,
                    'hidden' => true,
                    'widgetUID' => $this->widgetUID,
                ));
            return;
        }

        parent::run ();
    }

}

?>


