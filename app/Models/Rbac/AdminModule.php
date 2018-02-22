<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 25/12/2015
 * Time: 3:42 PM
 */

namespace App\Models\Rbac;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminModule extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $connection = 'sqlite';
    protected $table = 'admin_modules';
    protected $guarded = ['id'];

    public function children()
    {
        return $this->hasMany('App\Models\Rbac\AdminModule', 'parent_id', 'id');
    }

}