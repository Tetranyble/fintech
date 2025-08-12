<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class RefreshTokenController extends Controller
{
    /**
     * Attempts to login the user.
     *
     *
     * @OA\Post(
     * path="/users/refresh",
     * operationId="APi/RefreshTokenController::__invoke",
     * tags={"Authentication"},
     * security={ * {"sanctum": {} } * },
     * summary="Refresh authenticated user's token",
     * description="Attempts to refresh user's access token",
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
     *                      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vdmVyaXRlZC50ZXN0L2FwaS92MS9sb2dpbiIsImlhdCI6MTY5NzI3NTMwOSwiZXhwIjoxNjk3Mjc4OTA5LCJuYmYiOjE2OTcyNzUzMDksImp0aSI6IjZBYmN0ZEJUaU9tY1RKemYiLCJzdWIiOiI1MSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjciLCJkYXRhIjp7ImlkIjo1MSwiZmlyc3RuYW1lIjoiQWx2YSIsIm1pZGRsZW5hbWUiOiJQZmVmZmVyIiwibGFzdG5hbWUiOiJDYXNwZXIiLCJ1c2VybmFtZSI6bnVsbCwiYmlvIjoiTW9sZXN0aWFlIGV4IHBlcmZlcmVuZGlzIHBlcnNwaWNpYXRpcyBoaWMgdW5kZS4iLCJpbnRyb2R1Y3Rpb24iOiJQbGFjZWF0IGN1bHBhIGVhIHJlY3VzYW5kYWUgbWFnbmFtIHNlZCB1dC4iLCJleHBlcmllbmNlIjoiRXhlcmNpdGF0aW9uZW0gZHVjaW11cyBkb2xvciBuaWhpbCBxdWlhIGFzcGVybmF0dXIuIiwiaW1hZ2UiOiIvc3RvcmFnZS9pbWFnZXMvMTE2YzI3MDgtZWE5OC00ODNiLWE5ZDAtNmQ4NWNkZWM0YjJkLTIwMjMtMDgtMTQtMTEtNDEtNTUuanBlZyIsInJvbGUiOiJTdHVkZW50Iiwicm9sZXMiOlt7ImlkIjoyLCJuYW1lIjoic3R1ZGVudCIsImxhYmVsIjoiU3R1ZGVudCJ9LHsiaWQiOjY5LCJuYW1lIjoidXQiLCJsYWJlbCI6IlV0In1dfX0.7gYWPr0VVFpBOyYKPO8qjfvEr09p8nwF8it-hGwM8Xk",
     *                      "token_type": "bearer",
     *                      "expires_in": "3600",
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
     *
     * @throws AuthenticationException
     */
    public function __invoke(Request $request)
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
}
