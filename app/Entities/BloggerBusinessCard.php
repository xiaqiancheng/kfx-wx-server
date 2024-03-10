<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property int $id 
 * @property int $blogger_id 
 * @property string $douyin_id 
 * @property string $nick_name 
 * @property int $fans_count 
 * @property int $digg_count 
 * @property int $level_id 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class BloggerBusinessCard extends Entity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blogger_business_card';
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
    protected $casts = ['id' => 'integer', 'blogger_id' => 'integer', 'fans_count' => 'integer', 'digg_count' => 'integer', 'level_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}