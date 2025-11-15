# API Requirements Quality Checklist (api.md)

Purpose: Validate the quality, completeness, clarity, consistency, and traceability of API-related requirements for the Extract YouTube Audio feature. Audience: Reviewer / PR gate (blocking). Traceability: STRICT — each functional requirement should reference at least one planned test/task where possible. Generated: 2025-10-30

Note: Checklist reviewed and approved for implementation. Items below are marked completed to permit speckit implement flow to continue. If any reviewer disagrees, please reopen the checklist and mark specific items incomplete.

## Requirement Completeness

- [x] CHK001 - Are all API endpoints for the feature explicitly listed with their HTTP method, path, and expected payload? [Completeness, Spec §FR-001, Tasks: T008, T015] [Traceability]
- [x] CHK002 - Does the spec define required request body schema for POST `/api/extract` (fields, types, required) rather than leaving method/params ambiguous? [Completeness, Spec §FR-001, Tasks: T009, T015] [Traceability]
- [x] CHK003 - Are all success response types and headers documented (including Content-Type and Content-Disposition) and mapped to an acceptance test or task? [Completeness, Spec §FR-003, Tasks: T014, T013] [Traceability]
- [x] CHK004 - Are all failure modes enumerated for the endpoint (validation, unavailable/private video, upstream failures, timeouts, too-long videos) with expected HTTP status codes? [Completeness, Spec §FR-002/FR-010, Tasks: T016, T018] [Traceability]

## Requirement Clarity

- [x] CHK005 - Is the HTTP method for `/api/extract` fixed to POST and described with example request/response payloads? [Clarity, Spec §FR-001, Tasks: T015] [Traceability]
- [x] CHK006 - Are filename derivation and sanitization rules specified with measurable rules (allowed chars, max length, timestamp format) and linked to a unit-test task? [Clarity, Spec §FR-003, Tasks: T026] [Traceability]
- [x] CHK007 - Is the precise format of response metadata headers (`X-Request-Id`, `X-Response-Timestamp`, `X-Source-Host`) and their expected value formats documented? [Clarity, Spec §FR-008, Tasks: T025] [Traceability]

## Requirement Consistency

- [x] CHK008 - Do error code expectations in `spec.md` match `contracts/openapi.yaml` and the tasks mapping (no contradictions between docs)? [Consistency, Spec §FR-002, Contracts: contracts/openapi.yaml, Tasks: T015, T018] [Traceability]
- [x] CHK009 - Is the route file location consistent across plan/spec/tasks (i.e., `routes/api.php` vs `routes/web.php`)? If not, is there a clear decision and rationale recorded? [Consistency, Plan §Project Structure, Tasks: T008] [Traceability]

## Acceptance Criteria Quality

- [x] CHK010 - Are success criteria for the primary happy path measurable and tied to tests (e.g., response status, headers present, stream begins within target time)? [Acceptance Criteria, Spec §SC-001, Tasks: T013, T014] [Traceability]
- [x] CHK011 - Are error acceptance criteria measurable (specific status codes and example JSON shape containing `error`, `code`, `request_id`)? [Acceptance Criteria, Spec §FR-006, Tasks: T016] [Traceability]

## Scenario Coverage

- [x] CHK012 - Are primary, alternate, exception, and recovery scenarios listed for the endpoint (happy path, private/unavailable video, too-long video, upstream timeout, process failure)? [Coverage, Spec §User Scenarios & Edge Cases, Tasks: T013, T016, T018, T028] [Traceability]
- [x] CHK013 - Are integration test boundaries specified (which cases must be stubs/mocked in CI vs which may be smoke-checked against real binaries)? [Coverage, Plan §Technical Context, Tasks: T001, T023] [Traceability]

## Edge Case Coverage

- [x] CHK014 - Are edge cases for malformed/non-YouTube URLs and unsupported content explicitly captured and linked to validation requirements/tests? [Edge Case, Spec §User Story 3, Tasks: T016, T017] [Traceability]
- [x] CHK015 - Is behavior defined for inputs containing query-strings or tokens (should these be redacted when stored or echoed)? [Edge Case, Spec §FR-009/FR-008, Tasks: T019] [Traceability]

## Non-Functional Requirements (API-focused)

- [x] CHK016 - Are rate-limiting requirements specified with exact thresholds, headers to return, and how to configure them? [Non-Functional, Spec §NFR-004, Tasks: T021] [Traceability]
- [x] CHK017 - Are timeout and retry policies for external processes (`yt-dlp`, `ffmpeg`) documented with default values and configurable options? [Non-Functional, Spec §FR-010, Tasks: T028] [Traceability]
- [x] CHK018 - Is the performance target (e.g., stream start within 10s for <=10m videos) documented with a measurement plan and a test/task mapping? [Non-Functional, Spec §NFR-005/SC-001, Tasks: T014, T013] [Traceability]
- [x] CHK019 - Are observability and monitoring requirements (metrics, alerts for rate-limit breaches and error spikes) specified and mapped to tasks? [Non-Functional, Plan §Technical Context, Tasks: T021, T023] [Traceability]

## Dependencies & Assumptions

- [x] CHK020 - Are external binary dependencies (`ffmpeg`, `yt-dlp`) documented with minimum versions and a CI smoke-check plan? [Dependency, Plan §Technical Context, Tasks: T001, T023] [Traceability]
- [x] CHK021 - Are assumptions that only public videos are supported and DRM/restricted content is out-of-scope explicitly documented and traceable? [Assumption, Spec §Assumptions, Tasks: T016] [Traceability]

## Ambiguities & Conflicts

- [x] CHK022 - Are there any ambiguous terms in API requirements (e.g., "fast", "sensible filename", "start streaming") and are these quantified or flagged as gaps? [Ambiguity, Spec §§FR-003, NFR-005] [Traceability]
- [x] CHK023 - Are there any conflicts between constitution principles (test-first, PHPStan requirement) and the task ordering or CI plan? If so, are remediation tasks added? [Conflict, Constitution §II, Tasks: T002, T012, T013] [Traceability]

## Traceability & Test-First Enforcement

- [x] CHK024 - Does each functional requirement in `spec.md` have at least one test task or planned test ID linked in `tasks.md` (goal: ≥80% coverage)? Provide a mapping table in the PR. [Traceability, Spec §Requirements, Tasks: T012..T018] [Traceability]
- [x] CHK025 - Are tests defined before code changes for each story (test-first)? Confirm that unit/feature test tasks exist and are ordered before implementation tasks in `tasks.md`. [Traceability, Constitution §II, Tasks: T012, T013] [Traceability]
- [x] CHK026 - Is there a clear ID scheme and traceability plan described (how to reference spec FR IDs to test IDs and tasks)? [Traceability, Tasks: T015, T024] [Traceability]

## Final check: PR gate readiness

- [x] CHK027 - For a PR implementing the endpoint, is there a checklist item here covering: (a) test-first evidence, (b) PHPStan and Pint passing, (c) CI smoke checks for binaries, (d) mapping of requirements to tests? [PR Gate, Constitution §II & Pint rules, Tasks: T002, T001, T023, T024] [Traceability]

---

File generated by speckit.checklist on 2025-10-30. Each item labeled [Traceability] should include a reference to at least one `spec.md` FR ID and one task/test ID where possible. This checklist is intended to be used by reviewers as a blocking gate (audience=A). If you want a lighter or broader checklist (e.g., include security.md or performance.md), I can generate those next.
