<?php

declare(strict_types=1);

namespace App\Http\Github;

use App\Dto\CreateEventInput;
use App\Dto\Github\ArchiveQuery;
use App\Validation\CreateEventConstraint;
use App\ValueObject\DateRange;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @phpstan-import-type EventInputData from \App\Dto\CreateEventInput
 */
class HttpArchiveClient implements ArchiveClient
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ValidatorInterface $validator,
        private readonly CreateEventConstraint $createEventConstraint,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return \Generator<CreateEventInput>
     */
    public function fetchEvents(ArchiveQuery $archiveQuery): \Generator
    {
        $dateRange = $archiveQuery->dateRange();
        $urls = $this->generateUrls($dateRange);

        foreach ($urls as $url) {
            try {
                $response = $this->client->request('GET', $url);

                if ($response->getStatusCode() === 404) {
                    continue;
                }

                $tempFilePath = $this->createTemporaryFile($response);

                /** @var resource $gzStream */
                $gzStream = gzopen($tempFilePath, 'rb');

                while ($line = gzgets($gzStream)) {
                    /** @var EventInputData $eventData */
                    $eventData = json_decode($line, true);

                    if ($this->validateEventLine($line) === false) {
                        continue;
                    }

                    yield new CreateEventInput($eventData);
                }

                gzclose($gzStream);
                unlink($tempFilePath);
            }  catch (\Exception $e) {
                $this->logger->warning('Error while fetching events', [
                    'url' => $url,
                    'exception' => $e
                ]);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function generateUrls(DateRange $dateRange): array
    {
        $urls = [];
        foreach ($dateRange->period() as $date) {
            $formattedDate = $date->format('Y-m-d-H');
            $urls[] = "https://data.gharchive.org/$formattedDate.json.gz";
        }

        return $urls;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function createTemporaryFile(ResponseInterface $response): string
    {
        /** @var string $tempFilePath */
        $tempFilePath = tempnam(sys_get_temp_dir(), 'gharchive');
        /** @var resource $outputStream */
        $outputStream = fopen($tempFilePath, 'wb');

        foreach ($this->client->stream($response) as $chunk) {
            fwrite($outputStream, $chunk->getContent());
        }

        fclose($outputStream);

        return $tempFilePath;
    }

    private function validateEventLine(string $line): bool
    {
        $eventData = json_decode($line, true);
        $violations = $this->validator->validate($eventData, $this->createEventConstraint->get());

        if ($violations->count() > 0) {
            $this->logger->warning('Invalid event data', [
                'event_data' => $eventData,
                'violations' => $violations
            ]);

            return false;
        }

        return $violations->count() === 0;
    }
}
