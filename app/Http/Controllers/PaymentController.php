<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Handle incoming payment status webhook notifications from Midtrans.
     */
    public function notification(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $serverKey = (string) config('services.midtrans.server_key');

        // Security check: Verify SHA-512 signature
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($request->input('signature_key') !== $expectedSignature) {
            Log::warning('Midtrans webhook signature mismatch', [
                'order_id' => $orderId,
                'received_signature' => $request->input('signature_key'),
            ]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = Order::where('order_number', $orderId)->first();

        if (! $order) {
            Log::warning('Midtrans webhook order not found', ['order_id' => $orderId]);

            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = (string) $request->input('transaction_status');
        $fraudStatus = (string) $request->input('fraud_status');
        $paymentType = (string) $request->input('payment_type');
        $transactionId = (string) $request->input('transaction_id');

        if (in_array($transactionStatus, ['capture', 'settlement'], true)) {
            if ($fraudStatus === 'challenge') {
                $order->update([
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_method' => $paymentType ?: $order->payment_method,
                    'midtrans_order_id' => $transactionId ?: $order->midtrans_order_id,
                ]);
            } elseif ($fraudStatus !== 'deny') {
                $order->update([
                    'status' => 'processing',
                    'payment_status' => 'paid',
                    'payment_method' => $paymentType ?: $order->payment_method,
                    'midtrans_order_id' => $transactionId ?: $order->midtrans_order_id,
                ]);
            }
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
                'payment_method' => $paymentType ?: $order->payment_method,
            ]);
        } elseif ($transactionStatus === 'pending') {
            $order->update([
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $paymentType ?: $order->payment_method,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Notification processed successfully',
        ]);
    }

    /**
     * Handle user redirect after completing payment on Midtrans Snap.
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->query('order_id');

        if ($orderId) {
            $order = Order::where('order_number', $orderId)->first();

            if ($order) {
                return redirect()->route('checkout.success', $order)
                    ->with('success', 'Payment status updated. Thank you for your order.');
            }
        }

        return redirect()->route('home');
    }

    /**
     * Development helper: simulate payment completion in local/testing environments.
     */
    public function simulateSuccess(Order $order): RedirectResponse
    {
        if (! app()->environment('local', 'testing')) {
            abort(404);
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
            'payment_method' => 'midtrans_simulator',
            'midtrans_order_id' => 'SIM-'.strtoupper(uniqid()),
        ]);

        return redirect()->route('checkout.success', $order)
            ->with('success', 'Sandbox payment simulated successfully.');
    }
}
