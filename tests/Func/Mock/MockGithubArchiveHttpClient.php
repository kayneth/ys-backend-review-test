<?php

declare(strict_types=1);

namespace App\Tests\Func\Mock;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class MockGithubArchiveHttpClient extends MockHttpClient
{
    public function __construct(private readonly string $dataDirectory)
    {
        parent::__construct([$this, 'mockResponseCallback']);
    }

    public function mockResponseCallback($method, $url, $options): MockResponse
    {
        preg_match('/(\d{4}-\d{2}-\d{2}-\d{1,2})\.json\.gz/', $url, $matches);
        $dateTime = $matches[1] ?? null;

        if ($dateTime) {
            $filePath = $this->dataDirectory . '/' . $dateTime . '.json';

            if (file_exists($filePath)) {
                $this->toGz($filePath);
                $fileContent = file_get_contents($filePath);

                unlink($filePath . '.gz');

                return new MockResponse($fileContent, [
                    'http_code' => Response::HTTP_OK
                ]);
            }
        }

        return new MockResponse('', ['http_code' => Response::HTTP_NOT_FOUND]);
    }

    private function toGz(string $filePath): void
    {
        $gz = gzopen($filePath . '.gz', 'wb9');
        gzwrite($gz, file_get_contents($filePath));
        gzclose($gz);
    }
}
