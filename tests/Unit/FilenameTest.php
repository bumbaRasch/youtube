<?php

use App\Services\ExtractionService;
use Psr\Log\NullLogger;

it('derives a safe filename from a video title', function () {
    $service = new ExtractionService(new NullLogger());

    $title = "My Awesome Video: An Example? / With Strange *Chars*";

    $filename = $service->deriveFilename($title);

    // Should be limited in characters and only allowed chars
    expect($filename)->toBeString();
    expect(strlen($filename))->toBeLessThanOrEqual(100);
    // No slashes or special control chars
    expect(strpos($filename, '/'))->toBeFalse();
    expect(preg_match('/[^A-Za-z0-9._\- ]/', $filename))->toBe(0);
});
