<?php
/**
 * @var string $shareUrl  absolute URL to share
 * @var string $shareText short message
 * @var string $qrUrl absolute URL to the printable QR page
 * @var \Closure(string, array<string, mixed>=): string $partial
 */
?>
<div class="share" data-share>
  <div class="share-grid">
    <div class="share-online">
      <p class="share-label">Przekaż dalej online</p>
      <p class="share-description">Zaproś znajomych i sąsiadów do podpisania.</p>
      <ul class="share-list">
        <?php foreach (share_links($shareUrl, $shareText) as $label => $href): ?>
          <li><a class="share-link" href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer">
            <?= $partial('_icon', ['icon' => $label]) ?><span><?= e($label) ?></span>
          </a></li>
        <?php endforeach; ?>
        <li hidden data-copy-item><button class="share-link share-copy" type="button" data-copy-url="<?= e($shareUrl) ?>">
          <?= $partial('_icon', ['icon' => 'copy']) ?><span data-copy-label>Kopiuj link</span>
        </button></li>
      </ul>
      <p class="share-feedback" data-share-status role="status" aria-live="polite"></p>
      <label class="share-manual" hidden>Link do skopiowania
        <input type="text" value="<?= e($shareUrl) ?>" readonly>
      </label>
    </div>
    <div class="share-offline">
      <p class="share-label">Działaj wśród sąsiadów</p>
      <a class="share-qr" href="<?= e($qrUrl) ?>" aria-label="Otwórz kartkę z kodem QR do druku">
        <span class="share-qr-image"><img src="<?= e(substr($qrUrl, 0, -3) . '/qr.svg') ?>" width="80" height="80" alt="Kod QR do petycji"></span>
        <span class="share-qr-copy"><strong>Kod QR do druku</strong><small>Wywieś i zaproś do podpisania</small></span>
        <?= $partial('_icon', ['icon' => 'arrow']) ?>
      </a>
      <a class="share-print" href="<?= e(substr($qrUrl, 0, -3) . '/lista') ?>">
        <?= $partial('_icon', ['icon' => 'print']) ?><span>Wydrukuj listę podpisów</span>
      </a>
      <p class="share-description share-paper-note">Przekaż mi wypełnioną listę — dopiszę podpisy do wyniku.</p>
    </div>
  </div>
</div>
