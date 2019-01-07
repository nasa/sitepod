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

class GeoIp
{
    private static $SERVER_URL = 'http://ip-api.com/json/';
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