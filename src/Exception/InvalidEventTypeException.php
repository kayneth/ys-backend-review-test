<?php

namespace App\Exception;

class InvalidEventTypeException extends \DomainException
{
    public function __construct(string $eventType)
    {
        parent::__construct(sprintf('Invalid event type "%s"', $eventType));
    }
}
