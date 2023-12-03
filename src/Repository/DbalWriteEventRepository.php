<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\CreateEventInput;
use App\Dto\EventInput;
use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use App\Exception\InvalidEventTypeException;
use Doctrine\DBAL\Connection;

class DbalWriteEventRepository implements WriteEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function create(CreateEventInput $createEventInput): void
    {
        $eventData = $createEventInput->data();

        $eventType = $eventData['type'];
        $toEntityType = match ($eventType) {
            'CommitCommentEvent' => EventType::COMMIT,
            'IssueCommentEvent' => EventType::COMMENT,
            'PullRequestEvent' => EventType::PULL_REQUEST,
            default => throw new InvalidEventTypeException($eventType),
        };

        $actor = new Actor(
            (int) $eventData['actor']['id'],
            $eventData['actor']['login'],
            $eventData['actor']['url'],
            $eventData['actor']['avatar_url']
        );

        $repo = new Repo(
            (int) $eventData['repo']['id'],
            $eventData['repo']['name'],
            $eventData['repo']['url']
        );

        $event = new Event(
            (int) $eventData['id'],
            $toEntityType,
            $actor,
            $repo,
            $eventData['payload'],
            new \DateTimeImmutable($eventData['created_at']),
            $eventData['comment'] ?? null
        );

        $this->connection->transactional(function () use ($actor, $repo, $event) {
            $this->connection->executeStatement('
                INSERT INTO actor (id, login, url, avatar_url) VALUES (:id, :login, :url, :avatar_url)
                ON CONFLICT (id) DO UPDATE SET login = EXCLUDED.login, url = EXCLUDED.url, avatar_url = EXCLUDED.avatar_url
            ', [
                'id' => $actor->id(),
                'login' => $actor->login(),
                'url' => $actor->url(),
                'avatar_url' => $actor->avatarUrl(),
            ]);

            $this->connection->executeStatement('
                INSERT INTO repo (id, name, url) VALUES (:id, :name, :url)
                ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name, url = EXCLUDED.url
            ', [
                'id' => $repo->id(),
                'name' => $repo->name(),
                'url' => $repo->url(),
            ]);

            $this->connection->executeStatement('
                INSERT INTO event (id, type, actor_id, repo_id, payload, create_at, comment, count) 
                VALUES (:id, :type, :actor_id, :repo_id, :payload, :create_at, :comment, :count)
                ON CONFLICT (id) DO UPDATE SET comment = EXCLUDED.comment, count = EXCLUDED.count, payload = EXCLUDED.payload
            ', [
                'id' => $event->id(),
                'type' => $event->type(),
                'actor_id' => $actor->id(),
                'repo_id' => $repo->id(),
                'payload' => json_encode($event->payload()),
                'create_at' => $event->createAt()->format('Y-m-d H:i:s'),
                'comment' => $event->getComment(),
                'count' => $event->count(),
            ]);
        });

    }

    public function update(EventInput $authorInput, int $id): void
    {
        $sql = <<<SQL
        UPDATE event
        SET comment = :comment
        WHERE id = :id
SQL;

        $this->connection->executeQuery($sql, ['id' => $id, 'comment' => $authorInput->comment]);
    }
}
