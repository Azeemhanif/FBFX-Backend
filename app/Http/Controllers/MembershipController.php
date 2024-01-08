<?php

namespace App\Http\Controllers;

use App\Http\Resources\IbBrokerResource;
use App\Http\Resources\MembershipResource;
use App\Http\Resources\PremiumMemberResource;
use App\Http\Resources\UserResource;
use App\Models\IbBroker;
use App\Models\Membership;
use App\Models\PremiumMember;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Validations\FBFXValidations;
use App\Traits\{ValidationTrait};
use App\Traits\{NotificationTrait};
use Google\Service\ServiceControl\Auth;

class MembershipController extends Controller
{
    use  ValidationTrait, NotificationTrait;
    public function store(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateCreateMembership($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            $message = 'Membership created successfully!';

            $membership = new Membership();
            if (isset($input['id'])) {
                $message = 'Membership updated successfully!';
                $membership = Membership::where('id', $input['id'])->first();
                if (!$membership)
                    return sendResponse(202, 'Membership does not exists!', (object)[]);
            }
            $membership->monthly_price = $input['monthly_price'];
            $membership->yearly_price = $input['yearly_price'];
            $membership->save();
            $response = new MembershipResource($membership);
            return sendResponse(200, $message, $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }



    public function addIbBroker(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validateIbBroker($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();

            $user = User::where('email', $input['email'])->first();
            if (!$user)
                return sendResponse(422, 'User does not registered!', (object)[]);

            $ibbroker = IbBroker::where('email', $input['email'])->first();
            if ($ibbroker)
                return sendResponse(422, 'Email already exists for membership!', (object)[]);

            $ibbroker = new IbBroker();
            $ibbroker->account_no = $input['account_no'];
            $ibbroker->email = $input['email'];
            $ibbroker->user_id = $user->id;
            $ibbroker->status = 'pending';
            $ibbroker->save();

            $response = new IbBrokerResource($ibbroker);
            return sendResponse(200, 'Joining ib broker request created successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }


    public function ibBrokerListing(Request $request)
    {
        try {

            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);

            $ibbroker =  IbBroker::where('status', 'pending');
            $count = $ibbroker->count();
            $data = $ibbroker->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);
            $collection =  IbBrokerResource::collection($data);
            $response = [
                'totalCount' => $count,
                'notifications' => $collection,
            ];
            return sendResponse(200, 'Joining ib broker request created successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    public function listingUsers(Request $request)
    {
        try {
            $email = $request->query('email', null);
            //fetching those users, those are not admin or have approved ibbroker.
            $users =  User::where('email', 'LIKE', '%' . $email . '%')->where('role', 'user')->has('ibBroker')->get();
            $collection =  UserResource::collection($users);

            return sendResponse(200, 'Data fetched successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }



    // public function addUsers(Request $request)
    // {
    //     try {
    //         $validatorResult = $this->checkValidations(FBFXValidations::validateIbBroker($request));
    //         if ($validatorResult) return $validatorResult;
    //         $input = $request->all();

    //         $user = User::where('email', $input['email'])->first();
    //         if (!$user)
    //             return sendResponse(422, 'User does not registered!', (object)[]);

    //         $ibbroker = IbBroker::where('email', $input['email'])->first();
    //         if ($ibbroker)
    //             return sendResponse(422, 'Email already exists for membership!', (object)[]);

    //         $ibbroker = new IbBroker();
    //         $ibbroker->account_no = $input['account_no'];
    //         $ibbroker->email = $input['email'];
    //         $ibbroker->status = 'pending';
    //         $ibbroker->save();

    //         $response = new IbBrokerResource($ibbroker);
    //         return sendResponse(200, 'Joining ib broker request created successfully!', $response);
    //     } catch (\Throwable $th) {
    //         $response = sendResponse(500, $th->getMessage(), (object)[]);
    //         return $response;
    //     }
    // }

    public function addPremiumUsers(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validatePremiumUsers($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            $user = User::where('email', $input['email'])->first();
            if (!$user)
                return sendResponse(422, 'User does not exists!', (object)[]);

            if ($user->is_premium == true)
                return sendResponse(422, 'User already have subsciption!', (object)[]);

            $user->is_premium = true;
            $user->save();

            $subscription = Subscription::where('user_id', $user->id)->first();
            $subscriptionhistory = SubscriptionHistory::where('user_id', $user->id)->first();
            if (!$subscription) {
                $subscription = new Subscription();
            }

            if (!$subscriptionhistory) {
                $subscriptionhistory = new SubscriptionHistory();
            }
            $subscription->add_by_admin = true;
            $subscription->user_id = $user->id;
            $subscription->subscription_type = $input['membership_type'];
            $subscription->save();


            $subscriptionhistory->add_by_admin = true;
            $subscriptionhistory->user_id = $user->id;
            $subscriptionhistory->subscription_type = $input['membership_type'];
            $subscriptionhistory->save();
            // $premiumMember = PremiumMember::where('email', $input['email'])->first();
            // if ($premiumMember)
            //     return sendResponse(422, 'Email already have membership!', (object)[]);

            // $premiumMember = new PremiumMember();
            // $premiumMember->membership_type = $input['membership_type'];
            // $premiumMember->email = $input['email'];
            // $premiumMember->user_id = $user->id;
            // $premiumMember->status = 'approved';
            // $premiumMember->type = 'premium';
            // $premiumMember->save();
            $response = new UserResource($user);
            return sendResponse(200, 'Premium member created successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    public function cancelSubscription($id)
    {
        try {
            $user = User::where('id', $id)->first();
            $user->is_premium = false;
            $user->save();
            $subscription = Subscription::where('user_id', $user->id)->first();
            if ($subscription) $subscription->delete();
            $response = new UserResource($user);
            return sendResponse(200, 'Data fetching successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }



    public function listingPremiumUsers(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $type = $request->query('type', 'all');

            // $premiumMemberIds = PremiumMember::where(['type' => 'premium', 'status' => 'approved'])->pluck('user_id');
            if ($type == 'premium') {
                $users  = User::where(['is_premium' => true, 'role' => 'user']);
            }
            if ($type == 'free') {
                $users  = User::where(['is_premium' => false, 'role' => 'user']);
            }
            if ($type == 'all') {
                $users = User::where('role', 'user');
            }

            $count = $users->count();
            $data = $users->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);
            $collection = UserResource::collection($data);
            $response = [
                'totalCount' => $count,
                'users' => $collection,
            ];
            return sendResponse(200, 'Data fetching successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }
}
