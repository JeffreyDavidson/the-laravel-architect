---
paths:
  - 'app/Console/Commands/**'
---

# Commands

## Keep commands thin and operationally safe
Console commands should parse input, delegate application work, and report results. Long-running or potentially overlapping commands must use bounded iteration and isolation where appropriate.

## Name commands after actions without redundant suffixes
Name application command classes with imperative PascalCase actions and omit the redundant Command suffix. Keep the namespaced CLI signature as the public command interface, and use Description and handle() for the command contract.

## Treat commands as CLI adapters
Commands may parse arguments, enforce command-specific safety checks, delegate to application services, report progress, and return exit codes. Keep persistence loops, complex validation matrices, external synchronization, and multi-step workflows out of commands.

## Keep command orchestration inside workflows
Commands should not invoke other Artisan commands to implement application workflows. Put cross-resource orchestration in an application workflow or service, return a structured report, and let the command render output and choose the exit code.

## Use names that distinguish adjacent operations
When commands operate on the same resource, name them after the actual distinction in behavior, such as GenerateMissingPostImages versus GeneratePostImageVariants. Preserve existing signatures when a class rename does not require a public CLI rename.
