<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Task;
use Belief\Hyperf\Repository;

class TaskRepository extends Repository
{
    protected static $entity = Task::class;
}
