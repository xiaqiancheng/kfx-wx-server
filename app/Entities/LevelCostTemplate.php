<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property int $template_id 
 * @property int $level_id 
 * @property int $cost 
 */
class LevelCostTemplate extends Entity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'level_cost_template';
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
    protected $casts = ['template_id' => 'integer', 'level_id' => 'integer', 'cost' => 'integer'];
}