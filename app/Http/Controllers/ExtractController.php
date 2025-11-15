<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ExtractionServiceContract;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExtractController
{
    public function __construct(protected ExtractionServiceContract $service, protected ResponseFactory $response) {}

    public function __invoke(Request $request): StreamedResponse
    {
        $request->validate(['url' => 'required|url']);

        $url = $request->input('url');

        // Reject long videos is implemented at the service level in full impl.
        $result = $this->service->extract($url);

        $headers = $result['headers'] ?? ['Content-Type' => 'application/octet-stream'];

        $streamCallback = $result['stream'] ?? function (): void {
            echo '';
        };

        return $this->response->stream(function () use ($streamCallback) {
            if (is_callable($streamCallback)) {
                $output = $streamCallback();
                if (is_string($output) && $output !== '') {
                    echo $output;
                }
            }
        }, 200, $headers);
    }
}
