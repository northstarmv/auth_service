<?php

namespace App\Http\Controllers;

use App\Traits\ConsumesInternalServices;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7;

class MeetingController extends Controller
{
    use ConsumesInternalServices;

    public $baseUri;

    public function __construct()
    {
        $this->baseUri = config('services.meeting.base_uri');
    }

    /**
     * @throws GuzzleException
     */
    public function callServiceGET(Request $request,$all): JsonResponse
    {
        $client = new Client([
            'base_uri' => $this->baseUri
        ]);

        try {
            $response = $client->request('GET', $all,['json' => $request->all()]);
            return response()->json(json_decode($response->getBody()->getContents()));
        } catch (BadResponseException  $e) {
            $response = $e->getResponse();
            return response()->json([
                'error' => $response->getReasonPhrase(),
                'info' => json_decode($response->getBody()->getContents())
            ],$response->getStatusCode());
        }
    }

    public function callServicePOST(Request $request,$all): JsonResponse
    {
        $client = new Client([
            'base_uri' => $this->baseUri
        ]);

        try {
            $contentType = $request->header('Content-Type');
            
            if (strpos($contentType, 'multipart/form-data') !== false) {
                
                $multipartData = [];

                foreach ($request->all() as $name => $contents) {
                    if ($contents instanceof \Illuminate\Http\UploadedFile && $contents->isValid()) {
                        // If it's an instance of UploadedFile and the file is valid, it's an uploaded file
                        $multipartData[] = [
                            'name'     => $name,
                            'contents' => fopen($contents->getRealPath(), 'r'), // Open the file for reading
                            'filename' => $contents->getClientOriginalName(),  // Set the original filename
                        ];
                    }else{
                        $multipartData[] = [
                            'name'     => $name,
                            'contents' => $contents,
                        ];
                    }
                }

                $response = $client->request('POST', $all, [
                    'multipart' => $multipartData,
                ]);

                return response()->json(json_decode($response->getBody()->getContents()));
            } else {
                $response = $client->request('POST', $all,['json' => $request->all()]);
                return response()->json(json_decode($response->getBody()->getContents()));
            }
            
        } catch (BadResponseException  $e) {
            $response = $e->getResponse();
            return response()->json([
                'error' => $response->getReasonPhrase(),
                'info' => json_decode($response->getBody()->getContents())
            ],$response->getStatusCode());
        } catch (GuzzleException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ],$e->getCode());
        }
    }
}
