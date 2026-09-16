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
