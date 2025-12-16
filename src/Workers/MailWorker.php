<?php

namespace FastRaven\Workers;

use FastRaven\Components\Core\Mail;
use FastRaven\Internal\Slave\MailSlave;

class MailWorker {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;
    private static MailSlave $slave;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function __getToWork(MailSlave &$slave): void {
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
     * Sends an email using the provided Mail configuration.
     *
     * This function delegates the email sending operation to the MailSlave instance.
     * The MailSlave must be properly initialized before calling this function.
     *
     * @param Mail $mail The Mail instance containing email configuration (sender, recipient, subject, body template, etc.).
     *
     * @return bool True if the email was sent successfully, false otherwise.
     */
    public static function sendMail(Mail $mail): bool {
        if(self::$busy) {
            return self::$slave->sendMail($mail);
        }

        return false;
    }

    #/ METHODS
    #----------------------------------------------------------------------
}