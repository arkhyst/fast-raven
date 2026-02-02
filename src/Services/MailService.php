<?php

namespace FastRaven\Services;

use FastRaven\Components\Core\Mail;
use FastRaven\Internals\Engines\MailEngine;

final class MailService {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $ready = false;
    private static MailEngine $engine;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(MailEngine &$engine): void {
        if(!self::$ready) {
            self::$ready = true;
            self::$engine = $engine;
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
     * Sends an email using the provided Mail configuration.
     *
     * This function delegates the email sending operation to the MailEngine instance.
     * The MailEngine must be properly initialized before calling this function.
     *
     * @param Mail $mail The Mail instance containing email configuration (sender, recipient, subject, body template, etc.).
     *
     * @return bool True if the email was sent successfully, false otherwise.
     */
    public static function send(Mail $mail, bool $fireAndForget = false): bool {
        if(self::$ready) {
            return $fireAndForget ? self::$engine->fireAndForget($mail) : self::$engine->send($mail);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}