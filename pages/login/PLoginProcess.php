<?php
// ===========================================================================================
//
// PLoginProcess.php
//
// Verify user and password. Create a session and store userinfo in.
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


// -------------------------------------------------------------------------------------------
//
// Destroy the current session (logout user), if it exists.
//
require_once(TP_SOURCEPATH . 'FDestroySession.php');


// -------------------------------------------------------------------------------------------
//
// Take care of _GET/_POST variables. Store them in a variable (if they are set).
//
$user 		= $pc->POSTisSetOrSetDefault('nameUser', '');
$password 	= $pc->POSTisSetOrSetDefault('passwordUser', '');


// -------------------------------------------------------------------------------------------
//
// Create a new database object, connect to the database, get the query and execute it.
// Relates to files in directory TP_SQLPATH.
//
$db 	= new CDatabaseController();
$mysqli = $db->Connect();
$query = $db->LoadSQL('SQLLoginUser.php');
$res 	= $db->Query($query);


// -------------------------------------------------------------------------------------------
//
// Use the results of the query to populate a session that shows we are logged in
//
session_start(); 			// Must call it since we destroyed it above.
session_regenerate_id(); 	// To avoid problems

$row = $res->fetch_object();

// Must be one row in the resultset
if($res->num_rows === 1) {
	$_SESSION['idUser'] 			= $row->id;
	$_SESSION['accountUser'] 		= $row->account;
	$_SESSION['groupMemberUser'] 	= $row->groupid;
} else {
	$_SESSION['errorMessage']	= "Failed to login, wrong username or password";
	$_POST['redirect'] 			= 'login';
}

$res->close();
$mysqli->close();

// -------------------------------------------------------------------------------------------
//
// Redirect to another page
//
$pc->RedirectTo($pc->POSTisSetOrSetDefault('redirect'));
exit;

?>