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

/**
 * Adapter to use a APC Instance for caching
 *
 * @category   WurflCache
 *
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */
class Apc extends AbstractAdapter implements AdapterInterface
{
    /**
     * the apc PHP module is required
     */
    const EXTENSION_MODULE_NAME = 'apc';

    /**
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->ensureModuleExistence();

        parent:: __construct($params);
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

        $storedValue = apc_fetch($cacheId, $success);
        if (false === $success) {
            $success = false;

            return null;
        }

        $value = $this->extract($storedValue);

        if ($value === null) {
            $success = false;

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
        $cacheId = $this->normalizeKey($cacheId);

        return apc_exists($cacheId);
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

        return apc_store(
            $cacheId,
            $this->compact($value),
            $this->cacheExpiration
        );
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

        return apc_delete($cacheId);
    }

    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush()
    {
        return apc_clear_cache('user');
    }

    /**
     * Ensures the existence of the the PHP Extension apc
     *
     * @throws \WurflCache\Adapter\Exception required extension is unavailable
     */
    private function ensureModuleExistence()
    {
        if (!(extension_loaded(self::EXTENSION_MODULE_NAME) && ini_get('apc.enabled'))) {
            throw new Exception('The PHP extension apc must be installed, loaded and enabled.');
        }
    }
}
