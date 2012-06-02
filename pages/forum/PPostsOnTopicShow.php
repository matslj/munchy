<?php
// ===========================================================================================
//
// PArticleShow.php
//
// Show the content of an article
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
//$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->UserIsMemberOfGroupAdminOrDie();

$urlToEditPost = "?p=article-edit&amp;article-id=";

// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$topicId	= $pc->GETisSetOrSetDefault('article-id', 0);
$userId		= isset($_SESSION['idUser']) ? $_SESSION['idUser'] : "";

// Always check whats coming in...
$pc->IsNumericOrDie($topicId, 0);


// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spPGetTopicDetailsAndPosts = DBSP_PGetTopicDetailsAndPosts;

$query = <<< EOD
CALL {$spPGetTopicDetailsAndPosts}({$topicId});
EOD;

// Perform the query
$results = Array();
$res = $db->MultiQuery($query);
$db->RetrieveAndStoreResultsFromMultiQuery($results);

// Get topic details
$row = $results[0]->fetch_object();
$title 		= $row->title;
$createdBy	= $row->creator;
$createdWhen	= $row->created;
$results[0]->close();

$row = $results[1]->fetch_object();
$lastPostBy 	= $row->lastpostby;
$lastPostWhen	= $row->lastpostwhen;
$numPosts	= $row->postcounter;
$results[1]->close();

// Get the list of posts
$posts = <<<EOD
<table id="topicPosts">
EOD;
while($row = $results[2]->fetch_object()) {

	$isEditable = "<a title='Edit this post' href='{$urlToEditPost}{$row->id}&amp;topic-id={$topicId}'>[edit]</a>";
	$isEditable = ($intFilter->IsUserMemberOfGroupAdminOrIsCurrentUser($row->userId)) ? $isEditable : '';

	$posts .= <<<EOD
<tr>
<td>
<img alt="Avatar" src='{$row->avatar}'><br>
<p class='small'>
{$row->username}<br>
{$row->created}
</p>
</td>
<td>
<div>
{$isEditable}
<a class='noUnderline' name='post-{$row->id}' title='Link to this post' href='#post-{$row->id}'>#</a>
</div>
{$row->content}
</td>
</tr>
EOD;
}
$posts .= "</table>";

$results[2]->close();
$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$urlToAddReply = "?p=article-edit&amp;topic-id={$topicId}";
$htmlMain = <<<EOD
<h1>{$title}</h1>
{$posts}
<p>
<a href='{$urlToAddReply}'>Add reply</a>
</p>
EOD;

$htmlLeft 	= "";
$htmlRight	= <<<EOD
<h3 class='columnMenu'>About This Topic</h3>
<p>
Created by {$createdBy} {$createdWhen}.<br>
</p>
<p>
$numPosts posts.<br>
</p>
<p>
Last edit by {$lastPostBy} {$lastPostWhen}<br>
</p>
EOD;

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage("Article: {$title}", $htmlLeft, $htmlMain, $htmlRight);
exit;

?>