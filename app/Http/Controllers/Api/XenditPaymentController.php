<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class XenditPaymentController extends Controller
{
    private $apiKey;
    private $baseUrl = 'https://api.xendit.co';

    // Daftar bank yang didukung
    private $supportedBanks = [
        'BCA',
        'BNI',
        'BRI',
        'MANDIRI',
        'PERMATA',
        'BSI',
        'BJB',
        'CIMB'
    ];

    public function __construct()
    {
        $this->apiKey = config('services.xendit.secret_key');
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_type' => 'required|string',
            'external_id' => 'required|string'
        ]);

        try {
            switch ($request->payment_type) {
                case 'VIRTUAL_ACCOUNT':
                    // Validasi bank code
                    if (!in_array($request->payment_method, $this->supportedBanks)) {
                        return response()->json([
                            'error' => 'Invalid bank code',
                            'message' => 'Supported banks are: ' . implode(', ', $this->supportedBanks)
                        ], 400);
                    }

                    return $this->createVirtualAccountPayment(
                        $request->customer_name,
                        $request->amount,
                        $request->payment_method,
                        $request->external_id
                    );
                case 'EWALLET':
                    return $this->createEWalletPayment(
                        $request->customer_name,
                        $request->amount,
                        $request->payment_method,
                        $request->external_id
                    );
                case 'RETAIL':
                    return $this->createRetailPayment(
                        $request->customer_name,
                        $request->amount,
                        $request->payment_method,
                        $request->external_id
                    );
                default:
                    return response()->json([
                        'error' => 'Payment method not supported',
                        'message' => 'Supported payment types are: VIRTUAL_ACCOUNT, EWALLET, RETAIL'
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment creation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function createVirtualAccountPayment(
        string $customerName,
        float $amount,
        string $bankCode,
        string $externalId
    ) {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withOptions([
                'verify' => false  // Disable SSL verification for development
            ])
            ->post($this->baseUrl . '/v2/virtual_accounts', [
                'external_id' => $externalId,
                'bank_code' => $bankCode,
                'name' => $customerName,
                'expected_amount' => $amount,
                'is_closed' => true,
                'expiration_date' => Carbon::now()->addHours(24)->toIso8601String(),
            ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        throw new \Exception('Failed to create virtual account: ' . $response->body());
    }
}
