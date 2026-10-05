<?php
/**
 * @var array{name: string, address: string, contactEmail: string, phone: string|null, contactFormEndpoint: string|null} $organizer
 * @var array<string, \App\Domain\Petition> $petitions
 * @var array<string, int> $counts
 * @var array<string, int|null> $percents
 * @var \App\Domain\Petition|null $featured
 * @var int $totalSignatures
 * @var int $activeTopicCount
 * @var array<string, \App\Domain\Topic> $topics
 * @var array{heading: string, html: string}|null $about
 */
?>
<?php
$phone = $organizer['phone'] ?? null;
$primaryHref = $featured !== null ? '/petycja/' . $featured->slug : '#sprawy';
?>
<section class="hero">
  <div class="wrap hero-inner">
    <p class="hero-eyebrow">Niezależna inicjatywa mieszkańców Radzymina</p>
    <h1>Twój podpis <span class="hero-highlight">ma znaczenie</span></h1>
    <p class="hero-lead">
      Wspólnie możemy więcej. Podpisz petycję w kilka minut, pomóż zebrać kolejne podpisy
      i napisz do mnie, jeśli masz sprawę, którą warto załatwić w Radzyminie.
    </p>

    <div class="hero-actions">
      <?php if ($featured !== null): ?>
        <a class="button button-accent button-lg" href="<?= e($primaryHref) ?>">Podpisz petycję</a>
      <?php endif; ?>
      <a class="button button-outline-light button-lg" href="#kontakt" data-contact-open>Napisz do mnie</a>
    </div>

    <?php if ($totalSignatures > 0 || $activeTopicCount > 0): ?>
      <dl class="hero-stats">
        <?php if ($totalSignatures > 0): ?>
          <div><dt><?= e(plural_form($totalSignatures, 'podpis zebrany', 'podpisy zebrane', 'podpisów zebranych')) ?></dt><dd><?= (int) $totalSignatures ?></dd></div>
        <?php endif; ?>
        <?php if ($activeTopicCount > 0): ?>
          <div><dt><?= e(plural_form($activeTopicCount, 'sprawa w toku', 'sprawy w toku', 'spraw w toku')) ?></dt><dd><?= (int) $activeTopicCount ?></dd></div>
        <?php endif; ?>
      </dl>
    <?php endif; ?>
  </div>
</section>

<section class="section" id="petycje">
  <div class="wrap">
    <h2>Petycje do podpisania</h2>
    <p class="section-lead">
      Przeczytaj treść i podpisz. Potrzebujemy tylko imienia, nazwiska, miejscowości i adresu e-mail —
      podpis potwierdzasz kliknięciem w link z wiadomości, bez zakładania konta.
    </p>

    <?php if ($petitions === []): ?>
      <p class="empty">Obecnie nie ma aktywnych petycji. Masz pomysł na petycję?
        <a href="#kontakt" data-contact-open>Napisz do mnie</a>.</p>
    <?php else: ?>
      <ul class="petition-list">
        <?php foreach ($petitions as $petition): ?>
          <?php $slug = $petition->slug; ?>
          <?php $percent = $percents[$slug]; ?>
          <li class="petition-card">
            <h3><a href="/petycja/<?= e($slug) ?>"><?= e($petition->title) ?></a></h3>
            <p><?= e($petition->lead) ?></p>

            <?php if ($percent !== null): ?>
              <div class="card-progress">
                <div class="progress-bar" role="progressbar" aria-label="Postęp zbierania podpisów"
                     aria-valuenow="<?= (int) $percent ?>" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar-fill" style="width: <?= (int) $percent ?>%"></div>
                </div>
                <p class="card-progress-label">
                  <strong><?= (int) $counts[$slug] ?></strong> z <?= e(plural((int) $petition->goal, '%d podpisu', '%d podpisów', '%d podpisów')) ?> (<?= (int) $percent ?>%)
                </p>
              </div>
            <?php else: ?>
              <p class="signature-count"><strong><?= (int) $counts[$slug] ?></strong> <?= e(plural_form($counts[$slug], 'potwierdzony podpis', 'potwierdzone podpisy', 'potwierdzonych podpisów')) ?></p>
            <?php endif; ?>

            <a class="button button-accent" href="/petycja/<?= e($slug) ?>">Przeczytaj i podpisz</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<section class="section section-tint" id="zaangazuj-sie">
  <div class="wrap">
    <h2>Jak możesz pomóc</h2>
    <p class="section-lead">Każdy głos się liczy — a im więcej osób się zaangażuje, tym trudniej sprawę pominąć.</p>

    <ol class="steps">
      <li class="step">
        <span class="step-number" aria-hidden="true">1</span>
        <h3>Podpisz</h3>
        <p>To zajmuje około minuty. Wybierz petycję, wypełnij formularz i potwierdź podpis w e-mailu.</p>
      </li>
      <li class="step">
        <span class="step-number" aria-hidden="true">2</span>
        <h3>Zbierz podpisy</h3>
        <p>
          Wydrukuj listę i zbierz podpisy wśród sąsiadów i znajomych.
          <?php if ($featured !== null): ?>
            <a href="/petycja/<?= e($featured->slug) ?>/lista">Otwórz listę do druku</a>.
          <?php endif; ?>
        </p>
      </li>
      <li class="step">
        <span class="step-number" aria-hidden="true">3</span>
        <h3>Powiedz dalej</h3>
        <p>Prześlij link znajomym, sąsiadom i lokalnym grupom. Na stronie każdej petycji jest gotowy przycisk udostępniania.</p>
      </li>
      <li class="step">
        <span class="step-number" aria-hidden="true">4</span>
        <h3>Napisz do mnie</h3>
        <p>Masz pomysł, problem albo chcesz działać razem? <a href="#kontakt" data-contact-open>Odezwij się</a> — chętnie porozmawiam.</p>
      </li>
    </ol>
  </div>
</section>

<?php if ($topics !== []): ?>
  <section class="section topics" id="sprawy">
    <div class="wrap">
      <h2>Czym się zajmuję</h2>
      <p class="section-lead">Sprawy, które prowadzę — wnioski, pisma i inicjatywy — wraz z aktualnym stanem.</p>

      <ul class="topic-list">
        <?php foreach ($topics as $topic): ?>
          <?php $percent = $topic->progressPercent(); ?>
          <li class="topic-card">
            <div class="topic-card-head">
              <h3><a href="/sprawy/<?= e($topic->slug) ?>"><?= e($topic->title) ?></a></h3>
              <span class="badge badge-<?= e($topic->status->value) ?>"><?= e($topic->status->label()) ?></span>
              <?php if ($topic->hasLateResponse()): ?>
                <span class="badge badge-late">Odpowiedź po terminie</span>
              <?php endif; ?>
            </div>
            <?php if ($topic->institution !== null): ?>
              <p class="topic-institution"><?= e($topic->institution) ?></p>
            <?php endif; ?>
            <p><?= e($topic->summary) ?></p>

            <?php if ($percent !== null): ?>
              <div class="topic-progress">
                <div class="progress-bar" role="progressbar" aria-label="Postęp sprawy"
                     aria-valuenow="<?= (int) $percent ?>" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar-fill" style="width: <?= (int) $percent ?>%"></div>
                </div>
                <span class="topic-meta">Postęp: <?= (int) $percent ?>%</span>
              </div>
            <?php endif; ?>

            <p class="topic-meta">
              <?php if ($topic->updatedAt !== null): ?>Aktualizacja: <?= e(format_date($topic->updatedAt)) ?> &middot; <?php endif; ?>
              <a href="/sprawy/<?= e($topic->slug) ?>">Szczegóły &rarr;</a>
            </p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endif; ?>

<section class="section contact" id="o-mnie">
  <div class="wrap">
    <div class="about-card">
      <img class="about-photo" src="/img/michal-mleczko.jpg" alt="Michał Mleczko, organizator Radzymińskich Petycji" width="360" height="480" loading="lazy">
      <div class="about-copy">
        <h2><?= e($about['heading'] ?? 'O mnie') ?></h2>
        <?php if ($about !== null): ?><div class="contact-about"><?= $about['html'] ?></div><?php endif; ?>
        <a class="button button-accent" href="#kontakt" data-contact-open>Napisz do mnie</a>
      </div>
    </div>
  </div>
</section>

<section class="section contact" id="kontakt">
  <div class="wrap contact-invite">
    <h2>Masz pomysł albo sprawę do omówienia?</h2>
    <p>Napisz do mnie — chętnie porozmawiam o tym, co możemy wspólnie zmienić w Radzyminie.</p>
    <a class="button button-accent button-lg" href="mailto:<?= e($organizer['contactEmail']) ?>" data-contact-open>Napisz do mnie</a>
    <p class="contact-address"><?= e($organizer['contactEmail']) ?><?php if ($phone): ?> · <a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?></p>
  </div>
</section>
