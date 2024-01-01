<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Storage;

class Device extends Model
{
    use HasFactory;
    protected $guarded = [];

    public static function sendPush($data, $send_to, $type, $category = null)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(36000);
        if (empty($send_to)) {
            $android_devices = Device::where('device_type', 'android')->where('device_push_token', '!=', null)->where('user_id', '!=', Auth::user()->id)->get()->pluck('device_push_token')->toArray();
            $ios_devices = Device::where('device_type', 'ios')->where('device_push_token', '!=', null)->where('user_id', '!=', Auth::user()->id)->get()->pluck('device_push_token')->toArray();
        } else {
            $android_devices = Device::where('device_type', 'android')->where('device_push_token', '!=', null)->whereIn('user_id', $send_to)->where('user_id', '!=', Auth::user()->id)->get()->pluck('device_push_token')->toArray();
            $ios_devices = Device::where('device_type', 'ios')->where('device_push_token', '!=', null)->whereIn('user_id', $send_to)->get()->where('user_id', '!=', Auth::user()->id)->pluck('device_push_token')->toArray();
        }

        if (!empty($ios_devices)) {

            $data["title"] = "FirstBuckFx";
            $data["type"] = 'Generic';
            $data['body'] =  $data["message"];
            foreach (array_chunk($ios_devices, 400) as $key => $ios_devices_chunk) {
                $ios_data = [
                    "registration_ids" => array_values($ios_devices_chunk),
                    "notification" => [
                        "body" => $data["body"],
                        "title" => $data["title"],
                        "icon" => "",
                        "sound" => "default",
                    ],
                    "data" => $data,
                    'priority' => 'high',
                    'content_available' => true
                ];


                if (!empty($ios_devices)) {
                    return  Device::post($ios_data);
                }
            }
        }

        if (!empty($android_devices)) {

            $data["type"] = 'Generic';
            $data["title"] = "FirstBuckFx";
            $data['body'] =  $data["message"];
            foreach (array_chunk($android_devices, 400) as $key => $android_devices_chunk) {
                $android_data = [
                    "registration_ids" => array_values($android_devices_chunk),
                    "notification" => [
                        "body" => $data["body"],
                        "title" => $data["title"],
                        //                        "notification_type" =>  $data["type"],
                        "icon" => "",
                        "sound" => "default",
                    ],
                    "data" => $data,
                    'priority' => 'high',
                    'content_available' => true
                ];
                if (!empty($android_devices)) {
                    return Device::post($android_data);
                }
            }
        }
    }

    private static function post($json_data)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(36000);
        self::testjobAction("Job Started!");
        self::testjobAction($json_data['data']['body']);
        $data = json_encode($json_data);

        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key

        $server_key = "AAAAk8zvdlA:APA91bGFPpAyobG7jgy5-9elTQEWzRQbXctHnMUvg5J1PDJK-QSldunPMQSdua9ut7ili7lae_bL5ILCBvhQQ5ZBfZk6HosMuedBKHvOYM_VAru48dPLMvskZaxz-G9_Z0yBtc2U5dus";

        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );
        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        // Log fcm response stats


        if ($result) {
            $r = json_decode($result);
            $success = isset($r->success) ? $r->success : 'failed';
            $failure = isset($r->failure) ? $r->failure : 'failed';
            $multicast_id = isset($r->multicast_id) ? $r->multicast_id : 'failed';
            $detailed_results = isset($r->results) ? substr(json_encode($r->results), 0, 200) : '';

            if ($success == 1 &&  $failure == 0) {
                $log = "FCM Info: Success: {$success}, Failure: {$failure}, MulticastID: {$multicast_id} \n {$detailed_results}";
            } else {
                $log = $result;
                $log .= "\n\n";
                $log .= $data;
            }

            self::testjobAction($log);
        }
        curl_close($ch);
        self::testjobAction("Job finished!\n");
    }
    private static function testjobAction($msg = 'From test job')
    {
        $file_name = "logs/FCM-" . date('Y-m') . ".txt";
        $time = date('Y-m-d H:i:s');
        Storage::append($file_name, "[{$time}] {$msg} \n");
    }


    public static function sendPushToLogout($user_id) // Push to logout user from other devices
    {
        if ($user_id != null) {
            $previousDevices_iOS = Device::where('user_id', $user_id)->where('device_type', 'ios')->get()->pluck('device_push_token')->toArray();
            $previousDevices_android = Device::where('user_id', $user_id)->where('device_type', 'android')->get()->pluck('device_push_token')->toArray();

            if (!empty($previousDevices_iOS)) {
                $data = [
                    'title' => '',
                    'body' => '',
                    'flag' => 'logout'
                ];
                $ios_data = [
                    "registration_ids" => array_values($previousDevices_iOS),
                    // "notification"=>[
                    //     "body"=>$data["body"],
                    //     "title" => "",
                    //     "icon" => "",
                    // "sound" => "default",
                    // ],
                    "data" => $data,
                    "interruption-level" => "Passive",
                    'priority' => 'high',
                    'content_available' => true
                ];
                Device::post($ios_data);
            }

            if (!empty($previousDevices_android)) {
                $data = [
                    'title' => '',
                    'body' => '',
                    'flag' => 'logout'
                ];
                $android_data = [
                    "registration_ids" => array_values($previousDevices_android),
                    "data" => $data,
                    'priority' => 'high',
                    'content_available' => true
                ];
                Device::post($android_data);
            }
        }
        return true;
    }
}
