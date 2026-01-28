<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Tariff;
use App\Models\Voucher;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use ItsMurumba\Mpesa\Mpesa;

class PaymentController extends Controller
{
    protected MikroTikService $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Show payment page for a tariff
     */
    public function show($tariffId)
    {
        $tariff = Tariff::findOrFail($tariffId);

        return Inertia::render('Payment/Mpesa', [
            'tariff' => $tariff,
        ]);
    }

    /**
     * Initiate M-Pesa STK Push payment
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/^254[17]\d{8}$/',
            'tariff_id' => 'required|exists:tariffs,id',
        ]);

        $tariff = Tariff::findOrFail($request->tariff_id);
        $amount = (int) $tariff->price;
        $phone = $request->phone;
        $reference = 'HOTSPOT-' . Str::random(10);

        try {
            $mpesa = new Mpesa();
            $response = $mpesa->expressPayment(
                $phone,
                $amount,
                $reference,
                $tariff->name
            );

            if ($response->successful()) {
                $data = $response->json();
                $checkoutId = $data['CheckoutRequestID'];

                // Create payment record
                Payment::create([
                    'amount' => $amount,
                    'phone' => $phone,
                    'gateway' => 'mpesa',
                    'transaction_id' => $checkoutId,
                    'status' => 'pending',
                    'tariff_id' => $tariff->id,
                ]);

                return response()->json([
                    'success' => true,
                    'checkout_id' => $checkoutId,
                    'message' => 'STK Push sent. Please enter your M-Pesa PIN.',
                ]);
            }

            $errorMessage = $response->json()['errorMessage'] ?? 'Payment initiation failed';
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 400);

        } catch (\Exception $e) {
            \Log::error('M-Pesa STK Push failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment service unavailable. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle M-Pesa callback
     */
    public function callback(Request $request)
    {
        \Log::info('M-Pesa Callback received', $request->all());

        $data = $request->all();
        $body = $data['Body']['stkCallback'] ?? null;

        if (!$body) {
            return response()->json(['status' => 'ok']);
        }

        $checkoutId = $body['CheckoutRequestID'];
        $payment = Payment::where('transaction_id', $checkoutId)->first();

        if (!$payment) {
            \Log::warning('Payment not found for checkout ID: ' . $checkoutId);
            return response()->json(['status' => 'ok']);
        }

        // Check if payment was successful
        if ($body['ResultCode'] === 0) {
            // Extract M-Pesa receipt number
            $metadata = collect($body['CallbackMetadata']['Item'] ?? []);
            $receipt = $metadata->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;

            // Update payment status
            $payment->update([
                'status' => 'paid',
                'mpesa_receipt' => $receipt,
            ]);

            // Generate voucher
            $voucher = Voucher::create([
                'code' => Str::upper(Str::random(10)),
                'tariff_id' => $payment->tariff_id,
                'payment_id' => $payment->id,
                'expires_at' => $payment->tariff->duration_minutes
                    ? now()->addMinutes($payment->tariff->duration_minutes)
                    : now()->addDays(30),
            ]);

            // Add user to MikroTik
            $this->mikrotik->addUser($voucher->code, $payment->tariff);

            \Log::info('Voucher generated: ' . $voucher->code . ' for payment: ' . $payment->id);

        } else {
            // Payment failed
            $payment->update(['status' => 'failed']);
            \Log::warning('Payment failed for checkout ID: ' . $checkoutId . ' - Code: ' . $body['ResultCode']);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Check payment status (for polling)
     */
    public function status($checkoutId)
    {
        $payment = Payment::where('transaction_id', $checkoutId)->first();

        if (!$payment) {
            return response()->json([
                'status' => 'not_found',
                'voucher' => null,
            ]);
        }

        $voucher = null;
        if ($payment->status === 'paid') {
            $voucherRecord = Voucher::where('payment_id', $payment->id)->first();
            $voucher = $voucherRecord ? $voucherRecord->code : null;
        }

        return response()->json([
            'status' => $payment->status,
            'voucher' => $voucher,
            'amount' => $payment->amount,
            'phone' => $payment->phone,
        ]);
    }
}
