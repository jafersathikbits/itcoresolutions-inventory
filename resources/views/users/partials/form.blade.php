{{-- Shared user fields. Expects $roles, optional $user and $isOwner. --}}
@php
    $user = $user ?? null;
    $isOwner = $isOwner ?? false;
    $currentRole = old('role', $user?->getRoleNames()->first() ?? 'Operator');
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2" x-data="{ role: @js($currentRole) }">
    <div>
        <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $user?->name) }}" class="form-input" maxlength="120" required autofocus>
    </div>

    <div>
        <label for="email" class="form-label">Email <span class="text-red-500">*</span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" class="form-input" maxlength="180" required autocomplete="off">
    </div>

    <div x-data="{ show: false }">
        <label for="password" class="form-label">
            Password @if($user)<span class="font-normal text-slate-400">(leave blank to keep)</span>@else<span class="text-red-500">*</span>@endif
        </label>
        <div class="relative">
            <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" class="form-input pr-10" minlength="6" maxlength="100" autocomplete="new-password" @unless($user) required @endunless>
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 mt-1 flex items-center px-3 text-slate-400 hover:text-slate-600" aria-label="Toggle password visibility"><x-icon name="eye" class="h-5 w-5" /></button>
        </div>
        <p class="form-help">At least 6 characters.</p>
    </div>

    <div>
        <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
        <select id="role" name="role" class="form-input" required x-model="role">
            @foreach($roles as $r)
                <option value="{{ $r }}" @selected($currentRole === $r)>{{ $r }}</option>
            @endforeach
        </select>
        @if($isOwner)
            <p class="form-help text-brand-700">This user is the Owner and must stay Super Admin until ownership is transferred.</p>
        @endif
    </div>

    <div class="sm:col-span-2 rounded-lg border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
        <b class="text-slate-800">Operator</b>: view materials/products, place orders, update order status.
        <b class="text-slate-800">Admin</b>: everything except user management.
        <b class="text-slate-800">Super Admin</b>: full access including users.
    </div>

    <template x-if="role === 'Admin' || role === 'Super Admin'">
        <div class="sm:col-span-2">
            <label for="telegram_chat_id" class="form-label">Telegram Chat ID <span class="font-normal text-slate-400">(optional)</span></label>
            <input id="telegram_chat_id" name="telegram_chat_id" value="{{ old('telegram_chat_id', $user?->telegram_chat_id) }}" maxlength="80" class="form-input" placeholder="e.g. 123456789">
            <p class="form-help">Admins and Super Admins with a chat ID receive stock alerts and daily reports on Telegram.</p>
        </div>
    </template>

    <template x-if="role === 'Operator'">
        <div class="sm:col-span-2 alert alert-info">
            <x-icon name="info" class="h-5 w-5 shrink-0" />
            <div>Operators don't receive Telegram alerts{{ $user?->telegram_chat_id ? '; the saved Chat ID will be cleared' : '' }}.</div>
        </div>
    </template>
</div>
