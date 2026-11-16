---
paths:
  - 'app/NativeComponents/**'
---

# Native components

## Screens are native components
Build each screen as a `NativeComponent` in `app/NativeComponents` with its view in `resources/views/native`, routed with `Route::native()` in `routes/mobile.php`. Tab screens use `AppLayout`. Don't add Livewire, controllers or web views.

## Inject services into mount and actions
Type-hint services on `mount()` and action methods. `render()` isn't injected, so resolve services there with `app()`. Keep business logic in the service, not the component.

## Switch tabs with replace, push everything else
Go to a tab screen with `$this->replace()` and push any other screen with `$this->navigate()`, so the back button only appears on pushed screens.

## Store preferences with Setting
Store user preferences and app state with `Setting::read($key, $default)` and `Setting::put($key, $value)`. Values are strings, so cast them on read.
