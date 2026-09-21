// Reveals the print button (hidden without JavaScript; Ctrl/Cmd+P works either way).
for (const button of document.querySelectorAll('[data-print]')) {
  button.hidden = false;
  button.addEventListener('click', () => window.print());
}
