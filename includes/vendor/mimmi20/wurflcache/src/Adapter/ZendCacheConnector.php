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

use Zend\Cache\Exception as ZendException;
use Zend\Cache\Storage\Adapter\AbstractAdapter as AbstractZendAdapter;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\Plugin\Serializer;

/**
 * Connector class to use the Zend Cache
 *
 * @category   WurflCache
 *
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */
class ZendCacheConnector extends AbstractAdapter
{
    /**
     * a Zend CacheAdapter Instance
     *
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    private $cache = null;

    /**
     * Constructor class, checks for the existence of (and loads) the cache and
     * if needed updated the definitions
     *
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $cache
     */
    public function __construct(AbstractZendAdapter $cache)
    {
        $this->cache = $cache;
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

        /** @var $cache \Zend\Cache\Storage\Adapter\AbstractAdapter */
        $cache    = $this->cache;
        $casToken = null;

        try {
            $content = $cache->getItem($cacheId, $success, $casToken);
        } catch (ZendException\ExceptionInterface $ex) {
            $success = false;

            return null;
        }

        if (!$cache->hasPlugin(new Serializer())) {
            $content = unserialize($content);
        }

        return $content;
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
        $cacheId = $this->normalizeKey($cacheId);

        /** @var $cache \Zend\Cache\Storage\Adapter\AbstractAdapter */
        $cache = $this->cache;

        if (!$cache->hasPlugin(new Serializer())) {
            $content = serialize($content);
        }

        try {
            return $cache->setItem($cacheId, $content);
        } catch (ZendException\ExceptionInterface $ex) {
            return false;
        }
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
        $cacheId = $this->normalizeKey($cacheId);

        try {
            return $this->cache->hasItem($cacheId);
        } catch (ZendException\ExceptionInterface $ex) {
            return false;
        }
    }

    /**
     * normalizes the cache id for zend cache
     *
     * @param string $cacheId The cache id
     *
     * @return string The formated cache id
     */
    protected function normalizeKey($cacheId)
    {
        $cacheId = parent::normalizeKey($cacheId);

        if (($pattern = $this->cache->getOptions()->getKeyPattern())
            && !preg_match($pattern, $cacheId)
        ) {
            $pattern = str_replace(array('^[', '*$'), array('[^', ''), $pattern);

            $cacheId = preg_replace($pattern, '_', $cacheId);
        }

        return $cacheId;
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

        try {
            return $this->cache->removeItem($cacheId);
        } catch (ZendException\ExceptionInterface $ex) {
            return false;
        }
    }

    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush()
    {
        if (!($this->cache instanceof FlushableInterface)) {
            return false;
        }

        try {
            return $this->cache->flush();
        } catch (ZendException\ExceptionInterface $ex) {
            return false;
        }
    }
}
