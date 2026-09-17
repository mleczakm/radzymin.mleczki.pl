<?php /** @var list<\App\Domain\Signature> $recentSignatures */ ?>
<?php if ($recentSignatures !== []): ?>
  <div class="recent-signatures">
    <h3>Niedawno podpisali</h3>
    <ul class="recent-signatures-list">
      <?php foreach ($recentSignatures as $signature): ?>
        <li><?= e($signature->publicDisplayName()) ?> <span class="recent-signatures-city">(<?= e($signature->city) ?>)</span></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
