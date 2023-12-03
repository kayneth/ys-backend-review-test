<?php

namespace App\Tests\Unit\Validation;

use App\Validation\CreateEventConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class CreateEventConstraintTest extends TestCase
{
    public function test_invalid_event_type(): void
    {
        $violations = Validation::createValidator()->validate(
            [],
            $this->getConstraint()->get()
        );

        $this->assertNotSame(0, $violations->count());
    }

    /**
     * @dataProvider provideValidEventTypes
     */
    public function test_event_types_data_provider(string $eventType): void
    {
        $violations = Validation::createValidator()->validate(
            $this->getEventInputDataFromFile($eventType . '.json'),
            $this->getConstraint()->get()
        );

        $this->assertCount(0, $violations);
    }

    public static function provideValidEventTypes(): array
    {
        return [
            ['commit_comment_event'],
            ['issue_comment_event'],
            ['pull_request_event'],
        ];
    }

    private function getConstraint(): CreateEventConstraint
    {
        return new CreateEventConstraint();
    }

    private function getEventInputDataFromFile(string $filename): array
    {
        return json_decode(
            file_get_contents(__DIR__ . '/../../data/' . $filename),
            true
        );
    }
}
