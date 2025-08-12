<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DestroyAccountController extends Controller
{
    /**
     * Attempts to logout the user.
     *
     *
     * @OA\Delete(
     * path="/users/destroy",
     * operationId="APi/DestroyAccountController::__invoke",
     * tags={"Profiles"},
     * security={ * {"sanctum": {} } * },
     * summary="Logout current user",
     * description="Delete Account",
     *
     *     @OA\Response(
     *         response=204,
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
        $user = auth('api')->user();
        $user->delete();

        return $this->delete([], 'success');
    }
}
