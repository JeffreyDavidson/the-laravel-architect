---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Keep controllers thin and resourceful
Controllers are standalone and expose only invokable or resourceful actions. Do not add private helper methods; move supporting behavior to view models, actions, queries, builders, or services.

## Pass validated input to the ViewModel, never call Queries
A page controller passes only validated input and bound models to its ViewModel, which calls the Queries it needs. Controllers do not inject or call App\Queries; tests/Architecture/ControllerArchitectureTest.php enforces this. The same applies to Livewire components (tests/Architecture/LivewireArchitectureTest.php): BlogIndex goes through PostIndexViewModel.

## Use standalone singular controllers
Name resource controllers with the singular resource plus Controller, and keep application controllers as standalone classes without an empty shared base controller.
