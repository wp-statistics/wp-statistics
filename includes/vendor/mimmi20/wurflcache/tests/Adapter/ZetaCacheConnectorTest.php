<?php

namespace WurflCacheTest\Adapter;

use WurflCache\Adapter\ZetaCacheConnector;

/**
 * Interface class to use the zeta cache with Browscap
 *
 * PHP version 5
 *
 * Copyright (c) 2006-2012 Jonathan Stoppani
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
class ZetaCacheConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get an item.
     */
    public function testGetItemNull()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('restore'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('restore')
            ->will(self::returnValue(null));

        $object = new ZetaCacheConnector($mock);
        self::assertFalse($object->getItem('test'));
    }

    /**
     * Get an item.
     */
    public function testGetItemArray()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('restore'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('restore')
            ->will(self::returnValue('a:2:{i:0;s:4:"name";i:1;s:5:"value";}'));

        $object = new ZetaCacheConnector($mock);
        self::assertSame(array('name', 'value'), $object->getItem('test'));
    }

    /**
     * Get an item.
     */
    public function testGetItemException()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('restore'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('restore')
            ->will(self::throwException(new \ezcCacheApcException('test')));

        $object = new ZetaCacheConnector($mock);
        self::assertNull($object->getItem('test'));
    }

    /**
     * Test if an item exists.
     */
    public function testHasItemException()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('countDataItems'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('countDataItems')
            ->will(self::throwException(new \ezcCacheApcException('test')));
        $object = new ZetaCacheConnector($mock);
        self::assertFalse($object->hasItem('test'));
    }

    /**
     * Test if an item exists.
     */
    public function testHasItem()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('countDataItems'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('countDataItems')
            ->will(self::returnValue(false));
        $object = new ZetaCacheConnector($mock);
        self::assertFalse($object->hasItem('test'));
    }

    /**
     * Store an item.
     */
    public function testSetItemException()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('store'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('store')
            ->will(self::throwException(new \ezcCacheApcException('test')));
        $object = new ZetaCacheConnector($mock);
        self::assertFalse($object->setItem('test', 'testValue'));
    }

    /**
     * Store an item.
     */
    public function testSetItem()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('store'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('store')
            ->will(self::returnValue(true));
        $object = new ZetaCacheConnector($mock);
        self::assertTrue($object->setItem('test', 'testValue'));
    }

    /**
     * Store an item.
     */
    public function testRemoveItemException()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('delete'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('delete')
            ->will(self::throwException(new \ezcCacheApcException('test')));
        $object = new ZetaCacheConnector($mock);
        self::assertFalse($object->removeItem('test'));
    }

    /**
     * Store an item.
     */
    public function testRemoveItem()
    {
        $mock = $this->getMock('\\ezcCacheStorageMemory', array('delete'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('delete')
            ->will(self::returnValue(true));
        $object = new ZetaCacheConnector($mock);
        self::assertTrue($object->removeItem('test'));
    }

    /**
     * Flush the whole storage
     */
    public function testflush()
    {
        $mock   = $this->getMock('\\ezcCacheStorageMemory', array('dropCache'), array(), '', false);
        $object = new ZetaCacheConnector($mock);
        self::assertFalse($object->flush());
    }

    /**
     * Flush the whole storage
     */
    public function testflushException()
    {
        $mock   = $this->getMock('\\ezcCacheStorageMemory', array('dropCache'), array(), '', false);
        $object = new ZetaCacheConnector($mock);
        self::assertFalse($object->flush());
    }
}
