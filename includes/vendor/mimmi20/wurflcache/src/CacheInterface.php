<?php
/**
 * Copyright (c) 2013-2014 Thomas Müller
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   WurflCache
 *
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */

namespace WurflCache;

/**
 * Interface that all Cache providers must implement to be compatible with ScientiaMobile\WurflCloud\Client
 *
 * Base class for WurflCache Exceptions
 *
 * @category   WurflCache
 *
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */
interface CacheInterface
{
    /**
     * Get the device capabilities for the given user agent from the cache provider
     *
     * @param string $key User Agent
     *
     * @return array|bool Capabilities array or boolean false
     */
    public function getDevice($key);

    /**
     * Get the device capabilities for the given device ID from the cache provider
     *
     * @param string $key WURFL Device ID
     *
     * @return array|bool Capabilities array or boolean false
     */
    public function getDeviceFromID($key);

    /**
     * Stores the given user agent with the given device capabilities in the cache provider for the given time period
     *
     * @param string $key   User Agent
     * @param array  $value Capabilities
     *
     * @return bool Success
     */
    public function setDevice($key, array $value);

    /**
     * Stores the given user agent with the given device capabilities in the cache provider for the given time period
     *
     * @param string $key   WURFL Device ID
     * @param array  $value Capabilities
     *
     * @return bool Success
     */
    public function setDeviceFromID($key, array $value);

    /**
     * Closes the connection to the cache provider
     */
    public function close();

    /**
     * Sets the string that is prefixed to the keys stored in this cache provider (to prevent collisions)
     *
     * @param string $prefix
     */
    public function setCachePrefix($prefix);

    /**
     * Sets the expiration time of the cached items
     *
     * @param int $time Expiration time in seconds
     */
    public function setCacheExpiration($time);
}
