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
use WurflCache\Adapter\Helper\IdGenerator;

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
class IdGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \WurflCache\Adapter\Helper\IdGenerator
     */
    private $object = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new IdGenerator();
    }

    /**
     * Encode the Object Id using the Persistence Identifier
     */
    public function testEncodeWithoutParams()
    {
        self::assertSame(
            'da5819ed71635493d7d2fd46aafa3d913d44a03f1fb98ddddfe76ce60b2b253be8887d4c8e0686ee1551ebce31bdc552e35825447e39b862bac3e15b874115b1',
            $this->object->encode()
        );
    }

    /**
     * Encode the Object Id using the Persistence Identifier
     */
    public function testEncodeWithoutCacheId()
    {
        self::assertSame(
            '7b99a2477d05181d9572c7f2fb40513e127522f34be5efc4cf3a3c8e6782019d41b38963321452cb4b52736f6140f2abf02399da224cc504aca86274e3560cac',
            $this->object->encode('test')
        );
    }

    /**
     * Encode the Object Id using the Persistence Identifier
     */
    public function testEncode()
    {
        self::assertSame(
            '1fc1cef6f6fc08a4f9f1c56aff2bb23f16690432ca2ac89a574400afd3b529962f34d9c0467581c09b0c7a12e80ab21368bcb57884f93165a3b9154e55789767',
            $this->object->encode('test', 'testValue')
        );
    }
}
