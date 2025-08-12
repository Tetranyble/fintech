<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $payments = $request->user('api')->payments()->latest()->paginate();

        return PaymentResource::collection($payments);
    }

    public function show(Payment $payment): PaymentResource
    {
        return new PaymentResource($payment->load('customer'));
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(null, 204);
    }

    public function store(CreatePaymentRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user('api');
            $data = $request->validated();

            // Step 1: Payment Creation
            if (! $user->hasSufficientBalance($data['amount'])) {
                return response()->json(['message' => 'Insufficient balance'], 422);
            }

            $payment = $user->payments()->create([
                'amount' => $data['amount'],
                'recipient' => $data['recipient'],
                'description' => $data['description'],
                'status' => 'pending',
            ]);

            // Step 2: Payment Processing
            $this->paymentService->processPayment($payment);

            return (new PaymentResource($payment))
                ->response()
                ->setStatusCode(201);
        });
    }
}
