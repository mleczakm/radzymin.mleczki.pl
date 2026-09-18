<?php
/**
 * @var string $shareUrl  absolute URL to share
 * @var string $shareText short message
 */
?>
<div class="share">
  <p class="share-label">Powiedz znajomym i sąsiadom:</p>
  <ul class="share-list">
    <?php foreach (share_links($shareUrl, $shareText) as $label => $href): ?>
      <li><a class="share-link" href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a></li>
    <?php endforeach; ?>
  </ul>
</div>
