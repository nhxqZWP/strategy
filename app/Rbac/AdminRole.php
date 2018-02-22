<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 25/12/2015
 * Time: 3:42 PM
 */

namespace App\Models\Rbac;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'admin_roles';
    protected $guarded = ['id'];
}