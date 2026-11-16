---
paths:
  - 'app/Livewire/**'
---

# Livewire

## Pages are full-page Livewire components
Build each screen as a full-page Livewire class component with `#[Layout('layouts.app')]`, routed with `Route::livewire()`. Don't add controllers.

## Inject services into Livewire methods
Resolve services by type-hinting them on the Livewire method that needs them (`mount`, `render`, actions). Keep business logic in the service, not the component.

## Store preferences with Setting
Store user preferences and app state with `Setting::read($key, $default)` and `Setting::put($key, $value)`. Values are strings, so cast them on read.
