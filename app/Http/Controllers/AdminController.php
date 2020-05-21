<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use App\User;
use Illuminate\Support\Facades\Hash;

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
                //Session::put('adminSession', $data['email']);
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
        /*if(Session::has('adminSession')){
        }else{
            return redirect('/admin')->with('flash_message_error', 'Please Login to gain Access');
        }
        */
        return view('admin.dashboard');
    }

    //settings method
    public function settings()
    {
        return view('admin.settings');
    }

    //cecking password method
    public function checkPassword(Request $request)
    {/*
        $data = $request->all();
        $current_password = $data['current_pwd'];
        $check_password = User::where(['admin'=>'1'])->first();
        if(Hash::check($current_password,$check_password->password))
        {
            echo "true"; die;
        }else{
            echo "false"; die;
        }*/
    }

    //update password method
    public function updatePassword(Request $request)
    {
        if($request->isMethod('post'))
        {
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            $check_password = User::where(['email' => Auth::user()->email])->first();
            $current_password = $data['current_pwd'];
            if(Hash::check($current_password,$check_password->password))
            {
                $password = bcrypt($data['new_pwd']);
                User::where('id','1')->update(['password'=>$password]);
                return redirect('/admin/settings')->with('flash_message_success','Password updated Successfully!');
            }else 
            {
                return redirect('/admin/settings')->with('flash_message_error','Incorrect Current Password!');
            }
        }
    }


    //logout method
    public function logout()
    {
        Session::flush();
        return redirect('/admin')->with('flash_message_success', 'Logged Out Successfully');
    }
}
