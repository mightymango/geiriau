# geiriau/haptics

A local NativePHP Mobile plugin for light, native haptic feedback. NativePHP's built-in `Device::vibrate()` plays the full system buzz; this uses iOS's feedback generators (and Android's predefined effects) instead.

```php
use Geiriau\Haptics\Facades\Haptics;

Haptics::impact();        // light tap; also 'medium' or 'heavy'
Haptics::success();       // also warning() and error()
Haptics::selection();     // a selection changing
```

Calls do nothing off-device, so tests and `php artisan` are unaffected.
