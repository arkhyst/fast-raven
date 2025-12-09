<?php

namespace FastRaven\Components\Data;


class Collection {
    #----------------------------------------------------------------------
    #\ VARIABLES

    protected array $data;
        public function getRawData(): array { return $this->data; }
        public function add(Item $pair): void { $this->data[] = $pair; }
        
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Collection instance.
     *
     * @param array $data The list of Item objects to store in the Collection.
     *
     * @return Collection
     */
    public static function new(array $data = []): Collection {
        return new Collection($data);
    }

    protected function  __construct(array $data = []) {
        $this->data = [];
        foreach($data as $item) {
            if($item instanceof Item) {
                $this->data[] = $item;
            }
        }
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Merges the given Collection into this instance.
     *
     * This will overwrite any existing values with the values from the given Collection.
     *
     * @param Collection $collection The Collection to merge into this instance.
     */
    public function merge(Collection $collection): void {
        foreach($collection->getRawData() as $item) {
            $this->data[] = $item;
        }
    }
    
    /**
     * Retrieves an Item from the Collection by its key.
     *
     * @param string $key The key of the Item to retrieve.
     *
     * @return Item|null The Item with the given key, or null if not found.
     */
    public function get(string $key): ?Item {
        foreach($this->data as $item) {
            if($item->getKey() === $key) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Returns an array of all keys in the Collection.
     *
     * @return array The list of keys in the Collection.
     */
    public function getAllKeys(): array {
        $keys = [];
        foreach($this->data as $item) {
            $keys[] = $item->getKey();
        }
        return $keys;
    }

    /**
     * Returns an array of all values in the Collection.
     *
     * @return array The list of values in the Collection.
     */
    public function getAllValues(): array {
        $values = [];
        foreach($this->data as $item) {
            $values[] = $item->getValue();
        }
        return $values;
    }

    /**
     * Sets an Item in the Collection by its key.
     *
     * If the key already exists, this will overwrite the existing value.
     *
     * @param string $key The key of the Item to set.
     * @param Item $pair The Item to set in the Collection.
     */
    public function set(string $key, Item $pair): void {
        foreach($this->data as $index => $item) {
            if($item->getKey() === $key) {
                $this->data[$index] = $pair;
            }
        }
    }

    /**
     * Removes an Item from the Collection by its key.
     *
     * @param string $key The key of the Item to remove.
     */
    public function remove(string $key): void {
        foreach($this->data as $index => $item) {
            if($item->getKey() === $key) {
                unset($this->data[$index]);
            }
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}