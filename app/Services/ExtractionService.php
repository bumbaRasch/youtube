<?php

namespace App\Services;

use App\Services\Contracts\ExtractionServiceContract;
use Psr\Log\LoggerInterface;
use Throwable;

class ExtractionService implements ExtractionServiceContract
{
    public function __construct(protected LoggerInterface $logger, protected ?int $processTimeout = null) {}

    /**
     * Orchestrate extraction. For MVP this method is a stubbed placeholder
     * that should invoke yt-dlp and ffmpeg in production. Returns an array with
     * headers and a stream resource or null on error.
     *
     * @return array{headers: array, stream: resource|null}
     */
    public function extract(string $url): array
    {
        $this->logger->info('extract:start', ['url' => $url]);

        // Placeholder: in real implementation, spawn yt-dlp and ffmpeg and return a stream
        $headers = [
            'Content-Type' => 'audio/mpeg',
            'Content-Disposition' => 'attachment; filename="audio.mp3"',
        ];

        // For tests and local development a simple callable stream is returned
        // that writes a small marker. Real implementation will return a callback
        // that pipes ffmpeg stdout to output.
        $stream = function (): string {
            // This is not a real MP3, it's a harmless marker used by tests.
            return 'FAKE_MP3_STREAM';
        };

        return ['headers' => $headers, 'stream' => $stream];
    }

    /**
     * Derive a filesystem-safe filename (without path) from a video title.
     *
     * Rules:
     * - Allow letters, numbers, spaces, dot, underscore and dash
     * - Replace other chars with empty string
     * - Collapse consecutive spaces
     * - Trim and limit to 100 characters
     * - Append .mp3 if no extension
     */
    public function deriveFilename(string $title): string
    {
        // Normalize whitespace and remove control characters
        $s = preg_replace('/\s+/', ' ', trim($title));

        // Remove any character not allowed (letters, numbers, space, dot, underscore, dash)
        $s = preg_replace('/[^A-Za-z0-9 ._\-]/', '', $s);

        // Collapse spaces to single spaces
        $s = preg_replace('/\s+/', ' ', $s);

        // Limit length
        if (mb_strlen($s) > 100) {
            $s = mb_substr($s, 0, 100);
        }

        // Ensure extension
        if (! preg_match('/\.[A-Za-z0-9]{1,8}$/', $s)) {
            $s .= '.mp3';
        }

        return $s;
    }

    /**
     * Build ffmpeg CLI arguments for producing MP3 output.
     *
     * @param array $opts
     * @return array<int,string>
     */
    public function buildFfmpegArgs(array $opts = []): array
    {
        $bitrate = isset($opts['bitrate']) ? (int) $opts['bitrate'] : 192;

        // Example args: -i pipe:0 -f mp3 -b:a 192k -
        return [
            '-i', 'pipe:0',
            '-f', 'mp3',
            '-b:a', $bitrate . 'k',
            '-' // output to stdout
        ];
    }

    /**
     * Get process timeout in seconds from config.
     */
    public function getProcessTimeout(): int
    {
        if (! is_null($this->processTimeout)) {
            return (int) $this->processTimeout;
        }

        // Fallback to config when running inside the framework. If config
        // isn't available, default to 120 seconds.
        try {
            $t = config('extractor.process_timeout');
        } catch (Throwable $e) {
            $t = null;
        }

        return (int) ($t ?? 120);
    }
}
