<?php

namespace App\Http\Controllers;

use App\Models\Therapy_Meeting;
use App\Models\user_therapy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\ValidationErrrorHandler;
use Illuminate\Validation\ValidationException;

class TherapyMeetingController extends Controller
{
    public function add(Request $request):JsonResponse
    {
        try{
            $validatedData = $this->validate($request, [
                'therapy_id' => 'required|integer|min:1|max:2147483647',
                'apt_date' => 'required|date',
                'reason' => 'required|string|min:1|max:250|xssPrevent',
                'additional' => 'string|min:1|max:100|xssPrevent',
                'start_time' => 'required|date_format:H:i:s',
                'end_time' => 'required|date_format:H:i:s'
            ]);
            
            try{

                if(!$request->has('additional')){ $request->input('additional', ''); } 

                //check therapy exist
                $current_therapy = user_therapy::select('paying_type','session_duration')
                    ->where('id', $validatedData['therapy_id'])
                    ->get()->first();

                if(!$current_therapy){
                    
                    return response()->json(
                        [
                            ResponseHelper::error("0046")
                        ], 200
                        );
    
                }
                $val_start_time = Carbon::parse( $validatedData['start_time'] );
                $val_end_time = Carbon::parse($validatedData['end_time']);

                // validate time range 
                if ( ($val_start_time ->gt( $val_end_time )) || ( $val_start_time  ->eq( $val_end_time))) {
                    
                    return response()->json(
                        [
                            ResponseHelper::error("0049")
                        ], 200
                        );
                }

                //only hours can insert per hour paying therpy
                if($current_therapy-> paying_type == 1){

                    if (!( ( preg_match('/^\d{2}\:00\:00$/', $validatedData['start_time']) && preg_match('/^\d{2}\:00\:00$/', $validatedData['end_time']) ))){
                        return response()->json(
                            [
                                ResponseHelper::error("0048")
                            ],
                            200
                        );
                    }
                    
                //session theripes times validate
                }else if($current_therapy-> paying_type == 2){

                    $duration = $val_end_time->diffInMinutes($val_start_time);
                    if( ! ($current_therapy-> session_duration == $duration) ){

                        return response()->json(
                            [
                                ResponseHelper::error("0051")
                            ],
                            200
                        );

                    }

                }else{
                    return response()->json(
                        [
                            ResponseHelper::error("0048")
                        ],
                        200
                    );
                }
                
                //check time is overlaping
                $Time_validate = DB::table('therapy__meetings')->where('therapy_id','=', $validatedData['therapy_id'])->where('apt_date','=', $validatedData['apt_date'])
                ->where(function ($query) use ($validatedData) {
                    $query->whereRaw('TIME(start_time) <= ? AND TIME(end_time) > ?', [$validatedData['start_time'], $validatedData['start_time']])
                        ->orWhereRaw('TIME(start_time) <= ? AND TIME(end_time) >= ?', [$validatedData['end_time'], $validatedData['end_time']])
                        ->orWhere(function ($query) use ($validatedData) {
                            $query->whereRaw('TIME(start_time) >= ? AND TIME(end_time) < ?', [$validatedData['start_time'], $validatedData['end_time']]);
                        });
                })
                ->exists();

                    if($Time_validate){
                    
                        return response()->json(
                            [
                                ResponseHelper::error("0047")
                            ], 200
                            );
        
                    }
                
                $dateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
                $User = Therapy_Meeting::create([
                    'user_id' => $request->auth->id,
                    'therapy_id' => $validatedData['therapy_id'],
                    'apt_date' => $validatedData['apt_date'],
                    'start_time' => $validatedData['start_time'],
                    'end_time' =>  $validatedData['end_time'],
                    'reason' =>  $validatedData['reason'],
                    'additional' =>  $request->get('additional'),
                    'status' => '1',
                    'created_time' => $dateTime
                ]);

                if (!$User){
                    return response()->json(
                        [
                            ResponseHelper::error("0500")
                        ], 200
                    );
                }
                
                return response()->json([
                    ResponseHelper::success("200","","success")
                ], 200);

            }catch  (\Exception $e) {
                return response()->json(
                    [
                        ResponseHelper::error("500")
                    ], 200
                    );

            }
        }catch  (ValidationException  $e) {
            return response()->json(
                [
                    ResponseHelper::error( "0000", ValidationErrrorHandler::handle($e->validator->errors()) )
                ], 200
                );
        }
    }

    public function reserved_time(Request $request):JsonResponse
    {
        try{
            $validatedData = $this->validate($request, [
                'therapy_id' => 'required|integer|min:1|max:2147483647',
                'apt_date' => 'required|date'
            ]);
            
            try{

                //check therapy exist
                $reserved_time = Therapy_Meeting::select('start_time','end_time')
                    ->where('therapy_id', $validatedData['therapy_id'])
                    ->where('apt_date', $validatedData['apt_date'])
                    ->where('status','=','1')
                    ->get();

                    return response()->json([
                        ResponseHelper::success("200", [
                            'domain' => env('AWS_URL'),
                            'result' => $reserved_time
                        ],"success")
                            
                    ]);

                

            }catch  (\Exception $e) {
                return response()->json(
                    [
                        ResponseHelper::error("500")
                    ], 200
                    );

            }
        }catch  (ValidationException  $e) {
            return response()->json(
                [
                    ResponseHelper::error( "0000", ValidationErrrorHandler::handle($e->validator->errors()) )
                ], 200
                );
        }
    }

    public function my_meetings(Request $request):JsonResponse
    {
        
        try{

            //check therapy exist
            $reserved_time = Therapy_Meeting::select('apt_date','start_time','end_time','reason','additional')
                ->where('user_id', $request->auth->id)
                ->where('status','=','1')
                ->get();

                return response()->json([
                    ResponseHelper::success("200", [
                        'domain' => env('AWS_URL'),
                        'result' => $reserved_time
                    ],"success")
                        
                ]);

            

        }catch  (\Exception $e) {
            return response()->json(
                [
                    ResponseHelper::error("500")
                ], 200
                );

        }      
            
    }
}
