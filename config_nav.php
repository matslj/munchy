<?php

// ===========================================================================================
//
// config_nav.php
//
// Navigation specific configurations.
//

$menuNavBar = Array (
        'Hem'           => '?p=home',
	'Senaste trådarna'	=> '?p=forum-list',
	'Ny tråd' 	=> '?p=article-edit',
	'Install' 	=> '?p=install',
    	'About' 			=> '?p=about',
	'Sourcecode' 	=> '?p=ls',
);
define('MENU_NAVBAR', 	serialize($menuNavBar));

$infoNavBar = Array (
	'Me' 		=> '?p=me',
        'Redovisning'   => '?p=redovisning',
);
define('INFO_NAVBAR', 	serialize($infoNavBar));

?>