<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contract\Repository\BookRepositoryInterface;
use App\Exception\CantSaveException;
use App\Exception\Client\RequestException;
use App\Exception\EntityNotFoundException;
use App\Model\Book;
use App\Model\BookCollection;

class BookRepository extends AbstractRepository implements BookRepositoryInterface
{
    public const URI = 'books';

    /**
     * @inheritDoc
     */
    public function getMany(int $page = 1, int $limit = 20): BookCollection
    {
        $models = [];

        $request = $this->requestFactory->create(
            'GET',
            self::URI,
            true,
        );

        $request
            ->withPage($page)
            ->withCollection(true);

        $booksData = \json_decode($this->client->request($request)->getBody()->getContents(), true);

        $totalPages = $totalResults = 0;
        $currentPage = 1;

        if (is_iterable($booksData)) {
            [
                $totalPages,
                $currentPage,
                $totalResults
            ] = $this->getResultStats($booksData);

            $items = $booksData['items'] ?? [];

            $models = array_map(function ($item) {
                return Book::createFromArray($item);
            }, $items);
        }

        return new BookCollection($models, $totalPages, $currentPage, $totalResults);
    }

    public function getOne(int $bookId): Book
    {
        $request = $this->requestFactory->create(
            'GET',
            self::URI . '/' . $bookId,
            true
        );

        try {
            $bookData = \json_decode($this->client->request($request)->getBody()->getContents(), true);

            if (is_array($bookData)) {
                return Book::createFromArray($bookData);
            }
        } catch (RequestException) {
        }

        throw new EntityNotFoundException('Book not found');
    }

    /**
     * @inheritDoc
     */
    public function save(Book $book): void
    {
        $method = 'POST';

        $uri = self::URI;

        $bookId = $book->getId();

        if ($bookId !== null) {
            $method = 'PUT';
            $uri .= '/' . $bookId;
        }

        $request = $this->requestFactory->create(
            $method,
            $uri,
            true,
            $book->toArray()
        );

        try {
            $this->client->request($request);
        } catch (RequestException $exception) {
            throw new CantSaveException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function remove(int $bookId): void
    {
        $request = $this->requestFactory->create(
            'DELETE',
            self::URI . '/' . $bookId,
            true
        );

        try {
            $this->client->request($request);
        } catch (RequestException) {
            throw new EntityNotFoundException('Book not found');
        }
    }
}
