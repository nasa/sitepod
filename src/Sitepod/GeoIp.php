<?php
namespace Sitepod;

class GeoIp
{
    private static $SERVER_URL = 'http://freegeoip.net/json/';

    /**
     * Returns the geo data for the given IP address.
     *
     * @param string $ipAddress The IP address to get geo data for.
     * @return array
     */
    public function getGeoData($ipAddress)
    {
        $json = file_get_contents(self::$SERVER_URL . $ipAddress);
        if ($json) {
            /** @TODO: Add some more checks to be sure we got the right response. */
            return json_decode($json, true);
        }
        return [];
    }
}