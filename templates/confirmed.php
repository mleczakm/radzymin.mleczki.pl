<h1><?= $alreadyConfirmed ? 'Ten podpis był już potwierdzony' : 'Dziękujemy! Podpis potwierdzony' ?></h1>

<?php if ($petition !== null): ?>
  <p>Twój podpis pod petycją „<?= e($petition->title) ?>” został <?= $alreadyConfirmed ? 'wcześniej ' : '' ?>zaliczony.</p>
  <?php if ($confirmedCount !== null): ?>
    <p class="signature-count"><strong><?= (int) $confirmedCount ?></strong> potwierdzonych podpisów</p>
  <?php endif; ?>
  <p><a href="/petycja/<?= e($petition->slug) ?>">&larr; Wróć do petycji</a></p>
<?php else: ?>
  <p>Twój podpis został zaliczony. <a href="/">Wróć na stronę główną</a></p>
<?php endif; ?>
