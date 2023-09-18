<?php

namespace App\Http\Controllers;

use App\Models\Therapy_Qualification;
use App\Models\therapy_working_hours;
use Carbon\Carbon;
use App\Models\User;
use App\Models\User_Admin;
use App\Models\user_therapy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Date;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use App\Helpers\ValidationErrrorHandler;
use Illuminate\Validation\Rule;

class TherapyController extends Controller
{
    public function add(Request $request):JsonResponse
    {
        try{
            
            $validatedData = $this->validate($request, [
                'name' => 'required|string|min:1|max:200|xssPrevent',
                'email' => 'required|email|min:1|max:360|unique:users',
                'working_type' => 'required|integer|min:1|max:2',
                'paying_type'=> 'required|integer|min:1|max:2',
                'hourly_rate' => 'required_if:paying_type,1,|numeric|nullable',
                'session_duration' => 'required_if:paying_type,2,|numeric|nullable',
                'session_rate' => 'required_if:paying_type,2,|numeric|nullable',
                'password' => 'required|string|min:8|max:100',
                'phone' => 'required|string|unique:users|phoneValidate',
                'nic' => 'required|string|unique:users|min:1|max:50|xssPrevent',
                'gender' => 'required|string|min:1|max:10|xssPrevent',
                'country' => 'required|string|min:1|max:10|xssPrevent',
                'birthday' => 'required|date',
                'working_time' => 'array|required_if:working_type,2',
                'working_time.*.day' => 'required|integer', 
                'working_time.*.disabled' => 'required|boolean',
                'working_time.*.start_time' => 'required_if:working_time.*.disabled,false,|date_format:H:i:s|nullable',
                'working_time.*.end_time' => 'required_if:working_time.*.disabled,false,|date_format:H:i:s|nullable', 
                'qualification' => 'array|required_if:qualification,!empty',
                'qualification.*.title' => 'required_if:qualification,!empty|string|xssPrevent|min:1|max:100', 
                'qualification.*.description' => 'required_if:qualification,!empty|string|xssPrevent|min:1|max:500',
            ]);
            
            try{  

                //date time range check
                if($request->get('working_type') == '2'){
                    $array = $request->get('working_time');

                    foreach ($array as $value) {
                        if($value["disabled"] == false){

                            $val_start_time = Carbon::parse( $value["start_time"] );
                            $val_end_time = Carbon::parse($value['end_time']);
                            
                             // validate time range 
                            if ( ($val_start_time ->gt( $val_end_time )) || ( $val_start_time  ->eq( $val_end_time))) {
                                
                                return response()->json(
                                    [
                                        ResponseHelper::error("0000", ["Invalid Range In day ".$value['day']])
                                    ], 200
                                    );
                            }

                        }
                    }

                }


                $User = User::create([
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'password' => app('hash')->make($request->get('password')),
                    'role' => 'therapy',
                    'nic' => $request->get('nic'),
                    'phone' => $request->get('phone'),
                    'gender' =>$request->get('gender'),
                    'birthday' => Date::parse($request->get('birthday')),
                    'address' => 'N/A',
                    'country_code' => $request->get('country'),
                    'currency' => 'N/A',
                ]);

                if (!$User){
                    return response()->json(
                        [
                            ResponseHelper::error("0500")
                        ], 200
                    );
                }

                $User_therapy = user_therapy::create([
                    'user_id' => $User->id,
                    'working_type' => $request->get('working_type'),
                    'hourly_rate' => $request->get('hourly_rate'),
                    'paying_type' => $request->get('paying_type'),
                    'session_rate' => $request->get('session_rate'),
                    'session_duration' => $request->get('session_duration')
                ]);
                

                if (!$User_therapy){
                    User::where('id',  $User->id)->delete();
                    return response()->json(
                        [
                            ResponseHelper::error("0500")
                        ], 200
                    );
                }

                $qualifications =  $request->get('qualification');
                DB::transaction(function () use ($qualifications, $User_therapy) {
                    foreach ($qualifications as $item) {
                            Therapy_Qualification::create([
                                'therapy_id' => $User_therapy->id,
                                'title' => $item['title'],
                                'description' => $item['description'],
                            ]);
                    }
                });


                if($request->get('working_type') == 1){
                    return response()->json([
                        ResponseHelper::success("200","","success")
                    ], 200);
                }

                $working_hours =  $request->get('working_time');
                DB::transaction(function () use ($working_hours, $User_therapy) {
                    foreach ($working_hours as $item) {

                        if($item['disabled']){
                            therapy_working_hours::create([
                                'therapy_id' => $User_therapy->id,
                                'day' => $item['day'],
                                'rest_day' => $item['disabled'],
                                'start_time' => null,
                                'end_time' => null,
                            ]);
                        }else{
                            therapy_working_hours::create([
                                'therapy_id' => $User_therapy->id,
                                'day' => $item['day'],
                                'rest_day' => $item['disabled'],
                                'start_time' => $item['start_time'],
                                'end_time' => $item['end_time'],
                            ]);
                        }
                
        
                    }
                });

        
                return response()->json([
                    ResponseHelper::success("200","","success")
                ], 200);

            }catch  (\Exception $e) {
                error_log($e);
                return response()->json(
                    [
                        ResponseHelper::error("0000", $e)
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

    public function list(): JsonResponse
    {
        try {
            $stories =user_therapy::with('therapy_working_hours')->with('therapy__qualifications')
            ->select('users.id as user_id','users.nic as nic_no', 'users.name', 'users.email', 'users.phone', 'user_therapies.id as therapy_Id', 'user_therapies.hourly_rate','user_therapies.working_type','user_therapies.paying_type','user_therapies.session_rate','user_therapies.session_duration','users.country_code','users.birthday', 'users.gender',)
            ->join('users', 'users.id', '=', 'user_therapies.user_id')
            ->where('users.role', '=', 'therapy')
            ->get();

            return response()->json([
                ResponseHelper::success("200", [
                    'domain' => env('AWS_URL'),
                    'result' => $stories
                ],"success")
                    
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    ResponseHelper::error("0000",$e)
                ], 200
                );
        }
    }

    public function update(Request $request):JsonResponse
    {   
        try{
           $this->validate($request, [
                'userId'=>'required|integer|min:1|max:2147483647',
                'therapyId'=>'required|integer|min:1|max:2147483647',
                'name' => 'required|string|min:1|max:200|xssPrevent',
                'email' => ['required','email','min:1','max:360',Rule::unique('users','email')->ignore($request->get('userId'), 'id')],
                'working_type' => 'required|integer|min:1|max:2',
                'paying_type'=> 'required|integer|min:1|max:2',
                'gender' => 'required|string|min:1|max:10|xssPrevent',
                'country' => 'required|string|min:1|max:10|xssPrevent',
                'birthday' => 'required|date',
                'hourly_rate' => 'required_if:paying_type,1,|numeric|nullable',
                'session_duration' => 'required_if:paying_type,2,|numeric|nullable',
                'session_rate' => 'required_if:paying_type,2,|numeric|nullable',
                'password' => 'nullable|string|min:8|max:100',
                'phone' => ['required','string','phoneValidate',Rule::unique('users')->ignore($request->get('userId'), 'id')],
                'nic' => ['required','string',Rule::unique('users')->ignore($request->get('userId'), 'id')],
                'working_time' => 'array|required_if:working_type,2',
                'working_time.*.day' => 'required|integer', 
                'working_time.*.disabled' => 'required|boolean',
                'working_time.*.start_time' => 'required_if:working_time.*.disabled,false,|date_format:H:i:s|nullable',
                'working_time.*.end_time' => 'required_if:working_time.*.disabled,false,|date_format:H:i:s|nullable',
                'qualification' => 'array|required_if:qualification,!empty',
                'qualification.*.title' => 'required_if:qualification,!empty|string|xssPrevent|min:1|max:100', 
                'qualification.*.description' => 'required_if:qualification,!empty|string|xssPrevent|min:1|max:500',
            ]);
            try{
                
                //date time range check
                if($request->get('working_type') == '2'){
                    $array = $request->get('working_time');

                    foreach ($array as $value) {
                        if($value["disabled"] == false){

                            $val_start_time = Carbon::parse( $value["start_time"] );
                            $val_end_time = Carbon::parse($value['end_time']);
                            
                             // validate time range 
                            if ( ($val_start_time ->gt( $val_end_time )) || ( $val_start_time  ->eq( $val_end_time))) {
                                
                                return response()->json(
                                    [
                                        ResponseHelper::error("0000", ["Invalid Range In day ".$value['day']])
                                    ], 200
                                    );
                            }

                        }
                    }

                }

                User::where('id','=',$request->get('userId'))->update([
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'nic' => $request->get('nic'),
                    'phone' => $request->get('phone'),
                    'gender' =>$request->get('gender'),
                    'birthday' => Date::parse($request->get('birthday')),
                    'country_code' => $request->get('country'),
                ]);

                if($request->get('password') != null){
                    User::where('id','=',$request->get('userId'))->update([
                        'password' => app('hash')->make($request->get('password')),
                    ]);
                }

                user_therapy::where('id','=',$request->get('therapyId'))->update([
                    'working_type' => $request->get('working_type'),
                    'hourly_rate' => $request->get('hourly_rate'),
                    'paying_type' => $request->get('paying_type'),
                    'session_rate' => $request->get('session_rate'),
                    'session_duration' => $request->get('session_duration')
                ]);

                therapy_working_hours::where('therapy_id', $request->get('therapyId'))->delete();
                DB::statement('ALTER TABLE therapy_working_hours AUTO_INCREMENT = 1');

                Therapy_Qualification::where('therapy_id', $request->get('therapyId'))->delete();
                DB::statement('ALTER TABLE therapy__qualifications AUTO_INCREMENT = 1');

                $User_therapy =  $request->get('therapyId');

                $qualifications =  $request->get('qualification');
                DB::transaction(function () use ($qualifications, $User_therapy) {
                    foreach ($qualifications as $item) {
                            Therapy_Qualification::create([
                                'therapy_id' => $User_therapy,
                                'title' => $item['title'],
                                'description' => $item['description'],
                            ]);
                    }
                });

                if($request->get('working_type') == 1){
                    return response()->json([
                        ResponseHelper::success("200","","success")
                    ], 200);
                }

                $working_hours =  $request->get('working_time');
                DB::transaction(function () use ($working_hours, $User_therapy) {
                    foreach ($working_hours as $item) {
                        if($item['disabled']){
                            therapy_working_hours::create([
                                'therapy_id' => $User_therapy,
                                'day' => $item['day'],
                                'rest_day' => $item['disabled'],
                                'start_time' => null,
                                'end_time' => null,
                            ]);
                        }else{
                            therapy_working_hours::create([
                                'therapy_id' => $User_therapy,
                                'day' => $item['day'],
                                'rest_day' => $item['disabled'],
                                'start_time' => $item['start_time'],
                                'end_time' => $item['end_time'],
                            ]);
                        }
                    }
                });

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

    public function DeleteTherapy(Request $request):JsonResponse
    {

        try{
            $this->validate($request, [
                'userId' => 'required|integer|min:1|max:2147483647',
                'therapy_id' => 'required|integer|min:1|max:2147483647',
            ]);
             try{
                
                User::where('id', $request->get('userId'))->where('role','=','therapy')->delete();
 
                 return response()->json([
                     ResponseHelper::success("200","","success")
                 ], 200);
                
             }catch  (\Exception $e) {
                 return response()->json(
                     [
                         ResponseHelper::error("0000",$e)
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

    public function searchTherapy(Request $request):JsonResponse
    {

        try{
            $this->validate($request, [
                'search' => 'string|min:1|max:200|xssPrevent',
            ]);
             try{

                if(!$request->has('search')){ $request->input('search', ''); } 
                
                $therpies =user_therapy::with('therapy_working_hours')->with('therapy__qualifications')
                    ->select('users.id as user_id','users.nic as nic_no', 'users.name', 'users.email', 'users.phone', 'user_therapies.id as therapy_Id', 'user_therapies.hourly_rate','user_therapies.working_type','user_therapies.paying_type','user_therapies.session_rate','user_therapies.session_duration')
                    ->join('users', 'users.id', '=', 'user_therapies.user_id')
                    ->where('users.role', '=', 'therapy')
                    ->where('users.name', 'LIKE', '%' . $request->get('search'). '%')
                    ->get();
 
                    return response()->json(
                        ResponseHelper::success_app("200",  $therpies
                        ,"success")
                            
                    );
                
             }catch  (\Exception $e) {
                 return response()->json(
                     [
                         ResponseHelper::error("0000",$e)
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
    
    public function getTherapy(Request $request):JsonResponse
    {
        try{

            if(!$request->has('search')){ $request->input('search', ''); } 
            
            $therapy =user_therapy::with('therapy_working_hours')->with('therapy__qualifications')
            ->select('users.id as user_id','users.nic as nic_no', 'users.name', 'users.avatar_url', 'users.nic', 'users.country_code','users.birthday', 'users.gender',  'users.email','users.role', 'users.phone', 'user_therapies.id as therapy_Id', 'user_therapies.hourly_rate','user_therapies.working_type','user_therapies.paying_type','user_therapies.session_rate','user_therapies.session_duration')
            ->join('users', 'users.id', '=', 'user_therapies.user_id')
            ->where('users.role', '=', 'therapy')
            ->where('users.id', '=', $request->auth->id)
            ->first();

            return response()->json(
                        ResponseHelper::success_app("200",  $therapy
                        ,"success")
                            
                    );
            
         }catch  (\Exception $e) {
             return response()->json(
                 [
                     ResponseHelper::error("0000",$e)
                 ], 200
                 );

         }
    }
    
}
