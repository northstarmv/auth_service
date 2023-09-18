<?php

namespace App\Http\Controllers;

use App\Models\SuccessStory;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidationErrrorHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SuccessStoryController extends Controller
{
    public function add(Request $request):JsonResponse
    {   
        
        try {

            if (! ($request->hasFile('image_1') && $request->hasFile('image_2'))) {
                return response()->json(
                    [
                        ResponseHelper::error("0044")
                    ]
                    );
            }

            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
                // Add other allowed MIME types here if needed
            ];

            $file_1 = $request->file('image_1');
            $file_2 = $request->file('image_2');
            $mime_1 = $file_1->getClientMimeType();
            $mime_2 = $file_2->getClientMimeType();

            if (! (in_array($mime_1, $allowedMimeTypes) && in_array($mime_2, $allowedMimeTypes)) ) {
                
                return response()->json(
                    [
                        ResponseHelper::error("0043")
                    ]
                    );
            }

            try{
                $validatedData = $this->validate($request, [
                    'name' => 'required|string|min:1|max:200|xssPrevent',
                    'age' => 'required|integer|min:1|max:100',
                    'desc' => 'required|string|min:1|max:2000|xssPrevent',
                    'point_1' => 'required|string|min:1|max:100|xssPrevent',
                    'point_2' => 'required|string|min:1|max:100|xssPrevent',
                    'point_3' => 'required|string|min:1|max:100|xssPrevent',
                    'point_4' => 'required|string|min:1|max:100|xssPrevent',
                ]);

                
                try{

                    $disk = Storage::disk('s3');
            

                    $fileName_1 = 'success_story/'.uniqid('story_').'.'.$file_1->getClientOriginalExtension();
                    $fileName_2 = 'success_story/'.uniqid('story_').'.'.$file_2->getClientOriginalExtension();

                    $disk->put( $fileName_1, $file_1->getContent(), 'public');
                    $disk->put( $fileName_2, $file_2->getContent(), 'public');

                    $dateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
                
                    $successStory = new SuccessStory();
                    $successStory->name = $validatedData['name'];
                    $successStory->age = $validatedData['age'];
                    $successStory->desc = $validatedData['desc'];
                    $successStory->point_1 = $validatedData['point_1'];
                    $successStory->point_2 = $validatedData['point_2'];
                    $successStory->point_3 = $validatedData['point_3'];
                    $successStory->point_4 = $validatedData['point_4'];
                    $successStory->before_img = $fileName_1;
                    $successStory->after_img = $fileName_2;
                    $successStory->status = 1;
                    $successStory->added_by = $request->auth->id;
                    $successStory->added_time = $dateTime;
                    $successStory->modified_by = null;
                    $successStory->modified_time = $dateTime;

                    $successStory->save();
                
                    return response()->json([
                        ResponseHelper::success("200","","success")
                    ], 200);

                }catch  (\Exception $e) {
                    return response()->json(
                        [
                            ResponseHelper::error("0500")
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
        
        } catch (\Exception $e) {
            return response()->json(
                [
                    ResponseHelper::error( "0000", $e->getMessage() )
                ], 200
                );
        }
    }

    public function list(): JsonResponse
    {
        try {
            $stories = SuccessStory::select('id','name', 'age', 'desc','before_img','after_img', 'status', 'point_1', 'point_2', 'point_3', 'point_4')
                ->where('status','!=','403')->get();
            return response()->json([
                ResponseHelper::success("200", [
                    'domain' => env('AWS_URL'),
                    'result' => $stories
                ],"success")
                    
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    ResponseHelper::error("0500")
                ], 200
                );
        }
    }

    public function update(Request $request):JsonResponse
    {   
        try{
            $validatedData = $this->validate($request, [
                'storyId'=>'required|integer|min:1|max:2147483647',
                'name' => 'required|string|min:1|max:200|xssPrevent',
                'age' => 'required|integer|min:1|max:100',
                'desc' => 'required|string|min:1|max:2000|xssPrevent',
                'point_1' => 'required|string|min:1|max:100|xssPrevent',
                'point_2' => 'required|string|min:1|max:100|xssPrevent',
                'point_3' => 'required|string|min:1|max:100|xssPrevent',
                'point_4' => 'required|string|min:1|max:100|xssPrevent',
            ]);
            try{

                $disk = Storage::disk('s3');
                $story = SuccessStory::select('before_img','after_img')
                    ->where('id', $validatedData['storyId'])
                    ->where('status', '!=', 403)
                    ->get()->first();
        
                if(!$story){
                    
                    return response()->json(
                        [
                            ResponseHelper::error("0045")
                        ], 200
                        );

                }

                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png',
                    // Add other allowed MIME types here if needed
                ];

                //before image update
                $image_file_1 = "";
                if($request->hasFile('image_1')){

                    $file_1 = $request->file('image_1');
                    $mime_1 = $file_1->getClientMimeType();
                    
                    if (! (in_array($mime_1, $allowedMimeTypes)) ) {
                    
                        return response()->json(
                            [
                                ResponseHelper::error("0043")
                            ]
                            );
                    }

                    $fileName_1 = 'success_story/'.uniqid('story_').'.'.$file_1->getClientOriginalExtension();
                    $disk->put( $fileName_1, $file_1->getContent(), 'public');
                    $image_file_1 = $fileName_1;

                    $existFile = $disk->exists($story->before_img);
                    if($existFile){
                        $disk->delete($story->before_img);
                    }

                }else{
                    $image_file_1 = $story->before_img;
                }

                //after image update
                $image_file_2 = "";
                if($request->hasFile('image_2')){
    
                    $file_2 = $request->file('image_2');
                    $mime_2 = $file_2->getClientMimeType();
                    
                    if (! (in_array($mime_2, $allowedMimeTypes)) ) {
                    
                        return response()->json(
                            [
                                ResponseHelper::error("0043")
                            ]
                            );
                    }
    
                    $fileName_2 = 'success_story/'.uniqid('story_').'.'.$file_2->getClientOriginalExtension();
                    $disk->put( $fileName_2, $file_2->getContent(), 'public');
                    $image_file_2 = $fileName_2;
    
                    $existFile = $disk->exists($story->after_img);
                    if($existFile){
                        $disk->delete($story->after_img);
                    }
    
                }else{
                    $image_file_2 = $story->after_img;
                }

                $dateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
                
                $result = SuccessStory::where('id',$validatedData['storyId'] )
                    ->update([
                        'name' => $validatedData['name'],
                        'age' => $validatedData['age'],
                        'desc' => $validatedData['desc'],
                        'point_1' =>  $validatedData['point_1'],
                        'point_2' =>  $validatedData['point_2'],
                        'point_3' =>  $validatedData['point_3'],
                        'point_4' =>  $validatedData['point_4'],
                        'before_img' => $image_file_1,
                        'after_img' => $image_file_2,
                        'modified_by' => $request->auth->id,
                        'modified_time' => $dateTime,// You can also update other fields if needed
                    ]);
                
                if(!$result){
                    
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
                        ResponseHelper::error("0500")
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

    public function all(): JsonResponse
    {
        try {
            $stories = SuccessStory::select('id','name', 'age', 'desc','before_img','after_img', 'point_1', 'point_2', 'point_3', 'point_4')
                ->where('status','=','1')->get();
            return response()->json([
                ResponseHelper::success("200", [
                    'domain' => env('AWS_URL'),
                    'result' => $stories
                ],"success")
                    
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    ResponseHelper::error("0500")
                ], 200
                );
        }
    }
}
