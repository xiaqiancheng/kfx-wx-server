<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property string $token 
 * @property string $link 
 * @property string $expiration_time 
 * @property int $valid_time 
 * @property string $files 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class FileShare extends Entity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_share';
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
    protected $casts = ['valid_time' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}