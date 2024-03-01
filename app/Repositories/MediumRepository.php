<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Medium;
use Belief\Hyperf\Repository;

class MediumRepository extends Repository
{
    protected static $entity = Medium::class;
}