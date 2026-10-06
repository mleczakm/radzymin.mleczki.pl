<?php
/**
 * @var \App\Domain\Petition $petition
 * @var string $progressHtml
 * @var string $recentSignaturesHtml
 * @var array<string, string> $errors
 * @var array<string, string> $old
 * @var string $timingToken
 * @var string $honeypotField
 * @var string $baseUrl
 * @var \Closure(string, array<string, mixed>=): string $partial
 */
?>
<p><a href="/#petycje">&larr; Wszystkie petycje</a></p>

<?php if ($errors !== []): ?>
  <div class="alert alert-error" id="form-errors" role="alert" tabindex="-1" data-form-errors>
    <h2>Nie udało się wysłać podpisu</h2>
    <p>Sprawdź poniższe informacje. Wpisane dane zostały zachowane.</p>
    <ul>
      <?php foreach ($errors as $field => $message): ?>
        <li><?php if ($field === '_global'): ?><?= e($message) ?><?php else: ?><a href="#<?= e($field) ?>"><?= e($message) ?></a><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<h1><?= e($petition->title) ?></h1>
<p class="lead"><?= e($petition->lead) ?></p>
<nav class="page-jumps" aria-label="Na tej stronie">
  <a class="button button-accent" href="#podpisz">Podpisz petycję</a>
  <a href="#tresc-petycji">Przeczytaj pełną treść</a>
</nav>

<div class="petition-body" id="tresc-petycji"><?= $petition->bodyHtml ?></div>

<?= $progressHtml ?>

<h2 id="podpisz">Podpisz petycję</h2>
<p>Wypełnij formularz, a następnie kliknij link w e-mailu. Dopiero wtedy Twój podpis zostanie policzony. Nie potrzebujesz konta.</p>
<p class="disclaimer disclaimer-inline">
  Podpis trafi do organizatora tej niezależnej inicjatywy, nie do Urzędu Gminy. Po potwierdzeniu e-mailem
  na stronie pokażemy Twoje imię, inicjał nazwiska i miejscowość. Szczegóły: <a href="/polityka-prywatnosci">polityka prywatności</a>.
</p>

<form method="post" action="/petycja/<?= e($petition->slug) ?>/podpisz" class="signature-form">
  <div class="field hp-field" aria-hidden="true">
    <label for="<?= e($honeypotField) ?>">Strona WWW</label>
    <input type="text" id="<?= e($honeypotField) ?>" name="<?= e($honeypotField) ?>" tabindex="-1" autocomplete="off">
  </div>
  <input type="hidden" name="timing_token" value="<?= e($timingToken) ?>">

  <div class="field">
    <label for="first_name">Imię</label>
    <input type="text" id="first_name" name="first_name" required minlength="2" maxlength="100"
           autocomplete="given-name"
           <?php if (isset($errors['first_name'])): ?>aria-invalid="true" aria-describedby="first_name-error"<?php endif; ?>
           value="<?= e($old['first_name'] ?? '') ?>">
    <?php if (isset($errors['first_name'])): ?><p class="field-error" id="first_name-error"><?= e($errors['first_name']) ?></p><?php endif; ?>
  </div>

  <div class="field">
    <label for="last_name">Nazwisko</label>
    <input type="text" id="last_name" name="last_name" required minlength="2" maxlength="100"
           autocomplete="family-name"
           <?php if (isset($errors['last_name'])): ?>aria-invalid="true" aria-describedby="last_name-error"<?php endif; ?>
           value="<?= e($old['last_name'] ?? '') ?>">
    <?php if (isset($errors['last_name'])): ?><p class="field-error" id="last_name-error"><?= e($errors['last_name']) ?></p><?php endif; ?>
  </div>

  <div class="field">
    <label for="city">Miejscowość</label>
    <input type="text" id="city" name="city" required minlength="2" maxlength="100"
           autocomplete="address-level2"
           <?php if (isset($errors['city'])): ?>aria-invalid="true" aria-describedby="city-error"<?php endif; ?>
           value="<?= e($old['city'] ?? '') ?>">
    <?php if (isset($errors['city'])): ?><p class="field-error" id="city-error"><?= e($errors['city']) ?></p><?php endif; ?>
  </div>

  <div class="field">
    <label for="email">Adres e-mail</label>
    <input type="email" id="email" name="email" required maxlength="190"
           autocomplete="email"
           aria-describedby="email-hint<?= isset($errors['email']) ? ' email-error' : '' ?>"
           <?php if (isset($errors['email'])): ?>aria-invalid="true"<?php endif; ?>
           value="<?= e($old['email'] ?? '') ?>">
    <p class="field-hint" id="email-hint">Na ten adres wyślemy link potwierdzający podpis.</p>
    <?php if (isset($errors['email'])): ?><p class="field-error" id="email-error"><?= e($errors['email']) ?></p><?php endif; ?>
  </div>

  <div class="field field-checkbox">
    <label>
      <input type="checkbox" id="consent" name="consent" value="1" required
             <?= ($old['consent'] ?? '') === '1' ? 'checked' : '' ?>
             <?php if (isset($errors['consent'])): ?>aria-invalid="true" aria-describedby="consent-error"<?php endif; ?>>
      <span>
        Wyrażam zgodę na przetwarzanie moich danych osobowych (imię, nazwisko, miejscowość, e-mail)
        w celu weryfikacji i złożenia podpisu pod petycją, zgodnie z
        <a href="/polityka-prywatnosci">polityką prywatności</a>.
      </span>
    </label>
    <?php if (isset($errors['consent'])): ?><p class="field-error" id="consent-error"><?= e($errors['consent']) ?></p><?php endif; ?>
  </div>

  <button type="submit" class="button button-accent button-lg">Podpisz petycję</button>
</form>

<div class="help-box">
  <div class="help-heading">
    <span class="help-icon"><?= $partial('_icon', ['icon' => 'people']) ?></span>
    <div><p class="help-eyebrow">Razem mamy większy głos</p><h2>Pomóż zebrać podpisy</h2></div>
  </div>
  <?= $partial('_share', [
      'shareUrl' => $baseUrl . '/petycja/' . $petition->slug,
      'shareText' => 'Podpisz petycję: ' . $petition->title,
      'qrUrl' => $baseUrl . '/petycja/' . $petition->slug . '/qr',
  ]) ?>
</div>

<?= $recentSignaturesHtml ?>

<?= $partial('_contact_hint', ['contactSubject' => $petition->title]) ?>
