<?php

namespace App\Http\Controllers;

use App\Http\Resources\IbBrokerResource;
use App\Http\Resources\MembershipResource;
use App\Http\Resources\PremiumMemberResource;
use App\Http\Resources\UserResource;
use App\Models\IbBroker;
use App\Models\Membership;
use App\Models\PremiumMember;
use App\Models\User;
use Illuminate\Http\Request;
use App\Validations\FBFXValidations;
use App\Traits\{ValidationTrait};
use App\Traits\{NotificationTrait};

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
                return sendResponse(422, 'User does not registered!', (object)[]);

            $premiumMember = PremiumMember::where('email', $input['email'])->first();
            if ($premiumMember)
                return sendResponse(422, 'Email already have membership!', (object)[]);

            $premiumMember = new PremiumMember();
            $premiumMember->membership_type = $input['membership_type'];
            $premiumMember->email = $input['email'];
            $premiumMember->user_id = $user->id;
            $premiumMember->status = 'approved';
            $premiumMember->type = 'premium';
            $premiumMember->save();
            $response = new PremiumMemberResource($premiumMember);
            return sendResponse(200, 'Premium member created successfully!', $response);
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

            $premiumMemberIds = PremiumMember::where(['type' => 'premium', 'status' => 'approved'])->pluck('user_id');
            if ($type == 'premium') {
                $users  = User::whereIn('id', $premiumMemberIds);
            }
            if ($type == 'free') {
                $users  = User::where('role', 'user')->whereNotIn('id', $premiumMemberIds);
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
