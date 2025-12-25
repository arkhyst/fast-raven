<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;
use FastRaven\Components\Types\MiddlewareType;

class RouterTest extends TestCase
{
    public function testNewCreatesRouterWithType(): void
    {
        $router = Router::new(MiddlewareType::VIEW);

        $this->assertEquals(MiddlewareType::VIEW, $router->getType());
        $this->assertEmpty($router->getEndpointList());
        $this->assertEmpty($router->getSubrouterList());
    }

    public function testAddEndpointToRouter(): void
    {
        $endpoint = Endpoint::view(false, '/', 'home.php');
        
        $router = Router::new(MiddlewareType::VIEW)
            ->add($endpoint);

        $this->assertCount(1, $router->getEndpointList());
        $this->assertArrayHasKey($endpoint->getComplexPath(), $router->getEndpointList());
    }

    public function testAddMultipleEndpoints(): void
    {
        $endpoint1 = Endpoint::view(false, '/home', 'home.php');
        $endpoint2 = Endpoint::view(false, '/about', 'about.php');

        $router = Router::new(MiddlewareType::VIEW)
            ->add($endpoint1)
            ->add($endpoint2);

        $this->assertCount(2, $router->getEndpointList());
        $this->assertArrayHasKey($endpoint1->getComplexPath(), $router->getEndpointList());
        $this->assertArrayHasKey($endpoint2->getComplexPath(), $router->getEndpointList());
    }

    public function testAddSubrouterToRouter(): void
    {
        $subrouter = Endpoint::router(MiddlewareType::API, false, '/admin', 'admin.php');
        
        $router = Router::new(MiddlewareType::API)
            ->add($subrouter);

        $this->assertEmpty($router->getEndpointList());
        $this->assertCount(1, $router->getSubrouterList());
    }

    public function testApiRouterType(): void
    {
        $router = Router::new(MiddlewareType::API);

        $this->assertEquals(MiddlewareType::API, $router->getType());
    }

    public function testCdnRouterType(): void
    {
        $router = Router::new(MiddlewareType::CDN);

        $this->assertEquals(MiddlewareType::CDN, $router->getType());
    }

    public function testEndpointLookupByComplexPath(): void
    {
        $endpoint = Endpoint::api(false, 'GET', '/health', 'Health.php');
        
        $router = Router::new(MiddlewareType::API)
            ->add($endpoint);

        $complexPath = $endpoint->getComplexPath();
        $found = $router->getEndpointList()[$complexPath] ?? null;

        $this->assertSame($endpoint, $found);
    }
}
