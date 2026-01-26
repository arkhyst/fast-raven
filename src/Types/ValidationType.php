<?php

namespace FastRaven\Types;

/**
 * Enum ValidationType
 *
 * ValidationType is an enum that defines what validation is being done.
 */
enum ValidationType: string {
    case MIN_NUMBER = "MIN_NUMBER";
    case MAX_NUMBER = "MAX_NUMBER";
    case MIN_LENGTH = "MIN_LENGTH";
    case MAX_LENGTH = "MAX_LENGTH";
    case MIN_SPECIAL = "MIN_SPECIAL";
    case MAX_SPECIAL = "MAX_SPECIAL";
    case MIN_LOWERCASE = "MIN_LOWERCASE";
    case MAX_LOWERCASE = "MAX_LOWERCASE";
    case MIN_UPPERCASE = "MIN_UPPERCASE";
    case MAX_UPPERCASE = "MAX_UPPERCASE";
    case MIN_DIGITS = "MIN_DIGITS";
    case MAX_DIGITS = "MAX_DIGITS";
}