<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UsersMetaData;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

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
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => [Rules\Password::defaults()],
            'is_admin' => 'required|boolean'
        ]);

        if (array_key_exists('password', $fields) == false) {
            $fields['password'] = $this->getRandomPassword();
        }

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'is_admin' => $fields['is_admin'],
            'password' => bcrypt($fields['password'])
        ]);

        $metaData = is_array($request->meta_data) ? $request->meta_data : [];

        try {
            $metaDataList = ['user_id' => $user->id] + $metaData;
            $this->createNickname($fields, $metaDataList);
            UsersMetaData::create($metaDataList);
        } catch (QueryException $ex) {
            $user->delete();
            abort(422, 'Error: Could not create user.');
        }

        // refresh data before return
        $user = $user->fresh();

        return UserResource::make($user);
    }

    /**
     * Create Nickname with complete surname and first 3 letters of name.
     * see: https://github.com/EduardSchwarzkopf/BIS-Back-end-Assignment/issues/3
     *
     * @param  array $fields
     * @param  array $metaDataList
     */
    private function createNickname(array $fields, array &$metaDataList): void
    {

        if (array_key_exists('surname', $metaDataList) == false) {
            return;
        }

        $metaDataList['nickname'] = $metaDataList['surname'] . substr($fields['name'], 0, 3);
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
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $fields = $request->validate([
            'name' => 'string',
            'email' => 'email|unique:users,email,' . $user->id . ',id',
            'password' => [Rules\Password::defaults()],
            'is_admin' => 'boolean'
        ]);


        if ($user->is_admin == false && auth()->id() == $user->id) {
            // no-admin user is not allowed to change his admin status
            unset($fields['is_admin']);
        }

        if (array_key_exists('password', $fields)) {
            $fields['password'] = bcrypt($fields['password']);
        }

        $user->update($fields);

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

    private function getRandomPassword()
    {
        return bin2hex(openssl_random_pseudo_bytes(8));
    }
}
