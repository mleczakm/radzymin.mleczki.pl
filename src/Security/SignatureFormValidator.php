<?php

declare(strict_types=1);

namespace App\Security;

/** Validates the public signature form. Names allow Polish diacritics, spaces, hyphens and apostrophes. */
final class SignatureFormValidator
{
    private const NAME_PATTERN = '/^[\p{L} \'\-]{2,100}$/u';

    /**
     * @param array<string, mixed> $post
     * @return array<string, string> field => error message, empty when valid
     */
    public function validate(array $post): array
    {
        $errors = [];

        $firstName = trim((string) ($post['first_name'] ?? ''));
        $lastName = trim((string) ($post['last_name'] ?? ''));
        $city = trim((string) ($post['city'] ?? ''));
        $email = trim((string) ($post['email'] ?? ''));
        $consent = (string) ($post['consent'] ?? '') === '1';

        if (!preg_match(self::NAME_PATTERN, $firstName)) {
            $errors['first_name'] = 'Podaj poprawne imię (2–100 znaków).';
        }

        if (!preg_match(self::NAME_PATTERN, $lastName)) {
            $errors['last_name'] = 'Podaj poprawne nazwisko (2–100 znaków).';
        }

        if (mb_strlen($city) < 2 || mb_strlen($city) > 100) {
            $errors['city'] = 'Podaj poprawną miejscowość (2–100 znaków).';
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 190) {
            $errors['email'] = 'Podaj poprawny adres e-mail.';
        }

        if (!$consent) {
            $errors['consent'] = 'Zgoda na przetwarzanie danych osobowych jest wymagana.';
        }

        return $errors;
    }
}
