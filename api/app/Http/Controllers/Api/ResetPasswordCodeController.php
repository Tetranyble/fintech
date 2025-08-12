<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResetPasswordCodeController extends Controller
{
    /**
     * Attempts to login the user.
     *
     * @param  ResetPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ValidationException
     *
     * @OA\Post(
     * path="/forgot-password",
     * operationId="ResetPasswordCodeController::invoke",
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
     *            },
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
     *                 "data": {}
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
    public function __invoke(Request $request)
    {
        $user = $this->validateAndFindUser($request);
        $user->notify(
            new PasswordResetNotification(
                $user,
                $user->preEmailVerification()
            )
        );

        return $this->success(
            [],
            'A code has been sent to your email address'
        );
    }

    protected function validateAndFindUser(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
        ]);

        return User::where('email', $request->only('email'))
            ->firstOrFail();
    }
}
