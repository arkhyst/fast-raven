<?php

namespace FastRaven\Workers;

use FastRaven\Internal\Slave\ValidationSlave;
use FastRaven\Types\ValidationType;

final class ValidationWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static ValidationSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(ValidationSlave &$slave): void {
        if(!self::$busy) {
            self::$busy = true;
            self::$slave = $slave;
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
     * Validates a string according to the given flags.
     * 
     * This function will validate a string based on the passed flags.
     * 
     * @param string $text The string to validate.
     * @param array<ValidationType, int> $flags The flags to use for validation.
     * @param array|null $result Pointer to an array that will be filled with the result of the validation.
     * 
     * @return bool True if the string is valid, false otherwise.
     */
    public static function string(?string $text, array $flags, ?array &$result = null): bool {
        if(self::$busy && $text !== null) {
            return self::$slave->validateString($text, $flags, $result);
        }

        return false;
    }

    /**
     * Validates a number according to the given flags.
     * 
     * This function will validate a number based on the passed flags.
     * 
     * @param int|float $number The number to validate.
     * @param array<ValidationType, int> $flags The flags to use for validation.
     * @param array|null $result Pointer to an array that will be filled with the result of the validation.
     * 
     * @return bool True if the number is valid, false otherwise.
     */
    public static function number(int|float|null $number, array $flags, ?array &$result = null): bool {
        if(self::$busy && $number !== null) {
            return self::$slave->validateNumber($number, $flags, $result);
        }

        return false;
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
    public static function email(?string $email): bool {
        if(self::$busy && $email !== null) {
            return self::$slave->validateEmail($email);
        }

        return false;
    }

    /**
     * Validates a phone number according to the given criteria.
     * 
     * This function will validate a phone number based on the following criteria:
     * 
     * - The phone number must be at least 10 characters long.
     * - The phone number must be at most 15 characters long.
     * - The country code must be at least 1 and at most 999.
     * 
     * @param int $countryCode The country code of the phone number.
     * @param string $phone The phone number to validate.
     * 
     * @return bool True if the phone number is valid, false otherwise.
     */
    public static function phone(?int $countryCode, ?string $phone): bool {
        if(self::$busy && $countryCode !== null && $phone !== null) {
            return self::$slave->validatePhone($countryCode, $phone);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}