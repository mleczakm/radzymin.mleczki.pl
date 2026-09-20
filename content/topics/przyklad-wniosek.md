---
slug: przyklad-wniosek
title: "Przykładowy wniosek — zastąp własną sprawą"
summary: >
  To jest przykładowa sprawa pokazująca, jak prezentowane są wnioski i inne
  działania wraz ze statusem i postępem. Podmień ją na prawdziwe sprawy.
status: waiting
institution: "Przykładowa instytucja"
updatedAt: 2026-09-10
steps:
  - title: Przygotowanie wniosku
    date: 2026-08-20
    done: true
  - title: Złożenie wniosku
    date: 2026-09-01
    done: true
  - title: Odpowiedź instytucji
    done: false
  - title: Ewentualne odwołanie lub kolejne kroki
    done: false
---

Miejsce na opis sprawy w formacie **Markdown**: czego dotyczy wniosek, do kogo został
skierowany i jaki jest cel. Możesz tu wstawiać linki, listy i pogrubienia.

## Dostępne statusy

Pole `status` przyjmuje jedną z wartości: `planned` (planowane), `in_progress` (w toku),
`waiting` (oczekuje na odpowiedź), `completed` (zakończone), `rejected` (odrzucone).

## Przykładowy wykres

Wykresy wstawiasz blokiem `chart` — szczegóły w README. Poniższe liczby są **wymyślone**, tylko do
pokazania, jak to wygląda.

```chart
type: bar
stacked: true
title: Wnioski o ukaranie za wjazd do strefy 12 t (dane przykładowe)
unit: wniosków
caption: Liczba wniosków złożonych w danym miesiącu, z podziałem na rozstrzygnięcie.
source: dane przykładowe — zastąp własnymi
labels: [Sty, Lut, Mar, Kwi, Maj, Cze]
series:
  - name: Uwzględnione
    values: [1, 3, 5, 6, 9, 12]
  - name: Odrzucone
    values: [2, 3, 2, 2, 1, 2]
  - name: Oczekujące
    values: [1, 3, 7, 3, 7, 7]
```
