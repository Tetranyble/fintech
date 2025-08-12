<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Remove the specified resource from storage.
     *
     *
     * @param  RegistrationRequest  $request
     *
     * @OA\Get(
     * path="/verify-email/{email}/{code}",
     * operationId="VerifyEmailController::_invoke",
     * tags={"Authentication"},
     * summary="The user email verification",
     * description="The user email verification",
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="The user email",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The user code",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message":"Email verified successfully!",
     *                 "status": "success",
     *                 "status_code": 200,
     *                 "data": {
     *
     *                 }
     *             }
     *         )
     *     ),
     *
     *    @OA\Response(response=400, ref="#/components/responses/400"),
     *    @OA\Response(response=403, ref="#/components/responses/403"),
     *    @OA\Response(response=404, ref="#/components/responses/404"),
     *    @OA\Response(response=422, ref="#/components/responses/422"),
     *    @OA\Response(response="default", ref="#/components/responses/500")
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, User $user, string $code)
    {
        if ($user->verification_code !== $code) {
            return $this->success(
                [],
                'The code your provided is wrong. Please try again or request another code.',
                422
            );
        }

        if ($user->hasVerifiedEmail() || $user->markEmailAsVerified()) {

            event(new Verified($user));

            return $this->respondWithToken(auth('api')->login($user));
        }

    }

    protected function authenticate(string $email)
    {

    }
}
