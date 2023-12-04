<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\Github\ArchiveQuery;
use App\Exception\InvalidEventTypeException;
use App\Http\Github\ArchiveClient;
use App\Repository\WriteEventRepository;
use App\ValueObject\DateRange;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
class ImportGitHubEventsCommand extends Command
{
    protected static $defaultName = 'app:import-github-events';

    public function __construct(
        private readonly ArchiveClient        $archiveClient,
        private readonly WriteEventRepository $writeEventRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import GH events')
            ->addArgument('from', null, 'From date')
            ->addArgument('to', null, 'To date')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateFrom = $input->getArgument('from');
        $dateTo = $input->getArgument('to');

        if (!\is_string($dateFrom) || !\is_string($dateTo)) {
            $output->writeln('No date provided');

            return Command::INVALID;
        }

        $output->writeln('Importing events from ' . $dateFrom . ' to ' . $dateTo);

        try {
            $dateRange = new DateRange(new \DateTimeImmutable($dateFrom), new \DateTimeImmutable($dateTo));
            $archiveQuery = new ArchiveQuery($dateRange);
        } catch (\InvalidArgumentException $exception) {
            $output->writeln($exception->getMessage());

            return Command::INVALID;
        }

        $importedEventsCount = 0;
        foreach ($this->archiveClient->fetchEvents($archiveQuery) as $eventData) {
            try {
                $this->writeEventRepository->create($eventData);
                $importedEventsCount++;
                $output->writeln('Event ' . $eventData->id() . ' imported.');
            } catch (InvalidEventTypeException $exception) {
                $output->writeln($exception->getMessage());

                continue;
            }
        }

        $output->writeln('Import complete with ' . $importedEventsCount . ' events imported.');

        return Command::SUCCESS;
    }
}
