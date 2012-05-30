<?php
// ===========================================================================================
//
// SQLCreateUserAndGroupTables.php
//
// SQL statements to creta the tables for the User and group tables.
//
// WARNING: Do not forget to check input variables for SQL injections.
//
// Author: Mikael Roos
//

$imageLink = WS_IMAGES;

// Get the tablenames
$tArticle 		= DBT_Article;
$tUser 			= DBT_User;
$tGroup 		= DBT_Group;
$tGroupMember 	= DBT_GroupMember;
$tStatistics 	= DBT_Statistics;

// Get the SP/UDF/trigger names
$trInsertUser	= DBTR_TInsertUser;

// Create the query
$query = <<<EOD
DROP TABLE IF EXISTS {$tStatistics};
DROP TABLE IF EXISTS {$tArticle};

DROP TABLE IF EXISTS {$tGroupMember};
DROP TABLE IF EXISTS {$tUser};
DROP TABLE IF EXISTS {$tGroup};

--
-- Table for the User
--
CREATE TABLE {$tUser} (

  -- Primary key(s)
  idUser INT AUTO_INCREMENT NOT NULL PRIMARY KEY,

  -- Attributes
  accountUser CHAR(20) NOT NULL UNIQUE,
  nameUser CHAR(100) NOT NULL,
  emailUser CHAR(100) NOT NULL,
  passwordUser CHAR(32) NOT NULL,
  avatarUser VARCHAR(256) NULL
);


--
-- Table for the Group
--
CREATE TABLE {$tGroup} (

  -- Primary key(s)
  idGroup CHAR(3) NOT NULL PRIMARY KEY,

  -- Attributes
  nameGroup CHAR(40) NOT NULL
);


--
-- Table for the GroupMember
--
CREATE TABLE {$tGroupMember} (

  -- Primary key(s)
  --
  -- The PK is the combination of the two foreign keys, see below.
  --

  -- Foreign keys
  GroupMember_idUser INT NOT NULL,
  GroupMember_idGroup CHAR(3) NOT NULL,

  FOREIGN KEY (GroupMember_idUser) REFERENCES {$tUser}(idUser),
  FOREIGN KEY (GroupMember_idGroup) REFERENCES {$tGroup}(idGroup),

  PRIMARY KEY (GroupMember_idUser, GroupMember_idGroup)

  -- Attributes

);


--
-- Table for the Statistics
--
DROP TABLE IF EXISTS {$tStatistics};
CREATE TABLE {$tStatistics} (

  -- Primary key(s)
  -- Foreign keys
  Statistics_idUser INT NOT NULL,

  FOREIGN KEY (Statistics_idUser) REFERENCES {$tUser}(idUser),
  PRIMARY KEY (Statistics_idUser),

  -- Attributes
  numOfArticlesStatistics INT NOT NULL DEFAULT 0
);


--
-- Create trigger for Statistics
-- Add row when new user is created
--
DROP TRIGGER IF EXISTS {$trInsertUser};
CREATE TRIGGER {$trInsertUser}
AFTER INSERT ON {$tUser}
FOR EACH ROW
BEGIN
  INSERT INTO {$tStatistics} (Statistics_idUser) VALUES (NEW.idUser);
END;


--
-- Add default user(s)
--
INSERT INTO {$tUser} (accountUser, emailUser, nameUser, passwordUser, avatarUser)
VALUES ('mikael', 'mos@bth.se', 'Mikael Roos', md5('hemligt'), '{$imageLink}/man_60x60.png');
INSERT INTO {$tUser} (accountUser, emailUser, nameUser, passwordUser, avatarUser)
VALUES ('doe', 'doe@bth.se', 'John/Jane Doe', md5('doe'), '{$imageLink}/woman_60x60.png');


--
-- Add default groups
--
INSERT INTO {$tGroup} (idGroup, nameGroup) VALUES ('adm', 'Administrators of the site');
INSERT INTO {$tGroup} (idGroup, nameGroup) VALUES ('usr', 'Regular users of the site');


--
-- Add default groupmembers
--
INSERT INTO {$tGroupMember} (GroupMember_idUser, GroupMember_idGroup)
	VALUES ((SELECT idUser FROM {$tUser} WHERE accountUser = 'doe'), 'usr');
INSERT INTO {$tGroupMember} (GroupMember_idUser, GroupMember_idGroup)
	VALUES ((SELECT idUser FROM {$tUser} WHERE accountUser = 'mikael'), 'adm');


EOD;


?>