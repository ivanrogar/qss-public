<?php

declare(strict_types=1);

namespace App\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ClientFactory
{
    public const BASE_URL = 'https://some-book-api.com/';
    public const BASE_API_URL = self::BASE_URL . 'api/v2/';

    public function create(array $options = []): ClientInterface
    {
        $options = array_replace(['base_uri' => self::BASE_API_URL], $options);

        return new Client($options);
    }
}
