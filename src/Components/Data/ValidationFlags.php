<?php

namespace FastRaven\Components\Data;


final class ValidationFlags extends Collection {
    #----------------------------------------------------------------------
    #\ VARIABLES


        
    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Validation flags for email
     *
     * @param int $minLength Minimum length of the email
     * @param int $maxLength Maximum length of the email
     *
     * @return ValidationFlags
     */
    public static function email(int $minLength = 0, int $maxLength = 255): ValidationFlags {
        return new ValidationFlags([
            Item::new("minLength", $minLength),
            Item::new("maxLength", $maxLength),
        ]);
    }

    /**
     * Validation flags for password
     *
     * @param int $minLength Minimum length of the password
     * @param int $maxLength Maximum length of the password
     * @param int $minNumber Minimum number of numbers in the password
     * @param int $minSpecial Minimum number of special characters in the password
     * @param int $minLowercase Minimum number of lowercase characters in the password
     * @param int $minUppercase Minimum number of uppercase characters in the password
     *
     * @return ValidationFlags
     */
    public static function password(int $minLength = 0, int $maxLength = 255, int $minNumber = 0, int $minSpecial = 0, int $minLowercase = 0, int $minUppercase = 0): ValidationFlags {
        return new ValidationFlags([
            Item::new("minLength", $minLength),
            Item::new("maxLength", $maxLength),
            Item::new("minNumber", $minNumber),
            Item::new("minSpecial", $minSpecial),
            Item::new("minLowercase", $minLowercase),
            Item::new("minUppercase", $minUppercase),
        ]);
    }

    /**
     * Validation flags for age
     *
     * @param int $minAge Minimum age (defaults to 12)
     * @param int $maxAge Maximum age (defaults to 120)
     *
     * @return ValidationFlags
     */
    public static function age(int $minAge = 12, int $maxAge = 120): ValidationFlags {
        return new ValidationFlags([
            Item::new("minAge", $minAge),
            Item::new("maxAge", $maxAge),
        ]);
    }

    /**
     * Validation flags for username
     *
     * @param int $minLength Minimum length of the username
     * @param int $maxLength Maximum length of the username
     *
     * @return ValidationFlags
     */
    public static function username(int $minLength = 0, int $maxLength = 255): ValidationFlags {
        return new ValidationFlags([
            Item::new("minLength", $minLength),
            Item::new("maxLength", $maxLength),
        ]);
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
     * Retrieves an Item from the Collection by its key.
     *
     * @param string $key The key of the Item to retrieve.
     *
     * @return Item The Item with the given key, or a new Item with the given key and value 0 if not found.
     */
    public function get(string $key): Item {
        foreach($this->data as $item) {
            if($item->getKey() === $key) {
                return $item;
            }
        }
        return Item::new($key, 0);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}