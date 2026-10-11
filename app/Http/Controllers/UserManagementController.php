<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OwnershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(OwnershipService $ownership)
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);
        $owner = $ownership->getOwner();

        return view('users.index', [
            'users' => $users,
            'owner' => $owner,
        ]);
    }

    public function create()
    {
        $roles = ['Operator', 'Admin', 'Super Admin'];
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $roles = ['Operator', 'Admin', 'Super Admin'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'role' => ['required', Rule::in($roles)],
            'telegram_chat_id' => ['nullable', 'string', 'max:80'],
        ]);

        // Enforce: Operator should not have telegram_chat_id
        if ($data['role'] === 'Operator') {
            $data['telegram_chat_id'] = null;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telegram_chat_id' => $data['telegram_chat_id'] ?? null,
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user, OwnershipService $ownership)
    {
        $roles = ['Operator', 'Admin', 'Super Admin'];
        $isOwner = $ownership->isOwner($user);

        return view('users.edit', compact('user', 'roles', 'isOwner'));
    }

    public function update(Request $request, User $user, OwnershipService $ownership)
    {
        $roles = ['Operator', 'Admin', 'Super Admin'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
            'role' => ['required', Rule::in($roles)],
            'telegram_chat_id' => ['nullable', 'string', 'max:80'],
        ]);

        // Owner cannot be demoted away from Super Admin
        if ($ownership->isOwner($user) && $data['role'] !== 'Super Admin') {
            return back()->with('error', 'Owner cannot be demoted. Transfer ownership first.');
        }

        // Prevent removing last Super Admin
        if ($user->hasRole('Super Admin') && $data['role'] !== 'Super Admin') {
            if ($ownership->superAdminCount() <= 1) {
                return back()->with('error', 'Cannot demote the last remaining Super Admin.');
            }
        }

        // Enforce: Operator should not have telegram_chat_id
        if ($data['role'] === 'Operator') {
            $data['telegram_chat_id'] = null;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->telegram_chat_id = $data['telegram_chat_id'] ?? null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user, OwnershipService $ownership)
    {
        $authUser = $request->user();

        if ((int)$authUser->id === (int)$user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($ownership->isOwner($user)) {
            return back()->with('error', 'Owner cannot be deleted. Transfer ownership first.');
        }

        // Prevent deleting last Super Admin
        if ($user->hasRole('Super Admin') && $ownership->superAdminCount() <= 1) {
            return back()->with('error', 'Cannot delete the last remaining Super Admin.');
        }

        try {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Cannot delete user because it is referenced by orders/reports or other records.');
        }
    }

    public function transferOwnershipForm(OwnershipService $ownership)
    {
        $owner = $ownership->getOwner();

        $targets = User::role('Super Admin')
            ->when($owner, fn($q) => $q->where('id', '!=', $owner->id))
            ->orderBy('name')
            ->get();

        return view('users.transfer-ownership', [
            'owner' => $owner,
            'targets' => $targets,
        ]);
    }

    public function transferOwnership(Request $request, OwnershipService $ownership)
    {
        $data = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $owner = $ownership->getOwner();

        if (!$owner) {
            return back()->with('error', 'Owner not found.');
        }

        if ((int)$request->user()->id !== (int)$owner->id) {
            return back()->with('error', 'Only the current owner can transfer ownership.');
        }

        $target = User::findOrFail((int)$data['target_user_id']);

        try {
            $ownership->transferOwnership($owner, $target);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'Ownership transferred successfully.');
    }
}
