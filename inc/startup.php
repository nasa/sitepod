<?php
/*
	This is phpSitemapNG, a php script that creates your personal google sitemap
	It can be downloaded from http://enarion.net/google/
	License: GPL

	Tobias Kluge, enarion.net
*/

if (isset($_REQUEST['debug'])) {
	error_reporting(E_ALL);
} else {
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

// this script is greedy! :(
$timeout_before = ini_get('max_execution_time');
@set_time_limit(0);
$timeout_after = ini_get('max_execution_time');
@ini_set('memory_limit', '128M');
@ini_set('allow_url_fopen','1');
@ini_set('include_path',ini_get('include_path').':./inc:./inc/functions:./inc/classes:./settings:./templates');

include_once(dirname(__FILE__).'/Ontology.php');

include_once(dirname(__FILE__).'/functions/functions.inc.php');
include_once(dirname(__FILE__).'/functions/parsing.inc.php');
include_once(dirname(__FILE__).'/functions/settings.inc.php');
include_once(dirname(__FILE__).'/classes/Crawler.class.php');
include_once(dirname(__FILE__).'/classes/FilesystemHandler.class.php');
include_once(dirname(__FILE__).'/classes/LayoutEngine.class.php');
include_once(dirname(__FILE__).'/gsgxml/GsgXml.class.php');

$SETTINGS[PSNG_FILE_SETTINGS] = 	'settings/settings.inc.php';
$SETTINGS[PSNG_FILE_FILES] = 		'settings/files.inc.php';

include_once(dirname(__FILE__).'/../'.$SETTINGS[PSNG_FILE_SETTINGS]);

//echo $_SERVER[SCRIPT_NAME]."<br>\n";
/*if ($_SERVER['SCRIPT_URL'] != '' && (strpos($_SERVER['SCRIPT_URL'], 'index.php') !== FALSE || strpos($_SERVER['SCRIPT_URL'], 'cron.php') !== FALSE)) {
	$SETTINGS[PSNG_SCRIPT] = $_SERVER['SCRIPT_URL'];
} else
*/
if ($_SERVER['SCRIPT_NAME'] != '' && (strpos($_SERVER['SCRIPT_NAME'], 'index.php') !== FALSE || strpos($_SERVER['SCRIPT_NAME'], 'cron.php') !== FALSE)) {
	$SETTINGS[PSNG_SCRIPT] = $_SERVER['SCRIPT_NAME'];
} elseif ($_SERVER['PATH_INFO'] != '') {
	$SETTINGS[PSNG_SCRIPT] = $_SERVER['PATH_INFO'];
} elseif ($_SERVER['ORIG_PATH_INFO'] != '') { // only for FastCGI ?
	$SETTINGS[PSNG_SCRIPT] = $_SERVER['ORIG_PATH_INFO'];
} else {
	echo "error, couldn't extract script name!";
}

$state = init();
$SETTINGS[PSNG_TIMEOUT_AUTOMATIC] = ($timeout_after === $timeout_before);

// check if this could be executed before init()
if ($SETTINGS[PSNG_TIMEOUT] != PSNG_TIMEOUT_NONE) {
	$SETTINGS[PSNG_TIMEOUT_TIME_START] = microtime_float();
	$SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN] = 3;
//	$SETTINGS[PSNG_TIMEOUT_TIME_DURATION] = ($timeout_after != 0)?$timeout_after : 30;
	$SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE] = $SETTINGS[PSNG_TIMEOUT_TIME_START] + $SETTINGS[PSNG_TIMEOUT_TIME_DURATION] - $SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN];
}
?>