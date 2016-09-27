<?php

	debug($FILE, "templates/edit_files: will display following files");
	$layout = '
	<form action="'.$SETTINGS[PSNG_SCRIPT].'" method="post">
	<input type="submit" name="submit" value="Create file">
	<input type="hidden" name="'.PSNG_SETTINGS_ACTION.'" value="'.PSNG_ACTION_SETTINGS_WRITESITEMAP_USERINPUT.'">
	<table>
		<th>Number</th>
		<th>Filename</th>
		'.(($SETTINGS[PSNG_LASTMOD] != PSNG_LASTMOD_DISSABLED)?'<th>Last modification</th>':'').'
		'.(($SETTINGS[PSNG_CHANGEFREQ] != PSNG_CHANGEFREQ_DISSABLED)?'<th>Change frequency</th>':'').'
		'.(($SETTINGS[PSNG_PRIORITY] != PSNG_PRIORITY_DISSABLED)?'<th>Priority</th>':'').'

	';
	$numb = 0;
	$count = array();
	$count['numb'] = 5;
	$f = $FILE[array_pop(array_keys($FILE))];
	if ($f[PSNG_LASTMOD] == PSNG_LASTMOD_DISSABLED) $count[numb]--;
	if ($f[PSNG_CHANGEFREQ] == PSNG_CHANGEFREQ_DISSABLED) $count[numb]--;
	if ($f[PSNG_PRIORITY] == PSNG_PRIORITY_DISSABLED) $count[numb]--;
	$count[PSNG_HTML_SOURCE_FS] = 0;
	$count[PSNG_HTML_SOURCE_WEBSITE] = 0;
	$count[PSNG_HTML_SOURCE_FS_WEBSITE] = 0;
	$count[PSNG_HTML_HISTORY] = 0;

	foreach ($FILE as $filename => $fileinfo) {
		if ($fileinfo[PSNG_HTML_SOURCE] == PSNG_HTML_SOURCE_FS) $count[PSNG_HTML_SOURCE_FS]++;
		if ($fileinfo[PSNG_HTML_SOURCE] == PSNG_HTML_SOURCE_WEBSITE) $count[PSNG_HTML_SOURCE_WEBSITE]++;
		if ($fileinfo[PSNG_HTML_SOURCE] == PSNG_HTML_SOURCE_FS_WEBSITE) $count[PSNG_HTML_SOURCE_FS_WEBSITE]++;

		if (isset($fileinfo[PSNG_HTML_HISTORY])) {
			$count[PSNG_HTML_HISTORY]++;
		} else {
			$fileinfo[PSNG_HTML_HISTORY] = ''; // prevent PHP Notice error msg
		}

		$layout .= '<tr '. $fileinfo[PSNG_HTML_SOURCE] .  '>
			<td '.$fileinfo[PSNG_HTML_HISTORY] .'><input type="checkbox" '.( ($fileinfo[PSNG_FILE_ENABLED] != '') ? 'checked' : '' ).' name="FILE['.$numb.']['.PSNG_FILE_ENABLED.']" value="TRUE">'.$numb.'</td>
			<td class="filename"><input type="hidden" name="FILE['.$numb.']['.PSNG_FILE_URL.']" value="'.$fileinfo[PSNG_FILE_URL].'">'.$fileinfo[PSNG_FILE_DIRNAME].'</td>
		'.(($SETTINGS[PSNG_LASTMOD] != PSNG_LASTMOD_DISSABLED)?'
			<td class="lastmod"><input class="lastmod" type="text" name="FILE['.$numb.']['.PSNG_LASTMOD.']" value="'.$fileinfo[PSNG_LASTMOD].'" size="24"></td>
		':'').'
		'.(($SETTINGS[PSNG_CHANGEFREQ] != PSNG_CHANGEFREQ_DISSABLED)?'
			<td class="changefreq"><select name="FILE['.$numb.']['.PSNG_CHANGEFREQ.']" size="1">
				<option>'.$fileinfo[PSNG_CHANGEFREQ].'</option>
				<option>always</option>
				<option>hourly</option>
				<option>daily</option>
				<option>weekly</option>
				<option>monthly</option>
				<option>yearly</option>
				<option>never</option></select></td>':'').'
		'.(($SETTINGS[PSNG_PRIORITY] != PSNG_PRIORITY_DISSABLED)?'
			<td class="priority">
				<select name="FILE['.$numb.']['.PSNG_PRIORITY.']" size="1">
				<option>'.$fileinfo[PSNG_PRIORITY].'</option>
				<option>1.0</option>
				<option>0.9</option>
				<option>0.8</option>
				<option>0.7</option>
				<option>0.6</option>
				<option>0.5</option>
				<option>0.4</option>
				<option>0.3</option>
				<option>0.2</option>
				<option>0.1</option>
				<option>0.0</option>
				</select></td>':'').'
			</tr>'."\n";
			$numb++;
	}
	$layout .= '<tr><td colspan="'.$count['numb'].'">&nbsp;</td></tr>'."\n";
	if ($count[PSNG_HTML_HISTORY] != '') $layout .= '<tr class="'.PSNG_HTML_HISTORY.'"><td colspan="'.$count['numb'].'">A cell with this background means that this file is already stored in your local cached filelist</td></tr>'."\n";
	if ($count[PSNG_HTML_SOURCE_FS] != '') $layout .= '<tr '.PSNG_HTML_SOURCE_FS.'><td colspan="'.$count['numb'].'">A row with this background means that this file can found on your local filesystem</td></tr>'."\n";
	if ($count[PSNG_HTML_SOURCE_WEBSITE] != '') $layout .= '<tr '.PSNG_HTML_SOURCE_WEBSITE.'><td colspan="'.$count['numb'].'">A row with this background means that this file has been found with the crawler engine</td></tr>'."\n";
	if ($count[PSNG_HTML_SOURCE_FS_WEBSITE] != '') $layout .= '<tr '.PSNG_HTML_SOURCE_FS_WEBSITE.'><td colspan="'.$count['numb'].'">A row with this background means that this file is stored on filesystem and there are links to this file.</td></tr>'."\n";

	$layout .= '</table></form>'."\n";

?>