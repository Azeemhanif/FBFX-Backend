<?php

namespace App\Traits;

use App\Models\Device;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait NotificationTrait
{

    public function sendNotificationOnTpHitting($signal, $tp)
    {
        $type = 'signalInfo';
        $TPName = $tp;
        if ($tp == 'tp1') $TPName = 'TP1';
        if ($tp == 'tp2') $TPName = 'TP2';
        if ($tp == 'tp3') $TPName = 'TP3';

        $input["content"] = "$signal->currency reaches $TPName.";
        $this->sendToAllUsers($input, null, $type);
        return true;
    }

    public function sendNotificationOnSLHitting($signal, $sl)
    {
        $type = 'signalInfo';

        $input["content"] = "$signal->currency reaches SL.";
        $this->sendToAllUsers($input, null, $type);
        return true;
    }



    public function sendNotificationOnCloseSignal($signal, $close_price)
    {
        $type = 'signalInfo';
        $input["content"] = "$signal->currency manually closed at $close_price.";
        $this->sendToAllUsers($input, null, $type);
        return true;
    }

    public function sendNotificationOnAutoCloseSignal($signal, $close_price)
    {
        $type = 'signalInfo';
        $input["content"] = "$signal->currency closed at $close_price.";
        $this->sendToAllUsers($input, null, $type);
        return true;
    }


    public function sendOpeningPriceNotification($postSignal)
    {
        $type = 'signalInfo';
        $input["content"] = "A $postSignal->currency position is opened.";
        $this->sendToAllUsers($input, null, $type);
        return true;
    }

    public function sendToAllUsers($input, $ids, $type)
    {

        if (Auth::user()) {
            $users = User::where('is_notification', true)->where('id', '!=', Auth::user()->id)->pluck('id');
        } else {
            $users = User::where('is_notification', true)->pluck('id');
        }

        $data["message"] = $input['content'];
        $checkResponse = Device::sendPush($data, $users, $type);
        return true;
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
