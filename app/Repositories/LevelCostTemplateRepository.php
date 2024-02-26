<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\LevelCostTemplate;
use Belief\Hyperf\Repository;

class LevelCostTemplateRepository extends Repository
{
    protected static $entity = LevelCostTemplate::class;
}
