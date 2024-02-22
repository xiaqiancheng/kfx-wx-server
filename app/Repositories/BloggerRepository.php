<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Blogger;
use Belief\Hyperf\Repository;

class BloggerRepository extends Repository
{
    protected static $entity = Blogger::class;

    public function addScore($userId, $score, $description, $taskId = 0)
    {
        $user = $this->getModel()
        ->find($userId);

        if ($user && $score != 0) {
            $after = $user->income + $score;
            //更新会员信息
            $user->save(['income' => $after]);
            //写入日志
            UserIncomeDetailRepository::instance()->saveData(['blogger_id' => $userId, 'amount' => $score, 'name' => $description, 'task_id' => $taskId]);
        }
    }
}
