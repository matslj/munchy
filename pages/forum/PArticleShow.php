<?php
// ===========================================================================================
//
// PArticleEdit.php
//
// A WYSIWYG editor
//
// Author: Mikael Roos, mos@bth.se
//


// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = new CPageController();
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
$articleId	= $pc->GETisSetOrSetDefault('article-id', 0);
$topicId	= $pc->GETisSetOrSetDefault('topic-id', 0);
$userId		= $_SESSION['idUser'];

// Always check whats coming in...
$pc->IsNumericOrDie($topicId, 0);
$pc->IsNumericOrDie($articleId, 0);

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$title 		= "";
$content 	= "";
$siblingId      = "";

// Connect
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spGetArticleAndTopicDetails	= DBSP_PGetArticleAndTopicDetails;

$query = <<< EOD
CALL {$spGetArticleAndTopicDetails}({$topicId}, {$articleId}, '{$userId}');
EOD;

// Perform the query
$results = Array();
$res = $db->MultiQuery($query);
$db->RetrieveAndStoreResultsFromMultiQuery($results);
$saved = 'Not yet';

// Get article details
$row = $results[0]->fetch_object();
if ($row) {
    $title 	= $row->title;
}
$results[0]->close();

// Get article details
$row = $results[1]->fetch_object();
if ($row) {
    $content 	= $row->content;
    $saved	= empty($row->latest) ? 'Not yet' : $row->latest;
    $siblingId  = $row->siblingId;
}
$results[1]->close();

$mysqli->close();

$isEditable = "<a title='Edit this post' href='?p=article-edit&amp;article-id={$articleId}&amp;topic-id={$topicId}'>[edit]</a>";
$isEditable = ($intFilter->IsUserMemberOfGroupAdminOrIsCurrentUser($userId)) ? $isEditable : '';
// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlMain = <<<EOD
    <h2>I tråden: "{$title}"</h2>
    <p>
    {$content}
    </p>
    <p>
        {$isEditable}
    </p>
EOD;

$htmlLeft 	= "";
$htmlRight	= "";
if (!empty($siblingId)) {
    $htmlRight = <<<EOD
    <h3 class='columnMenu'>Topic</h3>
    <p>
    <a href='?p=forum-topic&amp;article-id={$siblingId}'>{$title}</a>
    </p>
EOD;
}



// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Show article', $htmlLeft, $htmlMain, $htmlRight);
exit;

?>