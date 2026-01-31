<?php

namespace FastRaven\Workers;

use FastRaven\Components\Core\Mail;
use FastRaven\Internal\Slave\MailSlave;

final class MailWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $ready = false;
    private static MailSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(MailSlave &$slave): void {
        if(!self::$ready) {
            self::$ready = true;
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
     * Sends an email using the provided Mail configuration.
     *
     * This function delegates the email sending operation to the MailSlave instance.
     * The MailSlave must be properly initialized before calling this function.
     *
     * @param Mail $mail The Mail instance containing email configuration (sender, recipient, subject, body template, etc.).
     *
     * @return bool True if the email was sent successfully, false otherwise.
     */
    public static function send(Mail $mail, bool $fireAndForget = false): bool {
        if(self::$ready) {
            return $fireAndForget ? self::$slave->fireAndForget($mail) : self::$slave->send($mail);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}