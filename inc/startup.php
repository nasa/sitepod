<?php
/* This file is part of Sitepod.
 *
 * Sitepod is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Sitepod is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sitepod.  If not, see <http://www.gnu.org/licenses/>.
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

include_once(dirname(__FILE__).'/../vendor/autoload.php');

/* Set timezone against user IP address. */
$geoData = (new Sitepod\GeoIp())->getGeoData($_SERVER['REMOTE_ADDR']);
$timeZone = new DateTimeZone('UTC'); // Set a default timezone
if ($geoData['time_zone'] !== '') {
    $timeZone = new DateTimeZone($geoData['time_zone']); // When there is a time_zone for the client IP address, use it.
}
date_default_timezone_set($timeZone->getName());

include_once(dirname(__FILE__).'/Ontology.php');

include_once(dirname(__FILE__).'/functions/functions.inc.php');
include_once(dirname(__FILE__).'/functions/parsing.inc.php');

$SETTINGS[PSNG_FILE_SETTINGS] =     'settings/settings.inc.php';
$SETTINGS[PSNG_FILE_FILES] =        'settings/files.inc.php';

include_once(dirname(__FILE__).'/../'.$SETTINGS[PSNG_FILE_SETTINGS]);

if ($_SERVER['SCRIPT_NAME'] != '' && (strpos($_SERVER['SCRIPT_NAME'], 'index.php') !== FALSE || strpos($_SERVER['SCRIPT_NAME'], 'cron.php') !== FALSE)) {
    $SETTINGS[PSNG_SCRIPT] = $_SERVER['SCRIPT_NAME'];
} elseif ($_SERVER['PATH_INFO'] != '') {
    $SETTINGS[PSNG_SCRIPT] = $_SERVER['PATH_INFO'];
} elseif ($_SERVER['ORIG_PATH_INFO'] != '') { // only for FastCGI ?
    $SETTINGS[PSNG_SCRIPT] = $_SERVER['ORIG_PATH_INFO'];
} else {
    echo "error, couldn't extract script name!";
}

$SETTINGS['base'] = dirname($SETTINGS[PSNG_SCRIPT]). '/';

$state = init();
$SETTINGS[PSNG_TIMEOUT_AUTOMATIC] = ($timeout_after === $timeout_before);

// check if this could be executed before init()
if ($SETTINGS[PSNG_TIMEOUT] != PSNG_TIMEOUT_NONE) {
    $SETTINGS[PSNG_TIMEOUT_TIME_START] = microtime_float();
    $SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN] = 3;
    $SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE] = $SETTINGS[PSNG_TIMEOUT_TIME_START] + $SETTINGS[PSNG_TIMEOUT_TIME_DURATION] - $SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN];
}
?>