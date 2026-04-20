<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:6', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'phone' => trim($data['phone']),
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_CLIENT,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $this->serializeUser($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($data)) {
            throw ValidationException::withMessages([
                'email' => ['Nepareizs e-pasts vai parole.'],
            ]);
        }

        if ($request->user()?->is_blocked) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Konts ir bloķēts. Sazinieties ar administratoru.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $this->serializeUser($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->serializeUser($request->user()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'min:6', 'max:30'],
        ]);

        $user->fill([
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
        ]);
        $user->save();

        return response()->json([
            'message' => 'Profils atjaunots.',
            'user' => $this->serializeUser($user->fresh()),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Pašreizējā parole nav pareiza.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['new_password']),
        ])->save();

        return response()->json([
            'message' => 'Parole veiksmīgi nomainīta.',
        ]);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'is_blocked' => (bool) $user->is_blocked,
            'specialization' => $user->specialization,
            'branch_id' => $user->branch_id,
        ];
    }
}
