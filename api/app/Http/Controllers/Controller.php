<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      termsOfService="This API is subject to terms of services and maybe be changed without service notice.",
 *      title="Ugbanawaji API Docs",
 *      description="Ugbanawaji Application Programme Interface(Play Ground)",
 *      x={
 *          "logo": {
 *              "url": "https://www.ugbanawaji.com/public/web/images/logo.png"
 *          }
 *      },
 *
 *      @OA\Contact(
 *          email="e.leonard@ugbanawaji.com",
 *          name="Ekenekiso Leonard Ugbanawaji",
 *          url="https://ugbanawaji.com"
 *      ),
 *
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OAS\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="http",
 *      scheme="bearer"
 * )
 * @OAS\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="https",
 *      scheme="bearer",
 *
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Allow user access to the system",
 * )
 * @OA\Tag(
 *     name="Profiles",
 *     description="The User profile resource collection",
 * )
 *
 * @OA\Server(
 *     description="Ugbanawaji API Live Server",
 *     url="https://fintech.ugbanawaji.com/api/v1"
 * )
 * @OA\Server(
 *     description="Ugbanawaji API Local Server. This server is attached to the local environment.",
 *     url="http://api.test/api/v1"
 * ),
 * @OA\Server(
 *      description="Ugbanawaji API Local Server. This server is attached to the local environment.",
 *      url="http://localhost:8000/api/v1"
 *  ),
 *
 * @OA\Components(
 *
 *     @OA\Response(
 *          response="200",
 *          description="Ok.",
 *
 *          @OA\JsonContent(
 *              type="object",
 *              example={"message": "Ok.", "errors": {}}
 *          )
 *     ),
 *
 *     @OA\Response(
 *          response="201",
 *          description="Created.",
 *
 *          @OA\JsonContent(
 *              type="object",
 *              example={"message": "Created.", "errors": {}}
 *          )
 *     ),
 *
 *     @OA\Response(
 *          response="202",
 *          description="Accepted.",
 *
 *          @OA\JsonContent(
 *              type="object",
 *              example={"message": "Created.", "errors": {}}
 *          )
 *     ),
 *
 *     @OA\Response(
 *          response="204",
 *          description="No Content.",
 *
 *          @OA\JsonContent(
 *              type="object",
 *              example={"message": "No Content.", "errors": {}}
 *          )
 *     ),
 *
 *     @OA\Response(
 *         response="400",
 *         description="Bad Request.",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             example={"message": "Bad Request.", "errors": {}}
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="401",
 *         description="Unauthenticated.",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             example={"message": "Unauthenticated.", "errors": {}}
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="403",
 *         description="Forbidden.",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             example={"message":"Forbidden.", "errors": {}}
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="404",
 *         description="Resource Not Found.",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             example={"message":"Resource Not Found.", "errors": {}}
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="413",
 *         description="Request Entity Too Large.",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             example={"message":"Request Entity Too Large.", "errors": {}}
 *         )
 *     ),
 *
 *     @OA\Response(
 *          response="422",
 *          description="Unprocessable Entity.",
 *
 *          @OA\JsonContent(
 *              type="object",
 *              example={"message":"Unprocessable Entity.", "errors": {}}
 *          )
 *     ),
 *
 *     @OA\Response(
 *          response="423",
 *          description="Locked.",
 *
 *          @OA\JsonContent(
 *              type="object",
 *              example={"message":"Locked.", "errors": {}}
 *          )
 *     ),
 *
 *     @OA\Response(
 *         response="500",
 *         description="Server Error.",
 *
 *         @OA\JsonContent(
 *             type="object",
 *             example={"message":"Server Error.", "errors": {}}
 *         )
 *     )
 * )
 */
abstract class Controller
{
    public function created(mixed $data, string $message = 'created successfully', int $status_code = 201, array $headers = [])
    {
        return Response::success($data, $message, $status_code, $headers);
    }

    public function success(mixed $data, string $message = 'Operation successful', int $status_code = 200, array $headers = [])
    {
        return Response::success($data ?? [], $message, $status_code, $headers);

    }

    public function delete(mixed $data, string $message = 'Deleted successfully', int $status_code = 204, array $headers = [])
    {
        return Response::success($data, $message, $status_code, $headers);
    }

    public function abort(mixed $data = [], string $message = 'Unauthorized', $status_code = 403, array $headers = [])
    {
        return Response::aborts($message, $data, $status_code, $headers);
    }

    public function error($data = [], $message = 'Something went wrong', $status_code = 500, array $headers = [])
    {
        return Response::error($data, $message, $status_code, $headers);
    }

    public function filter(Request $request, array $except = [])
    {
        return array_filter($request->except(
            array_merge(
                $except,
                ['_token']
            )));
    }

    public function respondWithToken(string $token, string $message = 'success')
    {
        return $this->success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], $message);
    }
}
