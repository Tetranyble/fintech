<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResumeRequest;
use App\Http\Resources\UserProfileResource;
use App\Services\FileSystem;
use App\Services\ResumeParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     * path="/users/resumes",
     * operationId="ProfileDocumentController::__invoke",
     * tags={"Resumes"},
     * summary="Created user resume with uploaded resume",
     * security={ * {"sanctum": {} } * },
     * description="Created user resume",
     *
     *    @OA\RequestBody(
     *         description="Created user resume",
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             example={
     *                 "resume": "Ugbanawaji",
     *             },
     *
     *             @OA\Schema(ref="#/components/schemas/ResumeRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="The user rehydrated profile record is return with affected fields.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message":"success",
     *                 "status": "true",
     *                 "data": {
     *                     "education" : "s3-public",
     *                     "job_experience": {{"name":"Harde Business"}},
     *                     "skills" : {"PHP","Laravel"},
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
    public function store(ResumeRequest $request, FileSystem $system, ResumeParser $parser)
    {

        $user = $request->user('api');
        $resume = $parser->parse(
            $system->show(
                $system->store(
                    $request->file('resume'),
                    'resumes',
                    \App\Enums\Disk::S3PUBLIC
                ),
                \App\Enums\Disk::S3PUBLIC
            )
        );
        $user->fill([
            'name' => $resume->name,
            'email' => $resume->email,
        ])->save();
        $profile = $user->profile()->create([
            'skills' => $resume->skills,
            'education' => $resume->education,
            'job_experience' => $resume->experience,
        ]);

        return $this->created(
            new UserProfileResource($profile),
            'success'
        );

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
