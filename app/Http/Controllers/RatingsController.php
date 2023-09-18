<?php

namespace App\Http\Controllers;

use App\Models\Ratings;
use App\Models\User;
use App\Models\User_Trainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingsController extends Controller
{
    public function saveReview(Request $request): JsonResponse
    {
        $this->validate($request, [
            'reviewee' => 'required|integer',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string',
        ]);

        try {
            Ratings::create([
                'reviewer' => Auth::user()->id,
                'reviewee' => $request->input('reviewee'),
                'rating' => $request->input('rating'),
                'comment' => $request->input('comment'),
            ]);

            $Reviewee = User_Trainer::where('user_id','=',$request->input('reviewee'))->first();
            User_Trainer::where('user_id','=',$request->input('reviewee'))->update([
                'stars_count' => $Reviewee->stars_count + $request->input('rating'),
                'rating_count' => $Reviewee->rating_count + 1,
                'rating' => $Reviewee->stars_count / $Reviewee->rating_count,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getReviews($trainer_id): JsonResponse
    {
        $reviews = Ratings::with('reviewer')
            ->where('reviewee','=',$trainer_id)
            ->limit(10)
            ->get();
        $totalCount = $reviews->count();

        if($totalCount < 1) {
            $totalCount = 1;
        }

        return response()->json([
            '5'=>$reviews->where('rating','=',5)->count(),
            '4'=>$reviews->where('rating','=',4)->count(),
            '3'=>$reviews->where('rating','=',3)->count(),
            '2'=>$reviews->where('rating','=',2)->count(),
            '1'=>$reviews->where('rating','=',1)->count(),
            'all'=>$totalCount,
            'reviews'=>$reviews,
        ]);
    }


}
