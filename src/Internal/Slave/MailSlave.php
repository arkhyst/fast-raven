<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Components\Core\Mail;
use FastRaven\Workers\MailWorker;
use FastRaven\Workers\LogWorker;

use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;

use FastRaven\Workers\Bee;
use PHPMailer\PHPMailer\PHPMailer;

final class MailSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Initializes the MailSlave if it is not already busy.
     * 
     * This function will create a new MailSlave if it is not already busy.
     * It will then call MailWorker::__getToWork() and pass the new MailSlave object.
     * The new MailSlave object will be returned.
     * 
     * @return ?MailSlave The MailSlave object if it was successfully created, null otherwise.
     */
    public static function zap(): ?MailSlave {
        if(!self::$busy) {
            self::$busy = true;
            $inst = new MailSlave();
            MailWorker::__getToWork($inst);

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

    /**
     * Retrieves the email template content from a file in the views directory.
     *
     * @param string $file The path to the template file (relative to src/views/).
     *
     * @return ?string The template content if the file exists, null otherwise.
     */
    private function getMailTemplate(string $file): ?string {
        $path = SITE_PATH . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . Bee::normalizePath($file);
        if(file_exists($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    /**
     * Configures the basic SMTP settings for the PHPMailer instance.
     *
     * @param PHPMailer $mailer The PHPMailer instance to configure.
     * @param int $timeout The timeout in milliseconds for the SMTP connection (default: 3000).
     */
    private function setMailerBasic(PHPMailer &$mailer, int $timeout = 3000): void {
        $mailer->isSMTP();
        $mailer->SMTPAuth = true;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Host = Bee::env("SMTP_HOST", "smtp.notvalid.com");
        $mailer->Username = Bee::env("SMTP_USER", "notvalid");
        $mailer->Password = Bee::env("SMTP_PASS", "notvalid");
        $mailer->Port = Bee::env("SMTP_PORT", 587);
        $mailer->Timeout = $timeout;
    }

    /**
     * Sets the sender, recipient, and BCC addresses for the PHPMailer instance.
     *
     * @param PHPMailer $mailer The PHPMailer instance to configure.
     * @param Item $origin The sender's email information (key: name, value: email address).
     * @param Item $destination The recipient's email information (key: name, value: email address).
     * @param ?Collection $bccMails Optional collection of BCC email addresses (key: name, value: email address).
     */
    private function setMailerAddress(PHPMailer &$mailer, Item $origin, Item $destination, ?Collection $bccMails): void {
        $mailer->setFrom($origin->getValue(), $origin->getKey());
        $mailer->addAddress($destination->getValue(), $destination->getKey());

        if($bccMails) {
            foreach($bccMails->getRawData() as $bcc) {
                $mailer->addBCC($bcc->getValue(), $bcc->getKey());
            }
        }
    }

    /**
     * Sets the email subject and body for the PHPMailer instance with optional placeholder replacements.
     *
     * @param PHPMailer $mailer The PHPMailer instance to configure.
     * @param string $template The HTML template content.
     * @param string $subject The email subject line.
     * @param ?Collection $replaceValues Optional collection of placeholder replacements (key: placeholder, value: replacement).
     */
    private function setMailerBody(PHPMailer &$mailer, string $template, string $subject, ?Collection $replaceValues): void {
        $mailer->isHTML(true);
        $mailer->Subject = $subject;

        if($replaceValues) {
            $template = str_replace($replaceValues->getAllKeys(), $replaceValues->getAllValues(), $template);
        }
        
        $mailer->Body = $template;
    }

    /**
     * Adds attachments to the PHPMailer instance from the assets directory.
     *
     * @param PHPMailer $mailer The PHPMailer instance to configure.
     * @param ?Collection $attachments Optional collection of attachments (key: display name, value: file path relative to src/assets/).
     */
    private function setMailerAttachments(PHPMailer &$mailer, ?Collection $attachments): void {
        if($attachments) {
            foreach($attachments->getRawData() as $attachment) {
                $mailer->addAttachment(SITE_PATH . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . Bee::normalizePath($attachment->getValue()), $attachment->getKey());
            }
        }
    }

    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Sends an email using the provided Mail configuration.
     *
     * This function retrieves the email template, configures PHPMailer with the Mail settings,
     * and attempts to send the email. Any errors are logged via LogWorker.
     *
     * @param Mail $mail The Mail instance containing email configuration.
     *
     * @return bool True if the email was sent successfully, false otherwise.
     */
    public function sendMail(Mail $mail): bool {
        $template = $this->getMailTemplate($mail->getBodyFile());
        if(!$template) return false;
        
        $mailer = new PHPMailer(true);

        try {

            $this->setMailerBasic($mailer, $mail->getTimeout());
            $this->setMailerAddress($mailer, $mail->getOrigin(), $mail->getDestination(), $mail->getBccMails());
            $this->setMailerBody($mailer, $template, $mail->getSubject(), $mail->getReplaceValues());
            $this->setMailerAttachments($mailer, $mail->getAttachments());

            $res = $mailer->send();

            if(!$res) LogWorker::error("PHPMailer Error: " . $mailer->ErrorInfo);
            return $res;

        } catch (\Exception $e) {
            LogWorker::error("PHPMailer Exception: " . $e->getMessage());
            return false;
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}