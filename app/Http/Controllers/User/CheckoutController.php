<?php

namespace App\Http\Controllers\User;

use App\Helper\Cart;
use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $carts = $request->carts;
        $products = $request->products;

        // Prevent admin from buying any products
        if (isset($user->is_admin) && $user->is_admin) {
            return back()->with('error', 'Admins cannot buy products.');
        }

        $mergedData = [];

        // Build a product lookup by product id for efficiency
        $productLookup = [];
        foreach ($products as $product) {
            $productLookup[$product['id']] = $product;
        }

        // Loop through the "carts" array and merge with "products" data
        foreach ($carts as $cartItem) {
            if (isset($productLookup[$cartItem["product_id"]])) {
                $product = $productLookup[$cartItem["product_id"]];
                // Prevent user from buying their own product - check created_by instead of user_id
                if (!isset($product["created_by"]) || $product["created_by"] != $user->id) {
                    $mergedData[] = array_merge($cartItem, [
                        "title" => $product["title"],
                        'price' => $product['price']
                    ]);
                }
            }
        }

        // If all cart items are filtered out, show error
        if (empty($mergedData)) {
            return back()->with('error', 'You cannot buy your own products.');
        }

        // Stripe payment integration
        $stripe = new \Stripe\StripeClient(env('STRIPE_KEY'));
        $lineItems = [];
        foreach ($mergedData as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item['title'],
                    ],
                    'unit_amount' => (int)($item['price'] * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel'),
        ]);

        $newAddress = $request->address;
        if ($newAddress['address1'] != null) {
            $address = UserAddress::where('isMain', 1)->count();
            if ($address > 0) {
                UserAddress::where('isMain', 1)->update(['isMain' => 0]);
            }
            $address = new UserAddress();
            $address->address1 = $newAddress['address1'];
            $address->state = $newAddress['state'];
            $address->zipcode = $newAddress['zipcode'];
            $address->city = $newAddress['city'];
            $address->country_code = $newAddress['country_code'];
            $address->type = $newAddress['type'];
            $address->user_id = Auth::user()->id;
            $address->save();
        }
        
        $mainAddress = $user->user_address()->where('isMain', 1)->first();
        if ($mainAddress) {
            $order = new Order();
            $order->status = 'unpaid';
            $order->total_price = $request->total;
            $order->session_id = $checkout_session->id;
            $order->user_id = $user->id; // Add this line - set user_id
            $order->created_by = $user->id;
            $order->user_address_id = $mainAddress->id;
            $order->save();
            
            $cartItems = CartItem::where(['user_id' => $user->id])->get();
            foreach ($cartItems as $cartItem) {
                // Check created_by instead of user_id for products
                if ($cartItem->product && (!isset($cartItem->product->created_by) || $cartItem->product->created_by != $user->id)) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->product->price,
                    ]);
                }
                $cartItem->delete();
            }
            
            // Remove cart items from cookies
            $cookieCartItems = Cart::getCookieCartItems();
            foreach ($cookieCartItems as $item) {
                unset($item);
            }
            array_splice($cookieCartItems, 0, count($cookieCartItems));
            Cart::setCookieCartItems($cookieCartItems);

            $paymentData = [
                'order_id' => $order->id,
                'amount' => $request->total,
                'status' => 'pending',
                'type' => 'stripe',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                // 'session_id' => $session->id
            ];

            Payment::create($paymentData);
        }
        // Redirect to Stripe Checkout
        return Inertia::location($checkout_session->url);
    }

    public function success(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_KEY'));
        $sessionId = $request->get('session_id');
        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            if (!$session) {
                throw new NotFoundHttpException;
            }
            $order = Order::where('session_id', $session->id)->first();
            if (!$order) {
                throw new NotFoundHttpException();
            }
            if ($order->status === 'unpaid') {
                $order->status = 'paid';
                $order->save();
            }

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            throw new NotFoundHttpException();
        }
    }
}