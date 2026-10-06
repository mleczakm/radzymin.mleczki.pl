<?php /** @var string $icon */ ?>
<svg class="ui-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
  <?php switch ($icon): case 'WhatsApp': ?>
    <path d="M20 11.5a8 8 0 0 1-12 7L3 20l1.5-5A8 8 0 1 1 20 11.5Z"/><path d="m8 7 2 3-1 1c1 2 2 3 4 4l1-1 3 2c-2 3-8-1-10-5-1-2 0-3 1-4Z"/>
  <?php break; case 'Facebook': ?>
    <path d="M14 21v-8h3l.5-4H14V7c0-1 .5-2 2-2h2V1h-3c-4 0-5 2-5 5v3H7v4h3v8"/>
  <?php break; case 'E-mail': ?>
    <rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/>
  <?php break; case 'copy': ?>
    <rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>
  <?php break; case 'print': ?>
    <path d="M7 8V3h10v5M7 17H3V9h18v8h-4"/><path d="M7 14h10v7H7zM17 11h1"/>
  <?php break; case 'arrow': ?>
    <path d="M5 12h14m-6-6 6 6-6 6"/>
  <?php break; case 'people': ?>
    <circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"/>
  <?php break; endswitch; ?>
</svg>
