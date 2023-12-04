<?php

declare(strict_types=1);

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';
    public const COMMENT = 'MSG';
    public const PULL_REQUEST = 'PR';
//    public const COMMIT = 'CommitCommentEvent';
//    public const CREATE = 'CreateEvent';
//    public const DELETE = 'DeleteEvent';
//    public const FORK = 'ForkEvent';
//    public const GOLLUM = 'GollumEvent';
//    public const COMMENT = 'IssueCommentEvent';
//    public const ISSUE = 'IssuesEvent';
//    public const MEMBER = 'MemberEvent';
//    public const PUBLIC = 'PublicEvent';
//    public const PULL_REQUEST = 'PullRequestEvent';
//    public const REVIEW = 'PullRequestReviewEvent';
//    public const REVIEW_COMMENT = 'PullRequestReviewCommentEvent';
//    public const REVIEW_THREAD = 'PullRequestReviewThreadEvent';
//    public const PUSH = 'PushEvent';
//    public const RELEASE = 'ReleaseEvent';
//    public const SPONSORSHIP = 'SponsorshipEvent';
//    public const WATCH = 'WatchEvent';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
//        self::COMMIT => 'Commit Comment',
//        self::CREATE => 'Create',
//        self::DELETE => 'Delete',
//        self::FORK => 'Fork',
//        self::GOLLUM => 'Wiki',
//        self::COMMENT => 'Issue Comment',
//        self::ISSUE => 'Issue',
//        self::MEMBER => 'Member',
//        self::PUBLIC => 'Public',
//        self::PULL_REQUEST => 'Pull Request',
//        self::REVIEW => 'Pull Request Review',
//        self::REVIEW_COMMENT => 'Pull Request Review Comment',
//        self::REVIEW_THREAD => 'Pull Request Review Thread',
//        self::PUSH => 'Push',
//        self::RELEASE => 'Release',
//        self::SPONSORSHIP => 'Sponsorship',
//        self::WATCH => 'Watch',
    ];
}
