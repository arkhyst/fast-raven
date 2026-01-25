<?php

namespace FastRaven\Tests\Workers;

use PHPUnit\Framework\TestCase;
use FastRaven\Workers\ValidationWorker;
use FastRaven\Internal\Slave\ValidationSlave;
use FastRaven\Types\ValidationType;

class ValidationWorkerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Initialize ValidationSlave for each test
        ValidationSlave::zap();
    }

    #----------------------------------------------------------------------
    #\ EMAIL VALIDATION TESTS

    /**
     * @dataProvider validEmailProvider
     */
    public function testEmailValidatesCorrectEmails(string $email): void
    {
        $result = ValidationWorker::email($email);

        $this->assertTrue($result);
    }

    public static function validEmailProvider(): array
    {
        return [
            'standard email' => ['user@example.com'],
            'subdomain' => ['user@mail.example.com'],
            'plus addressing' => ['user+tag@example.com'],
            'dots in local' => ['first.last@example.com'],
            'hyphen in domain' => ['user@my-domain.com'],
            'numbers' => ['user123@example456.com'],
            'single letter local' => ['a@example.com'],
            'single letter domain' => ['user@e.com'],
            'international domain' => ['user@domain.co.uk'],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function testEmailRejectsInvalidEmails(string $email): void
    {
        $result = ValidationWorker::email($email);

        $this->assertFalse($result);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'no at sign' => ['userexample.com'],
            'no domain' => ['user@'],
            'no local part' => ['@example.com'],
            'double at' => ['user@@example.com'],
            'spaces' => ['user @example.com'],
            'missing tld' => ['user@domain'],
            'only at sign' => ['@'],
            'empty' => [''],
            'just domain' => ['example.com'],
            'special chars in domain' => ['user@exam ple.com'],
        ];
    }

    public function testEmailReturnsFalseForNull(): void
    {
        $result = ValidationWorker::email(null);

        $this->assertFalse($result);
    }

    #/ EMAIL VALIDATION TESTS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ STRING VALIDATION TESTS

    #----------------------------------------------------------------------
    #\ STRING VALIDATION TESTS

    public function testStringValidatesWithAllCriteriaMet(): void
    {
        $flags = [
            ValidationType::MIN_LENGTH->value => 8, 
            ValidationType::MAX_LENGTH->value => 20, 
            ValidationType::MIN_DIGITS->value => 1, 
            ValidationType::MIN_SPECIAL->value => 1, 
            ValidationType::MIN_LOWERCASE->value => 1, 
            ValidationType::MIN_UPPERCASE->value => 1
        ];
        $text = 'Passw0rd!';

        $result = ValidationWorker::string($text, $flags);

        $this->assertTrue($result);
    }

    public function testStringRejectsTooShort(): void
    {
        $flags = [ValidationType::MIN_LENGTH->value => 8];
        $text = 'short';

        $result = ValidationWorker::string($text, $flags);

        $this->assertFalse($result);
    }

    public function testStringRejectsTooLong(): void
    {
        $flags = [ValidationType::MAX_LENGTH->value => 10];
        $text = 'thisstringistoolong';

        $result = ValidationWorker::string($text, $flags);

        $this->assertFalse($result);
    }

    public function testStringRejectsWhenMissingDigits(): void
    {
        $flags = [ValidationType::MIN_DIGITS->value => 1];
        $text = 'NoDigitsHere';

        $result = ValidationWorker::string($text, $flags);

        $this->assertFalse($result);
    }

    public function testStringRejectsWhenMissingSpecialChars(): void
    {
        $flags = [ValidationType::MIN_SPECIAL->value => 1];
        $text = 'NoSpecialChars123';

        $result = ValidationWorker::string($text, $flags);

        $this->assertFalse($result);
    }

    public function testStringRejectsWhenMissingLowercase(): void
    {
        $flags = [ValidationType::MIN_LOWERCASE->value => 1];
        $text = 'ALLUPPERCASE123!';

        $result = ValidationWorker::string($text, $flags);

        $this->assertFalse($result);
    }

    public function testStringRejectsWhenMissingUppercase(): void
    {
        $flags = [ValidationType::MIN_UPPERCASE->value => 1];
        $text = 'alllowercase123!';

        $result = ValidationWorker::string($text, $flags);

        $this->assertFalse($result);
    }

    public function testStringValidatesExactMinLength(): void
    {
        $flags = [ValidationType::MIN_LENGTH->value => 8];
        $text = 'exactly8';

        $result = ValidationWorker::string($text, $flags);

        $this->assertTrue($result);
    }

    public function testStringValidatesExactMaxLength(): void
    {
        $flags = [ValidationType::MAX_LENGTH->value => 10];
        $text = '1234567890';

        $result = ValidationWorker::string($text, $flags);

        $this->assertTrue($result);
    }

    public function testStringReturnsFalseForNull(): void
    {
        $flags = [];

        $result = ValidationWorker::string(null, $flags);

        $this->assertFalse($result);
    }

    public function testStringHandlesUnicodeCharacters(): void
    {
        $flags = [];
        $text = 'пароль密码🔒';

        $result = ValidationWorker::string($text, $flags);

        $this->assertTrue($result);
    }

    public function testStringValidatesMultipleDigits(): void
    {
        $flags = [ValidationType::MIN_DIGITS->value => 3];
        $text = 'text123';

        $result = ValidationWorker::string($text, $flags);

        $this->assertTrue($result);
    }

    public function testStringRejectsInsufficientDigits(): void
    {
        $flags = [ValidationType::MIN_DIGITS->value => 3];
        $text = 'text12';

        $result = ValidationWorker::string($text, $flags);

        $this->assertFalse($result);
    }

    public function testStringPopulatesResultPointer(): void
    {
        $flags = [
            ValidationType::MIN_LENGTH->value => 5,
            ValidationType::MIN_DIGITS->value => 2
        ];
        $text = 'abc1'; // Length 4 (fail), Digits 1 (fail)
        $details = [];

        $result = ValidationWorker::string($text, $flags, $details);

        $this->assertFalse($result);
        $this->assertArrayHasKey(ValidationType::MIN_LENGTH->value, $details);
        $this->assertArrayHasKey(ValidationType::MIN_DIGITS->value, $details);
        $this->assertFalse($details[ValidationType::MIN_LENGTH->value]);
        $this->assertFalse($details[ValidationType::MIN_DIGITS->value]);
        
        // Test mixed success
        $text = 'abc12'; // Length 5 (pass), Digits 2 (pass)
        $result = ValidationWorker::string($text, $flags, $details);
        $this->assertTrue($result);
        $this->assertTrue($details[ValidationType::MIN_LENGTH->value]);
        $this->assertTrue($details[ValidationType::MIN_DIGITS->value]);
    }

    #/ STRING VALIDATION TESTS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ NUMBER VALIDATION TESTS

    #----------------------------------------------------------------------
    #\ NUMBER VALIDATION TESTS

    public function testNumberValidatesWithinRange(): void
    {
        $flags = [
            ValidationType::MIN_NUMBER->value => 18, 
            ValidationType::MAX_NUMBER->value => 65
        ];
        $number = 30;

        $result = ValidationWorker::number($number, $flags);

        $this->assertTrue($result);
    }

    public function testNumberRejectsTooSmall(): void
    {
        $flags = [
            ValidationType::MIN_NUMBER->value => 18, 
            ValidationType::MAX_NUMBER->value => 65
        ];
        $number = 17;

        $result = ValidationWorker::number($number, $flags);

        $this->assertFalse($result);
    }

    public function testNumberRejectsTooLarge(): void
    {
        $flags = [
            ValidationType::MIN_NUMBER->value => 18, 
            ValidationType::MAX_NUMBER->value => 65
        ];
        $number = 66;

        $result = ValidationWorker::number($number, $flags);

        $this->assertFalse($result);
    }

    public function testNumberValidatesExactMin(): void
    {
        $flags = [
            ValidationType::MIN_NUMBER->value => 18, 
            ValidationType::MAX_NUMBER->value => 65
        ];
        $number = 18;

        $result = ValidationWorker::number($number, $flags);

        $this->assertTrue($result);
    }

    public function testNumberValidatesExactMax(): void
    {
        $flags = [
            ValidationType::MIN_NUMBER->value => 18, 
            ValidationType::MAX_NUMBER->value => 65
        ];
        $number = 65;

        $result = ValidationWorker::number($number, $flags);

        $this->assertTrue($result);
    }

    public function testNumberReturnsFalseForNull(): void
    {
        $flags = [
            ValidationType::MIN_NUMBER->value => 18, 
            ValidationType::MAX_NUMBER->value => 65
        ];

        $result = ValidationWorker::number(null, $flags);

        $this->assertFalse($result);
    }

    public function testNumberReturnsFalseForZeroIfOutOfRange(): void
    {
        $flags = [
            ValidationType::MIN_NUMBER->value => 1, 
            ValidationType::MAX_NUMBER->value => 100
        ];

        $result = ValidationWorker::number(0, $flags);

        $this->assertFalse($result);
    }

    public function testNumberValidatesFloat(): void
    {
        $flags = [
            ValidationType::MIN_NUMBER->value => 10.5, 
            ValidationType::MAX_NUMBER->value => 20.5
        ];
        $number = 15.5;

        $result = ValidationWorker::number($number, $flags);

        $this->assertTrue($result);
    }

    #/ NUMBER VALIDATION TESTS
    #----------------------------------------------------------------------



    #----------------------------------------------------------------------
    #\ PHONE VALIDATION TESTS

    public function testPhoneValidatesCorrectPhoneNumber(): void
    {
        $countryCode = 1;
        $phone = '5551234567';

        $result = ValidationWorker::phone($countryCode, $phone);

        $this->assertTrue($result);
    }

    public function testPhoneRejectsTooShortNumber(): void
    {
        $countryCode = 1;
        $phone = '123456';  // 6 digits, min is 7

        $result = ValidationWorker::phone($countryCode, $phone);

        $this->assertFalse($result);
    }

    public function testPhoneRejectsTooLongNumber(): void
    {
        $countryCode = 1;
        $phone = '1234567890123456';

        $result = ValidationWorker::phone($countryCode, $phone);

        $this->assertFalse($result);
    }

    public function testPhoneValidatesExactMinLength(): void
    {
        $countryCode = 1;
        $phone = '1234567890';

        $result = ValidationWorker::phone($countryCode, $phone);

        $this->assertTrue($result);
    }

    public function testPhoneValidatesExactMaxLength(): void
    {
        $countryCode = 1;
        $phone = '123456789012345';

        $result = ValidationWorker::phone($countryCode, $phone);

        $this->assertTrue($result);
    }

    public function testPhoneRejectsInvalidCountryCode(): void
    {
        $countryCode = 0;
        $phone = '5551234567';

        $result = ValidationWorker::phone($countryCode, $phone);

        $this->assertFalse($result);
    }

    public function testPhoneRejectsCountryCodeAbove999(): void
    {
        $countryCode = 1000;
        $phone = '5551234567';

        $result = ValidationWorker::phone($countryCode, $phone);

        $this->assertFalse($result);
    }

    public function testPhoneValidatesCountryCode999(): void
    {
        $countryCode = 999;
        $phone = '5551234567';

        $result = ValidationWorker::phone($countryCode, $phone);

        $this->assertTrue($result);
    }

    public function testPhoneReturnsFalseForNullPhone(): void
    {
        $countryCode = 1;

        $result = ValidationWorker::phone($countryCode, null);

        $this->assertFalse($result);
    }

    public function testPhoneReturnsFalseForNullCountryCode(): void
    {
        $phone = '5551234567';

        $result = ValidationWorker::phone(null, $phone);

        $this->assertFalse($result);
    }

    public function testPhoneReturnsFalseForBothNull(): void
    {
        $result = ValidationWorker::phone(null, null);

        $this->assertFalse($result);
    }

    #/ PHONE VALIDATION TESTS
    #----------------------------------------------------------------------
}
