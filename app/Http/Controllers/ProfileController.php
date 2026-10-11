<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $isAdminOrSuper = $user->hasAnyRole(['Admin', 'Super Admin']);

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'max:100', 'confirmed'],
        ];

        // Only Admin/Super Admin can update telegram_chat_id
        if ($isAdminOrSuper) {
            $rules['telegram_chat_id'] = ['nullable', 'string', 'max:80'];
        }

        $data = $request->validate($rules);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if ($isAdminOrSuper) {
            $user->telegram_chat_id = $data['telegram_chat_id'] ?? null;
        } else {
            // Enforce: Operators should not have telegram_chat_id
            $user->telegram_chat_id = null;
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated.');
    }
}
