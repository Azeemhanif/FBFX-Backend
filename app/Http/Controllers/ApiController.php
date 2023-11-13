<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\post_signal;
use App\Models\announcement;
use App\Models\notification;
use App\Models\affiliate_link;
use App\Models\academy;
use App\Models\analysis;
use App\Models\premium;
use App\Models\plan_request;
use App\Models\premium_detail;
use App\Models\admin;
use App\Models\feedback;
use App\Models\chat;
use Illuminate\Support\Facades\File;
use Mail;
use DB;
use DateTime;

class ApiController extends Controller
{
    // start  users login function

    public function user_login(Request $Request)
    {
        $response = array();
        if ($Request['type'] == 'social') {
            $user_name = admin::where('email', $Request->email)
                ->get()->toarray();
        } else {
            $user_name = admin::where('email', $Request->email)
                ->where('password', $Request->password)
                ->get()->toarray();
        }
        if ($user_name) {
            foreach ($user_name as $row) {
                $user["id"] = $row['id'];
                $user["f_name"] = $row['f_name'];
                $user["l_name"] = $row['l_name'];
                $user["email"] = $row['email'];
                $user["password"] = $row['password'];
                $user["mobile"] = $row['mobile'];
                $user["experience"] = $row['experience'];
                $user["age"] = $row['age'];
                $user["gender"] = $row['gender'];
                $user["type"] = $row['type'];
                $user["role"] = $row['role'];
                $user["created_at"] = $row['created_at'];
                $user["updated_at"] = $row['updated_at'];
            }

            $response["user"] = array();
            array_push($response["user"], $user);
            $response['success'] = 1;
            $response["message"] = "You are successfully login";
            return json_encode($response);
        } else {

            $response['success'] = 0;
            $response["message"] = "Please enter correct email and password";
            return json_encode($response);
        }
    }
    // end   users login function

    // start  users fetch function

    public function users()
    {
        $response = array();
        $user_name = admin::select('*')
            ->where('role', '=', '0')
            ->get();
        $count = count($user_name);
        if ($count != 0) {
            $response["user_name"] = array();
            foreach ($user_name as $row) {
                $users["id"] = $row['id'];
                $users["f_name"] = $row['f_name'];
                $users["l_name"] = $row['l_name'];
                $users["email"] = $row['email'];
                $users["password"] = $row['password'];
                $users["mobile"] = $row['mobile'];
                $users["experience"] = $row['experience'];
                $users["age"] = $row['age'];
                $users["gender"] = $row['gender'];
                $users["type"] = $row['type'];
                $users["role"] = $row['role'];
                $users["created_at"] = $row['created_at'];
                $users["updated_at"] = $row['updated_at'];
                array_push($response["user_name"], $users);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch users";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  users fetch function

    // start  add users fetch function

    public function add_users(Request $Request)
    {
        $response = array();
        $user_name = admin::where('email', $Request->email)->where('role', '0')->get()->toarray();
        if (empty($user_name)) {
            $user = new admin;
            $user->role = '0';
            $user->f_name = $Request['f_name'];
            $user->plan = "free";
            $user->l_name = $Request['l_name'];
            $user->email = $Request['email'];
            if ($Request['type'] == 'social') {
                $user->password = '';
                $user->mobile = '';
            } else {
                $user->password = $Request['password'];
                $user->mobile = $Request['mobile'];
            }
            $user->experience = '';
            $user->age = '';
            $user->gender = '';
            $user->image = '';
            $user->type = '';
            $Result = $user->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Registration successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Registration Faild";
                return json_encode($response);
            }
        } else {
            $response['success'] = 0;
            $response["message"] = "user is already Exist";
            return json_encode($response);
        }
    }
    // end  add users fetch function

    // start  edit users fetch function

    public function edit_user($id)
    {
        $response = array();
        $user_name = admin::select('*')
            ->where('id', '=', $id)
            ->where('role', '=', '0')
            ->get();
        $count = count($user_name);
        if ($count != 0) {
            $response["user_name"] = array();
            foreach ($user_name as $row) {
                $users["id"] = $row['id'];
                $users["f_name"] = $row['f_name'];
                $users["l_name"] = $row['l_name'];
                $users["email"] = $row['email'];
                $users["password"] = $row['password'];
                $users["mobile"] = $row['mobile'];
                $users["role"] = $row['role'];
                $users["created_at"] = $row['created_at'];
                $users["updated_at"] = $row['updated_at'];
                array_push($response["user_name"], $users);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch users";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // start  edit users fetch function

    // start  update users fetch function

    public function update_user(Request $Request)
    {
        $response = array();
        $user_id = admin::find($Request['id']);
        if (!empty($user_id)) {
            if ($user_id->email == $Request['email'] && $user_id->role == '0') {
                $user = admin::find($Request['id']);
                $user->role = '0';
                $user->f_name = $Request['f_name'];
                $user->l_name = $Request['l_name'];
                $user->email = $Request['email'];
                $user->password = $Request['password'];
                $user->mobile = $Request['mobile'];
                $user->experience = $Request['experience'];
                $user->age = $Request['age'];
                $user->gender = $Request['gender'];
                $user->type = $Request['type'];
                if (!empty($Request['image'])) {
                    $file = $Request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . "." . $extension;
                    $file->move('uploads/images/', $filename);
                    $user->image = $filename;
                } else {
                    $user->image = " ";
                }
                $Result = $user->save();
                if ($Result) {
                    $response['success'] = 1;
                    $response["message"] = "Record update successfully";
                    return json_encode($response);
                } else {
                    $response['success'] = 0;
                    $response["message"] = "Record update Faild";
                    return json_encode($response);
                }
            } else {
                $user_name = admin::where('email', $Request->email)->where('role', '0')->get()->toarray();
                if (empty($user_name)) {
                    $user = admin::find($Request['id']);
                    $user->role = '0';
                    $user->f_name = $Request['f_name'];
                    $user->l_name = $Request['l_name'];
                    $user->email = $Request['email'];
                    $user->password = $Request['password'];
                    $user->mobile = $Request['mobile'];
                    $user->experience = $Request['experience'];
                    $user->age = $Request['age'];
                    $user->gender = $Request['gender'];
                    $user->type = $Request['type'];
                    if (!empty($Request['image'])) {
                        $file = $Request->file('image');
                        $extension = $file->getClientOriginalExtension();
                        $filename = time() . "." . $extension;
                        $file->move('uploads/images/', $filename);
                        $user->image = $filename;
                    } else {
                        $user->image = " ";
                    }
                    $Result = $user->save();
                    if ($Result) {
                        $response['success'] = 1;
                        $response["message"] = "Record update successfully";
                        return json_encode($response);
                    } else {
                        $response['success'] = 0;
                        $response["message"] = "Record update Faild";
                        return json_encode($response);
                    }
                } else {
                    $response['success'] = 0;
                    $response["message"] = "user is already Exist";
                    return json_encode($response);
                }
            }
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end  update users fetch function

    // start edit user delete function    

    public function user_delete($id)
    {
        $response = array();
        $user = admin::find($id);
        if (empty($user)) {
            $response['success'] = 0;
            $response["message"] = "Id cannot not exist";
            return json_encode($response);
        } else {
            $post_signal = post_signal::select('*')
                ->where('user_id', '=', $id)
                ->delete();
            $Result = $user->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit user delete function


    // start  admins fetch function

    public function admins()
    {
        $response = array();
        $user_name = admin::select('*')
            ->where('role', '=', '1')
            ->where('type', '=', '')
            ->get();
        $count = count($user_name);
        if ($count != 0) {
            $response["user_name"] = array();
            foreach ($user_name as $row) {
                $admins["id"] = $row['id'];
                $admins["email"] = $row['email'];
                $admins["type"] = $row['type'];
                $admins["created_at"] = $row['created_at'];
                $admins["updated_at"] = $row['updated_at'];
                array_push($response["user_name"], $admins);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch admins";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  admins fetch function

    // start  add users fetch function

    public function add_admin(Request $Request)
    {
        $response = array();
        $user_name = admin::where('email', $Request->email)->where('role', '1')->get()->toarray();
        if (empty($user_name)) {
            $user = new admin;
            $user->role = '1';
            $user->f_name = '';
            $user->l_name = '';
            $user->email = $Request['email'];
            $user->password = 'password';
            $user->type = '';
            $user->experience = '';
            $user->age = '';
            $user->gender = '';
            $user->mobile = '';
            $user->image = '';
            $Result = $user->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Registration successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Registration Faild";
                return json_encode($response);
            }
        } else {
            $response['success'] = 0;
            $response["message"] = "admin is already Exist";
            return json_encode($response);
        }
    }
    // end  add admin fetch function

    // start  edit admin fetch function

    public function edit_admin($id)
    {
        $response = array();
        $admin_name = admin::select('*')
            ->where('id', '=', $id)
            ->where('role', '=', '1')
            ->get();
        $count = count($admin_name);
        if ($count != 0) {
            $response["admin_name"] = array();
            foreach ($admin_name as $row) {
                $admins["id"] = $row['id'];
                $admins["email"] = $row['email'];
                $admins["created_at"] = $row['created_at'];
                $admins["updated_at"] = $row['updated_at'];
                array_push($response["admin_name"], $admins);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch admins";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // start  edit admins fetch function

    // start  update admins fetch function

    public function update_admin(Request $Request)
    {
        $response = array();
        $admin_id = admin::find($Request['id']);
        if (!empty($admin_id)) {
            if ($admin_id->email == $Request['email'] && $admin_id->role == '1') {
                $admin = admin::find($Request['id']);
                $admin->role = '1';
                $admin->f_name = $admin->f_name;
                $admin->l_name = $admin->l_name;
                $admin->email = $Request['email'];
                $admin->experience = $Request['experience'];
                $admin->age = $Request['age'];
                $admin->gender = $Request['gender'];
                $admin->password = $admin->password;
                $admin->mobile = $admin->mobile;
                $admin->image = $admin->image;
                $Result = $admin->save();
                if ($Result) {
                    $response['success'] = 1;
                    $response["message"] = "Record update successfully";
                    return json_encode($response);
                } else {
                    $response['success'] = 0;
                    $response["message"] = "Record update Faild";
                    return json_encode($response);
                }
            } else {
                $admin_name = admin::where('email', $Request->email)->where('role', '1')->get()->toarray();
                if (empty($admin_name)) {
                    $admin = admin::find($Request['id']);
                    $admin->role = '1';
                    $admin->f_name = $admin->f_name;
                    $admin->l_name = $admin->l_name;
                    $admin->email = $Request['email'];
                    $admin->experience = $Request['experience'];
                    $admin->age = $Request['age'];
                    $admin->gender = $Request['gender'];
                    $admin->password = $admin->password;
                    $admin->mobile = $admin->mobile;
                    $admin->image = $admin->image;
                    $Result = $admin->save();
                    if ($Result) {
                        $response['success'] = 1;
                        $response["message"] = "Record update successfully";
                        return json_encode($response);
                    } else {
                        $response['success'] = 0;
                        $response["message"] = "Record update Faild";
                        return json_encode($response);
                    }
                } else {
                    $response['success'] = 0;
                    $response["message"] = "admin is already Exist";
                    return json_encode($response);
                }
            }
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end  update admins fetch function

    // start edit admin delete function    

    public function admin_delete($id)
    {
        $response = array();
        $admin = admin::find($id);
        if (empty($admin)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            $post_signal = post_signal::select('*')
                ->where('user_id', '=', $id)
                ->delete();
            $Result = $admin->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit admin delete function

    // start  post singal fetch function

    public function post_signal()
    {
        $response = array();
        $post_name = post_signal::where('pips', '=', 0)->where('closed', '=', 'no')->orderBy('id', 'DESC')->get();
        $count = count($post_name);
        if ($count != 0) {
            $response["post_name"] = array();
            foreach ($post_name as $row) {
                $post_signal["id"] = $row['id'];
                $post_signal["currency_pair"] = $row['currency_pair'];
                $post_signal["action"] = $row['action'];
                $post_signal["stop_loss"] = $row['stop_loss'];
                $post_signal["profit_one"] = $row['profit_one'];
                $post_signal["profit_two"] = $row['profit_two'];
                $post_signal["profit_three"] = $row['profit_three'];
                $post_signal["RRR"] = $row['RRR'];
                $post_signal["type"] = $row['type'];
                $post_signal["pips"] = $row['pips'];
                $post_signal["close_price"] = $row['close_price'];
                $post_signal["open_price"] = $row['open_price'];
                $post_signal["timeframe"] = $row['timeframe'];
                $post_signal["created_at"] = $row['created_at'];
                $post_signal["updated_at"] = $row['updated_at'];
                array_push($response["post_name"], $post_signal);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch user post signal";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  post singal fetch function

    // start  history_signal fetch function

    public function history_signal()
    {
        $response = array();
        $post_name = post_signal::where('closed', '=', 'yes')->orderBy('id', 'DESC')->get();
        $count = count($post_name);
        if ($count != 0) {
            $response["post_name"] = array();
            foreach ($post_name as $row) {
                $post_signal["id"] = $row['id'];
                $post_signal["currency_pair"] = $row['currency_pair'];
                $post_signal["action"] = $row['action'];
                $post_signal["stop_loss"] = $row['stop_loss'];
                $post_signal["profit_one"] = $row['profit_one'];
                $post_signal["profit_two"] = $row['profit_two'];
                $post_signal["profit_three"] = $row['profit_three'];
                $post_signal["RRR"] = $row['RRR'];
                $post_signal["type"] = $row['type'];
                $post_signal["pips"] = $row['pips'];
                $post_signal["close_price"] = $row['close_price'];
                $post_signal["open_price"] = $row['open_price'];
                $post_signal["timeframe"] = $row['timeframe'];
                $post_signal["created_at"] = $row['created_at'];
                $post_signal["updated_at"] = $row['updated_at'];
                array_push($response["post_name"], $post_signal);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch user post signal";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start   fetch function        

    //             // start  signal_posted fetch function

    public function signal_posted($id)
    {
        $response = array();
        // return $id;
        $post_name = post_signal::where('currency_pair', '=', $id)->orderBy('id', 'DESC')->get();
        $count = count($post_name);
        if ($count != 0) {
            $response["post_name"] = array();
            $post_signal["signal_posted"] = $count;
            array_push($response["post_name"], $post_signal);
            $response['success'] = 1;
            $response["message"] = "Successfully fetch user post signal";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  signal_posted fetch function        


    // start  user_post_signal fetch function

    public function user_post_signal($id)
    {
        $response = array();
        $post_name = post_signal::all()->where('user_id', '=', $id);
        $count = count($post_name);
        if ($count != 0) {
            $response["post_name"] = array();
            foreach ($post_name as $row) {
                $post_signal["id"] = $row['id'];
                $post_signal["currency_pair"] = $row['currency_pair'];
                $post_signal["action"] = $row['action'];
                $post_signal["stop_loss"] = $row['stop_loss'];
                $post_signal["profit_one"] = $row['profit_one'];
                $post_signal["profit_two"] = $row['profit_two'];
                $post_signal["profit_three"] = $row['profit_three'];
                $post_signal["RRR"] = $row['RRR'];
                $post_signal["timeframe"] = $row['timeframe'];
                $post_signal["user_id"] = $row['user_id'];
                $post_signal["created_at"] = $row['created_at'];
                $post_signal["updated_at"] = $row['updated_at'];
                array_push($response["post_name"], $post_signal);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch user post signal";
            $response["value"] = 0;
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  user_post_signal fetch function        

    // start add post singal function            

    // public function add_post_signal(Request $Request)
    // {
    //     $response = array();
    //     $post_signal = new post_signal;
    //     $post_signal->currency_pair = $Request['currency_pair'];
    //     $post_signal->action = $Request['action'];
    //     $post_signal->stop_loss = $Request['stop_loss'];
    //     $post_signal->profit_one = $Request['profit_one'];
    //     $post_signal->profit_two = $Request['profit_two'];
    //     $post_signal->profit_three = $Request['profit_three'];
    //     $post_signal->RRR = $Request['RRR'];
    //     $post_signal->timeframe = $Request['timeframe'];
    //     $post_signal->user_id = "0";
    //     $post_signal->open_price = $Request['open_price'];
    //     $post_signal->close_price = '0';
    //     $post_signal->pips = '0';
    //     $post_signal->fvrt = '0';
    //     $post_signal->role = "0";
    //     $post_signal->type = $Request['type'];
    //     $post_signal->created_at = $Request['date'];
    //     $Result = $post_signal->save();
    //     if ($Result) {
    //         $response['success'] = 1;
    //         $response["message"] = "Post signal insert successfull";
    //         return json_encode($response);
    //     } else {
    //         $response['success'] = 0;
    //         $response["message"] = "Post singal insertion Faild";
    //         return json_encode($response);
    //     }
    // }
    // end add post singal function

    // start edit post singal function

    // public function edit_post_signal($id)
    // {
    //     $response = array();
    //     $post_name = post_signal::find($id);
    //     if ($post_name) {
    //         $post_signal["id"] = $post_name['id'];
    //         $post_signal["currency_pair"] = $post_name['currency_pair'];
    //         $post_signal["action"] = $post_name['action'];
    //         $post_signal["stop_loss"] = $post_name['stop_loss'];
    //         $post_signal["profit_one"] = $post_name['profit_one'];
    //         $post_signal["profit_two"] = $post_name['profit_two'];
    //         $post_signal["profit_three"] = $post_name['profit_three'];
    //         $post_signal["RRR"] = $post_name['RRR'];
    //         $post_signal["type"] = $post_name['type'];
    //         $post_signal["pips"] = $post_name['pips'];
    //         $post_signal["close_price"] = $post_name['close_price'];
    //         $post_signal["timeframe"] = $post_name['timeframe'];
    //         $post_signal["created_at"] = $post_name['created_at'];
    //         $post_signal["updated_at"] = $post_name['updated_at'];
    //         $response["post_signal"] = array();
    //         array_push($response["post_signal"], $post_signal);
    //         $response['success'] = 1;
    //         $response["message"] = "Singal post signal fetch";
    //         return json_encode($response);
    //     } else {

    //         $response['success'] = 0;
    //         $response["message"] = "Data cannot be exist";
    //         return json_encode($response);
    //     }
    // }
    // end edit post singal function  

    // start add post singal update function            

    public function update_post_signal(Request $Request)
    {
        $response = array();
        // $post_signal = post_signal::find($id);
        $post_signal = post_signal::find($Request['id']);
        if ($post_signal) {
            $post_signal->currency_pair = $Request['currency_pair'];
            $post_signal->action = $Request['action'];
            $post_signal->stop_loss = $Request['stop_loss'];
            $post_signal->profit_one = $Request['profit_one'];
            $post_signal->profit_two = $Request['profit_two'];
            $post_signal->profit_three = $Request['profit_three'];
            $post_signal->RRR = $Request['RRR'];
            $post_signal->timeframe = $Request['timeframe'];
            $post_signal->type = $Request['type'];
            $Result = $post_signal->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Post signal Update successfull";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Post signal updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add post singal update function   

    // start add update_pips function            

    public function update_pips(Request $Request)
    {
        $response = array();

        // $post_signal = post_signal::find($id);
        $post_signal = post_signal::find($Request['id']);
        if ($post_signal) {

            $post_signal->pips = $Request['pips'];
            $post_signal->close_price = $Request['close_price'];
            $post_signal->closed = 'yes';
            $Result = $post_signal->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Post signal Update successfull";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Post signal updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add update_pips function           

    // end add post singal update function          
    public function add_fvrt(Request $Request)
    {
        $response = array();
        // $post_signal = post_signal::find($id);
        $post_signal = post_signal::find($Request['id']);
        if ($post_signal) {
            $post_signal->fvrt = $Request['fvrt'];
            $Result = $post_signal->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Post signal  add to favourite list";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Post signal Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }


    // start  post singal fetch function

    public function fvrt()
    {
        $response = array();
        $post_name = post_signal::all()->where('fvrt', '=', '1');
        $count = count($post_name);
        if ($count != 0) {
            $response["post_name"] = array();
            foreach ($post_name as $row) {
                $post_signal["id"] = $row['id'];
                $post_signal["currency_pair"] = $row['currency_pair'];
                $post_signal["action"] = $row['action'];
                $post_signal["stop_loss"] = $row['stop_loss'];
                $post_signal["profit_one"] = $row['profit_one'];
                $post_signal["profit_two"] = $row['profit_two'];
                $post_signal["profit_three"] = $row['profit_three'];
                $post_signal["open_price"] = $row['open_price'];
                $post_signal["RRR"] = $row['RRR'];
                $post_signal["type"] = $row['type'];
                $post_signal["timeframe"] = $row['timeframe'];
                $post_signal["created_at"] = $row['created_at'];
                $post_signal["updated_at"] = $row['updated_at'];
                array_push($response["post_name"], $post_signal);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch user post signal";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  post singal fetch function
    // start edit post singal delete function    

    public function post_signal_delete($id)
    {
        $response = array();
        $post_signal = post_signal::find($id);
        if (empty($post_signal)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            $Result = $post_signal->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit post singal delete function    


    // start  announcments fetch function

    public function announcement()
    {
        $response = array();
        $announcement_name = announcement::all();
        $count = count($announcement_name);
        if ($count != 0) {
            $response["announcement_name"] = array();
            foreach ($announcement_name as $row) {
                $announcement["id"] = $row['id'];
                $announcement["description"] = $row['description'];
                $announcement["created_at"] = $row['created_at'];
                $announcement["updated_at"] = $row['updated_at'];
                array_push($response["announcement_name"], $announcement);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  announcement";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  announcments fetch function

    // start add announcments function            

    public function add_announcement(Request $Request)
    {
        $response = array();
        $announcement = new announcement;
        $announcement->description = $Request['description'];
        $Result = $announcement->save();
        if ($Result) {
            $response['success'] = 1;
            $response["message"] = "Announcement insert successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Announcement insertion Faild";
            return json_encode($response);
        }
    }
    // end add announcments function

    // start edit announcments function

    public function edit_announcement($id)
    {
        $response = array();
        $announcement_name = announcement::find($id);
        if ($announcement_name) {
            $announcement["id"] = $announcement_name['id'];
            $announcement["description"] = $announcement_name['description'];
            $announcement["created_at"] = $announcement_name['created_at'];
            $announcement["updated_at"] = $announcement_name['updated_at'];
            $response["announcement"] = array();
            array_push($response["announcement"], $announcement);
            $response['success'] = 1;
            $response["message"] = "Singal announcement fetch";
            return json_encode($response);
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end edit announcments function  

    // start add announcments update function            

    public function update_announcement(Request $Request)
    {
        $response = array();
        // $announcement = announcement::find($id);
        $announcement = announcement::find($Request['id']);
        if ($announcement) {
            $announcement->description = $Request['description'];
            $Result = $announcement->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Announcement Update successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Announcement updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add announcments update function          

    // start edit announcments delete function    

    public function announcement_delete($id)
    {
        $response = array();
        $announcement = announcement::find($id);
        if (empty($announcement)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            $Result = $announcement->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit announcments delete function


    // start  notification fetch function

    public function notification()
    {
        $response = array();
        $notification_name = notification::all();
        $count = count($notification_name);
        if ($count != 0) {
            $response["notification_name"] = array();
            foreach ($notification_name as $row) {
                $notification["id"] = $row['id'];
                $notification["description"] = $row['description'];
                $notification["created_at"] = $row['created_at'];
                $notification["updated_at"] = $row['updated_at'];
                array_push($response["notification_name"], $notification);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch all  notification";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  notification fetch function

    // start add notification function            

    public function add_notification(Request $Request)
    {
        $response = array();
        $notification = new notification;
        $notification->description = $Request['description'];
        $Result = $notification->save();
        if ($Result) {
            $url = 'https://fcm.googleapis.com/fcm/send';
            // Put your Server Key here
            $apiKey = "AAAAHzc33_M:APA91bFGdCvEtHrOCq_rPv1KGjgjLewQCNYgPtTLVnieSVrNCSKJPrmQroWMDjsoRA7KPRz8Yj1rVPW2G9siXSJQ-Z4nZ6d7lM4tU4HZJxRvvnLfuAqP4xf1YUVhHEwn8YtZwR48Yad2";

            // Compile headers in one variable
            $headers = array(
                'Authorization:key=' . $apiKey,
                'Content-Type:application/json'
            );
            // Create the api body
            $apiBody = [
                'data' => $notification,
                'time_to_live' => 10800, // optional - In Seconds
                'to' => '/topics/FirstBuckFx'
                //'registration_ids' = ID ARRAY
                // 'to' => 'cc3y906oCS0:APA91bHhifJikCe-6q_5EXTdkAu57Oy1bqkSExZYkBvL6iKCq2hq3nrqKWymoxfTJRnzMSqiUkrWh4uuzzEt3yF5KZTV6tLQPOe9MCepimPDGTkrO8lyDy79O5sv046-etzqCGmKsKT4'
            ];

            // Initialize curl with the prepared headers and body
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiBody));
            curl_setopt(
                $ch,
                CURLOPT_SSL_VERIFYPEER,
                FALSE
            );
            // Execute call and save result
            $result = curl_exec($ch);
            // print($result);
            // Close curl after call
            curl_close($ch);
            // if($result){
            $response['success'] = 1;
            $response["message"] = "Notification send successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Notification send Faild";
            return json_encode($response);
        }
    }
    // end add notification function

    public function edit_notification($id)
    {
        $response = array();
        $notification_name = notification::find($id);
        if ($notification_name) {
            $notification["id"] = $notification_name['id'];
            $notification["description"] = $notification_name['description'];
            $notification["created_at"] = $notification_name['created_at'];
            $notification["updated_at"] = $notification_name['updated_at'];
            $response["notification"] = array();
            array_push($response["notification"], $notification);
            $response['success'] = 1;
            $response["message"] = "Singal notification fetch";
            return json_encode($response);
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end edit notification function  

    // start add notification update function            

    public function update_notification(Request $Request)
    {
        $response = array();
        // $notification = notification::find($id);
        $notification = notification::find($Request['id']);
        if ($notification) {
            $notification->description = $Request['description'];
            $Result = $notification->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Notification Update successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Notification updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add notification update function          

    // start edit notification delete function    

    public function notification_delete($id)
    {
        $response = array();
        $notification = notification::find($id);
        if (empty($notification)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            $Result = $notification->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit notification delete function

    // start  affiliate_link fetch function

    public function affiliate_link()
    {
        $response = array();
        $affiliate_link_name = affiliate_link::all();
        $count = count($affiliate_link_name);
        if ($count != 0) {
            $response["affiliate_link_name"] = array();
            foreach ($affiliate_link_name as $row) {
                $affiliate_link["id"] = $row['id'];
                $affiliate_link["VPS"] = $row['VPS'];
                $affiliate_link["trade"] = $row['trade'];
                $affiliate_link["PAMMM"] = $row['PAMMM'];
                $affiliate_link["IB_broker"] = $row['IB_broker'];
                $affiliate_link["created_at"] = $row['created_at'];
                $affiliate_link["updated_at"] = $row['updated_at'];
                array_push($response["affiliate_link_name"], $affiliate_link);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  Affiliate link";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  affiliate_link fetch function

    // start add affiliate_link function            

    public function add_affiliate_link(Request $Request)
    {
        $response = array();
        $affiliate_link = new affiliate_link;
        $affiliate_link->VPS = $Request['VPS'];
        $affiliate_link->trade = $Request['trade'];
        $affiliate_link->PAMM = $Request['PAMM'];
        $affiliate_link->IB_broker = $Request['IB_broker'];
        $Result = $affiliate_link->save();
        if ($Result) {
            $response['success'] = 1;
            $response["message"] = "Affiliate Link insert successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Affiliate Link insertion Faild";
            return json_encode($response);
        }
    }
    // end add affiliate_link function

    // start edit affiliate_link function

    public function edit_affiliate_link($id)
    {
        $response = array();
        $affiliate_link_name = affiliate_link::find($id);
        if ($affiliate_link_name) {
            $affiliate_link["id"] = $affiliate_link_name['id'];
            $affiliate_link["VPS"] = $affiliate_link_name['VPS'];
            $affiliate_link["trade"] = $affiliate_link_name['trade'];
            $affiliate_link["PAMM"] = $affiliate_link_name['PAMM'];
            $affiliate_link["IB_broker"] = $affiliate_link_name['IB_broker'];
            $affiliate_link["created_at"] = $affiliate_link_name['created_at'];
            $affiliate_link["updated_at"] = $affiliate_link_name['updated_at'];
            $response["affiliate_link"] = array();
            array_push($response["affiliate_link"], $affiliate_link);
            $response['success'] = 1;
            $response["message"] = "Singal Affiliate link fetch";
            return json_encode($response);
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end edit affiliate_link function  

    // start add affiliate_link update function            

    public function update_affiliate_link(Request $Request)
    {
        $response = array();
        // $affiliate_link = affiliate_link::find($id);
        $affiliate_link = affiliate_link::find($Request['id']);
        if ($affiliate_link) {
            $affiliate_link->VPS = $Request['VPS'];
            $affiliate_link->trade = $Request['trade'];
            $affiliate_link->PAMM = $Request['PAMM'];
            $affiliate_link->IB_broker = $Request['IB_broker'];
            $Result = $affiliate_link->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Affiliate link Update successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Affiliate link updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add affiliate_link update function          

    // start edit affiliate_link delete function    

    public function affiliate_link_delete($id)
    {
        $response = array();
        $affiliate_link = affiliate_link::find($id);
        if (empty($affiliate_link)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            $Result = $affiliate_link->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit affiliate_link delete function


    // start  premium fetch function

    public function premium()
    {
        $response = array();
        $premium_name = premium::all();
        $count = count($premium_name);
        if ($count != 0) {
            $response["premium_name"] = array();
            foreach ($premium_name as $row) {
                $premium["id"] = $row['id'];
                $premium["monthly_price"] = $row['monthly_price'];
                $premium["yearly_price"] = $row['yearly_price'];
                $premium["email"] = $row['email'];
                if ($row['plan'] == '1') {
                    $premium["plan"] = 'Monthly Plan';
                } else {
                    $premium["plan"] = 'Yearly Plan';
                }
                $premium["created_at"] = $row['created_at'];
                $premium["updated_at"] = $row['updated_at'];
                array_push($response["premium_name"], $premium);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  premium";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  premium fetch function

    // start add premium function            

    public function add_premium(Request $Request)
    {
        $response = array();
        $admin_name = premium::where('email', $Request->email)->get()->toarray();
        if (empty($admin_name)) {
            $id1 = premium_detail::all();
            // $premium = new premium;
            // $premium->email = $Request['email'];
            if ($Request['plan'] == 'month') {
                $plan = $id1[0]['id'];
            } else {
                $plan = $id1[1]['id'];
            }
            // $premium->plan = $plan;
            // $Result = $premium->save();


            $user = new admin;
            $user->role = 'premium';
            $user->f_name = "";
            $user->l_name = "";
            $user->plan = "premium";
            $user->email = $Request['email'];
            $user->password = '';
            $user->mobile = $plan;
            $user->experience = '';
            $user->image = '';
            $user->age = '';
            $user->gender = '';
            $user->type = '';
            $Result = $user->save();

            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Premium insert successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Premium insertion Faild";
                return json_encode($response);
            }
        } else {
            $response['success'] = 0;
            $response["message"] = "Email already exist";
            return json_encode($response);
        }
    }
    // end add premium function

    // start edit premium function

    public function edit_premium($id)
    {
        $response = array();
        $premium_name = premium::find($id);
        if ($premium_name) {
            $premium["id"] = $premium_name['id'];
            $premium["monthly_price"] = $premium_name['monthly_price'];
            $premium["yearly_price"] = $premium_name['yearly_price'];
            $premium["email"] = $premium_name['email'];
            $premium["plan"] = $premium_name['plan'];
            $premium["created_at"] = $premium_name['created_at'];
            $premium["updated_at"] = $premium_name['updated_at'];
            $response["premium"] = array();
            array_push($response["premium"], $premium);
            $response['success'] = 1;
            $response["message"] = "Singal premium fetch";
            return json_encode($response);
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end edit premium function  

    // start add premium update function            

    public function update_premium(Request $Request)
    {
        $response = array();
        // $premium = premium::find($id);
        $premium = premium::find($Request['id']);
        if ($premium) {
            $premium->monthly_price = $Request['monthly_price'];
            $premium->yearly_price = $Request['yearly_price'];
            $premium->email = $Request['email'];
            $premium->plan = $Request['plan'];
            $Result = $premium->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "premium Update successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Premium updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add premium update function          


    // start add premium update function            

    public function update_premium_detail(Request $Request)
    {
        $response = array();
        $id1 = premium_detail::all();
        // $premium = premium::find($id);
        $premium = premium_detail::find($id1[0]['id']);
        if ($premium) {
            $premium->price = $Request['monthly_price'];
            $premium->plan = $premium->plan;
            $Result = $premium->save();
            if ($Result) {
                $premium_detail = premium_detail::find($id1[1]['id']);
                $premium_detail->price = $Request['yearly_price'];
                $premium_detail->plan = $premium_detail->plan;
                $premium_detail->save();
                $response['success'] = 1;
                $response["message"] = "Premium detail Update successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Premium detail updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add premium update function          


    // start edit premium delete function           

    // start edit premium delete function    

    public function premium_delete($id)
    {
        $response = array();
        $premium = premium::find($id);
        if (empty($premium)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            $Result = $premium->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit premium delete function                            

    // start  academy fetch function

    public function academy()
    {
        $response = array();
        $academy_name = academy::orderBy('id', 'DESC')->get();
        $count = count($academy_name);
        if ($count != 0) {
            $response["academy_name"] = array();
            foreach ($academy_name as $row) {
                $academy["id"] = $row['id'];
                $academy["title"] = $row['title'];
                $academy["url"] = $row['url'];
                $academy["youtube"] = $row['youtube'];
                $academy["description"] = $row['description'];
                $academy["image"] = $row['image'];
                $academy["created_at"] = $row['created_at'];
                $academy["updated_at"] = $row['updated_at'];
                array_push($response["academy_name"], $academy);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  academy";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  academy fetch function

    // start add academy function            

    public function add_academy(Request $Request)
    {
        $response = array();
        $academy = new academy;
        $academy->title = $Request['title'];
        if (empty($Request['url'])) {
            $academy->url = "";
        } else {
            $academy->url = $Request['url'];
        }

        if (empty($Request['youtube'])) {
            $academy->youtube = "";
        } else {
            $academy->youtube = $Request['youtube'];
        }
        $academy->description = $Request['description'];
        if (!empty($Request['image'])) {
            $file = $Request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . "." . $extension;
            $file->move('uploads/images/', $filename);
            $academy->image = $filename;
        } else {
            $academy->image = " ";
        }
        $Result = $academy->save();
        if ($Result) {
            $response['success'] = 1;
            $response["message"] = "Academy insert successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Academy insertion Faild";
            return json_encode($response);
        }
    }
    // end add academy function

    // start edit academy function

    public function edit_academy($id)
    {
        $response = array();
        $academy_name = academy::find($id);
        if ($academy_name) {
            $academy["id"] = $academy_name['id'];
            $academy["title"] = $academy_name['title'];
            $academy["description"] = $academy_name['description'];
            $academy["image"] = $academy_name['image'];
            $academy["url"] = $row['url'];
            $academy["youtube"] = $row['youtube'];
            $academy["created_at"] = $academy_name['created_at'];
            $academy["updated_at"] = $academy_name['updated_at'];
            $response["academy"] = array();
            array_push($response["academy"], $academy);
            $response['success'] = 1;
            $response["message"] = "Singal academy fetch";
            return json_encode($response);
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end edit academy function  

    // start add academy update function            

    public function update_academy(Request $Request)
    {
        $response = array();
        // $academy = academy::find($id);
        $academy = academy::find($Request['id']);
        if ($academy) {
            $academy->title = $Request['title'];
            $academy->description = $Request['description'];
            if (empty($Request['url'])) {
                $academy->url = "";
            } else {
                $academy->url = $Request['url'];
            }

            if (empty($Request['youtube'])) {
                $academy->youtube = "";
            } else {
                $academy->youtube = $Request['youtube'];
            }
            if (!empty($Request['image'])) {
                $file = $Request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . "." . $extension;

                $discription = 'uploads/images/' . $academy->image;
                if (File::exists($discription)) {

                    File::delete($discription);
                }
                $file->move('uploads/images/', $filename);
                $academy->image = $filename;
            } else {
                $academy->image = $academy->image;
            }
            $Result = $academy->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Academy Update successfull";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Academy updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add academy update function          

    // start edit academy delete function    

    public function academy_delete($id)
    {
        $response = array();
        $academy = academy::find($id);
        if (empty($academy)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            $Result = $academy->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit academy delete function                            

    // start  analysis fetch function

    public function analysis()
    {
        $response = array();
        $analysis_name = analysis::all();
        $count = count($analysis_name);
        if ($count != 0) {
            $response["analysis_name"] = array();
            foreach ($analysis_name as $row) {
                $analysis["id"] = $row['id'];
                $analysis["title"] = $row['title'];
                $analysis["description"] = $row['description'];
                $analysis["image"] = $row['image'];
                $analysis["created_at"] = $row['created_at'];
                $analysis["updated_at"] = $row['updated_at'];
                array_push($response["analysis_name"], $analysis);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  analysis";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  analysis fetch function

    // start add analysis function            

    public function add_analysis(Request $Request)
    {
        $response = array();
        $analysis = new analysis;
        $analysis->title = $Request['title'];
        $analysis->description = $Request['description'];
        if (!empty($Request['image'])) {
            $file = $Request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . "." . $extension;
            $file->move('uploads/images/', $filename);
            $analysis->image = $filename;
        } else {
            $analysis->image = " ";
        }
        $Result = $analysis->save();
        if ($Result) {
            $response['success'] = 1;
            $response["message"] = "Analysis insert successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Analysis insertion Faild";
            return json_encode($response);
        }
    }
    // end add analysis function

    // start edit analysis function

    public function edit_analysis($id)
    {
        $response = array();
        $analysis_name = analysis::find($id);
        if ($analysis_name) {
            $analysis["id"] = $analysis_name['id'];
            $analysis["title"] = $analysis_name['title'];
            $analysis["description"] = $analysis_name['description'];
            $analysis["image"] = $analysis_name['email'];
            $analysis["created_at"] = $analysis_name['created_at'];
            $analysis["updated_at"] = $analysis_name['updated_at'];
            $response["analysis"] = array();
            array_push($response["analysis"], $analysis);
            $response['success'] = 1;
            $response["message"] = "Singal analysis fetch";
            return json_encode($response);
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end edit analysis function  

    // start add analysis update function            

    public function update_analysis(Request $Request)
    {
        $response = array();
        // $analysis = analysis::find($id);
        $analysis = analysis::find($Request['id']);
        if ($analysis) {
            $analysis->title = $Request['title'];
            $analysis->description = $Request['description'];
            if (!empty($Request['image'])) {
                $file = $Request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . "." . $extension;

                $discription = 'uploads/images/' . $analysis->image;
                if (File::exists($discription)) {

                    File::delete($discription);
                }
                $file->move('uploads/images/', $filename);
                $analysis->image = $filename;
            } else {
                $analysis->image = $analysis->image;
            }
            $Result = $analysis->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Analysis Update successfully";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Analysis updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add analysis update function          

    // start edit analysis delete function    

    public function analysis_delete($id)
    {
        $response = array();
        $analysis = analysis::find($id);
        if (empty($analysis)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            $Result = $analysis->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }
    // end edit analysis delete function  

    public function send_otp(Request $Request)
    {
        $response = array();
        $users = admin::where('email', $Request->email)->get()->toarray();
        $count = count($users);
        if ($count == '0') {
            $otp = $Request['otp'];
            $email = $Request['email'];
            $data = ['name' => "FirstBuck FX", 'data' => $otp];
            Mail::send('aprove_mails', $data, function ($message) use ($email) {
                $message->to($email);
                $message->subject('Your OTP is on its way');
                $message->from('app@firstbuckfx.com', 'FirstBuck FX');
            });
            $response['success'] = 1;
            $response["message"] = "Send OTP successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Email is already exist";
            return json_encode($response);
        }
    }

    public function forget_password(Request $Request)
    {
        $response = array();
        $users = admin::where('email', $Request->email)->get()->toarray();
        // $count = count($users);
        if (!empty($users)) {
            $password = $users[0]['password'];
            $email = $Request['email'];
            $data = ['name' => "FirstBuck FX", 'data' => $password];
            Mail::send('aprove', $data, function ($message) use ($email) {
                $message->to($email);
                $message->subject('Your password is on its way');
                $message->from('app@firstbuckfx.com', 'FirstBuck FX');
            });
            $response['success'] = 1;
            $response["message"] = "Send password successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Please insert right email";
            return json_encode($response);
        }
    }
    // start add_plan_request function            

    public function add_plan_request(Request $Request)
    {
        $response = array();
        $plan_request = new plan_request;
        $plan_request->user_id = $Request['user_id'];
        $plan_request->plan_id = $Request['plan_id'];
        $plan_request->transation_id = $Request['transation_id'];
        $plan_request->account_type = $Request['account_type'];
        $plan_request->status = 'pending';
        $Result = $plan_request->save();
        if ($Result) {
            $user_name = admin::where('email', $Request->email)
                ->get()->toarray();
            $id =  $user_name[0]['id'];
            $user = admin::find($id);
            $user->plan = "premium";
            $user->save();
            $response['success'] = 1;
            $response["message"] = "Your request send successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Your request Faild";
            return json_encode($response);
        }
    }
    // end add_plan_request function

    // start  get_package fetch function

    public function get_package()
    {
        $response = array();
        $premium_detail = premium_detail::all();
        $count = count($premium_detail);
        if ($count != 0) {
            $response["premium_detail"] = array();
            foreach ($premium_detail as $row) {
                $premium["id"] = $row['id'];
                $premium["plan"] = $row['plan'];
                $premium["price"] = $row['price'];
                $premium["created_at"] = $row['created_at'];
                $premium["updated_at"] = $row['updated_at'];
                array_push($response["premium_detail"], $premium);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  premium";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  get_package fetch function

    // start  chat fetch function

    public function chat()
    {
        $response = array();
        $chat_name = chat::all();
        $count = count($chat_name);
        if ($count != 0) {
            $response["chat_name"] = array();
            foreach ($chat_name as $row) {
                $chat["id"] = $row['id'];
                $chat["user_id"] = $row['user_id'];
                $chat["message"] = $row['message'];
                $chat["image"] = $row['image'];
                $chat["created_at"] = $row['created_at'];
                $chat["updated_at"] = $row['updated_at'];
                array_push($response["chat_name"], $chat);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  chat";


            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  chat fetch function        


    // start add_chat function            

    public function add_chat(Request $Request)
    {
        $response = array();
        $chat = new chat;
        $chat->user_id = $Request['user_id'];
        $chat->message = $Request['message'];
        if (!empty($Request['image'])) {
            $file = $Request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . "." . $extension;
            $file->move('uploads/images/', $filename);
            $chat->image = $filename;
        } else {
            $chat->image = " ";
        }
        $Result = $chat->save();
        if ($Result) {
            $response['success'] = 1;
            $response["message"] = "chat insert successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "chat insertion Faild";
            return json_encode($response);
        }
    }
    // end add_chat function        

    // start feedback function            

    public function feedback(Request $Request)
    {
        $response = array();
        $feedback = new feedback;
        $feedback->user_id = $Request['user_id'];
        $feedback->message = $Request['message'];
        $Result = $feedback->save();
        if ($Result) {
            $response['success'] = 1;
            $response["message"] = "feedback insert successfully";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "feedback insertion Faild";
            return json_encode($response);
        }
    }
    // end feedback function     

    // start affiliate_get_VPS function            

    public function affiliate_get_VPS()
    {
        $response = array();
        $affiliate_link = affiliate_link::all();
        $count = count($affiliate_link);
        if ($count != 0) {
            $response["get_VPS"] = array();
            foreach ($affiliate_link as $row) {
                $premium["VPS"] = $row['VPS'];
                array_push($response["get_VPS"], $premium);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  VPS";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end affiliate_get_VPS function

    // start affiliate_get_trade function            

    public function affiliate_get_trade()
    {
        $response = array();
        $affiliate_link = affiliate_link::all();
        $count = count($affiliate_link);
        if ($count != 0) {
            $response["get_trade"] = array();
            foreach ($affiliate_link as $row) {
                $premium["trade"] = $row['trade'];
                array_push($response["get_trade"], $premium);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  Trade";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end affiliate_get_trade function

    // start affiliate_get_PAMM function            

    public function affiliate_get_PAMM()
    {
        $response = array();
        $affiliate_link = affiliate_link::all();
        $count = count($affiliate_link);
        if ($count != 0) {
            $response["get_PAMM"] = array();
            foreach ($affiliate_link as $row) {
                $premium["PAMM"] = $row['PAMM'];
                array_push($response["get_PAMM"], $premium);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  PAMM";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end affiliate_get_PAMM function

    // start affiliate_get_IB_broker function            

    public function affiliate_get_IB_broker()
    {
        $response = array();
        $affiliate_link = affiliate_link::all();
        $count = count($affiliate_link);
        if ($count != 0) {
            $response["get_IB_broker"] = array();
            foreach ($affiliate_link as $row) {
                $premium["IB_broker"] = $row['IB_broker'];
                array_push($response["get_IB_broker"], $premium);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  IB_broker";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end affiliate_get_IB_broker function        

    // start all_user function            

    public function all_user()
    {
        // return "hello";
        $response = array();
        $user = admin::select('*')
            ->where('plan', '=', 'free')
            ->ORwhere('plan', '=', 'premium')
            ->get();
        $count = count($user);
        if ($count != 0) {
            $response["user"] = array();
            foreach ($user as $row) {
                $users["email"] = $row['email'];
                $users["id"] = $row["id"];
                array_push($response["user"], $users);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  All User";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end all_user function


    // start free_user function            

    public function free_user()
    {
        // return "hello";
        $response = array();
        $user = admin::select('*')
            ->where('plan', '=', 'free')
            ->get();
        $count = count($user);
        if ($count != 0) {
            $response["user"] = array();
            foreach ($user as $row) {
                $userS["email"] = $row['email'];
                $userS["id"] = $row["id"];
                array_push($response["user"], $userS);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  All User";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end free_user function

    // start premium_user function            

    public function premium_user()
    {
        // return "hello";
        $response = array();
        $user = admin::select('*')
            ->where('plan', '=', 'premium')
            ->get();
        $count = count($user);
        if ($count != 0) {
            $response["user"] = array();
            foreach ($user as $row) {
                $userS["email"] = $row['email'];
                $userS["id"] = $row["id"];
                array_push($response["user"], $userS);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch  All User";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end premium_user function

    // start  delete_premium delete function    

    public function delete_premium($id)
    {
        $response = array();
        $admin = admin::find($id);
        if (empty($admin)) {
            $response['success'] = 0;
            $response["message"] = "Data is not exist";
            return json_encode($response);
        } else {
            //  $admin->plan = "delete";
            $Result = $admin->delete();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Data successfully deleted";
                return json_encode($response);
            } else {

                $response['success'] = 0;
                $response["message"] = "Data cannot be deleted";
                return json_encode($response);
            }
        }
    }

    // public function delete_premium($id){
    //     $response = array();
    //     $admin = admin::find($id);  
    //     if (empty($admin)) {
    //         $response['success'] = 0;
    //         $response["message"] = "Data is not exist"; 
    //         return json_encode($response); 

    //     }else{
    //          $admin->plan = "delete";
    //          $Result = $admin->save();
    //         if ($Result) {
    //             $response['success'] = 1;
    //             $response["message"]= "Data successfully deleted"; 
    //             return json_encode($response); 

    //         }else{

    //             $response['success'] = 0;
    //             $response["message"]= "Data cannot be deleted"; 
    //             return json_encode($response); 
    //         }
    //     }   

    // }
    // end  delete_premium delete function        

    // start monthly_status function            

    public function monthly_status()
    {
        $response = array();
        $currentMonth = date('m');

        // $max = post_signal::max('pips');

        $max = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->max('pips');

        // $min = post_signal::max('worst_pips');

        $min = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->max('worst_pips');

        // $sum = post_signal::sum('pips');

        $sum = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->sum('pips');


        $buy = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('action', '=', 'Buy')
            ->get();

        $long = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('action', '=', 'Buy')
            ->where('pips', '>', '0')
            ->get();

        $profit = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('pips', '>', '0')
            ->get();

        $loss = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('worst_pips', '=', '30')
            ->get();


        $sell = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('action', '=', 'Sell')
            ->get();

        $short = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('action', '=', 'Sell')
            ->where('pips', '>', '0')
            ->get();

        // $post_signal =  post_signal::all();

        $post_signal = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->get();

        $GBPUSD = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('currency_pair', '=', 'GBPUSD')
            ->get();

        $EURUSD = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('currency_pair', '=', 'EURUSD')
            ->get();

        $AUDUSD = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('currency_pair', '=', 'AUDUSD')
            ->get();

        $CHFUSD = DB::table("post_signal")
            ->whereRaw('MONTH(created_at) = ?', [$currentMonth])
            ->where('currency_pair', '=', 'CHFUSD')
            ->get();

        $count_post_signal = count($post_signal);
        $count_GBPUSD = count($GBPUSD);
        $count_EURUSD = count($EURUSD);
        $count_AUDUSD = count($AUDUSD);
        $count_CHFUSD = count($CHFUSD);

        $GBPUSD1 = intval(($count_GBPUSD / $count_post_signal) * 100);
        $EURUSD1 = intval(($count_EURUSD / $count_post_signal) * 100);
        $AUDUSD1 = intval(($count_AUDUSD / $count_post_signal) * 100);
        $CHFUSD1 = intval(($count_CHFUSD / $count_post_signal) * 100);



        $count_loss = count($loss);
        $count_profit = count($profit);
        $count_sell = count($sell);
        $count_buy = count($buy);

        $count_long = count($long);
        $count_short = count($short);


        $response["Monthly"] = array();

        $premium["post_signal"] = $count_post_signal;
        $premium["buy"] = $count_buy;
        $premium["sell"] = $count_sell;
        $premium["pips"] = $sum;
        $premium["best_pips"] = $max;
        $premium["worst_pips"] = $min;
        $premium["loss_signal"] = $count_loss;
        $premium["profit_signal"] = $count_profit;
        $premium["long_won"] = $count_long;
        $premium["short_won"] = $count_short;
        $premium["GBPUSD"] = $GBPUSD1;
        $premium["EURUSD"] = $EURUSD1;
        $premium["AUDUSD"] = $AUDUSD1;
        $premium["CHFUSD"] = $CHFUSD1;
        array_push($response["Monthly"], $premium);

        $response['success'] = 1;
        $response["message"] = "Successfully fetch  monthly";
        return json_encode($response);
    }
    // end monthly_status function           


    // start add worst_pips update function            

    public function worst_pips(Request $Request)
    {
        $response = array();
        // $post_signal = post_signal::find($id);
        $post_signal = post_signal::find($Request['id']);
        if ($post_signal) {
            $post_signal->worst_pips = $Request['worst_pips'];
            $Result = $post_signal->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Post signal Update successfull";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Post signal updated Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add worst_pips update function             

    // start  filter function

    public function filter(Request $Request)
    {

        $yesterdayTimestamp = strtotime('yesterday');
        $yesterdayDate = date('Y-m-d', $yesterdayTimestamp);

        // Get the current timestamp
        $currentTimestamp = time();

        // Calculate the timestamp for the start and end of last week
        $startLastWeekTimestamp = strtotime('last week', $currentTimestamp);
        $endLastWeekTimestamp = strtotime('last week +6 days 23:59:59', $currentTimestamp);

        // Convert timestamps to formatted date strings
        $formattedStartLastWeek = date('Y-m-d H:i:s', $startLastWeekTimestamp);
        $formattedEndLastWeek = date('Y-m-d H:i:s', $endLastWeekTimestamp);

        $response = array();
        $response1["action"] = array();
        $response1["currency_pair"] = array();
        $response1["date"] = array();

        // Start action Filter

        if ($Request['Buy'] != '') {
            array_push($response1["action"], $Request['Buy']);
        }

        if ($Request['Sell'] != '') {
            array_push($response1["action"], $Request['Sell']);
        }

        if ($Request['All'] != '') {
            array_push($response1["action"], "Sell");
            array_push($response1["action"], "Buy");
        }

        // end action Filter

        // Start currency Filter


        if ($Request['EURUSD'] != '') {
            array_push($response1["currency_pair"], $Request['EURUSD']);
        }

        if ($Request['GBPUSD'] != '') {
            array_push($response1["currency_pair"], $Request['GBPUSD']);
        }

        if ($Request['USDJPY'] != '') {
            array_push($response1["currency_pair"], $Request['USDJPY']);
        }

        if ($Request['USDCAD'] != '') {
            array_push($response1["currency_pair"], $Request['USDCAD']);
        }

        if ($Request['USDCHF'] != '') {
            array_push($response1["currency_pair"], $Request['USDCHF']);
        }

        if ($Request['AUDUSD'] != '') {
            array_push($response1["currency_pair"], $Request['AUDUSD']);
        }

        if ($Request['NZDUSD'] != '') {
            array_push($response1["currency_pair"], $Request['NZDUSD']);
        }

        if ($Request['EURJPY'] != '') {
            array_push($response1["currency_pair"], $Request['EURJPY']);
        }

        if ($Request['GBPJPY'] != '') {
            array_push($response1["currency_pair"], $Request['GBPJPY']);
        }

        if ($Request['XAUUSD'] != '') {
            array_push($response1["currency_pair"], $Request['XAUUSD']);
        }

        if ($Request['CrudeOil'] != '') {
            array_push($response1["currency_pair"], $Request['CrudeOil']);
        }

        if ($Request['XAGUSD'] != '') {
            array_push($response1["currency_pair"], $Request['XAGUSD']);
        }

        if ($Request['BTCUSD'] != '') {
            array_push($response1["currency_pair"], $Request['BTCUSD']);
        }

        if ($Request['ETHUSD'] != '') {
            array_push($response1["currency_pair"], $Request['ETHUSD']);
        }

        if ($Request['BNBUSD'] != '') {
            array_push($response1["currency_pair"], $Request['BNBUSD']);
        }

        if ($Request['ADAUSD'] != '') {
            array_push($response1["currency_pair"], $Request['ADAUSD']);
        }

        if ($Request['XRPUSD'] != '') {
            array_push($response1["currency_pair"], $Request['XRPUSD']);
        }

        if ($Request['US30'] != '') {
            array_push($response1["currency_pair"], $Request['US30']);
        }

        if ($Request['SP500'] != '') {
            array_push($response1["currency_pair"], $Request['SP500']);
        }

        if ($Request['DXY'] != '') {
            array_push($response1["currency_pair"], $Request['DXY']);
        }

        // end currency Filter
        $targetDate = date("Y-m-d");

        $query = post_signal::select('*');

        if (!empty($response1['action'])) {
            $query->whereIn('action', $response1['action']);
        }

        if ($Request['Result'] == 'Favourite') {
            $query->where('fvrt', '1');
        }

        if ($Request['Result'] == 'Home') {
            $query->where('pips', '=', 0);
            $query->where('closed', '=', 'no');
        }

        if ($Request['Result'] == 'History') {
            $query->where('closed', '=', 'yes');
        }


        if (!empty($response1['currency_pair'])) {
            $query->whereIn('currency_pair', $response1['currency_pair']);
        }

        if ($Request['StartDate'] != '' && $Request['EndDate'] != '') {
            $EndDateTEST = $Request['EndDate'];
            $dateTimetest = DateTime::createFromFormat('d/m/Y', $EndDateTEST);
            $EndDate = $dateTimetest->format('Y-m-d');

            $StartDateTEST = $Request['StartDate'];
            $dateTimetest = DateTime::createFromFormat('d/m/Y', $StartDateTEST);
            $StartDate = $dateTimetest->format('Y-m-d');

            $query->where('created_at', '>=', $StartDate);
            $query->Where(function ($query1) use ($EndDate) {
                $query1->whereDate('created_at', '<=', $EndDate);
            });
        } elseif ($Request['StartDate'] != '' && $Request['EndDate'] == '') {
            // return "hello";
            $StartDateTEST = $Request['StartDate'];
            $dateTimetest = DateTime::createFromFormat('d/m/Y', $StartDateTEST);
            $StartDate = $dateTimetest->format('Y-m-d');

            $query->where('created_at', '>=', $StartDate);
        } elseif ($Request['StartDate'] == '' && $Request['EndDate'] != '') {
            $EndDateTEST = $Request['EndDate'];
            $dateTimetest = DateTime::createFromFormat('d/m/Y', $EndDateTEST);
            $EndDate = $dateTimetest->format('Y-m-d');

            $query->Where(function ($query1) use ($EndDate) {
                $query1->whereDate('created_at', '<=', $EndDate);
            });
        } elseif ($Request['Today'] != '' && $Request['LastWeek'] != '') {
            $query->where('created_at', '>=', $formattedStartLastWeek);
            $query->where('created_at', '<=', $formattedEndLastWeek);
        } elseif ($Request['Today'] != '' && $Request['Yesterday'] != '') {
            // $query->Where(function ($query1) use ($targetDate) {
            //                     $query1->whereDate('created_at', '=', date("Y-m-d"));
            //             });
            // $query->Where(function ($query1) use ($yesterdayDate) {
            //             $query1->whereDate('created_at', '=', $yesterdayDate);
            //         });
            $query->where('created_at', '>=', $yesterdayDate);
            $query->where('created_at', '<=', $targetDate);
        } elseif ($Request['LastWeek'] != '') {
            $query->where('created_at', '>=', $formattedStartLastWeek);
            $query->where('created_at', '<=', $formattedEndLastWeek);
        } elseif ($Request['Today'] != '') {
            $query->Where(function ($query1) use ($targetDate) {
                $query1->whereDate('created_at', '=', date("Y-m-d"));
            });
        } elseif ($Request['Yesterday'] != '') {
            $query->Where(function ($query1) use ($yesterdayDate) {
                $query1->whereDate('created_at', '=', $yesterdayDate);
            });
        }

        $post_name = $query->orderBy('id', 'DESC')->get();

        $count = count($post_name);
        if ($count != 0) {
            $response["post_name"] = array();
            foreach ($post_name as $row) {
                $post_signal["id"] = $row->id;
                $post_signal["currency_pair"] = $row->currency_pair;
                $post_signal["action"] = $row->action;
                $post_signal["stop_loss"] = $row->stop_loss;
                $post_signal["profit_one"] = $row->profit_one;
                $post_signal["profit_two"] = $row->profit_two;
                $post_signal["profit_three"] = $row->profit_three;
                $post_signal["RRR"] = $row->RRR;
                $post_signal["timeframe"] = $row->timeframe;
                $post_signal["open_price"] = $row->open_price;
                $post_signal["close_price"] = $row->close_price;
                $post_signal["pips"] = $row->pips;
                $post_signal["role"] = $row->role;
                $post_signal["type"] = $row->type;
                $post_signal["created_at"] = $row->created_at;
                $post_signal["updated_at"] = $row->updated_at;
                array_push($response["post_name"], $post_signal);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch user post signal";
            return json_encode($response);
        } else {
            $response["post_name"] = array();
            $response['success'] = 1;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end   filter function

    // start add closed_signal function            

    public function closed_signal(Request $Request)
    {
        $response = array();
        $post_signal = post_signal::find($Request['id']);
        if ($post_signal) {
            $post_signal->closed = "yes";
            $Result = $post_signal->save();
            if ($Result) {
                $response['success'] = 1;
                $response["message"] = "Post signal closed successfull";
                return json_encode($response);
            } else {
                $response['success'] = 0;
                $response["message"] = "Post signal closed  Faild";
                return json_encode($response);
            }
        } else {

            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }
    // end add closed_signal function     
    // start  premium_free fetch function

    public function premium_free(Request $Request)
    {
        $response = array();
        $user = admin::where('email', '=', $Request->email)->get();

        if ($user[0]['plan'] == "premium") {
            $post_name = post_signal::select('*')
                ->where('type', '=', 'premium')
                ->orwhere('type', '=', 'not_premium')
                ->where('pips', '=', 0)
                ->where('closed', '=', 'no')
                ->orderBy('id', 'DESC')
                ->get();
        } else {
            $post_name = post_signal::select('*')
                ->where('type', '=', 'not_premium')
                ->where('pips', '=', 0)
                ->where('closed', '=', 'no')
                ->orderBy('id', 'DESC')
                ->get();
        }
        $count = count($post_name);
        if ($count != 0) {
            $response["post_name"] = array();
            foreach ($post_name as $row) {
                $post_signal["id"] = $row['id'];
                $post_signal["currency_pair"] = $row['currency_pair'];
                $post_signal["action"] = $row['action'];
                $post_signal["stop_loss"] = $row['stop_loss'];
                $post_signal["profit_one"] = $row['profit_one'];
                $post_signal["profit_two"] = $row['profit_two'];
                $post_signal["profit_three"] = $row['profit_three'];
                $post_signal["RRR"] = $row['RRR'];
                $post_signal["type"] = $row['type'];
                $post_signal["pips"] = $row['pips'];
                $post_signal["close_price"] = $row['close_price'];
                $post_signal["open_price"] = $row['open_price'];
                $post_signal["timeframe"] = $row['timeframe'];
                $post_signal["created_at"] = $row['created_at'];
                $post_signal["updated_at"] = $row['updated_at'];
                array_push($response["post_name"], $post_signal);
            }
            $response['success'] = 1;
            $response["message"] = "Successfully fetch user post signal";
            return json_encode($response);
        } else {
            $response['success'] = 0;
            $response["message"] = "Data cannot be exist";
            return json_encode($response);
        }
    }

    // start  premium_free fetch function    
}
