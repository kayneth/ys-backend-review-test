<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * @phpstan-type EventType = 'CommitCommentEvent'|'CreateEvent'|'DeleteEvent'|'ForkEvent'|'GollumEvent'|'IssueCommentEvent'|'IssuesEvent'|'MemberEvent'|'PublicEvent'|'PullRequestEvent'|'PullRequestReviewEvent'|'PullRequestReviewCommentEvent'|'PullRequestReviewThreadEvent'|'PushEvent'|'ReleaseEvent'|'SponsorshipEvent'|'WatchEvent'
 *
 * @phpstan-type Actor array{
 *     id: int,
 *     login: string,
 *     url: string,
 *     avatar_url: string
 * }
 * @phpstan-type Repo array{
 *     id: int,
 *     name: string,
 *     url: string
 * }
 * @phpstan-type EventInputData array{
 *     id: string,
 *     type: EventType,
 *     payload: array<string, mixed>,
 *     actor: Actor,
 *     repo: Repo,
 *     public: bool,
 *     comment: string|null,
 *     created_at: string
 * }
 */
class CreateEventInput
{
    /**
     * @phpstan-param EventInputData $data
     */
    public function __construct(private array $data)
    {
    }

    /**
     * @phpstan-return string
     */
    public function id(): string
    {
        return $this->data['id'];
    }

    /**
     * @phpstan-return EventType
     */
    public function type(): string
    {
        return $this->data['type'];
    }

    /**
     * @phpstan-return EventInputData
     */
    public function data(): array
    {
        return $this->data;
    }
}
