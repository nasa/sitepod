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

switch ($state) {
    case PSNG_ACTION_SETTINGS_RESET:
        viewSetup(TRUE);
        break;

    case PSNG_ACTION_SETTINGS_SETUP:
        resetRunon();
        viewSetup();
        break;

    case PSNG_ACTION_SETTINGS_GET: // & parse
        getSettings();

    case PSNG_ACTION_SETTINGS_PARSE:
        $FILE = parseFilesystem();

        // check for timeout
        if ($SETTINGS[PSNG_TIMEOUT_ACTION] != '') {
            break;
        }
        // if no timeout, print result or write it
        if ($SETTINGS[PSNG_EDITRESULT] == PSNG_EDITRESULT_TRUE) {
            displaySitemapEdit($FILE);
        } else {
            writeSitemap($FILE);
        }
        break;

    case PSNG_ACTION_SETTINGS_WRITESITEMAP_USERINPUT:
        writeSitemapUserinput();
        break;

    case PSNG_ACTION_SETTINGS_PINGGOOGLE:
        submitPageToGoogle();
        break;

    case PSNG_ACTION_CHECK_UPDATESTATUS:
        checkUpdateStatus();
        break;

    default:
        viewSetup();
        break;
}

require_once('inc/shutdown.php');

?>