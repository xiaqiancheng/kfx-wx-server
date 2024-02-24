<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Article;
use Belief\Hyperf\Repository;

class ArticleRepository extends Repository
{
    protected static $entity = Article::class;
}
