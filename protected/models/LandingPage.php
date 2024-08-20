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

/* @edition:ent */

class LandingPage extends X2ActiveRecord {

    public static $componentIcons = array(
        'docs' => 'fa-file-word-o',
        'images' => 'fa-file-image-o',
        'targetedContent' => 'fa-crosshairs',
        'webleadForms' => 'fa-bullhorn',
        'serviceForms' => 'fa-headphones',
        'newsletterForms' => 'fa-newspaper-o',
    );

    /**
     * Returns the static model of the specified AR class.
     * @return Imports the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_landing_pages';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'required'),
            array('name', 'unique', 'allowEmpty' => false),
        );
    }

    /**
     * @return array behaviors
     */
    public function behaviors() {
        return array(
            'TimestampBehavior' => array('class' => 'TimestampBehavior'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'name' => Yii::t('contacts', 'Name'),
            'description' => Yii::t('contacts', 'description'),
            'customCss' => Yii::t('contacts', 'Custom CSS'),
            'customHead' => Yii::t('contacts', 'Custom Head'),
        );
    }

    /**
     * Unpacks json into model
     * 
     * @param type $pageJson
     */
    public function unpackPageJson($pageJson) {
        $data = CJSON::decode($pageJson, true);

        $this->description = $data['description'];
        unset($data['description']);
        
        $this->customCss = $data['customCss'];
        unset($data['customCss']);
        
        $this->customHead = $data['customHead'];
        unset($data['customHead']);
        
        $this->data = CJSON::encode($data);
    }

    /**
     * Render the landing page according to configuration
     */
    public function render() {
        $data = CJSON::decode($this->data, true);
        if (isset($data['sections']) && is_array($data['sections'])) {
            foreach ($data['sections'] as $section) {
                $this->renderSection($section);
            }
        }
    }

    /**
     * Render a "section" of the landing page
     */
    private function renderSection(array $section) {
        if (isset($section['cols']) && is_array($section['cols'])) {
            $id = uniqid();
            $display = 'block';
            if ($section['collapsible']) {
                echo '<div class="row landingPageCollapser">';
                echo X2Html::minimizeButton(array(), '#' . $id, true, !$section['collapsedByDefault']);
                echo '</div>';
                if ($section['collapsedByDefault'])
                    $display = 'none';
            }
            echo '<div id="' . $id . '" class="landing-page-section" style="display:' . $display . '">';
            foreach ($section['cols'] as $col) {
                $this->renderColumn($col);
            }
            echo '</div>';
        }
    }

    /**
     * Render a "column" of the landing page
     */
    private function renderColumn(array $column) {
        if (isset($column['items']) && is_array($column['items'])) {
            echo '<div class="landing-page-column" style="display:inline-block; width:' . $column['width'] . '">';
            foreach ($column['items'] as $item) {
                $this->renderComponent($item);
            }
            echo '</div>';
        }
    }

    /**
     * Render an individual component of the landing page
     */
    private function renderComponent(array $component) {
        echo '<!--' . $component['type'] . ' ' . $component['id'] . '-->';
        switch ($component['type']) {
            case 'docs':
                $doc = Docs::model()->findByPk($component['id']);
                if ($doc) {
                    echo $doc->text;
                }
                break;
            case 'images':
                $image = Media::model()->findByPk($component['id']);
                if ($image && $image->isImage()) {
                    echo '<img src="' . $image->getPublicUrl() . '" />';
                }
                break;
            case 'targetedContent':
                $_GET['flowId'] = $component['id'];
                $targetedContent = WebListenerAction::track(true);
                echo '<script>';
                echo $targetedContent;
                echo '</script>';
                break;
            case 'webleadForms':
                $iframeSrc = Yii::app()->createExternalUrl('/contacts/contacts/weblead', array('webFormId' => $component['id']));
                /*
                  $id = uniqid();
                  echo '<div id="'.$id.'"></div><script>$("#'.$id.'").load("'.$iframeSrc.'");</script>';
                 */
                ///*
                echo '<iframe name="web-form-iframe" src="' . $iframeSrc . '" frameborder="0" allowtransparency="true" scrolling="0" width="200" height="385"></iframe>';
                //*/
                break;

            case 'serviceForms':
                $iframeSrc = Yii::app()->createExternalUrl('/services/services/webForm', array('webFormId' => $component['id']));
                echo '<iframe name="web-form-iframe" src="' . $iframeSrc . '" frameborder="0" allowtransparency="true" scrolling="0" width="200" height="385"></iframe>';
                break;
            case 'newsletterForms':
                $iframeSrc = Yii::app()->createExternalUrl('/marketing/weblist/weblist');
                echo '<iframe name="web-form-iframe" src="' . $iframeSrc . '" frameborder="0" allowtransparency="true" scrolling="0" width="200" height="100"></iframe>';
                break;
            case 'text':
                echo $component['text'];
                break;
            default:
                echo '<!-- no renderer found for type "' . $component['type'] . '" -->';
        }
        echo '<br />';
    }

}
