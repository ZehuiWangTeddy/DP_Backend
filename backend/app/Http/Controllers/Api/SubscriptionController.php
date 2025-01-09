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
            'status' => 'nullable|in:paid,expired',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after:start_date',
            'payment_method' => 'required|string|in:PayPal,Visa,MasterCard,Apple Pay,Google Pay,iDEAL',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, $validator->errors()->first());
        }

        $validated = $validator->safe();

        // Start database transaction
        DB::beginTransaction();

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

            // Commit the transaction after successful user creation
            DB::commit();

            // Return the response with subscription data
            return $this->dataResponse([
                'subscription' => $subscription->only(['subscription_id','user_id', 'name', 'start_date', 'end_date', 'status', 'payment_method']),
            ], "Subscription created successfully.");
        } catch (\Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            Log::error($e);
            // Return error response in case of failure
            return $this->errorResponse(500, 'Failed to add new subscription. Please try again later.');
        }
    }

    public function payment($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return $this->errorResponse('Subscription not found', 404);
        }
        return $this->dataResponse($subscription, "Subscription payment details retrieved successfully");
    }

    public function updateStartDate(Request $request, $id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            abort(404, 'Subscription Not found');
        }

        $validated = $request->validate([
            'start_date' => [
                'sometimes',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($subscription) {
                    if ($value > now()->format('Y-m-d') ) {
                        $fail('Start date cannot be later than today');
                    }
                }
            ],
        ]);

        $subscription->update($validated);
        return $this->dataResponse($subscription, "Subscription start date updated successfully");
    }

    public function updateEndDate(Request $request, $id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            abort(404, 'Subscription Not found');
        }

        $startDate = $subscription->start_date;

        $validated = $request->validate([
            'end_date' => [
                'sometimes',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($startDate) {
                    if ($startDate && $value < $startDate) {
                        $fail('end date must be after start date');
                    }
                },
            ],
        ]);

        $subscription->update($validated);
        return $this->dataResponse($subscription, "Subscription end date updated successfully");
    }

    public function updatePayment_method(Request $request, $id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            abort(404, 'Subscription Not found');
        }

        $validated = $request->validate([
            'payment_method' => [
                'sometimes',
                'string',
                function ($attribute, $value, $fail) use ($subscription) {
                    $allowedMethods = ['PayPal', 'Visa', 'MasterCard', 'Apple Pay', 'Google Pay', 'iDEAL'];
                    if ($value && !in_array($value, $allowedMethods)) {
                        $fail('The selected payment method is invalid. Allowed methods are: ' . implode(', ', $allowedMethods));
                    }
                }
            ],
        ]);

        $subscription->update($validated);
        return $this->dataResponse($subscription, "Subscription payment method updated successfully");
    }

    public function update(Request $request, $id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return $this->errorResponse('Subscription not found', 404);
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

        // Start database transaction
        DB::beginTransaction();
        try {
            // Update the subscription record in the database
            $subscription->update($validated->toArray());

            // Commit the transaction after successful update
            DB::commit();

            // Return the response with subscription data
            return $this->dataResponse([
                'subscription' => $subscription->only(['subscription_id','user_id', 'name', 'start_date', 'end_date', 'status', 'payment_method']),
            ], "Subscription updated successfully.");
        } catch (\Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            Log::error($e);
            // Return error response in case of failure
            return $this->errorResponse(500, 'Failed to update subscription. Please try again later.');
        }
    }

    public function destroy($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return $this->errorResponse('Subscription not found', 404);
        }
        $subscription->delete();
        return $this->messageResponse('Subscription deleted successfully.', 200);
    }
}
