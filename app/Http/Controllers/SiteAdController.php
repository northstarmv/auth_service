<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\SiteAd;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Helpers\ValidationErrrorHandler;

class SiteAdController extends Controller
{
    
    public static function updateAdDisplayOrder($adId, $newDisplayOrder)
    {
        try {
            // Get the current display order of the ad
            $currentAd = SiteAd::where('id', $adId)->first();
            $currentDisplayOrder = $currentAd->order;

            if ($currentDisplayOrder !== $newDisplayOrder) {
                if ($currentDisplayOrder < $newDisplayOrder) {
                    // Move the ad down the list
                    SiteAd::where('order', '>', $currentDisplayOrder)
                        ->where('order', '<=', $newDisplayOrder)
                        ->decrement('order');
                } else {
                    // Move the ad up the list
                    SiteAd::where('order', '>=', $newDisplayOrder)
                        ->where('order', '<', $currentDisplayOrder)
                        ->increment('order');
                }

                // Update the display order for the selected ad
                SiteAd::where('id', $adId)->where('status', '!=', 403)->update(['order' => $newDisplayOrder]);
            }

            return true;
        } catch (\Exception $e) {
            error_log($e);
            return false;
        }
    }

    public function order_list(): JsonResponse
    {
        try {
            $list = SiteAd::select('id','name', 'order')
                ->where('status','!=','403')->orderBy('order')->get();

            return response()->json([
                ResponseHelper::success("200", [
                    'result' => $list
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

    public function add(Request $request):JsonResponse
    {   
        try {

            if (! ($request->hasFile('image') )) {
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

            $file_1 = $request->file('image');
            $mime_1 = $file_1->getClientMimeType();

            if (! (in_array($mime_1, $allowedMimeTypes)) ) {
                
                return response()->json(
                    [
                        ResponseHelper::error("0043")
                    ]
                    );
            }

            try{
                $validatedData = $this->validate($request, [
                    'name' => 'required|string|min:1|max:200|xssPrevent',
                    'ad_url' => 'required|string|min:1|max:500|xssPrevent',
                    'duration' => 'required|integer|min:1|max:6400',
                    'order' => 'required|integer|min:0|max:2147483647'
                ]);
                try{

                    $maxDisplayOrder = SiteAd::max('order');
                    if (!$maxDisplayOrder){
                        $maxDisplayOrder = 1;
                    }else{
                        $maxDisplayOrder ++;
                    }

                    $disk = Storage::disk('s3');
            

                    $fileName_1 =  'adver_imgs/'.uniqid('img_').'.'.$file_1->getClientOriginalExtension();
                    

                    $disk->put( $fileName_1, $file_1->getContent(), 'public');

                    $dateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
                
                    $siteAd = new SiteAd();
                    $siteAd->name = $validatedData['name'];
                    $siteAd->ad_url = $validatedData['ad_url'];
                    $siteAd->duration = $validatedData['duration'];
                    $siteAd->order = $maxDisplayOrder;
                    $siteAd->ad_img = $fileName_1;
                    $siteAd->status = 1;
                    $siteAd->added_by = $request->auth->id;
                    $siteAd->added_time = $dateTime;
                    $siteAd->modified_by = null;
                    $siteAd->modified_time = $dateTime;

                    $siteAd->save();
                    if (!$siteAd){
                        return response()->json(
                            [
                                ResponseHelper::error("0500")
                            ], 200
                        );
                    }

                    if( $validatedData['order'] > 0){
                        $result = self::updateAdDisplayOrder($siteAd->id, $validatedData['order']);
                        
                        if( !$result){
                            return response()->json(
                                [
                                    ResponseHelper::error( "0000", "can't change the order" )
                                ], 200
                                );
                        }
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
            $list = SiteAd::select('id','name', 'ad_url','duration','order','ad_img','status')
                ->where('status','!=','403')->orderBy('order')->get();

            return response()->json([
                ResponseHelper::success("200", [
                    'domain' => env('AWS_URL'),
                    'result' => $list
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
                'adId'=>'required|integer|min:1|max:2147483647',
                'name' => 'required|string|min:1|max:200|xssPrevent',
                'ad_url' => 'required|string|min:1|max:500|xssPrevent',
                'duration' => 'required|integer|min:1|max:6400',
                'order' => 'required|integer|min:0|max:2147483647'
            ]);
            try{

                $disk = Storage::disk('s3');
                $current_site_ad = SiteAd::select('ad_img')
                    ->where('id', $validatedData['adId'])
                    ->where('status', '!=', 403)
                    ->get()->first();
                
                if(!$current_site_ad){
                    
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
                if($request->hasFile('image')){

                    $file_1 = $request->file('image');
                    $mime_1 = $file_1->getClientMimeType();
                    
                    if (! (in_array($mime_1, $allowedMimeTypes)) ) {
                    
                        return response()->json(
                            [
                                ResponseHelper::error("0043")
                            ]
                            );
                    }

                    $fileName_1 = 'adver_imgs/'.uniqid('img_').'.'.$file_1->getClientOriginalExtension();
                    $disk->put( $fileName_1, $file_1->getContent(), 'public');
                    $image_file_1 = $fileName_1;

                    $existFile = $disk->exists($current_site_ad->ad_img);
                    if($existFile){
                        $disk->delete($current_site_ad->ad_img);
                    }

                }else{
                    $image_file_1 = $current_site_ad->ad_img;
                }

                $dateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');
                
                $result = SiteAd::where('id',$validatedData['adId'] )
                    ->update([
                        'name' => $validatedData['name'],
                        'ad_url' => $validatedData['ad_url'],
                        'duration' => $validatedData['duration'],
                        'ad_img' =>  $image_file_1,
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

                if( $validatedData['order'] > 0){
                    $result = self::updateAdDisplayOrder($validatedData['adId'], $validatedData['order']);
                    
                    if( !$result){
                        return response()->json(
                            [
                                ResponseHelper::error( "0000", "can't change the order" )
                            ], 200
                            );
                    }
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
