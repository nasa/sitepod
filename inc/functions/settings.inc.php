<?php
/*
	This is phpSitemapNG, a php script that creates your personal google sitemap
	It can be downloaded from http://enarion.net/google/
	License: GPL

	Tobias Kluge, enarion.net
*/


/**
 * show settings
 * if settings are empty, set them to usable values
 */
function viewSetup($resetSettings = FALSE) {
	global $SETTINGS, $LAYOUT;

	$LAYOUT->setTitle('Edit settings');

	if($resetSettings === TRUE) {
		$SETTINGS = array();
	}
	$page_root = substr($_SERVER['SCRIPT_FILENAME'],0,strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['SCRIPT_NAME']));
	if (is_null($SETTINGS[PSNG_PAGEROOT])) 		$SETTINGS[PSNG_PAGEROOT] 			= ($page_root != '')?$page_root : $_SERVER[DOCUMENT_ROOT];
	if (is_null($SETTINGS[PSNG_WEBSITE])) 		$SETTINGS[PSNG_WEBSITE] 			= "http://".$_SERVER[HTTP_HOST];
	if (is_null($SETTINGS[PSNG_CRAWLER_URL])) 	$SETTINGS[PSNG_CRAWLER_URL] 		= $SETTINGS[PSNG_WEBSITE];

	if (is_null($SETTINGS[PSNG_SITEMAP_FILE])) 	$SETTINGS[PSNG_SITEMAP_FILE] 		= "/sitemap.xml";
	if (is_null($SETTINGS[PSNG_TXTSITEMAP_FILE])) 	$SETTINGS[PSNG_TXTSITEMAP_FILE] = "/sitemap.txt";
//	if (is_null($SETTINGS[PSNG_SITEMAP_FILE])) 	$SETTINGS[PSNG_SITEMAP_FILE] 		= $SETTINGS[PSNG_PAGEROOT] . "/sitemap.xml";
//	if (is_null($SETTINGS[PSNG_SITEMAP_URL])) 	$SETTINGS[PSNG_SITEMAP_URL] 		= $SETTINGS[PSNG_WEBSITE] ."/sitemap.xml";
	if (is_null($SETTINGS[PSNG_TEMP_DIR]))		$SETTINGS[PSNG_TEMP_DIR] 			= dirname(__FILE__) . "/temp/";
	if (is_null($SETTINGS[PSNG_PUBLIC_URL]))	$SETTINGS[PSNG_PUBLIC_URL] 			= $SETTINGS[PSNG_PAGEROOT] . "/phpsitemapng.php";
	if (is_null($SETTINGS[PSNG_SCAN_LOCAL]))	$SETTINGS[PSNG_SCAN_LOCAL] 			= TRUE;
	if (is_null($SETTINGS[PSNG_SCAN_WEBSITE]))	$SETTINGS[PSNG_SCAN_WEBSITE] 		= TRUE;
	if (is_null($SETTINGS[PSNG_PINGGOOGLE]))	$SETTINGS[PSNG_PINGGOOGLE] 			= TRUE;
	if (is_null($SETTINGS[PSNG_EDITRESULT]))	$SETTINGS[PSNG_EDITRESULT] 			= PSNG_EDITRESULT_TRUE;
	if (is_null($SETTINGS[PSNG_STORE_FILELIST])) $SETTINGS[PSNG_STORE_FILELIST] 	= TRUE;

//	if (!isset($SETTINGS[PSNG_TIMEOUT]))		$SETTINGS[PSNG_TIMEOUT] 			= PSNG_TIMEOUT_AUTOMATIC; //($SETTINGS[PSNG_TIMEOUT_AUTOMATIC] == TRUE)?PSNG_TIMEOUT_AUTOMATIC:PSNG_TIMEOUT_NONE;
	if (!isset($SETTINGS[PSNG_TIMEOUT]))		$SETTINGS[PSNG_TIMEOUT] 			= PSNG_TIMEOUT_FORCE;
	if (!isset($SETTINGS[PSNG_TIMEOUT_TIME_DURATION])) 	$SETTINGS[PSNG_TIMEOUT_TIME_DURATION] =
			(ini_get('max_execution_time') != '' && ini_get('max_execution_time') > 0)
					?	ini_get('max_execution_time')
					: 	30;
//is_null($SETTINGS[PSNG_TIMEOUT])
	/* list of disallowed directory names */
	if (is_null($SETTINGS[PSNG_DISALLOW_DIR])) {
		$SETTINGS[PSNG_DISALLOW_DIR] = array();
		$SETTINGS[PSNG_DISALLOW_DIR][] = "/admin/";
		$SETTINGS[PSNG_DISALLOW_DIR][] = "/include/";
		$SETTINGS[PSNG_DISALLOW_DIR][] = "/logs/";
		$SETTINGS[PSNG_DISALLOW_DIR][] = "/cgi-bin/";
		$SETTINGS[PSNG_DISALLOW_DIR][] = dirname($_SERVER['PHP_SELF']);
	}
	// TODO open robots.txt and add disallowed directories to our list

	/* list of disallowed file names */
	if (is_null($SETTINGS[PSNG_DISALLOW_FILE])) {
		$SETTINGS[PSNG_DISALLOW_FILE] = array();
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".xml";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".inc";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".old";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".save";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".txt";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".js";
		$SETTINGS[PSNG_DISALLOW_FILE][] = "~";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".LCK";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".zip";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".ZIP";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".bmp";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".BMP";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".jpg";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".jpeg";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".JPG";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".GIF";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".PNG";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".png";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".gif";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".CSV";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".csv";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".css";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".class";
		$SETTINGS[PSNG_DISALLOW_FILE][] = ".jar";
	}

	/* list of disallowed key names */
	if (is_null($SETTINGS[PSNG_DISALLOW_KEY])) {
		$SETTINGS[PSNG_DISALLOW_KEY] = array();  // 'PHPSESSID', 'link', 'sid');
		$SETTINGS[PSNG_DISALLOW_KEY][] = session_name();
		$SETTINGS[PSNG_DISALLOW_KEY][] = 'sid';
		$SETTINGS[PSNG_DISALLOW_KEY][] = 'PHPSESSID';

		$SETTINGS[PSNG_DISALLOW_KEY] = array_unique($SETTINGS[PSNG_DISALLOW_KEY]);
	}

	debug($SETTINGS[PSNG_DISALLOW_KEY], "disallow keys");
	if (is_null($SETTINGS[PSNG_CHANGEFREQ])) 			$SETTINGS[PSNG_CHANGEFREQ] = PSNG_CHANGEFREQ_FIXED;
	if (is_null($SETTINGS[PSNG_CHANGEFREQ_FIXED])) 		$SETTINGS[PSNG_CHANGEFREQ_FIXED] = 'weekly';

	if (is_null($SETTINGS[PSNG_PRIORITY])) 				$SETTINGS[PSNG_PRIORITY] = PSNG_PRIORITY_FIXED;
	if (is_null($SETTINGS[PSNG_PRIORITY_FIXED])) 		$SETTINGS[PSNG_PRIORITY_FIXED] = 0.5;

	if (is_null($SETTINGS[PSNG_LASTMOD])) 				$SETTINGS[PSNG_LASTMOD] = PSNG_LASTMOD_FILEDATE;
	if (is_null($SETTINGS[PSNG_LASTMOD_TIMEFORMAT])) 	$SETTINGS[PSNG_LASTMOD_TIMEFORMAT] = PSNG_TIMEFORMAT_LONG;

	require(dirname(__FILE__).'/../../'.PSNG_FILE_TEMPLATE_SETUP_EXPERT);

	$LAYOUT->addText($layout, 'Current settings');
	return TRUE;
}

/**
 * extract settings from passed user values
 */
function getSettings() {
	global $_REQUEST, $SETTINGS, $LAYOUT;
	$LAYOUT->setTitle("Store settings");

	// TODO check values we got from user

	// put user values into settings array
	$SETTINGS[PSNG_WEBSITE] = $_REQUEST[PSNG_WEBSITE];
	if (substr($SETTINGS[PSNG_WEBSITE],0,4) != "http") {
		// user hasn't put a http in front of the url of the website
		$SETTINGS[PSNG_WEBSITE] = "http://".$SETTINGS[PSNG_WEBSITE];
	}
	// TODO test this, if it's working with e.g. http://enarion.net
	if (substr($SETTINGS[PSNG_WEBSITE], -1) != '/' && substr($SETTINGS[PSNG_WEBSITE], -1) != "\\") $SETTINGS[PSNG_WEBSITE] .= '/';

	$SETTINGS[PSNG_PAGEROOT] = addslashes($_REQUEST[PSNG_PAGEROOT]);
	if (substr($SETTINGS[PSNG_PAGEROOT], -1) != '/' && substr($SETTINGS[PSNG_PAGEROOT], -1) != "\\") $SETTINGS[PSNG_PAGEROOT] .= '/';

	// sitemap is the file after the website-part of the sitemap_url
/*	$sitemap = str_replace($SETTINGS[PSNG_WEBSITE], '', $_REQUEST[PSNG_SITEMAP_URL]);
	$SETTINGS[PSNG_SITEMAP_FILE] = substr($SETTINGS[PSNG_PAGEROOT],0,strlen($SETTINGS[PSNG_PAGEROOT])) . $sitemap;
	$SETTINGS[PSNG_SITEMAP_URL] = $SETTINGS[PSNG_WEBSITE] . $sitemap;
*/
	$SETTINGS[PSNG_SITEMAP_FILE] = $_REQUEST[PSNG_SITEMAP_FILE];
	$SETTINGS[PSNG_TXTSITEMAP_FILE] = $_REQUEST[PSNG_TXTSITEMAP_FILE];
	$SETTINGS[PSNG_SITEMAP_URL] = $_REQUEST[PSNG_SITEMAP_FILE];

	if (isset($_REQUEST[PSNG_TIMEOUT])) {
		$SETTINGS[PSNG_TIMEOUT] = PSNG_TIMEOUT_FORCE;
		if ($_REQUEST[PSNG_TIMEOUT_TIME_DURATION] != $SETTINGS[PSNG_TIMEOUT_TIME_DURATION]) {
			$SETTINGS[PSNG_TIMEOUT_TIME_DURATION] = $_REQUEST[PSNG_TIMEOUT_TIME_DURATION];
			$SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE] = $SETTINGS[PSNG_TIMEOUT_TIME_START] + $SETTINGS[PSNG_TIMEOUT_TIME_DURATION] - $SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN] -1;
		}
	} else {
		$SETTINGS[PSNG_TIMEOUT] = PSNG_TIMEOUT_NONE;
	}

	if (isset($_REQUEST[PSNG_CRAWLER_URL]))		$SETTINGS[PSNG_CRAWLER_URL] = $_REQUEST[PSNG_CRAWLER_URL];

	if (isset($_REQUEST[PSNG_SCAN_LOCAL]))		$SETTINGS[PSNG_SCAN_LOCAL] = TRUE; else $SETTINGS[PSNG_SCAN_LOCAL] = FALSE;
	if (isset($_REQUEST[PSNG_SCAN_WEBSITE]))	$SETTINGS[PSNG_SCAN_WEBSITE] = TRUE; else $SETTINGS[PSNG_SCAN_WEBSITE] = FALSE;
	if (isset($_REQUEST[PSNG_PINGGOOGLE]))		$SETTINGS[PSNG_PINGGOOGLE] = TRUE; else $SETTINGS[PSNG_PINGGOOGLE] = FALSE;
	if (isset($_REQUEST[PSNG_SCAN_WEBSITE]))	$SETTINGS[PSNG_SCAN_WEBSITE] = TRUE; else $SETTINGS[PSNG_SCAN_WEBSITE] = FALSE;
	if (isset($_REQUEST[PSNG_SCAN_WEBSITE_LEVEL])) $SETTINGS[PSNG_SCAN_WEBSITE_LEVEL] = $_REQUEST[PSNG_SCAN_WEBSITE_LEVEL]; else $SETTINGS[PSNG_SCAN_WEBSITE_LEVEL] = 0;

	$SETTINGS[PSNG_EDITRESULT] = ($_REQUEST[PSNG_EDITRESULT] != '') ? PSNG_EDITRESULT_TRUE : PSNG_EDITRESULT_FALSE;
	$SETTINGS[PSNG_STORE_FILELIST] = $_REQUEST[PSNG_STORE_FILELIST];

	$SETTINGS[PSNG_LASTMOD] = $_REQUEST[PSNG_LASTMOD];
	$SETTINGS[PSNG_LASTMOD_TIMEFORMAT] = $_REQUEST[PSNG_LASTMOD_TIMEFORMAT];

	$SETTINGS[PSNG_CHANGEFREQ] = $_REQUEST[PSNG_CHANGEFREQ];
	$SETTINGS[PSNG_CHANGEFREQ_FIXED] = $_REQUEST[PSNG_CHANGEFREQ_FIXED];

	$SETTINGS[PSNG_PRIORITY] = $_REQUEST[PSNG_PRIORITY];
	$SETTINGS[PSNG_PRIORITY_FIXED] = $_REQUEST[PSNG_PRIORITY_FIXED];

	if ($_REQUEST[PSNG_DISALLOW_DIR] != "") $SETTINGS[PSNG_DISALLOW_DIR] = toArray($_REQUEST[PSNG_DISALLOW_DIR]);
	if ($_REQUEST[PSNG_DISALLOW_FILE] != "") $SETTINGS[PSNG_DISALLOW_FILE] = toArray($_REQUEST[PSNG_DISALLOW_FILE]);
	if ($_REQUEST[PSNG_DISALLOW_KEY] != "") $SETTINGS[PSNG_DISALLOW_KEY] = toArray($_REQUEST[PSNG_DISALLOW_KEY]);
	$tmp = array();
	foreach($SETTINGS[PSNG_DISALLOW_DIR] as $k => $v) {
		if ($v != '') $tmp[] = $v;
	}
	$SETTINGS[PSNG_DISALLOW_DIR] = $tmp;

	$tmp = array();
	foreach($SETTINGS[PSNG_DISALLOW_FILE] as $k => $v) {
		if ($v != '') $tmp[] = $v;
	}
	$SETTINGS[PSNG_DISALLOW_FILE] = $tmp;

	$tmp = array();
	foreach($SETTINGS[PSNG_DISALLOW_KEY] as $k => $v) {
		if ($v != '') $tmp[] = $v;
	}
	$SETTINGS[PSNG_DISALLOW_KEY] = $tmp;
	$SETTINGS[PSNG_COMPRESS_SITEMAP] = isset($_REQUEST[PSNG_COMPRESS_SITEMAP]) ? TRUE : FALSE;

	// TODO maybe try to open sitemap file

	// store settings in session
	$_SESSION[PSNG_SETTINGS] = $SETTINGS;

	// write settings to file
	$res = storeSettings($SETTINGS, $SETTINGS[PSNG_FILE_SETTINGS], "SETTINGS");
	if (!is_null($res)) {
		$LAYOUT->addWarning($res, 'Settings could not be written to file ' . $SETTINGS[PSNG_FILE_SETTINGS] . '!');
	} else {
		$LAYOUT->addSuccess('', 'Settings written to file ' . $SETTINGS[PSNG_FILE_SETTINGS] . '!');
	}

	debug($SETTINGS, 'Got and computed settings');

	return TRUE;
}

function checkUpdateStatus() {
	global $LAYOUT, $SETTINGS;
	if (array_key_exists('last_update_time', $SETTINGS) && (time() - $SETTINGS['last_update_time'] < 3600)) {
		$LAYOUT->addWarning('Checking for new updates is only allowed once per hour. Last check was ' . getDateTimeISO($SETTINGS['last_update_time']));
		return TRUE;
	}
	$SETTINGS['last_update_time'] = time();
	debug(PSNG_URL_UPDATESTATUS,'Grabbing update status');
	$res = file(PSNG_URL_UPDATESTATUS);
	debug($res,'Result of checkUpdateStatus');
	if (count($res) == 0) {
		$LAYOUT->addError("Couldn't connect to check latest version");
		return FALSE;
	}
	// current updatestatus only supports one line of response, upcoming might support more than one
	$stat = explode('---', trim($res[0]));
	$currentVersion = (isset($stat[0])) ? trim($stat[0]) : '';		// this is all that's needed for version_compare() mk/2005-11-13
	$latestRelease 	= (isset($stat[1])) ? trim($stat[1]) : '';
	$latestVersion 	= (isset($stat[2])) ? trim($stat[2]) : '';

	$comparison = version_compare($currentVersion,PSNG_VERSION);
	switch($comparison)
	{
		case +1:
			// version online is lower than version of current script (should not happen!)
			$LAYOUT->addSuccess('','Your version ('.PSNG_VERSION.') seems to be newer than the one online ('.$currentVersion.') - better hold on to it!');	# ;-) mk/2005-11-13
			break;
		case 0:
			// versions are identical
			$LAYOUT->addSuccess('','You have version '.PSNG_VERSION.'. There is no newer version available.');
			break;
		case -1:
			// version online is higher than version of current script
			$update_url = PSNG_URL_DOWNLOAD;
			$LAYOUT->addText('Please visit the download page at <a href="'.$update_url.'">'.$update_url.'</a>');
			$LAYOUT->addWarning('Latest version of this release is ' .$currentVersion,'New version available!');
	}
	return TRUE;
}

?>