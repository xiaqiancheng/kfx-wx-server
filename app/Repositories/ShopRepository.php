<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Shop;
use Belief\Hyperf\Repository;

class ShopRepository extends Repository
{
    protected static $entity = Shop::class;
}
