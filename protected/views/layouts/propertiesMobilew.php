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
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->pageTitle ?></title>
</head>
<div id='sideBar' class="sidebar" style="z-index: 10;">
  <a id='show-left-menu-button' onclick="togSidebar()">
     <i class='fa fa-bars' style=''></i>
  </a>

  <a <?php if(Yii::app()->controller->module->getName() == 'sellers2')echo "class='active'"; ?> id='sellTab' href="<?php echo $absBaseUrl . 'index.php/sellers2'; ?>">Sellers</a>
  <a <?php if(Yii::app()->controller->module->getName() == 'x2Leads')echo "class='active'"; ?> id='conTab' href="<?php echo $absBaseUrl . 'index.php/x2Leads'; ?>">Contacts</a>
  <a <?php if(Yii::app()->controller->module->getName() == 'contacts')echo "class='active'"; ?> id='buyTab' href="<?php echo $absBaseUrl . 'index.php/contacts'; ?>">Buyers</a>
  <a <?php if(Yii::app()->controller->module->getName() == 'listings2')echo "class='active'"; ?> id='listTab' href="<?php echo $absBaseUrl . 'index.php/listings2'; ?>">Listings</a>
  <a <?php if(Yii::app()->controller->module->getName() == 'actions')echo "class='active'"; ?> id='actionTab' href="<?php echo $absBaseUrl . 'index.php/actions'; ?>">Actions</a>
  <a id='mobileTab' href="<?php echo $absBaseUrl . 'index.php/profile/mobileOff'; ?>">View Desktop Version</a>
</div>
<style>
/* The side navigation menu got example from https://www.w3schools.com/howto/tryit.asp?filename=tryhow_css_sidebar_responsive*/
.sidebar {
  margin: 0;
  padding: 0;
  width: 0px;
  background-color: #f1f1f1;
  position: fixed;
  height: 100%;
  overflow: auto;
}

/* Sidebar links */
.sidebar a {
  display: block;
  color: black;
  padding: 16px;
  text-decoration: none;
}

/* Active/current link */
.sidebar a.active {
  background-color: #2B3F58;
  color: white;
}

/* Links on mouse-over */
.sidebar a:hover:not(.active) {
  background-color: #555;
  color: white;
}

.logout-link {
    color: black;
}

.logout-link:hover {
    text-decoration: none;
}
</style>
<script>
function togSidebar(){
    if($("#sideBar").width() != "200")$("#sideBar").width('200px');
    else $("#sideBar").width('0px');

}


</script>
<body style="background-color:#EFF3F5;" style="margin-left: 200px; font-family: 'Open Sans'">    
    <script type="text/javascript"
        src="<?php echo Yii::app()->getBaseUrl().'/js/fontdetect.js'; ?>">
    </script>
    <script type="text/javascript"
        src="<?php echo Yii::app()->getBaseUrl().'/js/X2Identity.js'; ?>">
    </script>
    <script src="<?php echo $absBaseUrl ?>webTracker.php"></script>

    <div id='content' class="">
        <div class="row no-gutters" style="height: 100%;">
            <div id="contentDiv" class="col-lg" style="">
                <?php $user = User::getMe(); ?>
                <?php if (isset($user)) : ?>
                <nav id="x2re-nav" class="navbar" style="background: #203046;">
                <div id='show-left-menu-button' onclick="togSidebar()">
                    <i class='fa fa-bars' style='color: #f9f9f9 !important;'></i>
                </div>
                    <a class="navbar-brand" style="margin-right: 0rem;" href="<?php echo $this->createUrl ('/contacts') ?>">
                        <img src="https://sydney.tworld.com/tworld-logo-lg.png" height="100" width="250"></img>
                    </a>
                    <?php $user = User::getMe(); ?>
                    <ul class="nav">
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle text-light" style="padding-right: 1rem;" data-toggle="dropdown"
                              aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-user text-light" style="padding-right: 0.5rem; font-size: 1rem;"><div class="text-light" style="font-size: 1rem; float:right; padding-left: 0.2rem;"><?php ?></div></i>
                            </a>
                            <ul class="dropdown-menu dropdown-secondary">
                                <li class="dropdown-item"><a class="logout-link" href="/index.php/site/logout">Logout</a></li>
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


