<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\SearchInput;
use Doctrine\DBAL\Connection;

class DbalReadEventRepository implements ReadEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function countAll(SearchInput $searchInput): int
    {
        $sql = <<<SQL
        SELECT sum(count) as count
        FROM event
        WHERE date(create_at) = :date
        AND payload like %{$searchInput->keyword}%
SQL;

        /** @var int|false $count */
        $count = $this->connection->fetchOne($sql, [
            'date' => $searchInput->date,
        ]);

        if ($count === false) {
            return 0;
        }

        return (int) $count;
    }

    public function countByType(SearchInput $searchInput): array
    {
        $sql = <<<'SQL'
            SELECT type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload like %{$searchInput->keyword}%
            GROUP BY type
SQL;

        /** @var array<string, int> $countByType */
        $countByType = $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchInput->date,
        ]);

        return $countByType;
    }

    public function statsByTypePerHour(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT extract(hour from create_at) as hour, type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload like %{$searchInput->keyword}%
            GROUP BY TYPE, EXTRACT(hour from create_at)
SQL;

        /** @var array<int, array{hour: string, type: string, count: int}> $stats */
        $stats = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date,
        ]);

        /** @var array<positive-int, array{commit: int, pullRequest: int, comment: int}> $data */
        $data = array_fill(0, 24, ['commit' => 0, 'pullRequest' => 0, 'comment' => 0]);

        foreach ($stats as $stat) {
            $data[(int) $stat['hour']][$stat['type']] = $stat['count'];
        }

        return $data;
    }

    public function getLatest(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT type, repo
            FROM event
            WHERE date(create_at) = :date
            AND payload like %{$searchInput->keyword}%
SQL;

        /** @var array<int, array{type: string, repo: string}> $result */
        $result = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date,
            'keyword' => $searchInput->keyword,
        ]);

        /** @var array<int, array{type: string, repo: array<string, mixed>}> $latest */
        $latest = array_map(static function (array $item) {
            $item['repo'] = json_decode($item['repo'], true);

            return $item;
        }, $result);

        return $latest;
    }

    public function exist(int $id): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM event
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $id,
        ]);

        return (bool) $result;
    }
}
