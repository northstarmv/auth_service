<?php

namespace App\Http\Controllers;

use App\Models\AdsGallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdsGalleryController extends Controller
{
    public function getAllAds(): JsonResponse
    {
        try {
            return response()->json(AdsGallery::orderBy('updated_at', 'desc')->get());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteAd(Request $request):JsonResponse
    {
        try {
            AdsGallery::where('id', $request->get('id'))->delete();

            $disk = Storage::disk('s3');
            $deleteStat = false;
            $existFile = $disk->exists($request->get('image'));
            if($existFile){
                $deleteStat = $disk->delete($request->get('image'));
            }
            return response()->json(['success' => $deleteStat]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function SaveAdGalleryItem(Request $request):JsonResponse
    {
        try {
            $disk = Storage::disk('s3');
            $image = $request->file('image');
            $imageFileName = 'ads/'.uniqid('ad_').'.'.$image->getClientOriginalExtension();

            $disk->put($imageFileName, $image->getContent(), 'public');

            AdsGallery::create([
                'image' => $imageFileName,
                'link' => $request->get('link')
            ]);

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
