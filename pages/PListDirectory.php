<?php
// ===========================================================================================
//
// Filename: PListDirectory.php
//
// Description: Shows a directory listning and view content of files.
//
// Author: Mikael Roos, mos@bth.se
//
//

// -------------------------------------------------------------------------------------------
//
// Settings for this pagecontroller.
//

// Separator between directories and files, change between Unix/Windows
$SEPARATOR = DIRECTORY_SEPARATOR; // Using built-in PHP-constant for separator.
//$SEPARATOR = '/'; 	// Unix, Linux, MacOS, Solaris
//$SEPARATOR = '\\'; 	// Windows 

// Show the content of files named config.php, except the rows containing DB_USER, DB_PASSWORD
//$HIDE_DB_USER_PASSWORD = FALSE; 
$HIDE_DB_USER_PASSWORD = TRUE; 

// Which directory to use as basedir, end with separator
$BASEDIR = TP_ROOT . $SEPARATOR;

// Show syntax of the code, currently only supporting PHP or DEFAULT.
// PHP uses PHP built-in function highlight_string.
// DEFAULT performs <pre> and htmlspecialchars.
// HTML to be done.
// CSS to be done.
$SYNTAX = 'PHP';
 
// The link to this page, usefull to change when using this pagecontroller for other things,
// such as showing stylesheets in a separate directory, for example.
$HREF = "?p=ls";


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
// Page specific code
//

$html = <<<EOD
<header>
<h1>Show files and sourcecode</h1>
<p>
Click a file to show its content.
</p>
</header>
EOD;


// -------------------------------------------------------------------------------------------
//
// Verify the input variable _GET, no tampering with it
//
$currentdir	= isset($_GET['dir']) ? $_GET['dir'] : '';

$fullpath1 	= realpath($BASEDIR);
$fullpath2 	= realpath($BASEDIR . $currentdir);
$len = strlen($fullpath1);
if(	strncmp($fullpath1, $fullpath2, $len) !== 0 ||
	strcmp($currentdir, substr($fullpath2, $len+1)) !== 0 ) {
	die('Tampering with directory?');
}
$fullpath = $fullpath2;
$currpath = substr($fullpath2, $len+1);


// -------------------------------------------------------------------------------------------
//
// Show the name of the current directory
//
$start		= basename($fullpath1);
$dirname 	= basename($fullpath);
$html .= <<<EOD
<p>
<a href='{$HREF}&amp;dir='>{$start}</a>{$SEPARATOR}{$currpath}
</p>
EOD;



// -------------------------------------------------------------------------------------------
//
// Open and read a directory, show its content
//
$dir 	= $fullpath;
$curdir1 = empty($currpath) ? "" : "{$currpath}{$SEPARATOR}";
$curdir2 = empty($currpath) ? "" : "{$currpath}";

$list = Array();
if(is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
        	if($file != '.' && $file != '..' && $file != '.svn' && $file != '.git') {
        		$curfile = $fullpath . $SEPARATOR . $file;
        		if(is_dir($curfile)) {
          	  		$list[$file] = "<a href='{$HREF}&amp;dir={$curdir1}{$file}'>{$file}{$SEPARATOR}</a>";
          	  	} else if(is_file($curfile)) {
          	  	   	$list[$file] = "<a href='{$HREF}&amp;dir={$curdir2}&amp;file={$file}'>{$file}</a>";
          	  	}
          	 }
        }
        closedir($dh);
    }
}

ksort($list);

$html .= '<p>';
foreach($list as $val => $key) {
	$html .= "{$key}<br />";
}
$html .= '</p>';


// -------------------------------------------------------------------------------------------
//
// Show the content of a file, if a file is set
//
$dir 	= $fullpath;
$file	= "";

if(isset($_GET['file'])) {
	$file = basename($_GET['file']);

	$content = file_get_contents($dir . $SEPARATOR . $file, 'FILE_TEXT');

	// Remove password and user from config.php, if enabled
	if($HIDE_DB_USER_PASSWORD == TRUE && $file == 'config.php') {

		$pattern[0] 	= '/(DB_PASSWORD|DB_USER)(.+)/';
		$replace[0] 	= '/* <em>\1,  is removed and hidden for security reasons </em> */ );';
		
		$content = preg_replace($pattern, $replace, $content);
	}
	
	 // Show syntax if defined
	if($SYNTAX == 'PHP') {
		$content = highlight_string($content, TRUE);
	} else {
		$content = htmlspecialchars($content);
		$content = "<pre>{$content}</pre>";
	}

	$html .= <<<EOD
<h3><a href='{$HREF}'>{$file}</a></h3>
<div class="sourcecode">
<pre>
{$content}
</pre>
</div>
EOD;
}


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
$page = new CHTMLPage();

$page->printPage('Show files and source code', "", $html, "");
exit;


?>