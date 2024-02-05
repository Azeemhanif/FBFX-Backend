<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
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
use App\Models\SubscriptionHistory;
use Illuminate\Support\Facades\Hash;
use App\Validations\FBFXValidations;
use App\Traits\{ValidationTrait};
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Google_Client;
use Google_Service_AndroidPublisher;
use Google_Service_Exception;

class SubscriptionController extends Controller
{

    public $successStatus = 200;

    use  ValidationTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function purchasePackage(Request $request)
    {
        try {

            $validatorResult = $this->checkValidations(FBFXValidations::validatePurchasePackage($request));
            if ($validatorResult) {
                return $validatorResult;
            }

            $userId = Auth::user()->id;
            $subscription = Subscription::where('user_id', $userId)->first();
            if ($request->has('is_downgrade') && $request->input('is_downgrade') === true) {
                // $this->deleteExtraFunctions($request->input('package_id'), $request->input('user_id'), $request->input('team_id'));
            }

            if ($subscription) {
                $subscription->update($request->all());
                $data = [
                    "title" => "silent_user_action",
                    "body" => "subscription updated"
                ];

                Device::sendPush($data, [$userId], "silent");
            } else {
                $subscription = Subscription::create($request->all());
            }

            // Manage subscription history
            SubscriptionHistory::create($request->all());
            $user = User::where('id', $userId)->update(['package_id' => $request->input('package_id'), 'is_premium' => true]);
            return sendResponse(200, 'Purchased Successful!', $subscription);
        } catch (\Exception $ex) {
            $response = sendResponse(500, $ex->getMessage(),  (object)[]);
            return $response;
        }
    }


    public function deleteExtraFunctions($packageId, $userId, $teamId)
    {
        if ($packageId == "1") {
            // delete card
            if ($teamId) {
                $cardIds = Card::where('user_id', $userId)->where('team_id', $teamId)->orderBy('id', 'desc')->pluck('id')->toArray();
            } else {
                $cardIds = Card::where('user_id', $userId)->orderBy('id', 'desc')->pluck('id')->toArray();
            }
            if (count($cardIds) > 1) {
                $cardIds = array_slice($cardIds, 1);
                Card::destroy($cardIds);
            }

            // Update the user's cards_count to 1
            $user = User::find($userId);
            if ($user) {
                $user->update(['cards_count' => 1]);
            }
        } else {
            $package = Package::whereId($packageId)->first();
            if (!$package) {
                return $this->sendError(config('constants.not_found'));
            }
            // Handle card
            if ($package->limit_card_create !== "unlimited") {
                if ($teamId) {
                    $cardIds = Card::where('user_id', $userId)->where('team_id', $teamId)->orderBy('id', 'desc')->pluck('id')->toArray();
                } else {
                    $cardIds = Card::where('user_id', $userId)->orderBy('id', 'desc')->pluck('id')->toArray();
                }

                if (count($cardIds) > $package->limit_card_create) {

                    $cardIdsToDelete = array_slice($cardIds, $package->limit_card_create);
                    Card::destroy($cardIdsToDelete);

                    // Get the remaining cards count
                    $remainingCardsCount = count($cardIds) - count($cardIdsToDelete);

                    $user = User::find($userId);
                    if ($user) {
                        $user->update(['cards_count' => $remainingCardsCount]);
                    }
                }
            }
        }

        // if (isset($activeFunction->name) && !empty($activeFunction->name)) {
        //     $this->handleChangeStatus($activeFunction->name, $userId, $teamId);
        //     $activeFunction->name = null;
        //     $activeFunction->save();
        // }
    }


    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        //
    }
}
