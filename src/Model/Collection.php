<?php

declare(strict_types=1);

namespace App\Model;

use ArrayIterator;

class Collection extends ArrayIterator
{
    private int $totalPages;
    private int $currentPage;
    private int $totalResults;

    public function __construct(array $data, int $totalPages, int $currentPage, int $totalResults, $flags = 0)
    {
        parent::__construct($data, $flags);

        $this->totalPages = $totalPages;
        $this->currentPage = $currentPage;
        $this->totalResults = $totalResults;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalResults(): int
    {
        return $this->totalResults;
    }
}
