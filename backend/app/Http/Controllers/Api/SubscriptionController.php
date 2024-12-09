<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends BaseController
{
    public function index(Request $request)
    {
        $Subscriptions = Subscription::paginate();
        return $this->paginationResponse($Subscriptions);
    }

    protected function findSubscriptionOrFail($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            abort(404, 'Subscription Not found');
        }
        return $subscription;
    }

    public function store(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'price' => 'required|in:7.99,10.99,13.99',
            'name' => 'required|in:SD,HD,UHD',
            'status' => 'nullable|in:paid,expired',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after:start_date',
            'payment_method' => 'required|string|in:PayPal,Visa,MasterCard,Apple Pay,Google Pay,iDEAL',
        ]);

        $status = (strtotime($validated['end_date']) < time()) ? 'expired' : 'paid';

        // Start database transaction
        DB::beginTransaction();

        try {
            // Create the new subscription record in the database
            $subscription = $this->subscription::create([
                'user_id' => $validated['user_id'],
                'price' => $validated['price'],
                'name' => $validated['name'],
                'status' => $status,
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
        } catch (Exception $e) {
            // If anything goes wrong, roll back the transaction
            DB::rollBack();

            Log::error($e);
            // Return error response in case of failure
            return $this->errorResponse(500, 'Failed to add new subscription. Please try again later.');
        }
    }

    public function payment($id)
    {
        $subscription = $this->findSubscriptionOrFail($id);
        return $this->dataResponse($subscription);
    }

    public function updateStartDate(Request $request, $id)
    {
        $subscription =  $this->findSubscriptionOrFail($id);

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
        return $this->dataResponse($subscription);
    }

    public function updateEndDate(Request $request, $id)
    {
        $subscription =  $this->findSubscriptionOrFail($id);

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
        return $this->dataResponse($subscription);
    }

    public function updatePayment_method(Request $request, $id)
    {
        $subscription =  $this->findSubscriptionOrFail($id);

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
        return $this->dataResponse($subscription);
    }

}
