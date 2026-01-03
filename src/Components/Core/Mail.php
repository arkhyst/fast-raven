<?php

namespace FastRaven\Components\Core;

use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;

final class Mail {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private Item $origin;
        public function getOrigin(): Item { return $this->origin; }
        public function setOrigin(Item $origin): Mail { $this->origin = $origin; return $this; }
    private Item $destination;
        public function getDestination(): Item { return $this->destination; }
        public function setDestination(Item $destination): Mail { $this->destination = $destination; return $this; }
    private Collection $bccMails;
        public function getBccMails(): Collection { return $this->bccMails; }
        public function setBccMails(Collection $bccMails): Mail { $this->bccMails = $bccMails; return $this; }
    private string $subject = "";
        public function getSubject(): string { return $this->subject; }
        public function setSubject(string $subject): Mail { $this->subject = $subject; return $this; }
    private string $bodyFile = "";
        public function getBodyFile(): string { return $this->bodyFile; }
        public function setBodyFile(string $bodyFile): Mail { $this->bodyFile = $bodyFile; return $this; }
    private Collection $replaceValues;
        public function getReplaceValues(): Collection { return $this->replaceValues; }
        public function setReplaceValues(Collection $replaceValues): Mail { $this->replaceValues = $replaceValues; return $this; }
    private Collection $attachments;
        public function getAttachments(): Collection { return $this->attachments; }
        public function setAttachments(Collection $attachments): Mail { $this->attachments = $attachments; return $this; }
    private int $timeout = 3000;
        public function getTimeout(): int { return $this->timeout; }
        public function setTimeout(int $timeout): Mail { $this->timeout = $timeout; return $this; }
    

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
        $this->origin = $origin;
        $this->destination = $destination;
        $this->subject = $subject;
        $this->bodyFile = $bodyFile;

        $this->bccMails = Collection::new();
        $this->replaceValues = Collection::new();
        $this->attachments = Collection::new();
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