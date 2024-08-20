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
 * A Campaign represents a one time mailing to a list of contacts.
 *
 * When a campaign is created, a contact list must be specified. When a campaing is 'launched'
 * a duplicate list is created leaving the original unchanged. The duplicate 'campaign' list
 * will keep track of which contacts were sent email, who opened the mail, and who unsubscribed.
 * A campaign is 'active' after it has been launched and ready to send mail. A campaign is 'complete'
 * when all applicable email has been sent. This is the model class for table "x2_campaigns".
 *
 * @package application.modules.marketing.models
 */
Yii::import('application.models.X2List');
class Campaign extends X2Model {

    const CAMPAIGN_TYPE_DROPDOWN = 107;

    public $supportsWorkflow = false;

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName()	{ return 'x2_campaigns'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'marketing'
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
            'TagBehavior' => array(
                'class' => 'TagBehavior',
                'disableTagScanning' => true,
            ),
		));
	}

	public function relations() {
		return array_merge(parent::relations(),array(
			'list'=>array(self::BELONGS_TO, 'X2List', array('listId'=>'nameId')),
			'attachments'=>array(self::HAS_MANY, 'CampaignAttachment', 'campaign'),
		));
	}

	//Similar to X2Model but we had a special case with 'marketing'
	public function attributeLabels() {
		$this->queryFields();

		$labels = array();

		foreach(self::$_fields[$this->tableName()] as &$_field)
			$labels[ $_field->fieldName ] = Yii::t('marketing',$_field->attributeLabel);

		return $labels;
	}

	//Similar to X2Model but we had a special case with 'marketing'
	public function getAttributeLabel($attribute) {

		$this->queryFields();

		// don't call attributeLabels(), just look in self::$_fields
		foreach(self::$_fields[$this->tableName()] as &$_field) {
			if($_field->fieldName == $attribute)
				return Yii::t('marketing',$_field->attributeLabel);
		}
		// original Yii code
		if(strpos($attribute,'.')!==false) {
			$segs=explode('.',$attribute);
			$name=array_pop($segs);
			$model=$this;
			foreach($segs as $seg) {
				$relations=$model->getMetaData()->relations;
				if(isset($relations[$seg]))
					$model=X2Model::model($relations[$seg]->className);
				else
					break;
			}
			return $model->getAttributeLabel($name);
		} else
			return $this->generateAttributeLabel($attribute);
	}

	/**
	 * Convenience method to retrieve a Campaign model by id. Filters by the current user's permissions.
	 *
	 * @param integer $id Model id
	 * @return Campaign
	 */
	public static function load($id) {
		$model = X2Model::model('Campaign');
		$campaign = $model->with('list')->findByPk((int)$id,$model->getAccessCriteria());
		$campaign->recalculateRates();
		return $campaign;
	}

	/**
	 * Search all Campaigns using this model's attributes as the criteria
	 *
	 * @return Array Set of matching Campaigns
	 */
	public function search() {
		$criteria=new CDbCriteria;
		$criteria->addCondition(" id != 19586");
        $criteria->addCondition(" id != 61783");
        $criteria->addCondition(" id != 61622");
        $criteria->addCondition(" id != 104937");
        $criteria->addCondition(" id != 104953");
		return $this->searchBase($criteria);
	}

    /**
     * Override of {@link X2Model::setX2Fields}
     *
     * Skips HTML purification for the content so that tracking links will work.
     */
    public function setX2Fields(&$data, $filter = false, $bypassPermissions=false) {
        $originalContent = isset($data['content'])?$data['content']:$this->content;
        parent::setX2Fields($data, $filter, $bypassPermissions);
        $this->content = $originalContent;
    }

    public static function getValidContactLists () {
        $list = new X2List;
        $criteria = $list->getAccessCriteria();
        $criteria->addCondition("type!='campaign'");
    	$lists = X2Model::model('X2List')->findAllByAttributes (array(), 
    		$criteria
    	);
    	return $lists;
    }

    public function getDisplayName ($plural=true, $ofModule=true) {
        if (!$ofModule) {
            return Yii::t('app', 'Campaign'.($plural ? 's' : ''));
        } else {
            return parent::getDisplayName ($plural, $ofModule);
        }
    }

    protected function recalculateRates() {
        $list = $this->list;
        if (!$list) return;

        $total = $list->statusCount('sent');
        if ($total == 0) return;

        $opened = $list->statusCount('opened');
        $clicked = $list->statusCount('clicked');
        $unsubscribed = $list->statusCount('unsubscribed');
        $updateAttrs = array();
        $this->openRate = sprintf('%.2f', $opened / $total * 100);
        if (!isset($this->_oldAttributes['openRate']) || $this->openRate != $this->_oldAttributes['openRate'])
            $updateAttrs[] = 'openRate';
        $this->clickRate = sprintf('%.2f', $clicked / $total * 100);
        if (!isset($this->_oldAttributes['clickRate']) || $this->clickRate != $this->_oldAttributes['clickRate'])
            $updateAttrs[] = 'clickRate';
        $this->unsubscribeRate = sprintf('%.2f', $unsubscribed / $total * 100);
        if (!isset($this->_oldAttributes['unsubscribeRate']) || $this->unsubscribeRate != $this->_oldAttributes['unsubscribeRate'])
            $updateAttrs[] = 'unsubscribeRate';

        if (!empty($updateAttrs) && $this->id != 19586)
            $this->update($updateAttrs);
    }

    private $quick = false;
    public function setQuick($set) {
        $this->quick = $set;
    }

    public function isQuick() {
        return $this->quick;
    }

    public function Complete() {



        $this->active = 0;
        $this->complete = 1;
        $this->save();

    }

    /**
     * update on initial model to update the rates
     */
    public function afterFind() {
        //$this->recalculateRates();

        parent::afterFind();
    }



    //this will check to see if we need to split the campaign and if we do 
    public function checkAndSplit(){
        //if not split type return false
        //if(!$this->splitCamp)return false;
        $splitList = array(
                array('name'=> "(A)" , 'value' =>
                    array('a','A')
                ),
                array('name'=> "(B)" , 'value' =>
                    array('b','B')
                ),
                array('name'=> "(C)" , 'value' =>
                    array('c','C')
                ),
                array('name'=> "(D)" , 'value' =>
                    array('d','D')
                ),
                array('name'=> "(E)" , 'value' =>
                    array('e','E')
                ),
                array('name'=> "(F)" , 'value' =>
                    array('f','F')
                ),
                array('name'=> "(G)" , 'value' =>
                    array('g','G')
                ),
                array('name'=> "(H)" , 'value' =>
                    array('h','H')
                ),
                array('name'=> "(I)" , 'value' =>
                    array('i','I')
                ),
                array('name'=> "(J)" , 'value' =>
                    array('j','J')
                ),
                array('name'=> "(K)" , 'value' =>
                    array('k','K')
                ),
                array('name'=> "(L)" , 'value' =>
                    array('l','L')
                ),
                array('name'=> "(M)" , 'value' =>
                    array('m','M')
                ),
                array('name'=> "(N)" , 'value' =>
                    array('n','N')
                ),
                array('name'=> "(O)" , 'value' =>
                    array('o','O')
                ),
                array('name'=> "(P)" , 'value' =>
                    array('p','P')
                ),
                array('name'=> "(Q)" , 'value' =>
                    array('q','Q')
                ),
                array('name'=> "(R)" , 'value' =>
                    array('r','R')
                ),
                array('name'=> "(S)" , 'value' =>
                    array('s','S')
                ),
                array('name'=> "(T)" , 'value' =>
                    array('t','T')
                ),
                array('name'=> "(U)" , 'value' =>
                    array('u','U')
                ),
                array('name'=> "(V)" , 'value' =>
                    array('v','V')
                ),
                array('name'=> "(W)" , 'value' =>
                    array('w','W')
                ),
                array('name'=> "(X)" , 'value' =>
                    array('x','X')
                ),
                array('name'=> "(Y)" , 'value' =>
                    array('y','Y')
                ),
                array('name'=> "(Z)" , 'value' =>
                    array('Z','z')
                ),
                array('name'=> "(0-9)" , 'value' =>
                    array('0','1','2','3','4','5','6','7','8','9')
                )
            );
        //here we go a seprate each peace and set up each camapaign
        $childCamps = array();
        foreach($splitList as $split){
            $dupList = new X2List;
            $dupCamp = $this->cloneCamp();
            $childCamps[] = $dupCamp->id;
            //$dupCamp->parent = $this->id;
            $dupCamp->active = 0;
            $dupCamp->name = $this->name . $split['name'];
            $dupList->name = $this->list->name . $split['name'];
            $dupList->type = 'campaign';
            $dupList->createDate = $dupList->lastUpdated = time();
            $dupList->modelName = $this->list->modelName;
            $dupList->save();
            $dupCamp->save();
            $this->splitList($split['value'],$this->list->id,$dupList->id);
            $dupCamp->list = $dupList;
            $dupCamp->listId = $dupList->nameId;
             $dupCamp->save();

        }
        //$this->type = 'Split';
        //$this->children = json_encode($childCamps);
        $this->active = 0;
        $this->complete = 1;
        $this->save();
        return true;

    }

    public function splitList($chars , $oldListId, $newListId){

        $params = array(
            ':oldListId' => $oldListId,
        ':newListId' => $newListId
        );
        //$condition = 'i.listId=:oldListId AND i.sent=0 AND i.suppressed=0 ';
        $condition = 'i.listId=:oldListId ';
        $likeC = ' FALSE ';
        foreach($chars as $char){
            $likeC .= " OR (i.emailAddress LIKE '" . $char . "%' OR c.email LIKE '" . $char . "%') ";

        }
        $condition .= " AND ( " . $likeC . ") ";
            $columns = 'i.listId=:newListId';
        $modelTypes =  ["Contacts", "Opportunity", "Accounts", "X2Leads"];

        //go through and check each model type
        //   foreach($modelTypes as $nameModel){
        //     $tableName = (new $nameModel)->tableName();
        //     Yii::app()->db->createCommand("UPDATE x2_list_items AS i LEFT JOIN {$tableName} AS c ON c.id=i.contactId SET {$columns} WHERE {$condition} AND modelType = '{$nameModel}'")->execute($params);
        //}
        $tableName = (new $this->list->modelName)->tableName();
        Yii::app()->db->createCommand("UPDATE x2_list_items AS i LEFT JOIN {$tableName} AS c ON c.id=i.contactId SET {$columns} WHERE {$condition}")->execute($params);
    }

    public function cloneCamp(){
        $clone = new Campaign;
        $clone->type = $this->type;
        $clone->name = $this->name;
        $clone->listId = $this->listId;
        //$clone->suppressionListId = $this->suppressionListId;
        $clone->visibility = $this->visibility;
        $clone->createDate = time();
        $clone->lastUpdated = time();
        $clone->lastActivity = time();
        $clone->content = $this->content;
        $clone->createdBy = $this->createdBy;
        $clone->assignedTo = $this->assignedTo;
        //$clone->launchDate = $this->launchDate;
        //$clone->categoryListId = $this->categoryListId;
        //$clone->category = $this->category;
        $clone->subject = $this->subject;
        $clone->enableRedirectLinks = $this->enableRedirectLinks;
        //$clone->bouncedAccount = $this->bouncedAccount;
       // $clone->enableBounceHandling = $this->enableBounceHandling;
        $clone->save();
        return $clone;

    }

    //check to make sure unsubscribe list exsist, if not make the list
    public static function checkUnsubList($Category) {
        $FullName = 'Unsubscribe_' . $Category . '_X2_internal_list';
        $list = CActiveRecord::model('X2List')
                        ->findByAttributes(array('name' => $FullName));
        if (empty($list)){
            $NewUNSub = new X2List;
            $NewUNSub->modelName = 'Contacts';
            $NewUNSub->type = 'UnSubscribe';
            $NewUNSub->assignedTo = 'Anyone';
            $NewUNSub->visibility = 1;
            $NewUNSub->name = $FullName;
            $NewUNSub->nameId = $FullName;
            $NewUNSub->createDate = time();
            $NewUNSub->lastUpdated = time();
            $NewUNSub->logicType = 'AND';
            $NewUNSub->save();

        }

        return($list);
     }


}
