<?php

namespace App\Http\Controllers;

use App\Models\ClientToDocTrainerPayments;
use App\Models\TransactionHistories;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function getWalletBalance():JsonResponse
    {
        try {
            $wallet = UserWallet::where('user_id', Auth::id())->first();
            if(!$wallet) {
                UserWallet::create([
                    'user_id' => Auth::id(),
                    'balance' => 0
                ]);
                return response()->json(UserWallet::where('user_id', Auth::id())->first());
            }else {
                return response()->json($wallet);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getWalletTransactions(){
        return response()->json(TransactionHistories::where('user_id', Auth::id())->latest()->take(25)->get());
    }

    public function getUserWalletTransactions(Request $request){
        return response()->json(TransactionHistories::where('user_id', $request->get('userID'))->latest()->take(25)->get());
    }

    public function getMyPaymentsHistory():JsonResponse
    {
        try {
            return response()->json(ClientToDocTrainerPayments::with('client')->where('doc_trainer_id','=', Auth::id())->get());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
