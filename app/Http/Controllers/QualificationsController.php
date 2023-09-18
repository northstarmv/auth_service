<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualificationsController extends Controller
{
    public function saveQualification(Request $request): JsonResponse
    {
        $this->validate($request, [
            'title' => 'required|string|max:64',
            'description' => 'required|string|max:256',
            'user_id' => 'required|integer',
        ]);

        try {
            Qualification::updateOrCreate(
                ['id' => $request->get('id')] ,
                [
                'title' => $request->title,
                'description' => $request->description,
                'user_id' => $request->user_id,
                ]);
            return response()->json(['message' => 'Qualification created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getQualifications($user_id): JsonResponse
    {
        try {
            return response()->json(Qualification::where('user_id', $user_id)->get());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteQualification($id):JsonResponse
    {
        try {
            Qualification::find($id)->delete();
            return response()->json(['message' => 'Qualification deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
