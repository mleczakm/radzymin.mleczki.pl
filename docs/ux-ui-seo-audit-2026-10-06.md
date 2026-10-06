# Audyt UX/UI i SEO — 6 października 2026

Badana i testowana wersja: `http://localhost:8080`. Ocena ekspercka na podstawie przeglądu strony i kodu, bez badań z rzeczywistymi użytkownikami. Lokalny proces Swoole wymagał restartu: przechowywał w pamięci wcześniejsze treści petycji.

## Odbiór przez nowego odwiedzającego

Strona jasno przedstawia lokalną inicjatywę i jej autora. Konkretne sprawy, dokumenty, daty i możliwość kontaktu budują wiarygodność. Herb Radzymina wzmacnia lokalny charakter; widoczna informacja o niezależności projektu jest istotna, ponieważ sam herb może sugerować związek z urzędem.

Największą przeszkodą była hierarchia: wiele zachęt do kontaktu, długa droga do spraw mieszkańców i panel udostępniania przed formularzem podpisu. Karty wymagały dużo przewijania. Zielone „ostatnie ustalenie” mogło też sugerować sukces, choć zdarzeniem bywa odmowa udzielenia informacji.

## Propozycje i wdrożone zmiany

| Obszar | Problem | Wdrożenie |
| --- | --- | --- |
| Pierwszy ekran | Powtarzające się wezwanie do kontaktu zamiast wyboru głównych zadań | Dwie ścieżki: petycja oraz sprawy mieszkańców. Przy braku spraw pozostaje kontakt. Nagłówek i opis korzystają z tytułu oraz wstępu petycji, jeśli nie podano krótkiej wersji. |
| Kolejność treści | Sekcja sposobów pomocy oddzielała petycję od dokumentowanych działań | Sprawy umieszczone bezpośrednio po petycjach; sposoby pomocy niżej. |
| Lista spraw | Długa lista bez możliwości zawężenia | Dwie kolumny na dużym ekranie, jedna na telefonie; filtry wszystkich, otwartych i zamkniętych spraw z licznikami, stanem przycisku i komunikatem dla czytników ekranu. Bez JS widoczna cała lista. |
| Karty | Status konkurował z długim tytułem, a zielony opis sugerował pozytywny wynik | Status pod tytułem, wyrównane odnośniki do szczegółów, neutralna etykieta „Ostatnie zdarzenie”. |
| Sposoby pomocy | Numery sugerowały obowiązkową kolejność działań | Nienumerowana lista z ikonami z istniejącego zestawu strony. |
| Nawigacja | „O mnie” prowadziło tylko do skrótu; długie podstrony utrudniały orientację | Link do pełnej strony autora, powrót do odpowiedniej sekcji strony głównej i skróty do treści, formularza oraz przebiegu sprawy. Menu mobilne zamyka się także po Escape i kliknięciu poza nim. |
| Podpisywanie | Udostępnianie poprzedzało najważniejszą akcję | Formularz przed panelem udostępniania i ostatnimi podpisami; obok formularza wyjaśnienie obowiązkowego potwierdzenia e-mailem. |
| Błędy formularza | Użytkownik mógł wrócić na początek długiej strony bez zauważenia błędu | Walidacja przeglądarki oraz zachowana walidacja serwera. Podsumowanie błędów otrzymuje fokus, linki prowadzą do pól, błędy są powiązane przez ARIA. Wpisane dane i zaznaczona zgoda pozostają po nieudanej próbie. |
| Podgląd udostępnienia | Grafika Open Graph wskazywała SVG | PNG 1200 × 630, około 66 kB, z typem, wymiarami i opisem oraz kartą `summary_large_image`. Plik SVG zachowany jako źródło. |
| Indeksowanie | Strony techniczne nie miały wspólnej dyrektywy noindex; robots blokował odczyt istniejącego noindex stron do druku | Strony bez publicznego adresu kanonicznego otrzymują `noindex, follow`. Robot może odczytać noindex listy do druku i strony QR. Dostęp do panelu oraz ścieżek potwierdzenia nadal jest wykluczony w robots.txt. |
| Mapa witryny | Możliwy podwójny ukośnik oraz wpis „O mnie” mimo braku treści | Normalizacja adresu bazowego i warunkowe dodawanie strony autora. |
| Nieaktualny link | Zwykły tekst 404 bez możliwości dalszej nawigacji | Strona błędu w normalnym układzie, opis i powrót na stronę główną; zachowany kod HTTP 404. |

## Weryfikacja lokalna

- `composer check`: kontrola składni, Mago analyze, Mago lint i PHPUnit — 179 testów, 2619 asercji.
- Kontrola składni JavaScriptu i `git diff --check`.
- Przeglądarka: szerokości 320, 390, 768 i 1280 px, bez poziomego przepełnienia na sprawdzonych widokach; tryb ciemny i ograniczenie animacji.
- Filtry zwracają odpowiednio 5 wszystkich, 2 otwarte i 3 zamknięte sprawy. Bez JavaScriptu wszystkie karty pozostają dostępne, a nieaktywne filtry są ukryte.
- Formularz: blokada pustego zgłoszenia, odpowiedź serwera na błędne imię, zachowanie danych i zgody, wskazanie błędnego pola. Dane syntetyczne nie utworzyły podpisu.
- Lokalny HTTP: wszystkie 9 adresów z mapy witryny zwraca 200, zawiera jeden H1 i poprawny canonical; brak powtórzonych identyfikatorów HTML. Strony do druku, QR i 404 mają noindex; PNG jest dostępny z poprawnym typem MIME.

## Dalsze usprawnienia redakcyjne

Otwarte sprawy mają aktualizacje z marca i lipca. Warto dopisać ich obecny stan, nawet gdy nadal trwa oczekiwanie, oraz odróżniać datę pisma od daty doręczenia. Wymaga to aktualnych informacji o rzeczywistym przebiegu spraw.

Przy rozwoju inicjatywy pomocne będą krótkie, potwierdzone podsumowania: co zmieniło się dla mieszkańca, co pozostaje do zrobienia i kiedy nastąpi następna aktualizacja. Takie informacje przekonują skuteczniej niż sama liczba pism. Fakty i daty w dokumentach pozostają podstawą tych podsumowań.

Wyników indeksowania ani wyglądu podglądu w poszczególnych serwisach społecznościowych nie da się potwierdzić wyłącznie na localhost; wymagają sprawdzenia publicznego adresu po wdrożeniu.

Podstawa zmiany indeksowania: [Google Search Central — noindex](https://developers.google.com/search/docs/crawling-indexing/block-indexing) i [dyrektywy robots](https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag). Robot musi móc pobrać stronę, aby odczytać jej dyrektywę noindex.
