<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 25/12/2015
 * Time: 3:42 PM
 */

namespace App\Models\Rbac;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class AdminOperator extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'sqlite';
    protected $table = 'admin_operators';
    protected $guarded = ['id'];

    /**
     * Get the unique identifier for the user.
     *
     * @return int
     */
    public function getKey()
    {
        return $this->id;
    }

    public function role()
    {
        return $this->hasOne('App\Models\Rbac\AdminRole', 'id', 'role_id');
    }
}