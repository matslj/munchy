<?php
// ===========================================================================================
//
// PIndex.php
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
$img = WS_IMAGES;

// -------------------------------------------------------------------------------------------
//
// Page specific code
//
$htmlMain = <<<EOD
<h1>Munchy</h1>
<h2>A dbwebb2 forum</h2>
<p>
This is the result of the final project in the course dbwebb2 @ <a href="http://www.bth.se/">BTH</a>.
</p>
<p>
Information about the project and how to install it can be found under <a href="?p=about">about</a>.
</p>
<p>
As wysiwyg-editor I've used <a href="http://nicedit.com">nicedit</a>.
</p>
<p>
The 'Munchy'-characters is re-created by me using Windows Paint. The colors may differ from the in game colors.
</p>
EOD;

$htmlLeft 	= "";
$htmlRight	= "";

// -------------------------------------------------------------------------------------------
//
// Local menu?
//
// require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config_nav.php');

// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Munchy - a forum template', $htmlLeft, $htmlMain, $htmlRight);
exit;

?>