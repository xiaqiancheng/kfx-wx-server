<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\UserIncomeDetail;
use Belief\Hyperf\Repository;

class UserIncomeDetailRepository extends Repository
{
    protected static $entity = UserIncomeDetail::class;
}
