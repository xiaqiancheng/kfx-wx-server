<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\BloggerBusinessCard;
use Belief\Hyperf\Repository;

class BloggerBusinessCardRepository extends Repository
{
    protected static $entity = BloggerBusinessCard::class;
}
