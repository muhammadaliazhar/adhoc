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

// Variables to pass to javascript file
$url = Yii::app()->createExternalUrl('/marketing/landingPage?id='.$id);
$iframe = '<iframe name"landing-page-iframe" src="'. $url .'" frameborder="0" allowtransparency="true" scrolling="0" style="width:100%;height:100%;"></iframe>';
$categories = array_keys($catalog);

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/components/landingPageDesigner.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/landingPageDesigner.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/lib/colResizable/colResizable.js');
Yii::app()->clientScript->registerPackage('CodeMirrorJS');
Yii::app()->clientScript->registerScript('init-landing-page-designer', '
    if (typeof x2 === "undefined")
        x2 = {};
    if (typeof x2.landingPageDesigner === "undefined")
        x2.landingPageDesigner = {};
        
    x2.landingPageDesigner.iframe = '. CJSON::encode($iframe) .';
        
    x2.landingPageDesigner.catalog = '. CJSON::encode($catalog) .';

    x2.landingPageDesigner.designerUrl = '.CJSON::encode($this->createUrl('')).';
    x2.landingPageDesigner.translations = {
        "landing page name": '.CJSON::encode(Yii::t('studio', 'What would you like to name your landing page?')).',
        "delete page": '.CJSON::encode(Yii::t('studio', 'Are you sure you want to delete this landing page?')).'
    };
', CClientScript::POS_READY);

if (isset($model) && $model instanceof LandingPage) {
    Yii::app()->clientScript->registerScript('landing-page-designer-data', '
        var formJson = '.CJSON::encode($model->data).';
        var description = '.CJSON::encode($model->description).';
        var customCss = '.CJSON::encode($model->customCss).';
        var customHtml = '.CJSON::encode($model->customHead).';

        x2.landingPageDesigner.loadFormJson(formJson, description, customCss, customHtml);
    ');
}


/**
 * Top bar title and buttons
 */
?>
<div class="page-title">
    <h2><?php echo Yii::t('studio', 'Landing Page Designer'); ?></h2>
    <?php if (isset($model) && $model instanceof LandingPage) { ?>
        <a id="landing-page-save-button" class="x2-button highlight right" href="javascript:void(0);">
            <?php echo Yii::t('app', 'Save'); ?>
        </a>
        <a id="landing-page-delete-button" class="x2-button right" href="javascript:void(0);">
            <?php echo Yii::t('app', 'Delete'); ?>
        </a>
    <?php } ?>
    <a id="landing-page-create-button" class="x2-button right" href="javascript:void(0);">
        <?php echo Yii::t('app', 'Create'); ?>
    </a>
</div>

<?php
/**
 * Initial fields
 */
?>
<div id="init-fields" class="form row">
    <p style="margin: 10px 0px 0px 0px;">
        <?php echo Yii::t('studio',
            'The Landing Page Designer allows you to quickly and '.
            'conveniently build out your landing pages in a drag and drop interface. You '.
            'can arrange any of the available components in your desired layout of rows '.
            'columns. Components that can be placed on your landing page include Docs for '.
            'rich text snippets, images in the Media module, targeted content X2Workflows, '.
            'and weblead, service, and newsletter forms. To add the landing page on your '.
            'public website, paste the provided embed code into your desired page.'
        ); ?>
    </p>
    <h4><?php echo Yii::t('marketing','Saved Landing Pages'); ?></h4>
    <?php
        echo X2Html::dropDownList('landingPage', $id, $landingPages);
    ?>
</div>

<?php if (isset($model) && $model instanceof LandingPage) {
    // helper function to render components and avoid code duplication
    $renderComponent = function($type, $model, $attr = 'name') {
        return X2Html::tag('div',
            array(
                'class' => 'landing-page-component',
                'data-id' => $model->id,
                'data-type' => $type,
            ),
            X2Html::fa(
                LandingPage::$componentIcons[$type],
                array(
                    'class' => 'component'
                ),
            $model->$attr)
        );
    }; ?>

    <?php echo X2Html::divider(NULL, '0px'); ?>

<div id="landing-page-designer-form">

    <?php
    /**
     * <iframe> generator
     */
    ?>
    <h4><?php echo Yii::t('marketing','Embed Code'); ?></h4>
    <div id='embed-row'>
        <input readonly type="text" id="embed-code"
        value='<?php echo $iframe; ?>' /><span class='x2-button' id='clipboard' title='Select Text'><i class='fa fa-clipboard'></i></span><span style='display:none'id='copy-help'><p class='fieldhelp'>
        <?php $help = Auxlib::isMac() ? "⌘-c to copy" : "ctrl-c to copy"; ?>
        <?php echo Yii::t('app', $help) ?></p></span>
    </div>
    
    <?php
    /**
     * Landing page designer studio
     */
    ?>
    <h4><?php echo Yii::t('marketing','Studio'); ?></h4>
    <p class="fieldhelp">
        <?php echo Yii::t('studio','Add or delete rows and columns and add your own HTML code or drag and drop various components to design your landing page.'); ?>
    </p>
    <div id="studio">
        <?php
        /**
         * Designer controls
         */
        ?>
        <div id="designer">
            <div id="designer-start"></div>
            <div class="row" style="margin: 10px 0px 10px 0px;">
                <a href="javascript:void(0)" id="add-row" class="x2-button"><?php echo Yii::t('admin', 'Add Row'); ?></a>
                <!--<a href="javascript:void(0)" id="add-collapsible-row" class="x2-button"><?php /*echo Yii::t('admin', 'Add Collapsible');*/ ?></a>-->
            </div>
        </div>

        <?php
        /**
         * Drag and drop components
         */
        ?>
        <h4><?php echo Yii::t('marketing','Components'); ?></h4>
        <div id="components" class="landing-page-sortable">
            <?php
                // Render each landing page component in the catalog according to type
                $componentTypes = array('docs', 'images', 'targetedContent', 'webleadForms', 'serviceForms', 'newsletterForms');
                foreach ($componentTypes as $type) {
                    foreach ($catalog[$type] as $component) {
                        echo $renderComponent($type, $component);
                    }
                }
            ?>
        </div>
        <?php echo X2Html::dropDownList('categories', 0, array_merge(array('all'), $categories)); ?>
    </div>
    
    <?php
    /**
     * Landing page preview
     */
    ?>
    <h4><?php echo Yii::t('marketing','Preview'); ?></h4>
    <p class="fieldhelp">
        <?php echo Yii::t('studio','Show a preview of your landing page.'); ?>
    </p>
    <div id="preview" style="display:none;">
        <?php echo $iframe; ?>
    </div>
    <a id="load-preview" href="javascript:void(0)" class="x2-button" style="margin-top: 10px;">
           <?php echo Yii::t('admin', 'Load Preview'); ?>
    </a>
    <a id="hide-preview" href="javascript:void(0)" class="x2-button" style="display:none;margin-top: 10px;">
           <?php echo Yii::t('admin', 'Hide Preview'); ?>
    </a>

    <?php
    /**
     * Landing page description
     */
    ?>
    <div class="custom-page-fields row">
        <h4><?php echo Yii::t('studio','Description'); ?></h4>
        <p class="fieldhelp">
            <?php echo Yii::t('studio','Enter a description for your landing page.'); ?>
        </p>
        <?php echo CHtml::textArea('description', '', array(
            'style' => 'width:99%;',
            'id' => 'description',
            'data-mode'=> 'text'
        ));
        ?>
    </div>

    <?php
    /**
     * Custom CSS
     */
    ?>
    <div class="custom-page-fields row">
        <h4><?php echo Yii::t('studio','CSS'); ?></h4>
        <p class="fieldhelp">
            <?php echo Yii::t('studio','Enter custom CSS for your landing page.'); ?>
        </p>
        <?php echo CHtml::textArea('css', '/* custom css */', array(
            'class' => 'code',
            'id'=>'custom-css',
            'data-mode'=> 'css'
        ));
        ?>
    </div>

    <?php
    /**
     * Custom <Head>
     */
    ?>
    <div class="custom-page-fields row">
        <h4><?php echo Yii::t('studio','Custom &lt;HEAD&gt;'); ?></h4>
        <p class="fieldhelp" style="width: 100%">
            <?php echo Yii::t('studio',
                'Enter any HTML you would like inserted into the &lt;HEAD&gt; tag.'); ?>
        </p>
        <?php echo CHtml::textArea('header', '<!-- custom html -->', array(
            'class'=> 'code', 
            'id'=>'custom-html',
            'data-mode' => 'xml'
        ));
        ?>
        <br/>
    </div>
</div>
<?php } ?>
