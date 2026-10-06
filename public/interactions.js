(() => {
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');

  const errorSummary = document.querySelector('[data-form-errors]');
  errorSummary?.focus();
  errorSummary?.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
      const field = document.getElementById(link.hash.slice(1));
      if (field) {
        event.preventDefault();
        field.focus();
      }
    });
  });

  // Every card stays readable without JavaScript; filters only enhance the complete list.
  const filters = document.querySelector('.topic-filters');
  if (filters) {
    const cards = [...document.querySelectorAll('[data-topic-group]')];
    const status = document.querySelector('[data-topic-status]');
    const empty = document.querySelector('[data-topic-empty]');
    const buttons = [...filters.querySelectorAll('[data-topic-filter]')];
    const matches = (card, group) => group === 'all' || card.dataset.topicGroup === group;
    filters.querySelectorAll('[data-topic-count]').forEach((count) => {
      count.textContent = `(${cards.filter((card) => matches(card, count.dataset.topicCount)).length})`;
    });
    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        let visible = 0;
        cards.forEach((card) => {
          card.hidden = !matches(card, button.dataset.topicFilter);
          if (!card.hidden) visible++;
        });
        buttons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        if (empty) empty.hidden = visible !== 0;
        if (status) status.textContent = `Widoczne sprawy: ${visible} z ${cards.length}.`;
      });
    });
    filters.hidden = false;
  }

  // Clipboard is optional. A selectable URL remains available if access is refused.
  document.querySelectorAll('[data-share]').forEach((share) => {
    const button = share.querySelector('[data-copy-url]');
    const label = share.querySelector('[data-copy-label]');
    const status = share.querySelector('[data-share-status]');
    const manual = share.querySelector('.share-manual');
    const item = share.querySelector('[data-copy-item]');
    if (!button || !label || !status || !manual || !item) return;
    item.hidden = false;
    let resetTimer;

    button.addEventListener('click', async () => {
      clearTimeout(resetTimer);
      button.disabled = true;
      try {
        if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
        await navigator.clipboard.writeText(button.dataset.copyUrl);
        manual.hidden = true;
        button.classList.add('is-copied');
        label.textContent = 'Skopiowano';
        status.classList.remove('is-error');
        status.textContent = 'Link skopiowany. Prześlij go dalej!';
        resetTimer = setTimeout(() => {
          button.classList.remove('is-copied');
          label.textContent = 'Kopiuj link';
          status.textContent = '';
        }, 4000);
      } catch {
        button.classList.remove('is-copied');
        label.textContent = 'Kopiuj link';
        status.classList.add('is-error');
        status.textContent = 'Zaznacz i skopiuj link poniżej.';
        manual.hidden = false;
        manual.querySelector('input').select();
      } finally {
        button.disabled = false;
      }
    });
  });

  // Keep the native POST and server validation; indicate that the signature is being sent.
  document.querySelectorAll('.signature-form').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    if (!button) return;
    const initialLabel = button.textContent;
    form.addEventListener('submit', (event) => {
      if (event.defaultPrevented) return;
      if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
      }
      form.dataset.submitting = 'true';
      form.setAttribute('aria-busy', 'true');
      button.classList.add('is-pending');
      button.textContent = 'Wysyłanie podpisu…';
      button.disabled = true;
    });
    window.addEventListener('pageshow', () => {
      delete form.dataset.submitting;
      form.removeAttribute('aria-busy');
      button.classList.remove('is-pending');
      button.textContent = initialLabel;
      button.disabled = false;
    });
  });

  document.querySelectorAll('.mobile-nav-panel a').forEach((link) => {
    link.addEventListener('click', () => { link.closest('details').open = false; });
  });
  const mobileNav = document.querySelector('.mobile-nav');
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && mobileNav?.open) {
      mobileNav.open = false;
      mobileNav.querySelector('summary').focus();
    }
  });
  document.addEventListener('click', (event) => {
    if (mobileNav?.open && !mobileNav.contains(event.target)) mobileNav.open = false;
  });

  if (!('IntersectionObserver' in window)) return;
  const animations = new Set();
  const play = (element, frames, options) => {
    if (motion.matches || typeof element.animate !== 'function') return;
    const animation = element.animate(frames, options);
    animations.add(animation);
    animation.finished.then(() => animations.delete(animation), () => animations.delete(animation));
  };
  motion.addEventListener('change', () => {
    if (motion.matches) animations.forEach((animation) => animation.cancel());
  });
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      observer.unobserve(entry.target);
      const element = entry.target;
      const isProgress = element.matches('.progress-bar-fill');
      play(element, isProgress
        ? [{ transform: 'scaleX(0)' }, { transform: 'scaleX(1)' }]
        : [{ opacity: .35, transform: 'translateY(16px)' }, { opacity: 1, transform: 'translateY(0)' }],
      { duration: isProgress ? 850 : 500, easing: 'cubic-bezier(.22, 1, .36, 1)' });
    });
  }, { threshold: .08 });
  document.querySelectorAll('.hero-inner, .petition-card, .topic-card, .step, .help-box, .about-card, .contact-invite, .timeline li, .confirmed-page, .progress-bar-fill').forEach((element) => observer.observe(element));
})();
