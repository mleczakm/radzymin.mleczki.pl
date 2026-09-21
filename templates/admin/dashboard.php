<?php
/**
 * @var list<array{petition: \App\Domain\Petition, confirmed: int, pending: int}> $rows
 */
?>
<h1>Panel administracyjny</h1>

<table class="admin-table">
  <thead>
    <tr>
      <th>Petycja</th>
      <th>Potwierdzone</th>
      <th>Oczekujące</th>
      <th>Akcje</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><a href="/petycja/<?= e($row['petition']->slug) ?>"><?= e($row['petition']->title) ?></a></td>
        <td><?= (int) $row['confirmed'] ?></td>
        <td><?= (int) $row['pending'] ?></td>
        <td>
          <a href="/admin/petycje/<?= e($row['petition']->slug) ?>/eksport.csv">Eksport CSV</a>
          &middot;
          <a href="/admin/petycje/<?= e($row['petition']->slug) ?>/papier">Dopisz z listy papierowej</a>
          &middot;
          <a href="/petycja/<?= e($row['petition']->slug) ?>/lista">Pusta lista do druku</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
