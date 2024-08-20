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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_template".
 * @package application.modules.template.models
 */
class Royaltyreport extends X2Model {

    public $acroIndustryMap = array(
        "Accounting"=>"0","Advertising & Media"=>"1","Advertising"=>"2","Agriculture"=>"3","Animals/Pets"=>"4","Antiques"=>"5","Appliances"=>"6","Art"=>"7","Automotive"=>"8","Aviation"=>"9","Awards/Prizes"=>"10","Beauty/Personal Care"=>"11","Beauty Salon & Supplies"=>"12","Books, Toys & Gifts"=>"13","Building Materials"=>"14","Business Services"=>"15","Cards/Gifts/Books"=>"16","Care"=>"17","Catering"=>"18","Child Care"=>"19","Cleaning"=>"20","Cleaning/Clothing"=>"21","Clothing"=>"22","Coaching & Tutoring"=>"23","Commercial Property"=>"24","Communications"=>"25","Construction"=>"26","Consultancy"=>"27","Consulting Business"=>"28","Consumer Durables & IT"=>"29","Convenience Stores"=>"30","Crafts/Hobbies"=>"31","Dealers & Distributors"=>"32","Delivery"=>"33","Dental-Related"=>"34","Distribution"=>"35","Early Education"=>"36","Educational/School"=>"37","Education Consultants"=>"38","Electronics/Computer"=>"39","Energy and Power"=>"40","Engineering"=>"41","Environmental Related"=>"42","Equipment S&S"=>"43","Financial"=>"44","Financial-Related"=>"45","Firearms"=>"46","Fitness"=>"47","Flooring"=>"48","Flower-Related"=>"49","Food Business Retail"=>"50","4 Wheelers & 2 Wheelers"=>"51","Furniture Retail"=>"52","Gas Station"=>"53","Glass"=>"54","Hardware Related"=>"55","Health Care & Fitness"=>"56","Higher Education"=>"57","Hobby-Related"=>"58","Home & Office"=>"59","Home-Based Business"=>"60","Hotels and Resorts"=>"61","Ice Cream/Yogurt"=>"62","Import/Export"=>"63","Information Technology"=>"64","Insurance"=>"65","Interior Design"=>"66","Internet-Related"=>"67","IT Services"=>"68","Jewelry"=>"69","K-12 Education"=>"70","Lawn/Landscaping"=>"71","Leisure Pubs & Hotels"=>"72","Liquor-Related"=>"73","Locksmith"=>"74","Logistics"=>"75","Machine Shop"=>"76","Mail Order"=>"77","Manufacturing"=>"78","Marine-Related"=>"79","Medical-Related"=>"80","Metal-Related"=>"81","Mobile Homes"=>"82","Motor & Transport"=>"83","Motorcycle"=>"84","Moving"=>"85","Music"=>"86","Newspaper/Magazines"=>"87","Office Supplies"=>"88","Online Education"=>"89","Optical-Related"=>"90","Pack/Ship/Postal"=>"91","Personal Services"=>"92","Professional Services"=>"93","Pest Control"=>"94","Photography"=>"95","Pool & Spa Business"=>"96","Printing/Typesetting"=>"97","Professional Practices"=>"98","Publishing"=>"99","Real Estate"=>"100","Real Estate-Related"=>"101","Real Property-Related"=>"102","Recreation"=>"103","Rental Business"=>"104","Repair"=>"105","Restaurants"=>"106","Retail"=>"107","Routes"=>"108","Sales & Marketing"=>"109","Security-Related"=>"110","Services"=>"111","Shoes/Footwear"=>"112","Signs"=>"113","Sport Goods & Fitness"=>"114","Sports-Related"=>"115","Start-Up Businesses"=>"116","Supermarkets & Marts"=>"117","Tailoring"=>"118","Telephone & Related"=>"119","Toys"=>"120","Transportation"=>"121","Travel"=>"122","Upholstery/Fabrics"=>"123","Vending-Related"=>"124","Video-Related"=>"125","Vocational Training"=>"126","Water-Related"=>"127","Wholesale"=>"128"
    ,"Wholesale & Distribution"=>"129"
    );


    public $acroRoyalMap = array(
    "FranName" => "form1[0].#subform[0].TextField2[0]", 
    "Territory" => "form1[0].#subform[0].TextField2[1]", 
    "ReportMonth" => "form1[0].#subform[0].DropDownList4[0]", 
    "activeListings" => "form1[0].#subform[0].NumericField5[0]", 
    "pendingDeals" => "form1[0].#subform[0].NumericField5[2]", 
    "closedDeals" => "form1[0].#subform[0].NumericField5[1]", 
    "activeListings2" => "form1[0].#subform[1].NumericField5[3]", 
    "closedDeals2" => "form1[0].#subform[1].NumericField5[5]", 
    "pendingDeals2" => "form1[0].#subform[1].NumericField5[4]", 
    "row1" => array("bisName"=>"form1[0].#subform[0].TextField7[0]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[0]", "listPrice"=>"form1[0].#subform[0].NumericField2[1]",  "soldPrice"=>"form1[0].#subform[0].SoldAmt1[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[0]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[0]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[1]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[2]", "industry"=>"form1[0].#subform[0].DropDownList3[0]",  "commAmmount"=>"form1[0].#subform[0].CommAmt1[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[1]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[0]"), 
    "row2" => array("bisName"=>"form1[0].#subform[0].TextField7[1]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[1]", "listPrice"=>"form1[0].#subform[0].NumericField2[5]", "soldPrice"=>"form1[0].#subform[0].SoldAmt2[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[3]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[4]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[2]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[3]", "industry"=>"form1[0].#subform[0].DropDownList3[1]",  "commAmmount"=>"form1[0].#subform[0].CommAmt2[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[3]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[2]"), 
    "row3" => array("bisName"=>"form1[0].#subform[0].TextField7[2]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[2]", "listPrice"=>"form1[0].#subform[0].NumericField2[8]", "soldPrice"=>"form1[0].#subform[0].SoldAmt3[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[5]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[7]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[4]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[6]", "industry"=>"form1[0].#subform[0].DropDownList3[2]",  "commAmmount"=>"form1[0].#subform[0].CommAmt3[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[5]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[4]"), 
    "row4" => array("bisName"=>"form1[0].#subform[0].TextField7[3]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[3]", "listPrice"=>"form1[0].#subform[0].NumericField2[11]", "soldPrice"=>"form1[0].#subform[0].SoldAmt4[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[7]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[10]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[6]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[9]", "industry"=>"form1[0].#subform[0].DropDownList3[3]",  "commAmmount"=>"form1[0].#subform[0].CommAmt4[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[7]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[6]"), 
    "row5" => array("bisName"=>"form1[0].#subform[0].TextField7[4]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[4]", "listPrice"=>"form1[0].#subform[0].NumericField2[14]", "soldPrice"=>"form1[0].#subform[0].SoldAmt5[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[9]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[13]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[8]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[12]", "industry"=>"form1[0].#subform[0].DropDownList3[4]",  "commAmmount"=>"form1[0].#subform[0].CommAmt5[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[9]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[8]"), 
    "row6" => array("bisName"=>"form1[0].#subform[0].TextField7[5]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[5]", "listPrice"=>"form1[0].#subform[0].NumericField2[17]", "soldPrice"=>"form1[0].#subform[0].SoldAmt6[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[11]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[16]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[10]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[15]", "industry"=>"form1[0].#subform[0].DropDownList3[5]",  "commAmmount"=>"form1[0].#subform[0].CommAmt6[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[11]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[10]"), 
    "row7" => array("bisName"=>"form1[0].#subform[0].TextField7[6]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[6]", "listPrice"=>"form1[0].#subform[0].NumericField2[20]", "soldPrice"=>"form1[0].#subform[0].SoldAmt7[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[13]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[19]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[12]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[18]", "industry"=>"form1[0].#subform[0].DropDownList3[6]",  "commAmmount"=>"form1[0].#subform[0].CommAmt7[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[13]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[12]"), 
    "row8" => array("bisName"=>"form1[0].#subform[0].TextField7[7]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[7]", "listPrice"=>"form1[0].#subform[0].NumericField2[23]", "soldPrice"=>"form1[0].#subform[0].SoldAmt8[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[15]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[22]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[14]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[21]", "industry"=>"form1[0].#subform[0].DropDownList3[7]",  "commAmmount"=>"form1[0].#subform[0].CommAmt8[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[15]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[14]"), 
    "row9" => array("bisName"=>"form1[0].#subform[0].TextField7[8]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[8]", "listPrice"=>"form1[0].#subform[0].NumericField2[26]", "soldPrice"=>"form1[0].#subform[0].SoldAmt9[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[17]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[25]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[16]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[24]", "industry"=>"form1[0].#subform[0].DropDownList3[8]",  "commAmmount"=>"form1[0].#subform[0].CommAmt9[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[17]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[16]"), 
    "row10" => array("bisName"=>"form1[0].#subform[0].TextField7[9]", "soldDate"=>"form1[0].#subform[0].DateTimeField4[9]", "listPrice"=>"form1[0].#subform[0].NumericField2[29]", "soldPrice"=>"form1[0].#subform[0].SoldAmt10[0]", "buySideAgent"=>"form1[0].#subform[0].TextField8[19]", "buySideCommish"=>"form1[0].#subform[0].NumericField2[28]", "sellSideAgent"=>"form1[0].#subform[0].TextField8[18]", "sellSideCommish"=>"form1[0].#subform[0].NumericField2[27]", "industry"=>"form1[0].#subform[0].DropDownList3[9]",  "commAmmount"=>"form1[0].#subform[0].CommAmt10[0]", "closedSydney"=>"form1[0].#subform[0].CheckBox1[19]", "closingSTMT"=>"form1[0].#subform[0].CheckBox1[18]"), 
                "row11" => array("bisName"=>"form1[0].#subform[1].TextField7[10]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[10]", "listPrice"=>"form1[0].#subform[1].NumericField2[31]", "soldPrice"=>"form1[0].#subform[1].SoldAmt11[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[20]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[30]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[21]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[32]", "industry"=>"form1[0].#subform[1].DropDownList3[10]",  "commAmmount"=>"form1[0].#subform[1].CommAmt11[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[21]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[20]"), 
                "row12" => array("bisName"=>"form1[0].#subform[1].TextField7[11]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[11]", "listPrice"=>"form1[0].#subform[1].NumericField2[35]", "soldPrice"=>"form1[0].#subform[1].SoldAmt12[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[23]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[34]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[22]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[33]", "industry"=>"form1[0].#subform[1].DropDownList3[11]",  "commAmmount"=>"form1[0].#subform[1].CommAmt12[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[23]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[22]"),
                "row13" => array("bisName"=>"form1[0].#subform[1].TextField7[12]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[12]", "listPrice"=>"form1[0].#subform[1].NumericField2[38]", "soldPrice"=>"form1[0].#subform[1].SoldAmt13[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[25]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[37]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[24]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[36]", "industry"=>"form1[0].#subform[1].DropDownList3[12]",  "commAmmount"=>"form1[0].#subform[1].CommAmt13[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[25]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[24]"),
                "row14" => array("bisName"=>"form1[0].#subform[1].TextField7[13]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[13]", "listPrice"=>"form1[0].#subform[1].NumericField2[41]", "soldPrice"=>"form1[0].#subform[1].SoldAmt14[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[27]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[40]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[26]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[39]", "industry"=>"form1[0].#subform[1].DropDownList3[13]",  "commAmmount"=>"form1[0].#subform[1].CommAmt14[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[27]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[26]"),
                "row15" => array("bisName"=>"form1[0].#subform[1].TextField7[14]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[14]", "listPrice"=>"form1[0].#subform[1].NumericField2[44]", "soldPrice"=>"form1[0].#subform[1].SoldAmt15[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[29]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[43]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[28]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[42]", "industry"=>"form1[0].#subform[1].DropDownList3[14]",  "commAmmount"=>"form1[0].#subform[1].CommAmt15[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[29]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[28]"),
                "row16" => array("bisName"=>"form1[0].#subform[1].TextField7[15]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[15]", "listPrice"=>"form1[0].#subform[1].NumericField2[47]", "soldPrice"=>"form1[0].#subform[1].SoldAmt16[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[31]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[46]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[30]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[45]", "industry"=>"form1[0].#subform[1].DropDownList3[15]",  "commAmmount"=>"form1[0].#subform[1].CommAmt16[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[31]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[30]"),
                "row17" => array("bisName"=>"form1[0].#subform[1].TextField7[16]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[16]", "listPrice"=>"form1[0].#subform[1].NumericField2[50]", "soldPrice"=>"form1[0].#subform[1].SoldAmt17[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[33]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[49]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[32]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[48]", "industry"=>"form1[0].#subform[1].DropDownList3[16]",  "commAmmount"=>"form1[0].#subform[1].CommAmt17[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[33]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[32]"),
                "row18" => array("bisName"=>"form1[0].#subform[1].TextField7[17]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[17]", "listPrice"=>"form1[0].#subform[1].NumericField2[53]", "soldPrice"=>"form1[0].#subform[1].SoldAmt18[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[35]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[52]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[34]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[51]", "industry"=>"form1[0].#subform[1].DropDownList3[17]",  "commAmmount"=>"form1[0].#subform[1].CommAmt18[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[35]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[34]"),
                "row19" => array("bisName"=>"form1[0].#subform[1].TextField7[18]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[18]", "listPrice"=>"form1[0].#subform[1].NumericField2[56]", "soldPrice"=>"form1[0].#subform[1].SoldAmt19[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[37]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[55]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[36]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[54]", "industry"=>"form1[0].#subform[1].DropDownList3[18]",  "commAmmount"=>"form1[0].#subform[1].CommAmt19[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[37]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[36]"),
                "row20" => array("bisName"=>"form1[0].#subform[1].TextField7[19]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[19]", "listPrice"=>"form1[0].#subform[1].NumericField2[59]", "soldPrice"=>"form1[0].#subform[1].SoldAmt20[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[39]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[58]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[38]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[57]", "industry"=>"form1[0].#subform[1].DropDownList3[19]",  "commAmmount"=>"form1[0].#subform[1].CommAmt20[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[39]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[38]"),
                "row21" => array("bisName"=>"form1[0].#subform[1].TextField7[20]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[20]", "listPrice"=>"form1[0].#subform[1].NumericField2[61]", "soldPrice"=>"form1[0].#subform[1].SoldAmt21[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[40]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[60]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[41]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[62]", "industry"=>"form1[0].#subform[1].DropDownList3[20]",  "commAmmount"=>"form1[0].#subform[1].CommAmt21[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[41]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[40]"),
                "row22" => array("bisName"=>"form1[0].#subform[1].TextField7[21]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[21]", "listPrice"=>"form1[0].#subform[1].NumericField2[65]", "soldPrice"=>"form1[0].#subform[1].SoldAmt22[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[43]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[64]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[42]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[63]", "industry"=>"form1[0].#subform[1].DropDownList3[21]",  "commAmmount"=>"form1[0].#subform[1].CommAmt22[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[43]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[42]"),
                "row23" => array("bisName"=>"form1[0].#subform[1].TextField7[22]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[22]", "listPrice"=>"form1[0].#subform[1].NumericField2[68]", "soldPrice"=>"form1[0].#subform[1].SoldAmt23[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[45]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[67]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[44]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[66]", "industry"=>"form1[0].#subform[1].DropDownList3[22]",  "commAmmount"=>"form1[0].#subform[1].CommAmt23[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[45]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[44]"),
                "row24" => array("bisName"=>"form1[0].#subform[1].TextField7[23]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[23]", "listPrice"=>"form1[0].#subform[1].NumericField2[71]", "soldPrice"=>"form1[0].#subform[1].SoldAmt24[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[47]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[70]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[46]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[69]", "industry"=>"form1[0].#subform[1].DropDownList3[23]",  "commAmmount"=>"form1[0].#subform[1].CommAmt24[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[47]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[46]"),
                "row25" => array("bisName"=>"form1[0].#subform[1].TextField7[24]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[24]", "listPrice"=>"form1[0].#subform[1].NumericField2[74]", "soldPrice"=>"form1[0].#subform[1].SoldAmt25[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[49]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[73]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[48]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[72]", "industry"=>"form1[0].#subform[1].DropDownList3[24]",  "commAmmount"=>"form1[0].#subform[1].CommAmt25[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[49]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[48]"),
                "row26" => array("bisName"=>"form1[0].#subform[1].TextField7[25]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[25]", "listPrice"=>"form1[0].#subform[1].NumericField2[77]", "soldPrice"=>"form1[0].#subform[1].SoldAmt26[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[51]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[76]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[50]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[75]", "industry"=>"form1[0].#subform[1].DropDownList3[25]",  "commAmmount"=>"form1[0].#subform[1].CommAmt26[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[51]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[50]"),
                "row27" => array("bisName"=>"form1[0].#subform[1].TextField7[26]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[26]", "listPrice"=>"form1[0].#subform[1].NumericField2[80]", "soldPrice"=>"form1[0].#subform[1].SoldAmt27[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[53]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[79]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[52]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[78]", "industry"=>"form1[0].#subform[1].DropDownList3[26]",  "commAmmount"=>"form1[0].#subform[1].CommAmt27[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[53]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[52]"),
                "row28" => array("bisName"=>"form1[0].#subform[1].TextField7[27]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[27]", "listPrice"=>"form1[0].#subform[1].NumericField2[83]", "soldPrice"=>"form1[0].#subform[1].SoldAmt28[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[55]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[82]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[54]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[81]", "industry"=>"form1[0].#subform[1].DropDownList3[27]",  "commAmmount"=>"form1[0].#subform[1].CommAmt28[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[55]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[54]"),
                "row29" => array("bisName"=>"form1[0].#subform[1].TextField7[28]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[28]", "listPrice"=>"form1[0].#subform[1].NumericField2[86]", "soldPrice"=>"form1[0].#subform[1].SoldAmt29[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[57]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[85]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[56]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[84]", "industry"=>"form1[0].#subform[1].DropDownList3[28]",  "commAmmount"=>"form1[0].#subform[1].CommAmt29[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[57]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[56]"),
                "row30" => array("bisName"=>"form1[0].#subform[1].TextField7[29]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[29]", "listPrice"=>"form1[0].#subform[1].NumericField2[89]", "soldPrice"=>"form1[0].#subform[1].SoldAmt30[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[59]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[88]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[58]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[87]", "industry"=>"form1[0].#subform[1].DropDownList3[29]",  "commAmmount"=>"form1[0].#subform[1].CommAmt30[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[59]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[58]"),
                "row31" => array("bisName"=>"form1[0].#subform[1].TextField7[30]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[30]", "listPrice"=>"form1[0].#subform[1].NumericField2[91]", "soldPrice"=>"form1[0].#subform[1].SoldAmt31[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[60]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[90]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[61]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[92]", "industry"=>"form1[0].#subform[1].DropDownList3[30]",  "commAmmount"=>"form1[0].#subform[1].CommAmt31[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[61]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[60]"),
                "row32" => array("bisName"=>"form1[0].#subform[1].TextField7[31]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[31]", "listPrice"=>"form1[0].#subform[1].NumericField2[95]", "soldPrice"=>"form1[0].#subform[1].SoldAmt32[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[63]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[94]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[62]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[93]", "industry"=>"form1[0].#subform[1].DropDownList3[31]",  "commAmmount"=>"form1[0].#subform[1].CommAmt32[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[63]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[62]"),
                "row33" => array("bisName"=>"form1[0].#subform[1].TextField7[32]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[32]", "listPrice"=>"form1[0].#subform[1].NumericField2[98]", "soldPrice"=>"form1[0].#subform[1].SoldAmt33[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[65]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[97]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[64]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[96]", "industry"=>"form1[0].#subform[1].DropDownList3[32]",  "commAmmount"=>"form1[0].#subform[1].CommAmt33[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[65]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[64]"),
                "row34" => array("bisName"=>"form1[0].#subform[1].TextField7[33]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[33]", "listPrice"=>"form1[0].#subform[1].NumericField2[101]", "soldPrice"=>"form1[0].#subform[1].SoldAmt34[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[67]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[100]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[66]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[99]", "industry"=>"form1[0].#subform[1].DropDownList3[33]",  "commAmmount"=>"form1[0].#subform[1].CommAmt34[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[67]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[66]"),
                "row35" => array("bisName"=>"form1[0].#subform[1].TextField7[34]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[34]", "listPrice"=>"form1[0].#subform[1].NumericField2[104]", "soldPrice"=>"form1[0].#subform[1].SoldAmt35[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[69]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[103]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[68]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[102]", "industry"=>"form1[0].#subform[1].DropDownList3[34]",  "commAmmount"=>"form1[0].#subform[1].CommAmt35[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[69]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[68]"),
                "row36" => array("bisName"=>"form1[0].#subform[1].TextField7[35]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[35]", "listPrice"=>"form1[0].#subform[1].NumericField2[107]", "soldPrice"=>"form1[0].#subform[1].SoldAmt36[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[71]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[106]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[70]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[105]", "industry"=>"form1[0].#subform[1].DropDownList3[35]",  "commAmmount"=>"form1[0].#subform[1].CommAmt36[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[71]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[70]"),
                "row37" => array("bisName"=>"form1[0].#subform[1].TextField7[36]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[36]", "listPrice"=>"form1[0].#subform[1].NumericField2[110]", "soldPrice"=>"form1[0].#subform[1].SoldAmt37[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[73]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[109]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[72]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[108]", "industry"=>"form1[0].#subform[1].DropDownList3[36]",  "commAmmount"=>"form1[0].#subform[1].CommAmt37[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[73]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[72]"),
                "row38" => array("bisName"=>"form1[0].#subform[1].TextField7[37]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[37]", "listPrice"=>"form1[0].#subform[1].NumericField2[113]", "soldPrice"=>"form1[0].#subform[1].SoldAmt38[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[75]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[112]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[74]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[111]", "industry"=>"form1[0].#subform[1].DropDownList3[37]",  "commAmmount"=>"form1[0].#subform[1].CommAmt38[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[75]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[74]"),
                "row39" => array("bisName"=>"form1[0].#subform[1].TextField7[38]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[38]", "listPrice"=>"form1[0].#subform[1].NumericField2[116]", "soldPrice"=>"form1[0].#subform[1].SoldAmt39[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[77]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[115]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[76]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[114]", "industry"=>"form1[0].#subform[1].DropDownList3[38]",  "commAmmount"=>"form1[0].#subform[1].CommAmt39[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[77]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[76]"),
                "row40" => array("bisName"=>"form1[0].#subform[1].TextField7[39]", "soldDate"=>"form1[0].#subform[1].DateTimeField4[39]", "listPrice"=>"form1[0].#subform[1].NumericField2[119]", "soldPrice"=>"form1[0].#subform[1].SoldAmt40[0]", "buySideAgent"=>"form1[0].#subform[1].TextField8[79]", "buySideCommish"=>"form1[0].#subform[1].NumericField2[118]", "sellSideAgent"=>"form1[0].#subform[1].TextField8[78]", "sellSideCommish"=>"form1[0].#subform[1].NumericField2[117]", "industry"=>"form1[0].#subform[1].DropDownList3[39]",  "commAmmount"=>"form1[0].#subform[1].CommAmt40[0]", "closedSydney"=>"form1[0].#subform[1].CheckBox1[79]", "closingSTMT"=>"form1[0].#subform[1].CheckBox1[78]"),

    "totalOfDeals" => "form1[0].#subform[0].NumericField4[0]",
    "grossCommCollected" => "form1[0].#subform[0].Total1[0]",
    "numOfBuyerCons" => "form1[0].#subform[0].NumericField6[0]",
    "numOfFrancReferrals" => "form1[0].#subform[0].NumericField6[1]",
    "numOfBrands" => "form1[0].#subform[0].NumericField6[2]",
    "franch1" => "form1[0].#subform[0].TextField5[0]",
    "franch2" => "form1[0].#subform[0].TextField5[1]",
    "franch3" => "form1[0].#subform[0].TextField5[2]",
    "franch4" => "form1[0].#subform[0].TextField5[3]",
    "franch5" => "form1[0].#subform[0].TextField5[5]",
    "franch6" => "form1[0].#subform[0].TextField5[4]",
    "grossCommCollected1" => "form1[0].#subform[0].Total2[0]",
    "consultFees" => "form1[0].#subform[0].Misc1[0]",
    "401k" => "form1[0].#subform[0].Misc2[0]",
    "SBAloan" => "form1[0].#subform[0].Misc3[0]",
    "realEstateFee" => "form1[0].#subform[0].Misc4[0]",
    "BOV" => "form1[0].#subform[0].Misc5[0]",
    "grossCommCollected2" => "form1[0].#subform[0].Total3[0]",
    "AFIReferrals" => "form1[0].#subform[0].NumericField1[0]",
    "nameOfClient1" =>  "form1[0].#subform[0].TextField5[7]",
    "nameOfClient2" => "form1[0].#subform[0].TextField5[6]",
    "grossCommCollected3" => "form1[0].#subform[0].Total4[0]",
    "totallGross" => "form1[0].#subform[0].TotalGross[0]",
    "grossFees" => "form1[0].#subform[0].TotRoy1[0]",
    "monthlyMin" => "form1[0].#subform[0].MinRoy[0]",
    "franchResaleFee" => "form1[0].#subform[0].Misc6[0]",
    "techFee" => "form1[0].#subform[0].TotRoy2[0]",
    "numOfAddAgents" => "form1[0].#subform[0].AgentNum[0]",
    "agentsPrice" => "form1[0].#subform[0].TotRoy5[0]",
    "totalDue" => "form1[0].#subform[0].NumericField3[0]"    
    
);


	/**
	 * Returns the static model of the specified AR class.
	 * @return Template the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_royaltyreport'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'royaltyreport'
			),
            'ERememberFiltersBehavior' => array(
				'class'=>'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
			'InlineEmailModelBehavior' => array(
				'class'=>'application.components.behaviors.InlineEmailModelBehavior',
			)
		));
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($pageSize = null) {
		$criteria=new CDbCriteria;
		return $this->searchBase($criteria, $pageSize);
	}

    public function beforeSave() {
        $this->makeReport();
        return parent::beforeSave();


    }

    public function makeReport(){
        //check to make sure fields are set
        if(empty($this->c_Franchises) || empty($this->c_EndDate) || empty($this->c_StartDate))return;
        //$media = Media::model()->findByPk(200742798);

        $media = Media::model()->findByPk(200765715);
        //check to see if we need to use 2 page ver
        $franchRecord = Franchisees::model()->findByAttributes(array("nameId" => $this->c_Franchises));
        $StartMonth = $this->c_StartDate;
        $EndMonth = $this->c_EndDate;
        //get the list of people from the office
        $employeRecords = Employees::model()->findAllByAttributes(array('c_franchisee__c'=>$this->c_Franchises));
        $userNames = "(";
        foreach($employeRecords as $emp){
            if($userNames == "("){
                $userNames = $userNames . "'" . $emp->c_user__c . "'";
            }else{
                $userNames = $userNames . ",'" . $emp->c_user__c . "'";
            }

        }
        $userNames = $userNames . ")";


        //adding a couple seconds to each end in case some one has closedate at the first of the month and the time is 12:00
        $deals = Opportunity::model()->findAllByAttributes(array('salesStage' => "Sold"), "c_actual_close_date_c > " . ($StartMonth - 30) . " AND c_actual_close_date_c < "  . ($EndMonth + 30) . " AND assignedTo in " . $userNames );

        if(count($deals) > 8){
            $media = Media::model()->findByPk(200767242);
        }


        //check to see if model already has a file, if not make one
        if(empty($this->c_)){
            $newMedia = new Media;
            $newMedia->fileName = time() . "Report.pdf";
            $newMedia->uploadedBy = "admin";
            $newMedia->save();
            $this->c_ = $newMedia->nameId;
            //$this->save();
        }else{
            $newMedia = Media::model()->findByAttributes(array("nameId" => $this->c_));
            
        }
    
        $behavior = MPDFBehavior::createInstance();
        $pdf = $behavior->newPdf();
        $setaDoc = $pdf->setUpDocForFill($media->path, "/var/www/transworld_crm/uploads/protected/media/admin/" . $newMedia->fileName);
        $FillForm = $pdf->setUpFillClass($setaDoc, false);
        $fieldsOnForm = $pdf->getAcroFields($FillForm);
        $fieldNames = $pdf->getAcroNames($fieldsOnForm);
        //$employee = Employees::model()->findByAttributes(array('c_user__c'=>User::getMe()->username));
        //Franchisees::model()->findByAttributes(array('nameId'=>$employee->c_franchisee__c));
        $this->fillInFranchInfo($franchRecord, $pdf, $fieldsOnForm);
        //get the deals
        //$StartMonth = strtotime(date('' . $this->c_Year  . '-' . $this->c_Month . '-01 00:00:00'));
        //$numberOfDays = cal_days_in_month(CAL_GREGORIAN, $this->c_Month, $this->c_Year );
        //$EndMonth = $StartMonth + ($numberOfDays * 60*60*24);

        $this->fillInListings($deals, $pdf, $fieldsOnForm);
        //fill in month and totals at top
        $this->fillTopBar($franchRecord, $userNames, $pdf, $fieldsOnForm);


        //$pdf->setFieldByName($fieldsOnForm, 'form1[0].#subform[0].TextField2[0]', "test");
        $pdf->saveForm($setaDoc);
        //$fieldsOnForm['form1[0].#subform[0].TextField2[0]']->setValue("test");
        //$setaDoc->save()->finish(); 
      // printR( $fieldsOnForm['form1[0].#subform[0].TextField2[0]'],1);

    }

    public function fillTopBar($franch, $userSQL, $pdf, $fieldsOnForm){
        //$StartMonth = strtotime(date('' . $this->c_Year  . '-' . $this->c_Month . '-01 00:00:00'));
        //$numberOfDays = cal_days_in_month(CAL_GREGORIAN, $this->c_Month, $this->c_Year );
        //$EndMonth = $StartMonth + ($numberOfDays * 60*60*24);
        $StartMonth = $this->c_StartDate;
        $EndMonth = $this->c_EndDate;
        $deals = Opportunity::model()->findAllByAttributes(array('salesStage' => "Sold"), "c_actual_close_date_c > " . ($StartMonth - 30) . " AND c_actual_close_date_c < "  . ($EndMonth + 30) . " AND assignedTo in " . $userSQL );     
        //get the count of closed deals deals
        $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["closedDeals"], count($deals) );   
        //set the month 
        //get avrage date
        $avgDate = (int)(($StartMonth + $EndMonth) / 2);



        $monthName = date('F',  $avgDate);
        $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["ReportMonth"], $monthName );


        //get pending deals

        $PendDeals = Opportunity::model()->findAllByAttributes(array('salesStage' => "DD/Contract"), "assignedTo in " . $userSQL );
        $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["pendingDeals"], count($PendDeals));

   
        $activeLists = Listings2::model()->findAllByAttributes(array('c_client_status__c' => "Active", "c_franchisee__c" => $this->c_Franchises));
        $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["activeListings"], count($activeLists));
     
                //if there is more then 8 deals fill in 2nd page
        if(count($deals) > 8){
            $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["closedDeals2"], count($deals) );
            $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["activeListings2"], count($activeLists));
            $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["pendingDeals2"], count($PendDeals));

        }



        

    }


    public function fillInFranchInfo($franchRecord, $pdf, $fieldsOnForm){
        //write name
        $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["FranName"], $franchRecord->name );
        //write territory
        $pdf->setFieldByName($fieldsOnForm,$this->acroRoyalMap["Territory"], $franchRecord->name );         
    }

    public function fillInListings($deals, $pdf, $fieldsOnForm){
        foreach($deals as $key => $deal){
            $rowNumber = (string)($key + 1);
            $listMap = $this->acroRoyalMap["row" . $rowNumber];
            $listing = Listings2::model()->findByAttributes(array('nameId' => $deal->c_listinglookup__c)); 
                if(isset($listing)){
                    //$pdf->setFieldByName($fieldsOnForm,$listMap["bisName"], $listing->name );      
                                    //will come back to this one need to make an array maping for industry
                    $indus = json_decode($listing->c_category__c,1);
                    if(isset( $indus[0]) && isset($this->acroIndustryMap[$indus[0]]))
                      $pdf->setFieldByName($fieldsOnForm,$listMap["industry"], $indus[0] );
                }
                $pdf->setFieldByName($fieldsOnForm,$listMap["bisName"], $deal->name );
                $pdf->setFieldByName($fieldsOnForm,$listMap["soldDate"], date("Y-m-d",$deal->c_actual_close_date_c)); 
                $pdf->setFieldByName($fieldsOnForm,$listMap["listPrice"], $deal->c_listing_price__c); 
                $pdf->setFieldByName($fieldsOnForm,$listMap["soldPrice"], $deal->c_sold_price_);
                //get just the name not nameId
                if(!empty($deal->c_buyer_agent_c)){
                    $buyernameAndId = Fields::nameAndId($deal->c_buyer_agent_c);
                    if(!empty($buyernameAndId[0]))
                        $pdf->setFieldByName($fieldsOnForm,$listMap["buySideAgent"], $buyernameAndId[0] ); 
                    else
                        $pdf->setFieldByName($fieldsOnForm,$listMap["buySideAgent"], $deal->c_buyer_agent_c );
                }

                $pdf->setFieldByName($fieldsOnForm,$listMap["buySideCommish"], $deal->c_Buy_side_actual); 

                if(!empty($deal->c_selleragent)){
                    $sellernameAndId = Fields::nameAndId($deal->c_selleragent);
                    if(!empty($sellernameAndId[0]))
                        $pdf->setFieldByName($fieldsOnForm,$listMap["sellSideAgent"], $sellernameAndId[0] );
                    else
                        $pdf->setFieldByName($fieldsOnForm,$listMap["sellSideAgent"], $deal->c_selleragent );
                }

                
                $pdf->setFieldByName($fieldsOnForm,$listMap["sellSideCommish"], $deal->c_Sell_side_actual);
                //will come back to this one need to make an array maping for industry
                //adding check for co broke with non tworld agent
                if($deal->c_cobroke == 1){
                    $Buyemployee = Employees::model()->findByAttributes(array('c_user__c' => $deal->c_buyer_agent_c ));
                    $Sellemployee = Employees::model()->findByAttributes(array('c_user__c' => $deal->c_selleragent ));
                    //if both are tworld agents then just use total else use one or other
                    if(isset($Buyemployee) && isset($Sellemployee)){
                        $pdf->setFieldByName($fieldsOnForm,$listMap["commAmmount"], $deal->c_actual_commission_c);
                    }elseif(isset($Buyemployee)){
                        $pdf->setFieldByName($fieldsOnForm,$listMap["commAmmount"], $deal->c_Buy_side_actual);
                    }else{
                        $pdf->setFieldByName($fieldsOnForm,$listMap["commAmmount"], $deal->c_Sell_side_actual);
                    }

                }else{
                    $pdf->setFieldByName($fieldsOnForm,$listMap["commAmmount"], $deal->c_actual_commission_c);
                    //if not co broke set sellside to assign to
                    $user = User::model()->findByAttributes(array('username' => $deal->assignedTo ));
                    $pdf->setFieldByName($fieldsOnForm,$listMap["sellSideAgent"], $user->name );
                }
                $pdf->setFieldByName($fieldsOnForm,$listMap["closedSydney"], 1 );
                //$pdf->setFieldByName($fieldsOnForm,$listMap["closedSydney"], $deal );
                //$pdf->setFieldByName($fieldsOnForm,$listMap["closingSTMT"], $deal );
            
            if($key > 38) return;
        }


    }   


        public function isQuick() {
            return true;
        }

     
        public function renderAttribute($fieldName, $makeLinks = true, $textOnly = true, $encode = true){
            switch ($fieldName) {
                case 'c_':
                    if (isset($this->c_) && $this->c_ != null) {
                        $media = X2Model::model('Media')->findByAttributes(array('nameId' => $this->c_ ));;
                        if(isset($media))
                            return $media->getMediaLink() . '  |  ' . $media->getDownloadLink();
                    }
                    return '--';
                default:
                    return parent::renderAttribute($fieldName, $makeLinks, $textOnly, $encode);
            }
        }





}
