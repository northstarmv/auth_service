<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Json\authorization;
use Carbon\Carbon;
use App\Models\setting;
use App\Helpers\ResponseHelper;

class SettingController extends Controller
{
    public function update(Request $request ): JsonResponse
    { 
        try{
                $this->validate($request, [
                    'data' => 'required|array',
                    'data.*' => 'required',
                    'data.*.key' => 'required|string|min:1|max:2000',
                    'data.*.value' => 'required',
                ]);
            

            try{

                $dateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
                $data = $request->get('data');
                if (empty($data)) {
                    return response()->json(
                        [
                            ResponseHelper::error("0006")
                        ]
                        );
                }
            
                $progress = 0;
            
                $success = DB::transaction(function () use ($data, $dateTime, &$progress) {
                    foreach ($data as $item) {
                        if (is_array($item['value']) && !is_null($item['value'])) {
                            $item['value'] = json_encode($item['value']);
                        }
            
                        $result = setting::where('key', $item['key'])
                            ->update(['value' => $item['value'], 'updated_at' => $dateTime]);
                    
                        if ($result) {
                            $progress += 1;
                        }
                    }
                    
                    return $progress;
                });
            
                $percentage = ($success / count($data)) * 100;
            
                return response()->json([
                    ResponseHelper::success("200", "","{$percentage}% successfully Updated")
                ], 200);

            }catch  (\Exception $e) {
                return response()->json(
                    [
                        ResponseHelper::error("0500")
                    ], 200
                    );

            }
        }catch  (\Exception $e) {
            return response()->json(
                [
                    ResponseHelper::error( "0000", $e->getMessage() )
                ], 200
                );
        }

    }

    public function list(Request $request): JsonResponse
    {
       //Input validation
       try{

        $this->validate($request, [
            'data' => 'array',
        ]);

            try{

                $data = $request->input('data');
                if (empty($data) || count($data) === 0) {
                    return response()->json(
                        [
                            ResponseHelper::error("0006")
                        ]
                        );
                }
            
                // Prepare the query using Eloquent
                $query = setting::select('key', 'value')->where('id', 0);

                foreach ($data as $key) {
                    $query->orWhere('key', $key);
                }

                // Execute the query
                $result = $query->get();

                return response()->json([
                    ResponseHelper::success("200", [
                        'domain' => env('AWS_URL'),
                        'result' => $result,
                    ],"success")
                    ]
                );

            }catch  (\Exception $e) {
                
                return response()->json(
                    [
                        ResponseHelper::error("0500")
                    ], 200
                    );

            }
        }catch  (\Exception $e) {
            return response()->json(
                [
                    ResponseHelper::error( "0000", $e->getMessage() )
                ], 200
                );
        }
    }
}
