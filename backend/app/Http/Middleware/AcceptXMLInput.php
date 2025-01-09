<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcceptXMLInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isXmlHttpRequest() || $request->header('Content-Type') == 'application/xml') {
            $xmlData = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);

            $jsonData = json_decode(json_encode($xmlData), true);
            $request->merge($jsonData);
        }

        return $next($request);
    }
}
