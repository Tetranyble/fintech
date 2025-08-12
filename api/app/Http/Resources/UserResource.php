<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="UserResource")
 * {
 *
 *   @OA\Property(
 *       property="id",
 *       type="integer",
 *       description="The user id"
 *    ),
 *   @OA\Property(
 *       property="name",
 *       type="string",
 *       description="The user firstname"
 *    ),
 *   @OA\Property(
 *       property="email",
 *       type="string",
 *       description="The user email"
 *    ),
 *   @OA\Property(
 *       property="created_at",
 *       type="string",
 *       description="The resource created date."
 *    ),
 * }
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'middlename' => $this->middlename,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
