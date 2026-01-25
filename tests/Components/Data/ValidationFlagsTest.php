<?php

namespace FastRaven\Tests\Components\Data;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Data\ValidationFlags;
use FastRaven\Components\Data\Pair;

class ValidationFlagsTest extends TestCase
{
    public function testEmailFlags(): void
    {
        $flags = ValidationFlags::email(10, 100);

        $this->assertEquals(10, $flags->get("minLength")->getValue());
        $this->assertEquals(100, $flags->get("maxLength")->getValue());
        $this->assertInstanceOf(Pair::class, $flags->get("minLength"));
    }

    public function testPasswordFlags(): void
    {
        $flags = ValidationFlags::password(8, 32, 1, 1, 1, 1);

        $this->assertEquals(8, $flags->get("minLength")->getValue());
        $this->assertEquals(32, $flags->get("maxLength")->getValue());
        $this->assertEquals(1, $flags->get("minNumber")->getValue());
        $this->assertEquals(1, $flags->get("minSpecial")->getValue());
        $this->assertEquals(1, $flags->get("minLowercase")->getValue());
        $this->assertEquals(1, $flags->get("minUppercase")->getValue());
    }

    public function testAgeFlags(): void
    {
        $flags = ValidationFlags::age(18, 99);

        $this->assertEquals(18, $flags->get("minAge")->getValue());
        $this->assertEquals(99, $flags->get("maxAge")->getValue());
    }

    public function testUsernameFlags(): void
    {
        $flags = ValidationFlags::username(3, 20);

        $this->assertEquals(3, $flags->get("minLength")->getValue());
        $this->assertEquals(20, $flags->get("maxLength")->getValue());
    }

    public function testGetReturnsValuesAsPairs(): void
    {
        $flags = ValidationFlags::username(5, 15);
        
        $minLength = $flags->get("minLength");
        $this->assertInstanceOf(Pair::class, $minLength);
        $this->assertEquals("minLength", $minLength->getKey());
        $this->assertEquals(5, $minLength->getValue());
    }

    public function testGetReturnsDefaultZeroPairForMissingKeys(): void
    {
        $flags = ValidationFlags::username(5, 15);
        
        $missing = $flags->get("nonExistent");
        $this->assertInstanceOf(Pair::class, $missing);
        $this->assertEquals("nonExistent", $missing->getKey());
        $this->assertEquals(0, $missing->getValue());
    }
}
