<?php

namespace FastRaven\Internals\Engines;

use FastRaven\Services\ValidationService;
use FastRaven\Types\ValidationType;

final class ValidationEngine {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $ready = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes the ValidationEngine if it is not already busy.
     * 
     * This function will create a new ValidationEngine if it is not already busy.
     * It will then call ValidationService::__getToWork() and pass the new ValidationEngine object.
     * The new ValidationEngine object will be returned.
     * 
     * @return ?ValidationEngine The ValidationEngine object if it was successfully created, null otherwise.
     */
    public static function zap(): ?ValidationEngine {
        if(!self::$ready) {
            self::$ready = true;
            $inst = new ValidationEngine();
            ValidationService::__getToWork($inst);

            return $inst;
        }
        
        return null;
    }

    private function __construct() {

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
     * Validates a string according to the given flags.
     * 
     * This function will validate a string based on the passed flags.
     * 
     * @param string $text The string to validate.
     * @param array<ValidationType, int> $flags The flags to use for validation.
     * @param array<ValidationType, bool>|null $result Pointer to an array that will be filled with the result of the validation.
     * 
     * @return bool True if the string is valid, false otherwise.
     */
    public function validateString(string $text, array $flags, ?array &$result = null): bool {
        $length = mb_strlen($text);
        $numbers = preg_match_all('/[0-9]/', $text);
        $specials = preg_match_all('/[^a-zA-Z0-9]/', $text);
        $lowercases = preg_match_all('/[a-z]/', $text);
        $uppercases = preg_match_all('/[A-Z]/', $text);

        if($result === null) $result = [];
        $result = [
            ValidationType::MIN_LENGTH->value => $length >= ($flags[ValidationType::MIN_LENGTH->value] ?? 0),
            ValidationType::MAX_LENGTH->value => $length <= ($flags[ValidationType::MAX_LENGTH->value] ?? PHP_FLOAT_MAX),
            ValidationType::MIN_DIGITS->value => $numbers >= ($flags[ValidationType::MIN_DIGITS->value] ?? 0),
            ValidationType::MAX_DIGITS->value => $numbers <= ($flags[ValidationType::MAX_DIGITS->value] ?? PHP_FLOAT_MAX),
            ValidationType::MIN_SPECIAL->value => $specials >= ($flags[ValidationType::MIN_SPECIAL->value] ?? 0),
            ValidationType::MAX_SPECIAL->value => $specials <= ($flags[ValidationType::MAX_SPECIAL->value] ?? PHP_FLOAT_MAX),
            ValidationType::MIN_LOWERCASE->value => $lowercases >= ($flags[ValidationType::MIN_LOWERCASE->value] ?? 0),
            ValidationType::MAX_LOWERCASE->value => $lowercases <= ($flags[ValidationType::MAX_LOWERCASE->value] ?? PHP_FLOAT_MAX),
            ValidationType::MIN_UPPERCASE->value => $uppercases >= ($flags[ValidationType::MIN_UPPERCASE->value] ?? 0),
            ValidationType::MAX_UPPERCASE->value => $uppercases <= ($flags[ValidationType::MAX_UPPERCASE->value] ?? PHP_FLOAT_MAX),
        ];

        return !\in_array(false, $result);
    }

    /**
     * Validates a number according to the given flags.
     * 
     * This function will validate a number based on the following criteria:
     * 
     * @param int|float $number The number to validate.
     * @param array<ValidationType, int> $flags The flags to use for validation.
     * @param array<ValidationType, bool>|null $result Pointer to an array that will be filled with the result of the validation.
     * 
     * @return bool True if the number is valid, false otherwise.
     */
    public function validateNumber(int|float $number, array $flags, ?array &$result = null): bool {
        if($result === null) $result = [];
        $result = [
            ValidationType::MIN_NUMBER->value => $number >= ($flags[ValidationType::MIN_NUMBER->value] ?? -PHP_FLOAT_MAX),
            ValidationType::MAX_NUMBER->value => $number <= ($flags[ValidationType::MAX_NUMBER->value] ?? PHP_FLOAT_MAX),
        ];

        return !\in_array(false, $result);
    }

    /**
     * Validates an email address according to the Unicode standard.
     * 
     * This function will use the filter_var() function to validate the email address.
     * It will return true if the email address is valid and false otherwise.
     * 
     * @param string $email The email address to validate.
     * 
     * @return bool True if the email address is valid, false otherwise.
     */
    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) !== false;
    }

    /**
     * Validates a phone number according to the given criteria.
     * 
     * This function will validate a phone number based on the following criteria:
     * 
     * - The phone number must be at least 7 characters long.
     * - The phone number must be at most 15 characters long.
     * - The country code must be at least 1 and at most 999.
     * 
     * @param int $countryCode The country code of the phone number.
     * @param string $phone The phone number to validate.
     * 
     * @return bool True if the phone number is valid, false otherwise.
     */
    public function validatePhone(int $countryCode, string $phone): bool {
        $length = mb_strlen($phone);

        return $length >= 7 && $length <= 15 && $countryCode >= 1 && $countryCode <= 999;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}