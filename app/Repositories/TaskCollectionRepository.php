<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\TaskCollection;
use Belief\Hyperf\Repository;

class TaskCollectionRepository extends Repository
{
    protected static $entity = TaskCollection::class;
}
