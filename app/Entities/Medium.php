<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property int $id 
 * @property string $media_id 
 * @property string $path 
 * @property string $file_type 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Medium extends Entity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';
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
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}