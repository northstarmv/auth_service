<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\User_Admin;
use App\Models\User_Client;
use App\Models\User_Doctor;
use App\Models\User_Gym;
use App\Models\User_Moderator;
use App\Models\User_Trainer;
use App\Models\UserPasswordResets;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'role' => 'required|string',
            'phone' => 'required|string',
            'nic' => 'required|string',
            'gender' => 'required|string',
            'birthday' => 'required|date',
            'country_code' => 'required|string',
            'currency' => 'required|string',
        ]);


        DB::beginTransaction();


        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => app('hash')->make($request->get('password')),
            'role' => $request->get('role'),
            'nic' => $request->get('nic'),
            'phone' => $request->get('phone'),
            'gender' => $request->get('gender'),
            'birthday' => Date::parse($request->get('birthday')),
            'address' => $request->get('address'),
            'country_code' => $request->get('country_code'),
            'currency' => $request->get('currency'),
        ]);

        if ($user->role == 'admin') {
            User_Admin::create([
                'user_id' => $user->id,
            ]);
        } elseif ($user->role == 'moderator') {
            User_Moderator::create([
                'user_id' => $user->id,
            ]);
        } elseif ($user->role == 'gym') {

            $this->validate($request, [
                'gym_type' => 'required|string|in:normal,exclusive',
                'gym_name' => 'required|string',
                'gym_address' => 'required|string',
                'gym_city' => 'required|string',
                'gym_country' => 'required|string',
                'hourly_rate' => 'required|numeric',
                'capacity' => 'required|integer',
                'gym_facilities' => 'required|array',
            ]);


            User_Gym::create([
                'user_id' => $user->id,
                'gym_type' => $request->get('gym_type'),
                'gym_name' => $request->get('gym_name'),
                'gym_phone' => $request->get('phone'),
                'gym_email' => $request->get('email'),
                'gym_address' => $request->get('gym_address'),
                'gym_city' => $request->get('gym_city'),
                'gym_country' => $request->get('gym_country'),
                'gym_facilities' => $request->get('gym_facilities'),
                'hourly_rate' => $request->get('hourly_rate'),
                'capacity' => $request->get('capacity'),
                'monthly_charge' => $request->get('monthly_charge'),
                'weekly_charge' => $request->get('weekly_charge'),
                'daily_charge' => $request->get('daily_charge'),
            ]);
        } elseif ($user->role == 'trainer') {

            try {
                $this->validate($request, [
                    'type' => 'required|string',
                    'about' => 'required|string'
                ]);

                User_Trainer::create([
                    'user_id' => $user->id,
                    'type' => $request->get('type'),
                    'about' => $request->get('about'),
                    'is_insured' => $request->get('is_insured'),
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

        } elseif ($user->role == 'doctor') {


            $this->validate($request, [
                'speciality' => 'required|string',
                'hourly_rate' => 'required|numeric',
                'can_prescribe' => 'required',
                'title' => 'required',
            ]);

            User_Doctor::create([
                'user_id' => $user->id,
                'hourly_rate' => $request->get('hourly_rate'),
                'speciality' => $request->get('speciality'),
                'can_prescribe' => $request->get('can_prescribe'),
                'title' => $request->get('title'),
            ]);

        } elseif ($user->role == 'client') {
            $this->validate($request, [
                'emergency_contact_name' => 'required|string',
                'emergency_contact_phone' => 'required|string',
            ]);

            User_Client::create([
                'user_id' => $user->id,
                'marital_status' => $request->get('marital_status'),
                'children' => $request->get('children'),
                'emergency_contact_name' => $request->get('emergency_contact_name'),
                'emergency_contact_phone' => $request->get('emergency_contact_phone'),
            ]);
        }

        try {
            DB::commit();

            $credentials = $request->only(['email', 'password']);
            if (!$token = Auth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return response()->json([
                'user' => User::with('trainer')
                    ->with('client')
                    ->with('doctor')
                    ->with('subscription')
                    ->where('id', '=', Auth::id())->first(),
                'token' => $token,
            ]);

            return response()->json(['user' => $user, 'message' => 'User Registration Success!']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registration Failed!',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function login(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $credentials = $request->only(['email', 'password']);

            if (!$token = Auth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return response()->json([
                'user' => User::with('trainer')
                    ->with('client')
                    ->with('doctor')
                    ->with('subscription')
                    ->where('id', '=', Auth::id())->first(),
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Login Failed!', 'error' => $e->getMessage()], 401);
        }
    }

    public function forgotPasswordStepOne(Request $request): JsonResponse
    {
        try {
            $User = User::where('email', $request->get('email'))->first();
            $OTP = rand(100000, 999999);

            UserPasswordResets::where('user_id','=', $User->id)->delete();

            UserPasswordResets::create([
                'user_id' => $User->id,
                'OTP' => $OTP,
            ]);

            Mail::html('<html><body>
                <h1>Your OTP Code: '.$OTP.'</h1>
                </body></html>', function($message) use($User) {
                $message->from('support@northstar.mv');
                $message->to($User->email);
                $message->subject('Test Email');
            });

            return response()->json(['message' => 'The 6 Digit OTP has been sent to your email!']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Reset Password Failed!', 'error' => $e->getMessage()], 401);
        }
    }

    public function forgotPasswordStepTwo(Request $request): JsonResponse
    {
        try {
            $UserPasswordResets = UserPasswordResets::where('otp','=', $request->get('code'))->first();
            if($UserPasswordResets){
                $User = User::where('id','=', $UserPasswordResets->user_id)->first();
                if($User){
                    $User->password = Hash::make($request->get('password'));
                    $User->save();
                    UserPasswordResets::where('user_id','=', $User->id)->delete();
                    return response()->json(['message' => 'Password Reset Success!']);
                } else {
                    return response()->json('Invalid OTP!');
                }
            } else {
                return response()->json('Invalid OTP!', 401);
            }

        } catch (\Throwable $e) {
            return response()->json(['message' => 'Reset Password Failed!', 'error' => $e->getMessage()], 401);
        }
    }

    public function check(): JsonResponse
    {
        try {
            $newToken = auth()->refresh(true, true);
            //return response()->json(['user' => Auth::user(), 'token' => $newToken]);
            return response()->json([
                'token' => $newToken,
                'user' => User::with('trainer')
                    ->with('client')
                    ->with('doctor')
                    ->with('subscription')
                    ->where('id', '=', Auth::id())->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Verification Failed! #2',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkWithoutTokenRefresh(): JsonResponse
    {
        try {
            return response()->json([
                'user' => User::with('trainer')
                    ->with('client')
                    ->with('doctor')
                    ->with('subscription')
                    ->where('id', '=', Auth::id())->first(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Verification Failed! #2',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getOneUser($id): JsonResponse
    {
        try {
            $user = User::with('trainer')
                ->with('client')
                ->with('doctor')
                ->with('subscription')
                ->with('qualifications')
                ->where('id', '=', $id)->first();
            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Get One User Failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getMe(): JsonResponse
    {
        $role = Auth::user()->role;

        if ($role == 'trainer') {
            return response()->json(
                User::with('trainer')
                    ->with('qualifications')
                    ->with('subscription')
                    ->where('id', '=', Auth::id())->first()
            );
        }

        if ($role == 'client') {
            return response()->json(
                User_Client::with('user')
                    ->with('physical_trainer')
                    ->with('diet_trainer')
                    ->with('requests')
                    ->with('requests.trainer')
                    ->with('subscription')
                    ->with('user.subscription')
                    ->with('user.client')
                    ->where('user_id', '=', Auth::id())->first()
            );
        }

        if ($role == 'doctor') {
            return response()->json(
                User::with('doctor')
                    ->with('qualifications')
                    ->where('id', '=', Auth::user()->id)->first()
            );
        }

        return response()->json([
            'message' => 'Get Me Failed!',
            'error' => 'User Role Not Found!',
        ]);


    }

    //Checks.
    public function checkAccountInfo(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|string',
            'phone' => 'required|string',
            'nic' => 'required|string',
        ]);

        if (User::where('email', '=', $request->get('email'))->exists()) {
            return response()->json([
                'message' => 'Email Already Exists!',
                'error' => 'Email Already Exists!',
            ]);
        }

        if (User::where('phone', '=', $request->get('phone'))->exists()) {
            return response()->json([
                'message' => 'Phone Number Already Exists!',
                'error' => 'Phone Number Already Exists!',
            ]);
        }

        if (User::where('nic', '=', $request->get('nic'))->exists()) {
            return response()->json([
                'message' => 'NIC/Passport Number Already Exists!',
                'error' => 'NIC/Passport Number Already Exists!',
            ]);
        }

        return response()->json([
            'message' => 'Account Info Is Valid!',
        ]);
    }

    //Updates
    public function updateMe(Request $request): JsonResponse
    {
        try {
            User::where('id', '=', Auth::user()->id)->update($request->all());

            return response()->json(['message' => 'Update Successful!']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update Me Failed!',
                'error' => $e->getMessage(),
            ],400);
        }
    }

    public function updateTrainer(Request $request):JsonResponse
    {
        try {
            User_Trainer::where('user_id', '=', Auth::user()->id)->update($request->all());

            return response()->json(['message' => 'Update Successful!']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update Trainer Failed!',
                'error' => $e->getMessage(),
            ],400);
        }
    }

    public function updateDoctor(Request $request):JsonResponse
    {
        try {
            User_Doctor::where('user_id', '=', Auth::user()->id)->update($request->all());

            return response()->json(['message' => 'Update Successful!']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update Trainer Failed!',
                'error' => $e->getMessage(),
            ],400);
        }
    }

    public function updateSubMe(Request $request): JsonResponse
    {
        try {
            User_Client::where('user_id', '=', Auth::id())->update($request->all());
            return response()->json(['message' => 'Update Successful!']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update Me Failed!',
                'error' => $e->getMessage(),
            ],400);
        }
    }

    public function sendTestEmail(): JsonResponse
    {
        try {

            Mail::html('<html><body>
                        <h1>Your OTP Code: </h1><p>Test Email</p>
                </body></html>', function($message) {
                $message->from('support@northstar.mv');
                $message->to('srilalsachintha@gmail.com');
                $message->subject('Test Email');
            });


            return response()->json(['message' => 'Email Sent!']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Send Test Email Failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }

    //Admin Ops
    public function updateMeAdmin(Request $request): JsonResponse
    {
        try {
            User::where('id', '=', $request->get('id'))->update($request->all());
            return response()->json([
                'message' => 'Update Successful!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Update Me Failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updateGymInformation(Request $request):JsonResponse
    {
        try {
            User::where('id','=', $request->get('id'))->update([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'nic' => $request->get('nic'),
                'phone' => $request->get('phone'),
                'birthday' => Date::parse($request->get('birthday')),
                'address' => $request->get('gym_address'),
                'country_code' => $request->get('country_code'),
                'currency' => $request->get('currency'),
            ]);

            User_Gym::where('user_id','=', $request->get('id'))->update([
                'gym_name' => $request->get('gym_name'),
                'gym_phone' => $request->get('phone'),
                'gym_email' => $request->get('email'),
                'gym_address' => $request->get('gym_address'),
                'gym_city' => $request->get('gym_city'),
                'gym_country' => $request->get('gym_country'),
                'gym_facilities' => $request->get('gym_facilities'),
                'hourly_rate' => $request->get('hourly_rate'),
                'capacity' => $request->get('capacity'),
                'monthly_charge' => $request->get('monthly_charge'),
                'weekly_charge' => $request->get('weekly_charge'),
                'daily_charge' => $request->get('daily_charge'),
            ]);

            return response()->json(['message' => 'Update Successful!']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Update Gym Failed!',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
