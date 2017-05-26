<?php
namespace Sitepod;

class GeoIp
{
    private static $SERVER_URL = 'http://freegeoip.net/json/';
    private static $GEO_DATA = [];

    /**
     * Returns the geo data for the given IP address.
     *
     * @param string $ipAddress The IP address to get geo data for.
     * @return array
     */
    public function getGeoData($ipAddress)
    {
        if(key_exists($ipAddress, self::$GEO_DATA)){
            return self::$GEO_DATA[$ipAddress];
        }
        $json = file_get_contents(self::$SERVER_URL . $ipAddress);
        self::$GEO_DATA[$ipAddress] = [];
        if ($json) {
            /** @TODO: Add some more checks to be sure we got the right response. */
            self::$GEO_DATA[$ipAddress] = json_decode($json, true);
        }
        return self::$GEO_DATA[$ipAddress];
    }
}