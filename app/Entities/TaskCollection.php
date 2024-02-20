<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property int $id 
 * @property int $task_id 
 * @property int $blogger_id 
 * @property int $shop_id 
 * @property string $reserve_time 
 * @property int $extra_cost 
 * @property string $remark 
 * @property int $status 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property string $reject_reason 
 */
class TaskCollection extends Entity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'task_collection';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'task_id' => 'integer', 'blogger_id' => 'integer', 'shop_id' => 'integer', 'extra_cost' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}