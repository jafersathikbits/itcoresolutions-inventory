<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class OwnershipService
{
    /**
     * Owner = earliest created user who has role Super Admin.
     * This assumes you seeded first Super Admin in Phase 2.
     */
    public function getOwner(): ?User
    {
        return User::role('Super Admin')
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();
    }

    public function isOwner(User $user): bool
    {
        $owner = $this->getOwner();
        return $owner && ((int)$owner->id === (int)$user->id);
    }

    /**
     * Transfer ownership by making sure target is Super Admin
     * and making target "earlier" than current owner by swapping created_at timestamps.
     *
     * This avoids schema changes while enforcing "first Super Admin = owner".
     */
    public function transferOwnership(User $currentOwner, User $targetSuperAdmin): void
    {
        if (!$this->isOwner($currentOwner)) {
            throw new \RuntimeException('Only current owner can transfer ownership.');
        }

        if (!$targetSuperAdmin->hasRole('Super Admin')) {
            throw new \RuntimeException('Target user must be a Super Admin.');
        }

        if ((int)$currentOwner->id === (int)$targetSuperAdmin->id) {
            throw new \RuntimeException('Cannot transfer ownership to same user.');
        }

        DB::transaction(function () use ($currentOwner, $targetSuperAdmin) {
            // Swap created_at timestamps so target becomes earliest Super Admin
            $tmp = $currentOwner->created_at;

            $currentOwner->created_at = $targetSuperAdmin->created_at;
            $currentOwner->save();

            $targetSuperAdmin->created_at = $tmp;
            $targetSuperAdmin->save();
        });
    }

    public function superAdminCount(): int
    {
        return User::role('Super Admin')->count();
    }
}
