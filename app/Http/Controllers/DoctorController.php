<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    public function approveNewDoctor(Request $request): JsonResponse
    {
        try {
            $Doctor = User_Doctor::where('user_id', $request->get('doctor_id'))->first();
            $Doctor->is_new = false;
            $Doctor->approved = true;
            $Doctor->save();
            return response()->json(['message'=>'Approved successfully'],200);
        } catch (\Throwable $th) {
            return response()->json(['message'=>'Error updating status'],500);
        }
    }

    public function toggleStatus(Request $request): JsonResponse
    {
        try {
            $Doctor = User_Doctor::where('user_id', $request->get('doctor_id'))->first();
            $Doctor->approved = !$Doctor->approved;
            $Doctor->save();
            return response()->json(['message'=>'Status updated successfully'],200);
        } catch (\Throwable $th) {
            return response()->json(['message'=>'Error updating status'],500);
        }
    }

    public function getAllNewDoctors():JsonResponse
    {
        try {
            $doctors = User::whereRelation('doctor', 'is_new','=', true)
                ->where('role','=','doctor')
                ->with('doctor')
                ->with('qualifications')
                ->get();
            return response()->json($doctors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllOldDoctors():JsonResponse
    {
        try {
            $doctors = User::whereRelation('doctor', 'is_new','=', false)
                ->where('role','=','doctor')
                ->with('doctor')
                ->with('qualifications')
                ->get();
            return response()->json($doctors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllDoctors(): JsonResponse
    {
        try {
            $doctors = User::with('doctor')
                ->with('qualifications')
                ->whereRelation('doctor', 'approved', true)
                ->whereRelation('doctor', 'is_new', false)
                ->where('role','=','doctor')->get();
            return response()->json($doctors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getOneDoctors($user_id): JsonResponse
    {
        try {
            $doctors = User::with('doctor')
                ->where('id','=',$user_id)
                ->get();
            return response()->json($doctors);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function searchDoctors(Request $request): JsonResponse
    {
        if($request->get('search_key') == 'ALL'){
            return response()->json(User::with('doctor')
                ->with('qualifications')
                ->whereRelation('doctor', 'approved', true)
                ->whereRelation('doctor', 'is_new', false)
                ->where('role','=','doctor')
                ->get());
        } else {
            $doctors = User::with('doctor')
                ->with('qualifications')
                ->whereRelation('doctor', 'approved', true)
                ->whereRelation('doctor', 'is_new', false)
                ->where('role','=','doctor')
                ->where('name', 'like', '%'.$request->get('search_key').'%')
                ->get();
            return response()->json($doctors);
        }
    }

    public function setOnlineStatus(): JsonResponse
    {
        try {
            User_Doctor::where('user_id','=',Auth::id())
                ->update(['online' => DB::raw('NOT online')]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
