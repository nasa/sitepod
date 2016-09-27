<?php
/*
	This is phpSitemapNG, a php script that creates your personal google sitemap
	It can be downloaded from http://enarion.net/google/
	License: GPL

	Tobias Kluge, enarion.net
*/

/**
 * parse filesystem for files
 */
function parseFilesystem($displayResultForEditing = TRUE) {
	global $SETTINGS, $LAYOUT;
	$FILE = array();

	// search for files on local file system
	if(is_null($SETTINGS[PSNG_PAGEROOT]) || $SETTINGS[PSNG_PAGEROOT] == "") {
		$LAYOUT->addError('','Page root not set');
		return FALSE;
	}
	$SETTINGS[PSNG_SETTINGS_STATE] = PSNG_ACTION_SETTINGS_PARSE;
	$FILES_CACHE = array();

	if($SETTINGS[PSNG_STORE_FILELIST] != '') {
		$FILES_CACHE = loadCachedFiles();
		if (count($FILES_CACHE)>0) {
			$LAYOUT->addInfo('', 'Loaded '.count($FILES_CACHE) . ' files from file cache.');
		}
	}

	if (isset($SETTINGS[PSNG_TIMEOUT_ACTION]) && isset($SETTINGS[PSNG_TIMEOUT_TODO]))
	{
		if($SETTINGS[PSNG_TIMEOUT_ACTION] != PSNG_TIMEOUT_NONE && $SETTINGS[PSNG_TIMEOUT_TODO] != '') {
			$FILE = $SETTINGS[PSNG_TIMEOUT_FILE_LASTRUN];
			if (count($FILE) > 0) {
				$LAYOUT->addInfo('', 'Loaded '.count($FILE) . ' files from last run before timeout occured.');
			}
		}
	}

	// scan filesystem for existing files
	if (	(!breakSession()) && // check for timeout
			$SETTINGS[PSNG_SCAN_LOCAL] != '' && // check if we have to scan filesystem
			(	$SETTINGS[PSNG_TIMEOUT] == PSNG_TIMEOUT_NONE ||	// go on, if no timeout selected
				(												// OR: go on, if rerun from last timeout
					$SETTINGS[PSNG_TIMEOUT_ACTION] == '' ||
					$SETTINGS[PSNG_TIMEOUT_ACTION] == PSNG_TIMEOUT_ACTION_FS
				)
			)
		) {
		$LAYOUT->addInfo('', 'Scanning filesystem for files now...');
		$SETTINGS[PSNG_TIMEOUT_ACTION] = PSNG_TIMEOUT_ACTION_FS;
		$FILE = runFilesystemHandler($FILE, $FILES_CACHE);
		if(count($FILE)>0) $LAYOUT->addInfo('', 'Found '.count($FILE). ' files on local filesystem.');
		debug($FILE, "Result from filesystem scan");
	}

	// crawl website for (dynamic) links
	if ((!breakSession()) && $SETTINGS[PSNG_SCAN_WEBSITE] != '' && ($SETTINGS[PSNG_TIMEOUT] == PSNG_TIMEOUT_NONE || ($SETTINGS[PSNG_TIMEOUT_ACTION] == '' || $SETTINGS[PSNG_TIMEOUT_ACTION] == PSNG_TIMEOUT_ACTION_WEBSITE))) {
		$LAYOUT->addInfo('', 'Crawling website now...');
		$SETTINGS[PSNG_TIMEOUT_ACTION] = PSNG_TIMEOUT_ACTION_WEBSITE;
		$FILE = runCrawler($FILE, $FILES_CACHE);
		if(!breakSession()&&count($FILE)>0) $LAYOUT->addInfo('', 'Found '.count($FILE). ' files on website.');
		debug($FILE, "Result from website crawler");
	}

	// link "/" to "/index.*"
	if (! breakSession()) {
		ksort($FILE);
		reset($FILE);
		$n_before = count($FILE);
		foreach ($FILE as $file => $fileinfo) {
			if (!(strpos($file, '?') === FALSE)) continue;
			if (!(strpos($file, '/index.') === FALSE)) {
				# mk/2005-11-09 fixed indents
				if (array_key_exists(dirname($file).'/', $FILE)) {
					debug(dirname($file).'/', 'removing file from list of files, because there exists an index file');
					unset($FILE[dirname($file).'/']);
				} else {
					$FILE[dirname($file).'/'] = $FILE[$file];
					$FILE[dirname($file).'/']['file_url'] = dirname($file).'/';
					unset($FILE[$file]);
				}
			}
		}
//		$LAYOUT->addInfo('', 'Matched '.($n_before-count($FILE)). ' directories with index files');

	}
	// if timeout - store current settings and files
	if (breakSession()) {
		$SETTINGS[PSNG_TIMEOUT_FILE_LASTRUN] = $FILE;
//		$LAYOUT->addInfo('', 'Already computed ' . count($FILE) . ' files.');
		$LAYOUT->addInfo('', 'Number of files that are in the todo list: ' . count($SETTINGS[PSNG_TIMEOUT_TODO]));
	} else {
		$SETTINGS[PSNG_TIMEOUT_IS] = FALSE;
	}

	return $FILE;
}

/**
 * returns true if there is some time left
 */
function breakSession($force = FALSE) {
	global $SETTINGS, $LAYOUT;
	if ($SETTINGS[PSNG_TIMEOUT] == PSNG_TIMEOUT_NONE) return FALSE;
	if ($SETTINGS[PSNG_TIMEOUT_IS] != '') return TRUE;

	$t2 = microtime_float();
	if (($t2 >= $SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE]) || ($force == TRUE)) {
		$header = $SETTINGS[PSNG_SCRIPT].'?'.PSNG_ACTION.'='.$SETTINGS[PSNG_SETTINGS_STATE];

		$LAYOUT->addInfo('Please click <a href="'.$header.'"><b>here</b></a> to continue scanning your site.', 'Timeout occured');
		$_SESSION[PSNG_SETTINGS] = $SETTINGS;
		debug($SETTINGS, "settings in breakSession");

		$SETTINGS[PSNG_TIMEOUT_IS] = TRUE;

		return TRUE;
	}

	return FALSE;
}

/**
 * load cached file information from file
 */
function loadCachedFiles() {
	global $SETTINGS;
	include_once(dirname(__FILE__).'/../../'.$SETTINGS[PSNG_FILE_FILES]);

	if (isset($FILES)) {
		foreach ($FILES as $numb => $arr) {
			debug($arr, 'Got file number '.$numb.' from file cache');
			$file_url = $arr[PSNG_FILE_URL];
			$FILES_CACHE[$file_url][PSNG_FILE_URL] = 		variableNameToString($arr[PSNG_FILE_URL]);
			$FILES_CACHE[$file_url][PSNG_LASTMOD] = 		variableNameToString($arr[PSNG_LASTMOD]);
			$FILES_CACHE[$file_url][PSNG_CHANGEFREQ] = 		variableNameToString($arr[PSNG_CHANGEFREQ]);
			$FILES_CACHE[$file_url][PSNG_PRIORITY] = 		variableNameToString($arr[PSNG_PRIORITY]);
			$FILES_CACHE[$file_url][PSNG_FILE_ENABLED] = 	variableNameToString($arr[PSNG_FILE_ENABLED]);
		}
		debug($FILES_CACHE, "FILES_CACHE");
	} else {
		$FILES_CACHE = array();
		debug($FILES_CACHE, "No file-cache found!");
	}

	return $FILES_CACHE;
}

/**
 * scan filesystem, compute file information depending on settings,
 * check for entry in file cache
 *
 * returns a list of fileinformation
 * 		array[fileurl] = array(
 * 				url=>'url',
 * 				lastmod=>'lastmod',
 * 				changefreq=>'changefreq',
 * 				priority => 'priority'
 * 				enabled => 'true' / 'false'
 *
 * 			)
 *
 * TODO add handling of given $FILE
 */
function runFilesystemHandler($FILE, $FILES_CACHE) {
	global $SETTINGS;
	// TODO improve this
	if ($SETTINGS[PSNG_TIMEOUT] != PSNG_TIMEOUT_NONE && $SETTINGS[PSNG_TIMEOUT_DONE] != '') {
		debug('', "Running FilesystemHandler from last point");
		$fsh = new FilesystemHandler($SETTINGS[PSNG_PAGEROOT], $SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE]);
		$fsh->setTodo($SETTINGS[PSNG_TIMEOUT_TODO]);
		$fsh->setDone($SETTINGS[PSNG_TIMEOUT_DONE]);
		$fsh->setFiles($SETTINGS[PSNG_TIMEOUT_FILE]);
	} else {
		$fsh = new FilesystemHandler($SETTINGS[PSNG_PAGEROOT], $SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE]);
		$fsh->setTodo(array($SETTINGS[PSNG_PAGEROOT]));
	}
	$fsh->setForbiddenDirectories($SETTINGS[PSNG_DISALLOW_DIR]);
	$fsh->setForbiddenFiles($SETTINGS[PSNG_DISALLOW_FILE]);
	$size = $fsh->start();

	$FILE = array();
	if (! $fsh->hasFinished()) {
		// store current data into session
		$SETTINGS[PSNG_TIMEOUT_TODO] = $fsh->getTodo();
		$SETTINGS[PSNG_TIMEOUT_DONE] = $fsh->getDone();
		$SETTINGS[PSNG_TIMEOUT_FILE] = $fsh->getFiles();
		$SETTINGS[PSNG_TIMEOUT_ACTION] = PSNG_TIMEOUT_ACTION_FS;
	}  else {
		while($fsh->hasNext()) {
			$filename = $fsh->getNext(); // returns the filename
			$file_url = $SETTINGS[PSNG_WEBSITE]. ((substr($filename, 0,1) != '/') ? $filename : substr($filename, 1));
			$lastmod = '';
			if ($SETTINGS[PSNG_LASTMOD] == PSNG_LASTMOD_FILEDATE) $lastmod = $fsh->getLastModificationTime($SETTINGS[PSNG_PAGEROOT].$filename);

			$fileinfo = handleURL($file_url, $lastmod);
			$fileinfo = handleURLCached($FILES_CACHE, $fileinfo);
			$fileinfo[PSNG_HTML_SOURCE] = PSNG_HTML_SOURCE_FS;

			$FILE[$file_url] = $fileinfo;
		}
		$SETTINGS[PSNG_TIMEOUT_ACTION] = '';
	}
	return $FILE;
}

/**
 * runs crawler for given settings, returns array of files
 * TODO update to new stuff
 */
function runCrawler($FILE, $FILES_CACHE) {
	global $SETTINGS, $LAYOUT;

	$urlToCrawl = (isset($SETTINGS[PSNG_CRAWLER_URL]) && $SETTINGS[PSNG_CRAWLER_URL] != $SETTINGS[PSNG_WEBSITE])
						? $SETTINGS[PSNG_CRAWLER_URL]
						: $SETTINGS[PSNG_WEBSITE];

	$url = parse_url($urlToCrawl);
	$path = $url['path'];
	if ((substr($urlToCrawl,-1) != '/') && ($url['path'] == '' )) { 
		$path .= '/'; 
		$urlToCrawl .= '/'; 
	} 

	// check if we have a already started scan
	debug($SETTINGS[PSNG_TIMEOUT], 'PSNG_TIMEOUT');
	if (isset($SETTINGS[PSNG_TIMEOUT_TODO])) debug($SETTINGS[PSNG_TIMEOUT_TODO], 'PSNG_TIMEOUT_TODO');		# !!! 'repair' may not be correct mk/2005-11-08

	if ($SETTINGS[PSNG_TIMEOUT] != PSNG_TIMEOUT_NONE && isset($SETTINGS[PSNG_TIMEOUT_TODO])) { // check if we're running in TIMEOUT mode
		debug('', "Running crawler engine from last point");
		$crawler = new Crawler($urlToCrawl, $SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE]);
		$crawler->setTodo($SETTINGS[PSNG_TIMEOUT_TODO]);
		$crawler->setDone($SETTINGS[PSNG_TIMEOUT_DONE]);
		$crawler->setFiles($SETTINGS[PSNG_TIMEOUT_FILE]);
	} else { // we are not in timeout mode, no rerun
		$crawler = new Crawler($urlToCrawl, $SETTINGS[PSNG_TIMEOUT_TIME_DEADLINE]);
		$crawler->setTodo(array($urlToCrawl));
	}
	$crawler->setForbiddenKeys($SETTINGS[PSNG_DISALLOW_KEY]);
	$crawler->setForbiddenDirectories($SETTINGS[PSNG_DISALLOW_DIR]);
	$crawler->setForbiddenFiles($SETTINGS[PSNG_DISALLOW_FILE]);

	//Set the directory to forbid the crawler to follow below it
	$crawler->setDirectory($path);

	$crawler->start();

	if (! $crawler->hasFinished()) {
		// store current data into session
		$SETTINGS[PSNG_TIMEOUT_TODO] = $crawler->getTodo();
		$SETTINGS[PSNG_TIMEOUT_DONE] = $crawler->getDone();
		$SETTINGS[PSNG_TIMEOUT_FILE] = $crawler->getFiles();
		$SETTINGS[PSNG_TIMEOUT_ACTION] = PSNG_TIMEOUT_ACTION_WEBSITE;
	}  else {
		while($crawler->hasNext()) {
			$fileinfo = $crawler->getNext(); // returns an array

			if (!isset($fileinfo['http_status']))	$fileinfo['http_status'] = '';
			if (!isset($fileinfo['file']))			$fileinfo['file'] = '';
			if (!isset($fileinfo['lastmod']))		$fileinfo['lastmod'] = '';
			if (!isset($fileinfo['changefreq']))	$fileinfo['changefreq'] = '';
			if (!isset($fileinfo['priority']))		$fileinfo['priority'] = '';

			$http_status = $fileinfo['http_status'];
			// create and setup valid values
			$fileinfo = handleURL($fileinfo['file'], $fileinfo['lastmod'], $fileinfo['changefreq'], $fileinfo['priority']);

			$fileinfo = handleURLCached($FILES_CACHE, $fileinfo);

			// handle some website specific stuff
			if ($http_status == "404") {
				$fileinfo[PSNG_FILE_ENABLED] = '';
				$fileinfo[PSNG_HTML_STATUS] = 'class="notfound"';
			}
//			info($fileinfo, 'Fileinfo from crawler');
			// handle if the file exists on filesystem and on website
			if (array_key_exists($fileinfo[PSNG_FILE_URL], $FILE)) {
				$fileinfo = handleDoubleEntryFilesystemWebsite($FILE[$fileinfo[PSNG_FILE_URL]], $fileinfo);
			}
//			info($fileinfo, 'Fileinfo after handle double entry');

			if (isset($fileinfo[PSNG_HTML_SOURCE]))
			{
				if ($fileinfo[PSNG_HTML_SOURCE] == PSNG_HTML_SOURCE_FS) {
					$fileinfo[PSNG_HTML_SOURCE] = PSNG_HTML_SOURCE_FS_WEBSITE;
				} else {
					$fileinfo[PSNG_HTML_SOURCE] = PSNG_HTML_SOURCE_WEBSITE;
				}
			}
			else
			{
				$fileinfo[PSNG_HTML_SOURCE] = PSNG_HTML_SOURCE_WEBSITE;
			}


			$FILE[$fileinfo[PSNG_FILE_URL]] = $fileinfo;
		}
		$SETTINGS[PSNG_TIMEOUT_ACTION] = '';
	}

	return $FILE;
}

/**
 * checks, if there is an entry in filelist cache;
 *  if yes, update fileinformation;
 * 		returns fileinformation (given or updated)
 */
function handleURLCached($FILES_CACHE, $fileInfo) {
	global $SETTINGS;
	$filename = $fileInfo[PSNG_FILE_URL];

	if ((isset($FILES_CACHE)) && (isset($FILES_CACHE[$filename]) != '') && ($FILES_CACHE[$filename] != '')) {
		$fileInfo[PSNG_FILE_ENABLED] = $FILES_CACHE[$filename][PSNG_FILE_ENABLED];
		if(isset($FILES_CACHE[$filename][PSNG_CHANGEFREQ]) && ($FILES_CACHE[$filename][PSNG_CHANGEFREQ] != '')) {
			$fileInfo[PSNG_CHANGEFREQ] = $FILES_CACHE[$filename][PSNG_CHANGEFREQ];
		}

		if(isset($FILES_CACHE[$filename][PSNG_PRIORITY]) && ($FILES_CACHE[$filename][PSNG_PRIORITY] != '')) {
			$fileInfo[PSNG_PRIORITY] = $FILES_CACHE[$filename][PSNG_PRIORITY];
		}

		$fileInfo[PSNG_HTML_HISTORY] = 'class="history"';
	}

	return $fileInfo;
}

/**
 * returns a correct entry for a fileinfo with given information and settings
 */
function handleURL($url, $lastmod = '', $changefreq = '', $priority = '') {
	global $SETTINGS;
	$res = array();

	$res[PSNG_FILE_URL] = $url;

	// default: file is enabled and will be handled
	$res[PSNG_FILE_ENABLED] = 'checked';

	// handle lastmod
	if ($SETTINGS[PSNG_LASTMOD] == PSNG_LASTMOD_FILEDATE) {
		$res[PSNG_LASTMOD] = $lastmod;
	} elseif ($SETTINGS[PSNG_LASTMOD] == PSNG_LASTMOD_CURRENT) {
		$res[PSNG_LASTMOD] = time();
	} elseif ($SETTINGS[PSNG_LASTMOD] == PSNG_LASTMOD_DISSABLED) {
		$res[PSNG_LASTMOD] = '';
	}
	// format timestamp appropriate to settings
	if ($res[PSNG_LASTMOD] != '') {
		if ($SETTINGS[PSNG_LASTMOD_TIMEFORMAT] == PSNG_TIMEFORMAT_SHORT) {
			$res[PSNG_LASTMOD] =  getDateTimeISO_short($res[PSNG_LASTMOD]);
		} elseif ($SETTINGS[PSNG_LASTMOD_TIMEFORMAT] == PSNG_TIMEFORMAT_LONG) {
			$res[PSNG_LASTMOD] =  getDateTimeISO($res[PSNG_LASTMOD]);
		}
	}

	// handle changefreq
	if($SETTINGS[PSNG_CHANGEFREQ] == PSNG_CHANGEFREQ_FIXED) {
		$res[PSNG_CHANGEFREQ] = $SETTINGS[PSNG_CHANGEFREQ_FIXED];
	} elseif ($SETTINGS[PSNG_CHANGEFREQ] == PSNG_CHANGEFREQ_DYNAMIC) {
		if ($changefreq != '') {
			$res[PSNG_CHANGEFREQ] = $changefreq;
		}else {
			$res[PSNG_CHANGEFREQ] = getFrequency($lastmod);
		}
	} elseif ($SETTINGS[PSNG_CHANGEFREQ] == PSNG_CHANGEFREQ_DISSABLED) {
		$res[PSNG_CHANGEFREQ] = '';
	}

	// handle priority
	if($SETTINGS[PSNG_PRIORITY] == PSNG_PRIORITY_FIXED) {
		$res[PSNG_PRIORITY] = $SETTINGS[PSNG_PRIORITY_FIXED];
	} elseif($SETTINGS[PSNG_PRIORITY] == PSNG_PRIORITY_AUTOMATIC) {
		// TODO IMPLEMENTME!!! calculate priority depending on #links, hierachie level, ...
		$res[PSNG_PRIORITY] = $SETTINGS[PSNG_PRIORITY_FIXED];
	} elseif($SETTINGS[PSNG_PRIORITY] == PSNG_PRIORITY_DISSABLED) {
		$res[PSNG_PRIORITY] = '';
	}

	return $res;
}


/**
 * adds the values of the 2nd array to them on the first array (if they don't exist)
 *
 */
function handleDoubleEntryFilesystemWebsite($fs, $website) {
	$res = ($fs != null) ? $fs : $website;

	if($fs[PSNG_FILE_URL] == $fs[PSNG_FILE_URL]) {
		// check lastmod
		if (($fs[PSNG_LASTMOD] != '' && $website[PSNG_LASTMOD] != '') ||($fs[PSNG_LASTMOD] != '' && $website[PSNG_LASTMOD] == '')) {
			$res[PSNG_LASTMOD] = $fs[PSNG_LASTMOD];
		} else { // empty value or only in website
			$res[PSNG_LASTMOD] = $website[PSNG_LASTMOD];
		}

		// check changefreq
		if (($fs[PSNG_CHANGEFREQ] != '' && $website[PSNG_CHANGEFREQ] != '') || ($fs[PSNG_CHANGEFREQ] != '' && $website[PSNG_CHANGEFREQ] == '')) {
			$res[PSNG_CHANGEFREQ] = $fs[PSNG_CHANGEFREQ];
		} else { // empty value or only in website
			$res[PSNG_CHANGEFREQ] = $website[PSNG_CHANGEFREQ];
		}

		// check priority
		if (($fs[PSNG_PRIORITY] != '' && $website[PSNG_PRIORITY] != '') || ($fs[PSNG_PRIORITY] != '' && $website[PSNG_PRIORITY] == '')) {
			$res[PSNG_PRIORITY] = $fs[PSNG_PRIORITY];
		} else { // empty value or only in website
			$res[PSNG_PRIORITY] = $website[PSNG_PRIORITY];
		}
	};

	// add missing keys from 1st array
	foreach (array_diff(array_keys($fs), array_keys($website)) as $id => $key) {
		$res[$key] = $fs[$key];
	}
	// add missing keys from the 2nd array
	foreach (array_diff(array_keys($website), array_keys($fs)) as $id => $key) {
		$res[$key] = $website[$key];
	}

	return $res;
}

/**
 * displays the edit page for list of files
 */
function displaySitemapEdit($FILE) {
	global $SETTINGS, $LAYOUT;
	$LAYOUT->setTitle("Result of scan");

	require(PSNG_FILE_TEMPLATE_EDIT_FILES);

	$LAYOUT->addText($layout, 'Found '. count($FILE) .' files');
}

/**
 * writes sitemap to file
 */
function writeSitemap($FILE) {
	global $SETTINGS, $openFile_error, $LAYOUT;
	$LAYOUT->setTitle("Writing sitemap");
	$gsg = new GsgXml($SETTINGS[PSNG_WEBSITE]);

	$numb = 0;
	$txtfilehandle = null;

	if (isset($SETTINGS[PSNG_TXTSITEMAP_FILE]) && strlen($SETTINGS[PSNG_TXTSITEMAP_FILE])>0) {
		$txtfilehandle = openFile($SETTINGS[PSNG_PAGEROOT]. $SETTINGS[PSNG_TXTSITEMAP_FILE], TRUE);
		if ($txtfilehandle === FALSE) {
			$LAYOUT->addError($openFile_error, 'Could not write sitemap');
			return FALSE;
		}
		
	}

	foreach ($FILE as $numb => $value) {
		if ($value[PSNG_FILE_ENABLED] != '') {
			debug($value, "Adding file ".$value[PSNG_FILE_URL]);
			if (isset($txtfilehandle)) fputs($txtfilehandle, $value[PSNG_FILE_URL]."\n");
			if ($gsg->addUrl($value[PSNG_FILE_URL], FALSE, $value[PSNG_LASTMOD], FALSE, $value[PSNG_CHANGEFREQ], $value[PSNG_PRIORITY]) === FALSE) {
				$LAYOUT->addError($value[PSNG_FILE_URL], 'Could not add file to sitemap' . $gsg->errorMsg);
			}
		} else {
			debug($value[PSNG_FILE_URL], 'Not enabled, so not writing file to sitemap');
		}
	}

	$filehandle = openFile($SETTINGS[PSNG_PAGEROOT]. $SETTINGS[PSNG_SITEMAP_FILE], TRUE);
	if ($filehandle === FALSE) {
		$LAYOUT->addError($openFile_error, 'Could not write sitemap');
		return FALSE;
	}
	$xml = $gsg->output(TRUE, $SETTINGS[PSNG_COMPRESS_SITEMAP], FALSE);

	fputs ($filehandle, $xml);
	fclose ($filehandle);
	if (isset($txtfilehandle)) fclose($txtfilehandle);

	if ($numb > 50000) {
		$LAYOUT->addWarning('Not implemented: split result into files with only 50000 entries','Only 50000 entries are allowed in one sitemap file at the moment!');
	}
	$LAYOUT->addSuccess('Sitemap successfuly created and saved to <a href="'.$SETTINGS[PSNG_SITEMAP_URL].'" target="_blank">'.basename($SETTINGS[PSNG_SITEMAP_FILE]).'</a>!');
	if (isset($SETTINGS[PSNG_TXTSITEMAP_FILE]) && strlen($SETTINGS[PSNG_TXTSITEMAP_FILE])>0) $LAYOUT->addSuccess('Txt-Sitemap successfuly created and saved to <a href="'.$SETTINGS[PSNG_TXTSITEMAP_URL].'" target="_blank">'.basename($SETTINGS[PSNG_TXTSITEMAP_FILE]).'</a>!');
	$LAYOUT->addText('<form action="' . $SETTINGS[PSNG_SCRIPT] . '" method="post">' ."\n".
			'<input type="hidden" name="'.PSNG_SETTINGS_ACTION.'" value="'.PSNG_ACTION_SETTINGS_PINGGOOGLE.'">' . "\n".
			'<input type="Submit" value="Submit to google" name="submit">' . "\n".
			'</form>' . "\n");

	return TRUE;
}


/**
 *
 */
function writeSitemapUserinput() {
	// TODO add deselected files from user into "blacklist" in temp directory
	global $SETTINGS, $openFile_error, $_REQUEST, $LAYOUT;
	$LAYOUT->setTitle('Writing sitemap');

	$gsg = new GsgXml($SETTINGS[PSNG_WEBSITE]);

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
			$LAYOUT->addWarning($res, 'Filelist-Cache could not be written to file ' . $SETTINGS[PSNG_FILE_FILES] . '!');
		} else {
			$LAYOUT->addSuccess('', 'Filelist-Cache written to file ' . $SETTINGS[PSNG_FILE_FILES] . '!');
		}
	}

	writeSitemap($files);

	return TRUE;
	//header('Content-type: application/xml; charset="utf-8"',TRUE);
}


/**
 * submit page to google
 */
function submitPageToGoogle() {
	global $SETTINGS, $LAYOUT;
	$LAYOUT->setTitle('Submit sitemap to google');

	$res = fopen("http://www.google.com/webmasters/sitemaps/ping?sitemap=".urlencode($SETTINGS['website'].$SETTINGS[PSNG_SITEMAP_URL]),"r");
	if ($res === FALSE) {
		$LAYOUT->addError('', 'Error while submitting '.$SETTINGS[sitemap_url].'to google!');
	}

	$str = "";
	while (!feof($res)) {
		$str .= fread($res, 1000);
	}
	fclose($res);
	$LAYOUT->addSuccess('Result was: <i>'.	strip_tags($str, '<br> <h2> <h1>')	. '</i>',
							 'Your sitemap file has been successfuly sent to google!');
	return TRUE;
}
?>