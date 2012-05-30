<?php
// ===========================================================================================
//
// SQLInsertOrUpdateArticle.php
//
// SQL statements to insert/update an article.
//
// WARNING: Do not forget to check input variables for SQL injections.
//
// Author: Mikael Roos
//


// Prevent SQL injections
global $user, $password;
$user 		= $mysqli->real_escape_string($user);
$password 	= $mysqli->real_escape_string($password);

// Create the query
$query = <<< EOD
CALL PInsertOrUpdateArticle(@aArticleId, aUserId, aTitle, aContent);
SELECT @aArticleId AS id;

EOD;


?>