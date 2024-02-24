<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\MessageNotice;
use Belief\Hyperf\Repository;

class MessageNoticeRepository extends Repository
{
    protected static $entity = MessageNotice::class;
}
