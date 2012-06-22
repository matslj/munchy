<?php
// ===========================================================================================
//
// PTemplate.php
//
// A standard template page for a pagecontroller.
//
// This is an example on how to use/create a pagecontroller. It shows the features available
// from a pagecontroller.
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
//$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->UserIsMemberOfGroupAdminOrDie();


// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//

// To show off the template and display the flexible 1-2-3 column layout used.
$showLeft 	= $pc->GETisSetOrSetDefault('showLeft', '1');
$showRight 	= $pc->GETisSetOrSetDefault('showRight', '1');


// -------------------------------------------------------------------------------------------
//
// Page specific code
//

$htmlMain = <<<EOD
<h1>Template</h1>
<h2>Introduction</h2>
<p>
Copy this file, PTemplate.php, to create new pacecontrollers.
</p>
<p>
<a href='?p=ls&amp;dir=pages/home&amp;file=PTemplate.php'>Review sourcecode for PTemplate.php</a>.
</p>
<p>
Main
</p>
<p>
<a href='?p=template&amp;showLeft=1&amp;showRight=1'>Show all 3 columns</a>
</p>
EOD;

$htmlLeft = <<<EOD
<h3 class='columnMenu'>Left column</h3>
<p>
This is HTML for the left column. Use it or loose it.
</p>
<p>
Left
</p>
<p>
<a href='?p=template&amp;showLeft=2&amp;showRight={$showRight}'>Do not display this column</a>
</p>
EOD;

$htmlRight = <<<EOD
<h3 class='columnMenu'>Right column</h3>
<p>
This is HTML for the right column. Use it or loose it.
</p>
<p>
Right
</p>
<p>
<a href='?p=template&amp;showLeft={$showLeft}&amp;showRight=2'>Do not display this column</a>
</p>
EOD;

// Display only thos column thats choosen
$htmlLeft 	= (($showLeft == 1) ? $htmlLeft : "");
$htmlRight 	= (($showRight == 1) ? $htmlRight : "");


// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
/*
$db 	= new CDatabaseController();
$mysqli = $db->Connect();
$query 	= $db->LoadSQL('SQLCreateUserAndGroupTables.php');
$res 	= $db->MultiQuery($query);
$no		= $db->RetrieveAndIgnoreResultsFromMultiQuery();
*/

/*
$db 	= new CDatabaseController();
$mysqli = $db->Connect();
$query 	= $db->LoadSQL('SQLLoginUser.php');
$res 	= $db->Query($query);
*/


// -------------------------------------------------------------------------------------------
//
// Use the results of the query
//
/*
$res->close();
*/


// -------------------------------------------------------------------------------------------
//
// Close the connection to the database
//
/*
$mysqli->close();
*/


// -------------------------------------------------------------------------------------------
//
// Redirect to another page
// Support $redirect to be local uri within site or external site (starting with http://)
//
/*
CHTMLPage::redirectTo(CPageController::POSTisSetOrSetDefault('redirect', 'home'));
exit;
*/

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Template', $htmlLeft, $htmlMain, $htmlRight);
exit;

?>