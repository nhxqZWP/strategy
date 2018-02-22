<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/5/19
 * Time: 21:26
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RbacMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check login
        if (Auth::guest()) {
            if (!$request->ajax()) {
                return redirect('/login');
            } else {
                return response()->json(['errcode' => 401, 'errmsg' => 'need login']);
            }
        }

        // check privilege
        if (Auth::user()->role->id != 1) {
            $approved = false;
            list($controller, $action) = explode('@', $request->route()->getAction()['uses'], 2);
            if ($controller && $action) {
                $rolePrivileges = explode(',', Auth::user()->role->privileges);
                $privileges = DB
                    ::connection('sqlite')
                    ->table('admin_modules')
                    ->whereIn('id', $rolePrivileges)
                    ->get(['priv_list']);
                if ($privileges->isNotEmpty()) {
                    $privileges = array_map(function ($item) {
                        return $item->priv_list;
                    }, $privileges->toArray());
                    $privileges = array_filter($privileges);
                    if ($privileges) {
                        $privileges = array_reduce($privileges, function ($carry, $item) {
                            $items = explode(',', $item);
                            return array_merge($carry, $items);
                        }, []);
                        $approved = $this->validate($controller, $action, $privileges);
                    }
                }
            }

            if (!$approved) {
                if (!$request->ajax()) {
                    return redirect('/no-auth');
                } else {
                    return response()->json(['errcode' => 403, 'errmsg' => 'permission deny']);
                }
            }
        }

        return $next($request);
    }

    public function validate($controller, $action, $privileges)
    {
        if ($privileges) {
            $controller = substr($controller, strlen('App\\Http\\Controllers\\'), -strlen('Controller'));
            foreach ($privileges as $route) {
                if (trim($route) === '*') {
                    return true;
                }

                if (str_contains($route, '.')) {
                    if ($controller . '.' . $action === $route) {
                        return true;
                    }
                } else {
                    if ($controller === $route) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
