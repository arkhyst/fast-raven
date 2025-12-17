<?php

namespace FastRaven\Tests\Components\Core;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Core\Mail;
use FastRaven\Components\Data\Item;
use FastRaven\Components\Data\Collection;

class MailTest extends TestCase
{
    private Item $origin;
    private Item $destination;

    protected function setUp(): void
    {
        parent::setUp();
        $this->origin = Item::new("Sender Name", "sender@example.com");
        $this->destination = Item::new("Recipient Name", "recipient@example.com");
    }

    public function testNewCreatesMailInstance(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $this->assertInstanceOf(Mail::class, $mail);
    }

    public function testGetOriginReturnsCorrectValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $this->assertEquals($this->origin, $mail->getOrigin());
    }

    public function testSetOriginUpdatesValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $newOrigin = Item::new("New Sender", "newsender@example.com");
        $mail->setOrigin($newOrigin);

        $this->assertEquals($newOrigin, $mail->getOrigin());
    }

    public function testGetDestinationReturnsCorrectValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $this->assertEquals($this->destination, $mail->getDestination());
    }

    public function testSetDestinationUpdatesValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $newDestination = Item::new("New Recipient", "newrecipient@example.com");
        $mail->setDestination($newDestination);

        $this->assertEquals($newDestination, $mail->getDestination());
    }

    public function testGetSubjectReturnsCorrectValue(): void
    {
        $subject = "Test Subject Line";
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            $subject,
            "test/template.html"
        );

        $this->assertEquals($subject, $mail->getSubject());
    }

    public function testSetSubjectUpdatesValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Original Subject",
            "test/template.html"
        );

        $newSubject = "Updated Subject";
        $mail->setSubject($newSubject);

        $this->assertEquals($newSubject, $mail->getSubject());
    }

    public function testGetBodyFileReturnsCorrectValue(): void
    {
        $bodyFile = "emails/welcome.html";
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            $bodyFile
        );

        $this->assertEquals($bodyFile, $mail->getBodyFile());
    }

    public function testSetBodyFileUpdatesValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "original/template.html"
        );

        $newBodyFile = "updated/template.html";
        $mail->setBodyFile($newBodyFile);

        $this->assertEquals($newBodyFile, $mail->getBodyFile());
    }

    public function testSetBccMailsUpdatesValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $bccList = Collection::new();
        $bccList->add(Item::new("BCC User 1", "bcc1@example.com"));
        $bccList->add(Item::new("BCC User 2", "bcc2@example.com"));

        $mail->setBccMails($bccList);

        $this->assertEquals($bccList, $mail->getBccMails());
        $this->assertCount(2, $mail->getBccMails()->getRawData());
    }

    public function testSetReplaceValuesUpdatesValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $replacements = Collection::new();
        $replacements->add(Item::new("{{NAME}}", "John"));
        $replacements->add(Item::new("{{LINK}}", "https://example.com"));

        $mail->setReplaceValues($replacements);

        $this->assertEquals($replacements, $mail->getReplaceValues());
        $this->assertCount(2, $mail->getReplaceValues()->getRawData());
    }

    public function testSetAttachmentsUpdatesValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $attachments = Collection::new();
        $attachments->add(Item::new("document.pdf", "files/document.pdf"));
        $attachments->add(Item::new("image.jpg", "images/image.jpg"));

        $mail->setAttachments($attachments);

        $this->assertEquals($attachments, $mail->getAttachments());
        $this->assertCount(2, $mail->getAttachments()->getRawData());
    }

    public function testGetTimeoutReturnsDefaultValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $this->assertEquals(3000, $mail->getTimeout());
    }

    public function testSetTimeoutUpdatesValue(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "Test Subject",
            "test/template.html"
        );

        $newTimeout = 5000;
        $mail->setTimeout($newTimeout);

        $this->assertEquals($newTimeout, $mail->getTimeout());
    }

    public function testMailHandlesEmptySubject(): void
    {
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            "",
            "test/template.html"
        );

        $this->assertEquals("", $mail->getSubject());
    }

    public function testMailHandlesSpecialCharactersInSubject(): void
    {
        $subject = "Test &lt;> Subject with 'special' \"characters\"";
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            $subject,
            "test/template.html"
        );

        $this->assertEquals($subject, $mail->getSubject());
    }

    public function testMailHandlesUnicodeCharactersInSubject(): void
    {
        $subject = "Test Subject 中文 العربية 🔥";
        $mail = Mail::new(
            $this->origin,
            $this->destination,
            $subject,
            "test/template.html"
        );

        $this->assertEquals($subject, $mail->getSubject());
    }
}
