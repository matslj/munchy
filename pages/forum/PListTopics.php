<?php
// ===========================================================================================
//
// PTopicsList.php
//
// A WYSIWYG editor
//
// Author: Mats Ljungquist
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

// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//

// Connect
$db 	= new CDatabaseController();
$mysqli = $db->Connect();

// Get the SP names
$spPGetTopicList	= DBSP_PGetLatestTopicsList;

$query = <<< EOD
CALL {$spPGetTopicList}();
EOD;

// Perform the query
$results = Array();
$res = $db->MultiQuery($query);
$db->RetrieveAndStoreResultsFromMultiQuery($results);

// Get the list of topics
$list = <<<EOD
<table id="topicList">
<tr>
<th>
Topic
</th>
<th>
Posts
</th>
<th colspan='2'>
Most recent
</th>
</tr>
EOD;
$rowCounter = 0;
while($row = $results[0]->fetch_object()) {
    $alt = $rowCounter%2 ?  " class='alt'" : "";
    $list .= <<<EOD
<tr{$alt}>
<td>
<a href='?p=forum-topic&amp;article-id={$row->siblingId}'>{$row->title}</a>
</td>
<td>
{$row->postcounter}
</td>
<td>
{$row->latestby}
</td>
<td>
{$row->latest}
</td>
</tr>
EOD;
$rowCounter++;
}
$list .= "</table>";
$results[0]->close();

$mysqli->close();


// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlMain = <<<EOD
<h1>My latest articles</h1>
{$list}
EOD;

$htmlLeft 	= "";
$htmlRight	= "";


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Edit article', $htmlLeft, $htmlMain, $htmlRight);
exit;

?>