<?php
/**
 * @copyright Bluz PHP Team
 * @link      https://github.com/bluzphp/framework
 */

/**
 * @namespace
 */

namespace Bluz\Tests\Router;

use Bluz\Http\RequestMethod;
use Bluz\Proxy\Router;
use Bluz\Tests\FrameworkTestCase;

/**
 * RouterTest
 *
 * @package  Bluz\Tests\Router
 *
 * @author   Anton Shevchuk
 * @created  12.08.2014 14:24
 */
class RouterTest extends FrameworkTestCase
{
    /**
     * testRouterUrl
     *
     * @dataProvider providerForDefaultRoutes
     *
     * @param string $url
     * @param string $module
     * @param string $controller
     * @param array  $params
     */
    public function testRouterUrl($url, $module, $controller, $params = [])
    {
        self::assertEquals($url, Router::getUrl($module, $controller, $params));
    }

    /**
     * Test Router Url for custom controller route
     *
     * @dataProvider providerForCustomRoutes
     *
     * @param string $url
     * @param string $module
     * @param string $controller
     * @param array  $params
     */
    public function testRouterUrlWithCustomControllerRoute($url, $module, $controller, $params = [])
    {
        self::assertEquals($url, Router::getUrl($module, $controller, $params));
    }

    /**
     * Test Router Url
     */
    public function testRouterFullUrl()
    {
        self::setRequestParams('/', [], [], RequestMethod::GET);

        self::assertEquals('http://127.0.0.1/', Router::getFullUrl());
    }

    /**
     * @return array
     */
    public function providerForDefaultRoutes()
    {
        return [
            ['/test/test', 'test', 'test', []],
            ['/test/test/foo/bar', 'test', 'test', ['foo' => 'bar']],
            ['/test/test?foo%5B0%5D=bar&foo%5B1%5D=baz', 'test', 'test', ['foo' => ['bar', 'baz']]],
            ['/test', 'test', null, []],
            ['/test', 'test', 'index', []],
            ['/test/index/foo/bar', 'test', 'index', ['foo' => 'bar']],
            ['/index/test', null, 'test', []],
            ['/index/test', 'index', 'test', []],
            ['/index/test/foo/bar', 'index', 'test', ['foo' => 'bar']],
        ];
    }

    /**
     * @return array
     */
    public function providerForCustomRoutes()
    {
        return [
            ['/another-route.html', 'test', 'route-static'],
            ['/another-route.html?foo%5B0%5D=bar&foo%5B1%5D=baz', 'test', 'route-static', ['foo' => ['bar', 'baz']]],
            ['/test/param/42/', 'test', 'route-with-param', ['a' => 42]],
            ['/foo-bar-baz/', 'test', 'route-with-params', ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']],
            ['/test/route-with-other-params/about', 'test', 'route-with-other-params', ['alias' => 'about']],
        ];
    }
}
