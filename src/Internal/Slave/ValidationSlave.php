<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Workers\ValidationWorker;

use FastRaven\Components\Data\ValidationFlags;

final class ValidationSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes the ValidationSlave if it is not already busy.
     * 
     * This function will create a new ValidationSlave if it is not already busy.
     * It will then call ValidationWorker::__getToWork() and pass the new ValidationSlave object.
     * The new ValidationSlave object will be returned.
     * 
     * @return ?ValidationSlave The ValidationSlave object if it was successfully created, null otherwise.
     */
    public static function zap(): ?ValidationSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new ValidationSlave();
            ValidationWorker::__getToWork($inst);

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
    public function validatePassword(string $password, ValidationFlags $flags): bool {
        $length = strlen($password);
        $hasNumber = preg_match_all('/[0-9]/', $password);
        $hasSpecial = preg_match_all('/[^a-zA-Z0-9]/', $password);
        $hasLowercase = preg_match_all('/[a-z]/', $password);
        $hasUppercase = preg_match_all('/[A-Z]/', $password);

        return $length >= $flags->get("minLength")->getValue() &&
        $length <= $flags->get("maxLength")->getValue() &&
        $hasNumber >= $flags->get("minNumber")->getValue() &&
        $hasSpecial >= $flags->get("minSpecial")->getValue() &&
        $hasLowercase >= $flags->get("minLowercase")->getValue() &&
        $hasUppercase >= $flags->get("minUppercase")->getValue();
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
    public function validateAge(int $age, ValidationFlags $flags): bool {
        return $age >= $flags->get("minAge")->getValue() && 
        $age <= $flags->get("maxAge")->getValue();
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
    public function validateUsername(string $username, ValidationFlags $flags): bool {
        $length = strlen($username);

        return $length >= $flags->get("minLength")->getValue() &&
        $length <= $flags->get("maxLength")->getValue();
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
    public function validatePhone(int $countryCode, string $phone): bool {
        $length = strlen($phone);

        return $length >= 10 && $length <= 15 && $countryCode >= 1 && $countryCode <= 999;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}