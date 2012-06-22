<?php
// ===========================================================================================
//
// PArticleSave.php
//
// Saves an article to database
//
// Author: Mikael Roos, mos@bth.se
//


// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = CPageController::getInstance();
//$pc->LoadLanguage(__FILE__);


// -------------------------------------------------------------------------------------------
//
// Interception Filter, controlling access, authorithy and other checks.
//
$intFilter = new CInterceptionFilter();

$intFilter->FrontControllerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->UserIsMemberOfGroupAdminOrDie();


// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$title		= $pc->POSTisSetOrSetDefault('title', 'No title');
$content	= $pc->POSTisSetOrSetDefault('content', 'No content');
$articleId	= $pc->POSTisSetOrSetDefault('article_id', 0);
$topicId	= $pc->POSTisSetOrSetDefault('topic_id', 0);
$action		= $pc->POSTisSetOrSetDefault('action', '');
$success	= $pc->POSTisSetOrSetDefault('redirect_on_success', '');
$failure	= $pc->POSTisSetOrSetDefault('redirect_on_failure', '');
$userId		= $_SESSION['idUser'];

// Always check whats coming in...
$pc->IsNumericOrDie($articleId, 0);
$pc->IsNumericOrDie($topicId, 0);

// Clean up HTML-tags
$tagsAllowed = '<h1><h2><h3><h4><h5><h6><p><a><br><i><em><li><ol><ul>';
$title 		= strip_tags($title, $tagsAllowed);
$content 	= strip_tags($content, $tagsAllowed);

// Kolla vilken action som gäller
// Om action == publish -> ändra redirecten till show
if (strcmp($action, 'publish') == 0) {
    $success = 'article-show&article-id=%1$d&topic-id=%2$d';
} else if (strcmp($action, 'draft') == 0) {
    $success = 'article-edit&article-id=%1$d&topic-id=%2$d';
}

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spPInsertOrUpdateArticle			= DBSP_PInsertOrUpdateArticle;

// Create the query
$query = <<< EOD
SET @aArticleId = {$articleId};
CALL {$spPInsertOrUpdateArticle}(@aArticleId, '{$userId}', '{$title}', '{$content}', {$topicId});
SELECT
    @aArticleId AS id,
    NOW() AS timestamp
;
EOD;

// Perform the query
$res = $db->MultiQuery($query);

// Use results
$results = Array();
$db->RetrieveAndStoreResultsFromMultiQuery($results);

// Store inserted/updated article id
$row = $results[2]->fetch_object();
$articleId = $row->id;
$timestamp = $row->timestamp;

$results[2]->close();
$mysqli->close();

// Specialfall för när man startar en ny tråd; efter att artikeln har skapats
// så får topicId samma nr som $articleId.
if ($topicId == 0) {
    $topicId = $articleId;
}

// -------------------------------------------------------------------------------------------
//
// Redirect to another page
// Support $redirect to be local uri within site or external site (starting with http://)
//
if(strcmp($success, 'json') == 0) {
	$json = <<<EOD
{
	"topicId": {$topicId},
	"articleId": {$articleId},
        "timestamp": "{$timestamp}"
}
EOD;

	echo $json;

} else {
$pc->RedirectTo(sprintf($success, $articleId, $topicId));
}
exit;

?>