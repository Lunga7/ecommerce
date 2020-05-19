<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;

class AdminController extends Controller
{
    //login method
    public function login(Request $request)
    {
        if($request->isMethod('post'))
        {
            $data = $request->input();
            if(Auth::attempt(['email'=>$data['email'], 'password'=>$data['password'], 'admin'=>'1']))
            {
                //echo "Success"; die;
                return redirect('admin/dashboard');
            }
            else
            {
                return redirect('/admin')->with('flash_message_error', 'Invalid Email or Password');
            }
        }
        return view('admin.admin_login');
    }

    //admin dashboard
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    //logout method
    public function logout()
    {
        Session::flush();
        return redirect('/admin')->with('flash_message_success', 'Logged Out Successfully');
    }
}
