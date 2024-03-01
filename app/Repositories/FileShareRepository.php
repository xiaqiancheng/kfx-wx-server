<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\FileShare;
use Belief\Hyperf\Repository;

class FileShareRepository extends Repository
{
    protected static $entity = FileShare::class;
}
