<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;
use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;

class RouterTest extends TestCase
{
    public function testFilesCreatesRouterWithFileList(): void
    {
        $fileList = Collection::new([
            Item::new('/v1', 'v1/routes.php'),
            Item::new('/v2', 'v2/routes.php')
        ]);

        $router = Router::files($fileList);

        $this->assertEquals(['/v1', '/v2'], $router->getFileCollection()->getAllKeys());
        $this->assertEquals(['v1/routes.php', 'v2/routes.php'], $router->getFileCollection()->getAllValues());
        $this->assertFalse($router->isEndpointsLoaded());
    }

    public function testFilesCreatesRouterWithEmptyFileList(): void
    {
        $router = Router::files(Collection::new([]));

        $this->assertEmpty($router->getFileCollection()->getRawData());
        $this->assertFalse($router->isEndpointsLoaded());
    }

    public function testEndpointsCreatesRouterWithEndpointList(): void
    {
        $endpoints = [
            Endpoint::view(false, '/', 'home.php'),
            Endpoint::api(false, 'GET', '/users', 'users.php')
        ];

        $router = Router::endpoints($endpoints);

        $this->assertEquals($endpoints, $router->getEndpointList());
        $this->assertTrue($router->isEndpointsLoaded());
    }

    public function testEndpointsCreatesRouterWithEmptyEndpointList(): void
    {
        $router = Router::endpoints([]);

        $this->assertEquals([], $router->getEndpointList());
        // endpointsLoaded is set based on !empty($endpointList), so empty array = false
        $this->assertFalse($router->isEndpointsLoaded());
    }

    public function testGetEndpointsLoadedReturnsTrueForEndpointsRouter(): void
    {
        $router = Router::endpoints([Endpoint::view(false, '/', 'home.php')]);

        $this->assertTrue($router->isEndpointsLoaded());
    }

    public function testGetEndpointsLoadedReturnsFalseForFilesRouter(): void
    {
        $router = Router::files(Collection::new([Item::new('/v1', 'routes.php')]));

        $this->assertFalse($router->isEndpointsLoaded());
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

    public function testGetFileCollectionReturnsCorrectList(): void
    {
        $files = Collection::new([
            Item::new('/api/v1', 'api/v1.php'),
            Item::new('/api/v2', 'api/v2.php')
        ]);

        $router = Router::files($files);

        $this->assertEquals(['/api/v1', '/api/v2'], $router->getFileCollection()->getAllKeys());
        $this->assertEquals(['api/v1.php', 'api/v2.php'], $router->getFileCollection()->getAllValues());
    }

    public function testFilesRouterHasEmptyEndpointList(): void
    {
        $router = Router::files(Collection::new([Item::new('/v1', 'routes.php')]));

        $this->assertEmpty($router->getEndpointList());
    }

    public function testEndpointsRouterHasEmptyFileCollection(): void
    {
        $router = Router::endpoints([Endpoint::view(false, '/', 'home.php')]);

        $this->assertEmpty($router->getFileCollection()->getRawData());
    }
}
