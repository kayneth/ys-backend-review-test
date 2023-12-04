<?php

namespace App\Dto\Github;

use App\ValueObject\DateRange;

class ArchiveQuery
{
    public function __construct(private DateRange $dateRange)
    {
    }

    public function dateRange(): DateRange
    {
        return $this->dateRange;
    }
}
