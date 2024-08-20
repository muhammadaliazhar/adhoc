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



?>

<div id='profile-badge'>
	<span id='profile-avatar'>
	<?php echo Profile::renderLowQualityAvatar ($this->model->id, 45, array('class'=>"avatar-upload avatar-image")); ?>
	</span>

	<span id='info'>
	<div id='profile-name'>
		<?php echo X2Html::link(
			$this->model->fullName	,
			Yii::app()->controller->createUrl('view', array(
					'id' => $this->model->id,
					'publicProfile'=> true))
			 );
		?>
	</div>

	<!--div id='profile-edit'>
		<!?php echo X2Html::link(
			Yii::t('profile','Edit Profile'),
			Yii::app()->controller->createUrl('update', array(
					'id' => $this->model->id 
				))
			);
		?!>
	</div-->
	</span>
	<div class='clear'></div>
</div>
<div id='profile-layout'>
	<center>
        <div id='profile-layout-selector' style='padding:5px;'>
        <?php
        $choice = '';
        $myGroups = Groups::model()->getUserGroups(Yii::app()->user->getId());
        if(isset($myGroups) || count($myGroups) > 0) {
            $groupsWithLabel = array();
            foreach($myGroups as $groupsId){
                $groupsWithLabel[$groupsId] = Groups::model()->findByPk($groupsId)->name;
            }
            $myGroups = $groupsWithLabel;
        }

        if(isset($this->model->currentLayout) && !empty($this->model->currentLayout)){
            $choice = $this->model->currentLayout;
        }

        $myGroups['personal'] = 'personal';

        echo CHtml::dropDownList('currentLayout', $choice, $myGroups);
        ?>
        </div>
        </center>
</div>

<div id='profile-actions'>
	<?php echo
	X2Html::ul($actionList, array(
		'id' => 'profile-widget-action-menu')
	, 'x2-minimal-button');
	?>
</div>
