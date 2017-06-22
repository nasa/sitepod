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

use Sitepod\Log\Logger;

class Update
{
    public function checkUpdateStatus()
    {
        /**
         * @var array $SETTINGS
         */
        global $SETTINGS;
        if (array_key_exists('last_update_time', $SETTINGS) && (time() - $SETTINGS['last_update_time'] < 3600)) {
            \Base::instance()->set('updateMessage', 'Checking for new updates is only allowed once per hour. Last check was ' . getDateTimeISO($SETTINGS['']));
        }
        else {
            $SETTINGS['last_update_time'] = time();
            Logger::instance()->debug('Grabbing update status: ' . PSNG_URL_UPDATESTATUS);
            $res = file(PSNG_URL_UPDATESTATUS);
            Logger::instance()->debug('Result of checkUpdateStatus: ' . $res);
            if (count($res) == 0) {
                \Base::instance()->set('updateMessage', "Couldn't connect to check latest version");
            }
            else {
                // current update status only supports one line of response, upcoming might support more than one
                $stat = explode('---', trim($res[0]));
                $currentVersion = (isset($stat[0])) ? trim($stat[0]) : '';      // this is all that's needed for version_compare() mk/2005-11-13

                $comparison = version_compare($currentVersion, PSNG_VERSION);
                switch ($comparison) {
                    case +1:
                        // version online is lower than version of current script (should not happen!)
                        \Base::instance()->set('updateMessage', 'Your version (' . PSNG_VERSION . ') seems to be newer than the one online (' . $currentVersion . ') - better hold on to it!');  # ;-) mk/2005-11-13
                        break;
                    case 0:
                        // versions are identical
                        \Base::instance()->set('updateMessage', 'You have version ' . PSNG_VERSION . '. There is no newer version available.');
                        break;
                    case -1:
                        // version online is higher than version of current script
                        $update_url = PSNG_URL_DOWNLOAD;
                        \Base::instance()->set('updateMessage', 'Please visit the download page at <a href="' . $update_url . '">' . $update_url . '</a>');
                        \Base::instance()->set('updateMessage', 'Latest version of this release is ' . $currentVersion, 'New version available!');
                }
            }
        }
        echo \Template::instance()->render('templates/update.checkUpdateStatus.html');
    }
}