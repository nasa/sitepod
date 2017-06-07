<?php
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
    function arrToStringReadable($array, $delim) {
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