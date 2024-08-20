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
 * Utility methods for modules whose records can be emailed with the InlineEmailWidget.
 * @package application.components
 */
class InlineEmailModelBehavior extends CBehavior {


    /**
     * @return <array of strings> Email addresses of associated contacts.
     */
    public function getRelatedContactsEmails () {
        $contactsArray = array();
        foreach($this->owner->relatedX2Models as $relatedModel) {
          if($relatedModel instanceof Contacts) {
            $contact = '"'.$relatedModel->name.'" <'.$relatedModel->email.'>';
            if($relatedModel->email != '' && !in_array($contact, $contactsArray)) {
               $contactsArray[] = $contact;
            }
          }
        }
        return $contactsArray;
    }

    /**
     * @return <array of strings> Email addresses of associated contacts.
     */
    public function getInquiriesContactsEmails () {
        $inquiriesArray = array();
        $me = User::getMe();
        $inquiries = Inquiries::model()->findAllByAttributes(array('c_listing__c' => $this->owner->nameId));
        if(!empty($inquiries) && $this->owner instanceof Listings2) {
           $inquiries = Inquiries::model()->findAllByAttributes(array('c_listing__c' => $this->owner->nameId), 'assignedTo = ' .  "'" . $me->username . "'");
           if(empty($inquiries)) {
              $inquiries = Inquiries::model()->findAllByAttributes(array('c_listing__c' => $this->owner->nameId), 'assignedTo IS NULL');
           }
           $used_emails = array();
           for($i = 0; $i < count($inquiries); $i++) {
              if(!in_array($inquiries[$i]['c_email__c'], $used_emails) && !empty($inquiries[$i]['c_email__c'])) {
                 $inquiriesArray[] = '"' . $inquiries[$i]['c_contact_name__c'] . '" <' . $inquiries[$i]['c_email__c'] . '>';
                 $used_emails[] = $inquiries[$i]['c_email__c'];
              }
           }
        }
        return $inquiriesArray;
    }

    /**
     * Gets insertable attributes for InlineEmailForm widget 
     * @param object $model
     * @return array
     */
    public function getEmailInsertableAttrs () {
        // Limit insertable attributes
        $insertableAttributes = array();
        foreach($this->owner->attributeLabels() as $fieldName => $label) {
            $attr = trim($this->owner->renderAttribute($fieldName,false));
            if($attr !== '')
                $insertableAttributes[$label] = $attr;
        }
        return $insertableAttributes;
    }

}

?>
