<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscriptions;
use App\Models\UserMainSubscriptionData;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidationErrrorHandler;
use Illuminate\Validation\ValidationException;

class UserSubscriptionController extends Controller
{

    public function activateFreeTrial():JsonResponse
    {
        try {
            $User = User::find(Auth::id());

            if($User->trial_used){
                return response()->json('Free Trial already used.', 400);
            } else {
                UserMainSubscriptionData::create([
                    'user_id' => Auth::id(),
                    'payment_id'=>'FREE_TRIAL',
                    'is_active'=> true,
                    'valid_till'=>Carbon::now()->addMonth()->toDateString()
                ]);

                $User->trial_used = true;
                $User->save();
                return response()->json(['success'=>true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 400);
        }
    }

    public function buySubscription():JsonResponse
    {
        try {
            /*$userMainSubscription = new UserMainSubscriptionData();
            $userMainSubscription->user_id = Auth::user()->id;
            $userMainSubscription->payment_id = '0';
            $userMainSubscription->monthly_charge = 99.99;
            $userMainSubscription->renewal_date = Carbon::now();
            $userMainSubscription->is_active = true;
            $userMainSubscription->save();*/

            return response()->json(['message' => 'NOT IMPLEMENTED'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function subcriptionAdAdmin(Request $request):JsonResponse
    {   
        try{
            $validatedData = $this->validate($request, [
                'subscriptionId'=>'required|integer|min:1|max:2147483647',
                'userId'=>'required|integer|min:1|max:2147483647',
            ]);
            try{

                $subscription = Subscriptions::select('duration_unit','duration_amount')
                    ->where('id', $validatedData['subscriptionId'])
                    ->get()->first();
        
                if(!$subscription){
                    
                    return response()->json(
                        [
                            ResponseHelper::error("0052")
                        ], 200
                        );

                }

                $current_user = User::where('id', $validatedData['userId'])->exists();
        
                if(!$current_user){
                    
                    return response()->json(
                        [
                            ResponseHelper::error("0053")
                        ], 200
                        );

                }

                $valid_till_months = 0;
                if( $subscription->duration_unit === 'month'){
                    $valid_till_months = intval($subscription->duration_amount);
                }elseif ( $subscription->duration_unit === 'year' ){
                    $valid_till_months = intval($subscription->duration_amount) * 12;
                }elseif ( $subscription->duration_unit === 'lifetime'){
                    $valid_till_months = 1200;
                }elseif( $valid_till_months ===0 ){

                    return response()->json(
                        [
                            ResponseHelper::error("0006")
                        ], 200
                        );

                }else{

                    return response()->json(
                        [
                            ResponseHelper::error("0054")
                        ], 200
                        );

                }

                if(UserMainSubscriptionData::where('user_id','=', $validatedData['userId'])->exists()){
                    $Subscription = UserMainSubscriptionData::where('user_id','=',$validatedData['userId'])->first();
                    $CurrentValidDate = Carbon::parse($Subscription->valid_till);
                    $NewValidDate = $CurrentValidDate->addMonths($valid_till_months)->toDateString();

                    $Subscription->payment_id = 'ADMIN_OFFER';
                    $Subscription->is_active = true;
                    $Subscription->valid_till = $NewValidDate;

                    $Subscription->save();
                } else {
                    UserMainSubscriptionData::create([
                        'user_id' => $validatedData['userId'],
                        'payment_id'=>'ADMIN_OFFER',
                        'is_active'=> true,
                        'valid_till'=>Carbon::now()->addMonths($valid_till_months)->toDateString()
                    ]);
                }

                return response()->json([
                    ResponseHelper::success("200","","success")
                ], 200);

            }catch  (\Exception $e) {
                return response()->json(
                    [
                        ResponseHelper::error("0500")
                    ], 200
                    );

            }
        }catch  (ValidationException  $e) {
            return response()->json(
                [
                    ResponseHelper::error( "0000", ValidationErrrorHandler::handle($e->validator->errors()) )
                ], 200
                );
        }
    }
    
}
