<?php

namespace FastRaven\Types;

/**
 * Enum CacheType
 *
 * CacheType is an enum that defines the type of cache that can be used.
 */
enum CacheType: string {
    case APCU = "APCU";
    case SHARED = "SHARED";
    case FILE = "FILE";
}