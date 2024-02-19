<?php

declare (strict_types=1);
namespace App\Entities;

use Belief\Hyperf\Database\Entity;
/**
 * @property int $id 
 * @property string $openid 
 * @property string $unionid 
 * @property string $name 
 * @property string $nickName 
 * @property string $avatarUrl 
 * @property int $gender 
 * @property string $city 
 * @property string $province 
 * @property string $country 
 * @property string $language 
 * @property string $acount 
 * @property int $create_time 
 * @property int $update_time 
 * @property string $phone 
 * @property string $doyin_id 
 * @property string $income 
 * @property int $level 
 */
class Blogger extends Entity
{
    public $timestamps = false;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blogger';
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
    protected $casts = ['id' => 'integer', 'gender' => 'integer', 'create_time' => 'integer', 'update_time' => 'integer', 'level' => 'integer'];
}