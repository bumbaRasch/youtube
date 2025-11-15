#!/usr/bin/env bash
set -euo pipefail

missing=0
echo "Checking required binaries: ffmpeg, yt-dlp"
if ! command -v ffmpeg >/dev/null 2>&1; then
  echo "MISSING: ffmpeg" >&2
  missing=1
else
  echo "ffmpeg: $(ffmpeg -version | head -n1)"
fi

if ! command -v yt-dlp >/dev/null 2>&1; then
  echo "MISSING: yt-dlp" >&2
  missing=1
else
  echo "yt-dlp: $(yt-dlp --version)"
fi

if [ "$missing" -ne 0 ]; then
  echo "One or more required binaries are missing" >&2
  exit 2
fi

echo "All required binaries present"
