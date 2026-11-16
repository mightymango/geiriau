---
paths:
  - 'routes/**'
---

# Routes

## Screens are native components
Build each screen as a `NativeComponent` in `app/NativeComponents` with its view in `resources/views/native`, routed with `Route::native()` in `routes/mobile.php`. Tab screens use `AppLayout`. Don't add Livewire, controllers or web views.
