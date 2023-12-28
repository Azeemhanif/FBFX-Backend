<?php

namespace App\Traits;

use App\Models\Device;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait NotificationTrait
{

    public function sendToAllUsers($input, $ids)
    {
        $users = $ids;

        $data["message"] = $input['content'];
        $checkResponse = Device::sendPush($data, $users, "");
    }


    public function createNotification($input)
    {
        // $notification = new Notification();
        // if (isset($input['id']))
        //     $notification = Notification::where('id', $input['id'])->first();

        // $notification->content = $input['content'];
        // $notification->type = 'info';
        // $notification->save();
    }
}
