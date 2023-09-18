<?php

namespace App\Http\Controllers;

use App\Traits\ConsumesInternalServices;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Auth;

class EcomController extends Controller
{
    use ConsumesInternalServices;

    public $baseUri;

    public function __construct()
    {
        $this->baseUri = config('services.ecom.base_uri');
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
            $request->merge(['auth_user_id' => Auth::id()]);
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
            $request->merge(['auth_user_id' => Auth::id()]);
            $response = $client->request('POST', $all,['json' => $request->all()]);
            return response()->json(json_decode($response->getBody()->getContents()));
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
