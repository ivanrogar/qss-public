<?php

declare(strict_types=1);

namespace App\Contract\Repository;

use App\Exception\CantSaveException;
use App\Exception\EntityNotFoundException;
use App\Model\Author;
use App\Model\AuthorCollection;

interface AuthorRepositoryInterface extends RepositoryInterface
{
    public function getMany(
        int $page = 1,
        int $limit = self::DEFAULT_LIMIT,
        ?string $orderBy = null
    ): AuthorCollection;

    /**
     * @throws EntityNotFoundException
     */
    public function getOne(int $authorId): Author;

    /**
     * @param Author $author
     * @throws CantSaveException
     */
    public function save(Author $author): void;

    /**
     * @throws EntityNotFoundException
     */
    public function remove(int $authorId): void;
}
