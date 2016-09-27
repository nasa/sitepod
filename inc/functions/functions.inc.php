<?php
/*
	This is phpSitemapNG, a php script that creates your personal google sitemap
	It can be downloaded from http://enarion.net/google/
	License: GPL

	Tobias Kluge, enarion.net
*/


/**
 * misc functions
 */
function info($param, $msg = '') {
	global $SETTINGS, $LAYOUT;

	if ($param == "" && $msg == "")
		return;
	if (is_array($param)) {
		$LAYOUT->addInfo(arrToStringReadable($param, "<br>\n"),$msg);
	} else {
		$LAYOUT->addInfo($param, $msg);
	}
}

function debug($param, $msg = '') {
	global $SETTINGS, $LAYOUT;

	if (isset($_SESSION[PSNG_DEBUG]) && isset($SETTINGS[PSNG_DEBUG]))
	{
		if ($SETTINGS[PSNG_DEBUG] === TRUE && $_SESSION[PSNG_DEBUG] === TRUE) {
			if ($param == "" && $msg == "")
				return;
			if (is_array($param)) {
				$LAYOUT->addDebug(arrToStringReadable($param, "<br>\n"),$msg);
			} else {
				$LAYOUT->addDebug($param, $msg);
			}
		}
	}
}

// source: http://de2.php.net/microtime
function microtime_float(){
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

/**
 * returns a filehandle if file is accessable
 */
function openFile($filename, $writable = FALSE) {
	global $openFile_error;
	$openFile_error = "";
	// check if file exists - if yes, perform tests:
	if (file_exists($filename)) {
		// check if file is accessable
		if (!is_readable($filename)) {
			$openFile_error = "File $filename is not readable";
			return FALSE;
		}
		if ($writable && !is_writable($filename)) {
			$openFile_error = "File $filename is not writable";
			return FALSE;
		}
	} else {
		// file does not exist, try to create file
	}
	$accessLevel = 'r+';
	if ($writable === TRUE) {
		$accessLevel = 'w+';
	}

	$filehandle = @fopen($filename, $accessLevel);
	if ($filehandle === FALSE) {
		$openFile_error = "File $filename could not be opened, don't know why";
		@fclose($filehandle);

		if (!file_exists($filename)) {
			$openFile_error = "File $filename does not exist and I do not have the rights to create it!";
		}
		return FALSE;
	}
	return $filehandle;
}

/**
 * start session
 * read settings from session
 * set and return action
 */
function init() {
	global $SETTINGS, $_REQUEST, $LAYOUT;

	session_start();

	// set layout engine
	$LAYOUT = new LayoutEngine("phpSitemapNG");
	$LAYOUT->addContentFooter('<div align="center"><p>Copyright by enarion.net. This script is licensed under GPL and can be downloaded from
					<a target="_blank" href="http://enarion.net/google/">enarion.net/google/</a></p></div>');
	$LAYOUT->setTitle("create your personal google sitemap file");
	$LAYOUT->setCharSet("iso-8859-1");
	$LAYOUT->addCss('.history, .required { background-color:#E0E0E0; }');
	$LAYOUT->addCss('.source_fs { background-color:#FF70CC; }');
	$LAYOUT->addCss('.source_website { background-color:#CCFF70; }');
	$LAYOUT->addCss('.source_fs_website { background-color:#70CCFF; }');

	$LAYOUT->addCss('.notfound { background-color:#FF3030; }');
	$LAYOUT->addCss('Label {color:#000099; font-weight: bold; }');
	$LAYOUT->addCss('h1,h2,h3 {color:#000099; }');
	$LAYOUT->addCss('.error {color:#cc0000; font-weight: bold; }');
	$LAYOUT->addCss('.warning {color:#000000; font-weight: italic; }');
	$LAYOUT->addCss('.info {color:#000000; font-weight: normal; }');
	$LAYOUT->addCss('.success {color:#009900; font-weight: bold; }');
	$LAYOUT->addCss('body {color:#000000; font-family:helvetica; background-color:#ebb150; }');
	$LAYOUT->switchOffBuffer();
	print $LAYOUT->getHeaderLayout();

/* repair NOTICES mk/2005-11-08 */
	if (isset($_REQUEST[PSNG_DEBUG]))
	{
/**/
		if ($_REQUEST[PSNG_DEBUG] == 'on') {
			$SETTINGS[PSNG_DEBUG] = TRUE;
			$_SESSION[PSNG_DEBUG] = TRUE;
			debug('', 'Debug on');
		} elseif ($_REQUEST[PSNG_DEBUG] == 'off') {
			debug('', 'Debug off');
			$SETTINGS[PSNG_DEBUG] = FALSE;
			$_SESSION[PSNG_DEBUG] = FALSE;
		}
/* repair NOTICES mk/2005-11-08 */
	}
	else												# mk assume off
	{
			debug('', 'Debug off');
			$SETTINGS[PSNG_DEBUG] = FALSE;
			$_SESSION[PSNG_DEBUG] = FALSE;
	}
/**/

	if (isset($_SESSION[PSNG_SETTINGS])) $SETTINGS = array_merge($_SESSION[PSNG_SETTINGS],$SETTINGS);

	$LAYOUT->addContentHeader('<a href="'.$SETTINGS[PSNG_SCRIPT].'?action=setup" title="Edit settings">Setup</a>');
	$LAYOUT->addContentHeader('<a href="'.$SETTINGS[PSNG_SCRIPT].'?action='.PSNG_ACTION_CHECK_UPDATESTATUS.'" title="Invoke an update check at enarion.net to get information of recent versions">Check for updates</a>');
	if (isset($SETTINGS[PSNG_SETTINGS_EXECUTED][PSNG_ACTION_SETTINGS_GET]))
	{
		if ($SETTINGS[PSNG_SETTINGS_EXECUTED][PSNG_ACTION_SETTINGS_GET] == TRUE)
			$LAYOUT->addContentHeader('<a href="'.$SETTINGS[PSNG_SCRIPT].'?action=parse" title="Start the scan for files">Start scan</a>');
	}
	if (@file_exists($SETTINGS[PSNG_SITEMAP_FILE]) && ( @filesize($SETTINGS[PSNG_SITEMAP_FILE]) > 0))
		$LAYOUT->addContentHeader('<a href="'.$SETTINGS[PSNG_SITEMAP_URL].'" target="_blank" title="View the created sitemap in a new browser window">View sitemap </a>');
	$LAYOUT->addContentHeader('<div align="left"><a href="http://enarion.net/google/phpsitemapng/feedback/" target="_blank" title="Create a feedback request at enarion.net in a new browser window">Give feedback</a>');
	$LAYOUT->addContentHeader('<div align="left"><p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="enarion@enarion.net">
<input type="hidden" name="item_name" value="Development of phpSitemapNG">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="http://enarion.net/google/phpsitemapng/donated.php">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="tax" value="0">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Donate the development of phpSitemapNG" title="Donate the development of phpSitemapNG" target="_blank">
</form></p></div>');

	debug('version: '.PSNG_VERSION, 'This is phpSitemapNG');
	debug($SETTINGS, 'Merged settings');

	debug($SETTINGS[PSNG_SETTINGS_STATE], 'last state');
	$action = '';
	if (isset($_REQUEST[PSNG_ACTION])) $action = $_REQUEST[PSNG_ACTION];
	// handle some special actions, eg submit buttons without approriate hidden action tag
	if (isset($_REQUEST[PSNG_ACTION_SETTINGS_RESET])) {
		if ($_REQUEST[PSNG_ACTION_SETTINGS_RESET] != '') $action = PSNG_ACTION_SETTINGS_RESET;
	}

	$SETTINGS[PSNG_SETTINGS_STATE] = $action;
	debug($SETTINGS[PSNG_SETTINGS_STATE], "current state");


	$SETTINGS[PSNG_SETTINGS_EXECUTED][$action] = TRUE;
	$SETTINGS[PSNG_TIMEOUT_IS] = FALSE;

	$SETTINGS[PSNG_TIMEOUT_IS] = '';
	if ($SETTINGS[PSNG_TIMEOUT] == PSNG_TIMEOUT_NONE) $SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE] = time()+60*60*24; // timeout is now in 24 h

	return $action;
}

function resetRunon() {
	global $SETTINGS;
	unset($SETTINGS[PSNG_TIMEOUT_DONE]);
	unset($SETTINGS[PSNG_TIMEOUT_TODO]);
	unset($SETTINGS[PSNG_TIMEOUT_FILE]);
	unset($SETTINGS[PSNG_TIMEOUT_FILE_LASTRUN]);
	unset($SETTINGS[PSNG_TIMEOUT_ACTION]);
	unset($SETTINGS[PSNG_TIMEOUT_IS]);
	unset($SETTINGS[PSNG_TIMEOUT_TIME]);
	unset($SETTINGS[PSNG_TIMEOUT_TIME_START]);
//	unset($SETTINGS[PSNG_TIMEOUT_TIME_DURATION]);
	unset($SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN]);
	unset($SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE]);
}
/**
 * overwrite existing filelist-file
 */
function resetFiles() {
	global $SETTINGS;
	// write empty settings to file
	storeSettings(array (), $SETTINGS['files_file'], "FILES");
	// all settings are okay for now, so go on
	return '<p>Your filelist '.$SETTINGS['files_file'].' is reseted now!<br/>'.'<a href="'.$SETTINGS[PSNG_SCRIPT].'">You can now create your personal google sitemap</a></p>';
}

/**
 * checks a given filename if it exists and is writable
 * returns empty string, if okay; otherwise the error message
 */
function checkFile($filename) {
	$file = @ fopen($filename, "r");
	// check if writable
	$msg = "";
	if (!file_exists($filename)) {
		$msg = "File ".$filename." does not exist; create file and set permission with chmod to 0666";
	}
	elseif (!is_writable($filename)) {
		$msg = "File ".$filename." does exist but cannot be written; change permission with chmod to 0666";
	}
	elseif ($file === FALSE) {
		$msg = "Error while opening ".$filename." for write access. Check existence and permission of file!";
	}
	@ fclose($file);
	return $msg;
}

function getDateTimeISO($timestamp) {
	return date("Y-m-d\TH:i:s", $timestamp) . substr(date("O"),0,3) . ":" . substr(date("O"),3);
}

function getDateTimeISO_short($timestamp) {
	return date("Y-m-d", $timestamp);
}

function getFrequency($lastmod) {
	// set changefreq
	$age = time() - $lastmod;
	$change_freq = "monthly"; // default value
	if ($age < 10) {
		$change_freq = "always";
	} elseif ($age < 60*60) {
		$change_freq = "hourly";
	} elseif ($age < 60*60*24) {
		$change_freq = "daily";
	} elseif ($age < 60*60*24*7) {
		$change_freq = "weekly";
	} elseif ($age < 60*60*24*31) { // longest month has 31 days
		$change_freq = "monthly";
	} elseif ($age < 60*60*24*365) {
		$change_freq = "yearly";
	} else {
		$change_freq = "never";
	}
	return $change_freq;
}

/* write settings to file */
function storeSettings($SETTINGS, $filename, $keyname) {
	global $openFile_error;

	$file = openFile($filename, TRUE);

	if ($file === FALSE)
		return $openFile_error;

	fputs($file, "<?php\n");
	foreach ($SETTINGS as $key => $val) {
		if (is_array($val)) {
			foreach ($val as $key2 => $val2) {
				fputs($file, '$'.$keyname."['".stringToVariableName($key)."']['".stringToVariableName($key2)."'] = '".stringToVariableName($val2)."';\n");
			}
			continue;
		}
		fputs($file, '$'.$keyname."['".stringToVariableName($key)."'] = '".stringToVariableName($val)."';\n");
	}
	fputs($file, "?>\n");
	fclose($file);

	return NULL;
}

/* remove maybe existing ' in string */
function stringToVariableName($str) {
	return ((!get_magic_quotes_gpc()) ? addslashes($str) : $str);
}
function variableNameToString($var_name) {
	if(get_magic_quotes_gpc()) {
		return stripslashes($var_name);
	} else {
		$res = $var_name;
		if (substr($res, 0, 1) == "'")
			$res = substr($res, 1);
		if (substr($res, strlen($res) - 1) == "'")
			$res = substr($res, 0, strlen($res) - 1);
		return $res;
	}
}

/* explodes the array for given deliminator and returns a correct array */
function toArray($str, $delim = "\n") {
	$res = array ();
	$res = explode($delim, $str);

	for ($i = 0; $i < count($res); $i ++) {
		$res[$i] = trim($res[$i]);
	}

	return $res;
}

/* returns a string of all entries of array with delim */
function arrToStringReadable($array, $delim) {
	$res = "";
	if (is_array($array)) {
		$i = 0;
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$res .= "$key: ".arrToStringReadable($val, $delim);
			} else {
				$res .= "$key: $val";
			}
			if ($i < (count($array) - 1))
				$res .= $delim;
			$i ++;
		}
	}
	return $res;
}

/* returns a string of all entries of array with delim */
function arrToString($array, $delim = "\n") {
	$res = "";
	if (is_array($array)) {
		for ($i = 0; $i < count($array); $i ++) {
			$res .= $array[$i];
			if ($i < (count($array) - 1))
				$res .= $delim;
		}
	}
	return $res;
}
?>