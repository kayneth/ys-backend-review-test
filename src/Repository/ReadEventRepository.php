<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\SearchInput;

interface ReadEventRepository
{
    public function countAll(SearchInput $searchInput): int;

    /**
     * @return array<string, int>
     */
    public function countByType(SearchInput $searchInput): array;

    /**
     * @return array<int, array{commit: int, pullRequest: int, comment: int}>
     * @phpstan-return array<int, non-empty-array<string, int>>
     */
    public function statsByTypePerHour(SearchInput $searchInput): array;

    /**
     * @return list<array{type: string, repo: array<string, mixed>}>
     */
    public function getLatest(SearchInput $searchInput): array;

    public function exist(int $id): bool;
}
