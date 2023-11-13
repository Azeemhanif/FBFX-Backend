<?php

namespace App\Validations;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Validator;

class FBFXValidations
{

    public static function validateRegister($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'first_name' => 'required|max:100',
                'last_name' => 'required|max:100',
                'email' => 'required|unique:users,email,' . $request->id,
                'password' => 'required|min:8',
                'mobile' => 'required|max:100',
                // 'confirm_password' => 'required|same:password',
                'device_type' => '',
                'device_push_token' => '',
                'device_uuid' => '',
                'os_version' => '',
            ],
            [
                'password.required' => "Password is required",
                // 'confirm_password.required' => "Confirm password is required",
                // 'confirm_password.same' => 'Passwords do not match',
            ]
        )->stopOnFirstFailure(true);

        if ($validator->fails()) {
            //   return $validator->first();
            return $validator;
        }
    }



    public static function validateLogin($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|exists:users,email',
                'password' => 'required',
            ],

            [
                'email.exists' => 'The email does not exists',
            ]
        )->stopOnFirstFailure(true);
        if ($validator->fails()) {
            return $validator;
        }
    }

    public static function validateOtp($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'otp' => 'required',
                // 'id' => 'required',
            ]

        );
        if ($validator->fails()) {
            return $validator;
        }
    }


    public static function validateSocialSignup($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'provider_type' => 'required',
                'token' => 'required'
            ]
        );
        if ($validator->fails()) {
            return $validator;
        }
    }

    public static function validateSocialLogin($request)
    {
        $validator = Validator::make($request->all(), [
            'provider_type' => 'required',
            'token' => 'required',
        ]);
        if ($validator->fails()) {
            return $validator;
        }
    }

    public static function forgetValidate($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|exists:users,email',
            ],
            [
                'email.exists' => 'The email does not exists.',
            ],
        );
        if ($validator->fails()) {
            return $validator;
        }
    }

    public static function validateVerifyOtpExists($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required',
                'otp' => 'required',
            ]
        );
        if ($validator->fails()) {
            return $validator;
        }
    }

    public static function resetPasswordValidate($request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ], [
            'password.required' => "Password is required",
            'confirm_password.required' => "Confirm password is required",
            'confirm_password.same' => 'Passwords do not match.',
            // 'password.regex' => "Password must be contain at-least one alphabet or number."
        ])->stopOnFirstFailure(true);
    }

    public static function validatePostSignal($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'currency_pair' => 'required',
                'action' => 'required',
                'stop_loss' => 'required',
                'profit_one' => 'required',
                'profit_two' => 'required',
                'profit_three' => 'required',
                'RRR' => 'required',
                'timeframe' => 'required',
                'open_price' => 'required',
                'type' => 'required',

            ]
        )->stopOnFirstFailure(true);

        if ($validator->fails()) {
            //   return $validator->first();
            return $validator;
        }
    }

    public static function addAdminValidate($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|unique:users,email,' . $request->id,
            ]
        )->stopOnFirstFailure(true);

        if ($validator->fails()) {
            //   return $validator->first();
            return $validator;
        }
    }



    public static function editAdminValidate($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required',
                'email' => 'required|unique:users,email,' . $request->id,
            ]
        )->stopOnFirstFailure(true);

        if ($validator->fails()) {
            //   return $validator->first();
            return $validator;
        }
    }


    public static function detailAdminValidate($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ]
        )->stopOnFirstFailure(true);

        if ($validator->fails()) {
            //   return $validator->first();
            return $validator;
        }
    }
}
