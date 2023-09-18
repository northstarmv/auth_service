<?php

namespace App\Http\Controllers;

use App\Models\ClientRequests;
use App\Models\NotificationsAndRequests;
use App\Models\User;
use App\Models\User_Client;
use App\Models\User_Trainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientRequestController extends Controller
{
    public function newTrainerRequests(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|email',
            'trainer_id' => 'required|integer',
            'trainer_type' => 'required|string|in:physical,diet',
        ]);
        try {
            $Client = User::where('email', $request->get('email'))->first();

            $Check = ClientRequests::where('client_id','=',$Client->id)
                ->where('trainer_id','=',Auth::id())
                ->exists();

            $UserClient = User_Client::where('user_id','=',$Client->id)->first();

            $CheckTwo = $UserClient->physical_trainer_id == $request->get('trainer_id');
            $CheckThree = $UserClient->diet_trainer_id == $request->get('trainer_id');

            if($Check) {
                return response()->json([
                    'success' => true,
                    'message' => 'You have already sent a request to this client.',
                ],200);
            } elseif ($CheckTwo || $CheckThree){
                return response()->json([
                    'success' => true,
                    'message' => 'You are already the trainer for this client!',
                ],200);
            } else {
                $ClientRequest = new ClientRequests();
                $ClientRequest->client_id = $Client->id;
                $ClientRequest->trainer_id = $request->get('trainer_id');
                $ClientRequest->trainer_type = $request->get('trainer_type');
                $ClientRequest->save();

                return response()->json(['success' => true, 'message' => 'Request sent successfully'], 200);
            }




        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acceptTrainerRequest($request_id): JsonResponse
    {
        $ClientRequest = ClientRequests::find($request_id);

        $Client = User::find($ClientRequest->client_id);
        $UserClient = User_Client::find($Client->id);
        $Trainer = User::where('id', '=', $ClientRequest->trainer_id)->first();

        if ($UserClient->physical_trainer_id == null) {
            $UserClient->physical_trainer_id = $Trainer->id;
        } else {
            if ($UserClient->diet_trainer_id == null) {
                $UserClient->diet_trainer_id = $Trainer->id;
            } else {
                return response()->json(['error' => 'You already have 2 trainers!'], 500);
            }
        }

        $UserClient->save();

        ClientRequests::find($request_id)->delete();


        return response()->json([
            'message' => 'Client added successfully'
        ]);

    }

    public function rejectTrainerRequest($request_id): JsonResponse
    {
        ClientRequests::find($request_id)->delete();
        return response()->json([
            'message' => 'Request deleted successfully'
        ]);
    }

    public function switchTrainers(): JsonResponse
    {
        try {
            $Client = User_Client::find(Auth::id());

            if ($Client->physical_trainer_id != null && $Client->diet_trainer_id != null) {
                $PTrainerID = $Client->physical_trainer_id;
                $DTrainerID = $Client->diet_trainer_id;

                $Client->physical_trainer_id = $DTrainerID;
                $Client->diet_trainer_id = $PTrainerID;
                $Client->save();

                return response()->json([
                    'message' => 'Trainers switched successfully'
                ]);
            } else {
                return response()->json([
                    'message' => 'You Have No Secondary Trainer!'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
