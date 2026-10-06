(() => {
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

})();
