<?php

namespace FastRaven\Tests\Components\Http;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Http\Request;

class RequestTest extends TestCase
{
    public function testConstructorParsesSimpleUri(): void
    {
        $request = new Request('/home', 'GET', '', '127.0.0.1');

        $this->assertEquals('/home', $request->getPath());
        $this->assertEquals('GET', $request->getMethod());
    }

    public function testConstructorParsesRootPath(): void
    {
        $request = new Request('/', 'GET', '', '127.0.0.1');

        $this->assertEquals('/', $request->getPath());
    }

    public function testConstructorKeepsTrailingSlashInPath(): void
    {
        $request = new Request('/about/', 'GET', '', '127.0.0.1');

        // Path keeps trailing slash, but complexPath removes it
        $this->assertEquals('/about/', $request->getPath());
        $this->assertEquals('/about#GET', $request->getComplexPath());
    }

    public function testConstructorKeepsRootSlash(): void
    {
        $request = new Request('/', 'GET', '', '127.0.0.1');

        $this->assertEquals('/', $request->getPath());
    }

    public function testConstructorParsesUriWithQueryString(): void
    {
        $request = new Request('/search?q=test&page=1', 'GET', '', '127.0.0.1');

        $this->assertEquals('/search', $request->getPath());
    }

    public function testComplexPathGenerationForGetRequest(): void
    {
        $request = new Request('/home', 'GET', '', '127.0.0.1');

        $this->assertEquals('/home#GET', $request->getComplexPath());
    }

    public function testComplexPathGenerationForPostRequest(): void
    {
        $request = new Request('/submit', 'POST', '', '127.0.0.1');

        $this->assertEquals('/submit#POST', $request->getComplexPath());
    }

    public function testComplexPathGenerationForRootPath(): void
    {
        $request = new Request('/', 'GET', '', '127.0.0.1');

        $this->assertEquals('/#GET', $request->getComplexPath());
    }

    public function testIsApiReturnsTrueForApiPaths(): void
    {
        $request = new Request('/api/users', 'GET', '', '127.0.0.1');

        $this->assertTrue($request->isApi());
    }

    public function testIsApiReturnsFalseForNonApiPaths(): void
    {
        $request = new Request('/home', 'GET', '', '127.0.0.1');

        $this->assertFalse($request->isApi());
    }

    public function testIsApiReturnsTrueForNestedApiPaths(): void
    {
        $request = new Request('/api/v1/users/123', 'GET', '', '127.0.0.1');

        $this->assertTrue($request->isApi());
    }

    public function testGetDataItemReturnsValueForExistingKey(): void
    {
        $jsonData = json_encode(['username' => 'testuser', 'email' => 'test@example.com']);
        $request = new Request('/submit', 'POST', $jsonData, '127.0.0.1');

        $this->assertEquals('testuser', $request->getDataItem('username'));
        $this->assertEquals('test@example.com', $request->getDataItem('email'));
    }

    public function testGetDataItemReturnsNullForNonExistentKey(): void
    {
        $jsonData = json_encode(['username' => 'testuser']);
        $request = new Request('/submit', 'POST', $jsonData, '127.0.0.1');

        $this->assertNull($request->getDataItem('nonexistent'));
    }

    public function testGetDataItemStripsHtmlTags(): void
    {
        $jsonData = json_encode(['content' => '<script>alert("xss")</script>Hello']);
        $request = new Request('/submit', 'POST', $jsonData, '127.0.0.1');

        $result = $request->getDataItem('content');

        // strip_tags removes opening tags but not the content between them
        $this->assertEquals('alert("xss")Hello', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testGetDataItemTrimsWhitespace(): void
    {
        $jsonData = json_encode(['name' => '  John Doe  ']);
        $request = new Request('/submit', 'POST', $jsonData, '127.0.0.1');

        $this->assertEquals('John Doe', $request->getDataItem('name'));
    }

    public function testConstructorHandlesInvalidJsonData(): void
    {
        $request = new Request('/submit', 'POST', 'invalid json', '127.0.0.1');

        $this->assertNull($request->getDataItem('any_key'));
    }

    public function testConstructorHandlesEmptyJsonData(): void
    {
        $request = new Request('/submit', 'POST', '', '127.0.0.1');

        $this->assertNull($request->getDataItem('any_key'));
    }

    public function testGetDataItemWithBooleanValue(): void
    {
        $jsonData = json_encode(['active' => true, 'deleted' => false]);
        $request = new Request('/submit', 'POST', $jsonData, '127.0.0.1');

        // With improved implementation, booleans remain as booleans
        $this->assertTrue($request->getDataItem('active'));
        $this->assertFalse($request->getDataItem('deleted'));
    }

    public function testGetDataItemWithIntegerValue(): void
    {
        $jsonData = json_encode(['count' => 42, 'zero' => 0]);
        $request = new Request('/submit', 'POST', $jsonData, '127.0.0.1');

        // Integers remain as integers with improved implementation
        $this->assertSame(42, $request->getDataItem('count'));
        $this->assertSame(0, $request->getDataItem('zero'));
    }

    public function testGetOriginInfoContainsIpAddress(): void
    {
        $request = new Request('/home', 'GET', '', '192.168.1.100');

        $originInfo = $request->getOriginInfo();

        $this->assertIsArray($originInfo);
        $this->assertArrayHasKey('IP', $originInfo);
        $this->assertEquals('192.168.1.100', $originInfo['IP']);
    }

    public function testGetInternalIdIsHexString(): void
    {
        $request = new Request('/home', 'GET', '', '127.0.0.1');

        $internalId = $request->getInternalID();

        $this->assertIsString($internalId);
        $this->assertEquals(8, strlen($internalId));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{8}$/', $internalId);
    }

    public function testGetInternalIdIsUnique(): void
    {
        $request1 = new Request('/home', 'GET', '', '127.0.0.1');
        $request2 = new Request('/home', 'GET', '', '127.0.0.1');

        $this->assertNotEquals($request1->getInternalID(), $request2->getInternalID());
    }

    public function testConstructorHandlesVariousHttpMethods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];

        foreach ($methods as $method) {
            $request = new Request('/endpoint', $method, '', '127.0.0.1');
            $this->assertEquals($method, $request->getMethod());
        }
    }

    public function testGetDataItemWithComplexJsonFormats(): void
    {
        $complexJson = json_encode([
            'string' => 'text',
            'number' => 123,
            'float' => 45.67,
            'boolean' => true,
            'unicode' => 'Hello 世界 🌍',
            'special_chars' => '!@#$%^&*()',
        ]);

        $request = new Request('/submit', 'POST', $complexJson, '127.0.0.1');

        // With improved implementation, scalar values keep their types
        $this->assertEquals('text', $request->getDataItem('string'));
        $this->assertSame(123, $request->getDataItem('number'));
        $this->assertSame(45.67, $request->getDataItem('float'));
        $this->assertTrue($request->getDataItem('boolean'));
        $this->assertEquals('Hello 世界 🌍', $request->getDataItem('unicode'));
        $this->assertEquals('!@#$%^&*()', $request->getDataItem('special_chars'));
    }
}
