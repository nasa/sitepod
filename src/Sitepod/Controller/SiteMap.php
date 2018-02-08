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
use Sitepod\Util;

class SiteMap
{

    public function parse()
    {
        global $SETTINGS;
        $FILE = parseFilesystem();

        // check for timeout
        if ($SETTINGS[PSNG_TIMEOUT_ACTION] != '') {
            return;
        }
        // if no timeout, print result or write it
        if ($SETTINGS[PSNG_EDITRESULT] == PSNG_EDITRESULT_TRUE) {
            \Base::instance()->set('title', 'Result of scan');
            \Base::instance()->set('files', $FILE);
            if ($SETTINGS[PSNG_LASTMOD] != PSNG_LASTMOD_DISSABLED) {
                \Base::instance()->set('lastModEnabled', true);
            }
            if ($SETTINGS[PSNG_CHANGEFREQ] != PSNG_CHANGEFREQ_DISSABLED) {
                \Base::instance()->set('changeFreqEnabled', true);
            }
            if ($SETTINGS[PSNG_PRIORITY] != PSNG_PRIORITY_DISSABLED) {
                \Base::instance()->set('priorityEnabled', true);
            }
            \Base::instance()->set('changeFreqList', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never']);
            \Base::instance()->set('priorityList', ['1,0', '0,9', '0,8', '0,7', '0,6', '0,5', '0,4', '0,3', '0,2', '0,1', '0,0']);

            \Base::instance()->set('explanations', $this->getExplanations($FILE));

            \Base::instance()->set('pageTitle', 'Found '. count($FILE) .' files');
            echo \Template::instance()->render('templates/sitemap.parse.html');
        } else {
            writeSitemap($FILE);
        }
    }

    public function resetSettings()
    {
        (new Home())->viewSetup(TRUE);
    }

    public function getSettings()
    {
        $this->collectSettings();
        $this->parse();
    }

    public function writeSiteMapUserInput()
    {
        // TODO add deselected files from user into "blacklist" in temp directory
        global $SETTINGS, $_REQUEST;
        \Base::instance()->set('title', 'Writing sitemap');

        // create the sitemap file
        $filesGot = $_REQUEST['FILE'];
        $files = array();
        foreach ($filesGot as $key => $value) {
            $files[$key] = array();
            $files[$key][PSNG_FILE_ENABLED] = isset($value[PSNG_FILE_ENABLED]) ? '1' : '';
            $files[$key][PSNG_FILE_URL] = $value[PSNG_FILE_URL];
            $files[$key][PSNG_LASTMOD] = $value[PSNG_LASTMOD];
            $files[$key][PSNG_CHANGEFREQ] = $value[PSNG_CHANGEFREQ];
            $files[$key][PSNG_PRIORITY] = $value[PSNG_PRIORITY];
        }

        if($SETTINGS[PSNG_STORE_FILELIST] != '') {
            $res = storeSettings($files, $SETTINGS[PSNG_FILE_FILES], "FILES");
            if (!is_null($res)) {
                Logger::instance()->warning('Filelist-Cache could not be written to file ' . $SETTINGS[PSNG_FILE_FILES] . '!', ['result' => $res]);
            } else {
                Logger::instance()->info('Filelist-Cache written to file ' . $SETTINGS[PSNG_FILE_FILES] . '!');
            }
        }

        writeSitemap($files);

        echo \Template::instance()->render('templates/sitemap.writeSiteMapUserInput.html');
    }

    public function submitPageToGoogle()
    {
        /** @var array $SETTINGS */
        global $SETTINGS;
        \Base::instance()->set('title', 'Submit sitemap to google');

        $res = fopen("http://www.google.com/webmasters/sitemaps/ping?sitemap=".urlencode($SETTINGS['website'].$SETTINGS[PSNG_SITEMAP_URL]),"r");
        if ($res === FALSE) {
            \Base::instance()->set('result', 'Error while submitting '.$SETTINGS[PSNG_SITEMAP_URL].'to google!');
        }

        $str = "";
        while (!feof($res)) {
            $str .= fread($res, 1000);
        }
        fclose($res);
        \Base::instance()->set('pageTitle', 'Your sitemap file has been successfully sent to google!');
        \Base::instance()->set('result', 'Result was: <i>'. strip_tags($str, '<br> <h2> <h1>') . '</i>');

        echo \Template::instance()->render('sitemap.submitPageToGoogle.html');
    }

    /**
     * extract settings from passed user values
     */
    private function collectSettings()
    {
        /**
         * @var array $_REQUEST
         * @var array $SETTINGS
         */
        global $_REQUEST, $SETTINGS;
        \Base::instance()->set('title', "Store settings");

        // TODO check values we got from user

        // put user values into settings array
        $SETTINGS[PSNG_WEBSITE] = $_REQUEST[PSNG_WEBSITE];
        if (substr($SETTINGS[PSNG_WEBSITE],0,4) != "http") {
            // user hasn't put a http in front of the url of the website
            $SETTINGS[PSNG_WEBSITE] = "http://".$SETTINGS[PSNG_WEBSITE];
        }
        // TODO test this, if it's working with e.g. http://enarion.net
        if (substr($SETTINGS[PSNG_WEBSITE], -1) != '/' && substr($SETTINGS[PSNG_WEBSITE], -1) != "\\") {
            $SETTINGS[PSNG_WEBSITE] .= '/';
        }

        $SETTINGS[PSNG_PAGEROOT] = addslashes($_REQUEST[PSNG_PAGEROOT]);
        if (substr($SETTINGS[PSNG_PAGEROOT], -1) != '/' && substr($SETTINGS[PSNG_PAGEROOT], -1) != "\\") {
            $SETTINGS[PSNG_PAGEROOT] .= '/';
        }

        $SETTINGS[PSNG_SITEMAP_FILE] = $_REQUEST[PSNG_SITEMAP_FILE];
        $SETTINGS[PSNG_TXTSITEMAP_FILE] = $_REQUEST[PSNG_TXTSITEMAP_FILE];
        $SETTINGS[PSNG_SITEMAP_URL] = basename($SETTINGS[PSNG_SITEMAP_FILE]);
        $SETTINGS[PSNG_TXTSITEMAP_URL] = basename($SETTINGS[PSNG_TXTSITEMAP_FILE]);

        if (isset($_REQUEST[PSNG_TIMEOUT])) {
            $SETTINGS[PSNG_TIMEOUT] = PSNG_TIMEOUT_FORCE;
            if ($_REQUEST[PSNG_TIMEOUT_TIME_DURATION] != $SETTINGS[PSNG_TIMEOUT_TIME_DURATION]) {
                $SETTINGS[PSNG_TIMEOUT_TIME_DURATION] = $_REQUEST[PSNG_TIMEOUT_TIME_DURATION];
                $SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE] = $SETTINGS[PSNG_TIMEOUT_TIME_START] + $SETTINGS[PSNG_TIMEOUT_TIME_DURATION] - $SETTINGS[PSNG_TIMEOUT_TIME_SHUTDOWN] -1;
            }
        } else {
            $SETTINGS[PSNG_TIMEOUT] = PSNG_TIMEOUT_NONE;
        }

        if (isset($_REQUEST[PSNG_CRAWLER_URL])) {
            $SETTINGS[PSNG_CRAWLER_URL] = $_REQUEST[PSNG_CRAWLER_URL];
        }

        if (isset($_REQUEST[PSNG_SCAN_LOCAL])) {
            $SETTINGS[PSNG_SCAN_LOCAL] = TRUE;
        } else {
            $SETTINGS[PSNG_SCAN_LOCAL] = FALSE;
        }
        if (isset($_REQUEST[PSNG_SCAN_WEBSITE])){
            $SETTINGS[PSNG_SCAN_WEBSITE] = TRUE;
        } else {
            $SETTINGS[PSNG_SCAN_WEBSITE] = FALSE;
        }
        if (isset($_REQUEST[PSNG_PINGGOOGLE])) {
            $SETTINGS[PSNG_PINGGOOGLE] = TRUE;
        } else {
            $SETTINGS[PSNG_PINGGOOGLE] = FALSE;
        }
        if (isset($_REQUEST[PSNG_SCAN_WEBSITE])) {
            $SETTINGS[PSNG_SCAN_WEBSITE] = TRUE;
        } else {
            $SETTINGS[PSNG_SCAN_WEBSITE] = FALSE;
        }
        if (isset($_REQUEST[PSNG_SCAN_WEBSITE_LEVEL])) {
            $SETTINGS[PSNG_SCAN_WEBSITE_LEVEL] = $_REQUEST[PSNG_SCAN_WEBSITE_LEVEL];
        } else {
            $SETTINGS[PSNG_SCAN_WEBSITE_LEVEL] = 0;
        }

        $SETTINGS[PSNG_EDITRESULT] = ($_REQUEST[PSNG_EDITRESULT] != '') ? PSNG_EDITRESULT_TRUE : PSNG_EDITRESULT_FALSE;
        $SETTINGS[PSNG_STORE_FILELIST] = $_REQUEST[PSNG_STORE_FILELIST];

        $SETTINGS[PSNG_LASTMOD] = $_REQUEST[PSNG_LASTMOD];
        $SETTINGS[PSNG_LASTMOD_TIMEFORMAT] = $_REQUEST[PSNG_LASTMOD_TIMEFORMAT];

        $SETTINGS[PSNG_CHANGEFREQ] = $_REQUEST[PSNG_CHANGEFREQ];
        $SETTINGS[PSNG_CHANGEFREQ_FIXED] = $_REQUEST[PSNG_CHANGEFREQ_FIXED];

        $SETTINGS[PSNG_PRIORITY] = $_REQUEST[PSNG_PRIORITY];
        $SETTINGS[PSNG_PRIORITY_FIXED] = $_REQUEST[PSNG_PRIORITY_FIXED];

        if ($_REQUEST[PSNG_DISALLOW_DIR] != "") {
            $SETTINGS[PSNG_DISALLOW_DIR] = Util::toArray($_REQUEST[PSNG_DISALLOW_DIR]);
        }
        if ($_REQUEST[PSNG_DISALLOW_FILE] != "") {
            $SETTINGS[PSNG_DISALLOW_FILE] = Util::toArray($_REQUEST[PSNG_DISALLOW_FILE]);
        }
        if ($_REQUEST[PSNG_DISALLOW_KEY] != "") {
            $SETTINGS[PSNG_DISALLOW_KEY] = Util::toArray($_REQUEST[PSNG_DISALLOW_KEY]);
        }
        $tmp = array();
        foreach($SETTINGS[PSNG_DISALLOW_DIR] as $k => $v) {
            if ($v != '') {
                $tmp[] = $v;
            }
        }
        $SETTINGS[PSNG_DISALLOW_DIR] = $tmp;

        $tmp = array();
        foreach($SETTINGS[PSNG_DISALLOW_FILE] as $k => $v) {
            if ($v != '') {
                $tmp[] = $v;
            }
        }
        $SETTINGS[PSNG_DISALLOW_FILE] = $tmp;

        $tmp = array();
        foreach($SETTINGS[PSNG_DISALLOW_KEY] as $k => $v) {
            if ($v != '') {
                $tmp[] = $v;
            }
        }
        $SETTINGS[PSNG_DISALLOW_KEY] = $tmp;
        $SETTINGS[PSNG_COMPRESS_SITEMAP] = isset($_REQUEST[PSNG_COMPRESS_SITEMAP]);

        // TODO maybe try to open sitemap file

        // store settings in session
        $_SESSION[PSNG_SETTINGS] = $SETTINGS;

        // write settings to file
        $res = storeSettings($SETTINGS, $SETTINGS[PSNG_FILE_SETTINGS], "SETTINGS");
        if (!is_null($res)) {
            Logger::instance()->warning('Settings could not be written to file ' . $SETTINGS[PSNG_FILE_SETTINGS] . '!', ['result' => $res]);
        } else {
            Logger::instance()->info('Settings written to file ' . $SETTINGS[PSNG_FILE_SETTINGS] . '!');
        }

        Logger::instance()->debug('Got and computed settings: ' . Util::arrToStringReadable($SETTINGS, ','));

        return TRUE;
    }

    private function getExplanations($FILE)
    {
        $explanations = [];
        foreach ($FILE as $filename => $fileinfo) {
            if ($fileinfo[PSNG_HTML_SOURCE] == PSNG_HTML_SOURCE_FS) {
                $explanations['source_fs'] = ['class' => 'source_fs', 'text' => 'A row with this background means that this file can found on your local filesystem'];
            }
            if ($fileinfo[PSNG_HTML_SOURCE] == PSNG_HTML_SOURCE_WEBSITE) {
                $explanations['source_website'] = ['class' => 'source_website', 'text' => 'A row with this background means that this file has been found with the crawler engine'];
            }
            if ($fileinfo[PSNG_HTML_SOURCE] == PSNG_HTML_SOURCE_FS_WEBSITE) {
                $explanations['source_fs_website'] = ['class' => 'source_fs_website', 'text' => 'A row with this background means that this file is stored on filesystem and there are links to this file.'];
            }
            if (isset($fileinfo[PSNG_HTML_HISTORY])) {
                $explanations['history'] = ['class' => 'history', 'text' => 'A cell with this background means that this file is already stored in your local cached filelist'];
            }
        }
        return $explanations;
    }
}