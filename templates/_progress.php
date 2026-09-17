<?php /** @var \App\Domain\Petition $petition */ ?>
<div class="progress-block">
  <?php if ($percent === null): ?>
    <p class="signature-count"><strong><?= (int) $confirmedCount ?></strong> potwierdzonych podpisów</p>
  <?php else: ?>
    <div class="progress-stats">
      <div class="progress-stat">
        <span class="progress-stat-value"><?= (int) $confirmedCount ?></span>
        <span class="progress-stat-label">z celu <?= (int) $petition->goal ?> podpisów</span>
      </div>
      <div class="progress-stat progress-stat-right">
        <span class="progress-stat-value"><?= (int) $percent ?>%</span>
        <?php if ($daysRemaining !== null): ?>
          <span class="progress-stat-label">pozostało <?= (int) $daysRemaining ?> dni</span>
        <?php endif; ?>
      </div>
    </div>
    <div class="progress-bar" role="progressbar" aria-valuenow="<?= (int) $percent ?>" aria-valuemin="0" aria-valuemax="100">
      <div class="progress-bar-fill" style="width: <?= (int) $percent ?>%"></div>
    </div>
    <?php $remaining = max(0, $petition->goal - $confirmedCount); ?>
    <?php if ($remaining > 0): ?>
      <p class="progress-remaining">Brakuje jeszcze <strong><?= (int) $remaining ?></strong> podpisów do osiągnięcia celu.</p>
    <?php else: ?>
      <p class="progress-remaining">Cel osiągnięty — dziękujemy wszystkim, którzy podpisali!</p>
    <?php endif; ?>
  <?php endif; ?>
</div>
