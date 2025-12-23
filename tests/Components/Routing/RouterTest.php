<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;

class RouterTest extends TestCase
{
    public function testEndpointsCreatesRouterWithEndpointList(): void
    {
        $endpoints = [
            Endpoint::view(false, '/', 'home.php'),
            Endpoint::api(false, 'GET', '/users', 'users.php')
        ];

        $router = Router::endpoints($endpoints);

        $this->assertEquals($endpoints, $router->getEndpointList());
    }

    public function testEndpointsCreatesRouterWithEmptyEndpointList(): void
    {
        $router = Router::endpoints([]);

        $this->assertEquals([], $router->getEndpointList());
    }

    public function testGetEndpointListReturnsCorrectList(): void
    {
        $endpoint1 = Endpoint::view(false, '/home', 'home.php');
        $endpoint2 = Endpoint::view(false, '/about', 'about.php');
        $endpoints = [$endpoint1, $endpoint2];

        $router = Router::endpoints($endpoints);

        $this->assertCount(2, $router->getEndpointList());
        $this->assertSame($endpoint1, $router->getEndpointList()[0]);
        $this->assertSame($endpoint2, $router->getEndpointList()[1]);
    }

    public function testEndpointsRouterContainsMixedEndpoints(): void
    {
        $viewEndpoint = Endpoint::view(true, '/dashboard', 'dashboard.php');
        $apiEndpoint = Endpoint::api(true, 'POST', '/api/update', 'update.php');
        
        $router = Router::endpoints([$viewEndpoint, $apiEndpoint]);

        $this->assertCount(2, $router->getEndpointList());
        $this->assertSame($viewEndpoint, $router->getEndpointList()[0]);
        $this->assertSame($apiEndpoint, $router->getEndpointList()[1]);
    }
}
