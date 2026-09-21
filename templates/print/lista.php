<?php
/**
 * @var \App\Domain\Petition $petition
 * @var array{name: string, address: string, contactEmail: string, phone: string|null, contactFormEndpoint: string|null} $organizer
 * @var int $rows
 */
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Lista podpisów — <?= e($petition->title) ?></title>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="stylesheet" href="<?= e(asset_url('print.css')) ?>">
<script src="<?= e(asset_url('print.js')) ?>" defer></script>
</head>
<body>
<div class="toolbar">
  <a href="/petycja/<?= e($petition->slug) ?>">&larr; Wróć do petycji</a>
  <button type="button" class="print-button" data-print hidden>Drukuj</button>
  <p>
    Strona jest przygotowana do wydruku na papierze A4 (Ctrl/Cmd + P). W oknie druku możesz wyłączyć
    nagłówki i stopki przeglądarki.
  </p>
</div>

<main class="sheet">
  <h1><?= e($petition->title) ?></h1>
  <p class="lead"><?= e($petition->lead) ?></p>

  <div class="rodo">
    Administratorem danych osobowych zbieranych na niniejszej liście jest <?= e($organizer['name']) ?>,
    <?= e($organizer['address']) ?>, kontakt: <?= e($organizer['contactEmail']) ?>. Dane (imię, nazwisko,
    miejscowość) przetwarzane są wyłącznie w celu poparcia niniejszej petycji. Podanie danych jest
    dobrowolne. Podpisując listę, wyrażasz zgodę na przetwarzanie podanych danych w tym celu. Pełna
    polityka prywatności dostępna jest pod adresem radzymin.mleczki.pl/polityka-prywatnosci.
  </div>

  <table>
    <thead>
      <tr>
        <th class="lp" scope="col">Lp.</th>
        <th class="name" scope="col">Imię i nazwisko</th>
        <th class="city" scope="col">Miejscowość</th>
        <th class="signature" scope="col">Podpis</th>
      </tr>
    </thead>
    <tbody>
      <?php for ($i = 1; $i <= $rows; $i++): ?>
        <tr>
          <td class="lp"><?= $i ?></td>
          <td class="name"></td>
          <td class="city"></td>
          <td class="signature"></td>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>
</main>
</body>
</html>
