<?php

declare(strict_types=1);

namespace App\Http\Github;

use App\Dto\CreateEventInput;
use App\Dto\Github\ArchiveQuery;

interface ArchiveClient
{
    /**
     * @return \Generator<CreateEventInput>
     */
    public function fetchEvents(ArchiveQuery $archiveQuery): \Generator;
}
