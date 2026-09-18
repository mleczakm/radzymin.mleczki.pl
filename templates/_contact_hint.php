<?php
/**
 * @var array{name: string, address: string, contactEmail: string, phone: string|null} $organizer
 * @var string|null $contactSubject
 */
?>
<?php $subject = rawurlencode('Petycje Radzymin — ' . ($contactSubject ?? 'wiadomość')); ?>
<aside class="contact-hint">
  <p>
    <strong>Masz pytanie albo chcesz pomóc?</strong>
    Napisz do mnie wprost:
    <a href="mailto:<?= e($organizer['contactEmail']) ?>?subject=<?= $subject ?>"><?= e($organizer['contactEmail']) ?></a>.
    Chętnie odpowiem.
  </p>
</aside>
