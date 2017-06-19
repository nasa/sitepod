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

class Util {

    /* remove maybe existing ' in string */
    static function stringToVariableName($str) {
        return ((!get_magic_quotes_gpc()) ? addslashes($str) : $str);
    }

    static function variableNameToString($var_name) {
        if(get_magic_quotes_gpc()) {
            return stripslashes($var_name);
        } else {
            $res = $var_name;
            if (substr($res, 0, 1) == "'") {
                $res = substr($res, 1);
            }
            if (substr($res, strlen($res) - 1) == "'") {
                $res = substr($res, 0, strlen($res) - 1);
            }
            return $res;
        }
    }

    /* explodes the array for given deliminator and returns a correct array */
    static function toArray($str, $delim = "\n") {
        $res = explode($delim, $str);

        for ($i = 0; $i < count($res); $i ++) {
            $res[$i] = trim($res[$i]);
        }

        return $res;
    }

    /* returns a string of all entries of array with delim */
    static function arrToStringReadable($array, $delim) {
        $res = "";
        if (is_array($array)) {
            $i = 0;
            foreach ($array as $key => $val) {
                if (is_array($val)) {
                    $res .= "$key: ".self::arrToStringReadable($val, $delim);
                } else {
                    $res .= "$key: $val";
                }
                if ($i < (count($array) - 1)) {
                    $res .= $delim;
                }
                $i ++;
            }
        }
        return $res;
    }

    /* returns a string of all entries of array with delim */
    static function arrToString($array, $delim = "\n") {
        $res = "";
        if (is_array($array)) {
            for ($i = 0; $i < count($array); $i ++) {
                $res .= $array[$i];
                if ($i < (count($array) - 1)) {
                    $res .= $delim;
                }
            }
        }
        return $res;
    }
}