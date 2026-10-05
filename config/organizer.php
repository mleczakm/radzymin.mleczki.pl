<?php

declare(strict_types=1);

// Dane administratora danych osobowych (RODO) wyświetlane w polityce prywatności,
// w sekcji kontaktowej i na drukowanej liście do zbierania podpisów.

return [
    'name' => env('ORGANIZER_NAME', 'Michał Mleczko'),
    'address' => env('ORGANIZER_ADDRESS', 'zamieszkały w Radzyminie'),
    'contactEmail' => env('ORGANIZER_EMAIL', 'kontakt@radzymin.mleczki.pl'),
    // Optional; shown next to the e-mail on the home page when set.
    'phone' => env('ORGANIZER_PHONE'),
    // Formspree endpoint and public Cloudflare Turnstile site key; private Turnstile secret belongs in Formspree.
    'contactFormEndpoint' => formspree_endpoint(env('CONTACT_FORM_ENDPOINT', 'https://formspree.io/f/xwlppzoj')),
    'turnstileSiteKey' => env('TURNSTILE_SITE_KEY') ?: null,
];
