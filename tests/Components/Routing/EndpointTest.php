<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Endpoint;
use FastRaven\Components\Core\Template;

class EndpointTest extends TestCase
{
    public function testApiCreatesEndpointWithApiPrefix(): void
    {
        $endpoint = Endpoint::api(false, 'GET', 'users', 'users.php');

        $this->assertEquals('/api/users#GET', $endpoint->getComplexPath());
        $this->assertEquals('users.php', $endpoint->getFile());
        $this->assertFalse($endpoint->getRestricted());
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

        $this->assertEquals('/api/users#GET', $getEndpoint->getComplexPath());
        $this->assertEquals('/api/users#POST', $postEndpoint->getComplexPath());
        $this->assertEquals('/api/users/1#PUT', $putEndpoint->getComplexPath());
        $this->assertEquals('/api/users/1#DELETE', $deleteEndpoint->getComplexPath());
    }

    public function testViewCreatesEndpointWithoutApiPrefix(): void
    {
        $endpoint = Endpoint::view(false, '/home', 'home.php');

        $this->assertEquals('/home#GET', $endpoint->getComplexPath());
        $this->assertEquals('home.php', $endpoint->getFile());
        $this->assertFalse($endpoint->getRestricted());
    }

    public function testViewCreatesRestrictedEndpoint(): void
    {
        $endpoint = Endpoint::view(true, '/admin', 'admin.php');

        $this->assertTrue($endpoint->getRestricted());
    }

    public function testViewAlwaysUsesGetMethod(): void
    {
        $endpoint = Endpoint::view(false, '/about', 'about.php');

        $this->assertStringEndsWith('#GET', $endpoint->getComplexPath());
    }

    public function testViewWithoutTemplate(): void
    {
        $endpoint = Endpoint::view(false, '/home', 'home.php');

        $this->assertNull($endpoint->getTemplate());
    }

    public function testViewWithTemplate(): void
    {
        $template = Template::new('Page Title', '1.0');
        $endpoint = Endpoint::view(false, '/home', 'home.php', $template);

        $this->assertInstanceOf(Template::class, $endpoint->getTemplate());
        $this->assertSame($template, $endpoint->getTemplate());
    }

    public function testPathNormalizationRemovesLeadingSlash(): void
    {
        $endpoint = Endpoint::view(false, '/about/', 'about.php');

        // Bee::normalizePath should remove leading and trailing slashes
        $this->assertEquals('/about#GET', $endpoint->getComplexPath());
    }

    public function testPathNormalizationHandlesRootPath(): void
    {
        $endpoint = Endpoint::view(false, '/', 'home.php');

        $this->assertEquals('/#GET', $endpoint->getComplexPath());
    }

    public function testPathNormalizationHandlesMultipleSlashes(): void
    {
        $endpoint = Endpoint::view(false, '//path//to//page//', 'page.php');

        // Should normalize to single slashes
        $this->assertStringStartsWith('/path/to/page#', $endpoint->getComplexPath());
    }

    public function testApiPathNormalization(): void
    {
        $endpoint = Endpoint::api(false, 'GET', '/v1/users/', 'users.php');

        // Should have /api/ prefix and normalized path
        $this->assertEquals('/api/v1/users#GET', $endpoint->getComplexPath());
    }

    public function testFilePathNormalization(): void
    {
        $endpoint = Endpoint::view(false, '/home', '//views//home.php');

        // File path should be normalized
        $this->assertEquals('views/home.php', $endpoint->getFile());
    }

    public function testGetRestrictedReturnsFalseByDefault(): void
    {
        $endpoint = Endpoint::view(false, '/public', 'public.php');

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

        $this->assertEquals('/api/v1/admin/users/123#GET', $endpoint->getComplexPath());
        $this->assertEquals('admin/users/show.php', $endpoint->getFile());
    }

    public function testNestedViewPaths(): void
    {
        $endpoint = Endpoint::view(false, '/admin/dashboard/stats', 'admin/dashboard/stats.php');

        $this->assertEquals('/admin/dashboard/stats#GET', $endpoint->getComplexPath());
        $this->assertEquals('admin/dashboard/stats.php', $endpoint->getFile());
    }

    public function testApiUnauthorizedExclusiveDefaultsFalse(): void
    {
        $endpoint = Endpoint::api(false, 'GET', 'users', 'users.php');

        $this->assertFalse($endpoint->getUnauthorizedExclusive());
    }

    public function testApiUnauthorizedExclusiveTrue(): void
    {
        $endpoint = Endpoint::api(false, 'GET', 'register', 'register.php', true);

        $this->assertTrue($endpoint->getUnauthorizedExclusive());
    }

    public function testApiUnauthorizedExclusiveFalse(): void
    {
        $endpoint = Endpoint::api(false, 'POST', 'login', 'login.php', false);

        $this->assertFalse($endpoint->getUnauthorizedExclusive());
    }

    public function testViewUnauthorizedExclusiveDefaultsFalse(): void
    {
        $endpoint = Endpoint::view(false, '/home', 'home.php');

        $this->assertFalse($endpoint->getUnauthorizedExclusive());
    }

    public function testViewUnauthorizedExclusiveTrue(): void
    {
        $endpoint = Endpoint::view(false, '/login', 'login.php', null, true);

        $this->assertTrue($endpoint->getUnauthorizedExclusive());
    }

    public function testViewUnauthorizedExclusiveFalse(): void
    {
        $template = Template::new('Page', '1.0');
        $endpoint = Endpoint::view(false, '/profile', 'profile.php', $template, false);

        $this->assertFalse($endpoint->getUnauthorizedExclusive());
    }

    public function testApiRestrictedWithUnauthorizedExclusive(): void
    {
        $endpoint = Endpoint::api(true, 'GET', 'admin', 'admin.php', true);

        $this->assertTrue($endpoint->getRestricted());
        $this->assertTrue($endpoint->getUnauthorizedExclusive());
    }

    public function testViewRestrictedWithUnauthorizedExclusive(): void
    {
        $endpoint = Endpoint::view(true, '/dashboard', 'dashboard.php', null, true);

        $this->assertTrue($endpoint->getRestricted());
        $this->assertTrue($endpoint->getUnauthorizedExclusive());
    }
}
