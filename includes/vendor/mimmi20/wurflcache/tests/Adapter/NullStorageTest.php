<?php

namespace WurflCacheTest\Adapter;

use WurflCache\Adapter\NullStorage;

/**
 * a outsourced cache class
 *
 * PHP version 5
 *
 * Copyright (c) 2013 Thomas Müller
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
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  Copyright (c) 2013 Thomas Müller
 *
 * @version    1.0
 *
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/phpbrowscap/
 */
class NullStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \WurflCache\Adapter\NullStorage
     */
    private $object = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new NullStorage();
    }

    /**
     * Get an item.
     */
    public function testGetItemNull()
    {
        self::assertNull($this->object->getItem('test'));
    }

    /**
     * Get an item.
     */
    public function testGetItemMocked()
    {
        /** @var $object \WurflCache\Adapter\NullStorage */
        $object = $this->getMock('\\WurflCache\\Adapter\\NullStorage', array('normalizeKey'));

        self::assertNull($object->getItem('test'));
    }

    /**
     * Test if an item exists.
     */
    public function testHasItem()
    {
        self::assertFalse($this->object->hasItem('test'));
    }

    /**
     * Store an item.
     */
    public function testSetItem()
    {
        self::assertTrue($this->object->setItem('test', 'testValue'));
    }

    /**
     * Store an item.
     */
    public function testRemoveItem()
    {
        self::assertTrue($this->object->removeItem('test'));
    }

    /**
     * Flush the whole storage
     */
    public function testflush()
    {
        self::assertTrue($this->object->flush());
    }
}
