<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Petycje Radzymin') ?></title>
<meta name="description" content="Serwis do zbierania podpisów pod petycjami mieszkańców Radzymina.">
<link rel="stylesheet" href="/style.css">
</head>
<body>
<header class="site-header">
  <div class="wrap">
    <a class="brand" href="/">Petycje&nbsp;Radzymin</a>
  </div>
</header>
<main class="wrap">
<?= $content ?>
</main>
<footer class="site-footer">
  <div class="wrap">
    <p>Petycje Radzymin &middot; <a href="/polityka-prywatnosci">Polityka prywatności</a></p>
  </div>
</footer>
</body>
</html>
