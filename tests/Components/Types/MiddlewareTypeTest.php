<?php

namespace FastRaven\Tests\Components\Types;

use PHPUnit\Framework\TestCase;
use FastRaven\Types\EndpointType;

class EndpointTypeTest extends TestCase
{
    public function testViewCaseExists(): void
    {
        $this->assertEquals('VIEW', EndpointType::VIEW->value);
    }

    public function testApiCaseExists(): void
    {
        $this->assertEquals('API', EndpointType::API->value);
    }

    public function testCdnCaseExists(): void
    {
        $this->assertEquals('CDN', EndpointType::CDN->value);
    }

    public function testRouterCaseExists(): void
    {
        $this->assertEquals('ROUTER', EndpointType::ROUTER->value);
    }

    public function testEnumIsStringBacked(): void
    {
        $this->assertIsString(EndpointType::VIEW->value);
        $this->assertIsString(EndpointType::API->value);
        $this->assertIsString(EndpointType::CDN->value);
        $this->assertIsString(EndpointType::ROUTER->value);
    }

    public function testAllCasesCount(): void
    {
        $cases = EndpointType::cases();
        $this->assertCount(4, $cases);
    }
}
