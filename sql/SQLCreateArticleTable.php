<?php
// ===========================================================================================
//
// SQLCreateArticleTable.php
//
// SQL statements to create the tables for the Article tables.
//
// WARNING: Do not forget to check input variables for SQL injections.
//
// Author: Mikael Roos
//


// Get the tablenames
$tArticle 		= DBT_Article;
$tUser 			= DBT_User;
$tGroup 		= DBT_Group;
$tGroupMember 	= DBT_GroupMember;
$tStatistics	= DBT_Statistics;

// Get the SP names
$spPGetArticleDetailsAndArticleList	= DBSP_PGetArticleDetailsAndArticleList;
$spPGetArticleDetails				= DBSP_PGetArticleDetails;
$spPInsertOrUpdateArticle			= DBSP_PInsertOrUpdateArticle;
$spPGetLatestTopicsList                         = DBSP_PGetLatestTopicsList;
$spPGetTopicDetailsAndPosts                     = DBSP_PGetTopicDetailsAndPosts;
$spPGetTopicFirstEntryDetails                   = DBSP_PGetTopicFirstEntryDetails;
$spPGetTopicLastEntryDetails                   = DBSP_PGetTopicLastEntryDetails;
$spPGetArticleAndTopicDetails                  = DBSP_PGetArticleAndTopicDetails;

// Get the UDF names
$udfFCheckUserIsOwnerOrAdmin	= DBUDF_FCheckUserIsOwnerOrAdmin;

// Get the trigger names
$trAddArticle					= DBTR_TAddArticle;

// Create the query
$query = <<<EOD

--
-- Table for the Article
--
DROP TABLE IF EXISTS {$tArticle};
CREATE TABLE {$tArticle} (

  -- Primary key(s)
  idArticle INT AUTO_INCREMENT NOT NULL PRIMARY KEY,

  -- Foreign keys
  Article_idUser INT NOT NULL,
  FOREIGN KEY (Article_idUser) REFERENCES {$tUser}(idUser),

  -- Attributes
  titleArticle VARCHAR(256) NOT NULL,
  contentArticle BLOB NOT NULL,
  createdArticle DATETIME NOT NULL,
  modifiedArticle DATETIME NULL,
  deletedArticle DATETIME NULL,
  siblingArticleId INT NULL
);


--
-- SP to insert or update article
-- If article id is 0 then insert, else update
--
DROP PROCEDURE IF EXISTS {$spPInsertOrUpdateArticle};
CREATE PROCEDURE {$spPInsertOrUpdateArticle}
(
	INOUT aArticleId INT,
	IN aUserId INT,
	IN aTitle VARCHAR(256),
	IN aContent BLOB,
        IN aThreadId INT
)
BEGIN
	IF aArticleId = 0 and aThreadId = 0 THEN
	BEGIN
		INSERT INTO {$tArticle}
			(Article_idUser, titleArticle, contentArticle, createdArticle)
			VALUES
			(aUserId, aTitle, aContent, NOW());
		SET aArticleId = LAST_INSERT_ID();
                UPDATE {$tArticle} SET
			siblingArticleId = aArticleId
		WHERE
			idArticle = aArticleId
		LIMIT 1;
	END;
        ELSEIF aArticleId = 0 and aThreadId > 0 THEN
        BEGIN
                INSERT INTO {$tArticle}
			(Article_idUser, titleArticle, contentArticle, createdArticle)
			VALUES
			(aUserId, '', aContent, NOW());
                SET aArticleId = LAST_INSERT_ID();
                UPDATE {$tArticle} SET
			siblingArticleId = aThreadId
		WHERE
			idArticle = aArticleId
		LIMIT 1;
        END;
	ELSE
	BEGIN
		UPDATE {$tArticle} SET
			titleArticle = aTitle,
			contentArticle 	= aContent,
			modifiedArticle	= NOW()
		WHERE
			idArticle = aArticleId  AND
			{$udfFCheckUserIsOwnerOrAdmin}(aArticleId, aUserId)
		LIMIT 1;
	END;
	END IF;
END;


--
-- SP to get the contents of an article
--
DROP PROCEDURE IF EXISTS {$spPGetArticleDetails};
CREATE PROCEDURE {$spPGetArticleDetails}
(
	IN aArticleId INT,
	IN aUserId INT
)
BEGIN
	SELECT
		A.titleArticle AS title,
		A.contentArticle AS content,
		A.createdArticle AS created,
		A.modifiedArticle AS modified,
		COALESCE(A.modifiedArticle, A.createdArticle) AS latest,
		U.nameUser AS username,
                A.siblingArticleId AS siblingId
	FROM {$tArticle} AS A
		INNER JOIN {$tUser} AS U
			ON A.Article_idUser = U.idUser
	WHERE
		idArticle = aArticleId AND
		deletedArticle IS NULL AND
		{$udfFCheckUserIsOwnerOrAdmin}(aArticleId, aUserId);
END;

--
-- SP to get the contents of an article and provide a list of the latest articles
--
-- Limit does not accept a varible
-- http://bugs.mysql.com/bug.php?id=11918
--
DROP PROCEDURE IF EXISTS {$spPGetArticleDetailsAndArticleList};
CREATE PROCEDURE {$spPGetArticleDetailsAndArticleList}
(
	IN aArticleId INT,
	IN aUserId INT
)
BEGIN
	CALL {$spPGetArticleDetails}(aArticleId, aUserId);

	SELECT
		idArticle AS id,
		titleArticle AS title,
		COALESCE(modifiedArticle, createdArticle) AS latest
	FROM {$tArticle}
	WHERE
		Article_idUser = aUserId AND
		deletedArticle IS NULL
	ORDER BY modifiedArticle, createdArticle
	LIMIT 20;
END;

--
-- SP to get the contents of an article and provide a list of the latest articles
--
-- Limit does not accept a varible
-- http://bugs.mysql.com/bug.php?id=11918
--
DROP PROCEDURE IF EXISTS {$spPGetArticleAndTopicDetails};
CREATE PROCEDURE {$spPGetArticleAndTopicDetails}
(
        IN aTopicId INT,
	IN aArticleId INT,
	IN aUserId INT
)
BEGIN
        CALL {$spPGetTopicFirstEntryDetails}(aTopicId);
	CALL {$spPGetArticleDetails}(aArticleId, aUserId);
END;

--
-- ***************************** TOPIC *********************************
-- **                                                                 **
-- ** The concept of topics is an addon to articles.                  **
-- ** By adding the attribute siblingArticleId to the article-table   **
-- ** it is possible to group articles under a topic.                 **
-- ** siblingArticleId has the same value as the articleId of the     **
-- ** first article in the 'topic'. So the defining article has       **
-- ** siblingArticleId == articleId.                                  **
-- **                                                                 **
-- ** So the concept of topics is a bit virtual here.                 **
-- **                                                                 **
-- ***************************** TOPIC *********************************
--

--
-- SP to get the contents of a topic. Remember - topic id (siblingId) is the
-- same as articleId for the first article.
--
DROP PROCEDURE IF EXISTS {$spPGetTopicFirstEntryDetails};
CREATE PROCEDURE {$spPGetTopicFirstEntryDetails}
(
	IN aTopicId INT
)
BEGIN
	SELECT
		A.titleArticle AS title,
		A.contentArticle AS content,
		A.createdArticle AS created,
		A.modifiedArticle AS modified,
		COALESCE(A.modifiedArticle, A.createdArticle) AS latest,
		U.nameUser AS creator
	FROM {$tArticle} AS A
		INNER JOIN {$tUser} AS U
			ON A.Article_idUser = U.idUser
	WHERE
		idArticle = aTopicId AND
		deletedArticle IS NULL;
END;

--
-- SP to get the contents of a topic. Remember - topic id (siblingId) is the
-- same as articleId for the first article.
--
DROP PROCEDURE IF EXISTS {$spPGetTopicLastEntryDetails};
CREATE PROCEDURE {$spPGetTopicLastEntryDetails}
(
	IN aTopicId INT
)
BEGIN
        SELECT
		COALESCE(A.modifiedArticle, A.createdArticle) AS lastpostwhen,
		U.nameUser AS lastpostby,
                count(A.idArticle) AS postcounter
	FROM {$tArticle} AS A
        LEFT JOIN {$tArticle} B ON (A.siblingArticleId = B.siblingArticleId AND COALESCE(A.modifiedArticle, A.createdArticle) < COALESCE(B.modifiedArticle, B.createdArticle))
        INNER JOIN {$tUser} AS U
            ON A.Article_idUser = U.idUser
        WHERE
            A.deletedArticle IS NULL AND
            B.idArticle IS NULL AND
            A.siblingArticleId = aTopicId
        GROUP BY A.idArticle;
END;

--
-- SP to get a list of the 20 most recent topics
--
-- Limit does not accept a varible
-- http://bugs.mysql.com/bug.php?id=11918
--
DROP PROCEDURE IF EXISTS {$spPGetLatestTopicsList};
CREATE PROCEDURE {$spPGetLatestTopicsList}
()
BEGIN
  -- Creating a temporary table (if it does not already exist)
  CREATE TEMPORARY TABLE IF NOT EXISTS OUT_TEMP(latest DATETIME, latestBy CHAR(100), siblingId INT);
  INSERT INTO OUT_TEMP(latest, latestBy, siblingId)
    SELECT
        COALESCE(A.modifiedArticle, A.createdArticle) AS latest,
        U.nameUser,
        A.siblingArticleId
    FROM {$tArticle} AS A
        LEFT JOIN {$tArticle} B ON (A.siblingArticleId = B.siblingArticleId AND COALESCE(A.modifiedArticle, A.createdArticle) < COALESCE(B.modifiedArticle, B.createdArticle))
        INNER JOIN {$tUser} AS U
                ON A.Article_idUser = U.idUser
        WHERE
            A.deletedArticle IS NULL AND
            B.idArticle IS NULL
        GROUP BY A.siblingArticleId
        LIMIT 20;

    SELECT
        A.titleArticle AS title,
        COUNT(A.idArticle) AS postcounter,
        B.latest,
        B.latestBy AS latestby,
        A.siblingArticleId AS siblingId
    FROM {$tArticle} AS A
        INNER JOIN OUT_TEMP AS B
                ON A.siblingArticleId = B.siblingId
    WHERE
        A.deletedArticle IS NULL AND
        A.titleArticle IS NOT NULL
    GROUP BY A.siblingArticleId
    ORDER BY B.latest DESC
    LIMIT 20;
END;

--
-- SP to get all articles within a topic as well as general topic information.
--
-- Limit does not accept a varible
-- http://bugs.mysql.com/bug.php?id=11918
--
DROP PROCEDURE IF EXISTS {$spPGetTopicDetailsAndPosts};
CREATE PROCEDURE {$spPGetTopicDetailsAndPosts}
(
	IN aTopicId INT
)
BEGIN
	CALL {$spPGetTopicFirstEntryDetails}(aTopicId);
        CALL {$spPGetTopicLastEntryDetails}(aTopicId);

	SELECT
		A.idArticle AS id,
		A.titleArticle AS title,
                A.contentArticle AS content,
                A.Article_idUser AS userId,
                U.nameUser AS username,
                A.createdArticle AS created,
		A.modifiedArticle AS modified,
		COALESCE(modifiedArticle, createdArticle) AS latest,
                U.avatarUser AS avatar
	FROM {$tArticle} AS A
		INNER JOIN {$tUser} AS U
			ON A.Article_idUser = U.idUser
	WHERE
		siblingArticleId = aTopicId AND
		deletedArticle IS NULL
	ORDER BY createdArticle, modifiedArticle
	LIMIT 20;
END;


--
--  Create UDF that checks if user owns article or is member of group adm.
--
DROP FUNCTION IF EXISTS {$udfFCheckUserIsOwnerOrAdmin};
CREATE FUNCTION {$udfFCheckUserIsOwnerOrAdmin}
(
	aArticleId INT,
	aUserId INT
)
RETURNS BOOLEAN
READS SQL DATA
BEGIN
	DECLARE isAdmin INT;
	DECLARE isOwner INT;

	SELECT idUser INTO isAdmin
	FROM {$tUser} AS U
		INNER JOIN {$tGroupMember} AS GM
			ON U.idUser = GM.GroupMember_idUser
		INNER JOIN {$tGroup} AS G
			ON G.idGroup = GM.GroupMember_idGroup
	WHERE
		idGroup = 'adm' AND
		idUser = aUserId;

	SELECT idUser INTO isOwner
	FROM {$tUser} AS U
		INNER JOIN {$tArticle} AS A
			ON U.idUser = A.Article_idUser
	WHERE
		idArticle = aArticleId AND
		idUser = aUserId;

	RETURN (isAdmin OR isOwner);
END;
                


--
-- Create trigger for Statistics
-- Add +1 when new article is created
--
DROP TRIGGER IF EXISTS {$trAddArticle};
CREATE TRIGGER {$trAddArticle}
AFTER INSERT ON {$tArticle}
FOR EACH ROW
BEGIN
  UPDATE {$tStatistics}
  SET
  	numOfArticlesStatistics = numOfArticlesStatistics + 1
  WHERE
  	Statistics_idUser = NEW.Article_idUser;
END;




-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- Insert some default topics
--

SET @aArticleId = 0;
SET @aTopicId = 0;
CALL {$spPInsertOrUpdateArticle}(@aArticleId, 2, 'Första tråden!', 'Hej det här är den första tråden', @aTopicId);

SET @aArticleId = 0;
SET @aTopicId = 0;
CALL {$spPInsertOrUpdateArticle}(@aArticleId, 2, 'Andra tråden!', 'Hej det här är den andra tråden', @aTopicId);

SET @aArticleId = 0;
SET @aTopicId = 0;
CALL {$spPInsertOrUpdateArticle}(@aArticleId, 2, 'Tredje tråden!', 'Hej det här är den tredje tråden', @aTopicId);

EOD;


?>