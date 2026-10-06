(() => {
  const dialog = document.querySelector('#contact-dialog');
  if (!(dialog instanceof HTMLDialogElement)) return;

  let opener = null;
  let closing = false;
  const close = async () => {
    if (!dialog.open || closing) return;
    closing = true;
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && typeof dialog.animate === 'function') {
      await dialog.animate([
        { opacity: 1, transform: 'translateY(0) scale(1)' },
        { opacity: 0, transform: 'translateY(8px) scale(.98)' },
      ], { duration: 150, easing: 'ease-in' }).finished.catch(() => {});
    }
    dialog.close();
    closing = false;
  };
  document.querySelectorAll('[data-contact-open]').forEach((link) => {
    link.addEventListener('click', (event) => {
      if (typeof dialog.showModal !== 'function') return;
      event.preventDefault();
      opener = link.closest('.mobile-nav')?.querySelector('summary') ?? link;
      if (!dialog.open) dialog.showModal();
      dialog.querySelector('input[name="email"]')?.focus();
    });
  });

  dialog.querySelector('[data-contact-close]')?.addEventListener('click', close);
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) close();
  });
  dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    close();
  });
  dialog.addEventListener('close', () => opener?.focus());
})();
