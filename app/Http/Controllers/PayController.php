<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * The Paddle default payment link (Paddle > Checkout > Checkout settings).
 *
 * Paddle appends `?_ptxn=<transaction id>` to this URL and Paddle.js opens the
 * checkout for that transaction. Three different flows land here, and only the
 * first one comes from our own code:
 *
 *  1. CheckoutController redirects a buyer here after PaddleGateway opened a
 *     transaction.
 *  2. Paddle's "update your payment method" emails, sent to customers whose
 *     card is expiring.
 *  3. Paddle's dunning emails, after a renewal payment failed.
 *
 * Because 2 and 3 are sent by Paddle to an inbox, the visitor may well not be
 * signed in — which is why this route carries no auth middleware. Putting it
 * behind auth would bounce a customer trying to fix their card to the login
 * page and turn a recoverable payment failure into a cancellation.
 */
class PayController extends Controller
{
    public function show(Request $request)
    {
        return view('pay', [
            // Public by design: Paddle.js needs it in the browser.
            'clientToken' => config('services.paddle.client_token'),
            'sandbox' => config('services.paddle.environment') === 'sandbox',
            'locale' => app()->getLocale(),
            // Present for a real checkout, absent when someone opens the bare
            // link — the page explains itself instead of showing a blank screen.
            'hasTransaction' => $request->filled('_ptxn'),
        ]);
    }
}
