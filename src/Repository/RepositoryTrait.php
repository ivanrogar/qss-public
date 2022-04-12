<?php

declare(strict_types=1);

namespace App\Repository;

trait RepositoryTrait
{
    protected function getResultStats(array $data): array
    {
        $totalPages = $data['total_pages'] ?? 0;
        $currentPage = $data['current_page'] ?? 1;
        $totalResults = $data['total_results'] ?? 0;

        return [
            $totalPages,
            $currentPage,
            $totalResults
        ];
    }
}
