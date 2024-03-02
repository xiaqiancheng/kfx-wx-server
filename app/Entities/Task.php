<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property int $id 
 * @property string $task_name 
 * @property int $task_settle_type 
 * @property string $start_page 
 * @property string $anchor_title 
 * @property string $task_icon 
 * @property int $task_start_time 
 * @property int $task_end_time 
 * @property string $task_desc 
 * @property string $refer_videos 
 * @property string $task_tags 
 * @property string $refer_ma_captures 
 * @property string $refer_video_captures 
 * @property string $refer_gids 
 * @property int $task_refund_period 
 * @property int $payment_allocate_ratio 
 * @property int $stauts 
 * @property int $create_time 
 * @property int $update_time 
 */
class Task extends Entity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';
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
    protected $casts = ['id' => 'integer', 'task_settle_type' => 'integer', 'task_start_time' => 'integer', 'task_end_time' => 'integer', 'task_refund_period' => 'integer', 'payment_allocate_ratio' => 'integer', 'stauts' => 'integer', 'task_tags' => 'array', 'commission' => 'integer', 'shop_id' => 'array', 'create_time' => 'integer', 'update_time' => 'integer'];
}