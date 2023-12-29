<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Validations\FBFXValidations;
use App\Traits\{ValidationTrait};
use App\Traits\{NotificationTrait};
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public $successStatus = 200;
    use  ValidationTrait, NotificationTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateCreateNotification($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            $message = 'Notification created successfully!';

            $notification = new Notification();
            if (isset($input['id'])) {
                $message = 'Notification updated successfully!';
                $notification = Notification::where('id', $input['id'])->first();
                if (!$notification)
                    return sendResponse(202, 'Notification does not exists!', (object)[]);
            }
            $notification->deliver_from = Auth::user()->id;
            $notification->content = $input['content'];
            $notification->type = 'info';
            $notification->save();
            $userIds = User::where('is_notification', true)->where('id', '!=', Auth::user()->id)->pluck('id');

            $this->sendToAllUsers($input, $userIds);
            $response = new NotificationResource($notification);
            return sendResponse(200, $message, $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    /**
     * Display the specified resource.
     */


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $notification = Notification::where('id', '=', $id)->delete();

            if (!$notification) {
                return sendResponse(202, 'Notification does not exists!', (object)[]);
            }

            return sendResponse(200, 'Notification deleted successfully!', (object)[]);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }
}
