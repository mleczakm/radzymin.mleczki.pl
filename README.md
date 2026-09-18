# radzymin.mleczki.pl — Petycje

Serwis do bezpiecznego zbierania podpisów pod petycjami mieszkańców Radzymina.
PHP + [Swoole](https://www.swoole.co.uk/) (współprogramy/coroutines), uruchamiany
jako pojedynczy mikroserwis HTTP — bez frameworka, wzorowany na
[logging-poc (poc-microservice)](https://github.com/mleczakm/logging-poc/tree/poc-microservice),
ale z bieżącą wersją `ext-swoole`.

## Zanim wdrożysz na produkcję

1. **Podmień treść petycji** — dodaj/edytuj pliki w
   [content/petitions/](content/petitions/) (jeden plik Markdown na petycję, format opisany
   niżej). Obecnie jest tam wyłącznie przykładowy wpis.
   To samo dotyczy przykładowej sprawy w [content/topics/](content/topics/).
2. **Uzupełnij dane administratora** (RODO) w [config/organizer.php](config/organizer.php)
   (imię i nazwisko / nazwa organizatora, adres, e-mail kontaktowy) lub przez zmienne
   `ORGANIZER_NAME`, `ORGANIZER_ADDRESS`, `ORGANIZER_EMAIL`.
3. Skonfiguruj konto `radzymin.mleczki@gmail.com` (hasło aplikacji Google, nie hasło do konta)
   i ustaw `MAILER_DSN`.
4. Wygeneruj `APP_SECRET` i hash hasła administratora (`bin/hash-password`).

## Treść petycji

Każda petycja to jeden plik Markdown w [content/petitions/](content/petitions/), np.
`content/petitions/sciezka-rowerowa.md`:

```markdown
---
slug: sciezka-rowerowa
title: "Petycja o budowę ścieżki rowerowej przy ul. Przykładowej"
lead: >
  Jedno-dwuzdaniowy lead widoczny na liście petycji i w nagłówku strony.
createdAt: 2026-09-16
goal: 500
deadline: 2026-12-31
---

Pełna treść petycji w **Markdown** — akapity, listy, pogrubienia itd.
```

`slug`, `title` i `lead` są wymagane. Opcjonalne pola:

- `createdAt` — domyślnie data modyfikacji pliku.
- `goal` — liczba podpisów jako cel; gdy ustawiona, na stronie petycji i po potwierdzeniu
  e-maila pojawia się pasek postępu (`X z celu Y podpisów`, `Z%`, „Brakuje jeszcze N…”).
- `deadline` — data (RRRR-MM-DD); razem z `goal` pokazuje też „pozostało N dni”.

Plik parsowany jest raz na proces workera (przy starcie), a wynikowy HTML trzymany w
pamięci przez cały czas życia workera — dodanie kolejnej petycji nie kosztuje nic przy obsłudze
requestów, tylko przy starcie serwera.

## Sprawy („Czym się zajmuję”)

Poza petycjami strona główna pokazuje sprawy, którymi się zajmujesz — wnioski do instytucji,
pisma, inicjatywy — ze statusem i postępem. Każda sprawa to plik Markdown w
[content/topics/](content/topics/) (lista na `/`, szczegóły na `/sprawy/{slug}`), np.
`content/topics/wniosek-do-urzedu.md`:

```markdown
---
slug: wniosek-do-urzedu
title: "Wniosek o remont chodnika przy ul. Przykładowej"
summary: Krótki opis widoczny na liście na stronie głównej.
status: waiting
institution: "Urząd Miasta i Gminy"
updatedAt: 2026-09-10
steps:
  - title: Przygotowanie wniosku
    date: 2026-08-20
    done: true
  - title: Złożenie wniosku
    date: 2026-09-01
    done: true
  - title: Odpowiedź urzędu
    done: false
---

Szczegóły sprawy w **Markdown** — treść wniosku, linki do pism, uzasadnienie.
```

Wymagane: `slug`, `title`, `summary`, `status`. `status` to jedno z: `planned` (planowane),
`in_progress` (w toku), `waiting` (oczekuje na odpowiedź), `completed` (zakończone),
`rejected` (odrzucone). Opcjonalne:

- `institution` — adresat/instytucja, pokazywana pod tytułem.
- `steps` — kroki sprawy (`title`, opcjonalnie `date`, `done: true|false`). Z nich liczony jest
  pasek postępu (odsetek zrealizowanych kroków; sprawa `completed` zawsze ma 100%) oraz oś czasu
  „Przebieg sprawy” na stronie szczegółów.
- `updatedAt` — data ostatniej aktualizacji; domyślnie data ostatniego zrealizowanego kroku.

Sprawy aktywne (w toku → oczekujące → planowane) są na górze, potem zakończone i odrzucone;
w obrębie statusu — od najnowszej aktualizacji. Tak jak petycje, pliki są parsowane raz na
proces workera przy starcie i trzymane w pamięci, więc **zmiana statusu wymaga nowego
wdrożenia** (tag), a nie tylko edycji pliku na serwerze.

## Architektura

- **Serwer**: `Swoole\Http\Server` w trybie `SWOOLE_BASE`, workery HTTP + workery zadań
  (`task_worker_num`) — wysyłka e-maili potwierdzających idzie przez task workera, żeby
  wolne SMTP nie blokowało obsługi requestów.
- **Baza danych**: SQLite (plik) przez PDO. Jedna tabela `signatures` z unikalnym
  indeksem `(petition_slug, email)` — uniemożliwia podwójny podpis tym samym e-mailem pod
  tą samą petycją. Połączenie PDO tworzone jest leniwie, dopiero w `onWorkerStart` (po forku
  procesu workera), bo uchwytów SQLite nie wolno dzielić między procesami.
- **Limiter żądań**: `Swoole\Table` w pamięci współdzielonej — liczniki per IP widoczne dla
  wszystkich workerów bez blokad.
- **Szablony**: proste pliki PHP (`templates/`), bez silnika szablonów — wyjście zawsze
  przez `e()` (`htmlspecialchars`).
- **Poczta**: `symfony/mailer`, transport SMTP wskazany przez `MAILER_DSN`
  (w produkcji: Gmail + hasło aplikacji).

### Przepływ podpisu petycji

1. `GET /petycja/{slug}` — formularz z ukrytym polem-pułapką (honeypot) i podpisanym
   znacznikiem czasu.
2. `POST /petycja/{slug}/podpisz` — walidacja + zabezpieczenia przed botami (patrz niżej),
   zapis rekordu `pending` w SQLite, zlecenie wysyłki e-maila do task workera.
3. Osoba klika link w e-mailu → `GET /potwierdz/{token}` → status zmienia się na `confirmed`
   (dopiero wtedy podpis liczy się do wyniku publicznego).

### Podstawowa ochrona przed botami

- **Honeypot** — ukryte polem CSS (`.hp-field`), niewidoczne dla ludzi; wypełnione pole =
  odrzucenie (po cichu, bez informowania bota).
- **Znacznik czasu formularza** — podpisany HMAC-em znacznik generowany przy renderze
  formularza; odrzucane jest wypełnienie szybsze niż 3 sekundy lub starsze niż 6 godzin.
- **Rate limiting per IP** — maks. 5 prób na godzinę (`Swoole\Table`).
- **Podwójne potwierdzenie e-mailem** — podpis liczy się dopiero po kliknięciu w link,
  więc atakujący musiałby kontrolować realne skrzynki e-mail.

To celowo *podstawowa* ochrona (bez zewnętrznych captchy typu reCAPTCHA/hCaptcha) — nie
wymaga zewnętrznych usług i nie zbiera dodatkowych danych o odwiedzających.

## Panel administracyjny

Dostępny pod `/admin`, chroniony HTTP Basic Auth (jedno konto, `ADMIN_USER` +
`ADMIN_PASSWORD_HASH`). Świadomie bez systemu logowania z sesjami — jeden administrator,
więc Basic Auth przez HTTPS jest wystarczający i dużo prostszy.

```bash
bin/hash-password "twoje-haslo"
# wynik wklej jako ADMIN_PASSWORD_HASH
```

Panel pozwala:

- zobaczyć liczbę podpisów potwierdzonych/oczekujących per petycja,
- pobrać eksport CSV podpisów potwierdzonych (`/admin/petycje/{slug}/eksport.csv`),
- dopisać podpisy zebrane na papierze (`/admin/petycje/{slug}/papier`) — wklejenie listy
  `Imię Nazwisko;Miejscowość` (jedna osoba na wiersz) od razu zapisuje je jako potwierdzone
  (`source = paper`), więc licznik na stronie łączy podpisy online i papierowe.

## Lista do zbierania podpisów papierowo

`GET /petycja/{slug}/lista.pdf` generuje (przez [dompdf](https://github.com/dompdf/dompdf))
gotowy do druku arkusz A4: tytuł petycji, krótka klauzula RODO i tabela
`Lp. / Imię i nazwisko / Miejscowość / Podpis`. Po zebraniu podpisów przepisz je do panelu
administracyjnego (patrz wyżej), żeby doliczyć je do wyniku.

## Rozwój lokalny

```bash
docker compose up
```

Plik [.env](.env) jest w repozytorium celowo — zawiera **wyłącznie jawne wartości deweloperskie**
(repozytorium jest publiczne), więc nie trzeba niczego kopiować ani generować. Logowanie do
panelu admina lokalnie: `admin` / `admin`. Serwer w trybie innym niż `APP_ENV=dev` odmówi
startu z `APP_SECRET` zaczynającym się od `dev-insecure-` lub z hasłem admina `admin`, więc
przypadkowe wklejenie tego pliku do sekretu `DOTENV` nie skończy się produkcją z publicznymi
danymi. Hash hasła w `.env` jest w pojedynczych cudzysłowach, bo inaczej `docker compose`
zinterpoluje znaki `$` i go zepsuje — zachowaj je, jeśli będziesz go zmieniać.

- Aplikacja: http://localhost:8080
- Mailpit (podgląd wysłanych e-maili zamiast prawdziwego Gmaila): http://localhost:8025

Bez Dockera (wymaga lokalnie zainstalowanego `ext-swoole`):

```bash
composer install
php bin/server
```

## Testy

```bash
composer install
composer run-script lint   # php -l dla wszystkich plików
composer test               # PHPUnit
```

## Wdrożenie

Tag (`git tag vX.Y.Z && git push --tags`) uruchamia
[`.github/workflows/production-build-and-deploy.yml`](.github/workflows/production-build-and-deploy.yml):
build obrazu → `ghcr.io/mleczakm/radzymin.mleczki.pl` → deployment przez Ansible na serwer
Mikrus, tym samym mechanizmem co [cargo.mleczki.pl](https://github.com/mleczakm/cargo.mleczki.pl)
(rola `app_deploy`, Cloudflare DNS, Cytrus).

### Wymagane sekrety repozytorium (Settings → Secrets and variables → Actions)

| Sekret | Opis |
|---|---|
| `SSH_PRIVATE_KEY` | klucz SSH do serwera Mikrus |
| `MIKRUS_SSH_HOST`, `MIKRUS_SSH_PORT`, `MIKRUS_IPV6` | dane dostępowe do serwera |
| `CYTRUS_IPV4`, `CYTRUS_API_TOKEN` | Mikrus Cytrus (proxy domenowe) |
| `CLOUDFLARE_API_TOKEN`, `CLOUDFLARE_ZONE_ID` | zarządzanie rekordem DNS domeny |
| `DOTENV` | zawartość pliku ze **sekretami produkcyjnymi** (patrz niżej; to nie jest commitowany `.env` z wartościami deweloperskimi) |

Zawartość sekretu `DOTENV` (jeden `KLUCZ=wartość` na linię):

```
APP_SECRET=...            # php -r "echo bin2hex(random_bytes(32));"
MAILER_DSN=smtp://radzymin.mleczki%40gmail.com:HASLO_APLIKACJI@smtp.gmail.com:587
ADMIN_USER=admin
ADMIN_PASSWORD_HASH=...   # bin/hash-password "..."
ORGANIZER_NAME=...
ORGANIZER_ADDRESS=...
ORGANIZER_EMAIL=radzymin.mleczki@gmail.com
```

Domena, port kontenera i zmienne niesekretne (np. `DB_PATH`) są ustawione w
[ansible/playbooks/config.yml](ansible/playbooks/config.yml) — port `8081` domyślnie
(inny niż `8080` używany przez cargo.mleczki.pl na tym samym serwerze); zweryfikuj, że jest
wolny na docelowym serwerze przed pierwszym wdrożeniem.
