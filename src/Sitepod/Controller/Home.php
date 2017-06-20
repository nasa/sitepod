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

class Home
{

    /**
     * Show settings
     * if settings are empty, set them to usable values
     * @param bool $resetSettings
     */
    public function viewSetup($resetSettings = FALSE)
    {
        /**
         * @var array $SETTINGS
         */
        global $SETTINGS;

        \Base::instance()->set('title', 'Edit settings');

        if ($resetSettings === TRUE) {
            $SETTINGS = array();
        }
        $page_root = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['SCRIPT_NAME']));
        if (is_null($SETTINGS[PSNG_PAGEROOT])) {
            $SETTINGS[PSNG_PAGEROOT] = ($page_root != '') ? $page_root : $_SERVER['DOCUMENT_ROOT'];
        }
        if (is_null($SETTINGS[PSNG_WEBSITE])) {
            $SETTINGS[PSNG_WEBSITE] = "http://" . $_SERVER['HTTP_HOST'];
        }
        if (is_null($SETTINGS[PSNG_CRAWLER_URL])) {
            $SETTINGS[PSNG_CRAWLER_URL] = $SETTINGS[PSNG_WEBSITE];
        }

        if (is_null($SETTINGS[PSNG_SITEMAP_FILE])) {
            $SETTINGS[PSNG_SITEMAP_FILE] = "/sitemap.xml";
        }
        if (is_null($SETTINGS[PSNG_TXTSITEMAP_FILE])) {
            $SETTINGS[PSNG_TXTSITEMAP_FILE] = "/sitemap.txt";
        }
        if (is_null($SETTINGS[PSNG_TEMP_DIR])) {
            $SETTINGS[PSNG_TEMP_DIR] = dirname(__FILE__) . "/temp/";
        }
        if (is_null($SETTINGS[PSNG_PUBLIC_URL])) {
            $SETTINGS[PSNG_PUBLIC_URL] = $SETTINGS[PSNG_PAGEROOT] . "/phpsitemapng.php";
        }
        if (is_null($SETTINGS[PSNG_SCAN_LOCAL])) {
            $SETTINGS[PSNG_SCAN_LOCAL] = TRUE;
        }
        if (is_null($SETTINGS[PSNG_SCAN_WEBSITE])) {
            $SETTINGS[PSNG_SCAN_WEBSITE] = TRUE;
        }
        if (is_null($SETTINGS[PSNG_PINGGOOGLE])) {
            $SETTINGS[PSNG_PINGGOOGLE] = TRUE;
        }
        if (is_null($SETTINGS[PSNG_EDITRESULT])) {
            $SETTINGS[PSNG_EDITRESULT] = PSNG_EDITRESULT_TRUE;
        }
        if (is_null($SETTINGS[PSNG_STORE_FILELIST])) {
            $SETTINGS[PSNG_STORE_FILELIST] = TRUE;
        }

        if (!isset($SETTINGS[PSNG_TIMEOUT])) {
            $SETTINGS[PSNG_TIMEOUT] = PSNG_TIMEOUT_FORCE;
        }
        if (!isset($SETTINGS[PSNG_TIMEOUT_TIME_DURATION])) {
            $SETTINGS[PSNG_TIMEOUT_TIME_DURATION] =
                (ini_get('max_execution_time') != '' && ini_get('max_execution_time') > 0)
                    ? ini_get('max_execution_time')
                    : 30;
        }
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
            $SETTINGS[PSNG_DISALLOW_KEY] = array();
            $SETTINGS[PSNG_DISALLOW_KEY][] = session_name();
            $SETTINGS[PSNG_DISALLOW_KEY][] = 'sid';
            $SETTINGS[PSNG_DISALLOW_KEY][] = 'PHPSESSID';

            $SETTINGS[PSNG_DISALLOW_KEY] = array_unique($SETTINGS[PSNG_DISALLOW_KEY]);
        }

        debug($SETTINGS[PSNG_DISALLOW_KEY], "disallow keys");
        if (is_null($SETTINGS[PSNG_CHANGEFREQ])) {
            $SETTINGS[PSNG_CHANGEFREQ] = PSNG_CHANGEFREQ_FIXED;
        }
        if (is_null($SETTINGS[PSNG_CHANGEFREQ_FIXED])) {
            $SETTINGS[PSNG_CHANGEFREQ_FIXED] = 'weekly';
        }

        if (is_null($SETTINGS[PSNG_PRIORITY])) {
            $SETTINGS[PSNG_PRIORITY] = PSNG_PRIORITY_FIXED;
        }
        if (is_null($SETTINGS[PSNG_PRIORITY_FIXED])) {
            $SETTINGS[PSNG_PRIORITY_FIXED] = 0.5;
        }

        if (is_null($SETTINGS[PSNG_LASTMOD])) {
            $SETTINGS[PSNG_LASTMOD] = PSNG_LASTMOD_FILEDATE;
        }
        if (is_null($SETTINGS[PSNG_LASTMOD_TIMEFORMAT])) {
            $SETTINGS[PSNG_LASTMOD_TIMEFORMAT] = PSNG_TIMEFORMAT_LONG;
        }

        $layout = '';
        /* It works because in startup.php you added it to the include_path */
        /** @TODO: Change it to a template system Eg.: Twig (https://twig.sensiolabs.org/) */
        require('./templates/setup_expert.php');

        \Base::instance()->set('pageTitle', 'Current settings');
        \Base::instance()->set('layout', $layout);
        echo \Template::instance()->render('templates/home.viewSetup.html');
    }

    public function setup()
    {
        $this->resetRunon();
        $this->viewSetup();
    }

    private function resetRunon()
    {
        global $SETTINGS;
        unset($SETTINGS[PSNG_TIMEOUT_DONE]);
        unset($SETTINGS[PSNG_TIMEOUT_TODO]);
        unset($SETTINGS[PSNG_TIMEOUT_FILE]);
        unset($SETTINGS[PSNG_TIMEOUT_FILE_LASTRUN]);
        unset($SETTINGS[PSNG_TIMEOUT_ACTION]);
        unset($SETTINGS[PSNG_TIMEOUT_IS]);
        unset($SETTINGS[PSNG_TIMEOUT_TIME]);
        unset($SETTINGS[PSNG_TIMEOUT_TIME_START]);
        unset($SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN]);
        unset($SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE]);
    }

}