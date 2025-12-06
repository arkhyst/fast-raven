<?php

namespace FastRaven\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use FastRaven\Exceptions\BadImplementationException;
use FastRaven\Exceptions\EndpointFileDoesNotExist;
use FastRaven\Exceptions\NotAuthorizedException;
use FastRaven\Exceptions\NotFoundException;

class ExceptionsTest extends TestCase
{
    // BadImplementationException Tests
    public function testBadImplementationExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(BadImplementationException::class);
        $this->expectExceptionMessage("Endpoint does not return Response object. (/path/to/endpoint.php)");

        throw new BadImplementationException("/path/to/endpoint.php");
    }

    public function testBadImplementationExceptionExtendsException(): void
    {
        $exception = new BadImplementationException("/test/path.php");
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testBadImplementationExceptionHasCorrectStatusCode(): void
    {
        $exception = new BadImplementationException("/test/path.php");
        $this->assertEquals(500, $exception->getStatusCode());
    }

    public function testBadImplementationExceptionHasCorrectPublicMessage(): void
    {
        $exception = new BadImplementationException("/test/path.php");
        $this->assertEquals("This resource is not available at this time.", $exception->getPublicMessage());
    }

    // EndpointFileDoesNotExist Tests
    public function testEndpointFileDoesNotExistCanBeThrownAndCaught(): void
    {
        $this->expectException(EndpointFileDoesNotExist::class);
        $this->expectExceptionMessage("¡Endpoint file does not exist! (/missing/file.php)");

        throw new EndpointFileDoesNotExist("/missing/file.php");
    }

    public function testEndpointFileDoesNotExistExtendsException(): void
    {
        $exception = new EndpointFileDoesNotExist("/test/path.php");
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testEndpointFileDoesNotExistHasCorrectStatusCode(): void
    {
        $exception = new EndpointFileDoesNotExist("/test/path.php");
        $this->assertEquals(500, $exception->getStatusCode());
    }

    public function testEndpointFileDoesNotExistHasCorrectPublicMessage(): void
    {
        $exception = new EndpointFileDoesNotExist("/test/path.php");
        $this->assertEquals("This resource is not available at this time.", $exception->getPublicMessage());
    }

    // NotAuthorizedException Tests (Resource Level)
    public function testNotAuthorizedExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(NotAuthorizedException::class);
        $this->expectExceptionMessage("Unauthorized user tried to access private resource.");

        throw new NotAuthorizedException(false);
    }

    public function testNotAuthorizedExceptionExtendsException(): void
    {
        $exception = new NotAuthorizedException(false);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testNotAuthorizedExceptionResourceLevelHasCorrectStatusCode(): void
    {
        $exception = new NotAuthorizedException(false);
        $this->assertEquals(401, $exception->getStatusCode());
    }

    public function testNotAuthorizedExceptionResourceLevelHasCorrectPublicMessage(): void
    {
        $exception = new NotAuthorizedException(false);
        $this->assertEquals("Authorization required.", $exception->getPublicMessage());
    }

    public function testNotAuthorizedExceptionResourceLevelIsDomainLevelReturnsFalse(): void
    {
        $exception = new NotAuthorizedException(false);
        $this->assertFalse($exception->isDomainLevel());
    }

    // NotAuthorizedException Tests (Domain Level)
    public function testNotAuthorizedExceptionDomainLevelCanBeThrownAndCaught(): void
    {
        $this->expectException(NotAuthorizedException::class);
        $this->expectExceptionMessage("Unauthorized user tried to access private subdomain.");

        throw new NotAuthorizedException(true);
    }

    public function testNotAuthorizedExceptionDomainLevelHasCorrectStatusCode(): void
    {
        $exception = new NotAuthorizedException(true);
        $this->assertEquals(401, $exception->getStatusCode());
    }

    public function testNotAuthorizedExceptionDomainLevelHasCorrectPublicMessage(): void
    {
        $exception = new NotAuthorizedException(true);
        $this->assertEquals("Authorization required.", $exception->getPublicMessage());
    }

    public function testNotAuthorizedExceptionDomainLevelIsDomainLevelReturnsTrue(): void
    {
        $exception = new NotAuthorizedException(true);
        $this->assertTrue($exception->isDomainLevel());
    }

    // NotFoundException Tests
    public function testNotFoundExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("No matching route found for request.");

        throw new NotFoundException();
    }

    public function testNotFoundExceptionExtendsException(): void
    {
        $exception = new NotFoundException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testNotFoundExceptionHasCorrectStatusCode(): void
    {
        $exception = new NotFoundException();
        $this->assertEquals(404, $exception->getStatusCode());
    }

    public function testNotFoundExceptionHasCorrectPublicMessage(): void
    {
        $exception = new NotFoundException();
        $this->assertEquals("Not found.", $exception->getPublicMessage());
    }
}
