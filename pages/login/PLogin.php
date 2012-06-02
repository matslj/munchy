<?php
// ===========================================================================================
//
// PLogin.php
//
// Show a login-form, ask for user name and password.
//
// Author: Mikael Roos
//

// -------------------------------------------------------------------------------------------
//
// Get pagecontroller helpers. Useful methods to use in most pagecontrollers
//
$pc = new CPageController();
// $pc->LoadLanguage(__FILE__);


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
// Always redirect to latest visited page on success.
//
$redirectTo = $pc->SESSIONisSetOrSetDefault('history2');

// -------------------------------------------------------------------------------------------
//
// Show the login-form
//
$htmlMain = <<<EOD
<h1>Login</h1>
<p>
To testusers are prepared (username - password):
</p>
<ul>
<li>mikael - hemligt</li>
<li>doe - doe</li>
</ul>
EOD;

$htmlLeft = "";

$htmlRight = <<<EOD
<div class='sidebox'>
<div id='login'>
<fieldset>
<p>
Enter username and password
</p>
<form action="?p=loginp" method="post">
<input type='hidden' name='redirect' value='{$redirectTo}'>
<table>
<tr>
<td style="text-align: right">
<label for="nameUser">Username: </label>
</td>
<td>
<input id="nameUser" class="login" type="text" name="nameUser">
</td>
</tr>
<tr>
<td style="text-align: right">
<label for="passwordUser">Password: </label>
</td>
<td>
<input id="passwordUser" class="password" type="password" name="passwordUser">
</td>
</tr>
<tr>
<td colspan='2' style="text-align: right">
<button type="submit" name="submit">Logga in</button>
</td>
</tr>
</table>
</form>
</fieldset>
<!--
<p><a href="PGetPassword.php">Skapa en ny användare!</a></p>
<p><a href="PGetPassword.php">Jag har glömt mitt lösenord!</a></p>
-->
</div> <!-- #login -->
</div> <!-- .sidebox -->

EOD;


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Template', $htmlLeft, $htmlMain, $htmlRight);
exit;


?>