<?php

class GeoIp
{
    private static $SERVER_URL = 'http://freegeoip.net/json/';

    public function getGeoData($ipAddress)
    {
        $json = file_get_contents(self::$SERVER_URL . $ipAddress);
        /** @TODO: Add some checks to be sure we got the right response. */
        return json_decode($json, true);
    }
}