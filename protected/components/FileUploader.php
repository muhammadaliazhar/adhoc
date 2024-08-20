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
 * Widget to upload files via AJAX. 
 * @package  application.components
 * @author Alex Rowe <alex@x2engine.com>, Peter Czupil <peter@x2engine.com>
 *
 * Examples Usage:
 *
 * widget('FileUploader', array(
 *        
 *     'id' => 'myFileUploader',
 *     
 *     'mediaParams' => array (
 *         'associationType' => 'Contacts',
 *         'associationId'   => 23
 *     ),
 *     
 *     'viewParams' => array (
 *         'showButton' => false
 *     ),
 *
 *     'events' => array (
 *         'success' => 'console.log("success")'
 *     )
 *
 *     'acceptedFiles' => 'image/*'
 * 
 * ));
 *
 * How to access in Javascript:
 * x2.FileUploader.list['myFileUploader']
 *
 * See js/FileUploader.js
 * for Javascript Examples
 * 
 */
class FileUploader extends X2Widget {

    /**
     * Static counter for number of instances
     * @var integer
     */
    public static $instanceCount = 0;

    /**
     * Config array of extra options to be sent to viewFile
     * @var array
     */
    public static $defaultViewParams = array (
        'class' => '',
        'noPadding' => false,
        'showButton' => true,
        'open' => false,
        'closeButton' => true,
        'buttonText' => null,
        'style' => '',
    );

    /**
     * @see X2Widget::$JSClass
     */
    public $JSClass = 'FileUploader';

    /**
     * @see X2Widget::$viewFile
     */
    public $viewFile = 'fileUploader';

    /**
     * Id / Namespace of this instance. Used to create a unique
     * ID, and to reference 
     * @var string
     */
    public $id;

    /**
     * Url to upload media to 
     */
    public $url = '/site/upload';

    /**
     * Wether to allow Google Drive
     * @var array 
     */
    public $googleDrive = true;

    /**
     * Array of model attributes to set to uploaded files
     * @var array 
     */
    public $mediaParams = array();

    /**
     * Array of model attributes to set to uploaded files
     * @var array 
     */
    public $viewParams = array();

    /**
     * Array of Javascript snippets
     * @var array 
     */
    public $events = array(
        // 'success' => 'console.log(this)'
    );

    public $acceptedFiles = '';

    /**
     * Determines if user will want to toggle display
     * @var boolean
     */
    public $displayToggle = true;

    // For enable reordering on the preview ELements
    public $previewElementSortable = false;

    // for enable custom remove button to remove the file on click
    public $removeFileBtn = false;

    // for enable download button
    public $downloadBtn = false;

    // for save files order when sortable option is enabled
    public $saveFilesOrderAPIUrl = "";

    // for enable existing files loading preview
    public $showExistingFiles = false;

    // for save files order when sortable option is enabled
    public $getExistingFilesUrl = "";

    // for preview image of document files when this option is enabled
    public $documentFilesPreviewImage = false;

    // for the preview of whole document in the popup when this option is enabled
    public $documentView = false;

    /**
     * Determines whether to render form element
     * @var boolean
     */
    public $displayForm = true;

    public function init() {
        // Increment instance count
        self::$instanceCount++;
        
        // Create a unique ID if one is not set
        if (empty($this->id)) {
            $this->id = 'attachments-'.self::$instanceCount;
        }

        // Create a name space to register mutiple scripts
        $this->namespace = 'attachments'.self::$instanceCount;

        // Set up default view Params
        $this->viewParams = array_merge (self::$defaultViewParams, $this->viewParams);
        if(is_null($this->viewParams['buttonText'])){
            $this->viewParams['buttonText'] = Yii::t('media','Upload File');
        }

        $this->googleDrive &= Yii::app()->params->profile->mediaWidgetDrive && 
            Yii::app()->settings->googleIntegration;


        $this->registerJSEvents ($this->events);
    }

    public function run () {
        $this->registerPackages ();
        $this->instantiateJSClass ();
        $this->render ($this->viewFile, $this->viewParams);
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'FileUploaderJS' => array(
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array(
                    'js/FileUploader.js'
                ),
                'depends' => array('Dropzone', 'auxlib')
            ),
            'FileUploaderCSS' => array(
                'baseUrl' => Yii::app()->theme->baseUrl,
                'css' => array(
                    'css/components/FileUploader.css'
                ),
            ),
        ));
        return $this->_packages;
    }

    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge( 
                parent::getJSClassParams(), array(
                    'url' => $this->url,
                    'id'  => $this->id,
                    'mediaParams' => $this->mediaParams,
                    'viewParams' => $this->viewParams,
                    'acceptedFiles' => $this->acceptedFiles,
                    "downloadBtn" => $this->downloadBtn,
                    "removeFileBtn" => $this->removeFileBtn,
                    'acceptedFiles' => $this->acceptedFiles,
                    "saveFilesOrderAPIUrl" => $this->saveFilesOrderAPIUrl,
                    "previewElementSortable" => $this->previewElementSortable,
                    "showExistingFiles" => $this->showExistingFiles,
                    "getExistingFilesUrl" => $this->getExistingFilesUrl,
                    "documentView" => $this->documentView,
                    "documentFilesPreviewImage" => $this->documentFilesPreviewImage,
                    'maxFileSize' =>  
                        AppFileUtil::sizeToMb (ini_get('upload_max_filesize'), false),
                    'parallelUploads' => 1,
                )
            );
        }
        return $this->_JSClassParams;
    }


    public function registerJSEvents ($events) {
        $js = '';
        foreach ($events as $event => $snippet) {
            $js .= "x2.FileUploader.on('$this->id', '$event', function(){".$snippet.";});";
        }
        Yii::app()->clientScript->registerScript ("FileUploaderEvents-$this->id", $js);
    }
}
    
?>
