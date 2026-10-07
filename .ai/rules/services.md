---
paths:
  - 'app/Services/**'
---

# Services

## Let workflows own complete operations
A workflow service should own the full application operation it names, including external calls, persistence coordination, and aggregation across supported resources. Commands should not split that operation across multiple injected collaborators.

## Name long operations with the Workflow suffix
Name a service that runs one long operation over many records `…Workflow` (for example YouTubeVideoSyncWorkflow, PostImageGenerationWorkflow); do not use Synchronizer, Manager or Processor. Constructor-inject its collaborators and accept only a reporter closure for progress output; never take collaborators as callables or method arguments. Return a structured report and let the command render it.

## Keep generators pure and wrap external systems in integration services
A Generator or Renderer returns output (bytes or a string) and never writes files or the database; the workflow or action that calls it stores the result (FeaturedImageGenerator returns PNG bytes and PostImageGenerationWorkflow stores them). An integration service such as YouTubeService wraps an external system and makes no business decisions. A job is an async unit that loads its records and calls an action or service.
