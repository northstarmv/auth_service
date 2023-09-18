<?php

namespace App\Http\Controllers;

use App\Models\TransactionHistories;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GymFinanceController extends Controller
{
    public function GetGymFinanceDetails():JsonResponse
    {
        try {
            return response()->json(
                TransactionHistories::with('user')
                    ->where('payee_id','=',Auth::id())
                    ->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at',Carbon::now()->month)
                    ->get()
            );

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
