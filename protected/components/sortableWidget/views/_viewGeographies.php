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

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/viewChangelog.css');

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'geographies-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template' => '<div class="page-title"><div class="widget-title">'.Yii::t('admin','Geographies').'</div><div class="title-bar">'
    //.CHtml::link(Yii::t('app', 'Clear Filters'), array('viewGeographies', 'clearFilters' => 1))
    .'{summary}</div></div>{items}{pager}',
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
    'dataProvider' => $model->search($this->model->name),
    'filter' => $model,
    'afterAjaxUpdate' => 'refreshQtipHistory',
    'columns' => array(
        'name',
    ),
));
echo "<br>";

Yii::app()->clientScript->registerScript ('changelog-js', '
    function refreshQtipHistory(){
        $(".x2-hint").qtip();
    }
', CClientScript::POS_END);
