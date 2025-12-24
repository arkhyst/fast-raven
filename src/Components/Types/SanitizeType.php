<?php

namespace FastRaven\Components\Types;

/**
 * Sanitization levels for request data. Levels cascade: higher levels include all previous transformations.
 * 
 * Hierarchy:
 *   RAW ───────────────────────────── No changes
 *     └─ SAFE ────────────────────── Strips null bytes + PHP tags
 *           ├─ ENCODED ───────────── + htmlspecialchars (non-destructive)
 *           └─ SANITIZED ─────────── + strip_tags (destructive)
 *                 └─ ONLY_ALPHA ──── + alphanumeric/spaces only
 */
enum SanitizeType: int {
    case RAW = 0;
    case SAFE = 1;
    case ENCODED = 2;
    case SANITIZED = 3;
    case ONLY_ALPHA = 4;
}