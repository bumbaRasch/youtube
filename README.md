<div align="center">

# youtube

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Pest](https://img.shields.io/badge/Pest-4-fa7298?style=flat)](https://pestphp.com)
[![CI](https://img.shields.io/badge/CI-GitHub%20Actions-2088FF?style=flat&logo=githubactions&logoColor=white)](https://github.com/bumbaRasch/youtube/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

**Laravel microservice that accepts a YouTube URL and streams the extracted audio as a downloadable MP3 — with per-IP rate limiting, audit logging, and a typed service contract.**

</div>

---

## What is this?

A focused Laravel API microservice built around a single endpoint: `POST /api/extract`. The service accepts a YouTube URL, delegates extraction to `yt-dlp` + `ffmpeg`, and streams the resulting MP3 directly to the client without writing temp files to disk.

The project was built spec-first: the business rules (rate limit, max video duration, audit retention) were documented in `specs/001-extract-youtube-audio/` before a line of code was written, and all acceptance scenarios from the spec have corresponding Pest tests.

## Endpoint

```
POST /api/extract
Content-Type: application/json

{ "url": "https://www.youtube.com/watch?v=..." }
```

**Success (200):**
```
Content-Type: audio/mpeg
Content-Disposition: attachment; filename="<title>.mp3"
<binary MP3 stream>
```

**Errors:**
| Status | Reason |
|--------|--------|
| 422 | Validation failed (missing or invalid URL) |
| 422 | Video exceeds 30-minute limit |
| 429 | Rate limit exceeded (10 req/min per IP) |
| 400/404 | Private, unavailable, or age-restricted content |
| 502/504 | Upstream timeout — `request_id` included for diagnostics |

## Architecture

```
youtube/
├── app/
│   ├── Http/Controllers/
│   │   └── ExtractController.php        # Single-action controller — validates, delegates, streams
│   ├── Services/
│   │   ├── Contracts/
│   │   │   └── ExtractionServiceContract.php   # Interface — extract(), deriveFilename(), buildFfmpegArgs()
│   │   └── ExtractionService.php        # yt-dlp + ffmpeg orchestration, timeout, filename sanitization
│   └── Models/
│       └── AuditLog.php                 # Audit trail: request_id, outcome, duration_ms, client_ip, url
├── routes/
│   └── api.php                          # POST /api/extract → ExtractController
├── config/
│   └── extractor.php                    # Rate limit, max duration, audit retention, process timeout
├── specs/
│   └── 001-extract-youtube-audio/
│       └── spec.md                      # Full feature spec with user stories and acceptance scenarios
└── tests/
    ├── Feature/
    │   ├── ExtractTest.php              # Happy path and error responses via mocked service
    │   └── ExtractStreamingTest.php    # Streaming response assertions
    └── Unit/
        ├── ExtractionServiceTest.php   # Service logic with NullLogger
        ├── FilenameTest.php            # deriveFilename() — sanitization edge cases
        └── ProcessArgsTest.php         # buildFfmpegArgs() — bitrate, output flags
```

## Tech stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| Language | PHP 8.4 |
| Testing | Pest 4 |
| ORM | Eloquent (AuditLog model) |
| Audio extraction | yt-dlp + ffmpeg (system binaries) |
| CI | GitHub Actions |

## Design decisions

**Service contract** — `ExtractionServiceContract` is an interface, not a class. Controllers depend on it; the service provider binds the concrete implementation. Tests inject a mock without touching the filesystem or running real extraction.

**Streaming without temp files** — the response is a `StreamedResponse` that pipes `ffmpeg` stdout directly to the HTTP response. No temporary MP3 is written to disk.

**Filename sanitization** — `deriveFilename()` allows only `[A-Za-z0-9 ._-]`, collapses whitespace, limits to 100 characters, and appends `.mp3` if no extension is present. All rules are covered by `FilenameTest.php`.

**Audit log** — every extraction attempt writes a row to `audit_logs` with `request_id`, `outcome`, `duration_ms`, `client_ip`, and `input_url`. Retention is 30 days (configurable via `EXTRACTOR_AUDIT_RETENTION_DAYS`).

## Quick start

```bash
git clone https://github.com/bumbaRasch/youtube
cd youtube

composer install
cp .env.example .env
php artisan key:generate

# Requires yt-dlp and ffmpeg on PATH
# macOS: brew install yt-dlp ffmpeg
# Debian/Ubuntu: apt install ffmpeg && pip install yt-dlp

php artisan migrate
php artisan serve
```

## Environment

```bash
EXTRACTOR_RATE_LIMIT=10              # requests per minute per IP
EXTRACTOR_MAX_DURATION_MINUTES=30   # videos longer than this are rejected
EXTRACTOR_AUDIT_RETENTION_DAYS=30   # audit log row retention
EXTRACTOR_PROCESS_TIMEOUT=120       # ffmpeg process timeout in seconds
```

## Tests

```bash
php artisan test --compact
```
