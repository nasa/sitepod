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
namespace Sitepod;
/*
TODO handle getting and sending of cookies
Format - in header: "Set-Cookie: $cookie_name=$cookie_value; path=$cookie_path"
*/

use Sitepod\Log\Logger;

define("PSNG_CRAWLER_MAX_FILESIZE", 100000); // only 100k data will be scanned
define("PSNG_CRAWLER_MAX_GETFILE_TIME", 5); //timeout in seconds as a float value
class Crawler {
    var $host = '';
    var $protocol = '';
    var $forbiddenKeys = array ();
    var $forbidden_dir = array ();
    var $forbidden_files = array ();

    var $timeout = 3;
    var $fileCounter = 0;
    var $url = '';
    var $withWWW = FALSE;
    var $cur_item = 0;
    var $keys = array ();

    var $files = array ();
    var $visitedUrls = array ();
    var $todo = array ();
    var $deadline;
    var $base = '';
    var $cookies = array();

    function __construct($host, $deadline = 0) {
        $url = parse_url($host);
        if ($url) {
            if ($url['scheme'] != "") {
                $this->protocol = $url['scheme'];
            } else {
                $this->protocol = "http";
            }
            $this->host = $url['host'];
            if (substr($this->host, 0, 3) == 'www') {
                $this->withWWW = TRUE;
            }
        }

        $this->url = $this->protocol.'://'.$this->host.'/';
        $this->todo[] = $this->url;
        $this->deadline = $deadline;
    }

    /**
     * crawles all files that are in the todo list
     * algorithm: breadth first search (former algorithm: dfs)
     */
    function start() {
        reset($this->todo);
        while (($this->deadline == 0) || (($this->deadline - $this->microtime_float()) > 0)) {
            $url = array_pop($this->todo);
            if (is_null($url) || $url == '') {
                break;
            }
            $this->_getFilesForURL($url);
        }
        ksort($this->files);
        reset($this->files);
        return count($this->files);
    }

    function microtime_float() {
        list ($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    function getTodo() {
        return $this->todo;
    }

    function getFiles() {
        return $this->files;
    }

    function getDone() {
        return $this->visitedUrls;
    }

    function setTodo($todo) {
        $this->todo = $todo;
    }

    function setFiles($files) {
        if (is_array($files)) {
            $this->files = $files;
        }
    }

    function setDone($done) {
        $this->done = $done;
    }

    function setDirectory($dir) {
        $this->path = $dir;
    }


    /**
     * returns number of files
     */
    function size() {
        return count(array_keys($this->files));
    }

    function hasFinished() {
        return (count($this->todo) == 0);
    }
    /**
     * returns TRUE when the current item is not the last item
     * behaves like in java
     */
    function hasNext() {
        if ($this->size() > $this->cur_item) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * returns the current item
     * behaves like in java
     */
    function getNext() {
        if (count($this->keys) == 0) {
            $this->keys = array_keys($this->files);
        }
        if ($this->hasNext()) {
            $tmp = $this->files[$this->keys[$this->cur_item]];
            $this->cur_item++;
            return $tmp;
        }
        return NULL;
    }

    /**
     * adds list of links extracted from this file $url
     * @param string $url
     * @return boolean
     */
    function _getFilesForURL($url) {
        $this->visitedUrls[] = $url;

        // if allready in list of files, return
        if (array_key_exists($url, $this->files)) {
            return false;
        }

        // check for non local file links that refers to another host
        if (!($this->_isLocal($url))) {
            return false;
        }

        // fetch content for given url
        $res = $this->_getURL($url);

        // extract headers
        $info = $this->_handleHeaders($res['header']);
        $res = $res['content'];

        if ($info['http_status'] >= '400' && $info['http_status'] < '499') {
            // we have an error - webpage is not accessible, just leave it
            return false;
        }

        // if not allready in list of files, add it
        if (!array_key_exists($url, $this->files) && $info['location'] == '') {
            $info['file'] = $url;
            $this->files[$url] = $info;
            $this->fileCounter++;
        } elseif ($info['location'] == '') {
            return false;
        } else {
        }

        // check location tag (when got a 302 response from webserver)
        $result = array ();
        if ($info['location'] != '') {
            $res = '<a href="'.$info['location'].'"> </a>';
        } else {
            Logger::instance()->info('Computing '.$url);
        }

        // remove html comments
        $a_begin = 0;
        while (TRUE) {
            $a_begin = strpos($res, '<!--', $a_begin);
            if ($a_begin === FALSE) {
                break; // no comment tag found, break
            }

            $a_end = strpos($res, '-->', $a_begin +3);
            if ($a_end === FALSE) {
                break; // no comment end tag found, break
            }

            $a_end += 3;
            $res = substr_replace($res, '', $a_begin, ($a_end - $a_begin));
        }

        // contribution by vvkov
        preg_match_all("/<[Aa][^>]*[Hh][Rr][Ee][Ff]=['\"]([^\"'>]+)[^>]*>/",$res ,$urls); // update by TK, 2005-07-27
        $urls_count = count( $urls[1] );

        if (preg_match("/<[Bb][Aa][Ss][Ee][^>]*[Hh][Rr][Ee][Ff]=['\"]([^\"'>]+)[^>]*>/", $res, $matches)) {
            $this->base = $matches[1];
        }

        $ts_begin = $this->microtime_float();
        while ((($ts_middle = ($this->microtime_float()-$ts_begin)) < PSNG_CRAWLER_MAX_GETFILE_TIME) && $urls_count > 0 ) {
            $thisurl =  trim(str_replace('&amp;', '&', $urls[1][--$urls_count]));
            if ($thisurl == '' || (strcasecmp(substr($thisurl, 0, strlen('javascript:')), 'javascript:') == 0)) {
                continue;
            }
            // filter out links to fragment ids (same resource) - added mk/2005-11-13
            if ('#' == $thisurl{0}) {
                continue;
            }

            $absUrl1 = $this->_absolute($thisurl, $url);
            $absUrl2 = $this->_removeForbiddenKeys($absUrl1);

            // remove "//"
            $start = (strpos($absUrl2, '//') + 3);
            $end = strpos($absUrl2, '?', $start);
            if ($end === FALSE) {
                $end = strlen($absUrl2);
            }
            $absUrl = substr($absUrl2, 0, $start).str_replace('//', '/', substr($absUrl2, $start, ($end - $start))).substr($absUrl2, $end);

            if ($this->_isLocal($absUrl)) {
                $result[] = $absUrl;
            }

            // just break this loop when a timeout occurs
            if (($this->deadline != 0) && (($this->deadline - $this->microtime_float()) < 0)) {
                Logger::instance()->debug("Global timeout");
                break;
            }
        }

        $result = array_unique($result);
        foreach ($result as $id => $file) {
            if (!in_array($file, $this->visitedUrls) && !array_key_exists($file, $this->files)) {
                // check forbidden files
                if ($this->checkFileName($file)) {
                    continue;
                }
                // check forbidden directories
                if ($this->checkDirectoryName($file)) {
                    continue;
                }

                // add file to todo list
                array_push($this->todo, $file);
            } // else: file already in list
        }

        return TRUE;
    }

    function _isLocal($givenURL) {
        if (preg_match(',^(ftp://|mailto:|news:|javascript:|telnet:|callto:),i', $givenURL)) {
            return FALSE;
        }

        $url = parse_url($givenURL);

        $startDir = $this->host . $this->path;
        $curentDir = $url["host"] . $url["path"];


        $retproto = (substr($curentDir, 0, strlen($startDir)) == $startDir);

        return $retproto;
    }

    /**
     * WAS: only allowed masking char: * (before and/or after search string)
     *
     * TODO check this with more data
     * @param string $filename
     * @return bool
     */
    function checkFileName($filename) {
        $filename = substr($filename, strrpos($filename, '/') + 1);
        if (is_array($this->forbidden_files) && count($this->forbidden_files) > 0) {
            foreach ($this->forbidden_files as $id => $file) {
                if ($file == '') {
                    continue;
                }
                $pos = strpos($filename, $file);
                if ($pos === FALSE) {
                    continue;
                }
                return TRUE;
            }
        }
        return FALSE;
    }

    function checkDirectoryName($directory) {
        $directory = substr($directory, 0, strrpos($directory, '/') + 1); // with last "/"
        if (is_array($this->forbidden_dir) && count($this->forbidden_dir) > 0) {
            foreach ($this->forbidden_dir as $id => $dir) {
                if ($dir == '') {
                    continue;
                }
                $pos = strpos($directory, $dir);
                if ($pos === FALSE) {
                    continue;
                }
                return TRUE;
            }
        }
        return FALSE;
    }

    function _handleHeaders($header) {
        $res = array();
        $res['http_status'] = '';
        $res['lastmod'] = '';
        $res['date'] = '';
        $res['size'] = '';
        $res['location'] = '';
        // TODO what about http result? after 'HTTP/' => split(" " ...) => [1]
        if (is_array($header)) {
        foreach ($header as $key => $value) {
            if ($key == '' && substr($value, 0, strlen('HTTP/'))) {
                $s = explode(" ", $value);
                $res['http_status'] = $s[1];
            } elseif ($key == "Last-Modified") {
                $res['lastmod'] = strtotime(trim($value)); // no dynamic (php/other script) generated page
            } elseif ($key == "Date") {
                $res['date'] = strtotime(trim($value));
            } elseif ($key == "Content-Length") {
                $res['size'] = trim($value);
            } elseif ($key == "Location") {
                $res['location'] = trim($value);
            } elseif ($key == 'Set-Cookie') {
                $parts = explode(";", trim($value));
                $cookie_name = '';
                $cookie = array();
                foreach ($parts as $id => $part) {
                    $p = explode('=', trim($part));
                    $cookie[$p[0]] = $p[1];
                    if ($p[0] != 'path' && strpos($p[0], 'expires') === FALSE && $p[0] != 'domain') {
                        $cookie_name = $p[0];
                    }
                }
                // add cookie if not already set
                if (!isset($this->cookies[$cookie_name])) {
                    $this->cookies[$cookie_name] = $cookie;
                    $this->forbiddenKeys[] = $cookie_name;
                }
            } elseif ($key == "Pragma") {
                $pragma = trim($value);
                if ($pragma == "no-cache") { // handle non-cached files -> normaly dynamic created pages
                    if (!isset ($res['lastmod'])) {
                        $res['lastmod'] = $res['date'];
                    }
                    $res['changefreq'] = 'always';
                }
            }
        }
        }
        if ($res['date'] != '' && $res['lastmod'] == '') {
            $res['lastmod'] = $res['date'];
        }
        return $res;
    }

    function _removeForbiddenKeys($url) {
        $paramsStart = strpos($url, '?');
        if ($paramsStart !== FALSE) { // url has no parameters, don't search for keys
            foreach ($this->forbiddenKeys as $id => $key) {
                if ($key == '') {
                    continue; // empty key => ignore it
                }
                $start = strpos($url, $key, $paramsStart);
                while ($start) {
                    $end = strpos($url, '&', $start);
                    if ($end !== FALSE) {
                        $url = substr($url, 0, $start).substr($url, $end);
                    } else {
                        $url = substr($url, 0, $start);
                    }
                    $start = strpos($url, $key, $paramsStart);
                } // else: does not contain key
            }
        }
        // remove anchor links : beginning with # to the end of the url
        if (strpos($url, '#') !== FALSE) {
            $url = substr($url, 0, strpos($url, '#'));
        }
        // remove empty & and ?
        while (substr($url, strlen($url) - 1) == "&") {
            $url = substr($url, 0, strlen($url) - 1);
        }
        while (substr($url, strlen($url) - 1) == "?") {
            $url = substr($url, 0, strlen($url) - 1);
        }
        return $url;
    }

    function _getURL($urlString) {
        $url = parse_url($urlString);
        $url_scheme = isset($url['scheme']) ? $url['scheme'] : '';
        $url_host = isset($url['host']) ? $url['host'] : '';
        $url_port = isset($url['port']) ? $url['port'] : '';
        $url_path = isset($url['path']) ? $url['path'] : '';
        $url_path = str_replace(' ', '%20', $url_path); // replace spaces in url
        $url_query = isset($url['query']) ? $url['query'] : '';
        $cookie_string = '';
        if (count($this->cookies) > 0) {
            foreach ($this->cookies as $cookie_name => $cookie) {
                // check path - dumb approach (only check if url contains cookie path)
                if (strpos($urlString, $cookie['path'])) {
                    $cookie_string .= $cookie_name . '=' . $cookie[$cookie_name] . '; ';
                }
            }
            if (strlen($cookie_string) > 0) {
                $cookie_string = 'Cookie: ' . $cookie_string ."\r\n";
            }
        }

        if ($url_port == '') {
            if ($url_scheme == 'https') {
                $url_port = "443";
            } else {
                $url_port = "80";
            }
        }
        $fp = fsockopen($url_host, $url_port, $errno, $errstr, $this->timeout);
        if ($fp === FALSE) {
            Logger::instance()->debug('Could not open connection for '.$urlString.' (host: '.$url_host.', port:'.$url_port.'), Errornumber: '.$errno . ', ' . $errstr);
            return array('header' => array(), 'content' => '');
        }
        $query_encoded = '';
        if ($url_query != '') {
            $query_encoded = '?';
            foreach (explode('&', $url_query) as $id => $quer) {
                $v = explode('=', $quer);
                if ($v[1] != '') {
                    $query_encoded .= $v[0].'='.rawurlencode($v[1]).'&';
                } else {
                    $query_encoded .= $v[0].'&';
                }
            }
            $query_encoded = substr($query_encoded, 0, strlen($query_encoded) - 1);
            $query_encoded = str_replace('%2B','+', $query_encoded);
        }

        $get = "GET ".$url_path.$query_encoded." HTTP/1.1\r\n";
        $get .= "Host: ".$url_host."\r\n";
        $get .= "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; phpSitemapNG ".PSNG_VERSION.")\r\n";
        $get .= "Referer: ".$url_scheme.'://'.$url_host.$url_path."\r\n";
        $get .= $cookie_string;
        $get .= "Connection: close\r\n\r\n";
        Logger::instance()->debug('GET-Query: ' . str_replace("\n", "<br>\n", $get));
        socket_set_blocking($fp, TRUE);
        fwrite($fp, $get);

        $ts_begin = $this->microtime_float();
        // source for chunk-decoding: http://www.phpforum.de/archiv_13065_fsockopen@end@chunked@geht@nicht_anzeigen.html

        // get headers
        $currentHeader = '';
        while ( '' != ($line=trim(fgets($fp, 1024))) ) {
            if ( FALSE !== ($pos=strpos($line, ':')) ) {
                $currentHeader = substr($line, 0, $pos);
                $header[$currentHeader] = trim(substr($line, $pos+1));
            } else {
                @$header[$currentHeader] .= $line;
            }
        }

        // check for chunk encoding
        if (isset($header['Transfer-Encoding']) && $header['Transfer-Encoding'] == 'chunked') {
            $chunk = hexdec(fgets($fp, 1024));
        } else {
            $chunk = -1;
        }

        // check file size
        if (isset($header['Content-Length']) && $header['Content-Length'] > PSNG_CRAWLER_MAX_FILESIZE) {
            Logger::instance()->info("File size ". $header['Content-Length'] . " of ".$urlString." exceeds file size limit of ".PSNG_CRAWLER_MAX_FILESIZE." byte!");
            fclose($fp);
            return array('header' => $header, 'content' => '');
        }

        // get content
        $res = '';
        while ($chunk != 0 && !feof($fp)) {
            if ($chunk > 0) {
                 $part = fread($fp, $chunk);
                 $chunk -= strlen($part);
                 $res .= $part;

                 if ($chunk == 0){
                     if (fgets($fp, 1024) != "\r\n") {
                         Logger::instance()->debug('Error in chunk-decoding');
                     }
                     $chunk = hexdec(fgets($fp, 1024));
                 }
            } else {
                 $res .= fread($fp, 1024);
            }
            // handle local timeout for fetching file
            if (($ts_middle = $this->microtime_float() - $ts_begin) > PSNG_CRAWLER_MAX_GETFILE_TIME) {
                break;
            }
            // handle global timeout:
            if (($this->deadline != 0) && (($this->deadline - $this->microtime_float()) < 0)) {
                break;
            }
        }
        fclose($fp);

        return array('header' => $header, 'content' => $res);
    }

    // based from: http://www.php-faq.de/q/q-regexp-links-absolut.html
    /**
     * Purpose: turn a link $relative found in the resource $absolute
     * (which must be a fully-qualified URI) into another fully-qualified
     * ("absolute") URI.
     * The $absolute parameter is assumed to contain a valid URI *without*
     * a fragment ID part: no checks are done; $relative can be any kind of
     * link found in this resource.
     *
     * Modified by Marjolein Katsma to support links with only a fragment id
     * or with only GET parameters.
     */
    function _absolute($relative, $absolute) {
        // Link ist schon absolut
        if (preg_match(',^(https?://|ftp://|mailto:|news:|javascript:|telnet:|callto:),i', $relative)) {
            // hostname is not the same (with/without www) than the one used in the link
            if (substr($relative, 0, 4) == 'http') {
                $url = parse_url($relative);
                if ($url['host'] != $this->host && ((("www.".$url['host']) == $this->host) && $this->withWWW || ($url['host'] == ("www.".$this->host)) && !$this->withWWW)) {
                    $relative = str_replace($url['host'], $this->host, $relative); // replace hostname that differes from local
                }
                // is pure hostname without path - so add a /
                if (!array_key_exists('path', $url) || $url['path'] == '' && substr($relative, -1) != '/') {
                    $relative .= '/';
                }
            }
            return $relative;
        }

        // parse_url() nimmt die URL auseinander
        $url = parse_url($absolute);

        // dirname() erkennt auf / endende URLs nicht
        if ($url['path'] { strlen($url['path']) - 1 } == '/') {
            $dir = substr($url['path'], 0, strlen($url['path']) - 1);
        }
        else {
            $dir = dirname($url['path']);
        }

        // absoluter Link auf dem gleichen Server
        if ($relative{0} == '/') {
            $relative = substr($relative, 1);
            $dir = '';
        }

        // set it to default host // TK
        if ($url['host'] != $this->host && (strpos($url['host'], $this->host) || strpos($this->host, $url['host']))) {
            $url['host'] = $this->host;
        }

        // Link f�ngt mit ./ an
        if (substr($relative, 0, 2) == './') {
            $relative = substr($relative, 2);
        }

        // Referenzen auf h�her liegende Verzeichnisse aufl�sen
        else {
            while (substr($relative, 0, 3) == '../') {
                $relative = substr($relative, 3);
                $dir = substr($dir, 0, strrpos($dir, '/'));
            }
        }

        // if base is set, add it.
        if (strlen($this->base)) {
            return $this->base . urldecode($relative);
        }

        // volle URL zur�ckgeben
        return sprintf('%s://%s%s/%s', $url['scheme'], $url['host'], $dir, urldecode($relative));
    }


    /* better compare function: contains */
    function _fl_contains($key, $array) {
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $id => $val) {
                $pos = @ strpos($key, $val);
                if ($pos === FALSE) {
                    continue;
                }
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * set list of forbidden directories
     * @param array $directories
     */
    function setForbiddenDirectories($directories = array ()) {
        $this->forbidden_dir = $directories;
    }

    /**
     * set list of forbidden files
     * @param array $files
     */
    function setForbiddenFiles($files = array ()) {
        $this->forbidden_files = $files;
    }

    function setForbiddenKeys($keys) {
        $this->forbiddenKeys = $keys;
    }
}
