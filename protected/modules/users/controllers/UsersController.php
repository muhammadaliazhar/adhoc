<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/

/**
 * @package application.modules.users.controllers
 */
class UsersController extends x2base {

    public $modelClass = 'User';

//    public function behaviors() {
//        return array_merge(parent::behaviors(), array(
//            'MobileControllerBehavior' => array(
//                'class' => 
//                    'application.modules.mobile.components.behaviors.MobileControllerBehavior'
//            ),
//        ));
//    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow',
                'actions'=>array('createAccount'),
                'users'=>array('*')
            ),
            array('allow',
                'actions'=>array('addTopContact','removeTopContact'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('view','index','create','update','admin','delete','search','inviteUsers', 'deactivateTwoFactor'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex(){
        $this->redirect('admin');
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $user=User::model()->findByPk($id);

        // Only load the Google Maps widget if we're on a User with an address
        if(isset($this->portlets['GoogleMaps']) && Yii::app()->settings->enableMaps) {
            $this->portlets['GoogleMaps']['params']['location'] = $user->address;
            $this->portlets['GoogleMaps']['params']['activityLocations'] = $user->getMapLocations();
            $this->portlets['GoogleMaps']['params']['defaultFilter'] = Locations::getDefaultUserTypes();
            $this->portlets['GoogleMaps']['params']['modelParam'] = 'userId';
        }
        $dataProvider=new CActiveDataProvider('Actions', array(
            'criteria'=>array(
                'order'=>'createDate DESC',
                'condition'=>'assignedTo=\''.$user->username.'\' OR completedBy = \''.$user->username.'\'',
        )));
        $actionHistory=$dataProvider->getData();
        $this->render('view',array(
            'model'=>$this->loadModel($id),
            'actionHistory'=>$actionHistory,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $admin = &Yii::app()->settings;
        $userCount = Yii::app()->db->createCommand(
                "SELECT COUNT(*) FROM x2_users;"
        )->queryAll();
        $userCountParsed = $userCount[0]["COUNT(*)"];
        if ($userCountParsed >= $admin->maxUserCount) {
            $this->render('userLimit',array());
        }
        
        $model=new User;
        $groups=array();
        foreach(Groups::model()->findAll() as $group){
            $groups[$group->id]=CHtml::encode($group->name);
        }
        $roles=array();
        foreach(Roles::model()->findAll() as $role){
            $roles[$role->id]=CHtml::encode($role->name);
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        $unhashedPassword = '';
        if(isset($_POST['User'])) {
            $model->attributes=$_POST['User'];
            //Temporarily maintain unhashed in case of validation error
            $unhashedPassword = $model->password;
            
            if ($model->validate (array('password')))
            
                $model->password = PasswordUtil::createHash($model->password);
            $model->userKey=substr(str_shuffle(str_repeat(
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
            $profile=new Profile;
            $profile->fullName=$model->firstName." ".$model->lastName;
            $profile->username=$model->username;
            $profile->allowPost=1;
            $profile->emailAddress=$model->emailAddress;
            $profile->status=$model->status;



//use the default seatings for grids 

	    $profile->widgets='1:1:1:1:1:1:1:1:1:1:1:1:1:1';
	    $profile->widgetOrder='FlowMacros:ChatBox:TimeZone:ActionMenu:SmallCalendar:OnlineUsers:GoogleMaps:TagCloud:MessageBox:QuickContact:NoteBox:MediaBox:DocViewer:TopSites';
	    $profile->widgetSettings='{"ChatBox":{"chatboxHeight":300,"chatmessageHeight":50},"NoteBox":{"noteboxHeight":200,"notemessageHeight":50},"DocViewer":{"docboxHeight":200},"TopSites":{"topsitesHeight":200,"urltitleHeight":10},"MediaBox":{"mediaBoxHeight":150,"hideUsers":[]},"TimeZone":{"clockType":"digital"},"SmallCalendar":{"justMe":"false"},"FilterControls":{"order":[]}}';
            $profile->gridviewSettings='{"listings2":{"gvCheckbox":28,"c_listing_number__c":76,"name":187,"c_client_status__c":69,"c_tom_marketing_status__c":107,"c_Seller":62,"c_deal_stage__c":66,"c_client_agreement_expires__c":100,"c_lease_expiration_date__c":66,"assignedTo":77,"c_listing_price__c":69,"c_total_sales__c":66,"c_seller_discretionary_earnings__c":66,"c_real_estate_included__c":66,"c_city__c":96},"services":{"gvCheckbox":30,"id":43,"impact":80,"status":233,"assignedTo":112,"lastUpdated":79,"updatedBy":111},"brokers":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"business":{"gvCheckbox":28,"name":255,"description":130,"assignedTo":103,"gvControls":71,"c_account__c":280},"sellers2":{"gvCheckbox":28,"name":99,"c_title":61,"c_Corporation_Name":65,"c_stage__c":61,"c_status__c":73,"c_Lead_Score":29,"c_leadsource":64,"lastUpdated":71,"assignedTo":61,"c_ttl_city__c":75,"c_email":161,"c_mobilephone":108,"c_phone":96},"x2leads":{"gvCheckbox":28,"name":132,"c_title":120,"c_company":131,"c_type__c":114,"c_contact_type__c":95,"leadSource":138,"assignedTo":101,"c_email":112,"c_phone":29,"c_mobilephone":76,"createDate":129},"inlinerelationshipsgrid":{"expandButton.":"12","name":"22%","relatedModelName":"18%","assignedTo":"18%","label":"18%","createDate":"15%","deletion.":70},"actionswidget":{"actionDescription":"52%","assignedTo":"34%","dueDate":166},"callswidget":{"actionDescription":"52%","assignedTo":"34%","duration":"106","createDate":"60"},"commentswidget":{"actionDescription":"52%","assignedTo":"34%","createDate":166},"emailswidget":{"actionDescription":"52%","assignedTo":"34%","createDate":166},"eventswidget":{"actionDescription":"52%","assignedTo":"34%","dueDate":"106","completeDate":"60"},"loggedtimewidget":{"actionDescription":"52%","assignedTo":"34%","timeSpent":"106","createDate":"60"},"quoteswidget":{"actionDescription":"52%","assignedTo":"34%","createDate":166},"webactivitywidget":{"actionDescription":"86%","createDate":166},"relateddocsgrid":{"expandButton.":"12","name":"22%"},"franchisees":{"name":255,"assignedTo":103,"c_franchisee_owner__c":207,"c_active__c":80,"c_state__c":80},"contacts":{"gvCheckbox":28,"name":105,"c_listinglookup__c":119,"c_business_categories__c":114,"c_status__c":44,"c_BuyerPipeline":63,"leadscore":63,"leadSource":112,"c_InitialInvestmentAvailable":63,"createDate":77,"city":63,"assignedTo":121,"email":186,"c_mobilephone":59,"phone":49},"savedreports":{"gvCheckbox":100,"name":227,"type":115,"createdBy":115,"ShareAgent":76,"ShareFranch":76,"createDate":113,"gvControls":115,"sharedTo":662,"assignedTo":67},"accounts":{"gvCheckbox":30,"name":184,"type":153,"annualRevenue":108,"phone":115,"lastUpdated":77,"assignedTo":99},"users-grid":{"username":157,"firstName":74,"lastName":74,"createDate":179,"login":74,"emailAddress":233,"":44},"dataroom":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"newwebleadsgridviewprofilewidget":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83},"contactsgridviewprofilewidget":{"gvCheckbox":28,"name":116,"email":156,"assignedTo":99,"createDate":169,"phone":98},"accountsgridviewprofilewidget":{"gvCheckbox":30,"name":184,"type":153,"phone":115},"profilesgridviewprofilewidget":{"isActive":65,"fullName":125,"lastLogin":80,"emailAddress":100},"summation-grid":[],"contactsgridviewprofilewidget59dfcfdd2fb39":{"gvCheckbox":28,"name":116,"email":156,"createDate":175,"assignedTo":100,"phone":98},"workflowindex":{"name":240,"isDefaultFor":100,"stages":100},"campaigns":{"gvCheckbox":28,"name":242,"listId":144,"subject":261,"launchDate":162,"active":34,"lastUpdated":113,"assignedTo":135},"managefields":{"modelName":105,"fieldName":237,"attributeLabel":246,"required":47,"type":67,"uniqueConstraint":37,"defaultValue":77},"employees":{"name":221,"c_franchisee__c":105,"c_cell_phone__c":119,"c_phone__c":109,"assignedTo":155,"c_active__c":70,"c_state__c":70,"c_agent_role__c":131},"opportunities":{"gvCheckbox":28,"name":212,"quoteAmount":85,"probability":67,"expectedCloseDate":115,"createDate":111,"lastActivity":86,"assignedTo":109,"c_listing_price__c":98,"c_purchase_price_c":76},"geographies":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"categories":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"offers":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"flow-grid":{"name":196,"description":299,"active":58,"triggerType":118,"modelClass":88,"createDate":148,"lastUpdated":148},"triggerlogsgridfloweditor":{"triggeredAt":200,"triggerLog":200,"delete":30},"subcategories":{"description":122,"name":246,"assignedTo":95,"c_category__c":246,"c_currencyisocode":80},"offices":{"name":257,"assignedTo":105,"c_state__c":80},"media-index":{"gvCheckbox":28,"fileName":355,"name":214,"associationType":72,"createDate":133,"uploadedBy":101,"filesize":62,"template":123},"profiles":{"fullName":125,"tagLine":165,"isActive":80},"actions":{"gvCheckbox":28,"subject":291,"actionDescription":420,"assignedTo":76,"complete":67,"completedBy":51,"createDate":144,"associationName":143,"completeDate":124,"dueDate":88,"lastUpdated":29,"type":29,"eventStatus":29,"eventSubtype":29},"tasks":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"accountsgridviewprofilewidget5a74cf98c58ba":{"gvCheckbox":30,"name":184,"type":153,"phone":115},"actionsgridviewprofilewidget5a74cf9f034a7":{"gvCheckbox":28,"actionDescription":545,"createDate":162,"complete":75,"dueDate":64,"associationName":152,"assignedTo":92,"completedBy":73,"lastUpdated":66},"documents":{"gvCheckbox":30,"name":257,"description":132,"gvControls":73},"templatesgridviewprofilewidget5ac3ec2f487a1":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"x2leadsgridviewprofilewidget5ac3ec43015f5":{"gvCheckbox":28,"name":161,"quoteAmount":92,"expectedCloseDate":122,"createDate":75,"lastActivity":76,"assignedTo":116,"probability":74},"listsgrid":{"name":180,"type":180,"assignedTo":180,"count":180,"gvControls":75},"contacts_list47":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"contacts_list34":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"contacts_list48":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"campaignprogressgrid":{"name":366,"email":125,"sent":84,"opened":130,"unsubscribed":52,"doNotEmail":41,"openedAt":29,"clicked":41},"contacts_list59":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"templatesgridviewprofilewidget5ad8f4cfc24bf":{"gvCheckbox":28,"name":155,"c_phone__c":100,"c_cell_phone__c":73,"description":125,"assignedTo":98,"gvControls":66,"c_state__c":73,"c_active__c":73},"x2leadsgridviewprofilewidget5ad8f6935a0f2":{"gvCheckbox":30,"name":164,"quoteAmount":95,"probability":77,"expectedCloseDate":125,"createDate":78,"lastActivity":79,"assignedTo":119},"contacts_list67":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"contacts_list71":{"gvCheckbox":28,"createDate":161,"name":114,"email":154,"leadSource":72,"phone":88,"c_mobilephone":69,"c_listinglookup__c":69,"c_listing_number_lookup":69,"assignedTo":108,"lastActivity":67,"gvControls":62},"contacts_list82":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"fingerprint":{"gvCheckbox":30,"fingerprint":160,"userAgent":140,"language":50,"plugins":100,"javaEnabled":60,"cookiesEnabled":60,"screenRes":80,"timezone":140,"anonymous":80,"createDate":80},"contacts_list99":{"gvCheckbox":30,"name":125,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"counties":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"countries":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"libraries":{"gvCheckbox":28,"name":254,"description":129,"assignedTo":102,"gvControls":70},"inquiries":{"gvCheckbox":28,"name":255,"description":130,"gvControls":71,"c_createddate":78,"c_owner":171},"docusign_fields":{"name":241,"c_fieldName":204,"description":122,"c_template":186,"assignedTo":95,"c_offset_right":70,"c_anchor_text":186,"c_offset_top":74,"c_type":74,"gvCheckbox":29},"sellerquestionairre":{"name":253,"assignedTo":103,"createDate":162,"gvCheckbox":29},"contactsgridviewprofilewidget5bb3a71c07452":{"gvCheckbox":28,"name":152,"email":158,"assignedTo":77,"phone":100,"c_listinglookup__c":161,"c_listing_number_lookup":77},"actionsgridviewprofilewidget5bb3a944cc5f9":{"gvCheckbox":28,"actionDescription":527,"associationName":161,"assignedTo":101,"completedBy":82,"createDate":75,"dueDate":73,"lastUpdated":75,"complete":78},"templatesgridviewprofilewidget5bb3aa088799a":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"actionsgridviewprofilewidget5bb3aa803486f":{"gvCheckbox":28,"actionDescription":493,"associationName":161,"assignedTo":101,"completedBy":82,"createDate":75,"dueDate":73,"lastUpdated":75,"complete":78},"templatesgridviewprofilewidget5bb3abae1ac60":{"gvCheckbox":28,"createDate":107,"name":252,"assignedTo":100,"c_createdbyid":75,"description":127,"gvControls":68,"c_phone":75},"contactsgridviewprofilewidget5bb3abe88895f":{"gvCheckbox":30,"name":125,"email":165,"phone":107},"actionsgridviewprofilewidget5bb3ac89021fd":{"gvCheckbox":28,"actionDescription":260,"associationName":163,"assignedTo":103,"completedBy":84,"createDate":77,"dueDate":75,"lastUpdated":77,"complete":80},"actionsgridviewprofilewidget5bb3adcc7140a":{"gvCheckbox":28,"actionDescription":268,"associationName":163,"assignedTo":103,"completedBy":84,"createDate":77,"dueDate":75,"lastUpdated":77,"complete":80},"actionsgridviewprofilewidget5bb3afad19f2d":{"gvCheckbox":28,"actionDescription":597,"createDate":186,"complete":78},"contacts_list153":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"actionsgridviewprofilewidget5bc638b333c26":{"gvCheckbox":28,"subject":73,"actionDescription":587,"assignedTo":94,"completedBy":75,"createDate":91,"dueDate":66,"lastUpdated":68,"complete":69},"docusign_templates":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"tmp_denver":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"contacts_list207":{"gvCheckbox":30,"name":125,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"contacts_list209":{"gvCheckbox":30,"name":125,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"accountsgridviewprofilewidget5c474734ab420":{"gvCheckbox":30,"name":184,"type":153,"phone":115},"actionsgridviewprofilewidget5c47474f222c9":{"gvCheckbox":28,"actionDescription":603,"complete":75,"createDate":74,"dueDate":72,"lastUpdated":74},"accountsgridviewprofilewidget5c7441e1ba630":{"gvCheckbox":30,"name":184,"type":153,"phone":115},"actionsgridviewprofilewidget5c74b0a91dc43":{"gvCheckbox":28,"actionDescription":324,"associationName":163,"assignedTo":103,"completedBy":84,"createDate":77,"dueDate":75,"lastUpdated":77},"actionsgridviewprofilewidget5c7558498ed90":{"gvCheckbox":77,"actionDescription":446,"createDate":86,"assignedTo":86,"complete":73,"completedBy":86,"associationName":86},"actionsgridviewprofilewidget5c76ada9ee5b1":{"gvCheckbox":30,"actionDescription":140,"associationName":165,"assignedTo":105,"completedBy":86,"createDate":79,"dueDate":77,"lastUpdated":79},"actionsgridviewprofilewidget5c77f315ba8ae":{"gvCheckbox":30,"actionDescription":140,"associationName":165,"assignedTo":105,"completedBy":86,"createDate":79,"dueDate":77,"lastUpdated":79},"triggerlogsgridtriggerlogs":{"flowName":250,"triggeredAt":200,"triggerLog":200,"delete":30},"sellerquestionnairelist2":{"name":253,"description":130,"assignedTo":103,"createDate":99,"gvCheckbox":54},"contacts_list2326":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"actionsgridviewprofilewidget5ca78c9d0ef9a":{"gvCheckbox":30,"actionDescription":140,"associationName":165,"assignedTo":105,"completedBy":86,"createDate":79,"dueDate":77,"lastUpdated":79},"contacts_list266":{"gvCheckbox":30,"name":125,"email":165,"leadSource":83,"leadstatus":91,"phone":107,"lastActivity":78,"gvControls":73},"templatesgridviewprofilewidget5ce457d768c2d":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"opportunitiesgridviewprofilewidget5ce45b004b068":{"gvCheckbox":30,"name":164,"quoteAmount":95,"probability":77,"expectedCloseDate":125,"createDate":78,"lastActivity":79,"assignedTo":119},"servicesgridviewprofilewidget5ce45c209f94e":{"gvCheckbox":30,"id":43,"impact":80,"status":233,"assignedTo":112,"lastUpdated":79,"updatedBy":111},"actionsgridviewprofilewidget5cf1949a6f0da":{"gvCheckbox":30,"actionDescription":140,"associationName":165,"assignedTo":105,"completedBy":86,"createDate":79,"dueDate":77,"lastUpdated":79},"x2leadsgridviewprofilewidget5cf194bd36e9d":{"gvCheckbox":30,"name":164,"quoteAmount":95,"probability":77,"expectedCloseDate":125,"createDate":78,"lastActivity":79,"assignedTo":119},"templatesgridviewprofilewidget5cf194f3431b4":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73},"templatesgridviewprofilewidget5cf1952264b01":{"gvCheckbox":30,"name":257,"description":132,"assignedTo":105,"gvControls":73}}';
           $profile->generalGridViewSettings='{"NewWebLeadsGridViewProfileWidget_":{"sort":"createDate.desc"},"listings2\/listings2\/view\/48290Changelog":{"filters":[]},"listings2\/listings2\/view\/48355Changelog":{"filters":[]},"contacts\/contacts\/view\/342580Changelog":{"filters":[]},"listings2\/listings2\/view\/48208Changelog":{"filters":[]},"TemplatesGridViewProfileWidget_5ad8f4cfc24bf":{"filters":{"tags":[],"c_active__c":"","name":"","assignedTo":"","c_cell_phone__c":"","c_phone__c":"","c_state__c":"","description":""}},"ActionsGridViewProfileWidget_5a74cf9f034a7":{"filters":{"tags":[],"createDate":"","lastUpdated":"","assignedTo":"","actionDescription":"","associationName":"","dueDate":"","complete":"no","completedBy":"","notificationUsers":"assigned"}},"studio\/flowIndexX2Flow":{"filters":{"name":"","createDate":"","lastUpdated":"","triggerType":"","active":"","modelClass":"","description":"client status"}},"listings2\/listings2\/view\/49574Changelog":{"filters":[]},"ActionsGridViewProfileWidget_5bb3afad19f2d":{"filters":{"tags":[],"createDate":"","actionDescription":"","complete":"no","notificationUsers":"assigned"}},"TemplatesGridViewProfileWidget_5bb3abae1ac60":{"filters":{"tags":[],"name":"","createDate":"","assignedTo":"","c_createdbyid":"","c_phone":"","description":""}},"ActionsGridViewProfileWidget_5bc638b333c26":{"filters":{"tags":[],"createDate":"","lastUpdated":"","actionDescription":"","dueDate":"","complete":"no","completedBy":"","notificationUsers":"assigned"}},"listings2\/listings2\/view\/51602Changelog":{"filters":[]},"ActionsGridViewProfileWidget_5c47474f222c9":{"filters":{"tags":[],"createDate":"","lastUpdated":"","actionDescription":"","dueDate":"","complete":"no","notificationUsers":"assigned"}},"ActionsGridViewProfileWidget_5c7558498ed90":{"filters":{"tags":[],"assignedTo":"","actionDescription":"","associationName":"","complete":"no","completedBy":"","notificationUsers":"assigned"}}}';
            $profile->layout='{"left":{"ActionMenu":{"title":"Actions","minimize":false},"ProfileInfo":{"title":"Profile Info","minimize":false},"RecentItems":{"title":"Recently Viewed","minimize":false}},"right":{"ActionMenu":{"title":"My Actions","minimize":false},"TimeZone":{"title":"Clock","minimize":false},"SmallCalendar":{"title":"Calendar","minimize":false}},"hiddenRight":{"TagCloud":{"title":"Tag Cloud","minimize":false},"MediaBox":{"title":"Files","minimize":false},"TopSites":{"title":"Top Sites","minimize":false},"MessageBox":{"title":"Message Board","minimize":false},"QuickContact":{"title":"Quick Contact","minimize":false},"DocViewer":{"title":"Doc Viewer","minimize":false},"GoogleMaps":{"title":"Google Map","minimize":false},"NoteBox":{"title":"Note Pad","minimize":false},"OnlineUsers":{"title":"Active Users","minimize":false},"ChatBox":{"title":"Activity Feed","minimize":false}}}';
	  
	    $profile->miscLayoutSettings = '{"themeSectionExpanded":true,"unhideTagsSectionExpanded":true,"x2flowShowLabels":true,"profileInfoIsMinimized":false,"perStageWorkflowView":false,"columnWidth":"40.36363636363636","recordViewColumnWidth":"63.51451973194341","enableTransactionalView":"0","enableJournalView":"1","viewModeActionSubmenuOpen":true}'; 




             
            // set a default theme if there is one
            $admin = Yii::app()->settings;
            if ($admin->defaultTheme) {
                $profile->theme = $profile->getDefaultTheme ();
            }
             

            if($model->save()){
                $calendar = new X2Calendar();
                $calendar->createdBy = $model->username;
                $calendar->updatedBy = $model->username;
                $calendar->createDate = time();
                $calendar->lastUpdated = time();
                $calendar->name = $profile->fullName."'s Calendar";
                $calendar->save();
                $profile->id=$model->id;
                $profile->defaultCalendar = $calendar->id;
                $profile->save();
                if(isset($_POST['roles'])){
                    $roles=$_POST['roles'];
                    foreach($roles as $role){
                        $link=new RoleToUser;
                        $link->roleId=$role;
                        $link->userId=$model->id;
                        $link->type="user";
                        $link->save();
                    }
                }
                if(isset($_POST['groups'])){
                    $groups=$_POST['groups'];
                    foreach($groups as $group){
                        $link=new GroupToUser;
                        $link->groupId=$group;
                        $link->userId=$model->id;
                        $link->username=$model->username;
                        $link->save();
                    }
                }



            //list of widgits
            $ListWidgits = array("ActionsGridViewProfileWidget","Sellers2::TemplatesGridViewProfileWidget","Contacts::TemplatesGridViewProfileWidget","ContactsGridViewProfileWidget"
                                ,"Libraries::TemplatesGridViewProfileWidget","OpportunitiesGridViewProfileWidget");


            //now go through and add each widgit to front page
            foreach($ListWidgits as $WIG){
                        $widgetClass = $WIG;
                        $widgetSettings = array ();
                        if (preg_match ('/::/', $widgetClass)) {
                                // Custom module summary widget. extract model name
                                $widgetSettings['modelType'] = preg_replace ('/::.*$/', '', $widgetClass);
                                $widgetSettings['label'] = Modules::displayName(true,$widgetSettings['modelType']) . ' Summary';
                                $widgetClass = preg_replace ('/^.*::/', '', $widgetClass);
                                if (!class_exists ($widgetSettings['modelType'])) {
                                    echo 'false';
                                }
                        }
                        $widgetLayoutName = "profile";
                        SortableWidget::createSortableWidget (
                        $profile, $widgetClass, $widgetLayoutName, $widgetSettings);

            }

            /**
                * Profile Save Layout (Groups) BEGIN
                * 1. once  new user is created with a group, they will inherit the layout
                * Related code area: protected/controllers/ProfileController.php (actionSaveGroupLayout())
                * date: September 4th, 2019
                * writer: Justin Toyomitsu
                */
                if(isset($_POST['groups']) && count($_POST['groups']) > 0){
                     if(isset($profile)){
			$group = Groups::model()->findByPk($_POST['groups'][0]); //get first group for layout
			if(isset($group)){
                            $profile->setProfileWidgetLayout(CJSON::decode($group->layout));
			    $profile->currentLayout = $_POST['groups'][0];
			}else{
			    $profile->currentLayout = 'personal';
			}
			$profile->personalLayout = '';
                        $profile->save();
                     }
                }
               /**
                * Profile Save Layout (Groups) END
                */


                $this->redirect(array('view','id'=>$model->id));
            }
        }
        $model->password = $unhashedPassword;

        $this->render('create',array(
            'model'=>$model,
            'groups'=>$groups,
            'roles'=>$roles,
            'selectedGroups'=>array(),
            'selectedRoles'=>array(),
        ));
    }

    public function actionCreateAccount(){
        Yii::import('application.components.ThemeGenerator.LoginThemeHelper');
        $this->layout='//layouts/login';
        if(isset($_GET['key'])){
            $key=$_GET['key'];
            $user=User::model()->findByAttributes(array('inviteKey'=>$key));
            if(isset($user)){
                $user->setScenario('insert');
                if($key==$user->inviteKey){
                    if(isset($_POST['User'])) {
                        $model=$user;
                        $model->attributes=$_POST['User'];
                        $model->status=1;
                        //$this->updateChangelog($model);
                        
                        if ($model->validate (array('password')))
                        
                            $model->password = PasswordUtil::createHash($model->password);
                        $model->userKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
                        $profile=new Profile;
                        $profile->fullName=$model->firstName." ".$model->lastName;
                        $profile->username=$model->username;
                        $profile->allowPost=1;
                        $profile->emailAddress=$model->emailAddress;
                        $profile->status=$model->status;

                        if($model->save()){
                            $model->inviteKey=null;
                            $model->temporary=0;
                            $model->save();
                            $profile->id=$model->id;
                            $profile->save();
                            $this->redirect(array('/site/login'));
                        }
                    }
                    $this->render('createAccount',array(
                        'user'=>$user,
                    ));
                }else{
                    $this->redirect($this->createUrl('/site/login'));
                }
            }else{
                $this->redirect($this->createUrl('/site/login'));
            }
        }else{
            $this->redirect($this->createUrl('/site/login'));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model=$this->loadModel($id);
        $groups=array();
        foreach(Groups::model()->findAll() as $group){
            $groups[$group->id]=CHtml::encode($group->name);
        }
        $selectedGroups=array();
        foreach(GroupToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
            $selectedGroups[]=$link->groupId;
        }
        $roles=array();
        foreach(Roles::model()->findAll() as $role){
            $roles[$role->id]=CHtml::encode($role->name);
        }
        $selectedRoles=array();
        foreach(RoleToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
            $selectedRoles[]=$link->roleId;
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (!isset($model->userAlias))
            $model->userAlias = $model->username;

        if(isset($_POST['User'])) {
            $old=$model->attributes;
            $temp=$model->password;
            $model->attributes=$_POST['User'];

            if($model->password!="") {
                
                if ($model->validate (array('password')))
                
                    $model->password = PasswordUtil::createHash($model->password);
            } else {
                $model->password=$temp;
            }
            if(empty($model->userKey)){
                $model->userKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
            }
            if($model->save()){
                $profile = $model->profile;
                if(!empty($profile)) {
                    $profile->emailAddress = $model->emailAddress;
                    $profile->fullName = $model->firstName.' '.$model->lastName;
                    $profile->save();
                }
                if($old['username']!=$model->username){
                    $fieldRecords=Fields::model()->findAllByAttributes(array('fieldName'=>'assignedTo'));
                    $modelList=array();
                    foreach($fieldRecords as $record){
                        $modelList[$record->modelName]=$record->linkType;
                    }
                    foreach($modelList as $modelName=>$type){
                        if($modelName=='Quotes')
                            $modelName="Quote";
                        if($modelName=='Products')
                            $modelName='Product';
                        if(empty($type)){
                            $list=X2Model::model($modelName)->findAllByAttributes(array('assignedTo'=>$old['username']));
                            foreach($list as $item){
                                $item->assignedTo=$model->username;
                                $item->save();
                            }
                        }else{
                            $list=X2Model::model($modelName)->findAllBySql(
                                    "SELECT * FROM ".X2Model::model($modelName)->tableName()
                                    ." WHERE assignedTo LIKE '%".$old['username']."%'");
                            foreach($list as $item){
                                $assignedTo=explode(", ",$item->assignedTo);
                                $key=array_search($old['username'],$assignedTo);
                                if($key>=0){
                                    $assignedTo[$key]=$model->username;
                                }
                                $item->assignedTo=implode(", ",$assignedTo);
                                $item->save();
                            }
                        }
                    }

                    $profile=Profile::model()->findByAttributes(array('username'=>$old['username']));
                    if(isset($profile)){
                        $profile->username=$model->username;
                        $profile->save();
                    }

                }
                foreach(RoleToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
                    $link->delete();
                }
                foreach(GroupToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
                    $link->delete();
                }
                if(isset($_POST['roles'])){
                    $roles=$_POST['roles'];
                    foreach($roles as $role){
                        $link=new RoleToUser;
                        $link->roleId=$role;
                        $link->type="user";
                        $link->userId=$model->id;
                        $link->save();
                    }
                }
                if(isset($_POST['groups'])){
                    $groups=$_POST['groups'];
                    foreach($groups as $group){
                        $link=new GroupToUser;
                        $link->groupId=$group;
                        $link->userId=$model->id;
                        $link->username=$model->username;
                        $link->save();
                    }
                }
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $this->render('update',array(
            'model'=>$model,
            'groups'=>$groups,
            'roles'=>$roles,
            'selectedGroups'=>$selectedGroups,
            'selectedRoles'=>$selectedRoles,
        ));
    }

    public function actionInviteUsers(){

        if(isset($_POST['emails'])){
            $list=$_POST['emails'];

            $body="Hello,

You are receiving this email because your X2Engine administrator has invited you to create an account.
Please click on the link below to create an account at X2Engine!

";

            $subject="Create Your X2Engine User Account";
            $list=trim($list);
            $emails=explode(',',$list);
            foreach($emails as &$email){
                $key=substr(str_shuffle(str_repeat(
                    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',16)),0, 16);
                $user=new User('invite');
                $email=trim($email);
                $user->inviteKey=$key;
                $user->temporary=1;
                $user->emailAddress=$email;
                $user->status=0;
                $userList=User::model()->findAllByAttributes(
                    array('emailAddress'=>$email,'temporary'=>1));
                foreach($userList as $userRecord){
                    if(isset($userRecord)){
                        $userRecord->delete();
                    }
                }
                $user->save();
                $link=CHtml::link(
                    'Create Account',
                    (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . 
                    $this->createUrl('/users/users/createAccount',array('key'=>$key)));
                $mail=new InlineEmail;
                $mail->to=$email;
                // Get email password
                $cred = Credentials::model()->getDefaultUserAccount(
                    Credentials::$sysUseId['systemResponseEmail'],'email');
                if($cred==Credentials::LEGACY_ID)
                    $cred = Credentials::model()->getDefaultUserAccount(
                        Yii::app()->user->id,'email');
                if($cred != Credentials::LEGACY_ID)
                    $mail->credId = $cred;
                $mail->subject=$subject;
                $mail->message=$body."<br><br>".$link;
                $mail->contactFlag=false;
                if($mail->prepareBody()){
                    $mail->deliver();
                }else{
                }
            }
            $this->redirect('admin');
        }

        $this->render('inviteUsers');
    }

    public function actionDeleteTemporary(){
        $deleted=User::model()->deleteAllByAttributes(array('temporary'=>1));
        $this->redirect('admin');
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model=new User('search');
        $this->render('admin',array('model'=>$model,'count'=>User::model()->countByAttributes(array('temporary'=>1))));
    }

    public function actionDelete($id) {
        if($id != 1){
            $model=$this->loadModel($id);
            if(Yii::app()->request->isPostRequest) {
                $model->delete();
            } else {
                throw new CHttpException(
                    400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
            }
            /* if AJAX request (triggered by deletion via admin grid view), we should not redirect 
            the browser */
            if(!isset($_GET['ajax'])) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
        }else{
            throw new CHttpException(
                400,Yii::t('app','Cannot delete admin user.  Please do not repeat this request.'));
        }
    }

    public function actionAddTopContact($recordId, $modelClass) {
        Yii::import('application.components.leftWidget.TopContacts');
        $model = $this->getModelFromTypeAndId ($modelClass, $recordId, false);
        if (TopContacts::addBookmark ($model))
            $this->renderTopContacts();
    }

    public function actionRemoveTopContact($recordId, $modelClass) {
        Yii::import('application.components.leftWidget.TopContacts');
        $model = $this->getModelFromTypeAndId ($modelClass, $recordId, false);
        if (TopContacts::removeBookmark ($model))
            $this->renderTopContacts();
    }
    
    public function actionUserMap(){
        if (!Yii::app()->settings->googleIntegration) {
            throw new CHttpException(403, 'Please enable Google Integration to use this page.');
        }
        $users = User::getUserIds();
        unset($users['']);
        $selectedUsers = array_keys($users);
        $filterParams = filter_input(INPUT_POST,'params',FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
        $params = array();
        if(isset($filterParams['users'])){
            $selectedUsers = $filterParams['users'];
            $userParams = AuxLib::bindArray($selectedUsers);
            $userList = AuxLib::arrToStrList($userParams);
        }
        $time = isset($filterParams['timestamp'])?$filterParams['timestamp']:Formatter::formatDateTime(time());
        $locations = Yii::app()->db->createCommand(
                "SELECT lat, lon AS lng, recordId, type, comment AS info, createDate AS time"
                . " FROM ("
                ."SELECT * FROM x2_locations"
                ." WHERE recordType = 'User'"
                .(isset($filterParams['users'])?" AND recordId IN ".$userList:'')
                ." AND createDate < :time"
                ." ORDER BY createDate DESC"
                .") AS tmp GROUP BY recordId"
        )->queryAll(true, array(':time'=>strtotime($time)));
        if(!empty($locations)){
            $center = $locations[0];
        } else {
            $center = array('lat' => 0, 'lng' => 0);;
        }
        $types = Locations::getLocationTypes();
        foreach($locations as &$location){
            $location['time'] = Formatter::formatLongDateTime($location['time']);
            if(array_key_exists($location['type'],$types)){
                $location['type'] = $types[$location['type']];
            }
        }
        $this->render('userMap',array(
            'users' => $users,
            'selectedUsers'=>$selectedUsers,
            'timestamp'=>$time,
            'center'=>json_encode($center),
            'locations'=>$locations,
        ));
    }

    public function actionDeactivateTwoFactor($id){
        if (!Yii::app()->request->isPostRequest) $this->denied();
        $model = Profile::model()->findByPk($id);
        if ($model) {
            $model->enableTwoFactor = 0;
            $model->update(array('enableTwoFactor'));
        }
    }

    private function renderTopContacts() {
        $this->renderPartial('application.components.leftWidget.views.topContacts',array(
            'bookmarkRecords'=>User::getTopContacts(),
            //'viewId'=>$viewId
        ));
    }

    /**
     * Create a menu for Users
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Users = Modules::displayName();
        $User = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'feed', 'admin', 'create', 'invite', 'view', 'profile', 'edit', 'delete',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'feed',
                'label'=>Yii::t('profile','Social Feed'),
                'url'=>array('/profile/index')
            ),
            array(
                'name'=>'admin',
                'label' => Yii::t('users', 'Manage {users}', array(
                    '{users}' => $Users,
                )),
                'url'=>array('admin')
            ),
            array(
                'name'=>'map',
                'label' => Yii::t('users', 'View {users} Map', array(
                    '{users}' => $Users,
                )),
                'url'=>array('userMap'),
                'visible' => (bool) Yii::app()->settings->enableMaps,
            ),
            array(
                'name'=>'create',
                'label' => Yii::t('users', 'Create {user}', array(
                    '{user}' => $User,
                )),
                'url' => array('create')
            ),
            array(
                'name'=>'invite',
                'label' => Yii::t('users', 'Invite {users}', array(
                    '{users}' => $Users,
                )),
                'url' => array('inviteUsers')
            ),
            array(
                'name'=>'view',
                'label'=>Yii::t('users','View {user}', array(
                    '{user}' => $User,
                )),
                'url'=>array('view', 'id'=>$modelId)
            ),
            array(
                'name'=>'profile',
                'label'=>Yii::t('profile','View Profile'),
                'url'=>array('/profile/view','id'=>$modelId)
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('users','Update {user}', array(
                    '{user}' => $User,
                )),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'login',
                'label'=>Yii::t('users','Login as {user}', array(
                    '{user}' => $User,
                )),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('/site/login', 'id'=>$modelId, 'loginAsUserViaAdmin'=>$modelId))
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('users','Delete {user}', array(
                    '{user}' => $User,
                )),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }


}
