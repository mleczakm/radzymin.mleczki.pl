<h1>Anonimizacja dokumentów</h1>

<p class="anon-note">
  Wszystko dzieje się w Twojej przeglądarce — tekst i pliki PDF nie są wysyłane na serwer.
  Wykrywanie jest heurystyczne (PESEL z sumą kontrolną, e-mail, telefon, adres oraz podane
  poniżej frazy), więc <strong>zawsze sprawdź wynik przed publikacją</strong>.
</p>

<section class="anon-section" aria-labelledby="anon-phrases-h">
  <h2 id="anon-phrases-h">Imiona i nazwiska do zamazania</h2>
  <label for="anon-phrases">Po przecinku, np. <em>Jan, Kowalski</em>. Odmienione formy też są łapane. Zapamiętywane w tej przeglądarce.</label>
  <input id="anon-phrases" type="text" class="anon-wide" autocomplete="off" placeholder="Jan, Kowalski">
</section>

<section class="anon-section" aria-labelledby="anon-text-h">
  <h2 id="anon-text-h">Tekst</h2>
  <label for="anon-text-in">Wklej treść (np. plik sprawy w Markdown):</label>
  <textarea id="anon-text-in" rows="10" class="anon-wide"></textarea>
  <p>
    <button type="button" id="anon-text-run" class="button">Zanonimizuj tekst</button>
    <button type="button" id="anon-text-copy" class="button button-secondary" disabled>Kopiuj wynik</button>
    <span id="anon-text-status" role="status"></span>
  </p>
  <textarea id="anon-text-out" rows="10" class="anon-wide" readonly aria-label="Wynik"></textarea>
</section>

<section class="anon-section" aria-labelledby="anon-pdf-h">
  <h2 id="anon-pdf-h">PDF</h2>
  <p>
    Program zamalowuje wykryte fragmenty czarnymi prostokątami, a Ty możesz dorysować własne
    (przeciągnij myszą) lub usunąć zbędne (kliknij prostokąt). Wynik to <strong>nowy PDF złożony
    z obrazów stron</strong> — bez warstwy tekstowej i metadanych, więc zamalowanych danych nie da
    się odzyskać zaznaczeniem ani kopiowaniem. Cena: wynik nie jest przeszukiwalny i jest większy.
    Skany bez warstwy tekstowej trzeba zamalować ręcznie.
  </p>
  <p>
    <input id="anon-pdf-file" type="file" accept="application/pdf" aria-label="Plik PDF">
    <label for="anon-pdf-quality">Jakość:</label>
    <select id="anon-pdf-quality">
      <option value="1.5">niższa (mały plik)</option>
      <option value="2" selected>standardowa</option>
      <option value="3">wysoka (duży plik)</option>
    </select>
    <label for="anon-pdf-format">Zapisz jako:</label>
    <select id="anon-pdf-format">
      <option value="pdf" selected>PDF</option>
      <option value="webp-files">WebP — osobny plik dla każdej strony</option>
      <option value="webp-anim">WebP — animacja ze slajdami (tylko wielostronicowe)</option>
    </select>
    <label for="anon-slide-seconds">Sekund na slajd:</label>
    <input id="anon-slide-seconds" type="number" min="1" max="60" value="4" style="width:4em">
  </p>

  <div id="anon-pdf-ui" hidden>
    <p class="anon-toolbar">
      <button type="button" id="anon-prev" class="button button-secondary">&larr;</button>
      <span id="anon-page-info"></span>
      <button type="button" id="anon-next" class="button button-secondary">&rarr;</button>
      <button type="button" id="anon-redetect" class="button button-secondary">Wykryj ponownie (wszystkie strony)</button>
      <button type="button" id="anon-clear-page" class="button button-secondary">Wyczyść tę stronę</button>
    </p>
    <div id="anon-stage" class="anon-stage"><canvas id="anon-canvas"></canvas></div>
    <p>
      <button type="button" id="anon-export" class="button">Pobierz zanonimizowany plik</button>
      <span id="anon-pdf-status" role="status"></span>
    </p>
  </div>
</section>

<script src="<?= e(asset_url('anonymize/pii-check.js')) ?>"></script>
<script src="<?= e(asset_url('anonymize/vendor/pdf-lib.min.js')) ?>"></script>
<script type="module" src="<?= e(asset_url('anonymize/anonymize.js')) ?>"></script>
