<?php

namespace FastRaven\Tests\Internal\Slave;

use PHPUnit\Framework\TestCase;
use FastRaven\Internal\Slave\AuthSlave;
use ReflectionClass;

class AuthSlaveTest extends TestCase
{
    public function testValidateCSRFReturnsTrueWhenTokensMatch(): void
    {
        $authSlave = $this->createAuthSlaveInstance();

        $result = $authSlave->validateCSRF('token123', 'token123');

        $this->assertTrue($result);
    }

    public function testValidateCSRFReturnsFalseWhenTokensDontMatch(): void
    {
        $authSlave = $this->createAuthSlaveInstance();

        $result = $authSlave->validateCSRF('token123', 'token456');

        $this->assertFalse($result);
    }

    public function testValidateCSRFReturnsTrueWhenBothTokensAreNull(): void
    {
        $authSlave = $this->createAuthSlaveInstance();

        $result = $authSlave->validateCSRF(null, null);

        $this->assertTrue($result);
    }

    public function testValidateCSRFReturnsFalseWhenOneTokenIsNull(): void
    {
        $authSlave = $this->createAuthSlaveInstance();

        $result1 = $authSlave->validateCSRF('token123', null);
        $result2 = $authSlave->validateCSRF(null, 'token123');

        $this->assertFalse($result1);
        $this->assertFalse($result2);
    }

    public function testValidateCSRFIsCaseSensitive(): void
    {
        $authSlave = $this->createAuthSlaveInstance();

        $result = $authSlave->validateCSRF('Token123', 'token123');

        $this->assertFalse($result);
    }

    public function testValidateCSRFHandlesEmptyStrings(): void
    {
        $authSlave = $this->createAuthSlaveInstance();

        $result = $authSlave->validateCSRF('', '');

        $this->assertTrue($result);
    }

    public function testValidateCSRFHandlesSpecialCharacters(): void
    {
        $authSlave = $this->createAuthSlaveInstance();
        $token = 'token!@#$%^&*()_+-=[]{}|;:,.<>?';

        $result = $authSlave->validateCSRF($token, $token);

        $this->assertTrue($result);
    }

    /**
     * Helper method to create an AuthSlave instance using reflection
     * since the constructor is private
     */
    private function createAuthSlaveInstance(): AuthSlave
    {
        $reflection = new ReflectionClass(AuthSlave::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        return $instance;
    }
}
