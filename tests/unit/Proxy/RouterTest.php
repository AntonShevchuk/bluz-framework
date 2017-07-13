<?php
/**
 * @copyright Bluz PHP Team
 * @link      https://github.com/bluzphp/skeleton
 */

/**
 * @namespace
 */

namespace Bluz\Tests\Proxy;

use Bluz\Router\Router as Target;
use Bluz\Proxy\Router as Proxy;
use Bluz\Tests\TestCase;

/**
 * Proxy Test
 *
 * @package  Bluz\Tests\Proxy
 * @author   Anton Shevchuk
 */
class RouterTest extends TestCase
{

    /**
     * Test instance
     */
    public function testProxyInstance()
    {
        self::assertInstanceOf(Target::class, Proxy::getInstance());
    }
}