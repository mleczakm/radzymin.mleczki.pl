<?php
/**
 * @var string $content
 * @var string $title
 * @var string|null $description
 * @var bool $fullWidth
 * @var array{name: string, address: string, contactEmail: string, phone: string|null, contactFormEndpoint: string|null, turnstileSiteKey: string|null} $organizer
 * @var string $baseUrl
 * @var string|null $canonicalPath
 */
?>
<?php
$pageDescription = $description ?? 'Niezależna inicjatywa mieszkańców Radzymina: petycje do podpisania i sprawy, którymi się zajmuję.';
$phone = $organizer['phone'] ?? null;
$contactFormEndpoint = $organizer['contactFormEndpoint'] ?? null;
$turnstileSiteKey = $organizer['turnstileSiteKey'] ?? null;
$canonicalPath ??= null;
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="theme-color" content="#007bc2">
<meta property="og:type" content="website">
<meta property="og:locale" content="pl_PL">
<meta property="og:site_name" content="Radzymińskie Petycje">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<?php if ($canonicalPath !== null): ?>
<link rel="canonical" href="<?= e(rtrim($baseUrl, '/') . ($canonicalPath === '/' ? '/' : $canonicalPath)) ?>">
<?php endif; ?>
<meta property="og:image" content="<?= e(rtrim($baseUrl, '/') . '/img/social-card.svg') ?>">
<?php if ($canonicalPath !== null): ?>
<meta property="og:url" content="<?= e(rtrim($baseUrl, '/') . ($canonicalPath === '/' ? '/' : $canonicalPath)) ?>">
<?php endif; ?>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="stylesheet" href="<?= e(asset_url('style.css')) ?>">
</head>
<body>
<a class="skip-link" href="#tresc">Przejdź do treści</a>

<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="/" aria-label="Radzymińskie Petycje — strona główna">
      <img class="brand-logo" src="<?= e(asset_url('img/herb-gmina-radzymin.svg')) ?>" alt="Herb Gminy Radzymin" width="46" height="58">
      <span class="brand-text">
        <strong>Radzymińskie Petycje</strong>
        <small>niezależny projekt mieszkańca</small>
      </span>
    </a>
    <nav class="site-nav" aria-label="Główna nawigacja">
      <a class="site-nav-link" href="/#petycje">Petycje</a>
      <a class="site-nav-link" href="/#sprawy">Sprawy</a>
      <a class="site-nav-link" href="/#o-mnie">O mnie</a>
      <a class="site-nav-cta" href="/#kontakt" data-contact-open>Napisz do mnie</a>
    </nav>
    <details class="mobile-nav">
      <summary aria-controls="mobile-nav-panel">Menu</summary>
      <nav class="mobile-nav-panel" id="mobile-nav-panel" aria-label="Menu główne">
        <a href="/#petycje">Petycje</a>
        <a href="/#sprawy">Sprawy</a>
        <a href="/#o-mnie">O mnie</a>
        <a href="/#kontakt" data-contact-open>Napisz do mnie</a>
      </nav>
    </details>
  </div>
</header>

<main id="tresc"<?= $fullWidth ? '' : ' class="wrap page"' ?>>
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap">
    <p class="footer-contact">
      Pytania, pomysły, chęć pomocy? <a href="/#kontakt" data-contact-open>Napisz do mnie</a><?php if ($phone): ?>
      lub zadzwoń: <a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
    </p>
    <p class="disclaimer">
      To niezależna inicjatywa mieszkańca, nieprowadzona przez Gminę Radzymin ani Urząd Miasta i Gminy
      i niewystępująca w ich imieniu. Herb i logo należą do Gminy Radzymin.
    </p>
    <p><a href="/polityka-prywatnosci">Polityka prywatności</a></p>
    <p class="site-credit">Stronę wykonał <a href="https://mleczakm.github.io/platnosci-blik/" rel="noopener" target="_blank">Michał Mleczko</a>.</p>
  </div>
</footer>
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'WebSite', '@id' => rtrim($baseUrl, '/') . '/#website', 'name' => 'Radzymińskie Petycje', 'url' => rtrim($baseUrl, '/') . '/'],
        ['@type' => 'Person', '@id' => rtrim($baseUrl, '/') . '/o-mnie#autor', 'name' => 'Michał Mleczko', 'url' => rtrim($baseUrl, '/') . '/o-mnie', 'image' => rtrim($baseUrl, '/') . '/img/michal-mleczko.jpg', 'jobTitle' => 'Organizator inicjatywy społecznej', 'homeLocation' => ['@type' => 'City', 'name' => 'Radzymin']],
        ...($canonicalPath !== null ? [[
            '@type' => 'WebPage',
            '@id' => rtrim($baseUrl, '/') . ($canonicalPath === '/' ? '/#strona' : $canonicalPath . '#strona'),
            'url' => rtrim($baseUrl, '/') . ($canonicalPath === '/' ? '/' : $canonicalPath),
            'name' => $title,
            'description' => $pageDescription,
            'isPartOf' => ['@id' => rtrim($baseUrl, '/') . '/#website'],
            'about' => ['@id' => rtrim($baseUrl, '/') . '/o-mnie#autor'],
        ]] : []),
        ...(is_string($canonicalPath) && str_starts_with($canonicalPath, '/sprawy/') ? [[
            '@type' => 'Article',
            'headline' => $title,
            'description' => $pageDescription,
            'author' => ['@id' => rtrim($baseUrl, '/') . '/o-mnie#autor'],
            'mainEntityOfPage' => rtrim($baseUrl, '/') . $canonicalPath,
        ]] : []),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>
<dialog class="contact-dialog" id="contact-dialog" aria-labelledby="contact-dialog-title">
  <div class="contact-dialog-head">
    <h2 id="contact-dialog-title">Napisz do mnie</h2>
    <button class="dialog-close" type="button" data-contact-close aria-label="Zamknij formularz">&times;</button>
  </div>
  <form class="contact-form" method="post" action="<?= e($contactFormEndpoint) ?>"
        data-contact-form data-fallback-email="<?= e($organizer['contactEmail']) ?>">
    <input type="hidden" name="_subject" value="Radzymińskie Petycje — wiadomość ze strony">
    <input type="hidden" name="_language" value="pl">
    <div class="field hp-field" aria-hidden="true">
      <label for="contact-gotcha">Nie wypełniaj tego pola</label>
      <input type="text" id="contact-gotcha" name="_gotcha" tabindex="-1" autocomplete="off">
    </div>
    <div class="field">
      <label for="contact-name">Imię <span class="optional">(opcjonalnie)</span></label>
      <input type="text" id="contact-name" name="name" autocomplete="name" maxlength="100">
    </div>
    <div class="field">
      <label for="contact-email">Adres e-mail</label>
      <input type="email" id="contact-email" name="email" autocomplete="email" required maxlength="190">
      <p class="field-hint">Na ten adres wyślę odpowiedź.</p>
    </div>
    <div class="field">
      <label for="contact-message">Wiadomość</label>
      <textarea id="contact-message" name="message" rows="5" required maxlength="4000"></textarea>
    </div>
    <?php if ($turnstileSiteKey !== null): ?>
      <div class="cf-turnstile" data-sitekey="<?= e($turnstileSiteKey) ?>" data-action="contact"></div>
    <?php endif; ?>
    <div class="field field-checkbox">
      <label>
        <input type="checkbox" name="consent" value="tak" required>
        <span>Wyrażam zgodę na przetwarzanie moich danych (imię, e-mail, treść wiadomości) w celu odpowiedzi na wiadomość, zgodnie z <a href="/polityka-prywatnosci#formularz-kontaktowy">polityką prywatności</a>.</span>
      </label>
    </div>
    <button type="submit" class="button button-accent">Wyślij wiadomość</button>
    <p class="form-status" data-contact-status role="status" aria-live="polite"></p>
  </form>
</dialog>
<script src="<?= e(asset_url('contact-form.js')) ?>" defer></script>
<script src="<?= e(asset_url('contact-modal.js')) ?>" defer></script>
<script src="<?= e(asset_url('interactions.js')) ?>" defer></script>
<?php if ($turnstileSiteKey !== null): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>
</body>
</html>
