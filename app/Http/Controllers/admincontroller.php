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
use App\Models\premium_detail;
use App\Models\plan_request;
use App\Models\admin;
use Illuminate\Support\Facades\File;


class admincontroller extends Controller
{
    //create function for fetch all post_signal and display     
    public function post_signal(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $post_signal = post_signal::all(); // all() function fetch all data from database 
          $data = compact('post_signal');  // compect send data to view
          return view('post_signal')->with($data); 
        }          
    }  
    //close function for fetch all post_signal and display     

    //create function for display add post_signal page     
    public function add_post_signal(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{ 
          return view('add_post_signal');
        }             
    }    
    //close function for display add post_signal page     

    //create function for insert post_signal in database    
    public function insert_post_signal(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $post_signal = new post_signal;
          $post_signal->currency_pair = $Request['currency_pair'];
          $post_signal->action = $Request['action'];
          $post_signal->stop_loss = $Request['stop_loss'];
          $post_signal->profit_one = $Request['profit_one'];
          $post_signal->profit_two = $Request['profit_two'];
          $post_signal->profit_three = $Request['profit_three'];
          $post_signal->RRR = $Request['RRR'];
          $post_signal->timeframe = $Request['timeframe'];
          $post_signal->user_id = session()->get('id');
          $post_signal->fvrt = '0';
          $post_signal->pips = $Request['pips'];
          $post_signal->save();
          return redirect('/post_signal');
        }  
    }
    //close function for insert post_signal in database    
    
    //create function for display edite post_signal page with id    
    public function edite_post_signal($id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $post_signal = post_signal::find($id); // find() function use to find id releted data from database
          $data = compact('post_signal'); 
          return view('edite_post_signal')->with($data);
        }    
    }
    //create function for display edite post_signal page with id    

    //create function for update post_signal with id    
    public function update_post_signal(Request $Request, $id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $post_signal = post_signal::find($id);
          $post_signal->currency_pair = $Request['currency_pair'];
          $post_signal->action = $Request['action'];
          $post_signal->stop_loss = $Request['stop_loss'];
          $post_signal->profit_one = $Request['profit_one'];
          $post_signal->profit_two = $Request['profit_two'];
          $post_signal->profit_three = $Request['profit_three'];
          $post_signal->RRR = $Request['RRR'];
          $post_signal->timeframe = $Request['timeframe'];
          $post_signal->pips = $Request['pips'];
          $post_signal->save();
          return redirect('/post_signal');
        }  
    }
    //close function for update post_signal with id    

    //create function for delete post_signal     
    public function delete_post_signal(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $post_signal = post_signal::find($Request['id']);
          $post_signal->delete();
          return redirect('/post_signal');
        }  
    }
    //close function for delete post_signal   



    //create function for fetch all announcement and display     
    public function announcement(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $announcement = announcement::all(); // all() function fetch all data from database 
          $data = compact('announcement');  // compect send data to view
          return view('announcement')->with($data); 
        }          
    }  
    //close function for fetch all announcement and display     

    //create function for display add announcement page     
    public function add_announcement(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{ 
          return view('add_announcement');
        }             
    }    
    //close function for display add announcement page     

    //create function for insert announcement in database    
    public function insert_announcement(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $announcement = new announcement;
          $announcement->description = $Request['description'];
          $announcement->save();
          return redirect('/announcement');
        }  
    }
    //close function for insert announcement in database    
    
    //create function for display edite announcement page with id    
    public function edite_announcement($id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $announcement = announcement::find($id); // find() function use to find id releted data from database
          $data = compact('announcement'); 
          return view('edite_announcement')->with($data);
        }    
    }
    //create function for display edite announcement page with id    

    //create function for update announcement with id    
    public function update_announcement(Request $Request, $id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $announcement = announcement::find($id);
          $announcement->description = $Request['description'];
          $announcement->save();
          return redirect('/announcement');
        }  
    }
    //close function for update announcement with id    

    //create function for delete announcement     
    public function delete_announcement(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $announcement = announcement::find($Request['id']);
          $announcement->delete();
          return redirect('/announcement');
        }  
    }
    //close function for delete announcement

    //create function for fetch all notification and display     
    public function notification(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $notification = notification::all(); // all() function fetch all data from database 
          $data = compact('notification');  // compect send data to view
          return view('notification')->with($data); 
        }          
    }  
    //close function for fetch all notification and display     

    //create function for display add notification page     
    public function add_notification(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{ 
          return view('add_notification');
        }             
    }    
    //close function for display add notification page     

   //create function for insert notification in database    
    public function insert_notification(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $notification = new notification;
          $notification->description = $Request['description'];
          $notification->save();
          if ($notification) {
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
                curl_setopt($ch,
                    CURLOPT_SSL_VERIFYPEER,
                    FALSE
                );
                // Execute call and save result
                $result = curl_exec($ch);
                // print($result);
                // Close curl after call
                curl_close($ch);
                if($result){
                  return redirect('/notification');
                }
                // return redirect()->back();
          }
        }  
    }
    //close function for insert notification in database   
    
    //create function for display edite notification page with id    
    public function edite_notification($id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $notification = notification::find($id); // find() function use to find id releted data from database
          $data = compact('notification'); 
          return view('edite_notification')->with($data);
        }    
    }
    //create function for display edite notification page with id    

    //create function for update notification with id    
    public function update_notification(Request $Request, $id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $notification = notification::find($id);
          $notification->description = $Request['description'];
          $notification->save();
          return redirect('/notification');
        }  
    }
    //close function for update notification with id    

    //create function for delete notification     
    public function delete_notification(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $notification = notification::find($Request['id']);
          $notification->delete();
          return redirect('/notification');
        }  
    }
    //close function for delete notification  


    //create function for fetch all affiliate_link and display     
    public function affiliate_link(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $affiliate_link = affiliate_link::all(); // all() function fetch all data from database 
          $data = compact('affiliate_link');  // compect send data to view
          return view('affiliate_link')->with($data); 
        }          
    }  
    //close function for fetch all affiliate_link and display     

    //create function for display add affiliate_link page     
    public function add_affiliate_link(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{ 
          return view('add_affiliate_link');
        }             
    }    
    //close function for display add affiliate_link page     

    //create function for insert affiliate_link in database    
    public function insert_affiliate_link(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $affiliate_link = new affiliate_link;
          $affiliate_link->VPS = $Request['VPS'];
          $affiliate_link->trade = $Request['trade'];
          $affiliate_link->PAMM = $Request['PAMM'];
          $affiliate_link->IB_broker = $Request['IB_broker'];
          $affiliate_link->save();
          return redirect('/affiliate_link');
        }  
    }
    //close function for insert affiliate_link in database    
    
    //create function for display edite affiliate_link page with id    
    public function edite_affiliate_link($id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $affiliate_link = affiliate_link::find($id); // find() function use to find id releted data from database
          $data = compact('affiliate_link'); 
          return view('edite_affiliate_link')->with($data);
        }    
    }
    //create function for display edite affiliate_link page with id    

    //create function for update affiliate_link with id    
    public function update_affiliate_link(Request $Request, $id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $affiliate_link = affiliate_link::find($id);
          $affiliate_link->VPS = $Request['VPS'];
          $affiliate_link->trade = $Request['trade'];
          $affiliate_link->PAMM = $Request['PAMM'];
          $affiliate_link->IB_broker = $Request['IB_broker'];
          $affiliate_link->save();
          return redirect('/affiliate_link');
        }  
    }
    //close function for update affiliate_link with id    

    //create function for delete affiliate_link     
    public function delete_affiliate_link(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $affiliate_link = affiliate_link::find($Request['id']);
          $affiliate_link->delete();
          return redirect('/affiliate_link');
        }  
    }
    //close function for delete affiliate_link


    //create function for fetch all academy and display     
    public function academy(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $academy = academy::all(); // all() function fetch all data from database 
          $data = compact('academy');  // compect send data to view
          return view('academy')->with($data); 
        }          
    }  
    //close function for fetch all academy and display     

    //create function for display add academy page     
    public function add_academy(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{ 
           return view('add_academy');
        }             
    }    
    //close function for display add academy page     

    //create function for insert academy in database    
    public function insert_academy(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $academy = new academy; // new function use for create new row in database
          $academy->title = $Request['title'];
          $academy->description = $Request['description'];
          $file = $Request->file('image');
          $extension = $file->getClientOriginalExtension();
          $filename = time(). "." . $extension;
          $file->move('uploads/images/',$filename);
          $academy->image = $filename;
          $academy->save();
          return redirect('/academy');  
        }  
    }
    //close function for insert academy in database    
    
    //create function for display edite academy page with id    
    public function edite_academy($id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $academy = academy::find($id); // find() function use to find id releted data from database
          $data = compact('academy'); 
          return view('edite_academy')->with($data);
        }    
    }
    //create function for display edite academy page with id    

    //create function for update academy with id    
    public function update_academy(Request $Request, $id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $academy = academy::find($id);
          $academy->title = $Request['title'];
          $academy->description = $Request['description'];
          if (!empty($Request['image'])) {
              $file = $Request->file('image');
              $extension = $file->getClientOriginalExtension();
              $filename = time(). "." . $extension;
              
              $discription = 'uploads/images/'.$academy->image;
              if (File::exists($discription)) {
                  
                  File::delete($discription);
              }
              $file->move('uploads/images/',$filename);
              $academy->image = $filename;

          }else{
              $academy->image = $academy->image;
          }
          $academy->save();
          return redirect('/academy');
        }  
    }
    //close function for update academy with id    

    //create function for delete academy     
    public function delete_academy(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $academy = academy::find($Request['id']);
          $discription = 'uploads/images/'.$academy->image;
          if (File::exists($discription)) {
              
              File::delete($discription);
          }
          $academy->delete();
          return redirect('/academy');
        }  
    }
    //close function for delete academy    


    //create function for fetch all analysis and display     
    public function analysis(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $analysis = analysis::all(); // all() function fetch all data from database 
          $data = compact('analysis');  // compect send data to view
          return view('analysis')->with($data); 
        }          
    }  
    //close function for fetch all analysis and display     

    //create function for display add analysis page     
    public function add_analysis(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{ 
           return view('add_analysis');
        }             
    }    
    //close function for display add analysis page     

    //create function for insert analysis in database    
    public function insert_analysis(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $analysis = new analysis; // new function use for create new row in database
          $analysis->title = $Request['title'];
          $analysis->description = $Request['description'];
          $file = $Request->file('image');
          $extension = $file->getClientOriginalExtension();
          $filename = time(). "." . $extension;
          $file->move('uploads/images/',$filename);
          $analysis->image = $filename;
          $analysis->save();
          return redirect('/analysis');  
        }  
    }
    //close function for insert analysis in database    
    
    //create function for display edite analysis page with id    
    public function edite_analysis($id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $analysis = analysis::find($id); // find() function use to find id releted data from database
          $data = compact('analysis'); 
          return view('edite_analysis')->with($data);
        }    
    }
    //create function for display edite analysis page with id    

    //create function for update analysis with id    
    public function update_analysis(Request $Request, $id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $analysis = analysis::find($id);
          $analysis->title = $Request['title'];
          $analysis->description = $Request['description'];
          if (!empty($Request['image'])) {
              $file = $Request->file('image');
              $extension = $file->getClientOriginalExtension();
              $filename = time(). "." . $extension;
              
              $discription = 'uploads/images/'.$analysis->image;
              if (File::exists($discription)) {
                  
                  File::delete($discription);
              }
              $file->move('uploads/images/',$filename);
              $analysis->image = $filename;

          }else{
              $analysis->image = $analysis->image;
          }
          $analysis->save();
          return redirect('/analysis');
        }  
    }
    //close function for update analysis with id    

    //create function for delete analysis     
    public function delete_analysis(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $analysis = analysis::find($Request['id']);
          $discription = 'uploads/images/'.$analysis->image;
          if (File::exists($discription)) {
              
              File::delete($discription);
          }
          $analysis->delete();
          return redirect('/analysis');
        }  
    }
    //close function for delete analysis    
  
    //create function for fetch all premium and display     
    public function premium(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $premium = premium::all(); // all() function fetch all data from database 
          $data = compact('premium');  // compect send data to view
          return view('premium')->with($data); 
        }          
    }  
    //close function for fetch all premium and display     

    //create function for display add premium page     
    public function add_premium(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{ 
          return view('add_premium');
        }             
    }    
    //close function for display add premium page     

    //create function for insert premium in database    
    public function insert_premium(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
            $admin_name = premium::where('email',$Request->email)->get()->toarray();  
            if (empty($admin_name)) {
              $id1 = premium_detail::all();
              $premium = new premium;
              $premium->email = $Request['email'];
              if ($Request['plan'] == 'month') {
                  $plan = $id1[0]['id'];
              }else{
                  $plan = $id1[1]['id'];
              }
              $premium->plan = $plan;
              $Result = $premium->save();
              return redirect('/premium');
            }else{
            return redirect()->back()->with('message', 'Email already exist!');
            }   
        }  
    }
    //close function for insert premium in database    
    
    //create function for display edite premium page with id    
    public function edite_premium($id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $premium = premium::find($id); // find() function use to find id releted data from database
          $data = compact('premium'); 
          return view('edite_premium')->with($data);
        }    
    }
    //create function for display edite premium page with id    

    //create function for update premium with id    
    public function update_premium(Request $Request, $id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $premium = premium::find($id);
          $premium->monthly_price = $Request['monthly_price'];
          $premium->yearly_price = $Request['yearly_price'];
          $premium->email = $Request['email'];
          $premium->plan = $Request['plan'];
          $premium->save();
          return redirect('/premium');
        }  
    }
    //close function for update premium with id    

    //create function for delete premium     
    public function delete_premium(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $premium = premium::find($Request['id']);
          $premium->delete();
          return redirect('/premium');
        }  
    }
    //close function for delete premium   

    //create function for fetch all admin and display     
    public function admin(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $admin = admin::all()->where('role','=','1')->where('type','!=','admin'); // all() function fetch all data from database 
          $data = compact('admin');  // compect send data to view
          return view('admin')->with($data); 
        }          
    }  
    //close function for fetch all admin and display     

    //create function for display add admin page     
    public function add_admin(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{ 
          return view('add_admin');
        }             
    }    
    //close function for display add admin page     

    //create function for insert admin in database    
    public function insert_admin(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $admin_name = admin::where('email',$Request->email)->where('role','1')->get()->toarray();  
          if (empty($admin_name)) {  
            $admin = new admin;
            $admin->email = $Request['email'];
            $admin->f_name = $Request['name'];;
            $admin->l_name = "";
            $admin->mobile = "";
            $admin->gender = "";
            $admin->age = "";
            $admin->image = "";
            $admin->type = "";
            $admin->experience = "";
            $admin->role = "1";
            $admin->password = $Request['password'];
            $admin->save();
            return redirect('/admin');
          }else{
            return redirect()->back()->with('message', 'Admin is already exist!');
          }  
        }  
    }
    //close function for insert admin in database    
    
    //create function for display edite admin page with id    
    public function edite_admin($id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $admin = admin::find($id); // find() function use to find id releted data from database
          $data = compact('admin'); 
          return view('edite_admin')->with($data);
        }    
    }
    //create function for display edite admin page with id    

    //create function for update admin with id    
    public function update_admin(Request $Request, $id){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $admin_id = admin::find($id);
          if ($admin_id->email == $Request['email'] && $admin_id->role == '1') {
            $admin = admin::find($id);
            $admin->email = $Request['email'];
            $admin->f_name = $Request['name'];
            $admin->l_name = $admin->l_name;
            $admin->mobile = $admin->mobile;
            $admin->gender = $admin->gender;
            $admin->age = $admin->age;
            $admin->type = $admin->type;
            $admin->experience = $admin->experience;
            $admin->role = $admin->role;
            $admin->password = $admin->password;
            $admin->save();
            return redirect('/admin');
          }else{
            $admin_name = admin::where('email',$Request->email)->where('role','1')->get()->toarray();  
            if (empty($admin_name)) {
                $admin = admin::find($id);
                $admin->email = $Request['email'];
              $admin->f_name = $Request['name'];
              $admin->l_name = $admin->l_name;
              $admin->mobile = $admin->mobile;
              $admin->gender = $admin->gender;
              $admin->age = $admin->age;
              $admin->type = $admin->type;
              $admin->experience = $admin->experience;
              $admin->role = $admin->role;
              $admin->password = $admin->password;
                $admin->save();
                return redirect('/admin');
            }else{
                return redirect()->back()->with('message', 'Admin is already exist!');
            }    
          }  
        }  
    }
    //close function for update admin with id    

    //create function for delete admin     
    public function delete_admin(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $admin = admin::find($Request['id']);
          $post_signal = post_signal::select('*')
                ->where('user_id','=',$Request['id'])
                ->delete();
          $admin->delete();
          return redirect('/admin');
        }  
    }
    //close function for delete admin
    
    //create function for plan_request and display     
    public function plan_request(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
          $plan_request = plan_request::all(); // all() function fetch all data from database 
          $data = compact('plan_request');  // compect send data to view
          return view('plan_request')->with($data); 
        }          
    }  
    //close function for plan_request and display     


    //create function for plan_request_status with id    
    public function plan_request_status(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{
            $plan_request = plan_request::find($Request['id']);
            $plan_request->status = $Request['status'];
            $plan_request->save();
            return redirect()->back();
        }  
    }
    //close function for plan_request_status with id     

    //create function for delete plan_request     
    public function delete_plan_request(Request $Request){
        if (!session()->get('name')) {
          return redirect('/login');
        }else{  
          $plan_request = plan_request::find($Request['id']);
          $plan_request->delete();
          return redirect('/plan_request');
        }  
    }
    //close function for delete plan_request
     
}
