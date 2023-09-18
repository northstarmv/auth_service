<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Previous_Projects;
use Illuminate\Validation\ValidationException;
use App\Helpers\ValidationErrrorHandler;
use Carbon\Carbon;

class PreviousProjectController extends Controller
{
    public function add(Request $request):JsonResponse
    {   
        
        try {

            if (! ($request->hasFile('image_1') && $request->hasFile('image_2')  && $request->hasFile('image_3') )) {
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
            $file_3 = $request->file('image_3');
            $mime_1 = $file_1->getClientMimeType();
            $mime_2 = $file_2->getClientMimeType();
            $mime_3 = $file_3->getClientMimeType();

            if (! (in_array($mime_1, $allowedMimeTypes) && in_array($mime_2, $allowedMimeTypes) && in_array($mime_3, $allowedMimeTypes) ) ) {
                
                return response()->json(
                    [
                        ResponseHelper::error("0043")
                    ]
                    );
            }

            try{

                $customMessages = [
                    'phone.phoneValidate' => 'The name field contains invalid characters.',
                ];

                $validatedData = $this->validate($request, [
                    'name' => 'required|string|min:1|max:200|xssPrevent',
                    'desc' => 'required|string|min:1|max:2000|xssPrevent',
                    'address' => 'required|string|min:1|max:500|xssPrevent',
                    'phone' => 'required|string|min:1|max:20|xssPrevent|phoneValidate',
                ], $customMessages );
                try{

                    $disk = Storage::disk('s3');
            

                    $fileName_1 = 'prev_projects/'.uniqid('project_').'.'.$file_1->getClientOriginalExtension();
                    $fileName_2 = 'prev_projects/'.uniqid('project_').'.'.$file_2->getClientOriginalExtension();
                    $fileName_3 = 'prev_projects/'.uniqid('project_').'.'.$file_3->getClientOriginalExtension();

                    $disk->put( $fileName_1, $file_1->getContent(), 'public');
                    $disk->put( $fileName_2, $file_2->getContent(), 'public');
                    $disk->put( $fileName_3, $file_3->getContent(), 'public');

                    $dateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
                
                    $prev_project = new Previous_Projects();
                    $prev_project->name = $validatedData['name'];
                    $prev_project->desc = $validatedData['desc'];
                    $prev_project->address = $validatedData['address'];
                    $prev_project->phone = $validatedData['phone'];
                    $prev_project->image_1 = $fileName_1;
                    $prev_project->image_2 = $fileName_2;
                    $prev_project->image_3 = $fileName_3;
                    $prev_project->status = 1;
                    $prev_project->added_by = $request->auth->id;
                    $prev_project->added_time = $dateTime;
                    $prev_project->modified_by = null;
                    $prev_project->modified_time = $dateTime;

                    $prev_project->save();
                
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
            $projects = Previous_Projects::select('id','name', 'desc', 'address','phone','image_1', 'image_2', 'image_3', 'status')
                ->where('status','!=','403')->get();
            return response()->json([
                ResponseHelper::success("200", [
                    'domain' => env('AWS_URL'),
                    'result' => $projects
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
                'projectId'=>'required|integer|min:1|max:2147483647',
                'name' => 'required|string|min:1|max:200|xssPrevent',
                'desc' => 'required|string|min:1|max:2000|xssPrevent',
                'address' => 'required|string|min:1|max:500|xssPrevent',
                'phone' => 'required|string|min:1|max:20|xssPrevent|phoneValidate',
            ]);
            try{

                $disk = Storage::disk('s3');
                $project = Previous_Projects::select('image_1','image_2','image_3')
                    ->where('id', $validatedData['projectId'])
                    ->where('status', '!=', 403)
                    ->get()->first();
        
                if(!$project){
                    
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

                //image 1 update
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

                    $fileName_1 = 'prev_projects/'.uniqid('project_').'.'.$file_1->getClientOriginalExtension();
                    $disk->put( $fileName_1, $file_1->getContent(), 'public');
                    $image_file_1 = $fileName_1;

                    $existFile = $disk->exists($project->image_1);
                    if($existFile){
                        $disk->delete($project->image_1);
                    }

                }else{
                    $image_file_1 = $project->image_1;
                }

                //image 2 update
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
    
                    $fileName_2 = 'prev_projects/'.uniqid('project_').'.'.$file_2->getClientOriginalExtension();
                    $disk->put( $fileName_2, $file_2->getContent(), 'public');
                    $image_file_2 = $fileName_2;
    
                    $existFile = $disk->exists($project->image_2);
                    if($existFile){
                        $disk->delete($project->image_2);
                    }
    
                }else{
                    $image_file_2 = $project->image_2;
                }

                //image 3 update
                $image_file_3 = "";
                if($request->hasFile('image_3')){
    
                    $file_3 = $request->file('image_3');
                    $mime_3 = $file_3->getClientMimeType();
                    
                    if (! (in_array($mime_3, $allowedMimeTypes)) ) {
                    
                        return response()->json(
                            [
                                ResponseHelper::error("0043")
                            ]
                            );
                    }
    
                    $fileName_3 = 'prev_projects/'.uniqid('project_').'.'.$file_3->getClientOriginalExtension();
                    $disk->put( $fileName_3, $file_3->getContent(), 'public');
                    $image_file_3 = $fileName_3;
    
                    $existFile = $disk->exists($project->image_3);
                    if($existFile){
                        $disk->delete($project->image_3);
                    }
    
                }else{
                    $image_file_3 = $project->image_3;
                }

                $dateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
                
                $result = Previous_Projects::where('id',$validatedData['projectId'] )
                    ->update([
                        'name' => $validatedData['name'],
                        'desc' => $validatedData['desc'],
                        'address' =>  $validatedData['address'],
                        'phone' =>  $validatedData['phone'],
                        'image_1' => $image_file_1,
                        'image_2' => $image_file_2,
                        'image_3' => $image_file_2,
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
}
