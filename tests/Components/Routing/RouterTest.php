<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;
use FastRaven\Components\Types\MiddlewareType;

class RouterTest extends TestCase
{
    public function testViewsCreatesRouterWithEndpointList(): void
    {
        $endpoints = [
            Endpoint::view(false, '/', 'home.php'),
        ];

        $router = Router::views($endpoints);

        $this->assertEquals($endpoints, $router->getEndpointList());
        $this->assertEquals(MiddlewareType::VIEW, $router->getType());
    }

    public function testApiCreatesRouterWithEndpointList(): void
    {
        $router = Router::api([]);

        $this->assertEquals([], $router->getEndpointList());
        $this->assertEquals(MiddlewareType::API, $router->getType());
    }

    public function testCdnCreatesRouterWithEndpointList(): void
    {
        $endpoint = Endpoint::cdn(false, 'GET', '/favicon', 'Favicon.php');

        $router = Router::cdn([$endpoint]);

        $this->assertCount(1, $router->getEndpointList());
        $this->assertEquals(MiddlewareType::CDN, $router->getType());
    }

    public function testGetEndpointListReturnsCorrectList(): void
    {
        $endpoint1 = Endpoint::view(false, '/home', 'home.php');
        $endpoint2 = Endpoint::view(false, '/about', 'about.php');

        $router = Router::views([$endpoint1, $endpoint2]);

        $this->assertCount(2, $router->getEndpointList());
        $this->assertSame($endpoint1, $router->getEndpointList()[0]);
        $this->assertSame($endpoint2, $router->getEndpointList()[1]);
    }
}
