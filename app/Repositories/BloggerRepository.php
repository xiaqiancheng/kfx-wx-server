<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Blogger;
use Belief\Hyperf\Repository;

class BloggerRepository extends Repository
{
    protected static $entity = Blogger::class;
}
