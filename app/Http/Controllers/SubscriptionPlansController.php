<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlansController extends Controller
{
    public function getSubscriptionPlans():JsonResponse
    {
        return response()->json(Subscriptions::all());
    }

    public function upsertSubscriptionPlan(Request $request):JsonResponse
    {
        try {
            $subscription = Subscriptions::updateOrCreate(
                ['id' => $request->get('id')],
                $request->all()
            );
            return response()->json($subscription);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
