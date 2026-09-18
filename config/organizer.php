<?php

declare(strict_types=1);

// Dane administratora danych osobowych (RODO) wyświetlane w polityce prywatności,
// w sekcji kontaktowej i na drukowanej liście do zbierania podpisów. Uzupełnij przed
// wdrożeniem — dane mogą też pochodzić ze zmiennych środowiskowych.

return [
    'name' => env('ORGANIZER_NAME', 'TODO: imię i nazwisko / nazwa organizatora petycji'),
    'address' => env('ORGANIZER_ADDRESS', 'TODO: adres korespondencyjny organizatora'),
    'contactEmail' => env('ORGANIZER_EMAIL', 'radzymin.mleczki@gmail.com'),
    // Optional; shown next to the e-mail on the home page when set.
    'phone' => env('ORGANIZER_PHONE'),
];
