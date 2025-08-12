<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Attempts to logout the user.
     *
     *
     * @OA\Post(
     * path="/users/logout",
     * operationId="APi/LogoutController::__invoke",
     * tags={"Authentication"},
     * security={ * {"sanctum": {} } * },
     * summary="Logout current user",
     * description="Logout current user",
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
     *                 "data": {
     *
     *                  }
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
        auth('api')->logout();

        return $this->success([], 'success');
    }
}
