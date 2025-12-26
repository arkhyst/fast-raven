<?php

namespace FastRaven\Tests\Components\Data;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;

class CollectionTest extends TestCase
{
    // =====================================================================
    // Basic Operations
    // =====================================================================

    public function testNewCreatesEmptyCollection(): void
    {
        $collection = Collection::new();

        $this->assertEmpty($collection->getAllKeys());
        $this->assertEmpty($collection->getAllValues());
    }

    public function testNewWithItemsPopulatesCollection(): void
    {
        $collection = Collection::new([
            Item::new("key1", "value1"),
            Item::new("key2", "value2"),
        ]);

        $this->assertCount(2, $collection->getAllKeys());
        $this->assertEquals(["key1", "key2"], $collection->getAllKeys());
    }

    public function testAddReturnsCollectionForChaining(): void
    {
        $collection = Collection::new();
        
        $result = $collection->add(Item::new("key", "value"));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($collection, $result);
    }

    public function testAddChaining(): void
    {
        $collection = Collection::new()
            ->add(Item::new("key1", "value1"))
            ->add(Item::new("key2", "value2"))
            ->add(Item::new("key3", "value3"));

        $this->assertCount(3, $collection->getAllKeys());
    }

    // =====================================================================
    // Get Operations (O(1) lookup)
    // =====================================================================

    public function testGetReturnsItemForExistingKey(): void
    {
        $collection = Collection::new([
            Item::new("username", "john_doe"),
        ]);

        $result = $collection->get("username");

        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals("username", $result->getKey());
        $this->assertEquals("john_doe", $result->getValue());
    }

    public function testGetReturnsNullForNonExistentKey(): void
    {
        $collection = Collection::new([
            Item::new("key1", "value1"),
        ]);

        $result = $collection->get("nonexistent");

        $this->assertNull($result);
    }

    public function testGetIsO1Lookup(): void
    {
        // Create a large collection
        $collection = Collection::new();
        for ($i = 0; $i < 1000; $i++) {
            $collection->add(Item::new("key$i", "value$i"));
        }

        // Get first, middle, and last keys - all should be fast
        $start = microtime(true);
        $collection->get("key0");
        $collection->get("key500");
        $collection->get("key999");
        $duration = microtime(true) - $start;

        // Should complete in less than 1ms for O(1) operations
        $this->assertLessThan(0.001, $duration);
    }

    // =====================================================================
    // Set Operations
    // =====================================================================

    public function testSetUpdatesExistingKey(): void
    {
        $collection = Collection::new([
            Item::new("key", "original"),
        ]);

        $collection->set("key", Item::new("key", "updated"));

        $this->assertEquals("updated", $collection->get("key")->getValue());
    }

    public function testSetAddsNewKeyIfNotExists(): void
    {
        $collection = Collection::new();

        $collection->set("newkey", Item::new("newkey", "newvalue"));

        $this->assertEquals("newvalue", $collection->get("newkey")->getValue());
    }

    // =====================================================================
    // Remove Operations (O(1))
    // =====================================================================

    public function testRemoveDeletesKey(): void
    {
        $collection = Collection::new([
            Item::new("key1", "value1"),
            Item::new("key2", "value2"),
        ]);

        $collection->remove("key1");

        $this->assertNull($collection->get("key1"));
        $this->assertNotNull($collection->get("key2"));
    }

    public function testRemoveNonExistentKeyDoesNothing(): void
    {
        $collection = Collection::new([
            Item::new("key", "value"),
        ]);

        $collection->remove("nonexistent");

        $this->assertCount(1, $collection->getAllKeys());
    }

    // =====================================================================
    // Utility Methods
    // =====================================================================

    public function testGetAllKeysReturnsAllKeys(): void
    {
        $collection = Collection::new([
            Item::new("a", 1),
            Item::new("b", 2),
            Item::new("c", 3),
        ]);

        $keys = $collection->getAllKeys();

        $this->assertEquals(["a", "b", "c"], $keys);
    }

    public function testGetAllValuesReturnsAllValues(): void
    {
        $collection = Collection::new([
            Item::new("a", 1),
            Item::new("b", 2),
            Item::new("c", 3),
        ]);

        $values = $collection->getAllValues();

        $this->assertEquals([1, 2, 3], $values);
    }

    public function testGetRawDataReturnsInternalArray(): void
    {
        $collection = Collection::new([
            Item::new("key1", "value1"),
            Item::new("key2", "value2"),
        ]);

        $raw = $collection->getRawData();

        $this->assertIsArray($raw);
        $this->assertArrayHasKey("key1", $raw);
        $this->assertArrayHasKey("key2", $raw);
        $this->assertEquals("value1", $raw["key1"]);
    }

    // =====================================================================
    // Merge Operations
    // =====================================================================

    public function testMergeCombinesCollections(): void
    {
        $collection1 = Collection::new([
            Item::new("a", 1),
            Item::new("b", 2),
        ]);

        $collection2 = Collection::new([
            Item::new("c", 3),
            Item::new("d", 4),
        ]);

        $collection1->merge($collection2);

        $this->assertCount(4, $collection1->getAllKeys());
        $this->assertEquals(3, $collection1->get("c")->getValue());
    }

    public function testMergeOverwritesDuplicateKeys(): void
    {
        $collection1 = Collection::new([
            Item::new("key", "original"),
        ]);

        $collection2 = Collection::new([
            Item::new("key", "updated"),
        ]);

        $collection1->merge($collection2);

        $this->assertEquals("updated", $collection1->get("key")->getValue());
    }

    // =====================================================================
    // Edge Cases
    // =====================================================================

    public function testHandlesNumericValues(): void
    {
        $collection = Collection::new([
            Item::new("int", 42),
            Item::new("float", 3.14),
            Item::new("zero", 0),
        ]);

        $this->assertEquals(42, $collection->get("int")->getValue());
        $this->assertEquals(3.14, $collection->get("float")->getValue());
        $this->assertEquals(0, $collection->get("zero")->getValue());
    }

    public function testHandlesBooleanValues(): void
    {
        $collection = Collection::new([
            Item::new("true", true),
            Item::new("false", false),
        ]);

        $this->assertTrue($collection->get("true")->getValue());
        $this->assertFalse($collection->get("false")->getValue());
    }

    public function testHandlesStringWithSpecialCharacters(): void
    {
        $collection = Collection::new([
            Item::new("special", "!@#$%^&*()"),
        ]);

        $this->assertEquals("!@#$%^&*()", $collection->get("special")->getValue());
    }
}
