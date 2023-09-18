<?php

namespace App\Http\Controllers;

use App\Models\Gym_Gallery;
use App\Models\User;
use App\Models\User_Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Helpers\ResponseHelper;


class FilesController extends Controller
{

    public function AddImage(Request $request):JsonResponse
    {
        try {

            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
                // Add other allowed MIME types here if needed
            ];

            $file = $request->file('image');
            $mime = $file->getClientMimeType();

            if (!in_array($mime, $allowedMimeTypes)) {
                
                return response()->json(
                    [
                        ResponseHelper::error("0043")
                    ]
                    );
            }

            $this->validate($request, [
                'name' => 'string|min:0|max:2000',
            ]);

            $disk = Storage::disk('s3');
            $filePath = $request->get('name');

            if($request->get('name') != null){
                
                try {
                    $existFile = $disk->exists($filePath);
                    if($existFile){
                       $disk->delete($filePath);
                    }
                    
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            ResponseHelper::error("0500")
                        ], 200
                        );
                }

            }

            $fileName = 'images/'.uniqid('image_').'.'.$file->getClientOriginalExtension();

            $disk->put( $fileName, $file->getContent(), 'public');
            
            return response()->json([
                ResponseHelper::success("200", [
                    'image_path' => $fileName
                ],"success")
                    
            ]);
        
        } catch (\Exception $e) {
            return response()->json(
                [
                    ResponseHelper::error( "0000", $e->getMessage() )
                ], 200
                );
        }
    }

    public function SaveProductImage(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $file = $request->file('image');
            $fileName = 'shop/'.uniqid('shops_').'.'.$file->getClientOriginalExtension();

            $disk->put( $fileName, $file->getContent(), 'public');
            $stat = $disk->url($fileName);

            return response()->json([
                'image_path' => $fileName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function SaveGymGallery(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $file = $request->file('image');
            $fileName = uniqid('gyms_').'.'.$file->getClientOriginalExtension();
            $filePath = 'gym-galleries/'.$fileName;

            $disk->put($filePath, $file->getContent(), 'public');
            $stat = $disk->url($filePath);

            Gym_Gallery::create([
                'user_id' => $request->get('id'),
                'image_path' => $fileName
            ]);

            return response()->json(['files' => $stat]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function SaveFile(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $file = $request->file('avatar');
            $fileName = 'avatars/'.uniqid('avatar_').'.'.$file->getClientOriginalExtension();

            $disk->put( $fileName, $file->getContent(), 'public');
            $stat = $disk->url($fileName);

            return response()->json(['files' => $stat]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function SaveExerciseRelatedFiles(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $animationFileName = 'NA';
            $previewFileName = 'NA';

            if($request->hasFile('animation')){
                $animation = $request->file('animation');
                $animationFileName = 'exercises/animations/'.uniqid('animation_').'.'.$animation->getClientOriginalExtension();
                $disk->put($animationFileName, $animation->getContent(), 'public');
            }

            if($request->hasFile('preview')){
                $preview = $request->file('preview');
                $previewFileName = 'exercises/previews/'.uniqid('preview_').'.'.$preview->getClientOriginalExtension();
                $disk->put($previewFileName, $preview->getContent(), 'public');
            }

            return response()->json([
                'animation' => $animationFileName,
                'preview' => $previewFileName
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function SaveResourceRelatedFiles(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $image = $request->file('image');
            $imageFileName = 'resources/'.uniqid('animation_').'.'.$image->getClientOriginalExtension();


            $disk->put($imageFileName, $image->getContent(), 'public');

            return response()->json([
                'image' => $imageFileName,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function SaveAvatar(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $file = $request->file('file');
            $user = User::where('id','=',Auth::id())->first();

            $fileName = uniqid('avatar_').'.'.$file->getClientOriginalExtension();
            $fullFileName = 'avatars/'.$fileName;



            try {
                $ImgFile = Image::make($file->getContent());
                $ImgFile = $ImgFile->resize(256, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $disk->put($fullFileName, $ImgFile->encode('jpg'), 'public');
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            if($user->avatar_url != 'default.jpg') {
                $disk->delete('avatars/'.$user->avatar_url);
            }
            $user->avatar_url = $fileName;
            $user->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function SaveLabReport(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $file = $request->file('file');
            $fileName = uniqid('lab_reports').'.'.$file->getClientOriginalExtension();
            $fullFileName = 'lab-reports/'.$fileName;

            $disk->put( $fullFileName, $file->getContent(), 'public');

            return response()->json(['success' => true,'file' => $fileName]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function SaveDocSignature(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $file = $request->file('file');
            $user = User_Doctor::find(Auth::id());

            $fileName = uniqid('avatar_').'.'.$file->getClientOriginalExtension();
            $fullFileName = 'signatures/'.$fileName;

            try {
                $ImgFile = Image::make($file->getContent());
                $ImgFile = $ImgFile->resize(256, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $disk->put($fullFileName, $ImgFile->encode('jpg'), 'public');
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            $disk->delete('avatars/'.$user->signature);
            $user->signature = $fileName;
            $user->save();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function SaveDocSeal(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $file = $request->file('file');
            $user = User_Doctor::find(Auth::id());

            $fileName = uniqid('avatar_').'.'.$file->getClientOriginalExtension();
            $fullFileName = 'seals/'.$fileName;

            try {
                $ImgFile = Image::make($file->getContent());
                $ImgFile = $ImgFile->resize(256, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $disk->put($fullFileName, $ImgFile->encode('jpg'), 'public');
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            $disk->delete('seals/'.$user->seal);
            $user->seal = $fileName;
            $user->save();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteFile(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $deleteStat = false;
            $existFile = $disk->exists($request->get('filePath'));
            if($existFile){
                $deleteStat = $disk->delete($request->get('filePath'));
            }
            return response()->json(['success' => $deleteStat]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
