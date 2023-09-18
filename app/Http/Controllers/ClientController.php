<?php

namespace App\Http\Controllers;

use App\Models\ClientRequests;
use App\Models\User;
use App\Models\User_Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function getAll():JsonResponse
    {
        return response()->json(User_Client::with('user')
            ->with('subscription')
            ->with('diet_trainer')
            ->with('physical_trainer')
            ->get()
        );
    }

    public function search($search_key):JsonResponse
    {
        try {
            return response()->json(User::where('role', '=','client')
                ->where('name', 'like', '%'.$search_key.'%')
                ->get());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function verifyRequest(Request $request):JsonResponse
    {
        $this->validate($request, [
            'trainer_id' => 'required|integer',
        ]);
        try {
            $TrainerID = $request->get('trainer_id');
            $UserClient = User_Client::find(Auth::id());

            if($UserClient->physical_trainer_id == $TrainerID){
                return response()->json(['error' => 'You can not request to your own trainer!'], 400);
            } elseif ($UserClient->diet_trainer_id == $TrainerID){
                return response()->json(['error' => 'You can not request to your own trainer!'], 400);
            } elseif($UserClient->physical_trainer_id != null && $UserClient->diet_trainer_id != null){
                return response()->json(['error' => 'You already have 2 trainers!'], 400);
            } else {
                return response()->json(['success' => 'You can sent a request to this trainer!'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeTrainer(Request $request):JsonResponse
    {
        $this->validate($request, [
            'trainer_id' => 'required|integer',
            'type' => 'required|string|in:physical,diet'
        ]);

        if($request->get('type') == 'physical'){
            $Client = User_Client::find(Auth::user()->id);
            $Client->physical_trainer_id = null;

            if($Client->diet_trainer_id != null){
                $Client->physical_trainer_id = $Client->diet_trainer_id;
                $Client->diet_trainer_id = null;
            }

            $Client->save();

        } else {
            User_Client::where('user_id', '=', Auth::user()->id)
                ->update(['diet_trainer_id' => null]);
        }

        return response()->json(['success' => true]);

    }

    public function saveAdditionalData(Request $request):JsonResponse
    {
        $this->validate($request, [
            'health_conditions' => 'required|json',
            'marital_status' => 'required|string',
            'children' => 'required|integer'
        ]);

        try {
            $client = User_Client::find(Auth::user()->id);
            $client->health_conditions = json_decode($request->get('health_conditions'),true);
            $client->marital_status = $request->get('marital_status');
            $client->children = $request->get('children');
            $client->is_complete = true;
            $client->save();
            return response()->json(['success' => 'Health conditions saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
