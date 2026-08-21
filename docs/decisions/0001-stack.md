# 0001 — Language and framework

**Date:** 2026-08-21
**Status:** Accepted

## Decision

Laravel (PHP) for the API. React for the user interface, as a separate
client consuming that API. PostgreSQL as the database, as already decided.

## Why

- Easy to learn, which matters because the project starts with a developer
  who is fairly new.
- Deep, mature learning resources and documentation.
- Proven in the industry for this class of application.
- A large local talent pool, which matters because developers will be hired
  or outsourced later. A framework that can be staffed cheaply and quickly
  beats one that is marginally better on paper.

## Alternatives considered

**ASP.NET Core with C#** — the better architectural fit, because each module
is a separate project and cross-module reach-in is a compile error rather
than a convention. Rejected on hiring and learning grounds, which were
judged to outweigh it.

**NestJS with TypeScript** — one language across frontend and backend, right
structural ideas, but boundaries enforced by lint rather than the compiler,
so it shares Laravel's weakness without Laravel's hiring advantage here.

**Spring Modulith (JVM)** — best boundary verification available, purpose-built
for this architecture, but heaviest to learn and develop in for a small team.

## The cost we are accepting

Laravel does not enforce module boundaries, and several of its idioms lead
directly toward the coupling this architecture forbids. Specifically:

- There is no build-unit boundary by default; any class can import any other.
- Eloquent is active record, so a model is a database row with behaviour
  attached, and the framework's ergonomics assume you use models everywhere.
- Eloquent relations make a cross-schema join a single idiomatic line of
  code that no tool objects to.

## The conditions this decision depends on

The decision is only sound if the following are in place. They are not
optional, and they must exist before the first module is built. Details in
`Dev/laravel-guardrails.txt`.

1. Each module is a separate Composer package with a declared dependency
   list — not just a folder.
2. Deptrac runs in CI and fails the build on a boundary violation.
3. PHPStan at a high level from day one.
4. Eloquent models are confined to the infrastructure layer, behind
   repositories; relations never cross a module boundary.
5. One database connection per module, with its own credentials and
   `search_path`, and per-module migration paths.

Additionally, a transactional outbox must be built, since Laravel's
after-commit job dispatch loses the event if the process dies between the
commit and the dispatch.

## Consequences

- `Dev/system-design.txt` is unchanged. It is stack-independent, and every
  rule in it still applies.
- `Dev/laravel-guardrails.txt` states how each architectural rule is
  achieved in Laravel specifically, and which framework features are
  restricted as a result.
- Discipline that would be automatic in a compiled stack is now manual and
  tool-enforced. If the guardrails are ever removed or allowed to fail, the
  architecture degrades quickly and quietly.
