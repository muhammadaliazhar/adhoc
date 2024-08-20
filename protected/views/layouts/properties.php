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
$cs->registerREMain();

// Import Bootstrap
Yii::app()->clientScript->registerCssFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
Yii::app()->clientScript->registerScriptFile('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js');
Yii::app()->clientScript->registerScriptFile('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js');

$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/ui-elements.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/font-awesome.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/all.min.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/fontAwesome/css/v4-shims.min.css');
$cs->registerCssFile(Yii::app()->theme->baseUrl.'/css/form.css?' . Yii::app()->params->buildDate, 'screen, projection');

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

// Append a slash to the external base URL if one is not already present
$absBaseUrl = Yii::app()->getExternalAbsoluteBaseUrl ();
if ($absBaseUrl[strlen($absBaseUrl)-1] !== '/')
    $absBaseUrl .= '/';

?>

<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo $this->pageTitle ?></title>
</head>
<body style="background-color:#EFF3F5;">	
    <script type="text/javascript"
        src="<?php echo Yii::app()->getBaseUrl().'/js/fontdetect.js'; ?>">
    </script>
    <script type="text/javascript"
        src="<?php echo Yii::app()->getBaseUrl().'/js/X2Identity.js'; ?>">
    </script>
    <script src="<?php echo $absBaseUrl ?>webTracker.php"></script>

	<div id='content' class="container-fluid">
        <div class="row no-gutters" style="height: 100%;">
            <div id="contentDiv" class="col-lg" style="min-width: 85%">
                <?php $user = User::getMe(); ?>
                <?php if (isset($user)) : ?>
                <nav id="x2re-nav" class="navbar navbar-dark bg-dark">
                    <a class="navbar-brand" href="<?php echo $this->createUrl ('/') ?>">
                    <?php $menuLogo = Media::getMenuLogo ();
                          if ($menuLogo &&
                              $menuLogo->fileName !== 'uploads/protected/logos/yourlogohere.png') {
                              echo CHtml::image(
                                  $menuLogo->getPublicUrl (),
                                  Yii::app()->settings->appName,
                                  array (
                                      'id' => 'your-logo',
                                      'class' => 'custom-logo',
                                      'width' => '330',
                                      'height' => '60',
                              )); 
                          } else {
                               echo X2Html::logo ('menu', array (
                                   'id' => 'your-logo',
                                   'class' => 'default-logo',
                                   'width' => '45',
                                   'height' => '45',
                               )); 
                          }
                    ?>
                    </a>
                    <?php $user = User::getMe(); ?>
                    <ul class="nav">
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle text-light" style="padding-right: 1rem;" data-toggle="dropdown"
                              aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-user text-light" style="padding-right: 0.5rem; font-size: 1rem;"><div class="text-light" style="font-size: 1rem; float:right; padding-left: 0.2rem;"><?php if(isset($user->userAlias)) echo $user->userAlias; else echo $user->username; ?></div></i>
                            </a>
                            <ul class="dropdown-menu dropdown-secondary">
                                <li class="dropdown-item" href="#">Switch User</li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
		        <?php echo $content; ?>
            </div>
        </div>
	</div>
</body>
</html>
