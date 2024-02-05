<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\admin;

class LoginController extends Controller
{
    //create function for display login page     
    public function login(Request $Request)
    {
        if (session()->get('name')) {
            return redirect('/');
        } else {
            return view('login');
        }
    }
    //close function for display login page

    //create function for admin login  and create session
    public function adminlogin(Request $Request)
    {
        if (session()->get('name')) {
            return redirect('/login');
        } else {
            $user = admin::where('email', $Request->email)->where('password', $Request->password)->get()->toarray();
            if ($user) {
                $id = $user['0']['id'];
                $name = $user['0']['f_name'];
                $data =  $Request->input();
                $Request->session()->put('id', $user['0']['id']);
                $Request->session()->put('name', $user['0']['f_name']);
                return redirect('/');
            } else {
                return redirect()->back()->with('message', 'Please insert right email and password!');
            }
        }
    }
    //close function for admin login  and create session

    //create function for vew change password page
    public function password(Request $Request)
    {
        if (session()->get('name')) {
            return view('/change_pass');
        } else {
            return view('login');
        }
    }
    //close function for vew change password page

    //create function for change admin password
    public function change_password(Request $Request)
    {
        if (session()->get('name')) {
            $user = admin::where('password', $Request->old_password)->get()->toarray();
            if ($user) {
                // $id = $user['0']['id'];
                $User = admin::find(session()->get('id'));
                $User->f_name = $User->f_name;
                $User->email = $User->email;
                $User->password = $Request['new_password'];
                $User->save();
                return redirect()->back()->with('message', 'You are successfully change your password!');
            } else {
                return redirect()->back()->with('message', 'Please insert right old password!');
            }
            return view('/change_pass');
        } else {
            return view('login');
        }
    }
    //close function for change admin password

    //create function for view admin profile page
    public function profile(Request $Request)
    {
        if (session()->get('name')) {
            $admin = admin::find(session()->get('id'));
            $data = compact('admin');
            return view('/change_profile')->with($data);
        } else {
            return view('login');
        }
    }
    //close function for view admin profile page

    //create function for change admin profile
    public function change_profile(Request $Request)
    {
        if (session()->get('name')) {
            $User = admin::find(session()->get('id'));
            $User->f_name = $Request['name'];
            $User->email = $Request['email'];
            $User->password = $User->password;
            $User->save();
            return redirect()->back()->with('message', 'You are successfully update your profile!');
        } else {
            return view('login');
        }
    }
    //create function for change admin profile

    //create function for view main index page
    public function index()
    {
        if (session()->get('name')) {
            return view('index');
        } else {
            return view('login');
        }
    }
    //close function for view main index page
}
