<?php

namespace FastRaven\Tests\Internals\Engines;

use PHPUnit\Framework\TestCase;
use FastRaven\Internals\Engines\AuthEngine;
use ReflectionClass;

class AuthEngineTest extends TestCase
{
    public function testValidateCSRFReturnsTrueWhenTokensMatch(): void
    {
        $authEngine = $this->createAuthEngineInstance();

        $result = $authEngine->validateCSRF('token123', 'token123');

        $this->assertTrue($result);
    }

    public function testValidateCSRFReturnsFalseWhenTokensDontMatch(): void
    {
        $authEngine = $this->createAuthEngineInstance();

        $result = $authEngine->validateCSRF('token123', 'token456');

        $this->assertFalse($result);
    }

    public function testValidateCSRFReturnsFalseWhenBothTokensAreNull(): void
    {
        $authEngine = $this->createAuthEngineInstance();

        $result = $authEngine->validateCSRF(null, null);

        $this->assertFalse($result);
    }

    public function testValidateCSRFReturnsFalseWhenOneTokenIsNull(): void
    {
        $authEngine = $this->createAuthEngineInstance();

        $result1 = $authEngine->validateCSRF('token123', null);
        $result2 = $authEngine->validateCSRF(null, 'token123');

        $this->assertFalse($result1);
        $this->assertFalse($result2);
    }

    public function testValidateCSRFIsCaseSensitive(): void
    {
        $authEngine = $this->createAuthEngineInstance();

        $result = $authEngine->validateCSRF('Token123', 'token123');

        $this->assertFalse($result);
    }

    public function testValidateCSRFHandlesEmptyStrings(): void
    {
        $authEngine = $this->createAuthEngineInstance();

        $result = $authEngine->validateCSRF('', '');

        $this->assertTrue($result);
    }

    public function testValidateCSRFHandlesSpecialCharacters(): void
    {
        $authEngine = $this->createAuthEngineInstance();
        $token = 'token!@#$%^&*()_+-=[]{}|;:,.<>?';

        $result = $authEngine->validateCSRF($token, $token);

        $this->assertTrue($result);
    }

    /**
     * Helper method to create an AuthEngine instance using reflection
     * since the constructor is private
     */
    private function createAuthEngineInstance(): AuthEngine
    {
        $reflection = new ReflectionClass(AuthEngine::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        return $instance;
    }
}
