<?php

namespace App\Services\Contracts;

interface ExtractionServiceContract
{
    /**
     * Orchestrate extraction for the given URL.
     *
     * @return array{headers: array, stream: mixed|null}
     */
    public function extract(string $url): array;
}
