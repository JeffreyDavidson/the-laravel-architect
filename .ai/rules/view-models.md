---
paths:
  - 'app/ViewModels/**'
---

# View Models

## Let page ViewModels own payloads and SEO
Page ViewModels assemble the view payload and own SEO metadata, including seoSource. Controllers should inject them and translate the HTTP request and response.
