<?php

namespace WurflCacheTest\Adapter;

use WurflCache\CloudCache;

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
class CloudCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \WurflCache\CloudCache
     */
    private $object = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new CloudCache();
    }

    public function callbackGetOk($deviceId, &$success)
    {
        $success = true;

        return array('a' => 12, 'b' => 42);
    }

    /**
     * Get an item.
     */
    public function testSetGetAdapter()
    {
        self::assertInstanceOf('\WurflCache\Adapter\Memory', $this->object->getAdapter());

        $adapter = $this->getMock('\WurflCache\Adapter\Memory', array(), array(), '', false);

        self::assertSame($this->object, $this->object->setAdapter($adapter));
        self::assertSame($adapter, $this->object->getAdapter());

        self::assertSame($this->object, $this->object->setCacheExpiration(0));
        self::assertSame($this->object, $this->object->setCachePrefix('test'));
    }

    /**
     * Get an item.
     */
    public function testGetDeviceNull()
    {
        self::assertNull($this->object->getDevice('test'));
    }

    /**
     * Get an item.
     */
    public function testGetDeviceFromIDNull()
    {
        self::assertNull($this->object->getDeviceFromID('test'));
    }

    /**
     * Store an item.
     */
    public function testSetDeviceFromID()
    {
        $adapter = $this->getMock('\WurflCache\Adapter\Memory', array(), array(), '', false);
        $adapter
            ->expects(self::once())
            ->method('setItem')
            ->will(self::returnValue(true));
        $this->object->setAdapter($adapter);
        self::assertTrue($this->object->setDeviceFromID('test', array('testValue')));
    }

    /**
     * Store an item.
     */
    public function testSetDevice()
    {
        $adapter = $this->getMock('\WurflCache\Adapter\Memory', array(), array(), '', false);
        $adapter
            ->expects(self::once())
            ->method('setItem')
            ->will(self::returnValue(true));
        $this->object->setAdapter($adapter);
        self::assertTrue($this->object->setDevice('test', array('testValue')));
    }

    /**
     * Get an item.
     */
    public function testGetDeviceFromID()
    {
        $capabilities = array('a' => 12, 'b' => 42);

        $adapter = $this->getMock('\WurflCache\Adapter\Memory', array(), array(), '', false);
        $adapter
            ->expects(self::once())
            ->method('getItem')
            ->will(self::returnCallback(array($this, 'callbackGetOk')));
        $this->object->setAdapter($adapter);
        self::assertSame($capabilities, $this->object->getDeviceFromID('test'));
    }

    /**
     * Get an item.
     */
    public function testGetDevice()
    {
        $capabilities = array('a' => 12, 'b' => 42);

        $adapter = $this->getMock('\WurflCache\Adapter\Memory', array(), array(), '', false);
        $adapter
            ->expects(self::once())
            ->method('getItem')
            ->will(self::returnCallback(array($this, 'callbackGetOk')));
        $this->object->setAdapter($adapter);
        self::assertSame($capabilities, $this->object->getDevice('test'));
    }
}
