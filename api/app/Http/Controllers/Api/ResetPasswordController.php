<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Notifications\PasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    /**
     * Attempts to login the user.
     *
     *
     * @throws ValidationException
     *
     * @OA\Post(
     * path="/reset-password",
     * operationId="ResetPasswordController::invoke",
     * tags={"Authentication"},
     * summary="Attempts to reset user credentials.",
     * description="Attempts to reset user credentials.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="The user credentials",
     *
     *         @OA\MediaType(
     *            mediaType="application/json",
     *            example={
     *               "email": "test@verited.com",
     *               "password": "password",
     *               "password_confirmation": "password",
     *               "token": "8937",
     *            },
     *
     *            @OA\Schema(
     *               ref="#/components/schemas/ResetPasswordRequest"
     *            ),
     *        ),
     *    ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message":"success",
     *                 "status": true,
     *                 "data": {"token": "2|I8yfaWt8rpfViHrY2VAZqUzG8IbxWeW4NMhIj1WQ"}
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=400, ref="#/components/responses/400"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response="default", ref="#/components/responses/500")
     * )
     */
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $user = $request->reset();
        $user->notify(
            new PasswordNotification($user)
        );

        return $this->success(
            [
                'token' => $user->createToken('web')->plainTextToken,
            ],
            'We have reset your access token'
        );
    }
}
