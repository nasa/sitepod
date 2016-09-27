<?php
/*
	This is phpSitemapNG, a php script that creates your personal google sitemap
	It can be downloaded from http://enarion.net/google/
	License: GPL

	Tobias Kluge, enarion.net
*/

require_once(dirname(__FILE__).'/inc/startup.php');

// check settings
if ($SETTINGS['website'] == "") {
	// no settings yet, force user to setup this first
	$LAYOUT->addError('Please edit the settings <a href="index.php">here</a> and store them to the file system!','No valid settings found!');
} else { // settings exists, lets start = (German:) los gehts. :)
	$FILE = parseFilesystem();


	if (count($FILE)>0) {
		if (!writeSitemap($FILE)) {
			$LAYOUT->addError('','Could not create sitemap file, giving up!');
		} else {
			$LAYOUT->addSuccess('Sitemap has been created and written to filesystem!','Sitemap successful created');
			if($SETTINGS[PSNG_PINGGOOGLE]) {
				submitPageToGoogle();
				$LAYOUT->addSuccess('Sitemap has been submitted to Google!','Finished my job');
			} else {
				$LAYOUT->addInfo('Value for submit to google not set in settings','Sitemap not submitted to Google');
			}
		}
	} else {
		$LAYOUT->addError('Will not write sitemap to filesystem nor submit it to Google!','Result from plugins was empty!');
	}
}

require_once(PSNG_FILE_INC_SHUTDOWN);

?>
