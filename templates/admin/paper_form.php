<?php
/**
 * @var \App\Domain\Petition $petition
 * @var int|null $added
 * @var list<string> $skipped
 */
?>
<p><a href="/admin">&larr; Panel administracyjny</a></p>

<h1>Dopisz podpisy z listy papierowej</h1>
<p>Petycja: <strong><?= e($petition->title) ?></strong></p>

<?php if ($added !== null): ?>
  <p class="alert alert-success">Dodano <?= (int) $added ?> podpis(ów).</p>
<?php endif; ?>

<?php if ($skipped !== []): ?>
  <p class="alert alert-error">Pominięto niektóre linie:</p>
  <ul>
    <?php foreach ($skipped as $line): ?>
      <li><?= e($line) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form method="post" action="/admin/petycje/<?= e($petition->slug) ?>/papier">
  <div class="field">
    <label for="lines">Jedna osoba na wiersz, w formacie: <code>Imię Nazwisko;Miejscowość</code></label>
    <textarea id="lines" name="lines" rows="12" placeholder="Jan Kowalski;Radzymin&#10;Anna Nowak;Ciemne"></textarea>
  </div>
  <button type="submit" class="button">Dodaj podpisy</button>
</form>
