<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        print_r(DB::connection('sqlite')->table('admin_operators')->get());
        print_r(DB::connection('sqlite')->table('admin_roles')->get());
        print_r(DB::connection('sqlite')->table('admin_modules')->get());

        DB::connection('sqlite')
            ->table('admin_operators')
            ->insert([
                'id' => 1,
                'name' => 'admin',
                'role_id' => 1,
                'password' => '$2y$10$2sLFwPsKKRd6J6ZRRcTqtOlfbUFSqCdIP6wg3z82j2C5e2gKBnZMe',
                'remember_token' => '',
                'created_at' => '2015-12-27 14:14:58',
                'updated_at' => '2015-12-29 07:40:48',
            ]);

        DB::connection('sqlite')
            ->table('admin_roles')
            ->insert([
                'id' => 1,
                'name' => '管理员',
                'privileges' => '2,3,4',
                'created_at' => '2015-12-27 14:14:58',
                'updated_at' => '2015-12-29 09:52:48',
            ]);

        DB::connection('sqlite')
            ->table('admin_modules')
            ->insert([
                [
                    'id' => 1,
                    'parent_id' => 0,
                    'name' => '管理员权限',
                    'priv_list' => '',
                    'created_at' => '2015-12-27 14:14:58',
                    'updated_at' => '2015-12-27 14:14:58',
                    'deleted_at' => '',
                ],
                [
                    'id' => 2,
                    'parent_id' => 1,
                    'name' => '管理员',
                    'priv_list' => '',
                    'created_at' => '2015-12-27 14:14:58',
                    'updated_at' => '2015-12-27 14:14:58',
                    'deleted_at' => '',
                ],
                [
                    'id' => 3,
                    'parent_id' => 1,
                    'name' => '模块',
                    'priv_list' => '',
                    'created_at' => '2015-12-27 14:14:58',
                    'updated_at' => '2015-12-27 14:14:58',
                    'deleted_at' => '',
                ],
                [
                    'id' => 4,
                    'parent_id' => 1,
                    'name' => '角色',
                    'priv_list' => '',
                    'created_at' => '2015-12-27 14:14:58',
                    'updated_at' => '2015-12-27 14:14:58',
                    'deleted_at' => '',
                ],
            ]);
    }
}
