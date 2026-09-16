<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; color: #111; }
  h1 { font-size: 14pt; margin-bottom: 4px; }
  .lead { font-size: 10pt; margin-top: 0; margin-bottom: 10px; }
  .rodo { font-size: 8pt; color: #333; border: 1px solid #999; padding: 6px 8px; margin-bottom: 14px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #333; padding: 4px 6px; font-size: 9pt; text-align: left; }
  th { background: #eee; }
  td.lp { width: 6%; text-align: center; }
  td.name { width: 32%; }
  td.city { width: 24%; }
  td.signature { width: 38%; }
  .row-cell { height: 24px; }
</style>
</head>
<body>
<h1><?= e($petition->title) ?></h1>
<p class="lead"><?= e($petition->lead) ?></p>

<div class="rodo">
  Administratorem danych osobowych zbieranych na niniejszej liście jest <?= e($organizer['name']) ?>,
  <?= e($organizer['address']) ?>, kontakt: <?= e($organizer['contactEmail']) ?>. Dane (imię, nazwisko,
  miejscowość) przetwarzane są wyłącznie w celu poparcia niniejszej petycji. Podanie danych jest
  dobrowolne. Podpisując listę, wyrażasz zgodę na przetwarzanie podanych danych w tym celu. Pełna
  polityka prywatności dostępna jest pod adresem radzymin.mleczki.pl/polityka-prywatnosci.
</div>

<table>
  <thead>
    <tr>
      <th class="lp">Lp.</th>
      <th class="name">Imię i nazwisko</th>
      <th class="city">Miejscowość</th>
      <th class="signature">Podpis</th>
    </tr>
  </thead>
  <tbody>
    <?php for ($i = 1; $i <= $rows; $i++): ?>
      <tr>
        <td class="lp row-cell"><?= $i ?></td>
        <td class="name row-cell"></td>
        <td class="city row-cell"></td>
        <td class="signature row-cell"></td>
      </tr>
    <?php endfor; ?>
  </tbody>
</table>
</body>
</html>
