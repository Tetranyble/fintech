<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Interface\PhoneVerificationInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneVerificationController extends Controller
{
    /**
     * @OA\Post(
     * path="/users/phone/verification",
     * operationId="Api/PhoneVerificationController::store",
     * tags={"Profiles"},
     * security={ * {"sanctum": {} } * },
     * summary="The user phone verification.",
     * description="The user phone verification.",
     *
     *    @OA\RequestBody(
     *         description="The user phone number to verify.",
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             example={
     *                 "phone" : "+23480666077**",
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="success",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "success",
     *                 "status": true,
     *                 "data": {
     *                     "firstname" : "Ugbanawaji",
     *                     "lastname" : "Ekenekiso",
     *                     "phone" : "+23480666077**",
     *                     "email" : "test@verited.com",
     *                     "created_at" : "2022-09-08T12:29:54.000000Z",
     *                     "updated_at" : "2022-09-08T12:29:54.000000Z",
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
     *  Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Request $request, PhoneVerificationInterface $client)
    {
        $phone = $request->validate([
            'phone' => 'required|phone:INTERNATIONAL,BE',
        ]);

        $client->create(
            $user = $request->user('api'),
            $phone['phone']
        );

        return $this->success(
            new UserResource($user),
            'success'
        );

    }

    /**
     * @OA\Post(
     * path="/users/phone/verify",
     * operationId="Api/PhoneVerificationController::verify",
     * tags={"Profiles"},
     * security={ * {"sanctum": {} } * },
     * summary="The user phone verification.",
     * description="The user phone verification.",
     *
     *    @OA\RequestBody(
     *         description="The code sent to the user phone number.",
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             example={
     *                 "code" : "373973",
     *             }
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
     *                 "message": "success",
     *                 "status": true,
     *                 "data": {
     *                     "firstname" : "Ugbanawaji",
     *                     "lastname" : "Ekenekiso",
     *                     "phone" : "+23480666077**",
     *                     "email" : "test@verited.com",
     *                     "created_at" : "2022-09-08T12:29:54.000000Z",
     *                     "updated_at" : "2022-09-08T12:29:54.000000Z",
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
     *  Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function verify(Request $request, PhoneVerificationInterface $client)
    {
        $code = $request->validate([
            'code' => 'required|numeric|min:6',
        ]);

        $client->verify(
            $user = $request->user('api'),
            $code['code']
        );

        return $this->success(
            new UserResource($user->refresh()),
            'success'
        );
    }
}
