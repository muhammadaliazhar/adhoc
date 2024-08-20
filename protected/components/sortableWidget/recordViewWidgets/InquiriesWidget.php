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
 * @package application.components.sortableWidget
 */
class InquiriesWidget extends SortableWidget {

     

    /**
     * @var CActiveRecord $model
     */
    public $model; 

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{titleBarButtons}{closeButton}{minimizeButton}</div>{widgetContents}';

    public $viewFile = '_inquiriesWidget';

    protected $containerClass = 'sortable-widget-container x2-layout-island history';

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array (
                'model' => new Inquiries('search'),
            );
        }
        return $this->_viewFileParams;
    }
 
    private static $_JSONPropertiesStructure;
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Inquiries',
                    'hidden' => false,
	   	    'containerNumber' => 2,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function renderTitleBarButtons () {
	echo '<div class="x2-button-group">';
        echo "<a class='x2-button rel-title-bar-button' href='#' onclick='toggleInquiriesEmailForm(); return false;' id='inquiries-email-button'>"
                . X2Html::fa ('fa-envelope', array()) . "</a>";
        echo "<a class='x2-button icon rel-title-bar-button' href='#' id='inquiries-export-button'>"
                . X2Html::fa ('fa-download', array ()) . "</a>";
        echo '</div>';
    }

    public static function getListingsInquiries () {
        $envelopeArray = array();
        // find listings id from page url
        $currentUrl = Yii::app()->request->url;
        if(!strpos($currentUrl, 'listings2')) return;
        $modelId = explode("/", $currentUrl);
        $model = Listings2::model()->findByPk(end($modelId));
        if (isset($model))
            $buyers = Inquiries::model()->findAllByAttributes(array('c_listing__c' => $model->nameId));
        if(!empty($buyers)) {
           foreach ($buyers as $buyer) {
                   $envelopeArray = array_merge($envelopeArray, Docusign_status::model()->findAllByAttributes(array('c_recordId' => substr($buyer->c_contact__c, strpos($buyer->c_contact__c, '_') + 1), 'c_recordType' => 'Contacts')));
           }
        }

        // update inquiries records here
	$emailAddresses = array();
        foreach ($envelopeArray as $envelope) {
                 if (isset($model->nameId) && isset($envelope->c_recordEmail)) {
		    if (!array_key_exists($envelope->c_recordEmail,$emailAddresses)) {
			$emailAddresses[$envelope->c_recordEmail] = 1;
                        $inquiriesOfThisListing = isset($model) && isset($envelope->c_recordEmail) ? Inquiries::model()->findByAttributes(array('c_listing__c' => $model->nameId, 'c_email__c' => $envelope->c_recordEmail)) : null;
                        if (isset($inquiriesOfThisListing)) {
                                $inquiriesOfThisListing->c_docusign_status = $envelope->c_status;
                                $inquiriesOfThisListing->save();
                        }
		    }
                 }
        }
    }

    public function renderWidgetLabel () {
        $label = $this->getWidgetLabel ();
        echo "<div class='widget-title'>Inquiries</div>";
    }

    public function run () {
        if ($this->widgetManager->layoutManager->staticLayout) return;

        $this->getListingsInquiries ();

        // hide widget if journal view is disabled
        if (!Yii::app()->params->profile->miscLayoutSettings['enableJournalView']) {
            $this->registerSharedCss ();
            $this->render ('application.components.sortableWidget.views.'.$this->sharedViewFile,
                array (
                    'widgetClass' => get_called_class (),
                    'profile' => $this->profile,
                    'hidden' => true,
                    'widgetUID' => $this->widgetUID,
                ));
            return;
        }

        parent::run ();
    }

}

?>
