---
paths:
  - 'app/Providers/**'
---

# Providers

## Register services as singletons
Put business logic in plain classes under `app/Services` and register them as singletons in `StudyServiceProvider`. Put platform integrations behind an interface with a swappable implementation.
