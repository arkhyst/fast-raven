<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Router;
use FastRaven\Components\Routing\Endpoint;

class RouterTest extends TestCase
{
    public function testFilesCreatesRouterWithFileList(): void
    {
        $fileList = ['/v1' => 'v1/routes.php', '/v2' => 'v2/routes.php'];

        $router = Router::files($fileList);

        $this->assertEquals($fileList, $router->getAssocFileList());
        $this->assertFalse($router->getEndpointsLoaded());
    }

    public function testFilesCreatesRouterWithEmptyFileList(): void
    {
        $router = Router::files([]);

        $this->assertEquals([], $router->getAssocFileList());
        $this->assertFalse($router->getEndpointsLoaded());
    }

    public function testEndpointsCreatesRouterWithEndpointList(): void
    {
        $endpoints = [
            Endpoint::view(false, '/', 'home.php'),
            Endpoint::api(false, 'GET', '/users', 'users.php')
        ];

        $router = Router::endpoints($endpoints);

        $this->assertEquals($endpoints, $router->getEndpointList());
        $this->assertTrue($router->getEndpointsLoaded());
    }

    public function testEndpointsCreatesRouterWithEmptyEndpointList(): void
    {
        $router = Router::endpoints([]);

        $this->assertEquals([], $router->getEndpointList());
        // endpointsLoaded is set based on !empty($endpointList), so empty array = false
        $this->assertFalse($router->getEndpointsLoaded());
    }

    public function testGetEndpointsLoadedReturnsTrueForEndpointsRouter(): void
    {
        $router = Router::endpoints([Endpoint::view(false, '/', 'home.php')]);

        $this->assertTrue($router->getEndpointsLoaded());
    }

    public function testGetEndpointsLoadedReturnsFalseForFilesRouter(): void
    {
        $router = Router::files(['/v1' => 'routes.php']);

        $this->assertFalse($router->getEndpointsLoaded());
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

    public function testGetAssocFileListReturnsCorrectList(): void
    {
        $files = [
            '/api/v1' => 'api/v1.php',
            '/api/v2' => 'api/v2.php'
        ];

        $router = Router::files($files);

        $this->assertEquals($files, $router->getAssocFileList());
    }

    public function testFilesRouterHasEmptyEndpointList(): void
    {
        $router = Router::files(['/v1' => 'routes.php']);

        $this->assertEmpty($router->getEndpointList());
    }

    public function testEndpointsRouterHasEmptyAssocFileList(): void
    {
        $router = Router::endpoints([Endpoint::view(false, '/', 'home.php')]);

        $this->assertEmpty($router->getAssocFileList());
    }
}
