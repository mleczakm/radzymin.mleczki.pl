<?php
/**
 * @var array{name: string, address: string, contactEmail: string, phone: string|null} $organizer
 */
?>
<h1>Polityka prywatności</h1>

<p>Poniższe informacje dotyczą przetwarzania danych osobowych osób podpisujących petycje
w serwisie Petycje Radzymin, zgodnie z art. 13 RODO.</p>

<h2>Administrator danych</h2>
<p>
  <?= e($organizer['name']) ?><br>
  <?= e($organizer['address']) ?><br>
  Kontakt: <a href="mailto:<?= e($organizer['contactEmail']) ?>"><?= e($organizer['contactEmail']) ?></a>
</p>

<h2>Cel i podstawa przetwarzania</h2>
<p>Dane osobowe (imię, nazwisko, miejscowość, adres e-mail) podane w formularzu podpisu petycji
przetwarzane są w celu weryfikacji i złożenia podpisu pod petycją oraz — jeśli petycja tego wymaga —
przekazania jej wraz z listą podpisów właściwemu adresatowi. Podstawą przetwarzania jest zgoda
osoby podpisującej (art. 6 ust. 1 lit. a RODO).</p>

<h2>Odbiorcy danych</h2>
<p>Dane mogą zostać przekazane adresatowi petycji w zakresie niezbędnym do jej rozpatrzenia oraz
podmiotom technicznie obsługującym serwis (hosting, dostawca poczty e-mail używany wyłącznie
do wysyłki linku potwierdzającego).</p>

<h2>Publiczna lista „Niedawno podpisali”</h2>
<p>Po potwierdzeniu podpisu Twoje <strong>imię i pierwsza litera nazwiska</strong> (np. „Jan K.”)
oraz <strong>miejscowość</strong> mogą pojawić się publicznie na stronie petycji, na liście osób,
które ją niedawno podpisały. Pełne nazwisko i adres e-mail nigdy nie są tam publikowane.</p>

<h2>Okres przechowywania</h2>
<p>Dane przechowywane są przez czas trwania zbiórki podpisów oraz procedowania petycji przez jej
adresata, a następnie usuwane lub anonimizowane, chyba że dłuższe przechowywanie wynika z przepisów prawa.</p>

<h2>Prawa osoby, której dane dotyczą</h2>
<p>Przysługuje Ci prawo dostępu do danych, ich sprostowania, usunięcia, ograniczenia przetwarzania,
a także prawo do cofnięcia zgody w dowolnym momencie (co nie wpływa na zgodność z prawem
przetwarzania dokonanego przed jej cofnięciem) oraz prawo wniesienia skargi do Prezesa Urzędu
Ochrony Danych Osobowych.</p>

<h2>Dobrowolność podania danych</h2>
<p>Podanie danych jest dobrowolne, ale niezbędne do złożenia podpisu pod petycją.</p>

<h2>Podpisy zebrane papierowo</h2>
<p>Osoby podpisujące petycję na papierowej liście podają dane osobowe bezpośrednio administratorowi
danych, na tych samych zasadach jak opisano powyżej.</p>
