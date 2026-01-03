<?php

namespace FastRaven\Tests\Workers;

use PHPUnit\Framework\TestCase;
use FastRaven\Workers\ValidationWorker;
use FastRaven\Internal\Slave\ValidationSlave;
use FastRaven\Components\Data\ValidationFlags;

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
    #\ PASSWORD VALIDATION TESTS

    public function testPasswordValidatesWithAllCriteriaMet(): void
    {
        $flags = ValidationFlags::password(8, 20, 1, 1, 1, 1);
        $password = 'Passw0rd!';

        $result = ValidationWorker::password($password, $flags);

        $this->assertTrue($result);
    }

    public function testPasswordRejectsTooShortPassword(): void
    {
        $flags = ValidationFlags::password(8, 20, 0, 0, 0, 0);
        $password = 'short';

        $result = ValidationWorker::password($password, $flags);

        $this->assertFalse($result);
    }

    public function testPasswordRejectsTooLongPassword(): void
    {
        $flags = ValidationFlags::password(0, 10, 0, 0, 0, 0);
        $password = 'thispasswordistoolong';

        $result = ValidationWorker::password($password, $flags);

        $this->assertFalse($result);
    }

    public function testPasswordRejectsWhenMissingNumbers(): void
    {
        $flags = ValidationFlags::password(0, 255, 1, 0, 0, 0);
        $password = 'PasswordWithoutNumber';

        $result = ValidationWorker::password($password, $flags);

        $this->assertFalse($result);
    }

    public function testPasswordRejectsWhenMissingSpecialChars(): void
    {
        $flags = ValidationFlags::password(0, 255, 0, 1, 0, 0);
        $password = 'PasswordWithoutSpecial123';

        $result = ValidationWorker::password($password, $flags);

        $this->assertFalse($result);
    }

    public function testPasswordRejectsWhenMissingLowercase(): void
    {
        $flags = ValidationFlags::password(0, 255, 0, 0, 1, 0);
        $password = 'PASSWORDWITHOURLOWERCASE123!';

        $result = ValidationWorker::password($password, $flags);

        $this->assertFalse($result);
    }

    public function testPasswordRejectsWhenMissingUppercase(): void
    {
        $flags = ValidationFlags::password(0, 255, 0, 0, 0, 1);
        $password = 'passwordwithoutuppercase123!';

        $result = ValidationWorker::password($password, $flags);

        $this->assertFalse($result);
    }

    public function testPasswordValidatesExactMinLength(): void
    {
        $flags = ValidationFlags::password(8, 20, 0, 0, 0, 0);
        $password = 'exactly8';

        $result = ValidationWorker::password($password, $flags);

        $this->assertTrue($result);
    }

    public function testPasswordValidatesExactMaxLength(): void
    {
        $flags = ValidationFlags::password(0, 10, 0, 0, 0, 0);
        $password = '1234567890';

        $result = ValidationWorker::password($password, $flags);

        $this->assertTrue($result);
    }

    public function testPasswordReturnsFalseForNull(): void
    {
        $flags = ValidationFlags::password(0, 255, 0, 0, 0, 0);

        $result = ValidationWorker::password(null, $flags);

        $this->assertFalse($result);
    }

    public function testPasswordHandlesUnicodeCharacters(): void
    {
        $flags = ValidationFlags::password(0, 255, 0, 0, 0, 0);
        $password = 'пароль密码🔒';

        $result = ValidationWorker::password($password, $flags);

        $this->assertTrue($result);
    }

    public function testPasswordValidatesMultipleNumbers(): void
    {
        $flags = ValidationFlags::password(0, 255, 3, 0, 0, 0);
        $password = 'password123';

        $result = ValidationWorker::password($password, $flags);

        $this->assertTrue($result);
    }

    public function testPasswordRejectsInsufficientNumbers(): void
    {
        $flags = ValidationFlags::password(0, 255, 3, 0, 0, 0);
        $password = 'password12';

        $result = ValidationWorker::password($password, $flags);

        $this->assertFalse($result);
    }

    #/ PASSWORD VALIDATION TESTS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ AGE VALIDATION TESTS

    public function testAgeValidatesWithinRange(): void
    {
        $flags = ValidationFlags::age(18, 65);
        $age = 30;

        $result = ValidationWorker::age($age, $flags);

        $this->assertTrue($result);
    }

    public function testAgeRejectsTooYoung(): void
    {
        $flags = ValidationFlags::age(18, 65);
        $age = 17;

        $result = ValidationWorker::age($age, $flags);

        $this->assertFalse($result);
    }

    public function testAgeRejectsTooOld(): void
    {
        $flags = ValidationFlags::age(18, 65);
        $age = 66;

        $result = ValidationWorker::age($age, $flags);

        $this->assertFalse($result);
    }

    public function testAgeValidatesExactMinAge(): void
    {
        $flags = ValidationFlags::age(18, 65);
        $age = 18;

        $result = ValidationWorker::age($age, $flags);

        $this->assertTrue($result);
    }

    public function testAgeValidatesExactMaxAge(): void
    {
        $flags = ValidationFlags::age(18, 65);
        $age = 65;

        $result = ValidationWorker::age($age, $flags);

        $this->assertTrue($result);
    }

    public function testAgeReturnsFalseForNull(): void
    {
        $flags = ValidationFlags::age(18, 65);

        $result = ValidationWorker::age(null, $flags);

        $this->assertFalse($result);
    }

    public function testAgeReturnsFalseForZero(): void
    {
        $flags = ValidationFlags::age(1, 120);

        $result = ValidationWorker::age(0, $flags);

        $this->assertFalse($result);
    }

    #/ AGE VALIDATION TESTS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ USERNAME VALIDATION TESTS

    public function testUsernameValidatesWithinLengthRange(): void
    {
        $flags = ValidationFlags::username(3, 20);
        $username = 'validuser';

        $result = ValidationWorker::username($username, $flags);

        $this->assertTrue($result);
    }

    public function testUsernameRejectsTooShort(): void
    {
        $flags = ValidationFlags::username(3, 20);
        $username = 'ab';

        $result = ValidationWorker::username($username, $flags);

        $this->assertFalse($result);
    }

    public function testUsernameRejectsTooLong(): void
    {
        $flags = ValidationFlags::username(3, 20);
        $username = str_repeat('a', 21);

        $result = ValidationWorker::username($username, $flags);

        $this->assertFalse($result);
    }

    public function testUsernameValidatesExactMinLength(): void
    {
        $flags = ValidationFlags::username(3, 20);
        $username = 'abc';

        $result = ValidationWorker::username($username, $flags);

        $this->assertTrue($result);
    }

    public function testUsernameValidatesExactMaxLength(): void
    {
        $flags = ValidationFlags::username(3, 20);
        $username = str_repeat('a', 20);

        $result = ValidationWorker::username($username, $flags);

        $this->assertTrue($result);
    }

    public function testUsernameReturnsFalseForNull(): void
    {
        $flags = ValidationFlags::username(3, 20);

        $result = ValidationWorker::username(null, $flags);

        $this->assertFalse($result);
    }

    public function testUsernameReturnsFalseForEmptyString(): void
    {
        $flags = ValidationFlags::username(1, 20);

        $result = ValidationWorker::username(null, $flags);

        $this->assertFalse($result);
    }

    public function testUsernameHandlesSpecialCharacters(): void
    {
        $flags = ValidationFlags::username(0, 50);
        $username = 'user_name-123';

        $result = ValidationWorker::username($username, $flags);

        $this->assertTrue($result);
    }

    #/ USERNAME VALIDATION TESTS
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
