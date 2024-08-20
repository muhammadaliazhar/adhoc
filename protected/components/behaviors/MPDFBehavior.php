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






require_once Yii::app()->basePath.'/integration/MPDF/autoload.php';
//require_once Yii::app()->basePath.'/integration/FPDI_PDF_Parser/src/autoload.php';
//require_once Yii::app()->basePath.'/integration/SetaPDF-Core/library/SetaPDF/Autoload.php';
//require_once Yii::app()->basePath.'/integration/SetaPDF-FormFiller-Full/SetaPDF-FormFiller-Full_2.38.3.1671/library/SetaPDF/Autoload.php';
require_once(Yii::app()->basePath.'/integration/newSeta/library/SetaPDF/Autoload.php');

class MPDFBehavior extends CBehavior {

    public function newPdf() {
        $pdf = new Pdf;
        $pdf->SetFont('helvetica', '', 12);
        return $pdf;
    }

    public static function createInstance() {
        $behavior = Yii::app()->controller->attachBehavior('MPDFBehavior', new MPDFBehavior);
        return $behavior;
    }

}

class Pdf extends \Mpdf\Mpdf {

    const PX_PT_RATIO = 0.75;

    public  $encrypted;

    // variables relating to current imported file
    private $pageCount;
    private $pageNum;
    private $setaWriter;
    private $setaReader;
    private $setaSourceFile;
    private $setaDestFile;
    private $document;
    private $newDocument;

    public function __construct($filePath = null) {
       if (isset($filePath))
           $this->checkForEncryption($filePath);

       parent::__construct();
    }

    public function getPageNum() {
        return $this->pageNum;
    }

    /**
     * Set pagecount on pdf load
     */
    public function setSourceFile($file) {
        $this->pageNum = 0;
        return $this->pageCount = parent::setSourceFile($file);
    }

    public function setSetaSourceFile($file) {
        $this->pageNum = 0;
        $this->setaSourceFile = $file;
        if (isset($this->document)) $this->pageCount = $this->document->getPages()->count();
    }

    public function setSetaDestFile($file) {
        $this->setaDestFile = $file;
        if (!isset($this->newDocument)) {
            $this->setaWriter = new SetaPDF_Core_Writer_File($file);
            $this->newDocument = new SetaPDF_Core_Document($this->setaWriter);
        }
    }

    public function setaOutput() {
        $this->newDocument->save()->finish();
    }

    public function isEncrypted() {
        return $this->encrypted;
    }

    public function checkForEncryption($filePath = null) {
        if (isset($filePath)) {
           $this->setaReader = new SetaPDF_Core_Reader_File($filePath);
           $this->document = SetaPDF_Core_Document::load($this->setaReader);

           // Check for encryption
           if ($this->document->hasSecurityHandler() && $this->document->getSecHandler() instanceof SetaPDF_Core_SecHandler_Standard) {
               $this->encrypted = 1;
               return true;
           }
           else if ($this->document->hasSecurityHandler() && !($this->document->getSecHandler() instanceof SetaPDF_Core_SecHandler_Standard))
               throw new Exception("Unsupported security handler.");
           else $this->encrypted = 0;
       } else { // No filePath supplied, assume not encrypted
           $this->encrypted = 0;
           return false;
       }

       return false;
    }

    /**
     * Imports the next page and increments $pageNum
     * @return pageNum (or null if no next page)
     */
    public function nextPage() {
        if ($this->pageNum == $this->pageCount) return null;

        //if($this->encrypted) {
            return $this->document->getPages()->getPage(++$this->pageNum);
        //}

        /*$this->AddPageByArray(['sheet-size'=>'Letter']);
        $tpl = $this->importPage(++$this->pageNum);
        $this->useTemplate($tpl); */
        //return $this->pageNum;
    }

    public function addSetaPage($page) {
        $pages = $this->newDocument->getCatalog()->getPages();
        $pages->append($page);
    }

    public function removeAnnotations($page, $fontSize = 12) {
        $page->flattenInheritedAttributes();
        $page->getStreamProxy()->encapsulateExistingContentInGraphicState();
        $canvas = $page->getCanvas();
        $canvas->normalizeRotation($page->getRotation(), $page->getBoundary());
        $font = SetaPDF_Core_Font_Standard_Helvetica::create($this->newDocument);

        // Instantiate annotations helper
        $annotations = $page->getAnnotations();
        if(!empty($annotations)) {
            $widgetText = new SetaPDF_Core_Text_Block($font, $fontSize);

            // Find all widget annotations on the page
            $widgets = $annotations->getAll(SetaPDF_Core_Document_Page_Annotation::TYPE_WIDGET);
            foreach($widgets as &$widget) {
                $rect = $widget->getRect();
                $value = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($widget->getDictionary(), 'V');
                if ($value instanceof SetaPDF_Core_Type_StringValue) {
                    $value = SetaPDF_Core_Encoding::convertPdfString($value->getValue());
                    if(!empty($value)) {
                        $widgetX = ((float) $rect->getLlx() + 2);
                        $widgetY = ((float) $rect->getLly() + 1);
                        $widgetText->setText($value);
                        $widgetText->draw($canvas, $widgetX, $widgetY);
                    }
                }
                $annotations->remove($widget);
            }
        }

        return $page;
    }

    public function WriteSetaText($page, $x, $y, $txt, $fontSize) {
        $page->flattenInheritedAttributes();
        $page->getStreamProxy()->encapsulateExistingContentInGraphicState();
        $rot = $page->getRotation();
        $canvas = $page->getCanvas();
         if ($rot > 0) { // need to normalize graphic state if page is rotated
            $canvas->saveGraphicState();
            $canvas->normalizeRotation($page->getRotation(), $page->getBoundary());
        }
        $cropBox = $page->getCropBox();
        $mediaBox = $page->getMediaBox();
        $cropWidthDiff = $mediaBox->getWidth() - $cropBox->getWidth();
        $cropHeightDiff = $mediaBox->getHeight() - $cropBox->getHeight();
        $letterHeightDiff = $page->getHeight() / $this->px2pt(1056);
        $font = SetaPDF_Core_Font_Standard_Helvetica::create($this->newDocument);
        $text = new SetaPDF_Core_Text_Block($font, $fontSize * $letterHeightDiff);
        $text->setText($txt);
        $xPer = ($this->px2pt($x) + 6) / $this->px2pt(816);
        $yPer = ($this->px2pt(1056) - $this->px2pt($y) - $fontSize - 6) / $this->px2pt(1056);
        $text->draw($canvas, ($xPer * $page->getWidth()) + ($cropWidthDiff / 2), ($yPer * $page->getHeight()) + ($cropHeightDiff / 2));
        if ($rot > 0) $canvas->restoreGraphicState();
        return $page; 
    }

    /**
     * Overrides WriteText()
     * Parameters in pixels instead of millimeters.
     */
    public function WriteText($x, $y, $txt, $family='', $style='', $size=0, $alpha=1) {
        $oldFamily = $this->currentfontfamily;
        $oldStyle = $this->currentfontstyle;
        $oldSize = $this->currentfontsize;
        $this->SetFont(
            $family = $family ?: $oldFamily, 
            $style = $style ?: $oldStyle,
            $size = $size ?: $oldSize);
        $this->SetAlpha($alpha);
        $offY = $this->px2mm($size * 1.33); //1.33 px per pt
        parent::WriteText($this->px2mm($x), $this->px2mm($y) + $offY, $txt);
        $this->SetFont($oldFamily, $oldStyle, $oldSize);
        $this->SetAlpha(1);
    }

    /**
     * Print PNG image onto page
     *
     */

    public function writeSetaPngImage($page, $filename, $x, $y, $w, $h, $options = []) {
        $page->flattenInheritedAttributes();
        $page->getStreamProxy()->encapsulateExistingContentInGraphicState();
        $rot = $page->getRotation();
        $canvas = $page->getCanvas();
        if ($rot > 0) { // need to normalize graphic state if page is rotated
            $canvas->saveGraphicState();
            $canvas->normalizeRotation($page->getRotation(), $page->getBoundary());
        }
        $img = SetaPDF_Core_Image::getByPath($filename);
        $cropBox = $page->getCropBox();
        $mediaBox = $page->getMediaBox();
        $cropWidthDiff = $mediaBox->getWidth() - $cropBox->getWidth();
        $cropHeightDiff = $mediaBox->getHeight() - $cropBox->getHeight();
        $letterWidthDiff = $page->getWidth() / $this->px2pt(816);
        $letterHeightDiff = $page->getHeight() / $this->px2pt(1056);
        $xPer = ($this->px2pt($x)) / $this->px2pt(816);
        $yPer = ($this->px2pt(1056) - $this->px2pt($y) - $h) / $this->px2pt(1056);
        $xObject = $img->toXObject($this->newDocument);
        $xObject->draw($canvas, ($xPer * $page->getWidth()) + ($cropWidthDiff / 2), ($yPer * $page->getHeight()) + ($cropHeightDiff / 2), $this->px2pt($w) * $letterWidthDiff, $this->px2pt($h) * $letterHeightDiff);
        if ($rot > 0) $canvas->restoreGraphicState();
        return $page; 
    }

    /**
     * Based on Image() but with QOL improvements.
     * Parameters in pixels instead of millimeters. 
     */
    public function ImageByArray($filename, $x, $y, $w=0, $h=0, $options=[]) {
        $defaults = [
            'ext' => '',
            'href_link' => '',
            'paint' => true,
            'constrain' => true,
            'is_watermark' => false,
            'shownoimg' => true,
            'allowvector' => true,
        ];
        $options = array_merge($defaults, $options);
        parent::Image($filename, $this->px2mm($x), $this->px2mm($y), $this->px2mm($w), $this->px2mm($h), $options['ext'], $options['href_link'], $options['paint'], $options['constrain'], $options['is_watermark'], $options['shownoimg'], $options['allowvector']);
    }

    private function px2pt($n) {
        return (float) $n * Pdf::PX_PT_RATIO;
    }

    /**
     * Convert pixel distances to millimeters, which are expected by certain Mpdf functions.
     */
    private function px2mm ($n) {
        $n = (float) preg_replace("/[^0-9.]/", "", $n);
        $px2mm = 25.4/$this->img_dpi; //25.4mm/in
        return $n*$px2mm;
    }

   
    public function setUpDocForFill($fileLoaction,$writeLocation){
         
         $writer = new SetaPDF_Core_Writer_File($writeLocation);
         
         $document = SetaPDF_Core_Document::loadByFilename($fileLoaction,$writer);
         return $document;
    }
    
    /**
     * set up the form filling class
     */
    public function setUpFillClass($SetaDoc, $flat = false){

        $formFiller = new SetaPDF_FormFiller($SetaDoc);
        //set up appearances if not flat file
        if (!$flat) {
            // Set render appearance flag
            $renderAppearance = false;
            $formFiller->setNeedAppearances($renderAppearance);
        }

        return $formFiller;
        // access the classes/functionallities via the Core component
        //$document->getCatalog()->setPageLayout(SetaPDF_Core_Document_PageLayout::ONE_COLUMN);
        
    }
    
    public function saveForm($SetaDoc){
        $SetaDoc->save(true)->finish();
    }

    public function getAcroFields($FillClass){
        $fields = $FillClass->getFields();
        return $fields;
    }

    public function getAcroNames($fields){
        $fieldNames = $fields->getNames();
        return $fieldNames;

    }
    
    public function setFieldByName($fields, $fieldName, $value){
        $fields[$fieldName]->setValue($value);

    }

    public function makeFlatFile($nonFlatPath, $flatPath){

         $writer = new SetaPDF_Core_Writer_File($flatPath);
         $document = SetaPDF_Core_Document::loadByFilename($nonFlatPath,$writer);
         $formFiller = new SetaPDF_FormFiller($document);
         $fields = $formFiller->getFields();
         $fields->flatten();
         $document->save(true)->finish();

    }




}
