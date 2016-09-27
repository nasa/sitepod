<?php
/* 
	This is phpSitemapNG, a php script that creates your personal google sitemap
	It can be downloaded from http://enarion.net/google/
	License: GPL
	
	Tobias Kluge, enarion.net
*/
print $LAYOUT->getFooterLayout();
// check and maybe remove timeout stuff
if ($SETTINGS[PSNG_TIMEOUT] == PSNG_TIMEOUT_NONE || $SETTINGS[PSNG_TIMEOUT_IS] !== TRUE) {
//	echo ('Removing timeout information(done, todo, file, file_lastrun)!');
	unset($SETTINGS[PSNG_TIMEOUT_DONE]);
	unset($SETTINGS[PSNG_TIMEOUT_TODO]);
	unset($SETTINGS[PSNG_TIMEOUT_FILE]);
	unset($SETTINGS[PSNG_TIMEOUT_FILE_LASTRUN]);
	unset($SETTINGS[PSNG_TIMEOUT_ACTION]);	
} else {
    $timeout = true;
}
unset($SETTINGS[PSNG_TIMEOUT_IS]);
unset($SETTINGS[PSNG_TIMEOUT_TIME]);
unset($SETTINGS[PSNG_TIMEOUT_TIME_START]);
// unset($SETTINGS[PSNG_TIMEOUT_TIME_DURATION]); should be stored in settings
unset($SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN]);
unset($SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE]);

$_SESSION[PSNG_SETTINGS] = $SETTINGS;
if ($timeout) {
	echo '<script type="text/javascript">window.location.href="'.$SETTINGS['script'].'?action=parse";</script>';
}
?>
