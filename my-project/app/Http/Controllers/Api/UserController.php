<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UsersMetaData;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        return UserResource::collection(User::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $request->merge(
            ['password' => bcrypt($request->password)]
        );
        $user = User::create($request->all());

        $metaData = is_array($request->meta_data) ? $request->meta_data : [];

        try {
            $metaDataList = ['user_id' => $user->id] + $metaData;
            $this->createNickname($request->name, $metaDataList);
            UsersMetaData::create($metaDataList);
        } catch (QueryException $ex) {
            $user->delete();
            abort(422, 'Error: Could not create user.');
        }

        // refresh data before return
        $user = $user->fresh();

        return UserResource::make($user)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Create Nickname with complete surname and first 3 letters of name.
     * see: https://github.com/EduardSchwarzkopf/BIS-Back-end-Assignment/issues/3
     *
     * @param  array $fields
     * @param  array $metaDataList
     */
    private function createNickname(string $name, array &$metaDataList): void
    {

        if (array_key_exists('surname', $metaDataList) == false) {
            return;
        }

        $metaDataList['nickname'] = $metaDataList['surname'] . substr($name, 0, 3);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        if ($request->password) {
            $request->merge(
                ['password' => bcrypt($request->password)]
            );
        }

        $user->update($request->all());

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return response()->noContent();
    }
}
