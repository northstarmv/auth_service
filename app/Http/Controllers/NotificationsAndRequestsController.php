<?php

namespace App\Http\Controllers;

use App\Models\NotificationsAndRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsAndRequestsController extends Controller
{
    public function createNotification(Request $request):JsonResponse
    {
        $this->validate($request,[
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:512',
        ]);

        try {
            NotificationsAndRequests::create([
                'sender_id' => $request->get('sender_id'),
                'receiver_id' => $request->get('receiver_id'),
                'title' => $request->get('title'),
                'description' => $request->get('description'),
            ]);

            return response()->json(['message' => 'Notification created successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function markAsSeen():JsonResponse
    {
        NotificationsAndRequests::where('receiver_id', Auth::user()->id)->update(['has_seen' => true]);
        return response()->json(['success' => true]);
    }

    public function getMyNotifications():JsonResponse
    {
        return response()->json(NotificationsAndRequests::where('receiver_id', Auth::user()->id)
            ->where('has_seen','=', false)
            ->get());
    }
}
