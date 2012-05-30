<?php
// ===========================================================================================
//
// Class CInterceptionFilter
//
// Used in each pagecontroller to check access, authority.
//
//
// Author: Mats Ljungquist
//


class CInterceptionFilter {

	// ------------------------------------------------------------------------------------
	//
	// Internal variables
	//

	// ------------------------------------------------------------------------------------
	//
	// Constructor
	//
	public function __construct() {
		;
	}


	// ------------------------------------------------------------------------------------
	//
	// Destructor
	//
	public function __destruct() {
		;
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if index.php (frontcontroller) is visited, disallow direct access to
	// pagecontrollers
	//
	public function FrontControllerIsVisitedOrDie() {

                // När man använder det reserverade ordet 'global' så innebär det att i en funktion
                // talar om att man vill referera till den globala 'varianten' av variabeln
		global $gPage; // Always defined in frontcontroller

		if(!isset($gPage)) {
			die('No direct access to pagecontroller is allowed.');
		}
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if user has signed in or redirect user to sign in page
	//
	public function UserIsSignedInOrRecirectToSignIn() {

		if(!isset($_SESSION['accountUser'])) {
                    $_SESSION['errorMessage'] = 'Du måste vara inloggad för att komma åt den sidan';
                    $_SESSION['redirect'] = $_GET['p'];
                    require_once(TP_SOURCEPATH . 'CHTMLPage.php');
                    CHTMLPage::redirectTo('login');
		} else {
                    $_SESSION['redirect'] = '';
                    unset($_SESSION['redirect']);
                }
	}


	// ------------------------------------------------------------------------------------
	//
	// Check if admin
	//
	public function UserIsMemberOfGroupAdminOrDie() {
            // User must be member of group adm or die
            if($_SESSION['groupMemberUser'] != 'adm')
                    die('You do not have the authourity to access this page');
	}

        // ------------------------------------------------------------------------------------
	//
	// Check if user belongs to the admin group or is a specific user.
	//
	public function IsUserMemberOfGroupAdminOrIsCurrentUser($aUserId) {

		$isAdmGroup 		= (isset($_SESSION['groupMemberUser']) && $_SESSION['groupMemberUser'] == 'adm') ? TRUE : FALSE;
		$isCurrentUser	= (isset($_SESSION['idUser']) && $_SESSION['idUser'] == $aUserId) ? TRUE : FALSE;

		return $isAdmGroup || $isCurrentUser;
	}


} // End of Of Class

?>