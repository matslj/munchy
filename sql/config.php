<?php
// ===========================================================================================
//
// config.php
//
// Config-file for database and SQL related issues. All SQL-statements are usually stored in this
// directory (TP_SQLPATH). This files contains global definitions for table names and so.
//
// Author: Mikael Roos, mos@bth.se
//

// -------------------------------------------------------------------------------------------
//
// Settings for the database connection
//
define('DB_HOST', 		'localhost');	// The database host
define('DB_USER', 		'mats');		// The username of the database
define('DB_PASSWORD', 	'hemligt');		// The users password
define('DB_DATABASE', 	'sanxion');		// The name of the database to use

//
// The following supports having many databases in one database by using table/view prefix.
//
define('DB_PREFIX', 'aut_');    // Prefix to use infront of tablename and views

// -------------------------------------------------------------------------------------------
//
// Define the names for the database (tables, views, procedures, functions, triggers)
//
define('DBT_User', 		DB_PREFIX . 'User');
define('DBT_Group', 		DB_PREFIX . 'Group');
define('DBT_GroupMember',	DB_PREFIX . 'GroupMember');
define('DBT_Statistics',	DB_PREFIX . 'Statistics');
define('DBT_Article',		DB_PREFIX . 'Article');

// Stored procedures
define('DBSP_PGetArticleDetailsAndArticleList',	DB_PREFIX . 'PGetArticleDetailsAndArticleList');
define('DBSP_PGetArticleDetails',			DB_PREFIX . 'PGetArticleDetails');
define('DBSP_PInsertOrUpdateArticle',			DB_PREFIX . 'PInsertOrUpdateArticle');
define('DBSP_PGetLatestTopicsList',			DB_PREFIX . 'PGetLatestTopicsList');
define('DBSP_PGetTopicDetailsAndPosts',			DB_PREFIX . 'PGetTopicDetailsAndPosts');
define('DBSP_PGetTopicFirstEntryDetails',		DB_PREFIX . 'PGetTopicFirstEntryDetails');
define('DBSP_PGetTopicLastEntryDetails',		DB_PREFIX . 'PGetTopicLastEntryDetails');
define('DBSP_PGetArticleAndTopicDetails',		DB_PREFIX . 'PGetArticleAndTopicDetails');

// User Defined Functions UDF and Stored procedures
define('DBSP_AuthenticateUser',		DB_PREFIX . 'PAuthenticateUser');
define('DBSP_CreateUser',		DB_PREFIX . 'PCreateUser');
define('DBUDF_FCheckUserIsOwnerOrAdmin',	DB_PREFIX . 'FCheckUserIsOwnerOrAdmin');

// Triggers
define('DBTR_TInsertUser',		DB_PREFIX . 'TInsertUser');
define('DBTR_TAddArticle',		DB_PREFIX . 'TAddArticle');
?>