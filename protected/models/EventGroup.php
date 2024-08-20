<?php
/***********************************************************************************
* Copyright (C) 2011-2018 X2 Engine Inc. All Rights Reserved.
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
 * This is the model class for table "x2_event_groups".
 * @package application.models
 */
class EventGroup extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Imports the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_event_groups';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
            return array(
                array('name', 'required'),
                array('name', 'length', 'max' => 255),
                // The following rule is used by search().
                // Please remove those attributes that should not be searched.
                array('id, name', 'safe', 'on' => 'search'),
            );
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			'name' => Yii::t('admin','Name'),
			'groupMembers' => Yii::t('admin','Group Members'),	
		);
	}
        
    /**
     * Looks up groups to which the specified user belongs.
     * Uses cache to lookup/store groups.
     *
     * @param string $username user to look up groups for
     * @param boolean $cache whether to use cache
     * @return Array array of groupIds
     */
    public static function getUserGroupNames($username, $cache = true) {
        $getUserGroupNames = array();
        if ($username === null)
            return array();

        $userGroups[$username] = Yii::app()->db->createCommand() // get array of groupIds
                        ->select('id')
                        ->from('x2_event_groups')
                        ->where('groupMembers LIKE "%'. $username .'%"', array())->queryColumn();
        
        foreach ($userGroups[$username] as $groupId) {
            $aGroupOfTheUser = EventGroup::model()->findByPk($groupId);
            $getUserGroupNames[$groupId] = $aGroupOfTheUser->name;
        }
        if ($cache === true) {
            // cache user groups for 3 days
            Yii::app()->cache->set('user_group_names', $getUserGroupNames, 259200);
        }
        

        return $getUserGroupNames;
    }
        
    /**
     * Looks up groups to which the specified user belongs.
     * Uses cache to lookup/store groups.
     *
     * @param string $username user to look up groups for
     * @param boolean $cache whether to use cache
     * @return Array array of groupIds
     */
    public static function getUserGroups($username, $cache = true) {
        if ($username === null)
            return array();
        // check the app cache for user's groups
        if ($cache === true && ($userGroups = Yii::app()->cache->get('user_groups')) !== false) {
            if (isset($userGroups[$username]))
                return $userGroups[$username];
        } else {
            $userGroups = array();
        }

        $userGroups[$username] = Yii::app()->db->createCommand() // get array of groupIds
                        ->select('id')
                        ->from('x2_event_groups')
                        ->where('groupMembers LIKE "%'. $username .'%"', array())->queryColumn();

        if ($cache === true) {
            // cache user groups for 3 days
            Yii::app()->cache->set('user_groups', $userGroups, 259200);
        }

        return $userGroups[$username];
    }


}
