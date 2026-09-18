<h1>Petycje mieszkańców Radzymina</h1>
<p class="lead">Podpisz petycję lub przekonaj się, ile osób już ją poparło.</p>

<?php if ($petitions === []): ?>
  <p>Obecnie nie ma żadnych aktywnych petycji.</p>
<?php else: ?>
  <ul class="petition-list">
    <?php foreach ($petitions as $slug => $petition): ?>
      <li class="petition-card">
        <h2><a href="/petycja/<?= e($slug) ?>"><?= e($petition->title) ?></a></h2>
        <p><?= e($petition->lead) ?></p>
        <p class="signature-count"><strong><?= (int) $counts[$slug] ?></strong> potwierdzonych podpisów</p>
        <a class="button" href="/petycja/<?= e($slug) ?>">Zobacz i podpisz</a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php if ($topics !== []): ?>
  <section class="topics">
    <h2>Czym się zajmuję</h2>
    <p class="lead">Sprawy, które prowadzę — wnioski, pisma i inicjatywy — wraz z aktualnym stanem.</p>

    <ul class="topic-list">
      <?php foreach ($topics as $topic): ?>
        <?php $percent = $topic->progressPercent(); ?>
        <li class="topic-card">
          <div class="topic-card-head">
            <h3><a href="/sprawy/<?= e($topic->slug) ?>"><?= e($topic->title) ?></a></h3>
            <span class="badge badge-<?= e($topic->status->value) ?>"><?= e($topic->status->label()) ?></span>
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
  </section>
<?php endif; ?>
