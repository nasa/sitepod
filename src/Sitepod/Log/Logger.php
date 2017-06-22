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

namespace Sitepod\Log;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{

    /**
     * @var Logger $logger
     */
    private static $logger;

    /**
     * @var LogMessage[]
     */
    private $loggedMessages = [];

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $context['filePath'] = $backtrace[0]['file'];
        $context['lineNumber'] = (int)$backtrace[0]['line'];

        $this->loggedMessages[] = new LogMessage($level, $message, $context);
    }

    /**
     * Returns a Logger instance
     *
     * @return Logger
     */
    public static function instance()
    {
        if (self::$logger == null) {
            self::$logger = new Logger();
        }
        return self::$logger;
    }

    /**
     * Returns logged messages
     *
     * @return LogMessage[]
     */
    public function getLoggedMessages()
    {
        return $this->loggedMessages;
    }

}