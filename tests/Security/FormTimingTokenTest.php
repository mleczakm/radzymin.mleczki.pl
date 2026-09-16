<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\FormTimingToken;
use PHPUnit\Framework\TestCase;

final class FormTimingTokenTest extends TestCase
{
    public function testFreshTokenIsRejectedAsTooFast(): void
    {
        $token = new FormTimingToken('secret');

        self::assertFalse($token->isValid($token->generate()));
    }

    public function testTokenBecomesValidAfterMinimumDelay(): void
    {
        $token = new FormTimingToken('secret');
        $generated = $token->generate();

        [$timestamp, $signature] = explode('.', $generated, 2);
        $backdated = ((int) $timestamp - 5) . '.' . $signature;

        self::assertFalse($token->isValid($backdated), 'signature should not match a tampered timestamp');
    }

    public function testInvalidFormatsAreRejected(): void
    {
        $token = new FormTimingToken('secret');

        self::assertFalse($token->isValid(null));
        self::assertFalse($token->isValid(''));
        self::assertFalse($token->isValid('not-a-token'));
        self::assertFalse($token->isValid('123'));
    }

    public function testTokenSignedInThePastIsValid(): void
    {
        $token = new FormTimingToken('secret');
        $timestamp = (string) (time() - 10);
        $signature = hash_hmac('sha256', $timestamp, 'secret');

        self::assertTrue($token->isValid($timestamp . '.' . $signature));
    }

    public function testExpiredTokenIsRejected(): void
    {
        $token = new FormTimingToken('secret');
        $timestamp = (string) (time() - 7 * 3600);
        $signature = hash_hmac('sha256', $timestamp, 'secret');

        self::assertFalse($token->isValid($timestamp . '.' . $signature));
    }
}
