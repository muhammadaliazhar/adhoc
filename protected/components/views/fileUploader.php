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





$padding = $noPadding ? 'no-padding' : '';

if ($showButton): ?>
    <div class='file-uploader-buttons <?php echo $padding?>'>
        <span class='x2-button x2-blue show-button' id="<?php echo $this->id.'-button' ?>"> <?php echo $buttonText ?></span>
    </div>
<?php endif;

echo X2Html::openTag ('div', array (
    'class' => "file-uploader $class $padding",
    'id' => $this->id,
    'style' => $open || !$this->displayToggle ? '' : 'display:none',
));
?>
<?php if ($this->displayForm) : ?>
    <form id='options'>
        <span>
            <input type='checkbox' id='file-uploader-private' name='private' />
            <label for='file-uploader-private'><?php echo Yii::t('app', 'Private') ?></label>
        </span>
        <?php if ($this->googleDrive): ?>
            <span>
                <input type='checkbox' id='file-uploader-drive' name='drive' />
                <label for='file-uploade-rdrive'><?php echo Yii::t('app', 'Upload to Google Drive') ?></label>
            </span>
        <?php endif; ?>
    </form>
<?php endif; ?>

    <div  class='dropzone' action='<?php echo $this->url ?>'>
        <?php if($closeButton): ?>
            <div class='dz-close'>
                <?php echo X2Html::fa('times-circle') ?>
            </div>
        <?php endif ?>
        <div class='dz-message'>
            <h3><?php echo Yii::t('media', 'Drop files here to upload')?></h3>
            <div> <?php echo Yii::t('app', 'or')?> </div>
            <span class='x2-button blue' > <?php echo Yii::t('media', 'select files') ?></span>
        </div>
    </div>

<?php echo X2Html::closeTag ('div'); ?>
