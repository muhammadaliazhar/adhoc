<?php

/* * *********************************************************************************
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
 * ******************************************************************************** */

/**
 * Widget class for the relationships form.
 *
 * Relationships lists the relationships a model has with other models,
 * and provides a way to add existing models to the models relationships.
 *
 * @package application.components.sortableWidget
 */
class RelatedDocsWidget extends GridViewWidget {

    public $model;
    public $viewFile = '_relatedDocsWidget';
    public $template = '
            <div class="submenu-title-bar widget-title-bar">
                <div class="widget-title">
                    Docusign Docs
                </div>
                {titleBarButtons}{closeButton}{minimizeButton}{settingsMenu}
            </div>
            {widgetContents}
    ';

    /**
     * Used to prepopulate create relationship forms
     * @var array (<model class> => <array of default values indexed by attr name>)
     */
    public $defaultsByRelatedModelType = array();
    protected $compactResultsPerPage = true;
    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure() {
        if (!isset(self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge(
                    parent::getJSONPropertiesStructure(), array(
                'label' => 'Docs',
                'hidden' => false,
                'resultsPerPage' => 10,
                'showHeader' => false,
                'displayMode' => 'grid',
                'height' => '200',
                'hideFullHeader' => true,
            ));
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getDataProvider() {
        $model = $this->model;

        $envelopeArray = array();

        $sqlShare = "";
        //set up SQL for shareTo permissions
        if(Yii::app()->params->isAdmin){
                $sqlShare = " (TRUE) ";
        }elseif(Groups::userHasRole(User::getMe()->id, 'Franchise')){
                $usersGroupIds = Groups::model()->getUserGroups(Yii::app()->getSuId());
                $sqlShare = " (c_owner = '" . User::getMe()->username . "' ";

                if (!empty($usersGroupIds)) {

                        $generationalGroupmates = Groups::model()->getGenerationGroupmates($usersGroupIds, array(), true);

                        foreach($generationalGroupmates as $mate){
                                $sqlShare .= " OR c_owner = '" . $mate . "' " ;

                        }
                }
                $sqlShare .= ") ";


        }else{
                $sqlShare = " (c_owner = '" . User::getMe()->username . "') ";
        }

        if (get_class($model) === 'Listings2') {

            $buyers = Contacts::model()->findAllByAttributes(array('c_listinglookup__c' => $model->nameId));
            foreach ($buyers as $buyer) {
                $envelopeArray = array_merge($envelopeArray, Docusign_status::model()->findAllBySql("select * from x2_docusign_status where c_recordId = " .  $buyer->id . " AND c_recordType = '" .  get_class($buyer) . "' AND " . $sqlShare));
            }
        } else {
            $envelopeArray = Docusign_status::model()->findAllBySql("select * from x2_docusign_status where c_recordId = " .  $model->id . " AND c_recordType = '" .  get_class($model) . "' AND " . $sqlShare);
        }


        $mediaList = array();

        if (!empty($envelopeArray)) {
            foreach ($envelopeArray as $envelope) {
                $media = Media::model()->findByPk($envelope->c_documentId);

                if (isset($media) && !empty($media))
                    $mediaList[] = $media;
            }
        }

        $envelopeArray = array_reverse($envelopeArray);

        $dataProvider = new CArrayDataProvider($envelopeArray, array(
            'id' => 'related-docs-gridview',
            'pagination' => array('pageSize' => $this->getWidgetProperty('resultsPerPage'))
        ));

        return $dataProvider;
    }

    public function getViewFileParams() {
        if (!isset($this->_viewFileParams)) {
            $linkableModels = Relationships::getRelationshipTypeOptions();
            asort($linkableModels);

            if (!Yii::app()->user->checkAccess('MarketingAdminAccess')) {
                unset($linkableModels['AnonContact']);
            }

            // used to instantiate html dropdown
            $linkableModelsOptions = $linkableModels;

            $hasUpdatePermissions = $this->checkModuleUpdatePermissions();

            $this->_viewFileParams = array_merge(
                    parent::getViewFileParams(), array(
                'model' => $this->model,
                'modelName' => get_class($this->model),
                'linkableModelsOptions' => $linkableModelsOptions,
                'hasUpdatePermissions' => $hasUpdatePermissions,
                'displayMode' => $this->getWidgetProperty('displayMode'),
                'height' => $this->getWidgetProperty('height'),
            ));
        }
        return $this->_viewFileParams;
    }

    protected function getSettingsMenuContentEntries() {
        return
                '<li class="expand-detail-views">' .
                X2Html::fa('fa-toggle-down') .
                Yii::t('profile', 'Toggle Detail Views') .
                '</li>' .
                parent::getSettingsMenuContentEntries();
    }

    private $_moduleUpdatePermissions;

    private function checkModuleUpdatePermissions() {
        if (!isset($this->_moduleUpdatePermissions)) {
            $this->_moduleUpdatePermissions = Yii::app()->controller->checkPermissions($this->model, 'edit');
        }
        return $this->_moduleUpdatePermissions;
    }

}
