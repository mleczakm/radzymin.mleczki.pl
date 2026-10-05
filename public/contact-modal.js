(() => {
  const dialog = document.querySelector('#contact-dialog');
  if (!(dialog instanceof HTMLDialogElement)) return;

  let opener = null;
  document.querySelectorAll('[data-contact-open]').forEach((link) => {
    link.addEventListener('click', (event) => {
      if (typeof dialog.showModal !== 'function') return;
      event.preventDefault();
      opener = link;
      dialog.showModal();
      dialog.querySelector('input[name="email"]')?.focus();
    });
  });

  dialog.querySelector('[data-contact-close]')?.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
  dialog.addEventListener('close', () => opener?.focus());
})();
