<?php

namespace WurflCacheTest\Adapter;

use WurflCache\Adapter\Memcached;

/**
 * test case
 */

/**
 * test case.
 */
class MemcachedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \WurflCache\Adapter\Memcached
     */
    private $object = null;

    public function setUp()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped(
                'PHP extension \'Memcached\' must be loaded and a local Memcached server running to run this test.'
            );
        }

        $this->object = new Memcached();
    }

    public function testMultipleServerConfiguration()
    {
        $params = array(
            'host' => '127.0.0.1;127.0.0.2',
        );

        new Memcached($params);
    }

    /**
     * @covers \WurflCache\Adapter\Memcached::setItem
     * @covers \WurflCache\Adapter\Memcached::getItem
     */
    public function testNeverToExpireItems()
    {
        $this->object->setItem('foo', 'foo');
        sleep(2);
        self::assertEquals('foo', $this->object->getItem('foo'));
    }

    /**
     * @covers \WurflCache\Adapter\Memcached::setItem
     * @covers \WurflCache\Adapter\Memcached::getItem
     */
    public function testShouldRemoveTheExpiredItem()
    {
        $params  = array('cacheExpiration' => 1);
        $storage = new Memcached($params);
        $storage->setItem('key', 'value');
        sleep(2);
        self::assertEquals(null, $storage->getItem('key'));
    }

    /**
     * @covers \WurflCache\Adapter\Memcached::setItem
     * @covers \WurflCache\Adapter\Memcached::getItem
     * @covers \WurflCache\Adapter\Memcached::flush
     */
    public function testShouldClearAllItems()
    {
        $this->object->setItem('key1', 'item1');
        $this->object->setItem('key2', 'item2');
        $this->object->flush();
        $this->assertThanNoElementsAreInStorage(array('key1', 'key2'), $this->object);
    }

    /**
     * @param array                         $keys
     * @param \WurflCache\Adapter\Memcached $storage
     */
    private function assertThanNoElementsAreInStorage(array $keys = array(), Memcached $storage = null)
    {
        foreach ($keys as $key) {
            self::assertNull($storage->getItem($key));
        }
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
        $this->markTestSkipped(
            'actually this test will fail'
        );

        /* @var $object \Memcached */
        $mock = $this->getMock('\Memcached', array('get'));
        $mock
            ->expects(self::once())
            ->method('get')
            ->will(self::returnValue(null));

        $object = new Memcached(array(), $mock);

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
