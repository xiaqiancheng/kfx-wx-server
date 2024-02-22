<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property int $id 
 * @property int $blogger_id 
 * @property int $task_id 
 * @property int $amount 
 * @property string $name 
 */
class UserIncomeDetail extends Entity
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_income_detail';
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
    protected $casts = ['id' => 'integer', 'blogger_id' => 'integer', 'task_id' => 'integer', 'amount' => 'integer'];
}