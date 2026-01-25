<?php

namespace FastRaven\Tests\Components\Data;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Data\Pair;

class PairTest extends TestCase
{
    public function testNewCreatesPair(): void
    {
        $pair = Pair::new("key", "value");

        $this->assertEquals("key", $pair->getKey());
        $this->assertEquals("value", $pair->getValue());
    }

    public function testNewHandlesDifferentValueTypes(): void
    {
        $intPair = Pair::new("int", 123);
        $this->assertEquals(123, $intPair->getValue());

        $floatPair = Pair::new("float", 12.34);
        $this->assertEquals(12.34, $floatPair->getValue());

        $boolPair = Pair::new("bool", true);
        $this->assertTrue($boolPair->getValue());
    }

    public function testMailFactory(): void
    {
        $pair = Pair::mail("John Doe", "john@example.com");

        $this->assertEquals("John Doe", $pair->getKey());
        $this->assertEquals("john@example.com", $pair->getValue());
    }

    public function testFileFactory(): void
    {
        $pair = Pair::file("avatar", "/path/to/avatar.png");

        $this->assertEquals("avatar", $pair->getKey());
        $this->assertEquals("/path/to/avatar.png", $pair->getValue());
    }

    public function testToArray(): void
    {
        $pair = Pair::new("key", "value");
        $array = $pair->__toArray();

        $this->assertIsArray($array);
        $this->assertCount(2, $array);
        $this->assertEquals("key", $array[0]);
        $this->assertEquals("value", $array[1]);
    }
}
