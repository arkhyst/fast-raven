<?php

namespace FastRaven\Tests\Components\Types;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Types\MiddlewareType;

class MiddlewareTypeTest extends TestCase
{
    public function testViewCaseExists(): void
    {
        $this->assertEquals('VIEW', MiddlewareType::VIEW->value);
    }

    public function testApiCaseExists(): void
    {
        $this->assertEquals('API', MiddlewareType::API->value);
    }

    public function testCdnCaseExists(): void
    {
        $this->assertEquals('CDN', MiddlewareType::CDN->value);
    }

    public function testRouterCaseExists(): void
    {
        $this->assertEquals('ROUTER', MiddlewareType::ROUTER->value);
    }

    public function testEnumIsStringBacked(): void
    {
        $this->assertIsString(MiddlewareType::VIEW->value);
        $this->assertIsString(MiddlewareType::API->value);
        $this->assertIsString(MiddlewareType::CDN->value);
        $this->assertIsString(MiddlewareType::ROUTER->value);
    }

    public function testAllCasesCount(): void
    {
        $cases = MiddlewareType::cases();
        $this->assertCount(4, $cases);
    }
}
