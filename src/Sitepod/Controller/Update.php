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
namespace Sitepod\Controller;

use Sitepod\LayoutEngine;

class Update
{
    public function checkUpdateStatus()
    {
        /**
         * @var LayoutEngine $LAYOUT
         * @var array $SETTINGS
         */
        global $LAYOUT, $SETTINGS;
        if (array_key_exists('last_update_time', $SETTINGS) && (time() - $SETTINGS['last_update_time'] < 3600)) {
            $LAYOUT->addWarning('Checking for new updates is only allowed once per hour. Last check was ' . getDateTimeISO($SETTINGS['last_update_time']));
            return TRUE;
        }
        $SETTINGS['last_update_time'] = time();
        debug(PSNG_URL_UPDATESTATUS, 'Grabbing update status');
        $res = file(PSNG_URL_UPDATESTATUS);
        debug($res, 'Result of checkUpdateStatus');
        if (count($res) == 0) {
            $LAYOUT->addError("Couldn't connect to check latest version");
            return FALSE;
        }
        // current update status only supports one line of response, upcoming might support more than one
        $stat = explode('---', trim($res[0]));
        $currentVersion = (isset($stat[0])) ? trim($stat[0]) : '';      // this is all that's needed for version_compare() mk/2005-11-13

        $comparison = version_compare($currentVersion, PSNG_VERSION);
        switch ($comparison) {
            case +1:
                // version online is lower than version of current script (should not happen!)
                $LAYOUT->addSuccess('', 'Your version (' . PSNG_VERSION . ') seems to be newer than the one online (' . $currentVersion . ') - better hold on to it!');  # ;-) mk/2005-11-13
                break;
            case 0:
                // versions are identical
                $LAYOUT->addSuccess('', 'You have version ' . PSNG_VERSION . '. There is no newer version available.');
                break;
            case -1:
                // version online is higher than version of current script
                $update_url = PSNG_URL_DOWNLOAD;
                $LAYOUT->addText('Please visit the download page at <a href="' . $update_url . '">' . $update_url . '</a>');
                $LAYOUT->addWarning('Latest version of this release is ' . $currentVersion, 'New version available!');
        }
        return TRUE;
    }
}