<div class="confirmed-page">
  <div class="confirmed-icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="4 13 9 18 20 6"></polyline>
    </svg>
  </div>

  <h1><?= $alreadyConfirmed ? 'Ten podpis był już potwierdzony' : 'Twój podpis został potwierdzony!' ?></h1>

  <?php if ($petition !== null): ?>
    <p class="confirmed-lead">
      Dziękujemy — Twój głos pod petycją „<strong><?= e($petition->title) ?></strong>” został
      <?= $alreadyConfirmed ? 'wcześniej' : 'właśnie' ?> doliczony do zbiórki.
    </p>
    <p class="confirmed-note">
      Pojawisz się na liście „Niedawno podpisali” — Twoje imię i pierwsza litera nazwiska są widoczne publicznie.
    </p>

    <?= $progressHtml ?>

    <p><a href="/petycja/<?= e($petition->slug) ?>">&larr; Wróć do petycji</a></p>
  <?php else: ?>
    <p class="confirmed-lead">Twój podpis został zaliczony.</p>
    <p><a href="/">Wróć na stronę główną</a></p>
  <?php endif; ?>
</div>
