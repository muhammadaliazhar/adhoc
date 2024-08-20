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
 * Widget class for the relationships form.
 *
 * Relationships lists the relationships a model has with other models,
 * and provides a way to add existing models to the models relationships.
 *
 * @package application.components.sortableWidget
 */
class InlineX2SignDetailWidget extends GridViewWidget {

     

    public $viewFile = '_inlineX2SignDetailWidget';

    public $model;

    public $template = '<div class="submenu-title-bar widget-title-bar">
                       {widgetLabel}{titleBarButtons}{closeButton}{minimizeButton}{settingsMenu}
                       </div>{widgetContents}';

    protected $containerClass = 'sortable-widget-container x2-layout-island history';

    private static $_JSONPropertiesStructure;

    private $_filterModel;

    public function renderTitleBarButtons () {
        echo '<div class="x2-button-group">';
        echo '</div>';
    }

    public function renderWidgetLabel () {
        echo '<div class="widget-title">X2Sign Audit Trails</div>';
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'X2Sign Detail',
                    'hidden' => false,
                    'resultsPerPage' => 10, 
                    'showHeader' => true,
                    'displayMode' => 'grid', // grid | graph
                    'height' => '200',
                    'hideFullHeader' => false, 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getDataProvider () {
        $model = $this->model;
        
        if(get_class($model) == "Listings2"){

            $criteria = array(
                "join" => 'JOIN x2_sign_links as t0 ON t.id = t0.envelopeId',
                "condition" => 't.c_listing = "'.addslashes($model->nameId).'"',
                "order" => 't.createDate DESC'
            );
        }else{
            $criteria = array(
                "join" => 'JOIN x2_sign_links as t0 ON t.id = t0.envelopeId',
                "condition" => 't0.modelId='.$model->id.' AND t0.modelType="'.get_class($model).'"',
                "order" => 't.createDate DESC'
            );


        }
        $x2signDataProvider = new CActiveDataProvider('X2SignEnvelopes', array(
            'id' => 'x2sign-gridview',
            'criteria' => $criteria,
            'pagination' => array('pageSize'=>$this->getWidgetProperty ('resultsPerPage'))
        ));
        return $x2signDataProvider;
    }

    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            if (isset ($_GET['ajax'])) {
                $this->_setupScript = "";
            } else {
                $this->_setupScript = "
                    $(function () {
                        x2.inlineX2SignDetailWidget = new x2.InlineX2SignDetailWidget (".
                            CJSON::encode (array_merge ($this->getJSSortableWidgetParams (), array (
                                'displayMode' => $this->getWidgetProperty ('displayMode'),
                                'widgetClass' => $widgetClass,
                                'setPropertyUrl' => Yii::app()->controller->createUrl (
                                    '/profile/setWidgetSetting'),
                                'cssSelectorPrefix' => $this->widgetType,
                                'widgetType' => $this->widgetType,
                                'widgetUID' => $this->widgetUID,
                                'enableResizing' => true,
                                'height' => $this->getWidgetProperty ('height'),
                                'recordId' => $this->model->id,
                                'recordType' => get_class ($this->model),
                            )))."
                        );
                    });
                ";
            }
        }
        return $this->_setupScript;
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
     public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'InlineX2SignDetailJSExt' => array(
                        'baseUrl' => Yii::app()->getTheme ()->getBaseUrl ().'/css/gridview/',
                        'js' => array (
                            'jquery.yiigridview.js',
                        ),
                        'depends' => array ('auxlib')
                    ),
                    'InlineX2SignDetailJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array (
                            'js/sortableWidgets/InlineX2SignDetailWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    } 

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'model' => $this->model,
                    'modelName' => get_class ($this->model),
                    'displayMode' => $this->getWidgetProperty ('displayMode'),
                    'height' => $this->getWidgetProperty ('height'),
                )
            );
        }
        return $this->_viewFileParams;
    } 

    protected function getSettingsMenuContentEntries () {
        return 
            '<li class="expand-detail-views">'.
                X2Html::fa('fa-toggle-down').
                Yii::t('profile', 'Toggle Detail Views').
            '</li>'.
            parent::getSettingsMenuContentEntries ();
    }


    private $_moduleUpdatePermissions;
    private function checkModuleUpdatePermissions () {
        if (!isset ($this->_moduleUpdatePermissions)) {
            $this->_moduleUpdatePermissions = 
                Yii::app()->controller->checkPermissions ($this->model, 'edit');
        }
        return $this->_moduleUpdatePermissions;
    }

    public function init ($skipGridViewInit=false) {
        return parent::init (true);
    }
}

?>
