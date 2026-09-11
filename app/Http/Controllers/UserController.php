<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(UserRequest $request): User
    {
        return $request->user();
    }

    public function update(UserRequest $request): JsonResponse
    {
        $request->validated();

        $user = User::findOrFail($request->user()->id);

        $user->fill($request->all());

        $user->save();

        return response()->json($user, 200);
    }

    public function delete(UserRequest $request): JsonResponse
    {
        $request->user()->delete();

        return response()->json(['message' => 'Successfully deleted account'], 200);
    }
}
