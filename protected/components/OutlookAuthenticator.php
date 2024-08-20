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
 * Wrapper class for interaction with Outlook's API and authentication methods.
 * This is designed to handle all user authentication and returning of Outlook API
 * Client classes in an easy to use manner. Much of the code is from Outlook's stock
 * PHP API examples, but it has been modified to be usable with our software and
 * as such some of the comments/classes are Outlook developers' not mine.
 */
class OutlookAuthenticator {

    /**
     * Client ID of the Outlook API Project
     * @var string
     */
    public $clientId = '';

    /**
     * Client secret of the Outlook API Project
     * @var string
     */
    public $clientSecret = '';

    /**
     * Redirect URI for the authentication request
     * @var string
     */
    public $redirectUri = '';

    /**
     * A list of scopes required by the Outlook API to use for Outlook Integration
     * within the software. This list defines the permissions that Outlook will ask
     * for when a user is authenticating with them and X2.
     * @var array
     */
    public $scopes = array(
        'openid',
        '20offline_access', // offline access
        'https%3A%2F%2Fgraph.microsoft.com', // Graph website
        '2Fcalendars.read', // Read Calendar
    );

    /**
     * An array of errors to be returned or displayed in case something goes wrong.
     * @var array
     */
    private $_errors;

    /**
     * Master control variable that prevents most methods being called unless
     * Outlook Integration is enabled in the admin settings.
     * @var boolean
     */
    private $_enabled;
    
        /**
     * Constructor that sets up the Authenticator with all the required data to
     * connect to Outlook properly.
     */
    public function __construct($scenario = null) {
        $this->_enabled = Yii::app()->settings->outlookIntegration; // Check if integration is enabled in the first place
        //get credentials id and secret
        $admin = Admin::model()->findByPk (1);
        $id = $admin->outlookCredentialsId;
        $credential = Credentials::model()->findByAttributes(array('id'=>$id));
        $auth_credential = $credential->auth;
        $client_id = $auth_credential->outlookId;
        if ($this->_enabled) {
            $this->clientId = $auth_credential->outlookId;
            $this->clientSecret = $auth_credential->outlookSecret;
            if (empty($this->redirectUri)) {
                $this->redirectUri = (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') .
                        $_SERVER['HTTP_HOST'] . Yii::app()->controller->createUrl('');
            }
        }
    }

     /* Retrieve the authorization URL.
     *
     * @param String $emailAddress User's e-mail address.
     * @param String $state State for the authorization URL.
     * @return String Authorization URL to redirect the user to.
     */
    public function getAuthorizationUrl() {
        //get credentials id and secret
        $admin = Admin::model()->findByPk (1);
        $id = $admin->outlookCredentialsId;
        $credential = Credentials::model()->findByAttributes(array('id'=>$id));
        $auth_credential = $credential->auth;
        $client_id = $auth_credential->outlookId;
        
        $url = "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id=". $client_id . "&redirect_uri=".urlencode("https://sydney.tworld.com/index.php/calendar/outlooksync")."&response_type=code&response_mode=query&scope=openid%20offline_access%20https%3A%2F%2Fgraph.microsoft.com%2Fcalendars.readwrite%20&state=12345";
        return $url;
    }
    
    public static function getAccessToken() {  
        $currentuser = Yii::app()->user->getName();
        $profile = Profile::model()->findByAttributes(array('username'=>$currentuser));
        $refresh = $profile->outlookRefreshToken; 
   
        //get credentials id and secret
        $admin = Admin::model()->findByPk (1);
        $id = $admin->outlookCredentialsId;
        $credential = Credentials::model()->findByAttributes(array('id'=>$id));
        $auth_credential = $credential->auth;
        $client_id = $auth_credential->outlookId;
        $client_secret = $auth_credential->outlookSecret;
        
        //every input needs to exist
        if($client_id == null || $client_secret == null || $refresh == null){
            return false;
        }
        
        $ch = curl_init();
        //create header and body for the POST request
        curl_setopt($ch, CURLOPT_URL,"https://login.microsoftonline.com/common/oauth2/v2.0/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array('refresh_token' => $refresh, 
                                   'grant_type' => 'refresh_token',
                                   'client_id' => $client_id,
                                   'client_secret' => $client_secret,
            )));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute url
        $server_output = curl_exec($ch);
        curl_close ($ch);
        
        $result = CJSON::decode($server_output);
        if(isset($result['error'])){
            throw new CHttpException(500, $result['error'] . "\n" . $result['error_description']);
        }
        $access_token = $result['access_token'];
        
        return $access_token;
    }

    public static function checkAccessToken() {
        $currentuser = Yii::app()->user->getName();
        $profile = Profile::model()->findByAttributes(array('username'=>$currentuser));
        $refresh = $profile->outlookRefreshToken;

        //get credentials id and secret
        $admin = Admin::model()->findByPk (1);
        $id = $admin->outlookCredentialsId;
        $credential = Credentials::model()->findByAttributes(array('id'=>$id));
        $auth_credential = $credential->auth;
        $client_id = $auth_credential->outlookId;
        $client_secret = $auth_credential->outlookSecret;

        //every input needs to exist
        if($client_id == null || $client_secret == null || $refresh == null){
            return false;
        }

        $ch = curl_init();
        //create header and body for the POST request
        curl_setopt($ch, CURLOPT_URL,"https://login.microsoftonline.com/common/oauth2/v2.0/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array('refresh_token' => $refresh,
                                   'grant_type' => 'refresh_token',
                                   'client_id' => $client_id,
                                   'client_secret' => $client_secret,
            )));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute url
        $server_output = curl_exec($ch);
        curl_close ($ch);

        $result = CJSON::decode($server_output);
        if(isset($result['error'])){
            return false;
        }

        return true;
    }
}

?>

