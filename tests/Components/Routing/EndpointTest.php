<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Endpoint;
use FastRaven\Types\EndpointType;

class EndpointTest extends TestCase
{
    public function testApiCreatesEndpointWithApiPrefix(): void
    {
        $endpoint = Endpoint::api(false, 'GET', 'users', 'users.php');

        $this->assertEquals('/api/users/#GET', $endpoint->getComplexPath());
        $this->assertEquals('users.php', $endpoint->getFile());
        $this->assertFalse($endpoint->getRestricted());
        $this->assertEquals(EndpointType::API, $endpoint->getType());
    }

    public function testApiCreatesRestrictedEndpoint(): void
    {
        $endpoint = Endpoint::api(true, 'POST', 'admin/users', 'admin/users.php');

        $this->assertTrue($endpoint->getRestricted());
    }

    public function testApiWithDifferentHttpMethods(): void
    {
        $getEndpoint = Endpoint::api(false, 'GET', 'users', 'users.php');
        $postEndpoint = Endpoint::api(false, 'POST', 'users', 'create.php');
        $putEndpoint = Endpoint::api(false, 'PUT', 'users/1', 'update.php');
        $deleteEndpoint = Endpoint::api(false, 'DELETE', 'users/1', 'delete.php');

        $this->assertEquals('/api/users/#GET', $getEndpoint->getComplexPath());
        $this->assertEquals('/api/users/#POST', $postEndpoint->getComplexPath());
        $this->assertEquals('/api/users/1/#PUT', $putEndpoint->getComplexPath());
        $this->assertEquals('/api/users/1/#DELETE', $deleteEndpoint->getComplexPath());
    }

    public function testApiWithMiddleware(): void
    {
        $endpoint = Endpoint::api(false, 'GET', 'admin', 'admin.php', 'requireAdmin');

        $this->assertEquals('requireAdmin', $endpoint->getMiddlewareId());
    }

    public function testApiWithoutMiddleware(): void
    {
        $endpoint = Endpoint::api(false, 'GET', 'users', 'users.php');

        $this->assertEquals('', $endpoint->getMiddlewareId());
    }

    public function testViewCreatesEndpointWithoutApiPrefix(): void
    {
        $endpoint = Endpoint::view(false, '/home', 'Home.php');

        $this->assertEquals('/home/#GET', $endpoint->getComplexPath());
        $this->assertEquals('Home.php', $endpoint->getFile());
        $this->assertFalse($endpoint->getRestricted());
        $this->assertEquals(EndpointType::VIEW, $endpoint->getType());
    }

    public function testViewCreatesRestrictedEndpoint(): void
    {
        $endpoint = Endpoint::view(true, '/admin', 'Admin.php');

        $this->assertTrue($endpoint->getRestricted());
    }

    public function testViewAlwaysUsesGetMethod(): void
    {
        $endpoint = Endpoint::view(false, '/about', 'About.php');

        $this->assertStringEndsWith('#GET', $endpoint->getComplexPath());
    }

    public function testViewWithMiddleware(): void
    {
        $endpoint = Endpoint::view(false, '/login', 'Login.php', 'guestOnly');

        $this->assertEquals('guestOnly', $endpoint->getMiddlewareId());
    }

    public function testViewWithoutMiddleware(): void
    {
        $endpoint = Endpoint::view(false, '/home', 'Home.php');

        $this->assertEquals('', $endpoint->getMiddlewareId());
    }

    public function testCdnCreatesEndpointWithCdnPrefix(): void
    {
        $endpoint = Endpoint::cdn(false, 'GET', 'favicon', 'Favicon.php');

        $this->assertEquals('/cdn/favicon/#GET', $endpoint->getComplexPath());
        $this->assertEquals('Favicon.php', $endpoint->getFile());
        $this->assertFalse($endpoint->getRestricted());
        $this->assertEquals(EndpointType::CDN, $endpoint->getType());
    }

    public function testCdnWithMiddleware(): void
    {
        $endpoint = Endpoint::cdn(false, 'GET', 'protected', 'Protected.php', 'requireAuth');

        $this->assertEquals('requireAuth', $endpoint->getMiddlewareId());
    }

    public function testCdnWithoutMiddleware(): void
    {
        $endpoint = Endpoint::cdn(false, 'GET', 'public', 'Public.php');

        $this->assertEquals('', $endpoint->getMiddlewareId());
    }

    public function testRouterCreatesEndpointWithRouterType(): void
    {
        $endpoint = Endpoint::router(EndpointType::API, false, '/admin', 'admin.php');

        $this->assertEquals(EndpointType::ROUTER, $endpoint->getType());
        $this->assertEquals('/api/admin/#GET', $endpoint->getComplexPath());
    }

    public function testRouterWithCdnTypeAddsCdnPrefix(): void
    {
        $endpoint = Endpoint::router(EndpointType::CDN, false, '/images', 'images.php');

        $this->assertEquals('/cdn/images/#GET', $endpoint->getComplexPath());
    }

    public function testRouterWithViewTypeNoPrefix(): void
    {
        $endpoint = Endpoint::router(EndpointType::VIEW, false, '/admin', 'admin.php');

        $this->assertEquals('/admin/#GET', $endpoint->getComplexPath());
    }

    public function testRouterWithMiddleware(): void
    {
        $endpoint = Endpoint::router(EndpointType::API, true, '/admin', 'admin.php', 'requireAdmin');

        $this->assertEquals('requireAdmin', $endpoint->getMiddlewareId());
        $this->assertTrue($endpoint->getRestricted());
    }

    public function testPathNormalizationRemovesLeadingSlash(): void
    {
        $endpoint = Endpoint::view(false, '/about/', 'About.php');

        // Bee::normalizePath normalizes path, then we add trailing slash
        $this->assertEquals('/about/#GET', $endpoint->getComplexPath());
    }

    public function testPathNormalizationHandlesRootPath(): void
    {
        $endpoint = Endpoint::view(false, '/', 'Home.php');

        $this->assertEquals('/#GET', $endpoint->getComplexPath());
    }

    public function testPathNormalizationHandlesMultipleSlashes(): void
    {
        $endpoint = Endpoint::view(false, '//path//to//page//', 'Page.php');

        // Should normalize to single slashes with trailing slash
        $this->assertStringStartsWith('/path/to/page/', $endpoint->getComplexPath());
    }

    public function testApiPathNormalization(): void
    {
        $endpoint = Endpoint::api(false, 'GET', '/v1/users/', 'users.php');

        // Should have /api/ prefix and normalized path with trailing slash
        $this->assertEquals('/api/v1/users/#GET', $endpoint->getComplexPath());
    }

    public function testFilePathNormalization(): void
    {
        $endpoint = Endpoint::view(false, '/home', '//views//Home.php');

        // File path should be normalized
        $this->assertEquals('views/Home.php', $endpoint->getFile());
    }

    public function testGetRestrictedReturnsFalseByDefault(): void
    {
        $endpoint = Endpoint::view(false, '/public', 'Public.php');

        $this->assertFalse($endpoint->getRestricted());
    }

    public function testComplexPathIncludesMethodSuffix(): void
    {
        $endpoint = Endpoint::api(false, 'POST', 'submit', 'submit.php');

        $this->assertStringEndsWith('#POST', $endpoint->getComplexPath());
    }

    public function testNestedApiPaths(): void
    {
        $endpoint = Endpoint::api(false, 'GET', 'v1/admin/users/123', 'admin/users/show.php');

        $this->assertEquals('/api/v1/admin/users/123/#GET', $endpoint->getComplexPath());
        $this->assertEquals('admin/users/show.php', $endpoint->getFile());
    }

    public function testNestedViewPaths(): void
    {
        $endpoint = Endpoint::view(false, '/admin/dashboard/stats', 'admin/dashboard/Stats.php');

        $this->assertEquals('/admin/dashboard/stats/#GET', $endpoint->getComplexPath());
        $this->assertEquals('admin/dashboard/Stats.php', $endpoint->getFile());
    }

    public function testGetPath(): void
    {
        $endpoint = Endpoint::api(false, 'GET', 'users', 'users.php');

        $this->assertEquals('/api/users/', $endpoint->getPath());
    }
}
