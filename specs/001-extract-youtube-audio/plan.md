# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary
The feature implements a minimal, fast, and compliant MVP that exposes a single HTTP endpoint to extract audio from a public YouTube video and return it as an MP3 download. Implementation will orchestrate a downloader (`yt-dlp`) to fetch the source stream and `ffmpeg` for conversion to MP3. The service will be API-only and designed to be stateless. Key non-functional constraints: per-IP rate limit (10 req/min), synchronous processing only for videos <= 30 minutes (longer videos rejected with HTTP 422), monitoring and audit logs (30-day retention).

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: PHP 8.4.13 (align with Laravel 12 requirement). Ensure CI/containers use compatible PHP runtime.
**Primary Dependencies**: laravel/framework v12, phpstan (v9), laravel/pint, pestphp/pest. Runtime native binaries: `ffmpeg`, `yt-dlp` (document versions in quickstart).
**Storage**: SQLite for development and tests. A single persistent table `audit_logs` for minimal audit events (configurable retention). Production DB (Postgres/MySQL) optional and must be documented.
**Testing**: Pest for unit and feature tests. CI steps: Pint -> PHPStan (v9) -> Pest/PHPUnit.
**Target Platform**: Linux server / container (Docker) with PHP-FPM or artisan serve for workers.
**Project Type**: Backend web API (Laravel controllers, services). No frontend/UI in MVP.
**Performance Goals**: 95% of videos <=10 minutes start streaming within 10s. System supports typical small-scale concurrent requests; ensure monitoring/alerts for spikes.
**Constraints**: Synchronous processing only for videos <=30 minutes. Rate limit default: 10 req/min per IP. No user accounts for MVP.
**Scale/Scope**: MVP targets low-to-moderate traffic; design for horizontal scaling (stateless workers), but production scaling plan to be defined later.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Gates determined based on constitution file:

- Framework: Project MUST target Laravel >= 12.0.  [PASS — repository uses Laravel layout; plan implements feature within Laravel app]
- Static analysis: PHPStan 9 MUST run and report no errors on CI for new code.  [PASS — CI steps will include PHPStan v9; code changes must be type-checked]
- Scope: Feature plan MUST justify any scope expansion beyond returning an audio file from a YouTube URL. Any additional user-models or account systems MUST be justified in Complexity Tracking.  [PASS — MVP scope constrained to a single API endpoint, mp3-only, no accounts]

Ensure the Plan documents native dependencies (ffmpeg, yt-dlp) required to produce audio outputs and any hosting/runtime considerations.  [PASS — documented in Technical Context and Implementation Notes]

Additional gates:

- Code style: Project MUST include Laravel Pint configuration; the Plan SHOULD note repository-specific formatting rules.  [PASS]
- Storage: Use SQLite as development/test DB by default; any other persistent DB choice must be documented and justified.  [PASS]

No constitution violations detected; proceed to Phase 0 research.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
# [REMOVE IF UNUSED] Option 1: Single project (DEFAULT)
src/
├── models/
├── services/
├── cli/
└── lib/

tests/
├── contract/
├── integration/
└── unit/

# [REMOVE IF UNUSED] Option 2: Web application (when "frontend" + "backend" detected)
backend/
├── src/
│   ├── models/
│   ├── services/
│   └── api/
└── tests/

frontend/
├── src/
│   ├── components/
│   ├── pages/
│   └── services/
└── tests/

# [REMOVE IF UNUSED] Option 3: Mobile + API (when "iOS/Android" detected)
api/
└── [same as backend above]

ios/ or android/
└── [platform-specific structure: feature modules, UI flows, platform tests]
```

**Structure Decision**: Use the existing Laravel app layout. Concrete paths:

- `app/Http/Controllers/ExtractController.php` — controller for `/api/extract`
- `app/Services/ExtractionService.php` — orchestrates downloader and ffmpeg
- `app/Models/AuditLog.php` — Eloquent model for audit logs
- `database/migrations/xxxx_xx_xx_create_audit_logs_table.php` — migration
- `routes/api.php` — register API route
- `tests/Feature/ExtractTest.php` — integration tests
- `tests/Unit/ExtractionServiceTest.php` — unit tests for core logic

This keeps the implementation inside the existing Laravel structure and aligns with the constitution.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
