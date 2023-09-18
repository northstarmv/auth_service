<?php

namespace App\Http\Controllers;

use App\Models\Gym_Gallery;
use App\Models\User_Gym;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GymController extends Controller
{
    public function deleteGymGalleryItem(Request $request):JsonResponse
    {
        try {
            Gym_Gallery::where('id', $request->get('id'))->delete();
            return response()->json(['message' => 'success']);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getGymByID($id):JsonResponse
    {
        $gym = User_Gym::with('user')
            ->with('gallery')
            ->where('user_id','=', $id)->get()->first();

        $galleryArray = [];
        foreach ($gym->gallery as $gallery){
            $galleryArray[] = $gallery->image_path;
        }


        $gym->gym_gallery = str_replace('\\','',json_encode($galleryArray));
        unset($gym->gallery);

        return response()->json($gym);
    }

    public function searchCommercialGyms(Request $request):JsonResponse
    {
        if($request->get('search_key') == 'ALL'){

            $Gyms  = User_Gym::with('user')
                ->with('gallery')
                ->where('gym_type','=','normal')->get();

        } else {
            $Gyms  = User_Gym::with('user')
                ->with('gallery')
                ->where('gym_type','=','normal')
                ->where('gym_name', 'like', '%'.$request->input('search_key').'%')->get();
        }
        foreach ($Gyms as $gym){
            $galleryArray = [];
            foreach ($gym->gallery as $gallery){
                $galleryArray[] = $gallery->image_path;
            }

            $gym->gym_gallery = str_replace('\\','',json_encode($galleryArray));
            unset($gym->gallery);
        }

        return response()->json($Gyms);
    }

    public function searchExclusiveGyms(Request $request):JsonResponse
    {
        if($request->get('search_key') == 'ALL'){

            $Gyms  = User_Gym::with('user')
                ->with('gallery')
                ->where('gym_type','=','exclusive')->get();

        } else {
            $Gyms  = User_Gym::with('user')
                ->with('gallery')
                ->where('gym_type','=','exclusive')
                ->where('gym_name', 'like', '%'.$request->input('search_key').'%')->get();
        }
        foreach ($Gyms as $gym){
            $galleryArray = [];
            foreach ($gym->gallery as $gallery){
                $galleryArray[] = $gallery->image_path;
            }

            $gym->gym_gallery = str_replace('\\','',json_encode($galleryArray));
            unset($gym->gallery);
        }

        return response()->json($Gyms);
    }


}
