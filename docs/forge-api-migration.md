# Forge API migration

Forge API v1 is deprecated and is scheduled to be discontinued on August 31, 2026. The application repository currently contains no direct Forge API client or `/api/v1` request, so the remaining migration work concerns operator tooling and external automation.

Official references:

- [Legacy API documentation and discontinuation notice](https://forge.laravel.com/api-documentation)
- [Current Forge API documentation](https://forge.laravel.com/docs/api-reference/introduction)

## Migration checklist

1. Inventory shell scripts, Hermes actions, scheduled jobs, webhooks, and CI secrets outside this repository for `forge.laravel.com/api/v1` usage.
2. Prefer a current Forge CLI command when it covers the operation. Keep the CLI updated through its supported installation method rather than replacing an application-bundled binary manually.
3. Migrate remaining HTTP integrations to the current organization-scoped API and its cursor pagination, resource identifiers, and permission model.
4. Store the Forge token only in the approved 1Password item or CI secret store. Never copy it into `.env`, repository files, command arguments, logs, or documentation.
5. Give automation the smallest Forge permissions needed and keep destructive production operations behind explicit approval.
6. Exercise read-only commands first, including organization, server, and site listing. Confirm the expected organization and production site before any mutation.
7. Test replacements in a non-production target, then compare outputs and failure behavior with the retired integration.
8. Remove old v1 code and revoke tokens that are no longer needed.

## Repository guardrail

Before merging operational automation, search the proposed changes for the legacy base path:

```bash
rg 'forge\.laravel\.com/api/v1|/api/v1' . --glob '!vendor/**' --glob '!node_modules/**' --glob '!docs/forge-api-migration.md'
```

An empty result is expected. If a legacy endpoint is deliberately documented, it must not be executable or contain a credential.
