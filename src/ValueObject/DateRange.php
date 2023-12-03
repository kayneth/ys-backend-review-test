<?php

declare(strict_types=1);

namespace App\ValueObject;

class DateRange
{
    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private readonly \DateTimeInterface $startDate,
        private readonly \DateTimeInterface $endDate,
    ) {
        if ($this->startDate > $this->endDate) {
            throw new \InvalidArgumentException('Start date must be before end date');
        }
    }

    public function period(): \DatePeriod
    {
        return new \DatePeriod(
            $this->startDate,
            new \DateInterval('PT1H'),
            $this->endDate
        );
    }
}
