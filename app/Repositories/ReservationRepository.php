<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Reservation;
use Belief\Hyperf\Repository;

class ReservationRepository extends Repository
{
    protected static $entity = Reservation::class;
}
