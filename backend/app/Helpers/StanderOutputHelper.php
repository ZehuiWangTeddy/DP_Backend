<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\ArrayToXml\ArrayToXml;

class StanderOutputHelper
{
    public static function StanderResponse($code, $message, $data = []): Response
    {
        $data = [
            'meta' => [
                'code' => $code,
                'message' => $message,
            ],
            'data' => $data,
        ];

        return self::send($data);
    }

    public static function paginationResponse($code, $message, $data): Response
    {
        $data = [
            'meta' => [
                'code' => $code,
                'message' => $message,
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                ]
            ],
            'data' => $data->items(),
        ];

        return self::send($data);
    }

    private static function send($data): Response
    {
        // if header accept xml
        $accept = request()->header('accept');
        if (strpos($accept, 'xml') !== false) {
            $result = ArrayToXml::convert($data);

            return new Response($result);
        }

        return response()->json($data);
    }
}
