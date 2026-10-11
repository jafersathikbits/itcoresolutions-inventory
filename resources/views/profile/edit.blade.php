<x-app-layout>
    <x-slot name="title">My Profile</x-slot>

    @php
        $isAdminOrSuper = $user->hasAnyRole(['Admin', 'Super Admin']);
    @endphp

    <div class="page-narrow">
        <div class="mb-6 flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-xl font-semibold text-white">
                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
            </span>
            <div>
                <h1 class="page-title">{{ $user->name }}</h1>
                <p class="page-subtitle">{{ $user->email }} · {{ $user->getRoleNames()->first() ?? 'No role' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-form-errors />

            <div class="card">
                <div class="card-header"><h3 class="card-title">Account details</h3></div>
                <div class="card-body grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input" maxlength="120" required>
                    </div>
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="form-input" maxlength="180" required autocomplete="username">
                    </div>

                    <div class="sm:col-span-2">
                        @if($isAdminOrSuper)
                            <label for="telegram_chat_id" class="form-label">Telegram Chat ID <span class="font-normal text-slate-400">(optional)</span></label>
                            <input id="telegram_chat_id" name="telegram_chat_id" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}" maxlength="80" class="form-input">
                            <p class="form-help">Used for stock alerts and daily reports.</p>
                        @else
                            <div class="alert alert-info">
                                <x-icon name="info" class="h-5 w-5 shrink-0" />
                                <div>Telegram alerts are available only for <b>Admin</b> and <b>Super Admin</b>.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Change password</h3>
                        <p class="card-subtitle">Leave blank to keep your current password.</p>
                    </div>
                </div>
                <div class="card-body grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="form-label">New password</label>
                        <input id="password" name="password" type="password" minlength="6" maxlength="100" class="form-input" autocomplete="new-password">
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Save changes</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
