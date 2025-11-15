<?php

use App\Services\Contracts\ExtractionServiceContract;

beforeEach(function () {
    // Ensure migrations are run in testing environment
});

it('validates url and returns streamed response', function () {
    $this->instance(ExtractionServiceContract::class, new class implements ExtractionServiceContract
    {
        public function extract(string $url): array
        {
            return ['headers' => ['Content-Type' => 'audio/mpeg', 'Content-Disposition' => 'attachment; filename="audio.mp3"'], 'stream' => null];
        }
    });

    $response = $this->postJson('/api/extract', ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'audio/mpeg');
    $response->assertHeader('Content-Disposition');
});
