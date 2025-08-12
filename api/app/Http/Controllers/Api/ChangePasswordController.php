<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    /**
     * @OA\Patch(
     * path="/users/profile/password",
     * operationId="ProfilePhotoController::store",
     * tags={"Profiles"},
     * summary="Update user password",
     * security={ * {"sanctum": {} } * },
     * description="Update user password",
     *
     *    @OA\RequestBody(
     *         description="Update user password resource",
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             example={
     *                 "current_password": "leonard",
     *                 "password": "syndication_confirmation£",
     *                 "password_confirmation": "syndication_confirmation£",
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="The user rehydrated profile record is return with affected fields.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message":"success",
     *                 "status": "success",
     *                 "data": {
     *                     "firstname" : "Ugbanawaji",
     *                     "middlename" : "Leonard",
     *                     "lastname" : "Ekenekiso",
     *                     "phone" : "+23480666077**",
     *                     "email" : "leonard@hardeverse.org",
     *                     "photo": "http://localhost:8000/storage/images/DU8Y739YQWHDLKuhluwqehdluiwked.png",
     *                     "id" : 24,
     *                     "created_at" : "2022-09-08T12:29:54.000000Z",
     *                     "updated_at" : "2022-09-08T12:29:54.000000Z"
     *                 }
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
     * * Update the specified resource in storage and database.
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user('api')->update([
            'password' => $request->password,
        ]);

        return $this->success(
            new UserResource($request->user('api')),
            'success'
        );
    }
}
