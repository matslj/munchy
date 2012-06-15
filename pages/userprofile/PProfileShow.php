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

$idUser = $row -> idUser;
$accountUser = $row -> accountUser;
$emailUser = $row -> emailUser;
$idGroup = $row -> idGroup;
$nameGroup = $row -> nameGroup;
$avatarUser = $row -> avatarUser;



$action = "?p=account-update";
$redirect = "?p=account-settings";
$imageLink = WS_IMAGES;

$htmlMain .= <<< EOD
<div id="userProfileWrap">
<div id="userProfile">

    <!-- userid and password -->
    <h2>Account and password</h2>
    <form action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}#basic'>
        <input type='hidden' name='redirect-fail' value='{$redirect}'>
        <input type='hidden' name='accountid' value='{$idUser}'>
        <fieldset class='accountsettings'>
            <table width='99%'>
                <tr>
                    <td><label for="account">Name:</label></td>
                    <td style='text-align: right;'><input class='account-dimmed' type='text' name='account' readonly value='{$accountUser}'></td>
                </tr>
                <tr>
                    <td><label for="account">Password:</label></td>
                    <td style='text-align: right;'><input class='password' type='password' name='password1'></td>
                </tr>
                <tr>
                    <td><label for="account">Password (again):</label></td>
                    <td style='text-align: right;'><input class='password' type='password' name='password2'></td>
                </tr>
                <tr>
                    <td colspan='2' style='text-align: right;'><button type='submit' name='submit' value='change-password'>Change password</button></td>
                </tr>
            </table>
        </fieldset>
    </form>

    <!-- email -->
    <h2 id='email'>Email settings</h2>
    <form action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}#email'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' name='accountid' value='{$idUser}'>
        <fieldset class='accountsettings'>
            <table width='99%'>
                <tr>
                    <td><label for="account">Email: </label></td>
                    <td style='text-align: right;'><input class='email' type='text' name='email' value='{$emailUser}' /></td>
                </tr>
                <tr>
                    <td colspan='2' style='text-align: right;'><button type='submit' name='submit' value='change-email'>Update email</button></td>
                </tr>
            </table>
        </fieldset>
    </form>

    <!-- avatar -->
    <h2 id='avatar'>Avatar</h2>
    <form action='{$action}' method='POST'>
        <input type='hidden' name='redirect' value='{$redirect}#avatar'>
        <input type='hidden' name='redirect-failure' value='{$redirect}'>
        <input type='hidden' name='accountid' value='{$idUser}'>
        <fieldset class='accountsettings'>
            <table width='99%'>
                <tr>
                    <td><label for="account">Avatar:</label></td>
                    <td style='text-align: right;'><input class='avatar' type='text' name='avatar' value='{$avatarUser}' placeholder="Insert link to avatar here">
                </td>
                </tr>
                <tr>
                    <td><img src='{$avatarUser}' alt=':)'></td>
                    <td style='text-align: right;'><button type='submit' name='submit' value='change-avatar'>Update avatar</button></td>
                </tr>
            </table>
        </fieldset>
    </form>
</div> <!-- div userProfile -->
</div> <!-- div userProfileWrap -->
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