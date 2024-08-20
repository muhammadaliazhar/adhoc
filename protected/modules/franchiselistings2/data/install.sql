DROP TABLE IF EXISTS x2_franchiselistings2;
/*&*/
CREATE TABLE x2_franchiselistings2(
    id           INT NOT NULL AUTO_INCREMENT primary key,
    assignedTo   VARCHAR(250),
    `name`       VARCHAR(250) NOT NULL,
    nameId       VARCHAR(250) DEFAULT NULL,
    description  TEXT,
    createDate   INT,
    lastUpdated  INT,
    lastActivity BIGINT,
    updatedBy    VARCHAR(250),
    UNIQUE(nameId)
) COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('franchiselistings2', 'Franchise Listings2', 1, 1, 1, 1, 0, 1, 1);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Franchiselistings2', 'id',           'ID',            0, 'int',        0, 1, NULL, 0, 0, '',       1, 1, 'PRI'),
('Franchiselistings2', 'name',         'Name',          0, 'varchar',    1, 0, NULL, 0, 0, 'High',   0, 1, NULL),
('Franchiselistings2', 'nameId',       'NameID',        0, 'varchar',    0, 1, NULL, 0, 0, 'High',   0, 1, 'FIX'),
('Franchiselistings2', 'assignedTo',   'Assigned To',   0, 'assignment', 0, 0, NULL, 0, 0, '',       0, 1, NULL),
('Franchiselistings2', 'description',  'Description',   0, 'text',       0, 0, NULL, 0, 0, 'Medium', 0, 1, NULL),
('Franchiselistings2', 'createDate',   'Create Date',   0, 'dateTime',   0, 1, NULL, 0, 0, '',       0, 1, NULL),
('Franchiselistings2', 'lastUpdated',  'Last Updated',  0, 'dateTime',   0, 1, NULL, 0, 0, '',       0, 1, NULL),
('Franchiselistings2', 'lastActivity', 'Last Activity', 0, 'dateTime',   0, 1, NULL, 0, 0, '',       0, 1, NULL),
('Franchiselistings2', 'updatedBy',    'Updated By',    0, 'assignment', 0, 1, NULL, 0, 0, '',       0, 1, NULL);
