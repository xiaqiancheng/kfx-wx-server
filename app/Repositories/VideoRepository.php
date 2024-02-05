<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Video;
use Belief\Hyperf\Repository;

class VideoRepository extends Repository
{
    protected static $entity = Video::class;
}
