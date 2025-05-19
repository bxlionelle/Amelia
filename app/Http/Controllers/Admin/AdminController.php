<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        // Set your Stripe secret API key
        Stripe::setApiKey(env('STRIPE_KEY'));

        $totalIncome = 0;
        $todayIncome = 0;
        $usdToPhp = 57; // Example conversion rate, update as needed

        try {
            // Fetch successful payment intents from the last 30 days
            $payments = PaymentIntent::all([
                'created' => [
                    'gte' => strtotime('-30 days'),
                ],
                'limit' => 100,
            ]);

            $todayStart = strtotime('today');
            $todayEnd = strtotime('tomorrow') - 1;

            foreach ($payments->data as $payment) {
                if ($payment->status === 'succeeded') {
                    $amountUsd = $payment->amount_received / 100;
                    $totalIncome += $amountUsd;

                    // Check if payment was made today
                    if ($payment->created >= $todayStart && $payment->created <= $todayEnd) {
                        $todayIncome += $amountUsd;
                    }
                }
            }
            $totalIncome = $totalIncome * $usdToPhp; // Convert to PHP
            $todayIncome = $todayIncome * $usdToPhp; // Convert to PHP

            // Count number of customers (users who are not admin)
            $customerCount = User::where('is_admin', 0)->count();

            // Fetch Stripe customers (limit to 100 for demo; for more, use pagination)
            $stripeCustomers = \Stripe\Customer::all(['limit' => 100]);
            $stripeCustomerCount = count($stripeCustomers->data);

        } catch (\Exception $e) {
            $totalIncome = null;
            $todayIncome = null;
            $customerCount = null;
            $stripeCustomerCount = null;
        }

        return Inertia::render('Admin/Dashboard', [
            'totalIncome' => $totalIncome,
            'todayIncome' => $todayIncome,
            'currencySymbol' => '₱',
            'customerCount' => $customerCount, // local users
            'stripeCustomerCount' => $stripeCustomerCount, // Stripe customers
        ]);
    }
}