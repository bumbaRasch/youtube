<!--
Sync Impact Report
Version change: 1.0.0 -> 1.1.0
Modified principles: Framework & Tooling expanded to mandate code style tooling (Laravel Pint)
Added sections: explicit Code Style requirement (Pint) and Storage guidance (SQLite as dev default)
Removed sections: none (template placeholders filled)
Templates requiring updates: 
  - .specify/templates/plan-template.md ✅ updated (Constitution Check guidance aligned)
  - .specify/templates/spec-template.md ✅ updated (requirements & testing emphasis)
  - .specify/templates/tasks-template.md ✅ updated (task phases reflect chosen foundation)
Follow-up TODOs:
  - RATIFICATION_DATE left as TODO (original adoption date unknown)
  - Manual review: ensure any agent-specific command docs (if present) are neutralized
-->

# YouTube Audio Extractor Constitution

## Core Principles

### I. Framework & Tooling: Laravel-First
All application code MUST use Laravel (>=12.0) as the primary framework. Projects
MUST be structured following Laravel conventions (apps, config, routes,
service providers). Tooling MUST include PHPStan 9 for static analysis and the
MCP Laravel Boost conventions for project scaffolding where applicable. Rationale:
using a single, modern framework reduces cognitive overhead and enables
conventional defaults for routing, configuration, and testing.

### II. Test-First & Static Analysis (NON-NEGOTIABLE)
All new behavior MUST be covered by automated tests before merging: unit tests
for business logic and integration tests for HTTP endpoints and storage. Static
analysis with PHPStan (level configured per project) MUST pass on CI. Rationale:
Tests + static analysis prevent regressions and improve long-term maintainability.

### III. Minimal Surface & Single-Responsibility
Services and controllers MUST expose a minimal, well-documented surface area.
Each component SHOULD have a single responsibility. The application MUST
accept a YouTube URL and return a selectable audio file format (mp3, m4a, opus)
but SHOULD NOT include unrelated features (e.g., user accounts) unless
explicitly required and justified. Rationale: narrow scope reduces security
and maintenance burden and aligns with YAGNI.

### IV. Observability & Error Transparency
All HTTP endpoints and background jobs MUST emit structured logs and capture
errors with context (request id, URL, format requested). The app MUST return
clear, machine-readable error responses for client automation and human
readability in dev mode. Rationale: debugging and incident response are
significantly easier with consistent observability.

### V. Security, Privacy & Third-Party Compliance
Interactions with third-party services (YouTube fetching/downloading) MUST
adhere to the third-party's terms of service. Secrets MUST be stored via
environment configuration (no checked-in secrets). The app MUST validate and
sanitize inputs (YouTube URLs) and have rate-limiting/throttling guards. Rationale:
Protect users, the project, and hosting resources from abuse and legal risk.

## Technology Constraints

This project targets a server-side Linux environment with PHP 8.3+ (per Laravel
12 requirements). Primary stack:
- Framework: Laravel >= 12
- Static Analysis: PHPStan 9
- Scaffold / Helpers: MCP Laravel Boost (where useful)
- Audio processing: permitted native binaries (ffmpeg) or well-maintained PHP
	libraries that wrap them; any native dependencies MUST be documented in
	quickstart.md
- Output formats supported: mp3, m4a, opus

Code style and storage:

- Code style: Laravel Pint MUST be used for formatting and basic linting. CI
	MUST run Pint and fail on formatting errors where configured.
- Storage: SQLite is an acceptable development and test database (default).
	Any other persistent database choice (MySQL, PostgreSQL) MUST be documented
	and justified in the plan with migration instructions.

## Development Workflow & Quality Gates

- All PRs MUST include tests for new behavior and pass PHPUnit/Pest test
	suites on CI.
- PHPStan (configured at repository-level) MUST pass. CI MUST run static
	analysis and fail on errors.
- Code reviews MUST verify security considerations around external fetching and
	secrets handling.
- Performance/security-sensitive changes (e.g., fetching strategy, ffmpeg
	options) MUST include a short rationale and any benchmarks or safety checks.

- Laravel Pint MUST run on CI to enforce consistent code style across PRs.

## Governance

Amendments to this constitution are tracked in Git history and require a PR
that: documents the reason for change, includes a migration/rollout plan for
technical changes, and is approved by at least one other maintainer. Versioning
follows semantic versioning for governance documents:

- MAJOR: Breaking governance changes or principle removals/redefinitions
- MINOR: Addition of new principles or material expansions
- PATCH: Clarifications, typos, or non-semantic refinements

All PRs that change runtime behavior in a way that conflicts with the
constitution MUST reference the governance PR that amended the constitution.

**Version**: 1.0.0 | **Ratified**: TODO(RATIFICATION_DATE): original adoption date unknown | **Last Amended**: 2025-10-30
