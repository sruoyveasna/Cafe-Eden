<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{
    // 👤 Get current user profile
    public function show(Request $request)
    {
        return response()->json(
            $request->user()->load('role')
        );
    }


    // 📝 Update profile info
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:male,female,other',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
        ]);

        // Update `users` table
        $user->update(['name' => $data['name'] ?? $user->name]);

        // Update `profiles` table
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $data['phone'] ?? '',
                'gender' => $data['gender'] ?? '',
                'birthdate' => $data['birthdate'] ?? null,
                'address' => $data['address'] ?? '',
            ]
        );

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->load('profile', 'role')
        ]);
    }


    // 🖼 Upload or change avatar
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $user = $request->user();
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'message' => 'Avatar updated.',
            'avatar' => $path
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->password = bcrypt($data['new_password']);
        $user->save();

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
