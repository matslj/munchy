<?php
// ===========================================================================================
//
// PLogoutProcess.php
//
// Logout by destroying the session.
//


// -------------------------------------------------------------------------------------------
//
// Page specific code
//


// -------------------------------------------------------------------------------------------
//
// Destroy the current session (logout user), if it exists.
//
require_once(TP_SOURCEPATH . 'FDestroySession.php');


// -------------------------------------------------------------------------------------------
//
// Redirect to another page
// Support $redirect to be local uri within site or external site (starting with http://)
//
CHTMLPage::redirectTo(CPageController::POSTisSetOrSetDefault('redirect', 'login'));
exit;


?>