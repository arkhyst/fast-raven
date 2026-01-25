<?php

namespace FastRaven\Components\Data;


class Map {
    #----------------------------------------------------------------------
    #\ VARIABLES

    protected array $data = [];
        public function getRawData(): array { return $this->data; }
        
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Map instance.
     *
     * @param array<string, mixed> $data The list of values to store in the Map.
     *
     * @return Map
     */
    public static function new(array $data = []): Map {
        return new Map($data);
    }

    protected function  __construct(array $data = []) {
        $this->data = [];
        foreach($data as $key => $value) {
            $this->data[$key] = $value;
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
     * Merges the given Map into this instance.
     *
     * This will overwrite any existing values with the values from the given Map.
     *
     * @param Map $collection The Map to merge into this instance.
     *
     * @return Map The updated Map.
     */
    public function merge(Map $collection): Map {
        foreach($collection->getRawData() as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }
    
    public function has(string $key): bool {
        return isset($this->data[$key]);
    }

    /**
     * Retrieves a value from the Map by its key.
     *
     * @param string $key The key of the value to retrieve.
     *
     * @return mixed|null The value with the given key, or null if not found.
     */
    public function get(string $key): mixed {
        if ($this->has($key)) {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * Returns an array of all keys in the Map.
     *
     * @return array The list of keys in the Map.
     */
    public function getAllKeys(): array {
        return array_keys($this->data);
    }

    /**
     * Returns an array of all values in the Map.
     *
     * @return array The list of values in the Map.
     */
    public function getAllValues(): array {
        return array_values($this->data);
    }

    /**
     * Adds a value to the Map.
     *
     * @param string $key The key of the value to add.
     * @param mixed $value The value to add.
     *
     * @return Map The updated Map.
     */
    public function add(string $key, mixed $value): Map {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Sets a value in the Map by its key.
     *
     * If the key already exists, this will overwrite the existing value.
     *
     * @param string $key The key of the value to set.
     * @param mixed $value The value to set in the Map.
     *
     * @return Map The updated Map.
     */
    public function set(string $key, mixed $value): Map {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Removes a value from the Map by its key.
     *
     * @param string $key The key of the value to remove.
     *
     * @return Map The updated Map.
     */
    public function remove(string $key): Map {
        unset($this->data[$key]);
        return $this;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}