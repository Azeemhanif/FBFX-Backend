<?php

namespace App\Traits;

trait ValidationTrait
{
    private function checkValidations($function)
    {
        $validator = $function;

        if (!empty($validator)) {
            $errorsArr = $validator->errors()->toArray();

            $errorResponse =  validationResponce($errorsArr);
            return sendError($errorResponse);
            // return sendResponse(422, $errorResponse, null);
        }
    }
}
