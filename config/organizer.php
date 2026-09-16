<?php

declare(strict_types=1);

// Dane administratora danych osobowych (RODO) wyświetlane w polityce prywatności,
// stopce e-maili i na drukowanej liście do zbierania podpisów. Uzupełnij przed
// wdrożeniem — dane mogą też pochodzić ze zmiennych środowiskowych.

return [
    'name' => env('ORGANIZER_NAME', 'TODO: imię i nazwisko / nazwa organizatora petycji'),
    'address' => env('ORGANIZER_ADDRESS', 'TODO: adres korespondencyjny organizatora'),
    'contactEmail' => env('ORGANIZER_EMAIL', 'radzymin.mleczki@gmail.com'),
];
