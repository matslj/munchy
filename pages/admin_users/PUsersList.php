<?php
// -------------------------------------------------------------------------------------------
//
// PUsersList.php
//
// Show all users in a list.
//

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
require_once(TP_SOURCEPATH . 'CPageController.php');

$pc = CPageController::getInstance();

// -------------------------------------------------------------------------------------------
//
// Interception Filter, access, authorithy and other checks.
//
require_once(TP_SOURCEPATH . 'CInterceptionFilter.php');

$intFilter = new CInterceptionFilter();
$intFilter->frontcontrollerIsVisitedOrDie();
$intFilter->UserIsSignedInOrRecirectToSignIn();
$intFilter->UserIsMemberOfGroupAdminOrDie();

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

$htmlLeft = "";

$htmlMain = <<<EOD
<h1>Admin: Show user accounts</h1>
EOD;

$htmlRight = "";

// -------------------------------------------------------------------------------------------
//
// Take care of _GET variables. Store them in a variable (if they are set).
// Then prepare the ORDER BY SQL-statement, but only if the _GET variables has a value.
//
$orderBy 	= $pc->GETisSetOrSetDefault('orderby', '');
$orderOrder 	= $pc->GETisSetOrSetDefault('order', '');

$orderStr = "";
if(!empty($orderBy) && !empty($orderOrder)) {
	$orderStr = " ORDER BY {$orderBy} {$orderOrder}";
}

// -------------------------------------------------------------------------------------------
//
// Prepare the order by ref, can you figure out how it works?
//

$ascOrDesc = $orderOrder == 'ASC' ? 'DESC' : 'ASC';
$httpRef = "?p=admin&amp;order={$ascOrDesc}&orderby=";

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
$query = $db->LoadSQL('SAdminList.php');
$res = $db->Query($query);

// -------------------------------------------------------------------------------------------
//
// Show the results of the query
//

$htmlMain .= <<< EOD
<table id="userAccounts">
<tr>
<th><a href='{$httpRef}idUser'>Id</a></th>
<th><a href='{$httpRef}accountUser'>Account</a></th>
<th><a href='{$httpRef}emailUser'>Email</a></th>
<th><a href='{$httpRef}idGroup'>Grupp</a></th>
<th><a href='{$httpRef}nameGroup'>Grupp description</a></th>
</tr>
EOD;

while($row = $res->fetch_object()) {
	$htmlMain .= <<< EOD
<tr>
<td>{$row->idUser}</td>
<td>{$row->accountUser}</td>
<td>{$row->emailUser}</td>
<td>{$row->idGroup}</td>
<td>{$row->nameGroup}</td>
</tr>
EOD;
}

$htmlMain .= "</table>";

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

$page->printPage($htmlLeft, $htmlMain, $htmlRight, '', $displayAs);
exit;


?>
