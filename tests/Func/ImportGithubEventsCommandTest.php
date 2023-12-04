<?php

declare(strict_types=1);

namespace App\Tests\Func;

use App\Command\ImportGitHubEventsCommand;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportGithubEventsCommandTest extends KernelTestCase
{
    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->connection = static::getContainer()->get(EntityManagerInterface::class)->getConnection();
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function test_it_does_not_import_events_if_no_dates_provided(): void
    {
        $command = static::getContainer()->get(ImportGitHubEventsCommand::class);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('No date provided', $output);

        $this->assertEquals(2, $commandTester->getStatusCode());
    }

    public function test_it_imports_only_handled_event_types(): void
    {
        $command = static::getContainer()->get(ImportGitHubEventsCommand::class);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'from' => '2022-12-31 10:00:00',
            'to' => '2022-12-31 12:00:00',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Importing events from 2022-12-31 10:00:00 to 2022-12-31 12:00:00', $output);
        $this->assertStringContainsString('Import complete with 3 events imported.', $output);

        $this->assertEquals(0, $commandTester->getStatusCode());

        $this->assertEquals([
            26159977167,
            26159976991,
            26159976883,
        ], $this->getPersistedEventIds(
            new \DateTimeImmutable('2022-12-31 10:00:00'),
            new \DateTimeImmutable('2022-12-31 12:00:00')
        ));
        $this->assertCount(3, $this->getPersistedActorIds(
            7595639,
            35613825,
            3375461,
        ));
        $this->assertCount(3, $this->getPersistedRepoIds(
            266261464,
            258733757,
            172822195,
        ));
    }

    /**
     * @return int[]
     */
    private function getPersistedEventIds(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->connection->executeQuery(
            'SELECT id FROM event WHERE create_at BETWEEN :from AND :to',
            [
                'from' => $from->format('Y-m-d H:i:s'),
                'to' => $to->format('Y-m-d H:i:s'),
            ]
        )->fetchFirstColumn();
    }

    private function getPersistedActorIds(int ...$expectedIds): array
    {
        return $this->connection->executeQuery(
            'SELECT id FROM actor WHERE id IN (:ids)',
            [
                'ids' => $expectedIds,
            ],
            [
                'ids' => Connection::PARAM_INT_ARRAY,
            ])->fetchFirstColumn();
    }

    private function getPersistedRepoIds(int ...$expectedIds): array
    {
        return $this->connection->executeQuery(
            'SELECT id FROM repo WHERE id IN (:ids)',
            [
                'ids' => $expectedIds,
            ],
            [
                'ids' => Connection::PARAM_INT_ARRAY,
            ])->fetchFirstColumn();
    }

    public function test_dates_are_required_to_be_in_right_order(): void
    {
        $command = static::getContainer()->get(ImportGitHubEventsCommand::class);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'from' => '2023-12-03 12:00:00',
            'to' => '2023-12-03 10:00:00',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Start date must be before end date', $output);

        $this->assertEquals(2, $commandTester->getStatusCode());
    }
}
