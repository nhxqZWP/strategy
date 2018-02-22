<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/5/19
 * Time: 21:20
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Duo;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller {

    function getLogin()
    {
        return view('login.index', ['error' => session('error')]);
    }

    function postLogin(Request $request)
    {
		if (env('APP_ENV') != 'local') {
	    	$IKEY = env('DUO_IKEY');
	    	$SKEY = env('DUO_SKEY');
	    	$HOST = env('DUO_HOST');
	    	$AKEY = env('DUO_AKEY');
	    	$input = $request->only(['name', 'password','sig_response']);
	    
	    	if(isset($input['sig_response'])) {
				$input = $request->only(['sig_response']);
				$resp = Duo::verifyResponse($IKEY, $SKEY, $AKEY, $input['sig_response']);
				if ($resp === session('duo_name')) {
					// Password protected content would go here.
					$data['name'] = session('name');
					$data['password'] = session('password');
					$logged = Auth::attempt($data);
					return redirect('/');
				}
			}
	        $input = $request->only(['name', 'password']);
	        $logged = Auth::validate($input);
	        if ($logged) {
	        	session(['duo_name'=>$input['name']]);
	        	session(['name'=>$input['name']]);
	        	session(['password'=>$input['password']]);
	        	
	        	$sig_request = Duo::signRequest($IKEY, $SKEY, $AKEY,  $input['name']);
	            return view('login.duo', ['error' => session('error'),'sig_request'=>$sig_request,'HOST'=>$HOST]);
	        }
	        
	        return redirect()->back()->withInput()->with('error', '用户名密码错误');
    	}else{
    		$input = $request->only(['name', 'password']);
    		$logged = Auth::attempt($input);
			if ($logged) {
				return redirect('/');
			}
    		return redirect()->back()->withInput()->with('error', '用户名密码错误');
    	}
    }

    function getLogout()
    {
        Auth::logout();
        return redirect('/login');
    }

    function getNoAuth()
    {
        return view('index.no_auth');
    }
}