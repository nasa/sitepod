<?php

if (!isset($SETTINGS[PSNG_COMPRESS_SITEMAP])) $SETTINGS[PSNG_COMPRESS_SITEMAP] = '';

	$layout = '
	<script type="text/javascript">
	<!--
	function checkSetupCompress() {
   	   var sitemap_url = document.setupExpert.'.PSNG_SITEMAP_URL.'.value;
	   if(document.setupExpert.'.PSNG_COMPRESS_SITEMAP.'.checked) {
	   	  if (sitemap_url.substring(sitemap_url.length - 3) != ".gz") {
	   	  	  document.setupExpert.'.PSNG_SITEMAP_URL.'.value = sitemap_url + ".gz";
	   	  }
		} else {
	   	  if (sitemap_url.substring(sitemap_url.length - 3) == ".gz") {
	   	  	  var end = sitemap_url.length - 3;
	   	  	  document.setupExpert.'.PSNG_SITEMAP_URL.'.value = sitemap_url.substring(0,end);
	   	  }
		}
	}
	function checkTimeoutEdit() {
	   if(document.setupExpert.'.PSNG_TIMEOUT.'.checked) {
   	  	  document.setupExpert.'.PSNG_TIMEOUT_TIME_DURATION.'.disabled = false;
		} else {
   	  	  document.setupExpert.'.PSNG_TIMEOUT_TIME_DURATION.'.disabled = true;
		}
	}
	function checkCrawlerEdit() {
	   if(document.setupExpert.'.PSNG_SCAN_WEBSITE.'.checked) {
   	  	  document.setupExpert.'.PSNG_CRAWLER_URL.'.disabled = false;
		} else {
   	  	  document.setupExpert.'.PSNG_CRAWLER_URL.'.disabled = true;
		}
	}
	//-->
	</script>
	<div align="center">
	<form name="setupExpert" action="'.$SETTINGS[PSNG_SCRIPT].'" method="post">
	<input type="hidden" name="'.PSNG_ACTION.'" value="'.PSNG_ACTION_SETTINGS_GET.'">
	<fieldset style="padding: 10; width:600; border-color:#000099; border-width:2px; border-style:solid; ">
	<legend style="color:#000099;"><b>Expert settings</b></legend>
	<table border="0" cellpadding="5" cellspacing="0" width="495">
	  <tr>
	  	<td width="200" valign="top"><label for="ipage_root" accesskey="R">Page root</label></td>
		<td width="396">
			<input class="required" type="Text" name="'.PSNG_PAGEROOT.'" id="ipage_root" align="LEFT" size="50" value="'.((strpos($SETTINGS[PSNG_PAGEROOT],"\\") === FALSE)?$SETTINGS[PSNG_PAGEROOT]:stripslashes($SETTINGS[PSNG_PAGEROOT])).'"/>
			<br/><font size="-1">website root path on <b>local file system</b> of your webserver</font>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="iwebsite" accesskey="W">Website</label></td>
		<td>
			<input class="required" type="Text" name="'.PSNG_WEBSITE.'" id="iwebsite" align="LEFT" size="50" value="'.$SETTINGS[PSNG_WEBSITE].'"/>
			<br /><font size="-1">url of your website</font>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="iping" accesskey="W">Ping google</label></td>
		<td>
			<input type="checkbox" '. (($SETTINGS[PSNG_PINGGOOGLE] != '') ? 'checked':'') .' name="'.PSNG_PINGGOOGLE.'" id="iping" align="LEFT" size="50" value="TRUE"/>Ping after update<br />
			<font size="-1">always inform google when sitemap is created</font>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="iping" accesskey="W">Compress sitemap</label></td>
		<td>
			<input onClick="checkSetupCompress()"' . (function_exists('gzencode')?'':'disabled') . ' type="checkbox" '. (($SETTINGS[PSNG_COMPRESS_SITEMAP] != '') ? 'checked':'') .' name="'.PSNG_COMPRESS_SITEMAP.'" id="icompress" align="LEFT" size="50" value="TRUE"/>Compress sitemap '.
				(function_exists('gzencode')?'':'(not available within your php installation (need gzip functionality enabled)!)').'
				<br/><font size="-1">(with gzip; necessary if uncompressed sitemap is larger than 10 MB)</font>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="itimeout" accesskey="O" title="stop the execution after an amount of time">Timeout</label></td>
		<td>
			<input onClick="checkTimeoutEdit()" type="checkbox" '. (($SETTINGS[PSNG_TIMEOUT] == PSNG_TIMEOUT_NONE) ? '':'checked') .' name="'.PSNG_TIMEOUT.'" id="itimeout" align="LEFT" size="50" value="TRUE"/>
			Stop this script after this amout of time <br/><font size="-1">(will prevent timeouts in the execution, see <a href="http://enarion.net/google/phpsitemapng/usage/#timeout" target="_blank">documentation</a>)</font>
			<br/><input type="Text" '. (($SETTINGS[PSNG_TIMEOUT] == PSNG_TIMEOUT_NONE) ? 'disabled':'') .' name="'.PSNG_TIMEOUT_TIME_DURATION.'" id="itimeout_duration" align="LEFT" size="10" value="'.$SETTINGS[PSNG_TIMEOUT_TIME_DURATION].'"/> <font size="-1">execution time in seconds</font>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="iscan_local" accesskey="C">Scan</label></td>
		<td>
			where to search for files<br/>
			<p><input type="checkbox" '.(($SETTINGS[PSNG_SCAN_LOCAL] != '') ? 'checked':'') .' name="'.PSNG_SCAN_LOCAL.'" id="iscan_local" align="LEFT" size="50" value="true"/>Local file system - <font size="-1">will scan for files located on the hard disk of your webserver; will find all files that are not excluded!</font></p>
			<input type="checkbox" onClick="checkCrawlerEdit()" '.(($SETTINGS[PSNG_SCAN_WEBSITE] != '') ? 'checked':'') .' name="'.PSNG_SCAN_WEBSITE.'" id="iscan_website" align="LEFT" size="50" value="true"/>Scan website - <font size="-1">will scan the website with the build-in crawler engine; might take some time if there are many urls to crawl!
				Optional: crawl only this url: <input type="Text" name="'.PSNG_CRAWLER_URL.'" id="icrawlerurl" align="LEFT" size="50" value="'.$SETTINGS[PSNG_CRAWLER_URL].'"/><br/>Will only crawl this url and subdirectories!</font></p>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="iedit_result" accesskey="E">Display edit screen after scan</label></td>
		<td>
			<input type="checkbox" '.(($SETTINGS[PSNG_EDITRESULT] == PSNG_EDITRESULT_TRUE) ? 'checked':'') .' name="'.PSNG_EDITRESULT.'" id="iedit_result" align="LEFT" size="50" value="TRUE"/>Display edit screen<br />
			<font size="-1">setup values for each url (priority, lastmod, changefreq, enabled/disabled) - might take some time with more than 1000 urls!</font>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="istore_filelist" accesskey="B">Store filelist</label></td>
		<td>
			<input type="checkbox" '.(($SETTINGS[PSNG_STORE_FILELIST] != '') ? 'checked':'') .' name="'.PSNG_STORE_FILELIST.'" id="istore_filelist" align="LEFT" size="50" value="TRUE"/>Create and use filelist<br />
			<font size="-1">store values of created sitemap for next creation of the sitemap (values of priority, changefreq and enabled/disabled will be stored)</font>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="idisallow_dir" accesskey="D">Exclude directories</label></td>
		<td>
			<font size="-1">directories containing these substrings will will not be scanned for files and will not be added to site index; use line break to separate entries</font><br />
			<textarea name="'.PSNG_DISALLOW_DIR.'" cols="40" rows="10" id="idisallow_dir">'.arrToString($SETTINGS[PSNG_DISALLOW_DIR]).'</textarea>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="idisallow_file" accesskey="F">Exclude files</label></td>
		<td>
			<font size="-1">files containing these substrings will not be crawled for further links and not added to site index; use line break to separate entries</font><br/>
			<textarea name="'.PSNG_DISALLOW_FILE.'" cols="40" rows="10" id="idisallow_file">'.arrToString($SETTINGS[PSNG_DISALLOW_FILE]).'</textarea>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="idisallow_key" accesskey="K">Exclude url keys</label></td>
		<td>
			<font size="-1">remove keys in urls; you should add the session key here (default: PHPSESSID)</font>
			<textarea name="'.PSNG_DISALLOW_KEY.'" cols="40" rows="10" id="idisallow_key">'.arrToString($SETTINGS[PSNG_DISALLOW_KEY]).'</textarea>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="isitemap_url" accesskey="S">Sitemap file</label></td>
		<td>
			<input type="Text" name="'.PSNG_SITEMAP_FILE.'" id="isitemap_url" align="LEFT" size="50" value="'.$SETTINGS[PSNG_SITEMAP_FILE].'"/><br />
			<font size="-1">relativ to your page root; the generated sitemap will be stored to this file</font>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="itxtsitemap_url" accesskey="S">TXT Sitemap file</label><br /><font size="-1">Used by Yahoo, ...</font></td>
		<td>
			<input type="Text" name="'.PSNG_TXTSITEMAP_FILE.'" id="itxtsitemap_url" align="LEFT" size="50" value="'.$SETTINGS[PSNG_TXTSITEMAP_FILE].'"/><br />
			<font size="-1">relativ to your page root; the generated txt sitemap will be stored to this file</font>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="ilastmod" accesskey="L">Lastmod</label></td>
		<td>
		  <p>
			last modification date of file:<br />
			<input type="radio" '.(($SETTINGS[PSNG_LASTMOD] == PSNG_LASTMOD_DISSABLED) ? 'checked':'') .' name="'.PSNG_LASTMOD.'" value="'.PSNG_LASTMOD_DISSABLED.'">Disabled, do not write it<br/>
			<input type="radio" '.(($SETTINGS[PSNG_LASTMOD] == PSNG_LASTMOD_FILEDATE) ? 'checked':'') .' name="'.PSNG_LASTMOD.'" id="ilastmod" value="'.PSNG_LASTMOD_FILEDATE.'">Get time from files<br/>
			<input type="radio" '.(($SETTINGS[PSNG_LASTMOD] == PSNG_LASTMOD_CURRENT) ? 'checked':'') .' name="'.PSNG_LASTMOD.'" value="'.PSNG_LASTMOD_CURRENT.'">Use current time<br/>
		  </p><p>
			select timeformat to use:<br />
			<input type="radio" '.(($SETTINGS[PSNG_LASTMOD_TIMEFORMAT] == PSNG_TIMEFORMAT_LONG) ? 'checked':'') .' name="'.PSNG_LASTMOD_TIMEFORMAT.'" value="'.PSNG_TIMEFORMAT_LONG.'">Long format (with time)<br/>
			<input type="radio" '.(($SETTINGS[PSNG_LASTMOD_TIMEFORMAT] == PSNG_TIMEFORMAT_SHORT) ? 'checked':'') .' name="'.PSNG_LASTMOD_TIMEFORMAT.'" value="'.PSNG_TIMEFORMAT_SHORT.'">Short format (date only)
		  </p>
		</td>
	  </tr>

	  <tr>
	  	<td valign="top"><label for="ipriority" accesskey="P">Priority</label></td>
		<td>
			relative priority of an url related to whole website <font size="-1">(value from 0.0 to 1.0, default 0.5)</font><br />
			<input type="radio" '.(($SETTINGS[PSNG_PRIORITY] == PSNG_PRIORITY_DISSABLED) ? 'checked':'') .' name="'.PSNG_PRIORITY.'" value="'.PSNG_PRIORITY_DISSABLED.'">Disabled, do not write it<br/>
			<input type="radio" '.(($SETTINGS[PSNG_PRIORITY] == PSNG_PRIORITY_FIXED) ? 'checked':'') .' name="'.PSNG_PRIORITY.'" value="'.PSNG_PRIORITY_FIXED.'">Use this priority for every file
			<select name="'.PSNG_PRIORITY_FIXED.'" id="ipriority_fixed" size="1">
				<option>'.$SETTINGS[PSNG_PRIORITY_FIXED].'</option>
				<option>0.0</option>
				<option>0.1</option>
				<option>0.2</option>
				<option>0.3</option>
				<option>0.4</option>
				<option>0.5</option>
				<option>0.6</option>
				<option>0.7</option>
				<option>0.8</option>
				<option>0.9</option>
				<option>1.0</option>
			</select>
		</td>
	  </tr>
	  <tr>
	  	<td valign="top"><label for="ichangefreq" accesskey="C">Changefreq</label></td>
		<td>
			specify the update(change) frequency of the files <br/><font size="-1">(from never to always)</font><br />
			<input type="radio" '.(($SETTINGS[PSNG_CHANGEFREQ] == PSNG_CHANGEFREQ_DISSABLED) ? 'checked':'') .' name="'.PSNG_CHANGEFREQ.'" value="'.PSNG_CHANGEFREQ_DISSABLED.'">Disabled, do not write it<br/>
			<input type="radio" '.(($SETTINGS[PSNG_CHANGEFREQ] == PSNG_CHANGEFREQ_DYNAMIC) ? 'checked':'') .' name="'.PSNG_CHANGEFREQ.'" value="'.PSNG_CHANGEFREQ_DYNAMIC.'">Calculate change frequency from last modification date<br/>
			<input type="radio"  '.(($SETTINGS[PSNG_CHANGEFREQ] == PSNG_CHANGEFREQ_FIXED) ? 'checked':'') .' name="'.PSNG_CHANGEFREQ.'" value="'.PSNG_CHANGEFREQ_FIXED.'">Use this change frequency for every file(will not overwrite stored file information):
					<select name="'.PSNG_CHANGEFREQ_FIXED.'" id="ichangefreq_fixed" size="1">
						<option>'.$SETTINGS[PSNG_CHANGEFREQ_FIXED].'</option>
						<option>always</option>
						<option>hourly</option>
						<option>daily</option>
						<option>weekly</option>
						<option>monthly</option>
						<option>yearly</option>
						<option>never</option>
					</select><br/>
		</td>
	  </tr>
	  <tr>
	    <td>&nbsp;</td>
		<td><input type="Submit" value="Submit Settings" name="'.PSNG_ACTION_SETTINGS_GET.'">&nbsp;
			<input type="Submit" value="Reset to initial values" name="'.PSNG_ACTION_SETTINGS_RESET.'">&nbsp;
		</td>
	  </tr>
	</table>
	</fieldset>
	</form></div>';
?>