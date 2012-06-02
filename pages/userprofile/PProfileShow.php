<?php
// ===========================================================================================
//
// PProfileShow.php
//
// Show the users profile information in a form and make it possible to edit the information.
//


// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
require_once(TP_SOURCEPATH . 'CPageController.php');

$pc = new CPageController();
//$pc->LoadLanguage(__FILE__);


// -------------------------------------------------------------------------------------------
//
// Interception Filter, access, authorithy and other checks.
//
require_once(TP_SOURCEPATH . 'CInterceptionFilter.php');

$intFilter = new CInterceptionFilter();

$intFilter->frontcontrollerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRecirectToSignIn();
//$intFilter->userIsMemberOfGroupAdminOrDie();


// -------------------------------------------------------------------------------------------
//
// Take care of global pageController settings, can exist for several pagecontrollers.
// Decide how page is displayed, review CHTMLPage for supported types.
//
$displayAs = $pc->GETisSetOrSetDefault('pc_display', '');


// -------------------------------------------------------------------------------------------
//
// Page specific code
//

//$settingsMenu = $pc->GetSidebarMenu(unserialize(MENU_SETTINGSBAR));

$htmlLeft = "";

//$headerMenu = $pc->GetSidebarMenu(unserialize(MENU_ACCOUNTBAR));

$htmlMain = <<<EOD
<h1>Account settings</h1>
EOD;

$htmlRight = "";


// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();


// -------------------------------------------------------------------------------------------
//
// Prepare and perform a SQL query.
//
$user = $_SESSION['accountUser'];
$query = $db->LoadSQL('SUserDetails.php');
$res = $db->Query($query);


// -------------------------------------------------------------------------------------------
//
// Show the results of the query
//
$row = $res->fetch_object();

$htmlMain .= <<< EOD
<table id="userProfile">
<tr>
<th>Id</th>
<td><input type='text' tab='10' name='idUser' size='80' readonly value='{$row->idUser}'></td>
</tr>
<tr>
<th>Account</th>
<td><input type='text' tab='11' name='accountUser' readonly size='80' value='{$row->accountUser}'></td>
</tr>
<tr>
<th>Email</th>
<td><input type='text' tab='12' name='emailUser' readonly size='80' value='{$row->emailUser}'></td>
</tr>
<tr>
<th>Group</th>
<td><input type='text' tab='13' name='idGroup' readonly size='80' value='{$row->idGroup}'></td>
</tr>
<tr>
<th>Group description</th>
<td><input type='text' tab='13' name='nameGroup' readonly size='80' value='{$row->nameGroup}'></td>
</tr>
</table>
EOD;


// -------------------------------------------------------------------------------------------
//
// Use the results of the query
//

$res->close();


// -------------------------------------------------------------------------------------------
//
// Close the connection to the database
//
$mysqli->close();


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
require_once(TP_SOURCEPATH . 'CHTMLPage.php');

$page = new CHTMLPage(WS_STYLESHEET);

$page->printPage("Your account", $htmlLeft, $htmlMain, $htmlRight);
exit;

?>