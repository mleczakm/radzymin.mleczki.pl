<?php

declare(strict_types=1);

// UWAGA: poniżej znajduje się PRZYKŁADOWA treść petycji — zastąp ją prawdziwą
// treścią przed wdrożeniem serwisu na produkcję. Każdy wpis w tablicy to jedna
// petycja dostępna pod adresem /petycja/{slug}.

return [
    [
        'slug' => 'przyklad',
        'title' => 'Petycja przykładowa — zastąp własną treścią',
        'lead' => 'To jest przykładowa petycja służąca do testów wdrożenia. '
            . 'Przed uruchomieniem serwisu podmień ten wpis na prawdziwą treść.',
        'body' => '<p>Miejsce na pełną treść petycji — możesz użyć znaczników HTML '
            . '(akapity, listy, pogrubienia), tekst zostanie wyświetlony bez dodatkowego escapowania.</p>'
            . '<p>Adresat petycji, uzasadnienie oraz konkretne żądania powinny znaleźć się właśnie tutaj.</p>',
        'createdAt' => '2026-09-16',
    ],
];
