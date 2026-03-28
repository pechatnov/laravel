<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            User::query()->orderBy('created_at')->get()->map(fn (User $u) => $this->userPayload($u))
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $path = $request->file('avatar')->store('avatars', 'public');

        $user = User::query()->create([
            'first_name' => $request->validated('first_name'),
            'last_name' => $request->validated('last_name'),
            'phone' => $request->validated('phone'),
            'avatar_path' => $path,
        ]);

        return response()->json($this->userPayload($user), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($this->userPayload($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->safe()->except(['avatar']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($data);
        $user->save();

        return response()->json($this->userPayload($user->fresh()));
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'phone' => $user->phone,
            'avatarUrl' => ApiJson::publicFileUrl($user->avatar_path),
            'createdAt' => $user->created_at?->utc()->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => $user->updated_at?->utc()->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
