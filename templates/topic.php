<?php $percent = $topic->progressPercent(); ?>
<p><a href="/">&larr; Strona główna</a></p>

<div class="topic-card-head">
  <h1><?= e($topic->title) ?></h1>
  <span class="badge badge-<?= e($topic->status->value) ?>"><?= e($topic->status->label()) ?></span>
</div>

<?php if ($topic->institution !== null): ?>
  <p class="topic-institution"><?= e($topic->institution) ?></p>
<?php endif; ?>

<p class="lead"><?= e($topic->summary) ?></p>

<?php if ($percent !== null): ?>
  <div class="topic-progress">
    <div class="progress-bar" role="progressbar" aria-label="Postęp sprawy"
         aria-valuenow="<?= (int) $percent ?>" aria-valuemin="0" aria-valuemax="100">
      <div class="progress-bar-fill" style="width: <?= (int) $percent ?>%"></div>
    </div>
    <span class="topic-meta">Postęp: <?= (int) $percent ?>%</span>
  </div>
<?php endif; ?>

<div class="topic-body"><?= $topic->bodyHtml ?></div>

<?php if ($topic->steps !== []): ?>
  <h2>Przebieg sprawy</h2>
  <ol class="timeline">
    <?php foreach ($topic->steps as $step): ?>
      <li class="timeline-step<?= $step->done ? ' timeline-step-done' : '' ?>">
        <span class="timeline-marker" aria-hidden="true"></span>
        <div>
          <p class="timeline-title">
            <?php if ($step->done): ?><span class="visually-hidden">Zrealizowano: </span><?php endif; ?>
            <?= e($step->title) ?>
          </p>
          <?php if ($step->date !== null): ?>
            <p class="timeline-date"><?= e(format_date($step->date)) ?></p>
          <?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
<?php endif; ?>

<?php if ($topic->updatedAt !== null): ?>
  <p class="topic-meta">Ostatnia aktualizacja: <?= e(format_date($topic->updatedAt)) ?></p>
<?php endif; ?>
