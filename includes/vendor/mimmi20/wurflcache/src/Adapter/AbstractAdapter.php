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
 * Base class for all Adapters
 *
 * @category   WurflCache
 *
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */
abstract class AbstractAdapter implements AdapterInterface
{
    const DEFAULT_NAMESPACE = 'wurfl';

    /**
     * the time until the cache expires
     *
     * @var int
     */
    protected $cacheExpiration = 0;

    /**
     * the namespace used to build the internal cache id
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * cache prefix used to build the internal cache id
     *
     * @var string
     */
    protected $cacheVersion = '';

    /**
     * @var array
     */
    protected $defaultParams = array(
        'namespace'       => self::DEFAULT_NAMESPACE,
        'cacheExpiration' => 0,
        'cacheVersion'    => WurflConstants::API_NAMESPACE,
    );

    /**
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $currentParams = $this->defaultParams;

        if (is_array($params) && !empty($params)) {
            $currentParams = array_merge($currentParams, $params);
        }

        $this->toFields($currentParams);
    }

    /**
     * Get an item.
     *
     * @param string $key
     * @param bool   $success
     *
     * @return mixed Data on success, null on failure
     */
    public function getItem($key, & $success = null)
    {
        $success = false;

        return;
    }

    /**
     * save the content into the zend cache
     *
     * @param string $cacheId The cache id
     * @param mixed  $content The content to store
     *
     * @return bool whether the content was stored
     */
    public function setItem($cacheId, $content)
    {
        return true;
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
        return false;
    }

    /**
     * Remove an item.
     *
     * @param string $key
     *
     * @return bool
     */
    public function removeItem($key)
    {
        return true;
    }

    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush()
    {
        return true;
    }

    /**
     * set the cacheExpiration time
     *
     * @param int $expiration
     *
     * @return AdapterInterface
     */
    public function setExpiration($expiration = 86400)
    {
        $this->cacheExpiration = (int) $expiration;

        return $this;
    }

    /**
     * set the cache namespace
     *
     * @param string $namespace
     *
     * @return AdapterInterface
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @param string $cacheVersion
     *
     * @return AdapterInterface
     */
    public function setCacheVersion($cacheVersion)
    {
        $this->cacheVersion = $cacheVersion;

        return $this;
    }

    /**
     * normalizes the cache id for the cache
     *
     * @param string $cacheId The cache id
     *
     * @return string The formated cache id
     */
    protected function normalizeKey($cacheId)
    {
        return Helper\IdGenerator::encode($this->cacheVersion, $this->namespace, $cacheId);
    }

    /**
     * compacts the content for the cache
     *
     * @param mixed $content
     *
     * @return string
     */
    protected function compact($content)
    {
        /** @var $object Helper\StorageObject */
        $object = new Helper\StorageObject($content, $this->cacheExpiration);

        return serialize($object);
    }

    /**
     * compacts the content for the cache
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function extract($value)
    {
        /** @var $object Helper\StorageObject */
        $object = unserialize($value);
        if ($value === $object) {
            return null;
        }

        if (!($object instanceof Helper\StorageObject)) {
            return null;
        }

        if ($object->isExpired()) {
            return null;
        }

        return $object->value();
    }

    /**
     * @param array $params
     */
    protected function toFields(array $params)
    {
        if (isset($params['namespace'])) {
            $this->setNamespace($params['namespace']);
        }

        if (isset($params['cacheExpiration'])) {
            $this->setExpiration($params['cacheExpiration']);
        }

        if (isset($params['cacheVersion'])) {
            $this->setCacheVersion($params['cacheVersion']);
        }
    }
}
