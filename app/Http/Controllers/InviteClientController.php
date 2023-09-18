<?php

namespace App\Http\Controllers;

use App\Models\NotificationsAndRequests;
use App\Models\User;
use App\Models\User_Client;
use App\Models\User_Trainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InviteClientController extends Controller
{
    public function addClient(Request $request):JsonResponse
    {
        $User = User::where('email', $request->get('email'))->first();
        $User_Trainer = User_Trainer::where('user_id','=',auth()->user()->id)->first();

        if ($User) {
            if ($User_Trainer->type == 'physical'){
                User_Client::where('user_id', $User->id)->update([
                    'physical_trainer_id' => $User_Trainer->user_id,
                ]);
            } else {
                User_Client::where('user_id', $User->id)->update([
                    'diet_trainer_id' => $User_Trainer->user_id,
                ]);
            }

            NotificationsAndRequests::create([
                'sender_id'=>$User->id,
                'receiver_id'=>$User_Trainer->id,
                'title'=>'New Trainer Invite',
                'description'=> 'You has been been invited by'.$User->name,
            ]);

            return response()->json([
                'message' => 'Client added successfully'
                ]);
        } else {
            //send email
            return response()->json(['message' => 'Client Invited successfully']);
        }
    }

    public function removeTrainer(Request $request):JsonResponse
    {
        $this->validate($request, [
            'user_id' => 'required|integer',
            'trainer_id' => 'required|integer',
            'type' => 'required|string',
        ]);

        if($request->get('type') == 'physical'){
            User_Client::where('user_id', $request->get('user_id'))->update([
                'physical_trainer_id' => null,
            ]);
        } else {
            User_Client::where('user_id', $request->get('user_id'))->update([
                'diet_trainer_id' => null,
            ]);
        }

        return response()->json(['message' => 'Trainer removed successfully']);
    }
}
