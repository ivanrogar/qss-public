<?php

declare(strict_types=1);

namespace App\Model;

class AuthorCollection extends Collection
{
    public function current(): Author
    {
        return parent::current();
    }
}
