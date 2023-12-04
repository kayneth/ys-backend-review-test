<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\CreateEventInput;
use App\Dto\EventInput;
use App\Exception\InvalidEventTypeException;

interface WriteEventRepository
{
    /**
     * @throws InvalidEventTypeException
     */
    public function create(CreateEventInput $createEventInput): void;

    public function update(EventInput $authorInput, int $id): void;
}
