<?php
/**
	This is phpSitemapNG", a php script that creates your personal google sitemap
	It can be downloaded from http://enarion.net/google/
	License: GPL

	Tobias Kluge", enarion.net

	this is a little ontology that defines some common names and descriptions
*/

// generic strings
define("PSNG_VERSION", '1.5.3');
define("PSNG_FILE_LIMIT", 500);

define("PSNG_DEBUG", 'debug');
define("PSNG_SCRIPT", 'script');
define("PSNG_SETTINGS", 'settings');
define("PSNG_SETTINGS_STATE", 'state');
define("PSNG_SETTINGS_ACTION", 'action');
define("PSNG_SETTINGS_EXECUTED", 'executed');

// define timeout settings
define("PSNG_TIMEOUT", 'timeout');
define("PSNG_TIMEOUT_NONE", 'timeout_none');
define("PSNG_TIMEOUT_FORCE", 'timeout_force');
define("PSNG_TIMEOUT_AUTOMATIC", 'timeout_automatic');
define("PSNG_TIMEOUT_ACTION", 'timeout_action');
define("PSNG_TIMEOUT_ACTION_FS", 'timeout_fs');
define("PSNG_TIMEOUT_ACTION_WEBSITE", 'timeout_website');

define("PSNG_TIMEOUT_IS", "timeout_is");
define("PSNG_TIMEOUT_TIME", 'timeout_time');
define("PSNG_TIMEOUT_TIME_START", 'timeout_time');
define("PSNG_TIMEOUT_TIME_DURATION", 'timeout_duration');
define("PSNG_TIMEOUT_TIME_SHUTDOWN", 'timeout_shutdown');
define("PSNG_TIMEOUT_TIME_DEADLINE", 'timeout_deadline');
define("PSNG_TIMEOUT_DONE", 'timeout_done');
define("PSNG_TIMEOUT_TODO", 'timeout_todo');
define("PSNG_TIMEOUT_FILE", 'timeout_file');
define("PSNG_TIMEOUT_FILE_LASTRUN", 'timeout_file_lastrun');


// setting strings
define("PSNG_PAGEROOT", 'page_root');
define("PSNG_WEBSITE", 'website');
define("PSNG_PINGGOOGLE", 'ping_google');
define("PSNG_COMPRESS_SITEMAP", 'compress_sitemap');
define("PSNG_EDITRESULT", 'edit_result');
define("PSNG_EDITRESULT_TRUE", 'edit_result_TRUE');
define("PSNG_EDITRESULT_FALSE", 'edit_result_FALSE');
define("PSNG_STORE_FILELIST", 'store_filelist'); // TODO necessary with upcoming Sitemap?

define("PSNG_SCAN", 'scan');
define("PSNG_SCAN_LOCAL", 'scan_local');
define("PSNG_SCAN_WEBSITE", 'scan_website');
define("PSNG_SCAN_WEBSITE_LEVEL", 'scan_website_level');

define("PSNG_CRAWLER_URL", 'crawler_url');

define("PSNG_DISALLOW_DIR", 'disallow_dir');
define("PSNG_DISALLOW_FILE", 'disallow_file');
define("PSNG_DISALLOW_KEY", 'disallow_key');

define("PSNG_TEMP_DIR", 'temp_dir');
define("PSNG_PUBLIC_URL", 'public_url');

define("PSNG_SITEMAP_URL", 'sitemap_url');
define("PSNG_SITEMAP_FILE", 'sitemap_file');
define("PSNG_TXTSITEMAP_FILE", 'txtsitemap_file');

define("PSNG_LASTMOD", 'lastmod');
define("PSNG_LASTMOD_DISSABLED", 'lastmod_dissabled');
define("PSNG_LASTMOD_FILEDATE", 'lastmod_filedate');
define("PSNG_LASTMOD_CURRENT", 'lastmod_current');
define("PSNG_LASTMOD_TIMEFORMAT", 'lastmod_format');
define("PSNG_TIMEFORMAT_SHORT", 'short');
define("PSNG_TIMEFORMAT_LONG", 'long');

define("PSNG_PRIORITY", 'priority');
define("PSNG_PRIORITY_DISSABLED", 'priority_dissabled');
define("PSNG_PRIORITY_AUTOMATIC", 'priority_automatic');
define("PSNG_PRIORITY_FIXED", 'priority_fixed');

define("PSNG_CHANGEFREQ", 'changefreq');
define("PSNG_CHANGEFREQ_DISSABLED", 'changefreq_dissabled');
define("PSNG_CHANGEFREQ_DYNAMIC", 'changefreq_dynamic');
define("PSNG_CHANGEFREQ_FIXED", 'changefreq_fixed');
define("PSNG_FILE_ENABLED", 'file_enabled');
define("PSNG_FILE_URL", 'file_url');
define("PSNG_FILE_DIRNAME", 'file_url');

// action names
define("PSNG_ACTION", PSNG_SETTINGS_ACTION);
define("PSNG_ACTION_CHECK_UPDATESTATUS", 'check_updatestatus');
define("PSNG_ACTION_SETTINGS_GET", 'getSettings');
define("PSNG_ACTION_SETTINGS_RESET", 'resetSettings');
define("PSNG_ACTION_SETTINGS_SETUP", 'setup');
define("PSNG_ACTION_SETTINGS_PARSE", 'parse');
define("PSNG_ACTION_SETTINGS_WRITESITEMAP_USERINPUT", 'writeSitemapUserinput');
define("PSNG_ACTION_SETTINGS_PINGGOOGLE", 'pinggoogle');

// file names
define("PSNG_FILE_TEMPLATE_SETUP_EXPERT", 'templates/setup_expert.php');
define("PSNG_FILE_TEMPLATE_SETUP_SIMPLE", 'templates/setup_simple.php');
define("PSNG_FILE_TEMPLATE_EDIT_FILES", 'templates/edit_files.php');
define("PSNG_FILE_INC_SHUTDOWN", 'inc/shutdown.php');

// output stuff
define("PSNG_HTML_HISTORY", 'history');
define("PSNG_HTML_STATUS", 'http_status');
define("PSNG_HTML_STATUS_HISTORY", 'class="history"');

define("PSNG_HTML_SOURCE", 'file_source');
define("PSNG_HTML_SOURCE_FS", 'class="source_fs"');
define("PSNG_HTML_SOURCE_WEBSITE", 'class="source_website"');
define("PSNG_HTML_SOURCE_FS_WEBSITE", 'class="source_fs_website"');

// maybe deprecated
define("PSNG_FILE_SETTINGS", 'settings_file');
define("PSNG_FILE_FILES", 'files_file');

define("PSNG_URL_UPDATESTATUS", 'http://enarion.net/google/phpsitemapng/status.php?version='.PSNG_VERSION);
define("PSNG_URL_DOWNLOAD", 'http://enarion.net/google/phpsitemapng/download/');
?>
