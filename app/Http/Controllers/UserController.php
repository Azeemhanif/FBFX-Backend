<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\admin;
use App\Models\User;
use App\Mail\OTPMail;
use App\Mail\ResetPasswordMail;
use App\Models\ContactUs;
use App\Models\Device;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\PostSignal;
use Illuminate\Support\Facades\Hash;
use App\Validations\FBFXValidations;
use App\Traits\{ValidationTrait};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class UserController extends Controller
{

    public $successStatus = 200;

    use  ValidationTrait;
    public function signup(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateRegister($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            $user = $this->updateOrCreateUser($request, $input);

            if (isset($input['device_type']))  $this->createOrUpdateDevice($input, $user);
            $collection = new LoginResource($user);
            $collection->token = $user->createToken('API token of ' . $user->first_name)->plainTextToken;

            if (!isset($input['id'])) {
                $mailData = ['otp' => $user->otp];
                sendEmailToUser($request->email, new OTPMail($mailData));
            }

            return sendResponse(200, !$request->has('id') ? 'Registration Successfully' : 'Updated Successfully', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }


    private function updateOrCreateUser(Request $request, array $input)
    {
        $userId = optional($request->user())->id;

        $role = 'user';
        if (isset($input['role']) && $input['role']) {
            $role = $input['role'];
        }

        $userData = [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'otp' => rand(1000, 9999),
            'otp_expiry_time' => Carbon::now()->addMinutes(15),
            'password' => bcrypt($input['password']),
            'mobile' => $input['mobile'],
            'role' => $role,
        ];

        $user = User::updateOrCreate(['id' => $userId], $userData);
        return $user;
    }




    public function login(Request $request)
    {
        $validatorResult = $this->checkValidations(FBFXValidations::validateLogin($request));
        if ($validatorResult) {
            return $validatorResult;
        }

        $input = $request->all();
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();
        if (!$user)
            return sendResponse(422, 'The email does not exists', (object)[]);
        if ($user->password == null || $user->password == '')
            return sendResponse(202, 'Please enter valid credentials', (object)[]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if (isset($input['device_type'])) $this->createOrUpdateDevice($input, $user);
            $collection = new LoginResource($user);
            $user->tokens()->delete();
            $collection->token = $user->createToken('API token of ' . $user->first_name)->plainTextToken;
            return sendResponse(200, 'Login Successful!', $collection);
        } else {
            return sendResponse(202, 'Invalid email or password!', (object)[]);
        }
    }

    private function createOrUpdateDevice(array $input, $user)
    {
        $userCheck = Device::Where(['user_id' => $user->id])->orderBy('id', 'DESC')->first();
        if ($userCheck == null) {
            $userCheck = new Device();
            $userCheck->device_type = $input['device_type'];
            $userCheck->device_push_token = $input['device_push_token'];
            $userCheck->device_uuid = $input['device_uuid'];
            $userCheck->os_version = $input['os_version'];
            $userCheck->user_id = $user->id;
            $userCheck->save();
        }

        $device = Device::where(['user_id' => $user->id, 'device_uuid' => $input['device_uuid']])->first();
        if ($device) {
            $device_store = [
                'user_id' => $user->id,
                'device_uuid' => $input['device_uuid'],
                'device_push_token' => $input['device_push_token'],
                'device_type' => $input['device_type'],
                'os_version' => $input['os_version'],
            ];
            $store = Device::where('user_id', $user->id)->update($device_store);
        } else {
            $device = Device::where(['device_uuid' => $input['device_uuid']])->first();
            if ($device) {
                $oldDevice = Device::where(['user_id' => $user->id])->first();
                if ($oldDevice) {
                    Device::sendPushToLogout($oldDevice->user_id); // Push to logout user from other devices

                    $oldDevice->user_id = null;
                    $oldDevice->device_uuid = null;
                    $oldDevice->save();
                }
                $device->user_id = $user->id;
                $device->save();
            } else {
                $oldDevice = Device::where(['user_id' => $user->id])->first();
                if ($oldDevice) {
                    Device::sendPushToLogout($oldDevice->user_id); // Push to logout user from other devices

                    $oldDevice->user_id = null;
                    $oldDevice->device_uuid = null;
                    $oldDevice->save();
                }
                $device_store = [
                    'user_id' => $user->id,
                    'device_uuid' => $input['device_uuid'],
                    'device_push_token' => $input['device_push_token'],
                    'device_type' => $input['device_type'],
                    'os_version' => $input['os_version'],
                ];
                $store = Device::create($device_store);
            }
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            if ($request->user() != null) {
                $id = $request->user()->id;
                $request->merge(["id" => $id]);
            }

            $validatorResult = $this->checkValidations(FBFXValidations::validateOtp($request));
            if ($validatorResult) return $validatorResult;

            $input = $request->all();
            $user = User::where(['id' =>  $input['id']])->first();

            if (!$user) {
                return sendResponse(202, 'No user found with this id!', (object)[]);
            }

            if ($user->otp != $request->input('otp')) {
                return sendResponse(202, 'Please enter valid otp code!', (object)[]);
            }
            if (isset($user->otp_expiry_time) && \Carbon\Carbon::parse($user->otp_expiry_time)->isFuture()) {
                $user->is_otp_verified = true;
                $user->save();
                $collection = new UserResource($user);
                return sendResponse(200, 'OTP verified successfully!', $collection);
            } else {
                return sendResponse(202, 'OTP expired!', (object)[]);
            }
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(),  (object)[]);
            return $response;
        }
    }


    public function regenerateOtp(Request $request)
    {
        try {
            $id = $request->user()->id;

            // if ($id) {
            $user = User::where('id', $id)->first();

            if ($user) {
                if ($user->is_otp_verified == true) {
                    return sendResponse(202, "You have already verified an OTP.", (object)[]);
                }

                $newDateTime = Carbon::now()->addMinute(15);
                $otp = rand(1000, 9999);
                $user->otp = $otp;
                $user->otp_expiry_time = $newDateTime;
                $user->save();

                $collection = new UserResource($user);
                $mailData = ['otp' => $otp];
                sendEmailToUser($user->email, new OTPMail($mailData));
                return sendResponse(200, 'OTP sent successfully!', $collection);
            } else {
                return sendResponse(202, 'No user found with this id!', (object)[]);
            }
            // }
            // return sendResponse(202, 'Unauthenticated user!', (object)[]);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(),  (object)[]);
            return $response;
        }
    }
    public function socialSignup(Request $request)
    {
        try {

            $validatorResult = $this->checkValidations(FBFXValidations::validateSocialSignup($request));
            if ($validatorResult) {
                return $validatorResult;
            }
            $string = substr($request->token, 0, 1000);
            $userExistWithEmail = null;
            // if ($request->provider_type == 'google') {
            //     $user = User::where(['google_token' => $string])->onlyTrashed()->first();
            // }

            // if ($request->provider_type == 'facebook') {
            //     $user = User::where(['fb_token' => $string])->onlyTrashed()->first();
            // }

            // if ($request->provider_type == 'apple') {
            //     $user = User::where(['apple_token' => $string])->onlyTrashed()->first();
            // }
            // if ($user) {
            //     return sendResponse(401, 'There is already an account associated with this email please contact support to resolve this issue.', (object)[]);
            // }

            $input = $request->all();
            if ($request->provider_type == 'google') {
                $data = User::where(['google_token' => $string])->first();
            }

            if ($request->provider_type == 'facebook') {
                $data = User::where(['fb_token' => $string])->first();
            }

            if ($request->provider_type == 'apple') {
                $data = User::where(['apple_token' => $string])->first();
            }
            // if ($data) {
            //     return sendResponse(422, 'Record Exist In Our System', (object)[]);
            // } else {

            if ($input['provider_type'] != 'facebook' && $input['provider_type'] != 'google' && $input['provider_type'] != 'apple') {
                return sendResponse(422, 'Provider Type Does not Exist In Our System', (object)[]);
            }
            if (!empty($request->email)) {
                $data = User::where(['email' => $request->email])->first();
            }

            if (!$data) {
                $data = User::create($input);
            }
            if ($request->provider_type == 'google') {
                $data->google_token = $string;
            }

            if ($request->provider_type == 'facebook') {
                $data->fb_token = $string;
            }

            if ($request->provider_type == 'apple') {
                $data->apple_token = $string;
            }
            $data->social_token = $string;
            $data->role = 'user';
            $data->save();
            $data->loginFrom = $input['provider_type'];
            $collection = new LoginResource($data);
            $collection->token = $data->createToken('API token of ' . $data->first_name)->plainTextToken;
            return sendResponse(200, 'Registration Successful!', $collection);
            // }
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }

    public function socialLogin(Request $request)
    {

        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateSocialLogin($request));
            if ($validatorResult) {
                return $validatorResult;
            }

            $string = substr($request->token, 0, 1000);
            $userExistWithEmail = null;
            // $user = User::where(['social_token' => $string])->onlyTrashed()->first();
            // if ($user) {
            //     return sendResponse(401, 'There is already an account associated with this email please contact support to resolve this issue.', (object) []);
            // }

            $input = $request->all();
            if ($input['provider_type'] != 'facebook' && $input['provider_type'] != 'google' && $input['provider_type'] != 'apple') {
                return sendResponse(422, 'Provider Type Does not Exist In Our System', (object)[]);
            }
            if (!empty($request->email)) {
                $user = User::where(['email' => $request->email])->first();
            } else {
                if ($request->provider_type == 'google') {
                    $user = User::where(['google_token' => $string])->first();
                }

                if ($request->provider_type == 'facebook') {
                    $user = User::where(['fb_token' => $string])->first();
                }

                if ($request->provider_type == 'apple') {
                    $user = User::where(['apple_token' => $string])->first();
                }
            }
            if (!$user) {
                return $response = sendResponse(202, 'No user found with this email', (object)[]);
            }
            if ($request->provider_type == 'google') {
                $user->google_token = $string;
            }

            if ($request->provider_type == 'facebook') {
                $user->fb_token = $string;
            }

            if ($request->provider_type == 'apple') {
                $user->apple_token = $string;
            }
            $user->social_token = $string;
            $user->save();
            $user->loginFrom = $input['provider_type'];
            $collection = new UserResource($user);
            $collection->token = $user->createToken('API token of ' . $user->first_name)->plainTextToken;

            return sendResponse(200, 'Login Successful!', $collection);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }

    public function forget(Request $request)
    {
        // working
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::forgetValidate($request));
            if ($validatorResult) return $validatorResult;

            $input = $request->all();
            $user = User::where('email', $input['email'])->first();

            if ($user) {
                if ($user->password == null || $user->password == '')
                    return sendResponse(202, 'You are not allow to change password', (object)[]);
            }

            $token = Str::random(60);
            $mailData = [
                'token' => $token,
            ];
            $user['reset_password_link'] = $token;
            $user['is_verified'] = 0;
            $user->save();

            $collection = new LoginResource($user);
            sendEmailToUser($request->email, new ResetPasswordMail($mailData));
            return sendResponse(200, 'We have e-mailed link for reset password!',  $collection);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }

    public function forgotPasswordValidate($token)
    {
        //working
        try {
            $user = User::where('reset_password_link', $token)->where('is_verified', 0)->first();
            if ($user) {
                $email = $user->email;
                return view('email.change-password', compact('email', 'token'));
            }
            $tokenExpired = 'Your forgot password link has been expired. Please try again.';
            return view('email.change-password', compact('tokenExpired'));
            // return sendResponse(202, 'Password reset link is expired', (object)[]);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }


    public function updatePassword(Request $request)
    {
        //working
        try {
            $validate = \Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required|min:8',
                'confirm_password' => 'required|same:password'
            ], [
                'password.required' => "Password is required",
                'confirm_password.required' => "Confirm password is required",
                'confirm_password.same' => 'Passwords do not match.',
                'password.regex' => "Password must be contain at-least one alphabet or number."
            ]);

            if ($validate->fails()) {
                return back()->withErrors($validate->errors())->withInput();
            }
            $input = $request->all();
            $user = User::where('email', $request->email)->first();
            if ($user) {
                // if ($user->password == null || $user->password == '')
                // return back()->with('Error', 'You are not allow to change password.');
                $user['is_verified'] = 0;
                $user['reset_password_link'] = '';
                $user['password'] = bcrypt($request->password);
                $user->save();
                return back()->with('Success', 'Your password has been changed successfully.');
            }
            // return sendResponse(202, 'Failed! something went wrong', null);
            return back()->with('Error', 'User not exists.');

            // return back()->with('Success', 'Your password has been changed successfully.');
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }
    public function addAdmin(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::addAdminValidate($request));
            if ($validatorResult) return $validatorResult;

            $input = $request->all();

            $user =  User::where(['email' => $input['email']])->first();
            if (!$user)
                return sendResponse(202, 'User does not exists!', (object)[]);

            if ($user->role == 'admin')
                return sendResponse(202, 'This account is already an admin!', (object)[]);

            $user->role = 'admin';
            $user->save();
            $collection = new LoginResource($user);
            return sendResponse(200, 'Admin added successfully!',  $collection);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }

    public function listingAdmin(Request $request)
    {
        try {
            $user =  User::where(['role' => 'admin'])->where('id', '!=', Auth::user()->id)->orderBy('id', 'DESC')->get();
            $collection =  LoginResource::collection($user);
            return sendResponse(200, 'Listing fetched successfully!',  $collection);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }


    // not working
    // public function updateAdmin(Request $request)
    // {
    //     try {
    //         $validatorResult = $this->checkValidations(FBFXValidations::editAdminValidate($request));
    //         if ($validatorResult) return $validatorResult;

    //         $input = $request->all();
    //         $data = User::where('id', $input['id'])->where('role', 'admin')->first();
    //         if (!$data)
    //             return sendResponse(202, 'Admin does not exists!',  (object)[]);
    //         $data->email = $input['email'];
    //         $data->save();

    //         $collection = new LoginResource($data);
    //         return sendResponse(200, 'Admin updated successfully!',  $collection);
    //     } catch (\Exception $ex) {
    //         $response = sendResponse(500, $ex->getMessage(), (object)[]);
    //         return $response;
    //     }
    // }


    public function detailAdmin(Request $request, $id)
    {
        try {
            $data = User::where('id', $id)->where('role', 'admin')->first();
            if (!$data)
                return sendResponse(202, 'Admin does not exists!',  (object)[]);

            $collection = new LoginResource($data);
            return sendResponse(200, 'Data fecthed successfully!',  $collection);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }

    public function updateProfile(Request $request)
    {
        try {

            $validatorResult = $this->checkValidations(FBFXValidations::validateUpdateUser($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            // $id   =  Auth::user()->id;

            $user = User::where('id', $input['id'])->first();
            if (!$user)
                return sendResponse(202, 'User does not exists!',  (object)[]);

            if (isset($input['email']))   $user->email = $input['email'];
            $user->first_name = $input['first_name'];
            $user->last_name = $input['last_name'];
            $user->mobile = $input['mobile'];
            $user->experience = $input['experience'];
            $user->age = $input['age'];
            $user->gender = $input['gender'];
            $user->trader_type = $input['trader_type'];

            if ($request->hasFile('image')) {
                $folderPath = 'uploads/images/';
                $file = $request->file('image');
                $uploadImage = uploadImage($file, $folderPath);
                $user['image'] = $uploadImage;
            }
            $user->save();
            $collection = new UserResource($user);
            return  sendResponse(200, 'Data fetched successfully', $collection);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }




    public function feedback(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateFeedback($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            $id   =  Auth::user()->id;

            $feedback = new Feedback();
            $feedback->description = $input['description'];
            $feedback->user_id = $id;
            $feedback->save();

            return  sendResponse(200, 'Feedback created successfully', (object)[]);
        } catch (\Exception $ex) {
            // DB::rollback();
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }

    public function contactUs(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateContactUs($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();

            $contact = new ContactUs();
            $contact->first_name = $input['first_name'];
            $contact->last_name = $input['last_name'];
            $contact->email = $input['email'];
            $contact->phone = $input['phone'];
            $contact->message = $input['message'];
            $contact->save();
            return  sendResponse(200, 'Contact created successfully', (object)[]);
        } catch (\Exception $ex) {
            // DB::rollback();
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }




    public function riskCalculator(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateRiskCalculator($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            $total = 0;
            $account_balance = $input['account_balance'];
            $stop_loss = $input['stop_loss'];
            if ($stop_loss == 0) $stop_loss = 1;

            $risk_percentage = $input['risk_percentage'] / 100;
            $expectedLoss = $account_balance * (-$risk_percentage);
            $total = -$expectedLoss / $stop_loss / 10;
            $total = round($total, 2);
            return  sendResponse(200, 'Risk calculated successfully', $total);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }

    public function setting(Request $request)
    {
        try {
            $user = Auth::user();
            $data = true;
            $message = "Notfication turned on successfully!";

            if ($user->is_notification == true) {
                $data = false;
                $message = "Notfication turned off successfully!";
            }
            $user->is_notification = $data;
            $user->save();
            $response = new UserResource($user);
            return  sendResponse(200, $message, $response);
        } catch (\Exception $ex) {
            // DB::rollback();
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }


    public function notificationListing(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);

            $notification = Notification::query();

            $count = $notification->count();
            $data = $notification->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);
            $collection = NotificationResource::collection($data);
            $response = [
                'totalCount' => $count,
                'notifications' => $collection,
            ];
            return sendResponse(200, 'Data fetching successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }


    public function profileDetail()
    {
        try {
            $id = Auth::user()->id;
            $user = User::where('id', $id)->first();
            $collection =  new UserResource($user);
            return sendResponse(200, 'Data fetching successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    public function usersListing(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);

            $user = User::orderBy('id', 'DESC')->get();
            // $count = $user->count();
            // $data = $user->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);
            $collection = UserResource::collection($user);
            // $response = [
            //     'users' => $collection,
            // ];
            return sendResponse(200, 'Data fetching successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    public function tokenListing()
    {
        try {
            $device = Device::where('device_push_token', '!=',  null)->pluck('device_push_token');
            return sendResponse(200, 'Data fetching successfully!', $device);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    public function logout(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateLogout($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            $device = Device::Where(['user_id' => $input['user_id'], "device_uuid" => $input['device_uuid']])->orderBy('id', 'DESC')->first();
            if ($device) {
                $device->device_uuid = NULL;
                $device->device_push_token = NULL;
                $device->save();
            }
            return sendResponse(200, 'Logout Sucessfully!', (object)[]);
            // return sendResponse(422, 'No device exists against this user!', $device);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }

    public function testCronJob()
    {
        $signals = PostSignal::where('closed', 'no')->get();

        foreach ($signals as $signal) {
            $this->processSignal($signal);
        }
    }
    private function processSignal($signal)
    {
        $tp1 = $signal->profit_one;
        $tp2 = $signal->profit_two;
        $tp3 = $signal->profit_three;
        $stop_loss = $signal->stop_loss;
        $currency_pair = $signal->currency_pair;
        // Make API request
        $response = Http::get('https://api.twelvedata.com/time_series', [
            'symbol' => 'EUR/USD,GBP/USD,USD/JPY,USD/CAD,USD/CHF,AUD/USD',
            'interval' => '1min',
            'apikey' => 'acab338c6b924d6ebfa6183fc4a2491e',
        ]);

        // Check if the request was successful
        if ($response->successful()) {
            $data = $response->json();

            if (isset($data[$currency_pair])) {
                $currencyData = $data[$currency_pair];

                $closePrice = $currencyData['values'][0]['close'];

                $this->updateSignalStatus($signal, $closePrice, $tp1, $tp2, $tp3, $stop_loss);

                $this->logApiResponse($currency_pair, $currencyData);
                $this->closeSignalIfTime($signal, $closePrice);
            }
        } else {

            $this->logApiError($response);
        }
    }

    private function updateSignalStatus($signal, $closePrice, $tp1, $tp2, $tp3, $stop_loss)
    {
        foreach (['tp1', 'tp2', 'tp3'] as $target) {
            $statusField = "{$target}_status";
            if ($$target <= $closePrice && !$signal->$statusField) {
                $signal->$statusField = true;
            }
        }

        if ($stop_loss <= $closePrice && $signal->stop_loss_status === null) {
            $signal->stop_loss_status = $closePrice;
        }

        if ($signal->tp1_status == true && $signal->tp2_status  == true && $signal->tp3_status  == true) {
            $signal->close_price_status = $closePrice;
            $signal->closed = 'yes';

            $runningPips = ($signal->action === 'buy' || $signal->action === 'Buy')
                ? ($signal->close_price_status - $signal->openPrice) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0)
                : ($signal->openPrice - $signal->close_price_status) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);

            $runningPips = round($runningPips, 2);
            $signal->pips = $runningPips;
        }

        $signal->save();
    }

    private function logApiResponse($currencyPair, $data)
    {
        \Log::info("API response for {$currencyPair}: " . json_encode($data));
    }

    private function logApiError($response)
    {
        \Log::error('API request failed. Status code: ' . $response->status());
    }

    private function closeSignalIfTime($signal, $closePrice)
    {
        $currentTime = now()->format('H:i');
        if ($currentTime >= '15:00') {
            $signal->closed = 'yes';
            $signal->close_price_status = $closePrice;
            $signal->stop_loss_status = $closePrice;

            $runningPips = ($signal->action === 'buy' || $signal->action === 'Buy')
                ? ($signal->close_price_status - $signal->openPrice) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0)
                : ($signal->openPrice - $signal->close_price_status) * ($signal->currency_pair === 'EUR/USD' ? 10000 : 0);

            $runningPips = round($runningPips, 2);
            $signal->pips = $runningPips;

            $signal->save();
        }
    }
    public function destroy($id)
    {
        try {
            $user = User::where('id', '=', $id)->first();
            if (!$user) return  sendResponse(202, 'User does not exists', (object)[]);
            $user->role = 'user';
            $user->save();
            $data = new UserResource($user);

            return  sendResponse(200, 'Deleted successfully', $data);
        } catch (\Exception $ex) {
            // DB::rollback();
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }
    public function deleteAccount()
    {
        try {
            $id = Auth::user()->id;
            User::where('id', '=', $id)->delete();
            return  sendResponse(200, 'Account deleted successfully', (object)[]);
        } catch (\Exception $ex) {
            // DB::rollback();
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }


    public function scrapeTable(Request $request)
    {
        $allowedClasses = ['EUR-USD', 'GBP-USD', 'USD-JPY', 'USD-CAD', 'USD-CHF', 'AUD-USD', 'NZD-USD', 'EUR-JPY', 'GBP-JPY', 'XAU-USD', 'XAG-USD', 'BTC-USD', 'ETH-USD', 'BNB-USD', 'ADA-USD', 'XRP-USD', 'US-30', 'SP-500', 'DXY'];

        $result = $this->scrapeData('https://fxpricing.com/help/get_currencty_list_ajax/forex', $allowedClasses);
        $result2 = $this->scrapeData('https://fxpricing.com/help/get_currencty_list_ajax/crypto', $allowedClasses);
        // Merge the two sets of data into a single array
        $mergedResult = array_merge($result, $result2);

        $signals = PostSignal::where('closed', 'no')->get();

        foreach ($signals as $signal) {
            $tp1 = $signal->profit_one;
            $tp2 = $signal->profit_two;
            $tp3 = $signal->profit_three;
            $stop_loss = $signal->stop_loss;
            $currency_pair = $signal->currency_pair;

            if (isset($mergedResult[$currency_pair])) {
                $currencyData = $mergedResult[$currency_pair];
                $closePrice = $currencyData['price'] ?? 0;
                $this->updateSignalStatus($signal, $closePrice, $tp1, $tp2, $tp3, $stop_loss);
                $this->logApiResponse($currency_pair, $currencyData);
                $this->closeSignalIfTime($signal, $closePrice);
            }
        }

        // $mergedResult now contains an associative array with all_class as keys and price, bid, ask as values
        dd($mergedResult);
    }

    private function scrapeData($url, $allowedClasses)
    {
        $formData = [
            'draw' => '1',
            'start' => '0',
            'length' => '7250'
        ];

        $client = new Client();

        $response = $client->post($url, [
            'form_params' => $formData,
        ]);

        $data = json_decode($response->getBody(), true);
        $result = [];

        foreach ($data['aaData'] as $item) {
            $allClass = $item['all_class'];

            // Check if $allClass exists in the allowed classes
            if (!in_array($allClass, $allowedClasses)) {
                // If $allClass is not in the allowed classes, skip to the next iteration
                continue;
            }

            $key = str_replace('-', '/', $allClass);

            // Extracting the relevant values (price, bid, ask) based on the all_class value
            $price = $item['price'];

            // Create an associative array with all_class as the key and the extracted values as the value
            $result[$key] = [
                'price' => $price,
            ];
        }

        return $result;
    }
}
