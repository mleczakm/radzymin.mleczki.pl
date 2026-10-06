<?php
/**
 * @var array{heading: string, html: string} $about
 */
?>
<p><a href="/">&larr; Strona główna</a></p>
<section class="about-page">
  <img class="about-photo" src="/img/michal-mleczko.jpg" alt="Michał Mleczko, organizator Radzymińskich Petycji" width="360" height="480">
  <div>
    <h1><?= e($about['heading']) ?></h1>
    <div class="contact-about"><?= $about['html'] ?></div>
    <a class="button button-accent" href="/#kontakt" data-contact-open>Napisz do mnie</a>
  </div>
</section>
