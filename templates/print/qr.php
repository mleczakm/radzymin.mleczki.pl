<?php
/**
 * @var \App\Domain\Petition $petition
 * @var string $petitionUrl
 * @var string $qrDataUri
 */
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Kod QR — <?= e($petition->title) ?></title>
<link rel="stylesheet" href="<?= e(asset_url('print.css')) ?>">
</head>
<body>
<div class="toolbar">
  <a href="/petycja/<?= e($petition->slug) ?>">&larr; Wróć do petycji</a>
  <button type="button" class="print-button" onclick="window.print()">Drukuj</button>
  <p>Wydrukuj tę kartkę i umieść ją w miejscu dostępnym dla sąsiadów. Zeskanuj kod telefonem, aby przeczytać petycję i ją podpisać.</p>
</div>
<main class="qr-sheet">
  <p class="qr-eyebrow">Radzymińskie Petycje</p>
  <h1><?= e($petition->title) ?></h1>
  <p class="qr-invitation">Zeskanuj kod, przeczytaj petycję i dołącz swój głos.</p>
  <img class="petition-qr" src="<?= e($qrDataUri) ?>" alt="Kod QR prowadzący do petycji: <?= e($petition->title) ?>">
  <p class="qr-url"><?= e($petitionUrl) ?></p>
</main>
</body>
</html>
