<?php
$pageDescription = $description ?? 'Niezależna inicjatywa mieszkańców Radzymina: petycje do podpisania i sprawy, którymi się zajmuję.';
$phone = $organizer['phone'] ?? null;
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Petycje Radzymin') ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="theme-color" content="#007bc2">
<meta property="og:type" content="website">
<meta property="og:locale" content="pl_PL">
<meta property="og:site_name" content="Petycje Radzymin">
<meta property="og:title" content="<?= e($title ?? 'Petycje Radzymin') ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="stylesheet" href="/style.css">
</head>
<body>
<a class="skip-link" href="#tresc">Przejdź do treści</a>

<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="/" aria-label="Petycje Radzymin — strona główna">
      <img class="brand-logo" src="/img/herb-gmina-radzymin.svg" alt="Herb Gminy Radzymin" width="46" height="66">
      <span class="brand-text">
        <strong>Petycje Radzymin</strong>
        <small>niezależna inicjatywa mieszkańców</small>
      </span>
    </a>
    <nav class="site-nav" aria-label="Główna nawigacja">
      <a class="site-nav-link" href="/#petycje">Petycje</a>
      <a class="site-nav-link" href="/#sprawy">Sprawy</a>
      <a class="site-nav-cta" href="/#kontakt">Napisz do mnie</a>
    </nav>
  </div>
</header>

<main id="tresc"<?= $fullWidth ? '' : ' class="wrap page"' ?>>
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <p class="footer-contact">
      Pytania, pomysły, chęć pomocy? <a href="mailto:<?= e($organizer['contactEmail']) ?>">Napisz do mnie</a><?php if ($phone): ?>
      lub zadzwoń: <a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
    </p>
    <p class="disclaimer">
      To niezależna, prywatna inicjatywa. Strona nie jest prowadzona przez Gminę Radzymin ani Urząd Miasta i Gminy
      i nie występuje w ich imieniu; herb i logo należą do Gminy Radzymin.
    </p>
    <p><a href="/polityka-prywatnosci">Polityka prywatności</a></p>
  </div>
</footer>
</body>
</html>
