<?php

declare(strict_types=1);

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * @template TValue of 'COM'|'MSG'|'PR'
 * @template TReadable of 'Commit'|'Comment'|'Pull Request'
 *
 * @extends AbstractEnumType<TValue, TReadable>
 */
class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';

    public const COMMENT = 'MSG';

    public const PULL_REQUEST = 'PR';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];
}
