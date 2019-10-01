<?php

namespace SteadfastCollective\CashierExtended\Http\Controllers\Concerns;

trait HandlesSubscriptionWebhooks
{
    /**
     * Handle customer subscription updated.
     *
     * @param  array $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        return $this->updateSubscription($payload);
    }

    protected function updateSubscription($payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            $data = $payload['data']['object'];
            $user->subscriptions()
                ->where('stripe_id', $data['id'])
                ->get()
                ->each(function (Subscription $subscription) use ($data) {
                    // Quantity...
                    if (isset($data['quantity'])) {
                        $subscription->quantity = $data['quantity'];
                    }

                    // Plan...
                    if (isset($data['plan']['id'])) {
                        $subscription->stripe_plan = $data['plan']['id'];
                    }

                    // Trial ending date...
                    if (isset($data['trial_end'])) {
                        $trial_ends = Carbon::createFromTimestamp($data['trial_end']);
                        if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trial_ends)) {
                            $subscription->trial_ends_at = $trial_ends;
                        }
                    }

                    // Cancellation date...
                    if (isset($data['cancel_at_period_end']) && $data['cancel_at_period_end']) {
                        $subscription->ends_at = $subscription->onTrial()
                            ? $subscription->trial_ends_at
                            : Carbon::createFromTimestamp($data['current_period_end']);
                    }

                    // Status...
                    if (isset($data['status']) && $data['status']) {
                        $subscription->status = $data['status'];
                    }

                    $subscription->save();
                });
        }
        return new Response('Webhook Handled', 200);
    }
}
