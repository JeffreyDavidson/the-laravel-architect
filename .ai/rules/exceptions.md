---
paths:
  - 'app/Exceptions/**'
---

# Exceptions

## Throw named domain exceptions from Actions
When an Action refuses because a business rule fails, throw a specific exception from app/Exceptions named after the failure (`ContentNotReadyToPublish`, `NewsletterIssueCannotBeSent`) instead of a generic `LogicException`. Make it final, extend `RuntimeException` (or `Exception`), build its message in the constructor or a named constructor, and carry the details a caller needs as readonly properties (`ContentNotReadyToPublish::$issues`). Filament actions and controllers catch it and show the message. The examples above now come from `jeffreydavidson/creator-kit`, so `app/Exceptions` is currently empty; `tests/Architecture/ExceptionArchitectureTest.php` still enforces final and the `Exception` parent for any exception TLA adds there.
