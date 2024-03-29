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
$spAuthenticateUser = DBSP_AuthenticateUser;
$spCreateUser = DBSP_CreateUser;
$trInsertUser	= DBTR_TInsertUser;
$spGetUserDetails = DBSP_GetUserDetails;
$spSetUserDetails = DBSP_SetUserDetails;
$spSetUserPassword = DBSP_SetUserPassword;
$spSetUserEmail = DBSP_SetUserEmail;
$spSetUserAvatar = DBSP_SetUserAvatar;
$spSetUserGravatar = DBSP_SetUserGravatar;

$fGetGravatarLinkFromEmail = DBUDF_GetGravatarLinkFromEmail;

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
  nameUser CHAR(100),
  emailUser CHAR(100) NULL UNIQUE,
  passwordUser CHAR(32) NOT NULL,
  avatarUser VARCHAR(256),
  gravatarUser VARCHAR(100) NULL
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
-- SP to create a new user
--
DROP PROCEDURE IF EXISTS {$spCreateUser};
CREATE PROCEDURE {$spCreateUser}
(
	IN anAccountUser CHAR(20),
	IN aPassword CHAR(32)
)
BEGIN
        INSERT INTO {$tUser}
                (accountUser, passwordUser)
                VALUES
                (anAccountUser, md5(aPassword));
        INSERT INTO {$tGroupMember} (GroupMember_idUser, GroupMember_idGroup)
	VALUES (LAST_INSERT_ID(), 'usr');
        CALL {$spAuthenticateUser}(anAccountUser,aPassword);
END;

--
-- SP to authenticate a user
--
DROP PROCEDURE IF EXISTS {$spAuthenticateUser};
CREATE PROCEDURE {$spAuthenticateUser}
(
	IN anAccountUserOrEmail CHAR(100),
	IN aPassword CHAR(32)
)
BEGIN
	SELECT
	idUser AS id,
	accountUser AS account,
        nameUser AS name,
        emailUser AS email,
        avatarUser AS avatar,
	GroupMember_idGroup AS groupid
FROM {$tUser} AS U
	INNER JOIN {$tGroupMember} AS GM
		ON U.idUser = GM.GroupMember_idUser
WHERE
        (
	accountUser	= anAccountUserOrEmail AND
	passwordUser 	= md5(aPassword)
        )
        OR
        (
	emailUser	= anAccountUserOrEmail AND
	passwordUser 	= md5(aPassword)
        )
;
END;
        
--
-- SP to get user details
--
DROP PROCEDURE IF EXISTS {$spGetUserDetails};
CREATE PROCEDURE {$spGetUserDetails}
(
	IN anIdUser INT
)
BEGIN
	SELECT
	idUser AS id,
	accountUser AS account,
        nameUser AS name,
        emailUser AS email,
        avatarUser AS avatar,
        gravatarUser AS gravatar,
        {$fGetGravatarLinkFromEmail}(gravatarUser, 60) AS gravatarsmall,
	GroupMember_idGroup AS groupid,
        nameGroup AS groupname
FROM {$tUser} AS U
	INNER JOIN {$tGroupMember} AS GM
		ON U.idUser = GM.GroupMember_idUser
        INNER JOIN {$tGroup} AS G
                ON GM.GroupMember_idGroup = G.idGroup
WHERE
	idUser = anIdUser
;
END;
      
--
-- SP to set user details
--
DROP PROCEDURE IF EXISTS {$spSetUserPassword};
CREATE PROCEDURE {$spSetUserPassword}
(
        IN anIdUser INT,
        IN aPassword CHAR(32)
)
BEGIN
        UPDATE {$tUser} SET
                passwordUser = md5(aPassword)
        WHERE
                idUser = anIdUser
        LIMIT 1;
END;
        
--
-- SP to set user details
--
DROP PROCEDURE IF EXISTS {$spSetUserEmail};
CREATE PROCEDURE {$spSetUserEmail}
(
        IN anIdUser INT,
        IN anEmailUser CHAR(100)
)
BEGIN
        UPDATE {$tUser} SET
                emailUser = anEmailUser
        WHERE
                idUser = anIdUser
        LIMIT 1;
END;
        
--
-- SP to set user details
--
DROP PROCEDURE IF EXISTS {$spSetUserAvatar};
CREATE PROCEDURE {$spSetUserAvatar}
(
        IN anIdUser INT,
        IN anAvatarUser VARCHAR(256)
)
BEGIN
        UPDATE {$tUser} SET
                avatarUser = anAvatarUser
        WHERE
                idUser = anIdUser
        LIMIT 1;
END;
        
--
-- SP to set user details
--
DROP PROCEDURE IF EXISTS {$spSetUserGravatar};
CREATE PROCEDURE {$spSetUserGravatar}
(
        IN anIdUser INT,
        IN aGravatarUser VARCHAR(256)
)
BEGIN
        UPDATE {$tUser} SET
                gravatarUser = aGravatarUser
        WHERE
                idUser = anIdUser
        LIMIT 1;
END;
        
--
-- SP to set user details
--
DROP PROCEDURE IF EXISTS {$spSetUserDetails};
CREATE PROCEDURE {$spSetUserDetails}
(
        IN anIdUser INT,
        IN aNameUser CHAR(100),
        IN anEmailUser CHAR(100),
        IN anAvatarUser VARCHAR(256),
        IN aPassword CHAR(32)
)
BEGIN
        UPDATE {$tUser} SET
                nameUser = aNameUser,
                emailUser = anEmailUser,
                avatarUser = anAvatarUser,
                passwordUser = md5(aPassword)
        WHERE
                idUser = anIdUser
        LIMIT 1;
END;
        
-- 
-- Function to create a link to gravatar.com from an emailadress.
-- http://en.gravatar.com/site/implement/url
--
DROP FUNCTION IF EXISTS {$fGetGravatarLinkFromEmail};
CREATE FUNCTION {$fGetGravatarLinkFromEmail}
(	
    aEmail CHAR(100),	
    aSize INT
)
RETURNS CHAR(255)
READS SQL DATA
BEGIN	
    DECLARE link CHAR(255);
    SELECT CONCAT('http://www.gravatar.com/avatar/', MD5(LOWER(aEmail)), '.jpg?s=', aSize)
        INTO link;
    RETURN link;
END;

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