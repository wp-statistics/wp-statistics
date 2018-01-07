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

use ezcBaseException;
use ezcCacheStorage;

/**
 * Connector class to use the zeta cache
 *
 * @category   WurflCache
 *
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */
class ZetaCacheConnector extends AbstractAdapter
{
    /**
     * a Zend Cache instance
     *
     * @var ezcCacheStorage
     */
    private $cache = null;

    /**
     * Constructor class, checks for the existence of (and loads) the cache and
     * if needed updated the definitions
     *
     * @param ezcCacheStorage $cache
     *
     * @throws Exception
     */
    public function __construct(ezcCacheStorage $cache)
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

        try {
            $success = true;

            return unserialize($this->cache->restore($cacheId, true));
        } catch (ezcBaseException $ex) {
            $success = false;

            return null;
        }
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

        try {
            return $this->cache->store($cacheId, serialize($content));
        } catch (ezcBaseException $ex) {
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
            return ($this->cache->countDataItems($cacheId) > 0);
        } catch (ezcBaseException $ex) {
            return false;
        }
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
            return ($this->cache->delete($cacheId));
        } catch (ezcBaseException $ex) {
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
        return false;
    }
}
