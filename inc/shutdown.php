<?php
/* 
 * This file is part of Sitepod.
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
print $LAYOUT->getFooterLayout();
// check and maybe remove timeout stuff
if ($SETTINGS[PSNG_TIMEOUT] == PSNG_TIMEOUT_NONE || $SETTINGS[PSNG_TIMEOUT_IS] !== TRUE) {
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
