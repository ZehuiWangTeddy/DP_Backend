<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcceptXMLInput
{
    public function handle(Request $request, Closure $next): Response
    {
        // Handle XML input
        if ($request->header('Content-Type') === 'application/xml') {
            try {
                $xmlData = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);
                if ($xmlData === false) {
                    throw new \Exception("Invalid XML format.");
                }
                $jsonData = json_decode(json_encode($xmlData), true);
                $request->merge($jsonData);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
        }

        // Proceed with the request and get the response
        $response = $next($request);

        // Convert JSON response to XML if the Accept header is application/xml
        if ($request->header('Accept') === 'application/xml') {
            $responseContent = $response->getContent();
            $jsonData = json_decode($responseContent, true);

            // Convert the JSON array to XML
            $xml = new \SimpleXMLElement('<root/>');
            array_walk_recursive($jsonData, [$xml, 'addChild']);

            return response($xml->asXML(), $response->status())->header('Content-Type', 'application/xml');
        }

        // Return the original response if no conversion is needed
        return $response;
    }
}
