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

/* @edition:ent */

/**
 * Action to serve the landing page designer
 */
class LandingPageDesignerAction extends CAction {
    public function run() {
        $id = filter_input(INPUT_GET, 'id', FILTER_DEFAULT);
        $create = filter_input(INPUT_POST, 'pageName', FILTER_SANITIZE_STRING);
        $delete = filter_input(INPUT_POST, 'deletePage', FILTER_SANITIZE_NUMBER_INT);
        $pageJson = filter_input(INPUT_POST, 'pageJson', FILTER_DEFAULT);

        $model = null;
        if ($id) {
            $model = LandingPage::model()->findByPk($id);
        }

        if (!empty($create)) {
            // Create Landing Page
            $page = new LandingPage;
            $page->name = $create;
            if (!$page->save()) {
                throw new CHttpException(500, Yii::t('studio', 'Failed to create landing page'));
            }
            echo $page->id;
            Yii::app()->end();
        } else if (!empty($delete)) {
            // Delete Landing Page
            $page = LandingPage::model()->findByPk($delete);
            if (!$page) {
                throw new CHttpException(500, Yii::t('studio', 'Failed to locate landing page'));
            }
            if (!$page->delete()) {
                throw new CHttpException(500, Yii::t('studio', 'Failed to delete landing page'));
            }
            Yii::app()->end();
        } else if (!empty($pageJson)) {
            // Save Landing Page data
            $model->unpackPageJson($pageJson);
            if (!$model->save()) {
                throw new CHttpException(500, Yii::t('studio', 'Failed to save landing page'));
            }
        }

        $catalog = $this->loadCatalog();
        $landingPages = $this->getLandingPagesDropdown();

        $this->controller->render('application.components.LandingPageDesigner.views.landingPageDesigner', array(
            'catalog' => $catalog,
            'id' => $id,
            'landingPages' => $landingPages,
            'model' => $model,
        ));
    }

    /**
     * Prepare catalog of available components
     */
    protected function loadCatalog() {
        $catalog = array();
        $catalog['docs'] = Docs::model()->findAllByAttributes(array(), 'type != "email" AND type != "quote"');
        $catalog['images'] = Media::model()->findAllByAttributes(array(), 'mimeType like "image/%"');
        $catalog['targetedContent'] = X2Flow::model()->findAllByAttributes(array(
            'triggerType' => 'TargetedContentRequestTrigger'
        ));
        $catalog['webleadForms'] = WebForm::model()->findAllByAttributes(array(
            'type' => 'weblead',
        ));
        $catalog['serviceForms'] = WebForm::model()->findAllByAttributes(array(
            'type' => 'serviceCase',
        ));
        $catalog['newsletterForms'] = WebForm::model()->findAllByAttributes(array(
            'type' => 'weblist',
        ));
        return $catalog;
    }

    protected function getLandingPagesDropdown() {
        $landingPages = array(0 => '');
        $landingPageRecords = Yii::app()->db->createCommand()
            ->select('id, name')
            ->from('x2_landing_pages')
            ->queryAll();
        foreach ($landingPageRecords as $record) {
            $landingPages[$record['id']] = $record['name'];
        }
        return $landingPages;
    }
}
