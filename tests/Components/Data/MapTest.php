<?php

namespace FastRaven\Tests\Components\Data;


use PHPUnit\Framework\TestCase;
use FastRaven\Components\Data\Map;

class MapTest extends TestCase
{
    // =====================================================================
    // Basic Operations
    // =====================================================================

    public function testNewCreatesEmptyMap(): void
    {
        $map = Map::new();

        $this->assertEmpty($map->getAllKeys());
        $this->assertEmpty($map->getAllValues());
    }

    public function testNewWithItemsPopulatesMap(): void
    {
        $map = Map::new([
            "key1" => "value1",
            "key2" => "value2",
        ]);

        $this->assertCount(2, $map->getAllKeys());
        $this->assertEquals(["key1", "key2"], $map->getAllKeys());
    }

    public function testAddReturnsMapForChaining(): void
    {
        $map = Map::new();
        
        $result = $map->add("key", "value");

        $this->assertInstanceOf(Map::class, $result);
        $this->assertSame($map, $result);
    }

    public function testAddChaining(): void
    {
        $map = Map::new()
            ->add("key1", "value1")
            ->add("key2", "value2")
            ->add("key3", "value3");

        $this->assertCount(3, $map->getAllKeys());
    }

    // =====================================================================
    // Get Operations (O(1) lookup)
    // =====================================================================

    public function testGetReturnsValueForExistingKey(): void
    {
        $map = Map::new([
            "username" => "john_doe",
        ]);

        $result = $map->get("username");

        $this->assertEquals("john_doe", $result);
    }

    public function testGetReturnsNullForNonExistentKey(): void
    {
        $map = Map::new([
            "key1" => "value1",
        ]);

        $result = $map->get("nonexistent");

        $this->assertNull($result);
    }

    public function testGetIsO1Lookup(): void
    {
        // Create a large map
        $map = Map::new();
        for ($i = 0; $i < 1000; $i++) {
            $map->add("key$i", "value$i");
        }

        // Get first, middle, and last keys - all should be fast
        $start = microtime(true);
        $map->get("key0");
        $map->get("key500");
        $map->get("key999");
        $duration = microtime(true) - $start;

        // Should complete in less than 1ms for O(1) operations
        $this->assertLessThan(0.001, $duration);
    }

    // =====================================================================
    // Set Operations
    // =====================================================================

    public function testSetUpdatesExistingKey(): void
    {
        $map = Map::new([
            "key" => "original",
        ]);

        $map->set("key", "updated");

        $this->assertEquals("updated", $map->get("key"));
    }

    public function testSetAddsNewKeyIfNotExists(): void
    {
        $map = Map::new();

        $map->set("newkey", "newvalue");

        $this->assertEquals("newvalue", $map->get("newkey"));
    }

    // =====================================================================
    // Remove Operations (O(1))
    // =====================================================================

    public function testRemoveDeletesKey(): void
    {
        $map = Map::new([
            "key1" => "value1",
            "key2" => "value2",
        ]);

        $map->remove("key1");

        $this->assertNull($map->get("key1"));
        $this->assertNotNull($map->get("key2"));
    }

    public function testRemoveNonExistentKeyDoesNothing(): void
    {
        $map = Map::new([
            "key" => "value",
        ]);

        $map->remove("nonexistent");

        $this->assertCount(1, $map->getAllKeys());
    }

    // =====================================================================
    // Utility Methods
    // =====================================================================

    public function testGetAllKeysReturnsAllKeys(): void
    {
        $map = Map::new([
            "a" => 1,
            "b" => 2,
            "c" => 3,
        ]);

        $keys = $map->getAllKeys();

        $this->assertEquals(["a", "b", "c"], $keys);
    }

    public function testGetAllValuesReturnsAllValues(): void
    {
        $map = Map::new([
            "a" => 1,
            "b" => 2,
            "c" => 3,
        ]);

        $values = $map->getAllValues();

        $this->assertEquals([1, 2, 3], $values);
    }

    public function testGetRawDataReturnsInternalArray(): void
    {
        $map = Map::new([
            "key1" => "value1",
            "key2" => "value2",
        ]);

        $raw = $map->getRawData();

        $this->assertIsArray($raw);
        $this->assertArrayHasKey("key1", $raw);
        $this->assertArrayHasKey("key2", $raw);
        $this->assertEquals("value1", $raw["key1"]);
    }

    // =====================================================================
    // Merge Operations
    // =====================================================================

    public function testMergeCombinesMaps(): void
    {
        $map1 = Map::new([
            "a" => 1,
            "b" => 2,
        ]);

        $map2 = Map::new([
            "c" => 3,
            "d" => 4,
        ]);

        $map1->merge($map2);

        $this->assertCount(4, $map1->getAllKeys());
        $this->assertEquals(3, $map1->get("c"));
    }

    public function testMergeOverwritesDuplicateKeys(): void
    {
        $map1 = Map::new([
            "key" => "original",
        ]);

        $map2 = Map::new([
            "key" => "updated",
        ]);

        $map1->merge($map2);

        $this->assertEquals("updated", $map1->get("key"));
    }

    // =====================================================================
    // Edge Cases
    // =====================================================================

    public function testHandlesNumericValues(): void
    {
        $map = Map::new([
            "int" => 42,
            "float" => 3.14,
            "zero" => 0,
        ]);

        $this->assertEquals(42, $map->get("int"));
        $this->assertEquals(3.14, $map->get("float"));
        $this->assertEquals(0, $map->get("zero"));
    }

    public function testHandlesBooleanValues(): void
    {
        $map = Map::new([
            "true" => true,
            "false" => false,
        ]);

        $this->assertTrue($map->get("true"));
        $this->assertFalse($map->get("false"));
    }

    public function testHandlesStringWithSpecialCharacters(): void
    {
        $map = Map::new([
            "special" => "!@#$%^&*()",
        ]);

        $this->assertEquals("!@#$%^&*()", $map->get("special"));
    }
}
