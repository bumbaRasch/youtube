# Feature Specification: Extract YouTube Audio

**Feature Branch**: `001-extract-youtube-audio`  
**Created**: 2025-10-30  
**Status**: Draft  
**Input**: User description: "I want to build an app that extracts audio tracks from YouTube videos and returns them as downloadable files. The goal is to create a minimal, fast, and compliant MVP using Laravel 12+, PHPStan 9, and MCP Laravel Boost."

## Clarifications

### Session 2025-10-30

- Q: Access control / abuse protection → A: Public anonymous endpoint with per-IP rate limit (10 requests per minute) and monitoring/alerts for suspicious activity.
 - Q: Long-video handling → A: Reject videos longer than 30 minutes synchronously with HTTP 422 (client error). This keeps MVP processing short and predictable; async/queueing can be added later.
 - Q: Extraction tooling → A: Require a system `ffmpeg` binary (documented version) for audio extraction; CI must verify availability. This provides reliable, well‑tested conversion for MVP.
 - Q: Downloader tooling → A: Require a downloader tool (recommend `yt-dlp`) to fetch video streams/metadata; CI must verify availability. The app will invoke the downloader to produce a stream or temp file passed to `ffmpeg`.
 - Q: Audit logging persistence → A: Persist minimal audit logs to the DB with 30-day retention (request_id, outcome, duration, client_ip, url, timestamp). This aids debugging and lightweight operational queries.

## User Scenarios & Testing *(mandatory)*

The MVP is a single HTTP endpoint that accepts a YouTube URL and returns an audio file (mp3) or a clear error response. All user stories are independently testable. CI MUST run PHPUnit/Pest and PHPStan 9; both MUST pass for PR merge.

### User Story 1 - Download audio from a public YouTube video (Priority: P1)

As an anonymous user, I can provide a YouTube video URL and receive a downloadable audio file so I can save the audio locally.

**Why this priority**: This is the core value of the product — converting YouTube video audio to a downloadable file.

**Independent Test**: Call the HTTP endpoint with a known public YouTube URL (test fixture or stubbed response) and assert the response is successful, has appropriate download headers, and contains a valid audio stream of one of the supported formats.

**Acceptance Scenarios**:

1. **Given** a valid public YouTube URL, **When** the user requests extraction, **Then** the service returns HTTP 200 with Content-Type and Content-Disposition headers and an audio file (mp3, m4a, or opus).
2. **Given** a valid YouTube URL with unavailable or private content, **When** the user requests extraction, **Then** the service returns an error (HTTP 400 or 404) with a clear error message.

---

*Note: To minimize scope for the MVP and speed time-to-value, the MVP will support mp3 only. Format selection will be considered in later iterations.*

### User Story 3 - Validate input and return helpful errors (Priority: P3)

As an anonymous user, I receive clear, minimal error messages when providing invalid input (malformed URL, non-YouTube URL, or unsupported content).

**Why this priority**: Good errors reduce user confusion and support burden.

**Independent Test**: Send malformed or unsupported URLs and assert that responses are HTTP 400 with structured JSON error describing the issue.

**Acceptance Scenarios**:

1. **Given** a malformed URL, **When** the user requests extraction, **Then** the service returns HTTP 400 with error code and human-readable message.

---

### Edge Cases

- Very long videos ( > 2 hours): service may reject with an informative error (see Non-functional constraints) or process with a warning if supported.
- Copyright-protected or age-restricted videos: service must not bypass access controls or require credentialed access. Such content should return an error indicating extraction is not permitted.
- Network timeouts from upstream video source: return a 502/504 style error and an idempotent request-id for diagnostics.
- Concurrent requests: service must remain fair and respond with rate-limit headers if limits are applied.


## Requirements *(mandatory)*

### Functional Requirements

FR-001: The system MUST expose a single public HTTP endpoint POST `/api/extract` that accepts a JSON body `{ "url": "..." }` containing a YouTube video URL and returns an audio file in the supported format: mp3. The API MUST use POST (not GET) for the extraction operation to avoid URL length and caching semantics. (Format selection is out of scope for MVP.)

FR-002: The system MUST validate input URLs and return a structured error response for malformed, unsupported, private, or unavailable videos.

FR-003: The system MUST include appropriate headers for download (`Content-Type`, `Content-Disposition`) so clients download the file with a sensible filename derived from the video title and timestamp. Filename derivation rules (MUST):

- Sanitize to remove path separators and control characters. Allow only letters, numbers, space, dash, underscore and replace others with `-`.
- Trim to a maximum of 100 characters (excluding extension).
- Append a UTC timestamp `YYYYMMDDTHHMMSSZ` when necessary to avoid collisions.
- Ensure the extension is `.mp3` for the MVP.

Implementations MUST document the sanitization behavior and provide a test that asserts the expected filename format.

FR-004: The system MUST respect YouTube's Terms of Service; any method used to obtain audio MUST be documented in the repo (README) and flagged for legal review if necessary.

FR-005: The system MUST stream the audio to the client without writing the entire file to persistent disk unless necessary for processing; temporary local files in a secure tmp area are acceptable if documented.

FR-006: The system MUST return clear, machine-readable JSON errors with an error code, message, and a request_id for diagnostics when failures occur.

FR-007: The system MUST return audio in mp3 format.

FR-008: The system MUST include minimal metadata in the response headers: `X-Request-Id` (UUID), `X-Response-Timestamp` (UTC ISO8601) and `X-Source-Host` (hostname). Implementations MAY also include these values in the body for debug modes, but headers are required for programmatic users. These headers MUST NOT contain user secrets or full URLs (input_url must be redacted if echoed).

FR-010 (timeouts): The system MUST bound external process execution (calls to `yt-dlp` and `ffmpeg`) with a configurable timeout (default: 120 seconds) and fail the request with a clear error and request_id if exceeded. Implementations MUST document this timeout in `quickstart.md` and make it configurable via `config/extractor.php`.

FR-009: The system MUST persist minimal audit logs for each extraction request with fields (request_id, outcome, duration_ms, client_ip, input_url (redacted), created_at). Retention MUST be configurable and default to 30 days.


### Non-functional Requirements

- NFR-001: CI must run PHPUnit/Pest and PHPStan 9; both must pass for PR merge.
- NFR-002: Code MUST be formatted with Laravel Pint. PRs failing Pint must be rejected or automatically fixed in CI.
- NFR-003: The MVP should be stateless and scale horizontally; avoid persistent per-user storage in MVP.
- NFR-004: For the MVP, enforce a conservative per-IP rate limit of 10 requests per minute (configurable) to reduce abuse risk.
- NFR-005: Processing time target: 95% of valid small videos (<= 10 minutes) should start streaming to the client within 10 seconds of request.
- NFR-006: Do not attempt to circumvent digital rights management (DRM) or access-restricted content.
 - NFR-005: The system must emit monitoring metrics and alerts for: rate-limit breaches, high error rates, and unusually large or frequent requests from single IPs. Alerts should target on-call or an operations inbox for investigation.
 - NFR-006: Processing time target: 95% of valid small videos (<= 10 minutes) should start streaming to the client within 10 seconds of request.
 - NFR-007: Do not attempt to circumvent digital rights management (DRM) or access-restricted content.
 - NFR-008: Audit logs persisted to the DB must be retained for 30 days by default and support safe redaction of sensitive fields (e.g., removing query strings from captured URLs).


### Key Entities *(include if feature involves data)*

- **ExtractionRequest** (ephemeral): Represents an incoming request and its transient metadata: request_id, input_url, requested_format, start_time, status, client_ip.
- **AuditLog** (optional, MVP discouraged): Minimal events for operational troubleshooting (request_id, outcome, duration) stored only if necessary and with retention policy defined.
 - **AuditLog**: Minimal events for operational troubleshooting persisted in the DB for MVP: fields: request_id (string), outcome (enum), duration_ms (integer), client_ip (string), input_url (string, redacted where necessary), created_at (timestamp). Retention: 30 days.


## Success Criteria *(mandatory)*

### Measurable Outcomes

- SC-001: Users can successfully extract audio from a public YouTube video and begin downloading it (or streaming) in under 10 seconds for videos <= 10 minutes, 95% of the time.
- SC-002: Primary task completion rate (first-attempt successful extraction) is >= 90% for supported public videos in test suite.
- SC-003: PHPStan 9 must pass in CI with no new errors introduced by the feature.
- SC-004: Automated tests (Pest) cover the primary happy path and at least two error scenarios; the coverage for this feature's module is >= 80% lines.
- SC-005: No test or CI tasks expose secrets or bypass access controls; all tests use fixtures, mocks, or recorded responses.


## Assumptions

- This MVP targets public, unrestricted YouTube videos only. It will not attempt to access private, age-restricted, or region-restricted content requiring authentication.
- The project will document the extraction method and legal considerations in the repository README for review.
- Storage of extracted files on disk is avoided where possible; streaming or temporary file usage is permitted for processing convenience.
- Reasonable rate limits will be applied to avoid abuse. For MVP use 10 requests per minute per IP as a starting default (configurable).
- Audio format conversion uses standard, well-known tools/libraries available in the PHP ecosystem or system packages; their choices will be documented and justified when implemented.
 - The MVP requires a system `ffmpeg` binary for audio extraction; the required minimum version will be documented in the README and CI will verify availability.
 - The MVP requires a system `ffmpeg` binary for audio extraction; the required minimum version will be documented in the README and CI will verify availability.
 - The MVP requires a downloader tool (recommend `yt-dlp`) to retrieve the source video stream or audio-only stream; the required version and invocation pattern will be documented and CI will verify availability.


## Implementation Notes (non-normative)

- Use an HTTP endpoint that streams the converted audio to the response using chunked transfer where possible.
- Consider using an isolated worker or queue for long-running conversions; for MVP, synchronous processing for short videos is acceptable.
-- Document any third-party libraries or system binaries required for audio conversion in the repository (versions and licensing) in a non-normative appendix. Do not rely on any single tool; treat tooling as an implementation detail.
- The MVP will use the `ffmpeg` binary for audio extraction. Document required versions and installation steps in the README. CI should include a smoke check that verifies `ffmpeg` is present before running integration tests.
 - The MVP will invoke an external downloader (e.g., `yt-dlp`) to fetch stream data, then pipe or pass that to `ffmpeg` for conversion. Document required versions and installation steps in the README. CI should include smoke checks that verify both `yt-dlp` and `ffmpeg` are present before running integration tests.
- Add integration tests that stub or record YouTube responses to avoid live calls in CI.


## Tests to Add

- Integration: happy path extraction using a recorded/fixture YouTube video metadata and stored sample audio; assert headers and audio format.
- Integration: invalid URL and private/unavailable video scenarios returning structured error responses.
- Unit: input validation, format selection logic, and response header generation.
 - Integration: invalid URL and private/unavailable video scenarios returning structured error responses.
 - Integration: audit log generation test — ensure a request generates an AuditLog row with expected fields and that sensitive parts of the input_url are redacted.
 - Unit: input validation, format selection logic, and response header generation.


## Open Questions (resolved)

1. Rate limiting policy — Resolved: Use 10 requests per minute per IP as the MVP default (configurable). This is documented in NFR-004 and Assumptions.

2. Accepted output formats — Resolved: MVP supports mp3 only. Other formats (m4a, opus) are out of scope for MVP and may be considered in later iterations.


---

End of spec.
# Feature Specification: [FEATURE NAME]

**Feature Branch**: `[###-feature-name]`  
**Created**: [DATE]  
**Status**: Draft  
**Input**: User description: "$ARGUMENTS"

## User Scenarios & Testing *(mandatory)*

IMPORTANT: User stories should be PRIORITIZED as user journeys ordered by
importance. Each user story/journey must be INDEPENDENTLY TESTABLE. For the
YouTube audio extractor project, the MVP MUST be a single HTTP endpoint that
accepts a YouTube URL and returns an audio file (mp3, m4a, or opus).

Assign priorities (P1, P2, P3, etc.) to each story, where P1 is the most critical.
Each story must include at least one automated test (unit or integration). CI
MUST run PHPUnit/Pest and PHPStan 9; both MUST pass for PR merge.

### User Story 1 - [Brief Title] (Priority: P1)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently - e.g., "Can be fully tested by [specific action] and delivers [specific value]"]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]
2. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

### User Story 2 - [Brief Title] (Priority: P2)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

### User Story 3 - [Brief Title] (Priority: P3)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

[Add more user stories as needed, each with an assigned priority]

### Edge Cases

<!--
  ACTION REQUIRED: The content in this section represents placeholders.
  Fill them out with the right edge cases.
-->

- What happens when [boundary condition]?
- How does system handle [error scenario]?


## Requirements *(mandatory)*

### Functional Requirements (example for YouTube audio extractor)

  of the supported formats: mp3, m4a, or opus.
  response for invalid or unsupported URLs.
  with YouTube's terms of service; any method used MUST be documented.
  include content-type and content-disposition headers suitable for direct
  download.
  minimal metadata (timestamp, request id, URL hostname) without storing
  user-provided secrets.
  
### Non-functional Requirements

- **NFR-001**: Code MUST be formatted with Laravel Pint configuration; CI MUST
  fail the PR if Pint reports formatting violations.
- **NFR-002**: Development and test environments MUST support SQLite by
  default; production database requirements MUST be documented when necessary.

*Note*: Any additional functional requirements (e.g., user accounts, rate
limits per user) MUST be justified and elevated in priority via the
Complexity Tracking table.

### Key Entities *(include if feature involves data)*

- **[Entity 1]**: [What it represents, key attributes without implementation]
- **[Entity 2]**: [What it represents, relationships to other entities]

## Success Criteria *(mandatory)*

<!--
  ACTION REQUIRED: Define measurable success criteria.
  These must be technology-agnostic and measurable.
-->

### Measurable Outcomes

- **SC-001**: [Measurable metric, e.g., "Users can complete account creation in under 2 minutes"]
- **SC-002**: [Measurable metric, e.g., "System handles 1000 concurrent users without degradation"]
- **SC-003**: [User satisfaction metric, e.g., "90% of users successfully complete primary task on first attempt"]
- **SC-004**: [Business metric, e.g., "Reduce support tickets related to [X] by 50%"]
