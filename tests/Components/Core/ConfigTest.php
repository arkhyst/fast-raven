<?php

namespace FastRaven\Tests\Components\Core;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Core\Config;

class ConfigTest extends TestCase
{
    public function testNewCreatesInstanceWithCorrectValues(): void
    {
        $config = Config::new('main', true);

        $this->assertEquals('main', $config->getSiteName());
        $this->assertTrue($config->isRestricted());
    }

    public function testNewCreatesNonRestrictedSite(): void
    {
        $config = Config::new('public', false);

        $this->assertEquals('public', $config->getSiteName());
        $this->assertFalse($config->isRestricted());
    }





    public function testDefaultAuthSessionName(): void
    {
        $config = Config::new('test', false);

        $this->assertEquals('PHPSESSID', $config->getAuthSessionName());
    }

    public function testDefaultAuthLifetime(): void
    {
        $config = Config::new('test', false);

        // Default is 7 days = 7 * 24 * 60 * 60 = 604800 seconds
        $this->assertEquals(604800, $config->getAuthLifetime());
    }

    public function testDefaultAuthGlobalIsFalse(): void
    {
        $config = Config::new('test', false);

        $this->assertFalse($config->isAuthGlobal());
    }

    public function testConfigureAuthorizationSetsGlobalAuthTrue(): void
    {
        $config = Config::new('test', false);

        $config->configureAuthorization('SESSION', 7, true);

        $this->assertTrue($config->isAuthGlobal());
    }

    public function testConfigureAuthorizationSetsGlobalAuthFalse(): void
    {
        $config = Config::new('test', false);

        $config->configureAuthorization('SESSION', 7, false);

        $this->assertFalse($config->isAuthGlobal());
    }

    public function testConfigureAuthorizationSetsSessionName(): void
    {
        $config = Config::new('test', false);

        $config->configureAuthorization('CUSTOM_SESSION', 30, false);

        $this->assertEquals('CUSTOM_SESSION', $config->getAuthSessionName());
    }

    public function testConfigureAuthorizationCalculatesLifetime(): void
    {
        $config = Config::new('test', false);

        $config->configureAuthorization('SESSION', 1, false);

        // 1 day = 86400 seconds
        $this->assertEquals(86400, $config->getAuthLifetime());
    }

    public function testConfigureAuthorizationCalculatesLifetimeForMultipleDays(): void
    {
        $config = Config::new('test', false);

        $config->configureAuthorization('SESSION', 14, false);

        // 14 days = 1209600 seconds
        $this->assertEquals(1209600, $config->getAuthLifetime());
    }

    public function testDefaultNotFoundPathRedirect(): void
    {
        $config = Config::new('test', false);

        $this->assertEquals('/', $config->getDefaultNotFoundPathRedirect());
    }

    public function testConfigureNotFoundRedirectsSetsPath(): void
    {
        $config = Config::new('test', false);

        $config->configureNotFoundRedirects('/404');

        $this->assertEquals('/404', $config->getDefaultNotFoundPathRedirect());
    }

    public function testDefaultUnauthorizedPathRedirect(): void
    {
        $config = Config::new('test', false);

        $this->assertEquals('/login', $config->getDefaultUnauthorizedPathRedirect());
    }

    public function testDefaultUnauthorizedSubdomainRedirect(): void
    {
        $config = Config::new('test', false);

        $this->assertEquals('', $config->getDefaultUnauthorizedSubdomainRedirect());
    }

    public function testConfigureUnauthorizedRedirectsSetsPath(): void
    {
        $config = Config::new('test', false);

        $config->configureUnauthorizedRedirects('/auth');

        $this->assertEquals('/auth', $config->getDefaultUnauthorizedPathRedirect());
    }

    public function testConfigureUnauthorizedRedirectsSetsSubdomain(): void
    {
        $config = Config::new('test', false);

        $config->configureUnauthorizedRedirects('/login', 'auth.example.com');

        $this->assertEquals('/login', $config->getDefaultUnauthorizedPathRedirect());
        $this->assertEquals('auth.example.com', $config->getDefaultUnauthorizedSubdomainRedirect());
    }

    public function testConfigureUnauthorizedRedirectsWithEmptySubdomain(): void
    {
        $config = Config::new('test', false);

        $config->configureUnauthorizedRedirects('/login', '');

        $this->assertEquals('', $config->getDefaultUnauthorizedSubdomainRedirect());
    }

    public function testMultipleConfigurationCalls(): void
    {
        $config = Config::new('test', true);

        $config->configureAuthorization('MY_SESSION', 10, true);
        $config->configureNotFoundRedirects('/not-found');
        $config->configureUnauthorizedRedirects('/unauthorized', 'auth.mydomain.com');

        $this->assertEquals('MY_SESSION', $config->getAuthSessionName());
        $this->assertEquals(864000, $config->getAuthLifetime()); // 10 days
        $this->assertTrue($config->isAuthGlobal());
        $this->assertEquals('/not-found', $config->getDefaultNotFoundPathRedirect());
        $this->assertEquals('/unauthorized', $config->getDefaultUnauthorizedPathRedirect());
        $this->assertEquals('auth.mydomain.com', $config->getDefaultUnauthorizedSubdomainRedirect());
    }
}
