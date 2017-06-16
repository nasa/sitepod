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

require_once('inc/startup.php');

$f3 = \Base::instance();

$f3->route('GET /', '\Sitepod\Controller\Home->viewSetup');
$f3->route('GET /setup', '\Sitepod\Controller\Home->setup');

$f3->route('GET /check_updatestatus', function() {
    checkUpdateStatus();
});
$f3->route('GET /parse', function() {
    global $SETTINGS;
    $FILE = parseFilesystem();

    // check for timeout
    if ($SETTINGS[PSNG_TIMEOUT_ACTION] != '') {
        return;
    }
    // if no timeout, print result or write it
    if ($SETTINGS[PSNG_EDITRESULT] == PSNG_EDITRESULT_TRUE) {
        displaySitemapEdit($FILE);
    } else {
        writeSitemap($FILE);
    }
});
$f3->route('POST /', function () {
    global $SETTINGS;
    if (isset($_REQUEST[PSNG_ACTION_SETTINGS_RESET])) {
        if ($_REQUEST[PSNG_ACTION_SETTINGS_RESET] != '') {
            viewSetup(TRUE);
        }
    }
    else {
        getSettings();
        $FILE = parseFilesystem();
        // check for timeout
        if ($SETTINGS[PSNG_TIMEOUT_ACTION] != '') {
            return;
        }
        // if no timeout, print result or write it
        if ($SETTINGS[PSNG_EDITRESULT] == PSNG_EDITRESULT_TRUE) {
            displaySitemapEdit($FILE);
        } else {
            writeSitemap($FILE);
        }
    }
});
$f3->route('POST /writeSitemapUserinput', function () {
    writeSitemapUserinput();
});
$f3->route('POST /pinggoogle', function () {
    submitPageToGoogle();
});

$f3->run();

require_once('inc/shutdown.php');
