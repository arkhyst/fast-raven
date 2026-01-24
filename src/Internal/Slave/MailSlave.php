<?php

namespace FastRaven\Internal\Slave;

use FastRaven\Workers\MailWorker;
use FastRaven\Workers\LogWorker;

use FastRaven\Components\Core\Mail;
use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;

use FastRaven\Types\ProjectFolderType;

use FastRaven\Workers\Bee;

use PHPMailer\PHPMailer\PHPMailer;

final class MailSlave {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private static bool $busy = false;

    private ?PHPMailer $mailer = null;
    private array $deferredMails = [];

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
        $path = realpath(Bee::buildProjectPath(ProjectFolderType::SRC_WEB_TEMPLATES_MAILS, $file));
        
        if($path !== false) return file_get_contents($path);
        else return null;
    }

    /**
     * Configures the basic SMTP settings for the PHPMailer instance.
     *
     * @param int $timeout The timeout in milliseconds for the SMTP connection (default: 3000).
     */
    private function setMailerBasic(int $timeout = 3000): void {
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearCustomHeaders();
        $this->mailer->clearReplyTos();

        $this->mailer->isSMTP();
        $this->mailer->SMTPAuth = true;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Host = Bee::env("SMTP_HOST", "smtp.notvalid.com");
        $this->mailer->Username = Bee::env("SMTP_USER", "notvalid");
        $this->mailer->Password = Bee::env("SMTP_PASS", "notvalid");
        $this->mailer->Port = Bee::env("SMTP_PORT", 587);
        $this->mailer->Timeout = $timeout;
    }

    /**
     * Sets the sender, recipient, and BCC addresses for the PHPMailer instance.
     *
     * @param Item $origin The sender's email information (key: name, value: email address).
     * @param Item $destination The recipient's email information (key: name, value: email address).
     * @param ?Collection $bccMails Optional collection of BCC email addresses (key: name, value: email address).
     */
    private function setMailerAddress(Item $origin, Item $destination, ?Collection $bccMails): void {
        $this->mailer->setFrom($origin->getValue(), $origin->getKey());
        $this->mailer->addAddress($destination->getValue(), $destination->getKey());

        if($bccMails) {
            foreach($bccMails->getRawData() as $bcc) {
                $this->mailer->addBCC($bcc->getValue(), $bcc->getKey());
            }
        }
    }

    /**
     * Sets the email subject and body for the PHPMailer instance with optional placeholder replacements.
     *
     * @param string $template The HTML template content.
     * @param string $subject The email subject line.
     * @param ?Collection $replaceValues Optional collection of placeholder replacements (key: placeholder, value: replacement).
     */
    private function setMailerBody(string $template, string $subject, ?Collection $replaceValues): void {
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $subject;

        if($replaceValues) {
            $template = str_replace($replaceValues->getAllKeys(), $replaceValues->getAllValues(), $template);
        }
        
        $this->mailer->Body = $template;
    }

    /**
     * Adds attachments to the PHPMailer instance from the storage/uploads directory.
     *
     * @param ?Collection $attachments Optional collection of attachments (key: display name, value: file path relative to src/assets/).
     */
    private function setMailerAttachments(?Collection $attachments): void {
        if($attachments) {
            foreach($attachments->getRawData() as $attachment) {
                $path = realpath(Bee::buildProjectPath(ProjectFolderType::STORAGE_UPLOADS, $attachment->getValue()));
                
                if($path !== false) $this->mailer->addAttachment($path, $attachment->getKey());
                else LogWorker::error("Attachment not found: " . $attachment->getValue());
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
    public function send(Mail $mail): bool {
        $template = $this->getMailTemplate($mail->getBodyFile());
        if(!$template) return false;
        
        if($this->mailer === null) $this->mailer = new PHPMailer(true);

        try {
            $this->setMailerBasic($mail->getTimeout());
            $this->setMailerAddress($mail->getOrigin(), $mail->getDestination(), $mail->getBccMails());
            $this->setMailerBody($template, $mail->getSubject(), $mail->getReplaceValues());
            $this->setMailerAttachments($mail->getAttachments());

            $res = $this->mailer->send();

            if(!$res) LogWorker::error("PHPMailer Error: " . $this->mailer->ErrorInfo);
            return $res;

        } catch (\Exception $e) {
            LogWorker::error("PHPMailer Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Queues an email to be sent after the response is sent to the client.
     * This method does NOT block the request.
     *
     * @param Mail $mail The Mail instance to queue.
     *
     * @return bool True if the email was queued successfully, false otherwise.
     */
    public function fireAndForget(Mail $mail): bool {
        $template = $this->getMailTemplate($mail->getBodyFile());
        if(!$template) return false;
        
        $this->deferredMails[] = [
            "template" => $template,
            "mail" => $mail
        ];

        return true;
    }

    /**
     * Processes all queued fire-and-forget emails.
     * 
     * This method should be called after fastcgi_finish_request() to ensure
     * that emails are sent in the background without blocking the response.
     *
     */
    public function processDeferredMails(): void {
        if(empty($this->deferredMails)) return;

        foreach($this->deferredMails as $item) {
            $mail = $item["mail"];
            $template = $item["template"];
            
            if($this->mailer === null) $this->mailer = new PHPMailer(false);

            $this->setMailerBasic($mail->getTimeout());
            $this->setMailerAddress($mail->getOrigin(), $mail->getDestination(), $mail->getBccMails());
            $this->setMailerBody($template, $mail->getSubject(), $mail->getReplaceValues());
            $this->setMailerAttachments($mail->getAttachments());

            if(!$this->mailer->send()) {
                LogWorker::error("Deferred PHPMailer Error: " . $this->mailer->ErrorInfo);
            }
        }

        $this->deferredMails = [];
    }

    #/ METHODS
    #----------------------------------------------------------------------
}