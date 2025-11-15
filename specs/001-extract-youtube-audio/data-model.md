# data-model.md

## Entities

- AuditLog
  - request_id: string (PK/UUID)
  - outcome: enum (success, error, rejected)
  - duration_ms: integer
  - client_ip: string
  - input_url: string (stored redacted: remove query strings)
  - created_at: timestamp

## Notes

- No persistent user model or sessions required for MVP.
