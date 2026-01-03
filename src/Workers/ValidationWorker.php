<?php

namespace FastRaven\Workers;

use FastRaven\Components\Data\ValidationFlags;
use FastRaven\Internal\Slave\ValidationSlave;

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
        if(self::$busy && $email) {
            return self::$slave->validateEmail($email);
        }

        return false;
    }

    /**
     * Validates a password according to the given flags.
     * 
     * This function will validate a password based on the following criteria:
     * 
     * - The password must be at least $flags->get("minLength")->getValue() characters long.
     * - The password must be at most $flags->get("maxLength")->getValue() characters long.
     * - The password must contain at least $flags->get("minNumber")->getValue() numbers.
     * - The password must contain at least $flags->get("minSpecial")->getValue() special characters.
     * - The password must contain at least $flags->get("minLowercase")->getValue() lowercase characters.
     * - The password must contain at least $flags->get("minUppercase")->getValue() uppercase characters.
     * 
     * @param string $password The password to validate.
     * @param ValidationFlags $flags The flags to use for validation.
     * 
     * @return bool True if the password is valid, false otherwise.
     */
    public static function password(?string $password, ValidationFlags $flags): bool {
        if(self::$busy && $password) {
            return self::$slave->validatePassword($password, $flags);
        }

        return false;
    }

    /**
     * Validates an age according to the given flags.
     * 
     * This function will validate an age based on the following criteria:
     * 
     * - The age must be at least $flags->get("minAge")->getValue() years old.
     * - The age must be at most $flags->get("maxAge")->getValue() years old.
     * 
     * @param int $age The age to validate.
     * @param ValidationFlags $flags The flags to use for validation.
     * 
     * @return bool True if the age is valid, false otherwise.
    */
    public static function age(?int $age, ValidationFlags $flags): bool {
        if(self::$busy && $age) {
            return self::$slave->validateAge($age, $flags);
        }

        return false;
    }

    /**
     * Validates a username according to the given flags.
     * 
     * This function will validate a username based on the following criteria:
     * 
     * - The username must be at least $flags->get("minLength")->getValue() characters long.
     * - The username must be at most $flags->get("maxLength")->getValue() characters long.
     * 
     * @param string $username The username to validate.
     * @param ValidationFlags $flags The flags to use for validation.
     * 
     * @return bool True if the username is valid, false otherwise.
     */
    public static function username(?string $username, ValidationFlags $flags): bool {
        if(self::$busy && $username) {
            return self::$slave->validateUsername($username, $flags);
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
        if(self::$busy && $countryCode && $phone) {
            return self::$slave->validatePhone($countryCode, $phone);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}