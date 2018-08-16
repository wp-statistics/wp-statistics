<?php

namespace WurflCacheTest\Adapter;

use WurflCache\Adapter\DoctrineCacheConnector;

/**
 * Interface class to use the coctrine cache with Browscap
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
class DoctrineCacheConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get an item.
     */
    public function testGetItemNull()
    {
        $mock = $this->getMock(
            '\\Doctrine\\Common\\Cache\\FilesystemCache',
            array('fetch', 'contains'),
            array(),
            '',
            false
        );
        $mock
            ->expects(self::once())
            ->method('fetch')
            ->will(self::returnValue(null));
        $mock
            ->expects(self::once())
            ->method('contains')
            ->will(self::returnValue(true));

        $object = new DoctrineCacheConnector($mock);
        self::assertFalse($object->getItem('test'));
    }

    /**
     * Get an item.
     */
    public function testGetItemNotFound()
    {
        $mock = $this->getMock(
            '\\Doctrine\\Common\\Cache\\FilesystemCache',
            array('fetch', 'contains'),
            array(),
            '',
            false
        );
        $mock
            ->expects(self::any())
            ->method('fetch')
            ->will(self::returnValue(null));
        $mock
            ->expects(self::once())
            ->method('contains')
            ->will(self::returnValue(false));

        $object = new DoctrineCacheConnector($mock);
        self::assertNull($object->getItem('test'));
    }

    /**
     * Get an item.
     */
    public function testGetItemArray()
    {
        $mock = $this->getMock(
            '\\Doctrine\\Common\\Cache\\FilesystemCache',
            array('fetch', 'contains'),
            array(),
            '',
            false
        );
        $mock
            ->expects(self::once())
            ->method('fetch')
            ->will(self::returnValue('a:2:{i:0;s:4:"name";i:1;s:5:"value";}'));
        $mock
            ->expects(self::once())
            ->method('contains')
            ->will(self::returnValue(true));

        $object = new DoctrineCacheConnector($mock);
        self::assertSame(array('name', 'value'), $object->getItem('test'));
    }

    /**
     * Test if an item exists.
     */
    public function testHasItem()
    {
        $mock = $this->getMock('\\Doctrine\\Common\\Cache\\FilesystemCache', array('contains'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('contains')
            ->will(self::returnValue(false));
        $object = new DoctrineCacheConnector($mock);
        self::assertFalse($object->hasItem('test'));
    }

    /**
     * Store an item.
     */
    public function testSetItem()
    {
        $mock = $this->getMock('\\Doctrine\\Common\\Cache\\FilesystemCache', array('save'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('save')
            ->will(self::returnValue(true));
        $object = new DoctrineCacheConnector($mock);
        self::assertTrue($object->setItem('test', 'testValue'));
    }

    /**
     * Store an item.
     */
    public function testRemoveItem()
    {
        $mock = $this->getMock('\\Doctrine\\Common\\Cache\\FilesystemCache', array('delete'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('delete')
            ->will(self::returnValue(true));
        $object = new DoctrineCacheConnector($mock);
        self::assertTrue($object->removeItem('test'));
    }

    /**
     * Flush the whole storage
     */
    public function testflush()
    {
        $mock = $this->getMock('\\Doctrine\\Common\\Cache\\FilesystemCache', array('flushAll'), array(), '', false);
        $mock
            ->expects(self::once())
            ->method('flushAll')
            ->will(self::returnValue(false));
        $object = new DoctrineCacheConnector($mock);
        self::assertFalse($object->flush());
    }
}
