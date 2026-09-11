# Data transfer objects

Native `final readonly` classes in `app/Data` describe data crossing application boundaries. They do not fetch data, send mail, or depend on HTTP requests.

- `StoreContactRequest::toData()` maps validated input into `ContactMessageData`, retaining `ContactType` and nullable `ContactBudget` enums. The controller calls it only after honeypot, rate-limit, and Turnstile checks. `SendContactMessage::handle()` accepts that DTO and converts enums to their existing string values at the mailable boundary. Existing queued-mail fields and rendered content remain unchanged.
- `YouTubeService` normalizes external responses into `YouTubeVideoData` objects. The sync command reads typed properties and uses the explicit `toArray()` mapping for new records. Updates continue to preserve curated slugs, publication dates, and featured status. Missing optional API fields remain null; malformed statistics retain the existing zero fallback.

Simple newsletter arguments, ViewModel payloads, and YouTube's separate statistics arrays remain unchanged. Do not add DTOs automatically for every array or introduce a shared DTO base class.
