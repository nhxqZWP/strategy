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

class AdminModuleController extends Controller {

    public function getList()
    {
        $list = AdminModule::paginate();
        return view('rbac.admin_module.list', ['list' => $list]);
    }

    public function getForm()
    {
        $id = intval(app('request')->input('id'));
        $item = AdminModule::findOrNew($id);
        $rootModules = AdminModule::where('parent_id', 0)->get();
        return view('rbac.admin_module.form', ['item' => $item, 'rootModules' => $rootModules]);
    }

    public function postForm()
    {
        $input = app('request')->only(['id', 'name', 'parent_id', 'priv_list']);

        $rule = [
            'parent_id' => 'required|numeric',
            'priv_list' => 'required',
            'name' => 'required',
        ];
        $resultError = $this->mustValidate($input, $rule);
        if(!is_null($resultError)){
            return $resultError;
        }

        $input['priv_list'] = join(',', preg_split('/[\s,]+/', $input['priv_list']));
        $item = AdminModule::findOrNew($input['id']);
        $item->fill($input);
        $item->save();
        if ($item->exists) {
            return redirect()->back()->withInput()->with('message', '修改成功');
        }
        return redirect()->back()->withInput()->with('error', '修改失败');
    }

    public function getDelete()
    {
        $id = app('request')->input('id');
        if ($id > 4) {
            AdminModule::where('id', $id)->delete();
        }
        return redirect()->back()->withInput()->with('message', '删除成功');
    }
}