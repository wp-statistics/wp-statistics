<?php

namespace WurflCacheTest\Adapter\Helper;

/*
 * Copyright (c) 2012 ScientiaMobile, Inc.
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package    \Wurfl\Storage
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
use WurflCache\Adapter\Helper\StorageObject;

/**
 * Base Storage Provider
 * A Skeleton implementation of the Storage Interface
 *
 * @category   WURFL
 *
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 *
 * @version    $id$
 */
class StorageObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testValue()
    {
        $value  = 'testValue';
        $object = new StorageObject($value, 10);

        self::assertSame($value, $object->value());
    }

    public function testIsExpired()
    {
        $object = new StorageObject('testValue', 0);

        self::assertFalse($object->isExpired());

        $object = new StorageObject('testValue', 1000);

        self::assertFalse($object->isExpired());

        $object = new StorageObject('testValue', 1);

        sleep(10);

        self::assertTrue($object->isExpired());
    }

    public function testExpiringOn()
    {
        $expiringTime = time() + 1000;
        $object       = new StorageObject('testValue', 1000);

        self::assertSame($expiringTime, $object->expiringOn());
    }
}
