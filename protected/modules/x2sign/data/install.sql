DROP TABLE IF EXISTS x2_sign_events;
/*&*/
CREATE TABLE `x2_sign_events` (
    id INT NOT NULL AUTO_INCREMENT,
    createDate BIGINT,
    lastUpdated BIGINT,
    IP VARCHAR(40) NOT NULL,
    envelopeId INT NOT NULL,
    documentId INT NOT NULL,
    signLinkId INT NOT NULL,
    type TINYINT(1) NOT NULL,
    PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS x2_sign_docs;
/*&*/
CREATE TABLE x2_sign_docs (
    id INT AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    docId INT NOT NULL,
    recipients VARCHAR(50) NOT NULL,
    fieldInfo TEXT NOT NULL,
    mediaId INT NOT NULL,
    PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS x2_signatures;
/*&*/
CREATE TABLE x2_signatures (
    id INT AUTO_INCREMENT,
    path VARCHAR(100),
    modelType VARCHAR(100) NOT NULL,
    modelId INT NOT NULL,
    emailAddress VARCHAR(100) DEFAULT NULL,
    signature VARCHAR(50) NOT NULL,
    IP VARCHAR(40),
    fingerprint BIGINT(20),
    PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS x2_sign_templates;
/*&*/
CREATE TABLE x2_sign_templates (
    id INT AUTO_INCREMENT,
    `name` VARCHAR(255),
    docId INT NOT NULL,
    PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS x2_sign_links;
/*&*/
CREATE TABLE x2_sign_links (
    id INT AUTO_INCREMENT,
    createDate BIGINT,
    `key` VARCHAR(255) UNIQUE,
    envelopeId INT NOT NULL,
    signRequired TINYINT(1) DEFAULT 0,
    signedDate BIGINT,
    position INT NOT NULL,
    sent TINYINT(1) DEFAULT 0,
    modelType VARCHAR(100) NOT NULL,
    modelId INT NOT NULL,
    emailAddress VARCHAR(100) DEFAULT NULL,
    PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS x2_sign_log;
/*&*/
DROP TABLE IF EXISTS x2_sign_fields;
/*&*/
CREATE TABLE x2_sign_fields (
    id INT AUTO_INCREMENT,
    `type` VARCHAR(20) NOT NULL,
    signDocId INT NOT NULL,
    envelopeId INT NOT NULL,
    fieldId VARCHAR(50) NOT NULL,
    value TEXT,
    PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS x2_sign_envelopes;
/*&*/
CREATE TABLE x2_sign_envelopes (
    id INT AUTO_INCREMENT,
    name TEXT,
    assignedTo VARCHAR(50) DEFAULT NULL,
    createDate BIGINT,
    lastUpdated BIGINT,
    completeDate BIGINT,
    updatedBy VARCHAR(50),
    signDocIds TEXT NOT NULL,
    completedDoc INT NULL,
    sender VARCHAR(50),
    `status` TINYINT(1),
    email TEXT,
    failed TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('x2sign', 'X2Sign', 1, 1, 1, 1, 0, 0, 1);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, custom, `type`, `required`, `readOnly`, linkType, searchable, isVirtual, relevance, uniqueConstraint, `safe`, keyType)
VALUES
('X2SignEvents', 'id',           'ID',            0, 'int',        0, 1, NULL, 0, 0, '',       1, 1, 'PRI'),
('X2SignEvents', 'createDate',   'Date Created',  0, 'date',       0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEvents', 'lastUpdated',  'Last Updated',  0, 'date',       0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEvents', 'envelopeId',   'Envelope',      0, 'int',        0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEvents', 'documentId',   'Document',      0, 'int',        0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEvents', 'IP',           'IP Address',    0, 'varchar',    0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEvents', 'signLinkId',   'Signer',        0, 'int',        0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEvents', 'type',         'Event Type',    0, 'int',        0, 1, NULL, 0, 0, '',       0, 1, NULL);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, custom, `type`, `required`, `readOnly`, linkType, searchable, isVirtual, relevance, uniqueConstraint, `safe`, keyType)
VALUES
('X2SignEnvelopes', 'id',           'ID',                 0, 'int',        0, 1, NULL, 0, 0, '',       1, 1, 'PRI'),
('X2SignEnvelopes', 'name',         'Name',               0, 'varchar',    0, 0, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'assignedTo',   'Assigned To',        0, 'assignment', 0, 0, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'createDate',   'Date Created',       0, 'date',       0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'completeDate', 'Date Completed',     0, 'date',       0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'lastUpdated',  'Last Updated',       0, 'date',       0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'updatedBy',    'Updated By',         0, 'varchar',    0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'completedDoc', 'Completed Document', 0, 'int',        0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'signDocIds',   'Signed Docs',        0, 'varchar',    0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'sender',       'Sender',             0, 'email',      0, 1, NULL, 0, 0, '',       0, 1, NULL),
('X2SignEnvelopes', 'status',       'Status',             0, 'int',        0, 0, NULL, 0, 0, '',       0, 1, NULL);
/*&*/
INSERT INTO x2_auth_item
(name, type, description, bizrule, data)
VALUES 
('X2signAdminAccess', 1, '', NULL, 'N;'),
('X2signBasicAccess', 1, '', NULL, 'N;'),
('X2signFullAccess', 1, '', NULL, 'N;'),
('X2signPrivateFullAccess', 1, '', NULL, 'N;'),
('X2signPrivateReadOnlyAccess',1,'',NULL,'N;'),
('X2signReadOnlyAccess',1,'',NULL,'N;'),
('X2signUpdateAccess',1,'',NULL,'N;'),
('X2signGetItems',0,'',NULL,'N;'),
('X2signView',0,'',NULL,'N;'),
('X2signCreate',0,'',NULL,'N;'),
('X2signUpdate',0,'',NULL,'N;'),
('X2signDelete',0,'',NULL,'N;'),
('X2signIndex',0,'',NULL,'N;'),
('X2signAdmin',0,'',NULL,'N;'),
('X2signReport',0,'',NULL,'N;'),
('X2signReportDefault',0,'',NULL,'N;'),
('X2signReportActivity',0,'',NULL,'N;'),
('X2signReportVelocity',0,'',NULL,'N;'),
('X2signGetList',0,'',NULL,'N;'),
('X2signGetListCompleted',0,'',NULL,'N;'),
('X2signSign', 0, 'Save fields that recipient filled out', NULL, 'N;'),
('X2signCreateSignature',0,'',NULL,'N;'),
('X2signSignDocsIndex',0,'',NULL,'N;'),
('X2signMinimumRequirements',1,'',NULL,'N;'),
('X2signViewPrivate',1,'',NULL,'N;'),
('X2signUpdatePrivate',1,'',NULL,'N;'),
('X2signPrivateUpdateAccess',1,'',NULL,'N;'),
('X2signSendDocs', 0, '', NULL, 'N;'),
('X2signGetFile', 0, 'Get PDF background for signing page', NULL, 'N;'),
('X2signSigningComplete', 0, 'Save fields that recipient filled out', NULL, 'N;'),
('X2signSignDocs', 0, 'Sign X2Sign document', NULL, 'N;'),
('X2signDeletePrivate',1,'',NULL,'N;');
/*&*/
INSERT INTO `x2_auth_item_child` (`parent`,`child`) VALUES
('administrator','X2signAdminAccess'),
('administrator','X2signFullAccess'),
('administrator','X2signUpdateAccess'),
('administrator','X2signBasicAccess'),
('administrator','X2signReadOnlyAccess'),
('X2signBasicAccess','X2signCreate'),
('X2signBasicAccess','X2signGetItems'),
('X2signBasicAccess','X2signReport'),
('X2signBasicAccess','X2signGetList'),
('X2signBasicAccess','X2signSendDocs'),
('X2signBasicAccess','X2signSignDocs'),
('GuestSiteFunctionsTask','X2signSign'),
('GuestSiteFunctionsTask','X2signCreateSignature'),
('X2signBasicAccess','X2signSignDocsIndex'),
('DefaultRole','X2signBasicAccess'),
('DefaultRole','X2signUpdateAccess'),
('DefaultRole','X2signReadOnlyAccess'),
('X2signReadOnlyAccess','X2signView'),
('X2signReadOnlyAccess','X2signMinimumRequirements'),
('X2signAdminAccess','X2signAdmin'),
('X2signAdminAccess','X2signMinimumRequirements'),
('X2signPrivateReadOnlyAccess','X2signMinimumRequirements'),
('X2signPrivateReadOnlyAccess','X2signViewPrivate'),
('X2signUpdateAccess','X2signMinimumRequirements'),
('X2signUpdateAccess','X2signUpdate'),
('X2signUpdatePrivate','X2signUpdate'),
('X2signPrivateUpdateAccess','X2signUpdatePrivate'),
('X2signDeletePrivate','X2signDelete'),
('X2signMinimumRequirements','X2signIndex'),
('X2signFullAccess','X2signDelete'),
('GuestSiteFunctionsTask','X2signSignDocs'),
('GuestSiteFunctionsTask','X2signGetFile'),
('GuestSiteFunctionsTask','X2signSigningComplete'),
('X2signPrivateFullAccess','X2signDeletePrivate');
