<?php

// ===========================================================================================
//
// index.php
//
// An implementation of a PHP frontcontroller for a web-site.
//
// All requests passes through this page, for each request is a pagecontroller choosen.
// The pagecontroller results in a response or a redirect.
//
// -------------------------------------------------------------------------------------------
//
// Require the files that are common for all pagecontrollers.
//
session_start();
require_once('config.php');

//
// start a timer to time the generation of this page (excluding config.php)
//
if(WS_TIMER) {
	$gTimerStart = microtime(TRUE);
}

//
// Enable autoload for classes
//
function __autoload($class_name) {
    require_once(TP_SOURCEPATH . $class_name . '.php');
}

// Allow only access to pagecontrollers through frontcontroller
// $indexIsVisited = TRUE;

// -------------------------------------------------------------------------------------------
//
// Redirect to the choosen pagecontroller.
//
$gPage = isset($_GET['p']) ? $_GET['p'] : 'home';

switch ($gPage) {
    //
    // Hem
    // changing from PIndex.php to forum/PIndex.php
    //
    case 'home': require_once(TP_PAGESPATH . 'PIndex.php');
        break;
    case 'about': require_once(TP_PAGESPATH . 'PAbout.php');
        break;

    //
    // Install database
    //
    case 'install': require_once(TP_PAGESPATH . 'install/PInstall.php');
        break;
    case 'installp': require_once(TP_PAGESPATH . 'install/PInstallProcess.php');
        break;

    //
    // Login
    //
    case 'login': require_once(TP_PAGESPATH . 'login/PLogin.php');
        break;
    case 'loginp': require_once(TP_PAGESPATH . 'login/PLoginProcess.php');
        break;
    case 'logoutp': require_once(TP_PAGESPATH . 'login/PLogoutProcess.php');
        break;

    //
    // User profile
    //
    case 'profile': require_once(TP_PAGESPATH . 'userprofile/PProfileShow.php');
        break;

    //
    // Admin pages
    //
    case 'admin': require_once(TP_PAGESPATH . 'admin_users/PUsersList.php');
        break;

    //
    // Directory listning
    //
    case 'ls':	require_once(TP_PAGESPATH . 'PListDirectory.php'); break;

    //
    // Forum
    //
    case 'article-index':		require_once(TP_PAGESPATH . 'forum/PIndex.php'); break;
    case 'article-edit':		require_once(TP_PAGESPATH . 'forum/PArticleEdit.php'); break;
    case 'article-save':		require_once(TP_PAGESPATH . 'forum/PArticleSave.php'); break;
    case 'article-delete':		require_once(TP_PAGESPATH . 'forum/PArticleDelete.php'); break;
    case 'article-show':		require_once(TP_PAGESPATH . 'forum/PArticleShow.php'); break;
    case 'forum-list':                  require_once(TP_PAGESPATH . 'forum/PListTopics.php'); break;
    case 'forum-topic':                  require_once(TP_PAGESPATH . 'forum/PPostsOnTopicShow.php'); break;

    //
    // Me-relaterad information
    //
    case 'me': CHTMLPage::redirectTo(WS_MELINK . 'hem.php'); break;
    case 'redovisning': CHTMLPage::redirectTo(WS_MELINK . 'redovisning.php'); break;

    //
    // Default case, trying to access some unknown page, should present some error message
    // or show the home-page
    //
    default: require_once(TP_PAGESPATH . 'P404.php');
        break;
}
?>
