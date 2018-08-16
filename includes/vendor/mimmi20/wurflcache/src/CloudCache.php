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

use WurflCache\Adapter\Memory;

/**
 * Class to use with the Wurfl Cloud
 *
 * @category   WurflCache
 *
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */
class CloudCache implements CacheInterface
{
    /**
     * @var Adapter\AdapterInterface
     */
    private $cache = null;

    /**
     * Gets a cache instance
     *
     * @return \WurflCache\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        if (null === $this->cache) {
            $this->cache = new Memory();
        }

        return $this->cache;
    }

    /**
     * Sets a cache instance
     *
     * @param \WurflCache\Adapter\AdapterInterface $adapter
     *
     * @return \WurflCache\CloudCache
     */
    public function setAdapter(Adapter\AdapterInterface $adapter)
    {
        $this->cache = $adapter;

        return $this;
    }

    /**
     * @param string $userAgent
     *
     * @return array|null
     */
    public function getDevice($userAgent)
    {
        $success = null;
        $data    = $this->getAdapter()->getItem($userAgent, $success);

        if (!$success) {
            return;
        }

        return $data;
    }

    /**
     * @param string $deviceId
     *
     * @return array|bool
     */
    public function getDeviceFromID($deviceId)
    {
        $success = null;
        $data    = $this->getAdapter()->getItem($deviceId, $success);

        if (!$success) {
            return;
        }

        return $data;
    }

    /**
     * @param string $userAgent
     * @param array  $capabilities
     *
     * @return bool
     */
    public function setDevice($userAgent, array $capabilities)
    {
        return $this->getAdapter()->setItem($userAgent, $capabilities);
    }

    // Required by interface but not used for this provider
    /**
     * @param string $deviceId
     * @param array  $capabilities
     *
     * @return bool
     */
    public function setDeviceFromID($deviceId, array $capabilities)
    {
        return $this->getAdapter()->setItem($deviceId, $capabilities);
    }

    /**
     * Sets the expiration time of the cached items
     *
     * @param int $time Expiration time in seconds
     *
     * @return \WurflCache\CloudCache
     */
    public function setCacheExpiration($time)
    {
        $this->getAdapter()->setExpiration($time);

        return $this;
    }

    /**
     * Sets the string that is prefixed to the keys stored in this cache provider (to prevent collisions)
     *
     * @param string $prefix
     *
     * @return \WurflCache\CloudCache
     */
    public function setCachePrefix($prefix)
    {
        $this->getAdapter()->setNamespace($prefix);

        return $this;
    }

    /**
     * Closes the connection to the cache provider
     *
     * @return \WurflCache\CloudCache
     */
    public function close()
    {
        return $this;
    }
}
