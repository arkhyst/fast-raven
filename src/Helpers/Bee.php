<?php

namespace SmartGoblin\Helpers;

class Bee {
    public static function isDev() { return getenv("STATE") === "dev"; }

}