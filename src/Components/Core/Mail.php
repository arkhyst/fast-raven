<?php

namespace FastRaven\Components\Core;

use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;

final class Mail {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private Item $origin;
        public function getOrigin(): Item { return $this->origin; }
        public function setOrigin(Item $origin): void { $this->origin = $origin; }
    private Item $destination;
        public function getDestination(): Item { return $this->destination; }
        public function setDestination(Item $destination): void { $this->destination = $destination; }
    private Collection $bccMails;
        public function getBccMails(): Collection { return $this->bccMails; }
        public function setBccMails(Collection $bccMails): void { $this->bccMails = $bccMails; }
    private string $subject = "";
        public function getSubject(): string { return $this->subject; }
        public function setSubject(string $subject): void { $this->subject = $subject; }
    private string $bodyFile = "";
        public function getBodyFile(): string { return $this->bodyFile; }
        public function setBodyFile(string $bodyFile): void { $this->bodyFile = $bodyFile; }
    private Collection $replaceValues;
        public function getReplaceValues(): Collection { return $this->replaceValues; }
        public function setReplaceValues(Collection $replaceValues): void { $this->replaceValues = $replaceValues; }
    private Collection $attachments;
        public function getAttachments(): Collection { return $this->attachments; }
        public function setAttachments(Collection $attachments): void { $this->attachments = $attachments; }
    private int $timeout = 3000;
        public function getTimeout(): int { return $this->timeout; }
        public function setTimeout(int $timeout): void { $this->timeout = $timeout; }
    

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Creates a new Mail instance with the specified origin, destination, subject and body template file.
     *
     * @param Item $origin The sender's email information (key: name, value: email address).
     * @param Item $destination The recipient's email information (key: name, value: email address).
     * @param string $subject The subject line of the email.
     * @param string $bodyFile The path to the HTML template file (relative to src/views/).
     *
     * @return Mail A new Mail instance configured with the provided parameters.
     */
    public static function new(Item $origin, Item $destination, string $subject, string $bodyFile): Mail {
        return new Mail($origin, $destination, $subject, $bodyFile);
    }

    private function __construct(Item $origin, Item $destination, string $subject, string $bodyFile) {
        $this->setOrigin($origin);
        $this->setDestination($destination);
        $this->setSubject($subject);
        $this->setBodyFile($bodyFile);
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS



    #/ METHODS
    #----------------------------------------------------------------------
}