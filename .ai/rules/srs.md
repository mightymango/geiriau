---
paths:
  - 'app/Srs/**'
---

# Srs

## Keep the SRS engine free of Laravel
Code in `app/Srs` is plain PHP: final classes and value objects, with no Laravel helpers, facades or models. Convert to and from models at the service layer.
