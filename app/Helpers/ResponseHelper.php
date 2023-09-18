<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function error($code, $msg = '')
    {
        $response = json_decode(file_get_contents(resource_path('/Json/response.json')), true);
        if ($code == '0000') {
            return [
                "status" => false,
                "code" => "0000",
                "message" => $msg,
            ];
        }

        return [
            "status" => false,
            "code" => $code,
            "message" =>$response[$code],
        ];
    }

    public static function success($code = '200', $data = '', $msg = '')
    {
        $response = json_decode(file_get_contents(resource_path('/Json/response.json')), true);
        $res = [
            'status' => true,
            'code' => $code,
        ];

        if ($msg == '') {
            $res['message'] = $response[$code];
        } else {
            $res['message'] = $msg;
        }

        if ($data !== '') {
            $res['data'] = $data;
        }

        return $res;
    }

    public static function success_app($code = '200', $data = '', $msg = '')
    {
        return $data;
    }
}
