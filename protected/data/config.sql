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
INSERT INTO x2_dropdowns (`id`, `name`, `options`) VALUES
(100,	'Product Status',	'{"Active":"Active","Inactive":"Inactive"}'),
(101,	'Currency List',	'{"USD":"USD","EUR":"EUR","GBP":"GBP","CAD":"CAD","JPY":"JPY","CNY":"CNY","CHF":"CHF","INR":"INR","BRL":"BRL"}'),
(102,	'Lead Type',		'{"None":"None","Web":"Web","In Person":"In Person","Phone":"Phone","E-Mail":"E-Mail"}'),
(103,	'Lead Source',		'{"None":"None","Google":"Google","Facebook":"Facebook","Walk In":"Walk In"}'),
(104,	'Lead Status',		'{"Unassigned":"Unassigned","Assigned":"Assigned","Accepted":"Accepted","Working":"Working","Dead":"Dead","Rejected":"Rejected"}'),
(105,	'Sales Stage',		'{"Working":"Working","Won":"Won","Lost":"Lost"}'),
(106,	'Quote Status',		'{"Draft":"Draft","Presented":"Presented","Issued":"Issued","Won":"Won"}'),
(107,	'Campaign Type',	'{"Email":"Email","Call List":"Call List","Physical Mail":"Physical Mail"}'),
(108,	'Case Impact', 		'{"1 - Severe":"1 - Severe","2 - Critical":"2 - Critical","3 - Moderate":"3 - Moderate","4 - Minor":"4 - Minor"}'),
(109,	'Case Status', 		'{"New":"New","WIP":"WIP","Waiting for response":"Waiting for response","Needs more info":"Needs more info","Escalated":"Escalated","Reopened":"Reopened","Work around provided, waiting for fix":"Work around provided, waiting for fix","Program Manager investigation":"Program Manager investigation","Closed - Resolved":"Closed - Resolved","Closed - No Response":"Closed - No Response"}'),
(110,	'Case Main Issue',	'{"Hardware":"Hardware","Software":"Software","Internet Connection":"Internet Connection","LMS":"LMS","General Request":"General Request"}'),
(111,	'Case Sub Issue', 	'{"Laptop":"Laptop","Desktop":"Desktop","WiFi":"WiFi","Loss Connection":"Loss Connection","Windows OS":"Windows OS","MS Office":"MS Office","Class Access":"Class Access","Lost Password":"Lost Password","Download\\/Upload":"Download\\/Upload","Other":"Other"}'),
(112,	'Case Origin', 		'{"Email":"Email","Web":"Web","Phone":"Phone"}'),
(113,	'Social Subtypes',	'{"Social Post":"Social Post","Link":"Link","Announcement":"Announcement","Product Info":"Product Info","Competitive Info":"Competitive Info","Confidential":"Confidential"}'),
(114,	'Invoice Status',	'{"Pending":"Pending","Issued":"Issued","Paid":"Paid","Open":"Open","Canceled":"Canceled","Other":"Other"}'),
(115,	'Bug Status',       '{"Unconfirmed":"Unconfirmed","Confirmed":"Confirmed","In Progress":"In Progress","Closed (Resolved Internally)":"Closed (Resolved Internally)","Closed (Unable to Reproduce)":"Closed (Unable to Reproduce)","Closed (Duplicate)":"Closed (Duplicate)","Merged Into Base Code":"Merged Into Base Code"}'),
(116,	'Bug Severity',     '{"5":"Blocker","4":"Critical","3":"Major","2":"Normal","1":"Minor","0":"Feature Request"}'),
(117,	'Quick Note',       '{"Contacted":"Contacted","Not Contacted":"Not Contacted"}'),
(120,   'Action Timers',    '{\"Research\":\"Research\",\"Meeting\":\"Meeting\",\"Email\":\"Email\"}'),
(121,   'Event Subtypes',    '{\"Meeting\":\"Meeting\",\"Appointment\":\"Appointment\",\"Call\":\"Call\"}'),
(122,   'Event Statuses',    '{\"Confirmed\":\"Confirmed\",\"Cancelled\":\"Cancelled\"}'),
(123,   "Event Colors",    '{"#6389de":"Blue","#a9c1fd":"Light Blue","#5de1e5":"Turquoise","#82e7c2":"Light Green","#6bc664":"Green","#fddb68":"Yellow","#ffbc80":"Orange","#ff978c":"Pink","#e74046":"Red","#d9adfb":"Purple","#dedddd":"Gray"}'),
(124,   'Priority',    '{"1":"Low","2":"Medium","3":"High"}');
/*&*/
INSERT INTO x2_dropdowns (`id`, `name`, `options`, `parent`, `parentVal`) VALUES
(118,	'Contacted Quick Note','{"Not interested.":"Not interested.","Requested follow up call.":"Requested follow up call.","Contact made.":"Contact made."}', 117, 'Contacted'),
(119,	'Not Contacted Quick Note','{"No answer.":"No answer.","Wrong number.":"Wrong number.","Left voicemail.":"Left voicemail."}', 117, 'Not Contacted');
/*&*/
ALTER TABLE x2_profile CHANGE `language` language varchar(40) DEFAULT '{language}', CHANGE `timeZone` timeZone varchar(100) DEFAULT '{timezone}';
/*&*/
ALTER TABLE x2_admin CHANGE `emailFromAddr` emailFromAddr varchar(255) NOT NULL DEFAULT '{bulkEmail}';
/*&*/
INSERT INTO x2_users (id, firstName, lastName, username, password, emailAddress, status, lastLogin, userKey)
        VALUES (1,'web','admin','{adminUsername}','{adminPass}','{adminEmail}','1','0','{adminUserKey}');
/*&*/
INSERT INTO x2_users (firstName, lastName, username, password, emailAddress, status, lastLogin)
        VALUES ('API','User','api','{apiKey}','{adminEmail}' ,'0', '0');
/*&*/
INSERT INTO x2_profile (fullName, username, emailAddress, status)
		VALUES ('Web Admin', '{adminUsername}', '{adminEmail}','1');
/*&*/
INSERT INTO x2_profile (fullName, username, emailAddress, status)
		VALUES ('API User', 'api', '{adminEmail}','0');
/*&*/
INSERT INTO x2_profile (id, fullName, username, emailAddress, status)
		VALUES (-1, '', '__x2_guest_profile__', '', '0');
/*&*/
INSERT INTO x2_social (`type`, `data`) VALUES ('motd', 'Please enter a message of the day!');
/*&*/
INSERT INTO x2_admin (timeout,webLeadEmail,emailFromAddr,currency,installDate,updateDate,quoteStrictLock,locationTrackingSwitch,unique_id,edition,serviceCaseFromEmailAddress,serviceCaseFromEmailName,serviceCaseEmailSubject,serviceCaseEmailMessage,eventDeletionTime,eventDeletionTypes,appName,appDescription,externalBaseUrl,externalBaseUri) VALUES (
	'3600',
	'{adminEmail}',
	'{bulkEmail}',
	'{currency}',
	'{time}',
	0,
	0,
        1,
	'{unique_id}',
	'{edition}',
	'{adminEmail}',
	'Tech Support',
	'Tech Support',
	'Hello {first} {last},\n\nJust wanted to check in with you about the support case you created. It is number {case}. We will get back to you as soon as possible.',
        0,
        '["record_create","record_deleted","action_reminder","action_complete","calendar_event","case_escalated","email_opened","email_sent","notif","weblead_create","web_activity","workflow_complete","workflow_revert","workflow_start"]',
        '{app}',
        'Your App Description',
        '{baseUrl}',
        '{baseUri}'
);
/*&*/
UPDATE x2_profile SET `widgets`='0:1:1:1:1:1:0:0:0:0:0:0:0:0',
	`widgetOrder`='OnlineUsers:TimeZone:GoogleMaps:SmallCalendar:ChatBox:TagCloud:TwitterFeed:MessageBox:QuickContact:NoteBox:ActionMenu:MediaBox:DocViewer:TopSites';
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable, moduleType)
VALUES
('x2Activity', 'Activity', 1, 0, 0, 0, 0, 0, 0, 'pseudoModule');
/*&*/
UPDATE `x2_modules` SET `visible`=0;
/*&*/
UPDATE `x2_modules` SET `visible`=1 WHERE `name` IN {visibleModules};
/*&*/
UPDATE `x2_modules` SET `itemName`= "Bug Report" WHERE `name` = "bugReports";
/*&*/
INSERT INTO `x2_tips` (`tip`, `edition`, `admin`, `module`) VALUES
("You can click on the icon with 4 outward arrows in the top right to hide the widget sidebar.",'opensource',0,'Layout'),
("An action's priority determines its color in the list. Gray actions have already been completed.",'opensource',0,'Actions'),
("Clicking on an item in the Action list will slide a window over with more information.",'opensource',0,'Actions'),
("The gear icon in the top right can be used to restore any widgets you have hidden.",'opensource',0,'Layout'),
("You can drag and drop widgets on the right sidebar to re-arrange their order.",'opensource',0,'Layout'),
("The options in the \"Quick Note\" menu on the publisher can be changed in the Dropdown Editor.",'opensource',1,'Admin'),
("You can see the history of related records by clicking \"Relationships\" on the History widget. Accounts do this by default.",'opensource',0,'Relationships');
/*&*/
INSERT INTO `x2_mobile_layouts`
(`id`,`modelName`, `layout`, `defaultForm`, `defaultView`, `version`)
VALUES
(-1, 'Profile', '["tagLine","username","officePhone","cellPhone","emailAddress","googleId"]',0,1,'5.4');
/*&*/
/* x2entstart */
INSERT INTO `x2_flows`
(`active`, `name`, `description`, `triggerType`, `modelClass`, `flow`, `createDate`, `lastUpdated`)
VALUES
(0, 'Default Workflow 1', 'Open Email To Trigger Notification To User', 'EmailOpenTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"EmailOpenTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Email has been opened by {firstName}"}}}],"flowName":"Default Workflow 1","flowDesc":"Open Email To Trigger Notification To User"}', 1498672464, 1498678229),
(0, 'Default Workflow 2', 'Web Lead Routing To Sales Rep', 'WebleadTrigger', 'Contacts', '{"version":"5.2","idCounter":3,"trigger":{"id":1,"type":"WebleadTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":3,"type":"X2FlowRecordReassign","options":{"user":{"value":"1"}}}],"flowName":"Default Workflow 2","flowDesc":"Web Lead Routing To Sales Rep"}', 1498672602, 1498678236),
(0, 'Default Workflow 3', 'Web Lead Routing To Team Based On Distribution Rule', 'WebleadTrigger', 'Contacts', '{"version":"5.2","idCounter":3,"trigger":{"id":1,"type":"WebleadTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordReassign","options":{"user":{"value":"auto"}}}],"flowName":"Default Workflow 3","flowDesc":"Web Lead Routing To Team Based On Distribution Rule"}', 1498672623, 1498678245),
(0, 'Default Workflow 4', 'Website Activity To Trigger Alert And Action For User', 'WebActivityTrigger', 'Contacts', '{"version":"5.2","idCounter":4,"trigger":{"id":1,"type":"WebActivityTrigger","options":{"url":{"value":"","operator":"="},"probability":{"value":"","operator":"="}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Website Activity"}}},{"id":4,"type":"X2FlowRecordCreateAction","options":{"subject":{"value":"New website activity"},"description":{"value":"New website activity from contact"}}}],"flowName":"Default Workflow 4","flowDesc":"Website Activity To Trigger Alert And Action For User"}', 1498672647, 1498678253),
(0, 'Default Workflow 5', 'Open Email to Trigger Notification to User', 'WebActivityTrigger', 'Contacts', '{"version":"5.2","idCounter":3,"trigger":{"id":1,"type":"WebActivityTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordEmail","options":{"from":{"value":"-1"},"template":{"value":""},"subject":{"value":"Hey"},"cc":{"value":""},"bcc":{"value":""},"logEmail":{"value":true},"doNotEmailLink":{"value":false},"body":{"value":"Hey this is an example"}}},{"id":3,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Website Activity"}}}],"flowName":"Default Workflow 5","flowDesc":"Open Email to Trigger Notification to User"}', 1498672661, 1498678260),
(0, 'Default Workflow 6', 'Inbound Email To Trigger Alert For User', 'InboundEmailTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"InboundEmailTrigger","options":{"modelClass":{"value":"Contacts"},"subject":{"value":"","operator":"="},"body":{"value":"","operator":"="},"to":{"value":"","operator":"="},"from":{"value":"","operator":"="}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Inbound Email"}}}],"flowName":"Default Workflow 6","flowDesc":"Inbound Email To Trigger Alert For User"}', 1498672675, 1498678268),
(0, 'Default Workflow 7', 'Outbound Email Opened To Trigger Alert For User', 'OutboundEmailTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"OutboundEmailTrigger","options":{"modelClass":{"value":"Contacts"},"subject":{"value":"","operator":"="},"body":{"value":"","operator":"="},"to":{"value":"","operator":"="},"from":{"value":"","operator":"="}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Outbound Email"}}}],"flowName":"Default Workflow 7","flowDesc":"Outbound Email Opened To Trigger Alert For User"}', 1498672688, 1498678277),
(0, 'Default Workflow 8', 'Open Email To Trigger Location To User Notification', 'EmailOpenTrigger', 'Contacts', '{"version":"5.2","idCounter":4,"trigger":{"id":1,"type":"EmailOpenTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":4,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"A contact opened an email {location} away"}}}],"flowName":"Default Workflow 8","flowDesc":"Open Email To Trigger Location To User Notification"}', 1498672704, 1498678285),
(0, 'Default Workflow 9', 'Action Completed To User Notification', 'ActionCompleteTrigger', 'Actions', '{"version":"5.2","idCounter":3,"trigger":{"id":1,"type":"ActionCompleteTrigger","options":[],"modelClass":"Actions"},"items":[{"id":3,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Action Completed!"}}}],"flowName":"Default Workflow 9","flowDesc":"Action Completed To User Notification"}', 1498673394, 1498678293),
(0, 'Default Workflow 10', 'Action Incomplete To Post To Activity Feed', 'ActionUncompleteTrigger', 'Actions', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"ActionUncompleteTrigger","options":[],"modelClass":"Actions"},"items":[{"id":2,"type":"X2FlowCreateEvent","options":{"type":{"value":"Social Post"},"text":{"value":"Action was marked Incomplete!"},"visibility":{"value":"1"},"feed":{"value":""},"user":{"value":"admin"},"createNotif":{"value":false}}}],"flowName":"Default Workflow 10","flowDesc":"Action Incomplete To Post To Activity Feed"}', 1498673470, 1498678301),
(0, 'Default Workflow 11', 'Action Overdue To Create Action', 'ActionOverdueTrigger', 'Actions', '{"version":"5.2","idCounter":3,"trigger":{"id":1,"type":"ActionOverdueTrigger","options":[],"modelClass":"Actions"},"items":[{"id":3,"type":"X2FlowCreateAction","options":{"dueDate":{"value":"Jun 7, 2017 12:00 AM"},"subject":{"value":"Redo Action"},"description":{"value":"This was made to redo the action overdue"},"assignedTo":{"value":"{assignedTo}"},"priority":{"value":"1"},"visibility":{"value":"1"},"color":{"value":"#6389de"}}}],"flowName":"Default Workflow 11","flowDesc":"Action Overdue To Create Action"}', 1498673584, 1498678309),
(0, 'Default Workflow 12', 'Campaign Email Clicked To Add Tag', 'CampaignEmailClickTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"CampaignEmailClickTrigger","options":{"campaign":{"value":""},"url":{"value":"","operator":"="}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordTag","options":{"action":{"value":"add"},"tags (optional)":{"value":"Clicked"}}}],"flowName":"Default Workflow 12","flowDesc":"Campaign Email Clicked To Add Tag"}', 1498673660, 1498678317),
(0, 'Default Workflow 13', 'Campaign Email Opened To Update Record', 'CampaignEmailOpenTrigger', 'Contacts', '{"version":"5.2","idCounter":5,"trigger":{"id":1,"type":"CampaignEmailOpenTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":5,"type":"X2FlowRecordUpdate","options":[],"attributes":[{"name":"otherUrl","value":"Email Opened"}]}],"flowName":"Default Workflow 13","flowDesc":"Campaign Email Opened To Update Record"}', 1498673817, 1498678325),
(0, 'Default Workflow 14', 'Campaign Web Activity To Create Notification', 'CampaignWebActivityTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"CampaignWebActivityTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Campaign Web Activity!"}}}],"flowName":"Default Workflow 14","flowDesc":"Campaign Web Activity To Create Notification"}', 1498673901, 1498673901),
(0, 'Default Workflow 15', 'Campaign Web Activity To Waiting and Create Notification', 'NewsletterWebActivityTrigger', NULL, '{"version":"5.2","idCounter":3,"trigger":{"id":1,"type":"NewsletterWebActivityTrigger","options":[]},"items":[{"id":2,"type":"X2FlowWait","options":{"delay":{"value":"1"},"unit":{"value":"mins"}}},{"id":3,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Campaign Web Activity! No contact available"}}}],"flowName":"Default Workflow 15","flowDesc":"Campaign Web Activity To Waiting and Create Notification"}', 1498673990, 1498678341),
(0, 'Default Workflow 16', 'Contact Location To Activity Feed Post', 'LocationTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"LocationTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateEvent","options":{"type":{"value":"Social Post"},"text":{"value":"Contacts location has been logged!"},"visibility":{"value":"1"},"feed":{"value":""},"user":{"value":"admin"},"createNotif":{"value":false}}}],"flowName":"Default Workflow 16","flowDesc":"Contact Location To Activity Feed Post"}', 1498674114, 1498678349),
(0, 'Default Workflow 17', 'Contact Web Activity To Create Action', 'WebActivityTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"WebActivityTrigger","options":{"url":{"value":"","operator":"="},"probability":{"value":"","operator":"="}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordCreateAction","options":{"subject":{"value":"Follow up with contact"},"description":{"value":"Follow up"}}}],"flowName":"Default Workflow 17","flowDesc":"Contact Web Activity To Create Action"}', 1498674217, 1498678374),
(0, 'Default Workflow 18', 'Macro Executed To Create Notification', 'MacroTrigger', 'Contacts', '{"version":"5.2","idCounter":3,"trigger":{"id":2,"type":"MacroTrigger","options":{"modelClass":{"value":"Contacts"}},"modelClass":"Contacts"},"items":[{"id":3,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"Macro has been executed!"}}}],"flowName":"Default Workflow 18","flowDesc":"Macro Executed To Create Notification"}', 1498674293, 1498678383),
(0, 'Default Workflow 19', 'Newsletter Email Clicked to Activity Feed Post', 'NewsletterEmailClickTrigger', NULL, '{"version":"5.2","idCounter":4,"trigger":{"id":2,"type":"NewsletterEmailClickTrigger","options":{"campaign":{"value":""},"url":{"value":"","operator":"="}}},"items":[{"id":4,"type":"X2FlowCreateEvent","options":{"type":{"value":"Social Post"},"text":{"value":"Newsletter email clicked!"},"visibility":{"value":"1"},"feed":{"value":""},"user":{"value":"admin"},"createNotif":{"value":false}}}],"flowName":"Default Workflow 19","flowDesc":"Newsletter Email Clicked to Activity Feed Post"}', 1498674380, 1498678392),
(0, 'Default Workflow 20', 'Newsletter Email Opened To Activity Feed Post', 'NewsletterEmailOpenTrigger', NULL, '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"NewsletterEmailOpenTrigger","options":[]},"items":[{"id":2,"type":"X2FlowCreateEvent","options":{"type":{"value":"Social Post"},"text":{"value":"Newsletter email opened!"},"visibility":{"value":"1"},"feed":{"value":""},"user":{"value":"admin"},"createNotif":{"value":false}}}],"flowName":"Default Workflow 20","flowDesc":"Newsletter Email Opened To Activity Feed Post"}', 1498674498, 1498678399),
(0, 'Default Workflow 21', 'Periodic Trigger To Notification', 'PeriodicTrigger', NULL, '{"version":"5.2","idCounter":4,"trigger":{"id":2,"type":"PeriodicTrigger","options":{"minutes":{"value":["2"]},"hours":{"value":["*"]},"dayOfMonth":{"value":["*"]},"month":{"value":["*"]},"dayOfWeek":{"value":["*"]}}},"items":[{"id":4,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"Reminder!"}}}],"flowName":"Default Workflow 21","flowDesc":"Periodic Trigger To Notification"}', 1498674574, 1498678408),
(0, 'Default Workflow 22', 'Record Created to Email Contact', 'RecordCreateTrigger', 'Contacts', '{"version":"5.2","idCounter":6,"trigger":{"id":5,"type":"RecordCreateTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":6,"type":"X2FlowRecordEmail","options":{"from":{"value":"-1"},"template":{"value":""},"subject":{"value":"You have been added to our CRM!"},"cc":{"value":""},"bcc":{"value":""},"logEmail":{"value":true},"doNotEmailLink":{"value":false},"body":{"value":"Hey just checking in with you!"}}}],"flowName":"Default Workflow 22","flowDesc":"Record Created to Email Contact"}', 1498674735, 1498678415),
(0, 'Default Workflow 23', 'Record Deleted To Add Tag To Parent Record', 'RecordDeleteTrigger', 'Accounts', '{"version":"5.2","idCounter":3,"trigger":{"id":1,"type":"RecordDeleteTrigger","options":[],"modelClass":"Accounts"},"items":[{"id":2,"type":"X2FlowRecordChange","options":{"linkField":{"value":"parentAccount"}},"linkType":"Accounts"},{"id":3,"type":"X2FlowRecordTag","options":{"action":{"value":"add"},"tags (optional)":{"value":"Deleted child record"}}}],"flowName":"Default Workflow 23","flowDesc":"Record Deleted To Add Tag To Parent Record"}', 1498674833, 1498678424),
(0, 'Default Workflow 24', 'Record Updated to Notification', 'RecordUpdateTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordUpdateTrigger","options":[],"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"Your record has been updated!"}}}],"flowName":"Default Workflow 24","flowDesc":"Record Updated to Notification"}', 1498674904, 1498678432),
(0, 'Default Workflow 25', 'Record Viewed To Tags Cleared', 'RecordViewTrigger', 'Accounts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordViewTrigger","options":[],"modelClass":"Accounts"},"items":[{"id":2,"type":"X2FlowRecordTag","options":{"action":{"value":"clear"},"tags (optional)":{"value":""}}}],"flowName":"Default Workflow 25","flowDesc":"Record Viewed To Tags Cleared"}', 1498674957, 1498678440),
(0, 'Default Workflow 26', 'Tag Added To Delete Record', 'RecordTagAddTrigger', 'Accounts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordTagAddTrigger","options":[],"modelClass":"Accounts"},"items":[{"id":2,"type":"X2FlowRecordDelete","options":[]}],"flowName":"Default Workflow 26","flowDesc":"Tag Added To Delete Record"}', 1498675022, 1498678450),
(0, 'Default Workflow 27', 'Tag Removed To Notification', 'RecordTagRemoveTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"RecordTagRemoveTrigger","options":{"modelClass":{"value":"Contacts"},"tags":{"value":"ToViewLater"}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"Reminder To View Contact"}}}],"flowName":"Default Workflow 27","flowDesc":"Tag Removed To Notification"}', 1498675102, 1498678458),
(0, 'Default Workflow 28', 'Targeted Content Request To Push Web Content', 'TargetedContentRequestTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"TargetedContentRequestTrigger","options":{"url":{"value":"","operator":"="},"content":{"value":""}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowPushWebContent","options":{"content":{"value":""}}}],"flowName":"Default Workflow 28","flowDesc":"Targeted Content Request To Push Web Content"}', 1498675180, 1498678467),
(0, 'Default Workflow 29', 'Unsubscribed To Campaign To Delete Record', 'CampaignUnsubscribeTrigger', 'Contacts', '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"CampaignUnsubscribeTrigger","options":{"campaign":{"value":""}},"modelClass":"Contacts"},"items":[{"id":2,"type":"X2FlowRecordDelete","options":[]}],"flowName":"Default Workflow 29","flowDesc":"Unsubscribed To Campaign To Delete Record"}', 1498675235, 1498678475),
(0, 'Default Workflow 30', 'Unsubscribed Newsletter To Notification', 'NewsletterUnsubscribeTrigger', NULL, '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"NewsletterUnsubscribeTrigger","options":{"campaign":{"value":""}}},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{assignedTo}"},"text":{"value":"Newsletter has 1 new unsubscriber"}}}],"flowName":"Default Workflow 30","flowDesc":"Unsubscribed Newsletter To Notification"}', 1498675305, 1498678484),
(0, 'Default Workflow 31', 'User Sign In To Activity Feed Post', 'UserLoginTrigger', NULL, '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"UserLoginTrigger","options":{"user":{"value":"Anyone","operator":"="}}},"items":[{"id":2,"type":"X2FlowCreateEvent","options":{"type":{"value":"Social Post"},"text":{"value":"User has signed in"},"visibility":{"value":"1"},"feed":{"value":""},"user":{"value":"admin"},"createNotif":{"value":false}}}],"flowName":"Default Workflow 31","flowDesc":"User Sign In To Activity Feed Post"}', 1498675364, 1498678492),
(0, 'Default Workflow 32', 'User Sign Out To Notification', 'UserLogoutTrigger', NULL, '{"version":"5.2","idCounter":2,"trigger":{"id":1,"type":"UserLogoutTrigger","options":{"user":{"value":"Anyone","operator":"="}}},"items":[{"id":2,"type":"X2FlowCreateNotif","options":{"user":{"value":"{user.username}"},"text":{"value":"User has signed out"}}}],"flowName":"Default Workflow 32","flowDesc":"User Sign Out To Notification"}', 1498675424, 1498678502);
/* x2entend */
