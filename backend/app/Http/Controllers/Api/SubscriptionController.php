<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends BaseController
{
    public function index(Request $request)
    {
        $Subscriptions = Subscription::paginate();
        return $this->paginationResponse($Subscriptions);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users',
            'price' => 'required|in:7.99,10.99,13.99',
            'name' => 'required|in:SD,HD,UHD',
            'status' => 'required|in:paid,expired',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after:start_date',
            'payment_method' => 'required|string|in:PayPal,Visa,MasterCard,Apple Pay,Google Pay,iDEAL',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        try {
            // Create the new subscription record in the database
            $subscription = Subscription::create([
                'user_id' => $validated['user_id'],
                'price' => $validated['price'],
                'name' => $validated['name'],
                'status' => $validated['status'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'payment_method' => $validated['payment_method'],
            ]);

            // Return the response with subscription data
            return $this->dataResponse([
                'subscription' => $subscription->only(['subscription_id','user_id', 'name', 'start_date', 'end_date', 'status', 'payment_method']),
            ], "Subscription created successfully.");
        } catch (\Exception $e) {

            Log::error($e);
            // Return error response in case of failure
            return $this->errorResponse(500, 'Failed to add new subscription. Please try again later.');
        }
    }

    public function show($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return $this->errorResponse(404, 'Subscription not found');
        }
        return $this->dataResponse($subscription, "Subscription payment details retrieved successfully");
    }

    public function update(Request $request, $id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return $this->errorResponse(404, 'Subscription not found');
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'sometimes|date|before:end_date',
            'end_date' => 'sometimes|date|after:start_date',
            'payment_method' => 'sometimes|string|in:PayPal,Visa,MasterCard,Apple Pay,Google Pay,iDEAL',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        try {
            // Update the subscription record in the database
            $subscription->update($validated->toArray());

            // Return the response with subscription data
            return $this->dataResponse([
                'subscription' => $subscription->only(['subscription_id','user_id', 'name', 'start_date', 'end_date', 'status', 'payment_method']),
            ], "Subscription updated successfully.");
        } catch (\Exception $e) {

            Log::error($e);
            // Return error response in case of failure
            return $this->errorResponse(500, 'Failed to update subscription. Please try again later.');
        }
    }

    public function destroy($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return $this->errorResponse(404, 'Subscription not found');
        }
        $subscription->delete();
        return $this->messageResponse('Subscription deleted successfully.', 200);
    }
}
