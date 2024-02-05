<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property int $id 
 * @property int $blogger_id 
 * @property string $openid 
 * @property int $create_time 
 * @property string $detail 
 * @property string $item_id 
 * @property int $share_count 
 * @property int $forward_count 
 * @property int $comment_count 
 * @property int $digg_count 
 * @property int $download_count 
 * @property int $play_count 
 * @property string $video_title 
 * @property string $cover 
 * @property string $video_link 
 * @property string $author 
 * @property int $task_id 
 * @property int $income 
 */
class Video extends Entity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'video';
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
    protected $casts = ['id' => 'integer', 'blogger_id' => 'integer', 'create_time' => 'integer', 'share_count' => 'integer', 'forward_count' => 'integer', 'comment_count' => 'integer', 'digg_count' => 'integer', 'download_count' => 'integer', 'play_count' => 'integer', 'task_id' => 'integer', 'income' => 'integer'];
}