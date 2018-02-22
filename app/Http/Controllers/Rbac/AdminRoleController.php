<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 27/12/2015
 * Time: 5:59 PM
 */

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Rbac\AdminModule;
use App\Models\Rbac\AdminRole;
use Cache;

class AdminRoleController extends Controller {

    public function getList()
    {
        $list = AdminRole::paginate();
        return view('rbac.admin_role.list', ['list' => $list]);
    }

    public function getForm()
    {
        $id = app('request')->input('id');
        $item = AdminRole::findOrNew($id);
        $rootModules = AdminModule::where('parent_id', 0)->get();
        $currentModIds = explode(',', $item->privileges);
        $currentModIds = array_map(function ($each) {
            return (int)trim($each);
        }, $currentModIds);
        return view('rbac.admin_role.form',
            ['item' => $item, 'rootModules' => $rootModules, 'currentModIds' => $currentModIds]);
    }

    public function postForm()
    {
        $input = app('request')->only(['id', 'name', 'privileges']);

        $rule = [
            'name' => 'required',
            'privileges' => 'required',
        ];
        $resultError = $this->mustValidate($input, $rule);
        if(!is_null($resultError)){
            return $resultError;
        }

        $input['privileges'] = join(',', $input['privileges']);
        $item = AdminRole::findOrNew($input['id']);
        $item->fill($input);
        $item->save();
        if ($item->exists) {
            Cache::forget($input['id'] . '_priv_list');
            return redirect()->back()->withInput()->with('message', '修改成功');
        }
        return redirect()->back()->withInput()->with('error', '修改失败');
    }

    public function getDelete()
    {
        $id = app('request')->input('id');
        if ($id > 1) {
            AdminRole::destroy($id);
        }
        return redirect()->back()->withInput()->with('message', '删除成功');
    }
}