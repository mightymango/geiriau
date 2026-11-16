---
paths:
  - 'resources/views/**'
---

# Views

## Welsh-first bilingual copy
Write UI text in Welsh with the English beneath or beside it, directly in Blade. Don't use translation files or `__()`.

## Colour with theme tokens
Style native views with theme tokens (`bg-theme-*`, `text-theme-*`, `border-theme-*`). Add any new colour as a token in both the `light` and `dark` blocks of `config/native-ui.php`, never as a hex value or palette class in a view.
