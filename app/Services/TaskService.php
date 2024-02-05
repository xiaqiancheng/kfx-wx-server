<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TaskRepository;

class TaskService
{
    public function getList($filter = [], $cols = ['*'], $page = 1, $pageSize = 10, $orderBy = []) {
        $list = TaskRepository::instance()->getList($filter, $cols, $page, $pageSize, $orderBy);

        if ($list['list']) {
            foreach($list['list'] as $k => $v) {
                $list['list'][$k]['refer_ma_captures'] = explode(',', $v['refer_ma_captures']);
            }
        }

        return $list;
    }

    public function __call($name, $arguments)
    {
        return TaskRepository::instance()->$name(...$arguments);
    }
}