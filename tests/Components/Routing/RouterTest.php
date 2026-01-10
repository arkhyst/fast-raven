<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;
use FastRaven\Types\EndpointType;

class RouterTest extends TestCase
{
    public function testNewCreatesRouterWithType(): void
    {
        $router = Router::new(EndpointType::VIEW);

        $this->assertEquals(EndpointType::VIEW, $router->getType());
        $this->assertEmpty($router->getEndpointList());
        $this->assertEmpty($router->getSubrouterList());
    }

    public function testAddEndpointToRouter(): void
    {
        $endpoint = Endpoint::view(false, '/', 'home.php');
        
        $router = Router::new(EndpointType::VIEW)
            ->add($endpoint);

        $this->assertCount(1, $router->getEndpointList());
        $this->assertArrayHasKey($endpoint->getComplexPath(), $router->getEndpointList());
    }

    public function testAddMultipleEndpoints(): void
    {
        $endpoint1 = Endpoint::view(false, '/home', 'home.php');
        $endpoint2 = Endpoint::view(false, '/about', 'about.php');

        $router = Router::new(EndpointType::VIEW)
            ->add($endpoint1)
            ->add($endpoint2);

        $this->assertCount(2, $router->getEndpointList());
        $this->assertArrayHasKey($endpoint1->getComplexPath(), $router->getEndpointList());
        $this->assertArrayHasKey($endpoint2->getComplexPath(), $router->getEndpointList());
    }

    public function testAddSubrouterToRouter(): void
    {
        $subrouter = Endpoint::router(EndpointType::API, false, '/admin', 'admin.php');
        
        $router = Router::new(EndpointType::API)
            ->add($subrouter);

        $this->assertEmpty($router->getEndpointList());
        $this->assertCount(1, $router->getSubrouterList());
    }

    public function testApiRouterType(): void
    {
        $router = Router::new(EndpointType::API);

        $this->assertEquals(EndpointType::API, $router->getType());
    }

    public function testCdnRouterType(): void
    {
        $router = Router::new(EndpointType::CDN);

        $this->assertEquals(EndpointType::CDN, $router->getType());
    }

    public function testEndpointLookupByComplexPath(): void
    {
        $endpoint = Endpoint::api(false, 'GET', '/health', 'Health.php');
        
        $router = Router::new(EndpointType::API)
            ->add($endpoint);

        $complexPath = $endpoint->getComplexPath();
        $found = $router->getEndpointList()[$complexPath] ?? null;

        $this->assertSame($endpoint, $found);
    }
}
