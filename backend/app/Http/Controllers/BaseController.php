<?php

namespace App\Http\Controllers;

use App\Helpers\StanderOutputHelper;

class BaseController extends Controller
{
    public const DEFAULT_CODE = 200;

    public function errorResponse($error_code, $error_message)
    {
        return $this->StanderResponse($error_code, $error_message, []);
    }

    public function messageResponse($message, $code = 200)
    {
        return $this->StanderResponse($code, $message, []);
    }

    public function dataResponse($data, $message = "success")
    {
        return $this->StanderResponse(self::DEFAULT_CODE, $message, $data);
    }

    public function paginationResponse($data, $message = "success")
    {
        return StanderOutputHelper::paginationResponse(self::DEFAULT_CODE, $message, $data);
    }

    public function StanderResponse($code, $message, $data = [])
    {
        return StanderOutputHelper::StanderResponse($code, $message, $data);
    }
}
