<?php

namespace App\Http\Controllers;

use App\Models\ClientToDocTrainerPayments;
use App\Models\TransactionHistories;
use App\Models\User;
use App\Models\User_Doctor;
use App\Models\UserMainSubscriptionData;
use App\Models\UserWallet;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use BMLConnect\Client;
use Illuminate\Support\Facades\Auth;

class PaymentGatewayController extends Controller
{
    public $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHBJZCI6ImZjMzMxZDA0LTM4YzItNDNhYi1hZTcxLTQzYmNmYWJhNDg2NiIsImNvbXBhbnlJZCI6IjYyMWM0MWQ4NTNkOGJkMDAwOTJkODM4ZiIsImlhdCI6MTY0NjAxOTAzNCwiZXhwIjo0ODAxNjkyNjM0fQ.8el6Q0vP2G_tWOgfK1AP7mKoq-AMK_Qhc1-6xqwelvE';
    public $appId = 'fc331d04-38c2-43ab-ae71-43bcfaba4866';

    //PRO_VERSION | WALLET_TU

    public function makePayment(Request $request):JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
        ]);

        try {
            $client = new Client($this->apiKey, $this->appId, 'sandbox');
            $json = [
                "currency" => "USD",
                "customerReference" => Auth::id(),
                "localId" => 'PRO_VERSION',
                "amount" => $request->get('amount'),
                "redirectUrl" => "https://api.northstar.mv/api/payments/message" // Optional redirect after payment completion
            ];

            $transaction = $client->transactions->create($json);



            return response()->json($transaction);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function makeTopUp(Request $request):JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
        ]);

        try {
            $client = new Client($this->apiKey, $this->appId, 'sandbox');
            $json = [
                "currency" => "USD",
                "localId" => 'WALLET_TU',
                "customerReference" => Auth::id(),
                "amount" => $request->get('amount'),
                "redirectUrl" => "https://api.northstar.mv/api/payments/message" // Optional redirect after payment completion
            ];
            $transaction = $client->transactions->create($json);
            return response()->json($transaction);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function commonPayments(Request $request):JsonResponse
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric',
            ]);

            try {
                $client = new Client($this->apiKey, $this->appId, 'sandbox');
                $json = [
                    "currency" => "USD",
                    "localId" => 'COMMON_PY',
                    "customerReference" => Auth::id(),
                    "amount" => $request->get('amount'),
                    "redirectUrl" => "https://api.northstar.mv/api/payments/message" // Optional redirect after payment completion
                ];
                $transaction = $client->transactions->create($json);
                return response()->json($transaction);
            } catch (\Exception $e) {
                return response()->json($e->getMessage());
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function payForExclusiveGym(Request $request):JsonResponse
    {
        $this->validate(request(), [
            'amount' => 'required|integer',
            'schedule_ids' => 'required|array',
        ]);

        try {
            $amount = $request->get('amount');
            $wallet = UserWallet::where('user_id',Auth::id())->first();
            $afterBalance = $wallet->balance - $amount;


            if($afterBalance >= 0){
                $wallet->decrement('balance',$amount);
                $wallet->save();
            } else {
                return response()->json(['message' => 'Insufficient balance'],403);
            }

            TransactionHistories::create([
                'user_id' => Auth::id(),
                'wallet_id'=>$wallet->id,
                'payee_id'=>$request->get('gym_id'),
                'type'=>'Debit',
                'amount'=>$amount,
            ]);

            try {
                $fitnessController = new FitnessController();
                $fitnessController->callServicePOST($request,'/exclusive-gyms/actions/confirm-schedule');

                return response()->json([
                    'message'=>'Payment Successful',
                    'status'=>'success',
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()],500);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function payForCommercialGym(Request $request):JsonResponse
    {
        $this->validate(request(), [
            'amount' => 'required|numeric',
        ]);

        try {
            $amount = $request->get('amount');
            $wallet = UserWallet::where('user_id',Auth::id())->first();
            $afterBalance = $wallet->balance - $amount;


            if($afterBalance >= 0){
                $wallet->decrement('balance',$amount);
                $wallet->save();
            } else {
                return response()->json(['message' => 'Insufficient balance'],403);
            }

            TransactionHistories::create([
                'user_id' => Auth::id(),
                'wallet_id'=>$wallet->id,
                'payee_id'=>$request->get('gym_id'),
                'type'=>'Debit',
                'amount'=>$amount,
            ]);

            try {
                $fitnessController = new FitnessController();
                $fitnessController->callServicePOST($request,'/commercial-gyms/actions/new-subscription');

                return response()->json([
                    'message'=>'Payment Successful',
                    'status'=>'success',
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()],500);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function payForDocMeetingNow(Request $request):JsonResponse
    {
        $this->validate(request(), [
            'doctor_id' => 'required|integer',
            'client_id' => 'required|integer',
            'seconds'=>'required|integer',
        ]);


        try {
            $seconds = $request->get('seconds');
            $doctor_id = $request->get('doctor_id');
            $client_id = $request->get('client_id');

            $Hours = $seconds / 3600;
            $Doctor = User_Doctor::find($doctor_id);
            $DocUser = User::find($Doctor->user_id);

            if($Doctor->charge_type == 'SESSION'){
                $amount = $Doctor->hourly_rate;
            } else {
                $amount = round($Doctor->hourly_rate * $Hours,2);
            }



            $wallet = UserWallet::where('user_id','=',$client_id)->first();

            $wallet->decrement('balance',$amount);
            $wallet->save();

            $TRS = TransactionHistories::create([
                'user_id' => Auth::id(),
                'wallet_id'=>$wallet->id,
                'type'=>'Debit',
                'amount'=>$amount,
                'description'=>'Payment for Doctor Meeting with '.$DocUser->name,
            ]);

            ClientToDocTrainerPayments::create([
                'client_id'=>$client_id,
                'doc_trainer_id'=>$doctor_id,
                'payment_id'=>$TRS->id,
                'amount'=>$amount,
            ]);

            return response()->json([
                'message'=>'Payment Successful',
                'status'=>'success',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function confirmPayment(Request $request):JsonResponse
    {
        try {
            $state = $request->get('state');
            $localId = $request->get('localId');
            if($state == 'CONFIRMED') {

                switch ($localId) {
                    case 'PRO_VERSION':
                        $transactionId = $request->get('transactionId');
                        $userId = $request->get('customerReference');

                        if(UserMainSubscriptionData::where('user_id','=', $userId)->exists()){
                            $Subscription = UserMainSubscriptionData::where('user_id','=',$userId)->first();
                            $CurrentValidDate = Carbon::parse($Subscription->valid_till);
                            $NewValidDate = $CurrentValidDate->addMonth()->toDateString();

                            $Subscription->payment_id = $transactionId;
                            $Subscription->is_active = true;
                            $Subscription->valid_till = $NewValidDate;

                            $Subscription->save();
                        } else {
                            UserMainSubscriptionData::create([
                                'user_id' => $userId,
                                'payment_id'=>$transactionId,
                                'is_active'=> true,
                                'valid_till'=>Carbon::now()->addMonth()->toDateString()
                            ]);
                        }
                        break;
                    case 'WALLET_TU':
                        $transactionId = $request->get('transactionId');
                        $amount = $request->get('amount'); // !!! In cents !!!
                        $userId = $request->get('customerReference');
                        $wallet = UserWallet::where('user_id',$userId)->first();
                        $wallet->increment('balance',$amount);

                        TransactionHistories::create([
                            'user_id' => $userId,
                            'wallet_id'=>$wallet->id,
                            'type'=>'Credit',
                            'amount'=>$amount,
                            'transactionId'=>$transactionId,
                        ]);

                        break;
                }
                return response()->json(['success'=>'Payment Successful']);
            } else {
                return response()->json(['error' => 'Payment is not confirmed']);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function paymentRedirect(Request $request)
    {
        return response(
            '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"><title>Payment Completed</title></head><body style="background-color: #232323;"><div style="position: absolute; width: 99vw; transform: translateY(-50%); margin-top: 46vh;"><svg style="transform:translateX(-50%);margin-left: 50%; width:50%" xmlns="http://www.w3.org/2000/svg" width="145" height="145" viewBox="0 0 145 145"><g id="Group_8835" data-name="Group 8835" transform="translate(-115 -261)"><circle id="Ellipse_1353" data-name="Ellipse 1353" cx="72.5" cy="72.5" r="72.5" transform="translate(115 261)" fill="#24a72d"/><path id="Path_3163" data-name="Path 3163" d="M-21739.27,11119.37l4.646-2.324s10.326,8.647,16.91,19.1c1.676-5.033,21.164-41.688,43.105-53.3a37.257,37.257,0,0,1,2.711,3.614s-27.232,23.489-36.4,56.788c-1.678,7.745-4.518,9.293-7.744,9.422s-7.1-7.485-9.937-13.164S-21739.27,11119.37-21739.27,11119.37Z" transform="translate(21892.424 -10784.527)" fill="#fff"/></g></svg><p style="text-align: center; color: white; font-size: 24px;">Payment <br><b style=" font-size: 32px;">Successful!</b> </p></div><p style=" text-align: center; color: rgb(165, 165, 165); position: absolute; width: 99vw; bottom: 40px;">Press back again to exit </p></body></html>'
        );
    }
}
