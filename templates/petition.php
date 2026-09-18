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
<p><a href="/">&larr; Wszystkie petycje</a></p>

<h1><?= e($petition->title) ?></h1>
<p class="lead"><?= e($petition->lead) ?></p>
<p><a class="button button-accent button-lg" href="#podpisz">Podpisz petycję</a></p>

<div class="petition-body"><?= $petition->bodyHtml ?></div>

<?= $progressHtml ?>

<div class="help-box">
  <h2>Pomóż zebrać podpisy</h2>
  <?= $partial('_share', [
      'shareUrl' => $baseUrl . '/petycja/' . $petition->slug,
      'shareText' => 'Podpisz petycję: ' . $petition->title,
  ]) ?>
  <p>
    Zbierasz podpisy wśród sąsiadów?
    <a href="/petycja/<?= e($petition->slug) ?>/lista.pdf">Pobierz listę do druku (PDF)</a>
    i przekaż mi wypełnioną — dopiszę podpisy do wyniku.
  </p>
</div>

<?= $recentSignaturesHtml ?>

<h2 id="podpisz">Podpisz petycję</h2>
<p class="disclaimer disclaimer-inline">
  Dane podajesz organizatorowi petycji (<a href="/polityka-prywatnosci">polityka prywatności</a>),
  a nie Gminie Radzymin — to niezależna inicjatywa, nie strona urzędowa.
</p>

<?php if (isset($errors['_global'])): ?>
  <p class="alert alert-error"><?= e($errors['_global']) ?></p>
<?php endif; ?>

<form method="post" action="/petycja/<?= e($petition->slug) ?>/podpisz" class="signature-form" novalidate>
  <div class="field hp-field" aria-hidden="true">
    <label for="<?= e($honeypotField) ?>">Strona WWW</label>
    <input type="text" id="<?= e($honeypotField) ?>" name="<?= e($honeypotField) ?>" tabindex="-1" autocomplete="off">
  </div>
  <input type="hidden" name="timing_token" value="<?= e($timingToken) ?>">

  <div class="field">
    <label for="first_name">Imię</label>
    <input type="text" id="first_name" name="first_name" required minlength="2" maxlength="100"
           value="<?= e($old['first_name'] ?? '') ?>">
    <?php if (isset($errors['first_name'])): ?><p class="field-error"><?= e($errors['first_name']) ?></p><?php endif; ?>
  </div>

  <div class="field">
    <label for="last_name">Nazwisko</label>
    <input type="text" id="last_name" name="last_name" required minlength="2" maxlength="100"
           value="<?= e($old['last_name'] ?? '') ?>">
    <?php if (isset($errors['last_name'])): ?><p class="field-error"><?= e($errors['last_name']) ?></p><?php endif; ?>
  </div>

  <div class="field">
    <label for="city">Miejscowość</label>
    <input type="text" id="city" name="city" required minlength="2" maxlength="100"
           value="<?= e($old['city'] ?? '') ?>">
    <?php if (isset($errors['city'])): ?><p class="field-error"><?= e($errors['city']) ?></p><?php endif; ?>
  </div>

  <div class="field">
    <label for="email">Adres e-mail</label>
    <input type="email" id="email" name="email" required maxlength="190"
           value="<?= e($old['email'] ?? '') ?>">
    <p class="field-hint">Na ten adres wyślemy link potwierdzający podpis.</p>
    <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
  </div>

  <div class="field field-checkbox">
    <label>
      <input type="checkbox" name="consent" value="1" required>
      <span>
        Wyrażam zgodę na przetwarzanie moich danych osobowych (imię, nazwisko, miejscowość, e-mail)
        w celu weryfikacji i złożenia podpisu pod petycją, zgodnie z
        <a href="/polityka-prywatnosci">polityką prywatności</a>.
      </span>
    </label>
    <?php if (isset($errors['consent'])): ?><p class="field-error"><?= e($errors['consent']) ?></p><?php endif; ?>
  </div>

  <button type="submit" class="button button-accent button-lg">Podpisz petycję</button>
</form>

<?= $partial('_contact_hint', ['contactSubject' => $petition->title]) ?>
