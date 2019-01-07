#!/usr/bin/php -q
<?php
/*
 * This file is part of Sitepod.
 *
 * Sitepod is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Sitepod is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sitepod. If not, see <http://www.gnu.org/licenses/>.
 */

// TODO: add verbose text, html, and quiet text flags
$text_only = 1;

ob_start ();

require_once (dirname ( __FILE__ ) . '/cron.php');

$results = ob_get_clean ();

if ($text_only) {
  $results = strip_tags ( $results );
}

echo $results;

?>
