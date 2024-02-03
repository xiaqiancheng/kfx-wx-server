<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Tag;
use Belief\Hyperf\Repository;

class TagRepository extends Repository
{
    protected static $entity = Tag::class;
}
