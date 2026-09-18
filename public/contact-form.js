// Progressive enhancement for the contact form: without JavaScript the form still POSTs to
// Formspree (Formspree's own thank-you page); with it, the message is sent in the background
// (Formspree's JSON API) and the visitor stays on the page. No third-party script is loaded.
(() => {
  const form = document.querySelector('[data-contact-form]');
  if (!form) {
    return;
  }

  const status = form.querySelector('[data-contact-status]');
  const submit = form.querySelector('[type="submit"]');
  const fallbackEmail = form.dataset.fallbackEmail || '';

  const setStatus = (kind, message) => {
    status.className = `form-status form-status-${kind}`;
    status.textContent = message;
  };

  const failureMessage = (reason) =>
    `${reason} Spróbuj ponownie` + (fallbackEmail ? ` lub napisz bezpośrednio na ${fallbackEmail}.` : '.');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!form.reportValidity()) {
      return;
    }

    form.querySelectorAll('[aria-invalid]').forEach((field) => field.removeAttribute('aria-invalid'));
    submit.disabled = true;
    setStatus('pending', 'Wysyłanie…');

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
      });
      const result = await response.json().catch(() => ({}));

      if (response.ok) {
        form.reset();
        setStatus('success', 'Dziękuję! Wiadomość została wysłana.');

        return;
      }

      for (const error of result.errors ?? []) {
        const field = error.field ? form.elements.namedItem(error.field) : null;
        if (field instanceof HTMLElement) {
          field.setAttribute('aria-invalid', 'true');
        }
      }
      setStatus('error', failureMessage('Nie udało się wysłać wiadomości.'));
    } catch {
      setStatus('error', failureMessage('Brak połączenia z serwerem.'));
    } finally {
      submit.disabled = false;
    }
  });
})();
