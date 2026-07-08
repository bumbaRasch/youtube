<?php

use App\Services\ExtractionService;
use Psr\Log\NullLogger;

it('builds ffmpeg args that enforce mp3 output', function () {
    $service = new ExtractionService(new NullLogger());

    $args = $service->buildFfmpegArgs(['bitrate' => 192]);

    expect(is_array($args))->toBeTrue();
    // ensure common mp3 flags are present
    expect(in_array('-f', $args))->toBeTrue();
    expect(in_array('mp3', $args))->toBeTrue();
});

it('exposes configured process timeout from config', function () {
    $service = new ExtractionService(new NullLogger(), 77);

    $timeout = $service->getProcessTimeout();

    expect(is_int($timeout))->toBeTrue();
    expect($timeout)->toBe(77);
});
