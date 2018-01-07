<?php

namespace WurflCacheTest\Adapter;

/*
 * This software is the Copyright of ScientiaMobile, Inc.
 *
 * Please refer to the LICENSE.txt file distributed with the software for licensing information.
 *
 * @package    ScientiaMobile\WurflCloud
 * @subpackage Cache
 */
use WurflCache\Adapter\Cookie;

/**
 * Cookie cache provider
 */
class CookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \WurflCache\Adapter\Cookie
     */
    private $object = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $_COOKIE      = array();
        $this->object = new Cookie();
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

        /** @var $object \WurflCache\Adapter\Cookie */
        $object = $this->getMock('\\WurflCache\\Adapter\\Cookie', array('normalizeKey'));

        $_COOKIE['test'] = 'testValue';

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
        $this->markTestSkipped('Test will result in an error because output already startet');
        self::assertTrue($this->object->setItem('test', 'testValue'));
    }

    /**
     * Store an item.
     */
    public function testRemoveItem()
    {
        $this->markTestSkipped('Test will result in an error because output already startet');
        self::assertTrue($this->object->removeItem('test'));
    }

    /**
     * Flush the whole storage
     */
    public function testflush()
    {
        self::assertFalse($this->object->flush());
    }
}
