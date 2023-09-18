<?php

namespace App\Http\Controllers;

use App\Models\CallHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallHistoryController extends Controller
{
    public function newCall(Request $request):JsonResponse
    {

        $this->validate($request, [
            'receiver_id' => 'required|integer',
            'duration' => 'required|numeric',
            'status' => 'required|string'
        ]);
        try {
            CallHistory::create([
                'caller_id' => auth()->id(),
                'receiver_id' => $request->get('receiver_id'),
                'duration' => $request->get('duration'),
                'status' => $request->get('status')
            ]);
            return response()->json(['message' => 'Call History Created Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCallHistory():JsonResponse
    {
        return response()->json(
            CallHistory::with('receiver')->where('caller_id','=',auth()->id())->get()
        );
    }
}
