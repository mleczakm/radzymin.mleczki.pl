<?php
/**
 * @var \App\Domain\Topic $topic
 */
?>
<?php
$assessments = $topic->responseAssessments();
$lastCompletedStep = null;
$nextStep = null;
foreach ($topic->steps as $step) {
  if ($step->done) { $lastCompletedStep = $step; continue; }
  if ($nextStep === null) { $nextStep = $step; }
}
?>
<p><a href="/#sprawy">&larr; Wszystkie sprawy mieszkańców</a></p>

<div class="topic-card-head">
  <h1><?= e($topic->title) ?></h1>
  <span class="badge badge-<?= e($topic->status->value) ?>"><?= e($topic->status->label()) ?></span>
</div>

<?php if ($topic->institution !== null): ?>
  <p class="topic-institution"><?= e($topic->institution) ?></p>
<?php endif; ?>

<p class="lead"><?= e($topic->summary) ?></p>

<?php if ($topic->updatedAt !== null || $lastCompletedStep !== null || $nextStep !== null): ?>
  <div class="topic-current">
    <?php if ($topic->updatedAt !== null): ?><p>Ostatnia aktualizacja: <strong><?= e(format_date($topic->updatedAt)) ?></strong></p><?php endif; ?>
    <?php if ($lastCompletedStep !== null): ?><p><strong>Ostatnie zdarzenie:</strong> <?= e($lastCompletedStep->title) ?></p><?php endif; ?>
    <?php if ($nextStep !== null): ?><p><strong>Następny krok:</strong> <?= e($nextStep->title) ?></p><?php endif; ?>
  </div>
<?php endif; ?>

<?php if ($topic->steps !== []): ?>
  <nav class="page-jumps" aria-label="Na tej stronie">
    <a href="#ustalenia">Treść i dokumenty</a>
    <a href="#przebieg">Przebieg sprawy</a>
  </nav>
<?php endif; ?>
<div class="topic-body" id="ustalenia"><?= $topic->bodyHtml ?></div>

<?php if ($topic->steps !== []): ?>
  <h2 id="przebieg">Przebieg sprawy</h2>
  <ol class="timeline">
    <?php foreach ($topic->steps as $index => $step): ?>
      <?php $assessment = $assessments[$index] ?? null; ?>
      <li class="timeline-step<?= $step->done ? ' timeline-step-done' : '' ?><?= $assessment !== null ? ' timeline-step-' . e($assessment->timing->value) : '' ?>">
        <span class="timeline-marker" aria-hidden="true"></span>
        <div>
          <p class="timeline-title">
            <?php if ($step->done): ?><span class="visually-hidden">Zrealizowano: </span><?php endif; ?>
            <?= e($step->title) ?>
          </p>
          <?php if ($step->date !== null): ?>
            <p class="timeline-date"><?= e(format_date($step->date)) ?></p>
          <?php endif; ?>
          <?php if ($assessment !== null): ?>
            <p class="timing timing-<?= e($assessment->timing->value) ?>">
              <strong class="timing-label"><?= e($assessment->label()) ?></strong>
              <span><?= e($assessment->summary()) ?></span>
            </p>
          <?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
<?php endif; ?>
