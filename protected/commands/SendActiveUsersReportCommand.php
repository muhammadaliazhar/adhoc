<?php


/**
 * A test area for executing experimental PHP code inside of a Yii run environment.
 *
 * @package application.commands
 */
class SendActiveUsersReportCommand extends CConsoleCommand {

    public function run($args)
    {
        $customDir = str_replace('/protected','/custom/protected',Yii::app()->basePath);
        $controllerFile = $customDir.'/controllers/SiteController.php';
        require $controllerFile;
        $site = new SiteController(array());
        $site->actionSendActiveUsersReport();
    }
}
?>