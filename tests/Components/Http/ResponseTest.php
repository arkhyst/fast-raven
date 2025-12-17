<?php

namespace FastRaven\Tests\Components\Http;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Http\Response;

class ResponseTest extends TestCase
{
    public function testNewCreatesResponseWithSuccessStatus(): void
    {
        $response = Response::new(true, 200);

        $this->assertTrue($response->getSuccess());
        $this->assertEquals(200, $response->getCode());
    }

    public function testNewCreatesResponseWithErrorStatus(): void
    {
        $response = Response::new(false, 404);

        $this->assertFalse($response->getSuccess());
        $this->assertEquals(404, $response->getCode());
    }

    public function testNewWithMessageAndData(): void
    {
        $response = Response::new(true, 200, 'Success message', ['key' => 'value']);

        $this->assertEquals('Success message', $response->getMessage());
        $this->assertEquals(['key' => 'value'], $response->getData());
    }

    public function testSetBodySetsMessageAndData(): void
    {
        $response = Response::new(true, 200);

        $response->setBody('Updated message', ['updated' => 'data']);

        $this->assertEquals('Updated message', $response->getMessage());
        $this->assertEquals(['updated' => 'data'], $response->getData());
    }

    public function testSetBodyWithEmptyParameters(): void
    {
        $response = Response::new(true, 200, 'Initial', ['initial']);

        $response->setBody();

        $this->assertEquals('', $response->getMessage());
        $this->assertEquals([], $response->getData());
    }

    public function testGetCodeReturnsCorrectHttpCode(): void
    {
        $response = Response::new(true, 201);

        $this->assertEquals(201, $response->getCode());
    }

    public function testDataCanBeMixedType(): void
    {
        $stringData = Response::new(true, 200, '', 'string data');
        $arrayData = Response::new(true, 200, '', ['array', 'data']);
        $intData = Response::new(true, 200, '', 42);

        $this->assertEquals('string data', $stringData->getData());
        $this->assertEquals(['array', 'data'], $arrayData->getData());
        $this->assertEquals(42, $intData->getData());
    }

    public function testCommonHttpStatusCodes(): void
    {
        $codes = [200, 201, 301, 400, 401, 403, 404, 500];

        foreach ($codes as $code) {
            $response = Response::new(true, $code);
            $this->assertEquals($code, $response->getCode());
        }
    }

    public function testMessageCanContainSpecialCharacters(): void
    {
        $message = 'Error: Invalid input! @#$%^&*()';
        $response = Response::new(false, 400, $message);

        $this->assertEquals($message, $response->getMessage());
    }
}
