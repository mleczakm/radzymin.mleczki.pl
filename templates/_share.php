<?php
/**
 * @var string $shareUrl  absolute URL to share
 * @var string $shareText short message
 * @var string $qrUrl absolute URL to the printable QR page
 */
?>
<div class="share">
  <p class="share-label">Powiedz znajomym i sąsiadom:</p>
  <ul class="share-list">
    <?php foreach (share_links($shareUrl, $shareText) as $label => $href): ?>
      <li><a class="share-link" href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a></li>
    <?php endforeach; ?>
    <li><a class="share-qr" href="<?= e($qrUrl) ?>" aria-label="Otwórz kartkę z kodem QR do druku">
      <img src="<?= e(str_replace('/qr', '/qr.svg', $qrUrl)) ?>" width="72" height="72" alt="Kod QR do petycji">
      <span>Kod QR do druku</span>
    </a></li>
  </ul>
</div>
