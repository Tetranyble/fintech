<?php

namespace App\Http\Controllers\Api;

use App\Events\ApiRegistered;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationCodeController extends Controller
{
    /**
     * @OA\Post(
     * path="/email/verification-notification",
     * operationId="EmailVerificationNotificationCodeController::__invoke",
     * tags={"Authentication"},
     * summary="Attempts to resend verification code.",
     * description="Attempts to resend verification code.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="The user re-verification re-try.",
     *
     *         @OA\MediaType(
     *            mediaType="application/json",
     *            example={
     *                "email": "test@verited.com"
     *            }
     *        ),
     *    ),
     *
     *    @OA\Response(
     *         response=200,
     *         description="success",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message":"success",
     *                 "status": "success",
     *                 "status_code": 200,
     *                 "data": { }
     *             }
     *         )
     *     ),
     *
     *    @OA\Response(response=400, ref="#/components/responses/400"),
     *    @OA\Response(response=404, ref="#/components/responses/404"),
     *    @OA\Response(response=422, ref="#/components/responses/422"),
     *    @OA\Response(response="default", ref="#/components/responses/500")
     * )
     *
     * Attempts to revalidate the user.
     */
    public function __invoke(Request $request): JsonResponse
    {

        event(new ApiRegistered(
            $this->validateAndFindUser($request)
        ));

        return $this->success(
            '',
            'success'
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
