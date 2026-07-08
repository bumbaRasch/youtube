<?php

use App\Services\ExtractionService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

it('returns mp3 headers for a sample url', function () {
    $service = new ExtractionService(new NullLogger());

    $result = $service->extract('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    expect($result)->toHaveKey('headers');
    expect($result['headers'])->toBeArray();
    expect($result['headers'])->toHaveKey('Content-Type');
    expect($result['headers']['Content-Type'])->toBe('audio/mpeg');
    expect($result['headers'])->toHaveKey('Content-Disposition');
    expect(str_contains($result['headers']['Content-Disposition'], 'filename'))->toBeTrue();
});
