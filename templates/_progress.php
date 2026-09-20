<?php
/**
 * @var \App\Domain\Petition $petition
 * @var int $confirmedCount
 * @var int|null $percent
 * @var int|null $daysRemaining
 */
?>
<div class="progress-block">
  <?php if ($percent === null): ?>
    <p class="signature-count"><strong><?= (int) $confirmedCount ?></strong> <?= e(plural_form($confirmedCount, 'potwierdzony podpis', 'potwierdzone podpisy', 'potwierdzonych podpisów')) ?></p>
  <?php else: ?>
    <div class="progress-stats">
      <div class="progress-stat">
        <span class="progress-stat-value"><?= (int) $confirmedCount ?></span>
        <span class="progress-stat-label">z celu <?= e(plural((int) $petition->goal, '%d podpisu', '%d podpisów', '%d podpisów')) ?></span>
      </div>
      <div class="progress-stat progress-stat-right">
        <span class="progress-stat-value"><?= (int) $percent ?>%</span>
        <?php if ($daysRemaining !== null): ?>
          <span class="progress-stat-label"><?= e(plural($daysRemaining, 'pozostał %d dzień', 'pozostały %d dni', 'pozostało %d dni')) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="progress-bar" role="progressbar" aria-valuenow="<?= (int) $percent ?>" aria-valuemin="0" aria-valuemax="100">
      <div class="progress-bar-fill" style="width: <?= (int) $percent ?>%"></div>
    </div>
    <?php $remaining = max(0, (int) $petition->goal - $confirmedCount); ?>
    <?php if ($remaining > 0): ?>
      <p class="progress-remaining">Brakuje jeszcze <strong><?= (int) $remaining ?></strong> <?= e(plural_form($remaining, 'podpisu', 'podpisów', 'podpisów')) ?> do osiągnięcia celu.</p>
    <?php else: ?>
      <p class="progress-remaining">Cel osiągnięty — dziękujemy wszystkim, którzy podpisali!</p>
    <?php endif; ?>
  <?php endif; ?>
</div>
