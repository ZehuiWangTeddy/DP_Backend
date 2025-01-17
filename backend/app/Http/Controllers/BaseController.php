<?php

namespace App\Http\Controllers;

use App\Helpers\StanderOutputHelper;
use Illuminate\Contracts\Pagination\Paginator;

class BaseController extends Controller
{
    public const DEFAULT_CODE = 200;

    // Success responses
    public function successResponse($data, $message = "success")
    {
        return $this->createResponse(self::DEFAULT_CODE, $message, $data);
    }

    // Error responses
    public function badRequestResponse($message = "Bad Request")
    {
        return $this->createResponse(400, $message);
    }

    public function unauthorizedResponse($message = "Unauthorized")
    {
        return $this->createResponse(401, $message);
    }

    public function forbiddenResponse($message = "Forbidden")
    {
        return $this->createResponse(403, $message);
    }

    public function notFoundResponse($message = "Not Found")
    {
        return $this->createResponse(404, $message);
    }

    public function internalErrorResponse($message = "Internal Server Error")
    {
        return $this->createResponse(500, $message);
    }

    // Custom responses
    public function errorResponse($errorCode, $errorMessage)
    {
        return $this->createResponse($errorCode, $errorMessage);
    }

    public function messageResponse($message, $code = self::DEFAULT_CODE)
    {
        return $this->createResponse($code, $message);
    }

    public function dataResponse($data, $message = "success")
    {
        return $this->createResponse(self::DEFAULT_CODE, $message, $data);
    }

    public function paginationResponse(Paginator $data, $message = "success")
    {
        return $this->createPaginationResponse(self::DEFAULT_CODE, $message, $data);
    }

    // Core response methods
    private function createResponse($code, $message, $data = [])
    {

        return StanderOutputHelper::StanderResponse($code, $message, $data);
    }

    private function createPaginationResponse($code, $message, Paginator $data)
    {
        return StanderOutputHelper::paginationResponse($code, $message, $data);
    }
}
