<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\SignatureFormValidator;
use PHPUnit\Framework\TestCase;

final class SignatureFormValidatorTest extends TestCase
{
    private SignatureFormValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SignatureFormValidator();
    }

    public function testValidSubmissionHasNoErrors(): void
    {
        $errors = $this->validator->validate([
            'first_name' => 'Małgorzata',
            'last_name' => 'Żółć-Wąsik',
            'city' => 'Radzymin',
            'email' => 'test@example.com',
            'consent' => '1',
        ]);

        self::assertSame([], $errors);
    }

    public function testMissingFieldsProduceErrors(): void
    {
        $errors = $this->validator->validate([]);

        self::assertArrayHasKey('first_name', $errors);
        self::assertArrayHasKey('last_name', $errors);
        self::assertArrayHasKey('city', $errors);
        self::assertArrayHasKey('email', $errors);
        self::assertArrayHasKey('consent', $errors);
    }

    public function testInvalidEmailIsRejected(): void
    {
        $errors = $this->validator->validate([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'city' => 'Radzymin',
            'email' => 'not-an-email',
            'consent' => '1',
        ]);

        self::assertArrayHasKey('email', $errors);
    }

    public function testNamesWithDigitsAreRejected(): void
    {
        $errors = $this->validator->validate([
            'first_name' => 'Jan123',
            'last_name' => 'Kowalski',
            'city' => 'Radzymin',
            'email' => 'test@example.com',
            'consent' => '1',
        ]);

        self::assertArrayHasKey('first_name', $errors);
    }

    public function testMissingConsentIsRejected(): void
    {
        $errors = $this->validator->validate([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'city' => 'Radzymin',
            'email' => 'test@example.com',
        ]);

        self::assertArrayHasKey('consent', $errors);
    }
}
