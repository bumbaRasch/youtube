<?php

use App\Services\Contracts\ExtractionServiceContract;

it('service provides a callable stream that returns the mp3 marker', function () {
    $service = new \App\Services\ExtractionService(new \Psr\Log\NullLogger());

    $result = $service->extract('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    expect($result)->toHaveKey('stream');
    $stream = $result['stream'];
    expect(is_callable($stream))->toBeTrue();
    $output = $stream();
    expect($output)->toBe('FAKE_MP3_STREAM');
});
