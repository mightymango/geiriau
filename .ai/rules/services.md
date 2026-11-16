---
paths:
  - 'app/Services/**'
---

# Services

## Register services as singletons
Put business logic in plain classes under `app/Services` and register them as singletons in `StudyServiceProvider`. Put platform integrations behind an interface with a swappable implementation.

## Store preferences with Setting
Store user preferences and app state with `Setting::read($key, $default)` and `Setting::put($key, $value)`. Values are strings, so cast them on read.
