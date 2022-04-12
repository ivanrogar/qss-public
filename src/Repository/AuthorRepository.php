<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\AuthorRepositoryInterface;
use App\Exception\CantSaveException;
use App\Exception\Client\RequestException;
use App\Exception\EntityNotFoundException;
use App\Model\Author;
use App\Model\AuthorCollection;

class AuthorRepository extends AbstractRepository implements AuthorRepositoryInterface
{
    public const URI = 'authors';

    public function getMany(
        int $page = 1,
        int $limit = 20,
        ?string $orderBy = null
    ): AuthorCollection {
        $models = [];

        $request = $this->requestFactory->create(
            'GET',
            self::URI,
            true
        );

        $request
            ->withPage($page)
            ->withCollection(true)
            ->withLimit($limit);

        if ($orderBy !== null) {
            $request->withOrderBy($orderBy);
        }

        $authorsData = \json_decode($this->client->request($request)->getBody()->getContents(), true);

        $totalPages = $totalResults = 0;
        $currentPage = 1;

        if (is_iterable($authorsData)) {
            [
                $totalPages,
                $currentPage,
                $totalResults
            ] = $this->getResultStats($authorsData);

            $items = $authorsData['items'] ?? [];

            foreach ($items as $authorData) {
                $models[] = Author::createFromArray($authorData);
            }
        }

        return new AuthorCollection($models, $totalPages, $currentPage, $totalResults);
    }

    /**
     * @inheritDoc
     */
    public function getOne(int $authorId): Author
    {
        $request = $this->requestFactory->create(
            'GET',
            self::URI . '/' . $authorId,
            true
        );

        try {
            $authorData = \json_decode($this->client->request($request)->getBody()->getContents(), true);

            if (is_array($authorData)) {
                return Author::createFromArray($authorData);
            }
        } catch (RequestException) {
        }

        throw new EntityNotFoundException('Author not found');
    }

    /**
     * @inheritDoc
     */
    public function save(Author $author): void
    {
        $method = 'POST';

        $uri = self::URI;

        $authorId = $author->getId();

        if ($authorId !== null) {
            $method = 'PUT';
            $uri .= '/' . $authorId;
        }

        $request = $this->requestFactory->create(
            $method,
            $uri,
            true,
            $author->toArray()
        );

        try {
            $this->client->request($request);
        } catch (RequestException $exception) {
            throw new CantSaveException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function remove(int $authorId): void
    {
        $request = $this->requestFactory->create(
            'DELETE',
            self::URI . '/' . $authorId,
            true
        );

        try {
            $this->client->request($request);
        } catch (RequestException) {
            throw new EntityNotFoundException('Author not found');
        }
    }
}
