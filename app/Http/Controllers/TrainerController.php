<?php

namespace App\Http\Controllers;

use App\Models\NotificationsAndRequests;
use App\Models\User;
use App\Models\User_Client;
use App\Models\User_Trainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainerController extends Controller
{
    public function getPublicTrainerData():JsonResponse
    {
        return response()->json(User_Trainer::with('user')->with('user.qualifications')->get());
    }

    public function newTrainerRequest(Request $request):JsonResponse
    {
        try {
            $MeetingController = new MeetingController();
            $response = $MeetingController->callServicePOST($request, 'notifications/actions/create-notifications');


            return response()->json(['success' => true,
                'message' => json_decode($response->getContent())
            ]);
        } catch (\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getHomeInformation():JsonResponse
    {
        return response()->json([
            'user' => Auth::user(),
            'notifications' => NotificationsAndRequests::where('has_seen','=', false)
            ->where('receiver_id','=',Auth::user()->id)->get(),
        ]);
    }

    public function getAllTrainers(): JsonResponse
    {
        try {
            $trainers = User::with('trainer')
                ->with('subscriptions')
                ->with('qualifications')
                ->where('role', '=', 'trainer')
                ->get();

            return response()->json($trainers);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function searchTrainers($search_key): JsonResponse
    {
        if ($search_key == 'ALL_TRAINERS'){
            return response()->json(User::with('trainer')
                ->with('qualifications')
                ->whereHas('subscription')
                ->where('role', '=', 'trainer')
                ->get());
        } else {
            return response()->json(User::with('trainer')
                ->with('qualifications')
                ->whereHas('subscription')
                ->where('role', '=', 'trainer')
                ->where('name', 'like', '%'.$search_key.'%')
                ->get());
        }

    }

    public function getTrainerClients(): JsonResponse
    {
        try {
            $clients = User_Client::where('physical_trainer_id', '=', Auth::user()->id)
                ->orWhere('diet_trainer_id', '=', Auth::user()->id)
                ->with('user')
                ->get();

            return response()->json($clients);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getTrainerClientsByTrainerID($trainerID): JsonResponse
    {
        try {
            $clients = User_Client::where('physical_trainer_id', '=', $trainerID)
                ->orWhere('diet_trainer_id', '=', $trainerID)
                ->with('user')
                ->get();

            return response()->json($clients);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function searchTrainerClients(Request $request): JsonResponse
    {
        if($request->get('search_key') == 'ALL'){
            $clients = User_Client::where('physical_trainer_id', '=', Auth::id())
                ->orWhere('diet_trainer_id', '=', Auth::id())
                ->with('user')
                ->get();
        } else {
            $search_key = $request->get('search_key');

            $clientIDS = User_Client::where('physical_trainer_id', '=', Auth::id())
                ->orWhere('diet_trainer_id', '=', Auth::id())
                ->get()
                ->pluck('user_id')->toArray();

            $resultIDS = User::whereIn('id', $clientIDS)
                ->where('name', 'like', '%'.$search_key.'%')
                ->get()
                ->pluck('id')->toArray();

            $clients = User_Client::WhereIn('user_id', array_intersect($clientIDS, $resultIDS))
                ->with('user')
                ->get();

        }
        return response()->json($clients);
    }

    public function updateTrainerSpecifics(Request $request): JsonResponse
    {
        $this->validate($request, [
            'about' => 'required|string'
        ]);

        User_Trainer::where('user_id',Auth::user()->id)->update([
            'about'=>$request->get('about'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Trainer specifics updated successfully'
        ]);
    }
}
