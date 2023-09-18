<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

trait ConsumesInternalServices
{
    /**
     * @throws GuzzleException
     */
    public function performRequest($method, $requestUrl, $request): ResponseInterface
    {
        $client = new Client([
            'base_uri' => $this->baseUri
        ]);

        if($method == 'POST'){
            return $client->request($method, $requestUrl, [
                'json'=>$request->all(),
                'Content-Type' => 'application/json',
            ]);
        } else {
            return $client->request($method, $requestUrl, [$request->all()]);
        }
    }
}
