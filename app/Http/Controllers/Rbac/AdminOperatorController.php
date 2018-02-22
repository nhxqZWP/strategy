<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 23/12/2015
 * Time: 6:32 PM
 */

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Rbac\AdminOperator;
use App\Models\Rbac\AdminRole;
use Illuminate\Http\Request;

class AdminOperatorController extends Controller
{

    public function getList(Request $request)
    {
        $list = AdminOperator::with('role')->paginate();
        $data = ['list' => $list];

        return view('rbac.admin_operator.list', $data);
    }

    public function getForm()
    {
        $id = app('request')->input('id');
        $item = AdminOperator::findOrNew($id);
        $roleList = AdminRole::all();
        return view('rbac.admin_operator.form', ['item' => $item, 'roleList' => $roleList]);
    }

    public function postForm(Request $request)
    {
        $input = $request->only(['id', 'name', 'role_id', 'password', 'password2']);

        $rule = [
            'password' => 'required',
            'password2' => 'required',
            'name' => 'required',
            'role_id' => 'required|numeric',
        ];

        if ($request->has('id')) {
            unset($rule['password']);
            unset($rule['password2']);
        }

        $resultError = $this->mustValidate($input, $rule);
        if (!is_null($resultError)) {
            return $resultError;
        }

        if ($input['password'] !== $input['password2']) {
            return redirect()->back()->withInput()->with('error', '两次密码不一致');
        }

        $item = AdminOperator::findOrNew($input['id']);
        unset($input['id']);
        unset($input['password2']);
        if (!empty($input['password'])) {
            $input['password'] = bcrypt($input['password']);
        } else {
            unset($input['password']);
        }

        $item->fill($input);
        $item->save();

        if ($item->exists) {
            return redirect()->back()->with(['message' => '修改成功']);
        }

        return redirect()->back()->with(['error' => '修改失败']);
    }

    public function getDelete()
    {
        $id = app('request')->input('id');
        if ($id > 1) {
            AdminOperator::destroy($id);
        }
        return redirect()->back()->with(['message' => '删除成功']);
    }

    public function getPassword()
    {
        $request = app('request');
        if ($request->isMethod('GET')) {
            return view('rbac.admin.auth.password');
        } else {
            $oldPassword = $request->input('oldPassword');
            $password = $request->input('password');
            $password2 = $request->input('password2');
            $user = \Auth::user();

            if (!\Auth::validate(['name' => $user->name, 'password' => $oldPassword])) {
                return redirect()->back()->withInput()->with('error', '密码错误');
            }
            if ($password2 !== $password) {
                return redirect()->back()->withInput()->with('error', '两次密码不一致');
            }
            $user->update(['password' => bcrypt($password)]);

            return redirect()->back()->with(['message' => '修改成功']);
        }
    }
}