# Tasks: Extract YouTube Audio (001-extract-youtube-audio)

This file contains executable tasks organized per user story. Each task follows the checklist format required by the speckit tooling.

Phase 1: Setup

- [X] T001 Install and verify native binaries in CI (smoke check) — create script at `scripts/ci/check-binaries.sh`
- [X] T002 Add CI job to run Pint, PHPStan (v9), and Pest in `.github/workflows/ci.yml` (or update existing CI)
- [X] T003 Ensure `routes/api.php` exists and add a note in `specs/001-extract-youtube-audio/quickstart.md` describing how to run the app locally
- [X] T004 Create `specs/001-extract-youtube-audio/tasks.md` (this file)

# Phase 2: Foundational (blocking prerequisites)

- [X] T005 Ensure migration `database/migrations/2025_10_30_000000_create_audit_logs_table.php` exists and matches schema (create if missing)
- [X] T006 Ensure model `app/Models/AuditLog.php` exists with `$fillable` for request_id, outcome, duration_ms, client_ip, input_url (update if missing)
- [X] T007 Add configuration `config/extractor.php` with keys: `rate_limit_per_minute`, `max_duration_minutes`, `audit_log_retention_days`
- [X] T008 Add route registration in `routes/api.php` for `POST /api/extract` pointing to `App\Http\Controllers\ExtractController` (ensure API middleware group applies)

Phase 3: [US1] Download audio from a public YouTube video (Priority: P1)

Story goal: Given a public YouTube URL, return an MP3 audio stream with correct download headers.

Independent test criteria: POST `/api/extract` with a valid URL returns HTTP 200, `Content-Type: audio/mpeg`, `Content-Disposition` includes filename, and response body is an audio stream (or stubbed stream in tests).

- [ ] T009 [US1] Create controller `app/Http/Controllers/ExtractController.php` with validation for `url` and dependency on `App\Services\Contracts\ExtractionServiceContract`
- [ ] T010 [US1] Create service contract `app/Services/Contracts/ExtractionServiceContract.php` (method `extract(string): array`)
- [ ] T011 [US1] Implement service `app/Services/ExtractionService.php` to implement the contract and return headers + stream placeholder
- [ ] T012 [US1] Add unit test `tests/Unit/ExtractionServiceTest.php` asserting `extract()` returns expected headers for a given URL (stub logger). Create test before implementation (per constitution).
- [ ] T013 [US1] Add feature test `tests/Feature/ExtractTest.php` that binds the contract to a stub implementation and posts to `/api/extract` asserting HTTP 200 and headers. Create test before implementation (per constitution).
- [ ] T014 [US1] Implement streaming response wiring in `ExtractController` using `ResponseFactory::stream()` to stream `ffmpeg` output (or stub for tests)
 - [X] T009 [US1] Create controller `app/Http/Controllers/ExtractController.php` with validation for `url` and dependency on `App\Services\Contracts\ExtractionServiceContract`
 - [X] T010 [US1] Create service contract `app/Services/Contracts/ExtractionServiceContract.php` (method `extract(string): array`)
 - [X] T011 [US1] Implement service `app/Services/ExtractionService.php` to implement the contract and return headers + stream placeholder
 - [X] T012 [US1] Add unit test `tests/Unit/ExtractionServiceTest.php` asserting `extract()` returns expected headers for a given URL (stub logger). Create test before implementation (per constitution).
 - [X] T013 [US1] Add feature test `tests/Feature/ExtractTest.php` that binds the contract to a stub implementation and posts to `/api/extract` asserting HTTP 200 and headers. Create test before implementation (per constitution).
 - [X] T014 [US1] Implement streaming response wiring in `ExtractController` using `ResponseFactory::stream()` to stream `ffmpeg` output (or stub for tests)
- [ ] T015 [US1] Add Pact/contract test: ensure `contracts/openapi.yaml` aligns with `routes/api.php` and controller behavior (file path: `specs/001-extract-youtube-audio/contracts/openapi.yaml`)

Additional Tasks (remediation additions)

- [ ] T025 Add response metadata headers (X-Request-Id, X-Response-Timestamp, X-Source-Host) in `app/Http/Controllers/ExtractController.php` and tests `tests/Feature/ExtractHeadersTest.php`
- [X] T026 Implement filename sanitization and derivation logic in `app/Services/ExtractionService.php` (unit tests in `tests/Unit/FilenameTest.php`)
- [ ] T027 Enforce MP3 output in `app/Services/ExtractionService.php` (ffmpeg args) and add integration test `tests/Feature/ExtractProducesMp3Test.php`
- [ ] T028 Implement process timeouts and resource limits for external calls (yt-dlp/ffmpeg) and add tests for timeout behavior (`app/Services/ExtractionService.php`, test: `tests/Feature/ExternalProcessTimeoutTest.php`)

Phase 4: [US3] Validate input and return helpful errors (Priority: P3)

Story goal: Provide clear JSON errors for malformed, unsupported, or restricted URLs.

Independent test criteria: POST `/api/extract` with invalid URL returns HTTP 400 and JSON body with `error`, `code`, and `request_id`.

- [ ] T016 [US3] Add request validation rules and custom exception formatting (`app/Exceptions/Handler.php` or a FormRequest) to return structured JSON errors
- [ ] T017 [US3] Add unit tests for validation failure paths `tests/Feature/ExtractValidationTest.php`
- [ ] T018 [US3] Implement mapping of yt-dlp/ffmpeg errors to HTTP status codes (400/404/422/502) in `app/Services/ExtractionService.php` and add tests

Final Phase: Polish & Cross-cutting Concerns

- [ ] T019 Add audit log writes in `ExtractController` or `ExtractionService` to create `AuditLog` rows after request completion (file: `app/Models/AuditLog.php` and `app/Services/ExtractionService.php`)
- [ ] T020 Add retention job `app/Console/Commands/PruneAuditLogs.php` and schedule in `app/Console/Kernel.php` to delete logs older than configured retention
- [ ] T021 Add rate limiting (10 req/min per IP) via middleware or `Route::middleware('throttle:extractor')` and configure in `App\Providers\RouteServiceProvider` or `bootstrap/app.php`
- [ ] T022 Document quickstart steps and binary requirements in `specs/001-extract-youtube-audio/quickstart.md` and top-level `README.md`
- [ ] T023 Add CI smoke check `scripts/ci/check-binaries.sh` and call it from CI workflow to fail early if `ffmpeg` or `yt-dlp` are missing
- [ ] T024 Run `vendor/bin/pint` and fix any style issues introduced by these changes

Dependencies (story completion order):

- Test-first note: For each story, create tests (unit + feature) before implementation tasks to satisfy the constitution (see T012/T013 ordering).
- Foundational tasks (T005..T008) must run before US1 implementation for DB and routing stability

Parallel execution examples:

- Backend developer A: implement T005, T006 (migration + model) while Backend developer B: implement T009..T011 (controller + contract + service) — these are parallelizable if service contract is agreed
- Add tests (T012, T013, T016, T017) can run in parallel with implementation using stubs/mocks

Implementation strategy

- MVP-first: implement US1 (core happy path) with stubs for external binaries, ensure tests pass. Once green, replace stubs with real orchestration calling `yt-dlp` + `ffmpeg` and add integration tests that use recorded fixtures or smoke-check guarded live runs.
- Incrementally add US3 (validation and error mapping), audit logging, rate-limiting, and pruning job.

---

Generated: 2025-10-30
