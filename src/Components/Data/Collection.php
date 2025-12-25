<?php

namespace FastRaven\Components\Data;


class Collection {
    #----------------------------------------------------------------------
    #\ VARIABLES

    protected array $data = [];
        public function getRawData(): array { return $this->data; }
        
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Collection instance.
     *
     * @param Item[] $data The list of Item objects to store in the Collection.
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
                $this->data[$item->getKey()] = $item->getValue();
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
     *
     * @return Collection The updated Collection.
     */
    public function merge(Collection $collection): Collection {
        foreach($collection->getRawData() as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }
    
    /**
     * Retrieves an Item from the Collection by its key.
     *
     * @param string $key The key of the Item to retrieve.
     *
     * @return Item|null The Item with the given key, or null if not found.
     */
    public function get(string $key): ?Item {
        if (isset($this->data[$key])) {
            return Item::new($key, $this->data[$key]);
        }
        return null;
    }

    /**
     * Returns an array of all keys in the Collection.
     *
     * @return array The list of keys in the Collection.
     */
    public function getAllKeys(): array {
        return array_keys($this->data);
    }

    /**
     * Returns an array of all values in the Collection.
     *
     * @return array The list of values in the Collection.
     */
    public function getAllValues(): array {
        return array_values($this->data);
    }

    /**
     * Adds an Item to the Collection.
     *
     * @param Item $pair The Item to add to the Collection.
     *
     * @return Collection The updated Collection.
     */
    public function add(Item $pair): Collection {
        $this->data[$pair->getKey()] = $pair->getValue();
        return $this;
    }

    /**
     * Sets an Item in the Collection by its key.
     *
     * If the key already exists, this will overwrite the existing value.
     *
     * @param string $key The key of the Item to set.
     * @param Item $pair The Item to set in the Collection.
     *
     * @return Collection The updated Collection.
     */
    public function set(string $key, Item $pair): Collection {
        $this->data[$pair->getKey()] = $pair->getValue();
        return $this;
    }

    /**
     * Removes an Item from the Collection by its key.
     *
     * @param string $key The key of the Item to remove.
     *
     * @return Collection The updated Collection.
     */
    public function remove(string $key): Collection {
        unset($this->data[$key]);
        return $this;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}