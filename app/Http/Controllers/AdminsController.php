<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class AdminsController extends Controller
{

    public function AuthAdmin(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $HasAnAdmin = User::where('email','=',$request->get('email'))
                ->whereIn('role',['admin','moderator','gym','therapy'])
                ->exists();
            if($HasAnAdmin){
                $credentials = $request->only(['email', 'password']);

                if (!$token = Auth::attempt($credentials)) {
                    return response()->json(['message' => 'Unauthorized'], 401);
                }

                return response()->json([
                    'user' => User::where('id', '=', Auth::id())->first(),
                    'token' => $token,
                ]);
            } else {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Login Failed!', 'error' => $e->getMessage()], 401);
        }
    }
    public function UpsertAdmin(Request $request):JsonResponse
    {
        if($request->get('id') != null){
            try {
                User::where('id','=',$request->get('id'))->update([
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'role' => $request->get('role'),
                    'nic' => $request->get('nic'),
                    'phone' => $request->get('phone'),
                ]);

                if($request->get('password') != null){
                    User::where('id','=',$request->get('id'))->update([
                        'password' => app('hash')->make($request->get('password')),
                    ]);
                }

                User_Admin::where('user_id','=',$request->get('id'))->update([
                    'user_id' => $request->get('id')
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Admin has been Upserted successfully.'
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 422);
            }
        } else {

            $this->validate($request, [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string',
                'role' => 'required|string',
                'phone' => 'required|string|unique:users',
                'nic' => 'required|string|unique:users',
            ]);

            try {
                $User = User::create([
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'password' => app('hash')->make($request->get('password')),
                    'role' => $request->get('role'),
                    'nic' => $request->get('nic'),
                    'phone' => $request->get('phone'),

                    'gender' => 'male',
                    'birthday' => Date::parse('1999-01-01'),
                    'address' => 'N/A',
                    'country_code' => 'N/A',
                    'currency' => 'N/A',
                ]);

                User_Admin::create(['user_id' => $User->id]);


                return response()->json([
                    'status' => 'success',
                    'message' => 'Admin has been Upserted successfully.'
                ]);

            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 422);
            }
        }




    }

    public function DeleteAdmin(Request $request):JsonResponse
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        try {
            $AdminCount = User::where('role','=','admin')->count();

            if($AdminCount == 1){
                return response()->json([
                    'status' => 'warning',
                    'message' => 'You cannot delete the last admin.'
                ]);
            } else {
                User::where('id', $request->get('id'))->delete();
                User_Admin::where('user_id', $request->get('id'))->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Admin has been deleted successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function GetAdmins(Request $request):JsonResponse
    {
        try {
            $admins = User::whereIn('role', ['admin','moderator'])->get();
            return response()->json($admins);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
