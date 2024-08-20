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
 * @author: Justin Toyomitsu <justin@x2engine.com>
 */



Yii::import('application.models.X2List');
Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_signatures".
 * @package application.modules.template.models
 */
class X2Signature extends CActiveRecord {

    public $font = array('fonts/Allura/Allura-Regular.otf', 'fonts/Otto/Otto.ttf', 'fonts/may-queen/mayqueen.ttf');
    public $original_image = 'template_signature.png';
    public $template_path = 'protected/modules/x2sign/assets/main/';
    public $signature_folder = 'protected/modules/x2sign/sign_folder/';

    /**
     * Returns the static model of the specified AR class.
     * @return Template the static model class
     */
    public static function model($className=__CLASS__) { return parent::model($className); }

    /**
     * @return string the associated database table name
     */
    public function tableName() { return 'x2_signatures'; }
	
    public function behaviors() {
        return array_merge(parent::behaviors(),array(
		'LinkableBehavior'=>array(
			'class'=>'LinkableBehavior',
			'module'=>'x2sign'
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

        
    public function toBin($str) {
        $str = (string)$str;
        $l = strlen($str);
        $result = '';
        for($i=0; $i<$l; $i++){
            $result .= str_pad(decbin(ord($str[$i])), 8, "0", STR_PAD_LEFT);
        }
        return $result;
    }

    public function toString($binary){
        return pack('H*', base_convert($binary, 2, 16));
    }

    /**
     * RULES: MUST BE A 256 STRING
     * @returns NONE
     */
    public function encryptToImage($im, $encrypt){
        $bitStr = '';
            foreach(str_split($encrypt) as $v){
            $bitStr .= str_pad(decbin(hexdec($v)), 4, '0', STR_PAD_LEFT);
        }
        for($x = 0; $x < strlen($bitStr); $x++){
            $j = $x * 215; //change pixel for every 215 pixel
            $newY = floor($j / 550);
            $newX = $j % 550;
            /**
             * Change only the blue color.
             */
            $rgb = imagecolorat($im, $newX, $newY);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $newB = $this->toBin($b);
            $newB[strlen($newB) - 1] = $bitStr[$x];
            $newB = $this->toString($newB);
            $new_color = imagecolorallocate($im, $r, $g, $newB);
            imagesetpixel($im, $newX, $newY, $new_color);
        }
    }

    /**
     * How to Call(Example):
     * 1. $im2 = imagecreatefrompng("protected/modules/x2sign/template2.png");
     * 2. $decrypted_message = $this->decryptImage($im2);
     * @return (string)
     */
    public function decryptImage($im){
        $message = '';
        for($x = 0; $x < 256; $x++){
            $j = $x * 215;
            $newY = floor($j / 550);
            $newX = $j % 550;
            $rgb = imagecolorat($im, $newX, $newY);
            $r = ($rgb >>16) & 0xFF;
            $g = ($rgb >>8) & 0xFF;
            $b = $rgb & 0xFF;
    
            $blue = $this->toBin($b);
            $message .= $blue[strlen($blue) - 1];
        }
        return $message;
    }

    /**
     * MAIN FUNCTION (ENCRYPTION STRING)
     * @return string (sha256)
     */
    public function createEncrypt($modelType, $id, $documentId, $signedDate){
        /**
         * Our Encryption will be created using 2 parts (RED | BLUE)
         * Our reasoning for not using (salt) in this encryption is our 
         * assumption that signedDate, documentId, modelType, and modelId is enough
         * for the final encryption to be unique.
         */
    
        //=============== RED SECTION =============
        $red_section = $modelType . $id;

        //=============== BLUE SECTION =============
        $blue_section = $signedDate . $documentId; 

        return hash("sha256", $red_section . $blue_section);
    }    

    /**
     * MAIN FUNCTION
     * @returns string (path)
     */
    public function createImage($name, $modelType, $modelId, $font = 0){
        $file_name = $this->signature_folder . str_replace(' ', '', $name) . 
                        '_' . $modelType . '_' . $modelId . '.png';

        $name = substr($name, 0, 14); //any greater and it will be cut from image

        /* ========= CHECK DB FOR IMAGE ========= */
        $signature = X2Signature::model()->findByAttributes(array(
            'modelId' => $modelId,
            'modelType' => $modelType
        ));
        
        if(!isset($signature)) {
            $im = imagecreatefrompng($this->template_path . $this->original_image);
            if($im){
                list($width) = getimagesize($this->template_path . $this->original_image);
                $text_box = imagettfbbox(50, 0, $this->template_path . $this->font[$font], $name);
                $text_box_width = (abs($text_box[0]) + abs($text_box[2]));
                if($width > $text_box_width) {
                    $corr_pos = ($width - $text_box_width) / 2;
                    } else {
                    $corr_pos = 0;
                }
                $black = imagecolorallocate($im, 0, 0, 0);
                $background = imagecolorallocate($im, 255, 255, 255);
                imagecolortransparent($im, $background);
                imagettftext($im, 50, 0, $corr_pos, 60, $black, $this->template_path . $this->font[$font], $name);
                if (function_exists('imageresolution')) imageresolution($im, 144);
                imagepng($im, $file_name, 9);
            }
            return $this->path = $file_name;
        }else{
            return $signature->path;
        }        
    }

    public function getSignatureImage() {
        $image = base64_encode(file_get_contents($this->path));
        $src = 'data:' . mime_content_type($this->path) . ';base64,' . $image;
        $signatureImage = "<img src='" . $src . "'>";
        return $signatureImage;
    }
}
