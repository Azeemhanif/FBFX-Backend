<?php


use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Event;
use App\Models\User;

function validationResponce($errors)
{

    $requiredArray = [];
    $invalidArray = [];
    $uniqueArray = [];
    foreach ($errors as $key => $value) {
        if (strpos($value[0], 'required') > -1) {

            $message = str_replace('is required', '', $value[0]);
            $message = str_replace('!', '', $message);
            $message = str_replace('The', '', $message);
            $message = str_replace('field', '', $message);
            $message = str_replace('.', '', $message);
            $message = trim($message);
            $message = ucwords($message);
            array_push($requiredArray, $message);
        } else if (strpos($value[0], 'already') > -1) {
            array_push($uniqueArray, $value[0]);
        } else {
            array_push($invalidArray, $value[0]);
        }
    }


    if (!empty($requiredArray)) {
        $response = 'Following fields are required: ' . implode(", ", $requiredArray);
    } else if (!empty($uniqueArray)) {
        $response = implode(", ", $uniqueArray);
    } else {
        $response = implode(", ", $invalidArray);
    }
    return $response;
}

function sendResponse($code, $message, $data)
{
    $response = [
        'code' => $code,
        'message' => $message,
        'data' => $data,
    ];
    return  response()->json($response);
}

function sendError($error, $dataError = [], $code = 422)
{
    $response = [
        'code' =>  $code,
        'message' => $error,
        "data" => $dataError
    ];
    return response()->json($response, $code);
}

function sendEmailToUser($email, $type)
{
    try {
        Mail::to($email)->send($type);
    } catch (\Throwable $th) {
        $response = sendResponse(500, $th->getMessage(), []);
    }
}
