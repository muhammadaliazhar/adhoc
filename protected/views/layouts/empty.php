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





$cs = Yii::app()->clientScript;
$cs->registerCoreScript('jquery');
$cs->registerCoreScript('jquery.migrate');
$cs->registerCoreScript('jquery.ui');
$cs->registerPackages($cs->getDefaultPackages());

$cs->registerScriptFile(Yii::app()->baseUrl.'/js/portal.js', CClientScript::POS_END);
$cs->registerCssFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');

$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/ui-elements.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/font-awesome.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/all.min.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/v4-shims.min.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/form.css?' . Yii::app()->params->buildDate, 'screen, projection');

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

?>

<html>
<head>
	<meta charset="UTF-8">
    <link rel="icon" href="<?php echo Yii::app()->getFavIconUrl (); ?>" type="image/x-icon">
    <link rel="shortcut-icon" href="<?php echo Yii::app()->getFavIconUrl (); ?>" type="image/x-icon">
	<title><?php echo $this->pageTitle ?></title>
</head>
<body style="background-color:#BBB;">
    
    <div id="flexible-content">
        <div id="content-container">
            <div id="content">
                <!-- content -->
                <?php echo $content; ?>
                <div class='clear'></div>
            </div>
        </div>
    </div>
</body>
</html>
