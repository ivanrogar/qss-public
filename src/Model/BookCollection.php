<?php

declare(strict_types=1);

namespace App\Model;

class BookCollection extends Collection
{
    public function current(): Book
    {
        return parent::current();
    }
}
