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

    public function testNewCreatesRouterWithRateLimit(): void
    {
        $router = Router::new(EndpointType::API, 200);

        $this->assertEquals(EndpointType::API, $router->getType());
        $this->assertEquals(200, $router->getLimitPerMinute());
    }

    public function testNewCreatesRouterWithDefaultRateLimit(): void
    {
        $router = Router::new(EndpointType::VIEW);

        $this->assertEquals(-1, $router->getLimitPerMinute());
    }

    public function testAddEndpointToRouter(): void
    {
        $endpoint = Endpoint::view(false, '/', 'Home.php');
        
        $router = Router::new(EndpointType::VIEW)
            ->add($endpoint);

        $this->assertCount(1, $router->getEndpointList());
        $this->assertArrayHasKey($endpoint->getComplexPath(), $router->getEndpointList());
    }

    public function testAddMultipleEndpoints(): void
    {
        $endpoint1 = Endpoint::view(false, '/home', 'Home.php');
        $endpoint2 = Endpoint::view(false, '/about', 'About.php');

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

    public function testApiRouterWithRateLimit(): void
    {
        $router = Router::new(EndpointType::API, 100);

        $this->assertEquals(100, $router->getLimitPerMinute());
    }

    public function testCdnRouterWithRateLimit(): void
    {
        $router = Router::new(EndpointType::CDN, 50);

        $this->assertEquals(50, $router->getLimitPerMinute());
    }

    public function testViewRouterWithRateLimit(): void
    {
        $router = Router::new(EndpointType::VIEW, 300);

        $this->assertEquals(300, $router->getLimitPerMinute());
    }

    public function testMethodChaining(): void
    {
        $endpoint1 = Endpoint::api(false, 'GET', '/health', 'Health.php');
        $endpoint2 = Endpoint::api(false, 'GET', '/ping', 'Ping.php');
        $subrouter = Endpoint::router(EndpointType::API, true, '/admin', 'admin.php');

        $router = Router::new(EndpointType::API, 200)
            ->add($endpoint1)
            ->add($endpoint2)
            ->add($subrouter);

        $this->assertCount(2, $router->getEndpointList());
        $this->assertCount(1, $router->getSubrouterList());
        $this->assertEquals(200, $router->getLimitPerMinute());
    }
}
