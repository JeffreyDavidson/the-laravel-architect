---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Keep controllers thin and resourceful
Controllers are standalone and expose only invokable or resourceful actions. Do not add private helper methods; move supporting behavior to view models, actions, queries, builders, or services.

## Pass validated input to the ViewModel, never call Queries
A page controller passes only validated input and bound models to its ViewModel, which calls the Queries it needs. Controllers do not inject or call App\Queries; tests/Architecture/ControllerArchitectureTest.php enforces this. The same applies to Livewire components (tests/Architecture/LivewireArchitectureTest.php): BlogIndex goes through PostIndexViewModel.

## Leave request checks to the route, middleware and FormRequest
Controllers only act on a request that is already trusted and normalised. Signature and token checks live in middleware on the route (`VerifyResendWebhookSignature`, `EnsureValidNewsletterConfirmationLink`) or in a model method (`Subscriber::hasConfirmationToken()`); tests/Architecture/ControllerArchitectureTest.php forbids `Hash`, `hash`, `hash_equals`, `hash_hmac` and `Resend\WebhookSignature` in controllers. Input normalisation, such as lower-casing an email, belongs in the FormRequest's `prepareForValidation()`. A nested model's ownership is enforced with `->scopeBindings()` on the route, not an `abort_unless()` comparing foreign keys.

## Use standalone singular controllers
Name resource controllers with the singular resource plus Controller, and keep application controllers as standalone classes without an empty shared base controller.
