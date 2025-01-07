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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request is an XMLHttpRequest or if the Content-Type is XML
        if ($request->isXmlHttpRequest() || $request->header('Content-Type') === 'application/xml') {
            // Parse the XML content from the request body into a SimpleXMLElement object
            $xmlData = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);
            // Convert the SimpleXMLElement object to a JSON-encoded array
            $jsonData = json_decode(json_encode($xmlData), true);
            // Merge the parsed JSON data into the request object
            $request->merge($jsonData);
        }

        // Pass the modified request to the next middleware or controller
        $response = $next($request);

        // Check if the client expects an XML response based on the Accept header
        if ($request->header('Accept') === 'application/xml') {
            // Get the response content and decode it from JSON to an array
            $responseContent = $response->getContent();
            $jsonData = json_decode($responseContent, true);

            // Create a new SimpleXMLElement to build the XML response
            $xml = new \SimpleXMLElement('<root/>');
            // Recursively convert the array into XML nodes
            array_walk_recursive($jsonData, [$xml, 'addChild']);

            // Return the XML response with the correct Content-Type header
            return response($xml->asXML(), $response->getStatusCode())->header('Content-Type', 'application/xml');
        }

        // Return the original response if no XML conversion is needed
        return $response;
    }
}
