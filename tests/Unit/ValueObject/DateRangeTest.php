<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\DateRange;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{

    public function test_with_end_date_before_start_date(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Start date must be before end date');

        new DateRange(
            new \DateTimeImmutable('2021-01-01 01:00:00'),
            new \DateTimeImmutable('2021-01-01 00:00:00')
        );
    }

    public function test_get_a_period_of_one_hour(): void
    {
        $dateRange = new DateRange(
            new \DateTimeImmutable('2021-01-01 00:00:00'),
            new \DateTimeImmutable('2021-01-01 01:00:00')
        );

        $period = $dateRange->period();

        $this->assertInstanceOf(\DatePeriod::class, $period);
        $this->assertCount(1, $period);
        $this->assertEquals(
            new \DateTimeImmutable('2021-01-01 00:00:00'),
            $period->getStartDate()
        );
    }
    public function test_with_end_date_equal_to_start_date(): void
    {
        $dateRange = new DateRange(
            new \DateTimeImmutable('2021-01-01 00:00:00'),
            new \DateTimeImmutable('2021-01-01 00:00:00')
        );

        $period = $dateRange->period();

        $this->assertInstanceOf(\DatePeriod::class, $period);
        $this->assertCount(0, $period);
    }

    /**
     * @dataProvider provideDateRangeExpectations
     */
    public function test_get_a_period_of_n_hours(DateRange $dateRange, array $expectedHours): void
    {
        $period = $dateRange->period();

        $this->assertInstanceOf(\DatePeriod::class, $period);
        $this->assertCount(count($expectedHours), $period);
        $this->assertEquals(
            new \DateTimeImmutable($expectedHours[0]->format('Y-m-d H:i:s')),
            $period->getStartDate(),
        );
        $this->assertEquals($expectedHours, iterator_to_array($period));
    }

    public static function provideDateRangeExpectations(): iterable
    {
        yield 'one hour' => [
            new DateRange(
                new \DateTimeImmutable('2021-01-01 00:00:00'),
                new \DateTimeImmutable('2021-01-01 01:00:00')
            ),
            [
                new \DateTimeImmutable('2021-01-01 00:00:00'),
            ]
        ];

        yield 'two hours' => [
            new DateRange(
                new \DateTimeImmutable('2021-01-01 00:00:00'),
                new \DateTimeImmutable('2021-01-01 02:00:00')
            ),
            [
                new \DateTimeImmutable('2021-01-01 00:00:00'),
                new \DateTimeImmutable('2021-01-01 01:00:00'),
            ]
        ];

        yield 'three hours' => [
            new DateRange(
                new \DateTimeImmutable('2021-01-01 00:00:00'),
                new \DateTimeImmutable('2021-01-01 03:00:00')
            ),
            [
                new \DateTimeImmutable('2021-01-01 00:00:00'),
                new \DateTimeImmutable('2021-01-01 01:00:00'),
                new \DateTimeImmutable('2021-01-01 02:00:00'),
            ]
        ];

        yield 'with minutes' => [
            new DateRange(
                new \DateTimeImmutable('2021-01-01 00:00:00'),
                new \DateTimeImmutable('2021-01-01 01:59:00')
            ),
            [
                new \DateTimeImmutable('2021-01-01 00:00:00'),
                new \DateTimeImmutable('2021-01-01 01:00:00'),
            ]
        ];
    }
}
