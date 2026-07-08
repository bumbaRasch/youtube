# research.md

## Decisions and Rationale

- Decision: Use `yt-dlp` as the downloader and `ffmpeg` for audio conversion.
  - Rationale: `yt-dlp` is actively maintained and handles YouTube's streaming formats robustly; `ffmpeg` is the de-facto standard for audio conversion and widely available in CI/container environments.
  - Alternatives considered: pure-PHP parsing (fragile), third-party APIs (cost and external dependency).

- Decision: Synchronous processing for videos <= 30 minutes; reject longer videos with HTTP 422.
  - Rationale: Limits resource consumption and simplifies the MVP; async queueing can be added later.

- Decision: Audit logs persisted to DB with 30-day retention and URL redaction.
  - Rationale: Enables reliable debugging and light operational queries without long-term storage costs.

- Decision: Public anonymous endpoint protected by per-IP rate limiting (10 req/min) and monitoring/alerts.
  - Rationale: Minimizes friction for MVP while providing basic abuse protection.

## Tasks for Implementation

- Add migration and model for `audit_logs`.
- Implement `ExtractionService` to call `yt-dlp` and stream into `ffmpeg` for MP3 conversion.
- Implement `ExtractController` endpoint `POST /api/extract` with input validation and response streaming.
- Add integration tests with recorded responses and smoke checks for `yt-dlp` and `ffmpeg`.
