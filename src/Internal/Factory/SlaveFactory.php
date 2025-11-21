<?php

namespace SmartGoblin\Internal\Factory;

use SmartGoblin\Components\Core\Config;

abstract class SlaveFactory {
    public static function call(): static {
        return new static();
    }

    abstract public function order(Config $config): void;
    abstract public function work(): void;
}