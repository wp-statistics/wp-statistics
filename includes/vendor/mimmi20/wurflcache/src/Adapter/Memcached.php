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

namespace WurflCache\Adapter;

use Wurfl\WurflConstants;

/**
 * Adapter to use a Memcache Server for caching
 *
 * @category   WurflCache
 *
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */
class Memcached extends AbstractAdapter
{
    /**
     *
     */
    const EXTENSION_MODULE_NAME = 'memcached';
    /**
     *
     */
    const DEFAULT_PORT = 11211;

    /**
     * @var \Memcached
     */
    private $memcached;
    /**
     * @var
     */
    private $host;
    /**
     * @var
     */
    private $port;

    /**
     * @var array
     */
    protected $defaultParams = array(
        'host'             => '127.0.0.1',
        'port'             => self::DEFAULT_PORT,
        'namespace'        => 'wurfl',
        'cacheExpiration'  => 0,
        'cacheVersion'     => WurflConstants::API_NAMESPACE,
    );

    /**
     * @param array      $params
     * @param \Memcached $memCache
     *
     * @throws \WurflCache\Adapter\Exception
     */
    public function __construct(array $params = array(), \Memcached $memCache = null)
    {
        $this->ensureModuleExistence();

        parent:: __construct($params);

        if (null === $memCache) {
            $this->initializeMemCached();
        } else {
            $this->memcached = $memCache;
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->memcached = null;
    }

    /**
     * Get an item.
     *
     * @param string $cacheId
     * @param bool   $success
     *
     * @return mixed Data on success, null on failure
     */
    public function getItem($cacheId, & $success = null)
    {
        $cacheId = $this->normalizeKey($cacheId);
        $success = false;

        $storedValue = $this->memcached->get($cacheId);
        if (\Memcached::RES_SUCCESS !== $this->memcached->getResultCode()) {
            return null;
        }

        $value = $this->extract($storedValue);
        if ($value === null) {
            return null;
        }

        $success = true;

        return $value;
    }

    /**
     * Test if an item exists.
     *
     * @param string $cacheId
     *
     * @return bool
     */
    public function hasItem($cacheId)
    {
        $this->memcached->get($this->normalizeKey($cacheId));

        return (\Memcached::RES_SUCCESS === $this->memcached->getResultCode());
    }

    /**
     * Store an item.
     *
     * @param string $cacheId
     * @param mixed  $value
     *
     * @return bool
     */
    public function setItem($cacheId, $value)
    {
        $cacheId = $this->normalizeKey($cacheId);
        $expire  = $this->cacheExpiration;

        $this->memcached->set(
            $cacheId,
            $this->compact($value),
            ($expire === 0) ? $expire : time() + $expire
        );

        return (\Memcached::RES_SUCCESS === $this->memcached->getResultCode());
    }

    /**
     * Remove an item.
     *
     * @param string $cacheId
     *
     * @return bool
     */
    public function removeItem($cacheId)
    {
        $cacheId = $this->normalizeKey($cacheId);

        $this->memcached->delete($cacheId);

        return (\Memcached::RES_SUCCESS === $this->memcached->getResultCode());
    }

    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush()
    {
        $this->memcached->flush();

        return (\Memcached::RES_SUCCESS === $this->memcached->getResultCode());
    }

    /**
     * Initializes the Memcache Module
     */
    private function initializeMemCached()
    {
        $this->memcached = new \Memcached();

        // support multiple hosts using semicolon to separate hosts
        $hosts = explode(';', $this->host);

        // different ports for each hosts the same way
        $ports = explode(';', $this->port);

        if (count($ports) < 1) {
            $ports = array_fill(0, count($hosts), self::DEFAULT_PORT);
        } elseif (count($ports) === 1) {
            // if we have just one port, use it for all hosts
            $usedPort = $ports[0];
            $ports    = array_fill(0, count($hosts), $usedPort);
        }

        foreach ($hosts as $i => $host) {
            if (!isset($ports[$i])) {
                /*
                 * if we have a difference between the count of hosts and
                 * the count of ports, use the default port to fill the gap
                 */
                $ports[$i] = self::DEFAULT_PORT;
            }

            $this->memcached->addServer($host, $ports[$i]);
        }
    }

    /**
     * Ensures the existence of the the PHP Extension memcache
     *
     * @throws Exception required extension is unavailable
     */
    private function ensureModuleExistence()
    {
        if (!extension_loaded(self::EXTENSION_MODULE_NAME)) {
            throw new Exception(
                'The PHP extension memcached must be installed and loaded in order to use the Memcached.'
            );
        }
    }

    /**
     * @param array $params
     */
    protected function toFields(array $params)
    {
        parent::toFields($params);

        if (isset($params['host'])) {
            $this->host = $params['host'];
        }

        if (isset($params['port'])) {
            $this->port = $params['port'];
        }
    }
}
