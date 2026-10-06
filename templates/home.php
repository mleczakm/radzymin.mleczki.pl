<?php
/**
 * @var array{name: string, address: string, contactEmail: string, phone: string|null, contactFormEndpoint: string|null} $organizer
 * @var array<string, \App\Domain\Petition> $petitions
 * @var array<string, int> $counts
 * @var array<string, int|null> $percents
 * @var \App\Domain\Petition|null $featured
 * @var array<string, \App\Domain\Topic> $topics
 * @var \Closure(string, array<string, mixed>=): string $partial
 */
?>
<?php
$phone = $organizer['phone'] ?? null;
$primaryHref = $featured !== null ? '/petycja/' . $featured->slug : '#sprawy';
?>
<section class="hero">
  <div class="wrap hero-inner">
    <p class="hero-eyebrow">Niezależny projekt społeczny Michała Mleczki</p>
    <h1><?= e($featured?->shortTitle ?? $featured?->title ?? 'Sprawy mieszkańców Radzymina') ?></h1>
    <p class="hero-lead">
      <?= e($featured?->homeSummary ?? $featured?->lead ?? 'Prowadzę lokalne sprawy mieszkańców i pokazuję ich przebieg. Masz pomysł? Napisz do mnie.') ?>
    </p>

    <div class="hero-actions">
      <?php if ($featured !== null): ?>
        <a class="button button-accent button-lg" href="<?= e($primaryHref) ?>">Poznaj pomysł i podpisz poparcie</a>
      <?php endif; ?>
      <?php if ($topics !== []): ?>
        <a class="button button-outline-light button-lg" href="#sprawy">Zobacz sprawy mieszkańców</a>
      <?php else: ?>
        <a class="button button-outline-light button-lg" href="#kontakt" data-contact-open>Napisz do mnie</a>
      <?php endif; ?>
    </div>

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
            <p><?= e($petition->homeSummary ?? $petition->lead) ?></p>

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

<?php if ($topics !== []): ?>
  <section class="section topics" id="sprawy">
    <div class="wrap">
      <h2>Sprawy mieszkańców</h2>
      <p class="section-lead">Przeczytaj, co udało się ustalić, na jakim etapie jest każda sprawa i jaki będzie kolejny krok.</p>

      <div class="topic-filters" role="group" aria-label="Filtruj sprawy" hidden>
        <button type="button" data-topic-filter="all" aria-pressed="true" aria-controls="topic-list">Wszystkie <span data-topic-count="all"></span></button>
        <button type="button" data-topic-filter="active" aria-pressed="false" aria-controls="topic-list">Otwarte <span data-topic-count="active"></span></button>
        <button type="button" data-topic-filter="closed" aria-pressed="false" aria-controls="topic-list">Zamknięte <span data-topic-count="closed"></span></button>
      </div>
      <p class="visually-hidden" data-topic-status role="status"></p>
      <p class="empty" data-topic-empty hidden>Brak spraw w tej kategorii. Wybierz „Wszystkie”, aby wrócić do pełnej listy.</p>
      <ul class="topic-list" id="topic-list" role="list">
        <?php foreach ($topics as $topic): ?>
          <?php $lastCompletedStep = null; $nextStep = null; ?>
          <?php foreach ($topic->steps as $step): ?>
            <?php if ($step->done): $lastCompletedStep = $step; elseif ($nextStep === null): $nextStep = $step; endif; ?>
          <?php endforeach; ?>
          <li class="topic-card" data-topic-group="<?= in_array($topic->status, [\App\Domain\TopicStatus::Completed, \App\Domain\TopicStatus::Rejected], true) ? 'closed' : 'active' ?>">
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
            <p class="topic-summary"><?= e($topic->summary) ?></p>
            <?php if ($lastCompletedStep !== null): ?>
              <p class="topic-outcome"><strong>Ostatnie zdarzenie:</strong> <?= e($lastCompletedStep->title) ?></p>
            <?php endif; ?>
            <?php if ($nextStep !== null): ?>
              <p class="topic-next"><strong>Następny krok:</strong> <?= e($nextStep->title) ?></p>
            <?php endif; ?>

            <p class="topic-meta">
              <?php if ($topic->updatedAt !== null): ?>Aktualizacja: <?= e(format_date($topic->updatedAt)) ?> &middot; <?php endif; ?>
              <a href="/sprawy/<?= e($topic->slug) ?>" aria-label="Szczegóły sprawy: <?= e($topic->title) ?>">Czytaj przebieg sprawy &rarr;</a>
            </p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endif; ?>

<section class="section section-tint" id="zaangazuj-sie">
  <div class="wrap">
    <h2>Jak możesz pomóc</h2>
    <p class="section-lead">Wybierz sposób działania, który Ci odpowiada.</p>

    <ul class="steps" role="list">
      <li class="step">
        <span class="step-icon"><?= $partial('_icon', ['icon' => 'people']) ?></span>
        <h3>Podpisz</h3>
        <p>To zajmuje około minuty. Wybierz petycję, wypełnij formularz i potwierdź podpis w e-mailu.</p>
      </li>
      <li class="step">
        <span class="step-icon"><?= $partial('_icon', ['icon' => 'print']) ?></span>
        <h3>Zbierz podpisy</h3>
        <p>
          Wydrukuj listę i zbierz podpisy wśród sąsiadów i znajomych.
          <?php if ($featured !== null): ?>
            <a href="/petycja/<?= e($featured->slug) ?>/lista">Otwórz listę do druku</a>.
          <?php endif; ?>
        </p>
      </li>
      <li class="step">
        <span class="step-icon"><?= $partial('_icon', ['icon' => 'copy']) ?></span>
        <h3>Powiedz dalej</h3>
        <p>Prześlij link znajomym, sąsiadom i lokalnym grupom. Na stronie każdej petycji jest gotowy przycisk udostępniania.</p>
      </li>
      <li class="step">
        <span class="step-icon"><?= $partial('_icon', ['icon' => 'E-mail']) ?></span>
        <h3>Napisz do mnie</h3>
        <p>Masz pomysł, problem albo chcesz działać razem? <a href="#kontakt" data-contact-open>Odezwij się</a> — chętnie porozmawiam.</p>
      </li>
    </ul>
  </div>
</section>

<section class="section contact" id="o-mnie">
  <div class="wrap">
    <div class="about-card about-card-compact">
      <img class="about-photo" src="/img/michal-mleczko.jpg" alt="Michał Mleczko, organizator Radzymińskich Petycji" width="360" height="480" loading="lazy">
      <div class="about-copy">
        <h2>Projekt społeczny mieszkańca Radzymina</h2>
        <p>Nazywam się Michał Mleczko. Zbieram podpisy i dokumentuję sprawy, które wpływają na codzienne życie mieszkańców.</p>
        <a class="button button-outline" href="/o-mnie">Poznaj mnie i założenia projektu</a>
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
