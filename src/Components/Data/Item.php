<?php

namespace FastRaven\Components\Data;


final class Item {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $key;
        public function getKey(): string { return $this->key; }
    private string|int|float|bool $value;
        public function getValue(): string|int|float|bool { return $this->value; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Item instance.
     *
     * @param string $key   The key of the item.
     * @param string|int|float|bool $value   The value of the item.
     *
     * @return Item
     */
    public static function new(string $key, string|int|float|bool $value): Item {
        return new Item($key, $value);
    }

    public static function mail(string $name, string $address): Item {
        return new Item($name, $address);
    }

    public static function file(string $name, string $path): Item {
        return new Item($name, $path);
    }

    private function  __construct(string $key, string|int|float|bool $value) {
        $this->key = $key;
        $this->value = $value;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public function __toArray(): array {
        return [$this->key, $this->value];
    }

    #/ METHODS
    #----------------------------------------------------------------------
}