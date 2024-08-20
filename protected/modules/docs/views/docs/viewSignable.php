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
 * @author Clifton Chiang <clifton@x2engine.com>
 */

$this->setPageTitle(CHtml::encode($model->name));
$title = Yii::t('docs', 'X2SignDoc:');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/pdfReader/build/pdf.js');

$authParams = array('X2Model' => $model);
$menuOptions = array(
  'index', 'create', 'createEmail', 'createQuote', 'createSignable',
);

if (!$model->isNewRecord) {
    $existingRecordMenuOptions = array(
        'viewSignable', 'editSignable', 'permissions', 'delete',
    );
  $menuOptions = array_merge($menuOptions, $existingRecordMenuOptions);
}
$user = User::getMe();
if(Groups::userHasRole($user->id, 'Agent') && isset($model->libraryId)){
    $menuOptions = array(
        'index', 'create', 'createEmail', 'createQuote', 'createSignable',
    );
}
$this->insertMenu($menuOptions, $model, $authParams);
$this->renderPartial('_docPageHeader', compact('title', 'model'));
// $layoutManager = $this->widget('RecordViewLayoutManager', array('staticLayout' => false));

if(!$mediaId)
    $mediaId = $_GET["mediaId"];
Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl.'/js/signable.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScript('viewSignable', "viewSignable($signDoc->mediaId, $signDoc->fieldInfo);", CClientScript::POS_END);
Yii::app()->clientScript->registerCss('viewX2Sign', '
    .column {
        float: left;
        margin: unset;
    }

    #document {
        background-color: #e4e4e4;
        border: 1px solid black;
        border-radius: 5px;
        width: 850px;
    }

    hr {
        background-color: #555555;
        margin: 1px;
    }

    .x2-sign-doc {
        width: 816px; 
        height: 1056px;
        display: inline-block; 
        margin: auto;
        z-index: 1;
        position: absolute;
        left: 16px;
    }

    #pdf-dropzone {
        z-index: 2;
        width: 816px;
        height: 1056px;
        position: absolute;
        left: 16px;
    }

    .x2-sign-template {
        position: relative;
        height: 1070px;
        overflow: scroll;
    }

    .draggable {
        top: 6px;
    }

    .input-wrapper:not([id=initial-wrapper]) {
        background-color: #03cffc;
        height: 30px;
        position: relative;
        width: 115px;
        z-index: -1 !important;
        top: -20px;
        left: 0px;
        opacity: 50%;
    }

    input.x2-sign-input:not([id=initials]):not([id=checkbox]) {
        min-width: 9px !important;
        width: 95px;
        position: relative;
        vertical-align: top;
        text-align: left;
        font-family: monospace !important;
        letter-spacing: 0.14px;
        margin: unset !important;
        margin-top: 4px !important;
        margin-left: 4px !important;
    }

    input#text {
        position: relative;
        min-width: 27px !important;
        width: 27px;
        margin: unset !important;
        margin-top: 5px !important;
        margin-left: 5px !important;
    }

    input#checkbox {
        position: absolute;
        top: 9px;
        left: 9px;
    }

    input#initials {
        min-width: 1px !important;
        width: 44px;
        position: relative;        
        text-align: left;
        vertical-align: top;
        font-family: monospace;
        letter-spacing: 0.14px;
        margin: unset !important;
        margin-top: 4px !important;
        margin-left: 4px !important;

    }

    #assign-fields {
        width: 150px;
        border-radius: 5px;
        float: right;
        border-radius: 5px;
        border: 1px solid black;
    }

    div[id^="Signature"] > p {
        display: block;
        margin: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-right: -50%;
        transform: translate(-50%, -50%);
    }

    div[id^="Date"] > i {
        display: block;
        margin: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-right: -50%;
        transform: translate(-50%, -50%);
    }

    .color-preview {
        height: 25px;
        width: 25px;
        background-color: rgba(3, 207, 252, 0.5);
        border-radius: 50%;
        display: inline-block;
        position: absolute;
        margin-left: 7px;
    }
');

/**
 * Header
 */
$title = Yii::t('docs', 'Document:');
?>

<div id="assign-fields" class="column" style="font-size: 1.2rem;">
    <h3 style="margin: 0px 0px 0px !important; text-align: center;">Recipients</h3>
    <hr>
    <div class="recipients">
        <?php
            for($i = 1; $i <= 10; $i++) {
                echo "<p> Recipient " . $i;
                echo "<span id=\"" . $i . "\" class=\"color-preview\"></span></p>";
            }
        ?>
    </div>
</div>

<div class="row" style="display: flex; flex-direction: row; justify-content: center;">
    <div id="field-bank" class="column" style="display: none;">
        <div id="Signature-draggable" class="draggable resizable">
            <p>Sign here</p>
        </div>
        <div id="Initials-draggable" class="draggable">
            <input id="initials" class="x2-sign-input" type="text" minlength="1" maxlength="8" size="5" placeholder="Initials" readonly></input>
        </div>
        <div id="Formula-draggable" class="draggable">
            <input id="formula" class="x2-sign-input" type="text" minlength="1" size="15" placeholder="Formula" readonly></input>
        </div>
        <div id="Checkbox-draggable" class="draggable">
            <input id="checkbox" class="x2-sign-input" type="checkbox" disabled></input>
        </div>
        <div id="Text-draggable" class="draggable">
            <input id="text" class="x2-sign-input" type="text" size="15" placeholder="Text" readonly></input>
        </div>
        <div id="Name-draggable" class="draggable">
            <input id="name" class="x2-sign-input" type="text" minlength="1" size="15" placeholder="Name" readonly></input>
        </div>
        <div id="Email-draggable" class="draggable">
            <input id="email" class="x2-sign-input" type="email" placeholder="Email" readonly></input>
        </div>
        <div id="Title-draggable" class="draggable">
            <input id="title" class="x2-sign-input" type="text" minlength="1" placeholder="Title" readonly></input>
        </div>
        <div id="Date-draggable" class="draggable resizable">
            <i class="far fa-calendar-alt fa-lg"></i>
        </div>
    </div>

    <div id="document" class="column">
        <div class="row">
            <div class="x2-button-group" style="text-align: center;">
                <span id="prev" class="x2-button page-prev">Previous</span>
                <span id="next" class="x2-button page-next">Next</span>
                &nbsp; &nbsp;
                <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
            </div>
        </div>
        <div class="row x2-sign-template">
            <canvas id="pdf" class="x2-sign-doc"></canvas>
            <div id="pdf-dropzone"></div>
        </div>
    </div>
</div>
