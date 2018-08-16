<?php

namespace WurflCacheTest\Adapter;

/*
 * test case
 */
use WurflCache\Adapter\Apc;

/**
 * test case.
 */
class ApcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \WurflCache\Adapter\Apc
     */
    private $object = null;

    public function setUp()
    {
        if (!extension_loaded('apc') || ini_get('apc.enabled') !== true) {
            self::markTestSkipped('PHP must have APC support.');
        }

        $this->object = new Apc();
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
        /** @var $object \WurflCache\Adapter\Memory */
        $object = $this->getMock('\\WurflCache\\Adapter\\Memory', array('normalizeKey'));

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
    public function testSetGetItem()
    {
        $cacheId    = 'test';
        $cacheValue = 'testValue';

        self::assertTrue($this->object->setItem($cacheId, $cacheValue));

        $success = null;
        self::assertSame($cacheValue, $this->object->getItem($cacheId, $success));
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

    /**
     * Store an item.
     */
    public function testSetNamespace()
    {
        self::assertSame($this->object, $this->object->setNamespace('test'));
    }

    /**
     * Store an item.
     */
    public function testSetExpiration()
    {
        self::assertSame($this->object, $this->object->setExpiration('test'));
    }
}
