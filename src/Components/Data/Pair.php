<?php

namespace FastRaven\Components\Data;


final class Pair {
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
     * Create a new Pair instance.
     *
     * @param string $key The key of the item.
     * @param string|int|float|bool $value The value of the item.
     *
     * @return Pair
     */
    public static function new(string $key, string|int|float|bool $value): Pair {
        return new Pair($key, $value);
    }

    public static function mail(string $name, string $address): Pair {
        return new Pair($name, $address);
    }

    public static function file(string $name, string $path): Pair {
        return new Pair($name, $path);
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